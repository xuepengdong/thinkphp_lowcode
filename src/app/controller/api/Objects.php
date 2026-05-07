<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class Objects extends BaseController
{
    // 获取对象列表
    public function list(): Json
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $search = $this->request->param('search', '', 'trim');
        
        $query = Db::table('objects')->where('status', 'active');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name_zh', 'like', '%' . $search . '%')
                  ->whereOr('name_en', 'like', '%' . $search . '%')
                  ->whereOr('object_id', 'like', '%' . $search . '%');
            });
        }
        
        $total = $query->count();
        $objects = $query->order('created_at', 'desc')
            ->page($page, $limit)
            ->select();
        
        return json([
            'code' => 200,
            'data' => $objects,
            'total' => $total
        ]);
    }
    
    // 获取单个对象
    public function get($id): Json
    {
        // 先尝试按 object_id 字段查询
        $object = Db::table('objects')->where('object_id', $id)->find();
        // 如果没找到，再尝试按主键 id 查询
        if (!$object) {
            $object = Db::table('objects')->find($id);
        }
        
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        $fields = Db::table('object_fields')->where('object_id', $object['object_id'])->select();
        $object['fields'] = $fields;
        
        return json([
            'code' => 200,
            'data' => $object
        ]);
    }
    
    // 创建对象
    public function create(): Json
    {
        $data = $this->request->only([
            'object_id', 'name_en', 'name_zh', 'type', 'is_parent', 'remark', 'project', 'fields'
        ]);
        
        $validate = $this->validate($data, [
            'object_id' => 'require|alpha_dash|unique:objects',
            'name_en' => 'require',
            'name_zh' => 'require',
            'type' => 'require',
            'is_parent' => 'boolean',
            'project' => 'require'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        // 保存字段数据到临时变量
        $fields = $data['fields'] ?? [];
        // 从$data数组中移除fields字段，因为objects表中没有这个字段
        unset($data['fields']);
        
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $id = Db::table('objects')->insertGetId($data);
        
        // 创建数据库表
        $tableName = $data['name_en'];
        $this->createDatabaseTable($tableName, $fields);
        
        // 处理字段数据
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!empty($field['field_name_zh']) && !empty($field['field_name_en'])) {
                    Db::table('object_fields')->insert([
                        'object_id' => $data['object_id'],
                        'field_name_en' => $field['field_name_en'],
                        'field_name_zh' => $field['field_name_zh'],
                        'field_type' => $field['field_type'] ?? '',
                        'is_unique' => $field['is_unique'] ?? 0,
                        'remark' => $field['remark'] ?? '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        
        return json([
            'code' => 200,
            'message' => '创建成功',
            'data' => ['id' => $id] + $data + ['fields' => $fields]
        ]);
    }
    
    // 创建数据库表
    private function createDatabaseTable($tableName, $fields)
    {
        // 检查表是否已存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (!empty($exists)) {
            return;
        }
        
        // 开始创建表
        $sql = "CREATE TABLE `{$tableName}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `status` TINYINT DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        Db::execute($sql);
        
        // 添加自定义字段
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (!empty($field['field_name_en']) && !empty($field['field_type'])) {
                    $columnName = $field['field_name_en'];
                    $columnType = $this->getFieldType($field['field_type']);
                    $isUnique = $field['is_unique'] ?? 0;
                    $unique = $isUnique ? 'UNIQUE' : '';
                    
                    $alterSql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnType} {$unique};";
                    Db::execute($alterSql);
                }
            }
        }
    }
    
    // 获取字段类型
    private function getFieldType($fieldType)
    {
        $typeMap = [
            'text' => 'VARCHAR(255)',
            'rich_text' => 'TEXT',
            'id_card' => 'VARCHAR(18)',
            'email' => 'VARCHAR(255)',
            'url' => 'VARCHAR(500)',
            'mobile' => 'VARCHAR(11)',
            'phone' => 'VARCHAR(20)',
            'address' => 'VARCHAR(500)',
            'pinyin' => 'VARCHAR(255)',
            'number' => 'INT',
            'amount' => 'DECIMAL(10,2)',
            'date' => 'DATE',
            'time' => 'TIME',
            'radio' => 'VARCHAR(50)',
            'document' => 'TEXT',
            'internal_object' => 'INT',
            'week' => 'VARCHAR(20)',
            'username' => 'VARCHAR(50)'
        ];
        
        return $typeMap[$fieldType] ?? 'VARCHAR(255)';
    }
    
    // 更新对象
    public function update($id): Json
    {
        $data = $this->request->only([
            'name_en', 'name_zh', 'type', 'is_parent', 'remark', 'project', 'status', 'fields'
        ]);
        
        $object = Db::table('objects')->find($id);
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        $validate = $this->validate($data, [
            'name_en' => 'require',
            'name_zh' => 'require',
            'type' => 'require',
            'is_parent' => 'boolean',
            'project' => 'require',
            'status' => 'in:active,inactive'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        // 保存字段数据到临时变量
        $fields = $data['fields'] ?? [];
        // 从$data数组中移除fields字段，因为objects表中没有这个字段
        unset($data['fields']);
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        Db::table('objects')->where('id', $id)->update($data);
        
        // 处理字段数据
        if (!empty($fields)) {
            // 获取旧字段
            $oldFields = Db::table('object_fields')->where('object_id', $object['object_id'])->select();
            
            // 先删除旧字段
            Db::table('object_fields')->where('object_id', $object['object_id'])->delete();
            
            // 从数据库表中删除旧字段
            $tableName = $data['name_en'];
            foreach ($oldFields as $oldField) {
                $this->removeFieldFromTable($tableName, $oldField['field_name_en']);
            }
            
            // 插入新字段并添加到数据库表
            foreach ($fields as $field) {
                if (!empty($field['field_name_zh']) && !empty($field['field_name_en'])) {
                    Db::table('object_fields')->insert([
                        'object_id' => $object['object_id'],
                        'field_name_en' => $field['field_name_en'],
                        'field_name_zh' => $field['field_name_zh'],
                        'field_type' => $field['field_type'] ?? '',
                        'is_unique' => $field['is_unique'] ?? 0,
                        'remark' => $field['remark'] ?? '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // 向数据库表添加新字段
                    $this->addFieldToTable($tableName, $field['field_name_en'], $field['field_type'], $field['is_unique']);
                }
            }
        }
        
        return json([
            'code' => 200,
            'message' => '更新成功',
            'data' => ['id' => $id] + $data + ['fields' => $fields]
        ]);
    }
    
    // 删除对象
    public function delete($id): Json
    {
        $object = Db::table('objects')->find($id);
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 软删除
        Db::table('objects')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')]);
        
        return json([
            'code' => 200,
            'message' => '删除成功'
        ]);
    }
    
    // 生成页面
    public function generatePage(): Json
    {
        $data = $this->request->only([
            'object_id', 'page_type', 'is_api', 'menu_id', 'name', 'is_menu'
        ]);
        
        $validate = $this->validate($data, [
            'object_id' => 'require',
            'page_type' => 'require',
            'is_api' => 'boolean',
            'menu_id' => 'require|integer',
            'name' => 'require',
            'is_menu' => 'boolean'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        // 检查pages表是否存在，如果不存在则创建
        $this->createPagesTable();
        
        // 生成页面路径
        $path = '/page/' . $data['object_id'] . '/' . $data['page_type'];
        
        // 存储页面信息到数据库
        $pageData = [
            'object_id' => $data['object_id'],
            'name' => $data['name'],
            'page_type' => $data['page_type'],
            'is_api' => $data['is_api'],
            'menu_id' => $data['menu_id'],
            'path' => $path,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // 检查是否已存在相同的页面
        $existingPage = Db::table('pages')->where([
            'object_id' => $data['object_id'],
            'page_type' => $data['page_type']
        ])->find();
        
        if ($existingPage) {
            // 更新现有页面
            Db::table('pages')->where('id', $existingPage['id'])->update($pageData);
        } else {
            // 创建新页面
            Db::table('pages')->insert($pageData);
        }
        
        // 如果需要生成栏目
        if ($data['is_menu'] == 1 && $data['page_type'] == 'list') {
            // 检查栏目是否已存在
            $existingMenu = Db::table('menus')->where([
                'name' => $data['name'],
                'parent_id' => $data['menu_id'],
                'object_id' => $data['object_id']
            ])->find();
            
            if (!$existingMenu) {
                // 创建新栏目
                $menuData = [
                    'name' => $data['name'],
                    'path' => $path,
                    'parent_id' => $data['menu_id'],
                    'object_id' => $data['object_id'],
                    'type' => 'menu',
                    'sort' => 0,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                Db::table('menus')->insert($menuData);
            }
        }
        
        // 这里可以添加页面生成逻辑
        // 例如：创建对应的前端页面文件或API接口
        
        return json([
            'code' => 200,
            'message' => '页面生成成功',
            'data' => $data
        ]);
    }
    
    // 创建pages表
    private function createPagesTable()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `pages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `object_id` varchar(50) NOT NULL,
                `name` varchar(100) NOT NULL,
                `page_type` varchar(50) NOT NULL,
                `is_api` tinyint(1) NOT NULL DEFAULT '0',
                `menu_id` int(11) NOT NULL,
                `path` varchar(255) NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'active',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `object_id` (`object_id`),
                KEY `menu_id` (`menu_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            Db::execute($sql);
        } catch (\Exception $e) {
            // 记录错误信息
            \think\facade\Log::error('创建pages表失败: ' . $e->getMessage());
        }
    }
    
    // 清除数据
    public function clearData(): Json
    {
        $data = $this->request->only([
            'object_id'
        ]);
        
        $validate = $this->validate($data, [
            'object_id' => 'require'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        // 获取对象信息
        $object = Db::table('objects')->where('object_id', $data['object_id'])->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 获取表名
        $tableName = $object['name_en'];
        
        // 检查表是否存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (!empty($exists)) {
            // 清空表数据
            Db::execute("TRUNCATE TABLE `{$tableName}`");
        }
        
        return json([
            'code' => 200,
            'message' => '清除数据成功',
            'data' => $data
        ]);
    }
    
    // 向数据库表添加字段
    private function addFieldToTable($tableName, $fieldName, $fieldType, $isUnique = 0)
    {
        // 检查表是否存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (empty($exists)) {
            return;
        }
        
        // 检查字段是否已存在
        $fieldExists = Db::query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$fieldName}'");
        if (!empty($fieldExists)) {
            return;
        }
        
        // 获取字段类型
        $columnType = $this->getFieldType($fieldType);
        $unique = $isUnique ? 'UNIQUE' : '';
        
        // 添加字段
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$fieldName}` {$columnType} {$unique};";
        Db::execute($sql);
    }
    
    // 从数据库表删除字段
    private function removeFieldFromTable($tableName, $fieldName)
    {
        // 检查表是否存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (empty($exists)) {
            return;
        }
        
        // 检查字段是否存在
        $fieldExists = Db::query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$fieldName}'");
        if (empty($fieldExists)) {
            return;
        }
        
        // 删除字段
        $sql = "ALTER TABLE `{$tableName}` DROP COLUMN `{$fieldName}`;";
        Db::execute($sql);
    }
    
    // 创建数据记录
    public function createData(): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        $data = $this->request->param();
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        // 获取对象信息
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 获取表名
        $tableName = $object['name_en'];
        
        // 检查表是否存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (empty($exists)) {
            return json(['code' => 404, 'message' => '数据表不存在']);
        }
        
        // 移除 object_id 字段，因为数据表中没有这个字段
        unset($data['object_id']);
        
        // 添加时间戳
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // 插入数据
        $id = Db::table($tableName)->insertGetId($data);
        
        return json([
            'code' => 200,
            'message' => '创建成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 更新数据记录
    public function updateData($id): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        $data = $this->request->param();
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        // 获取对象信息
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 获取表名
        $tableName = $object['name_en'];
        
        // 检查表是否存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (empty($exists)) {
            return json(['code' => 404, 'message' => '数据表不存在']);
        }
        
        // 移除 object_id 和 id 字段
        unset($data['object_id']);
        unset($data['id']);
        
        // 添加更新时间
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // 更新数据
        Db::table($tableName)->where('id', $id)->update($data);
        
        return json([
            'code' => 200,
            'message' => '更新成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 删除数据记录
    public function deleteData($id): Json
    {
        // 获取 object_id（优先从 GET 参数获取）
        $object_id = $this->request->get('object_id', '', 'trim');
        
        // 如果 GET 参数为空，尝试从 POST 参数获取
        if (empty($object_id)) {
            $object_id = $this->request->post('object_id', '', 'trim');
        }
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        // 获取对象信息
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 获取表名
        $tableName = $object['name_en'];
        
        // 使用原生 SQL 直接删除，避免 Query Builder 可能的问题
        $sql = "DELETE FROM `{$tableName}` WHERE `id` = ?";
        Db::execute($sql, [$id]);
        
        return json([
            'code' => 200,
            'message' => '删除成功'
        ]);
    }
    
    // 获取对象数据
    public function data(): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        $page = $this->request->param('page', 1, 'intval');
        $page_size = $this->request->param('page_size', 10, 'intval');
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        // 获取对象信息
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 获取表名
        $tableName = $object['name_en'];
        
        // 检查表是否存在
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (empty($exists)) {
            return json(['code' => 200, 'data' => ['list' => [], 'total' => 0]]);
        }
        
        // 构建查询
        $query = Db::table($tableName);
        
        // 处理动态搜索条件
        $params = $this->request->param();
        foreach ($params as $key => $value) {
            // 跳过已知参数
            if (in_array($key, ['object_id', 'page', 'page_size'])) {
                continue;
            }
            
            // 跳过数字键（数组索引）
            if (!is_string($key)) {
                continue;
            }
            
            // 检查是否为操作符参数（兼容PHP 7）
            $operatorSuffix = '_operator';
            if (substr($key, -strlen($operatorSuffix)) === $operatorSuffix) {
                continue;
            }
            
            // 获取对应的操作符
            $operator = $this->request->param($key . '_operator', 'like');
            
            // 根据操作符添加查询条件
            switch ($operator) {
                case '=':
                    $query->where($key, '=', $value);
                    break;
                case '!=':
                    $query->where($key, '<>', $value);
                    break;
                case '>':
                    $query->where($key, '>', $value);
                    break;
                case '<':
                    $query->where($key, '<', $value);
                    break;
                case '>=':
                    $query->where($key, '>=', $value);
                    break;
                case '<=':
                    $query->where($key, '<=', $value);
                    break;
                case 'like':
                case 'start':
                case 'end':
                default:
                    $query->where($key, 'like', $value);
                    break;
            }
        }
        
        // 获取数据
        $total = $query->count();
        $list = $query
            ->order('id', 'desc')
            ->page($page, $page_size)
            ->select();
        
        return json([
            'code' => 200,
            'data' => [
                'list' => $list,
                'total' => $total
            ]
        ]);
    }

    // 导出数据
    public function export(): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        $fields = $this->request->param('fields', '[]');
        $filters = $this->request->param('filters', '[]');
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        $tableName = $object['name_en'];
        
        $exists = Db::query("SHOW TABLES LIKE '{$tableName}'");
        if (empty($exists)) {
            return json(['code' => 404, 'message' => '数据表不存在']);
        }
        
        $fields = json_decode($fields, true);
        $filters = json_decode($filters, true);
        
        $query = Db::table($tableName);
        
        // 添加筛选条件
        if (!empty($filters)) {
            if (isset($filters['limit']) && $filters['limit'] > 0) {
                $query->limit($filters['limit']);
            }
        }
        
        $data = $query->select()->toArray();
        
        // 如果指定了字段，只返回指定字段
        if (!empty($fields)) {
            $fieldNames = array_column($fields, 'fieldName');
            $data = array_map(function($item) use ($fieldNames) {
                $result = [];
                foreach ($fieldNames as $field) {
                    $result[$field] = $item[$field] ?? '';
                }
                return $result;
            }, $data);
        }
        
        // 生成Excel内容
        $header = !empty($fields) ? array_column($fields, 'label') : array_keys($data[0] ?? []);
        
        ob_start();
        $fp = fopen('php://output', 'w');
        
        // 写入BOM头，确保Excel正确识别UTF-8编码
        fwrite($fp, "\xEF\xBB\xBF");
        
        fputcsv($fp, $header);
        
        foreach ($data as $row) {
            $rowData = [];
            foreach ($header as $h) {
                $fieldName = !empty($fields) ? $fields[array_search($h, array_column($fields, 'label'))]['fieldName'] : $h;
                $rowData[] = $row[$fieldName] ?? '';
            }
            fputcsv($fp, $rowData);
        }
        
        fclose($fp);
        
        $content = ob_get_clean();
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="export_' . $object_id . '_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo $content;
        exit;
    }

    // 获取导入模板
    public function importTemplate(): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        $fields = Db::table('object_fields')->where('object_id', $object_id)->select()->toArray();
        
        $header = ['ID'];
        foreach ($fields as $field) {
            $header[] = $field['field_name_zh'] ?? $field['field_name_en'];
        }
        
        ob_start();
        $fp = fopen('php://output', 'w');
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $header);
        
        // 添加示例数据行
        $example = ['(新增留空或填写ID用于更新)'];
        foreach ($fields as $field) {
            $example[] = '请输入' . ($field['field_name_zh'] ?? $field['field_name_en']);
        }
        fputcsv($fp, $example);
        
        fclose($fp);
        
        $content = ob_get_clean();
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="import_template_' . $object_id . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo $content;
        exit;
    }

    // 导入数据
    public function import(): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        $relationTables = $this->request->param('relation_tables', '[]');
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        $tableName = $object['name_en'];
        
        $file = $this->request->file('file');
        if (!$file) {
            return json(['code' => 400, 'message' => '请选择上传文件']);
        }
        
        $info = $file->validate(['ext' => 'csv,xls,xlsx'])->move('./uploads');
        if (!$info) {
            return json(['code' => 400, 'message' => '文件上传失败: ' . $file->getError()]);
        }
        
        $filePath = './uploads/' . $info->getSaveName();
        
        try {
            $data = $this->readCsvFile($filePath);
        } catch (\Exception $e) {
            return json(['code' => 400, 'message' => '文件读取失败: ' . $e->getMessage()]);
        }
        
        if (empty($data)) {
            return json(['code' => 400, 'message' => '文件内容为空']);
        }
        
        $fields = Db::table('object_fields')->where('object_id', $object_id)->select()->toArray();
        $fieldNames = array_column($fields, 'field_name_en');
        
        $relationTables = json_decode($relationTables, true);
        
        $successCount = 0;
        $failCount = 0;
        $errors = [];
        
        foreach ($data as $index => $row) {
            try {
                $rowData = [];
                foreach ($fieldNames as $fieldName) {
                    $rowData[$fieldName] = $row[$fieldName] ?? '';
                }
                
                // 处理关联表
                foreach ($relationTables as $relation) {
                    $displayField = $relation['displayField'];
                    $keyField = $relation['keyField'];
                    $tableNameRel = $relation['tableName'];
                    
                    if (isset($row[$displayField]) && !empty($row[$displayField])) {
                        $relatedRecord = Db::table($tableNameRel)->where($displayField, $row[$displayField])->find();
                        if ($relatedRecord) {
                            $rowData[$keyField] = $relatedRecord[$keyField];
                        }
                    }
                }
                
                $rowData['created_at'] = date('Y-m-d H:i:s');
                $rowData['updated_at'] = date('Y-m-d H:i:s');
                
                if (!empty($row['id'])) {
                    Db::table($tableName)->where('id', $row['id'])->update($rowData);
                } else {
                    Db::table($tableName)->insert($rowData);
                }
                
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $errors[] = '第' . ($index + 2) . '行: ' . $e->getMessage();
            }
        }
        
        // 删除临时文件
        unlink($filePath);
        
        return json([
            'code' => 200,
            'success' => true,
            'message' => '导入完成',
            'data' => [
                'successCount' => $successCount,
                'failCount' => $failCount,
                'errors' => $errors
            ]
        ]);
    }

    // 获取关联表列表
    public function relationTables(): Json
    {
        $object_id = $this->request->param('object_id', '', 'trim');
        
        if (empty($object_id)) {
            return json(['code' => 400, 'message' => '对象ID不能为空']);
        }
        
        $object = Db::table('objects')->where('object_id', $object_id)->find();
        if (!$object) {
            return json(['code' => 404, 'message' => '对象不存在']);
        }
        
        // 获取所有其他对象作为可能的关联表
        $relations = Db::table('objects')
            ->where('object_id', '<>', $object_id)
            ->where('status', 'active')
            ->select()
            ->toArray();
        
        $result = [];
        foreach ($relations as $rel) {
            $firstField = Db::table('object_fields')
                ->where('object_id', $rel['object_id'])
                ->order('id')
                ->find();
            
            $result[] = [
                'table_name' => $rel['name_en'],
                'key_field' => 'id',
                'display_field' => $firstField['field_name_en'] ?? 'name'
            ];
        }
        
        return json([
            'code' => 200,
            'success' => true,
            'data' => $result
        ]);
    }

    // 读取CSV文件
    private function readCsvFile($filePath)
    {
        $data = [];
        $header = [];
        
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception('无法打开文件');
        }
        
        // 读取BOM头
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($file, 0);
        }
        
        $rowIndex = 0;
        while (($row = fgetcsv($file)) !== false) {
            if ($rowIndex === 0) {
                $header = $row;
            } else {
                $rowData = [];
                foreach ($header as $index => $h) {
                    $rowData[$h] = $row[$index] ?? '';
                }
                $data[] = $rowData;
            }
            $rowIndex++;
        }
        
        fclose($file);
        
        return $data;
    }
}