<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class Page extends BaseController
{
    // 获取页面列表
    public function list(): Json
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 10);
        $search = $this->request->param('search', '');
        
        $query = Db::table('pages')
            ->where('status', 'active');
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->whereOr('page_type', 'like', '%' . $search . '%')
                  ->whereOr('object_id', 'like', '%' . $search . '%');
            });
        }
        
        $total = $query->count();
        $pages = $query->order('created_at', 'desc')
            ->limit(($page - 1) * $limit, $limit)
            ->select();
        
        return json([
            'code' => 200,
            'data' => $pages,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }
    
    // 删除页面
    public function delete($id): Json
    {
        $page = Db::table('pages')->find($id);
        if (!$page) {
            return json(['code' => 404, 'message' => '页面不存在']);
        }
        
        Db::table('pages')->where('id', $id)->update(['status' => 'inactive']);
        
        return json([
            'code' => 200,
            'message' => '删除成功'
        ]);
    }
    
    // 复制页面
    public function copy($id): Json
    {
        $page = Db::table('pages')->find($id);
        if (!$page) {
            return json(['code' => 404, 'message' => '页面不存在']);
        }
        
        $newPage = [
            'object_id' => $page['object_id'],
            'name' => $page['name'] . '（复制）',
            'page_type' => $page['page_type'],
            'is_api' => $page['is_api'],
            'menu_id' => $page['menu_id'],
            'path' => $page['path'] . '_copy',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $newId = Db::table('pages')->insertGetId($newPage);
        
        return json([
            'code' => 200,
            'message' => '复制成功',
            'data' => ['id' => $newId] + $newPage
        ]);
    }
    
    // 更新页面
    public function update($id): Json
    {
        $data = $this->request->only([
            'name', 'page_type', 'is_api', 'menu_id', 'path', 'status'
        ]);
        
        $page = Db::table('pages')->find($id);
        if (!$page) {
            return json(['code' => 404, 'message' => '页面不存在']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        Db::table('pages')->where('id', $id)->update($data);
        
        return json([
            'code' => 200,
            'message' => '更新成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 获取/保存页面设置
    public function setting(): Json
    {
        $request = $this->request;
        
        // 如果是POST请求，保存设置
        if ($request->isPost()) {
            $data = $request->only([
                'page_id', 'object_id', 'display_fields', 'display_form', 'fixedColumns',
                'defaultExpand', 'defaultLoad', 'countRecords', 'showNonIdle', 'supportIdleTime',
                'showCondition', 'sql', 'leftTreePage', 'blockName', 'page', 'relatedObject',
                'relatedCondition', 'showSearchCondition', 'customRelatedCondition'
            ]);
            
            if (empty($data['page_id']) && empty($data['object_id'])) {
                return json(['code' => 400, 'message' => '页面ID或对象ID不能为空']);
            }
            
            // 检查页面设置表是否存在
            $this->createPageSettingsTable();
            
            // 序列化display_fields
            if (!empty($data['display_fields'])) {
                $data['display_fields'] = json_encode($data['display_fields']);
            }
            
            // 检查是否已存在设置
            $query = Db::table('page_settings');
            if (!empty($data['page_id'])) {
                $query->where('page_id', $data['page_id']);
            } else {
                $query->where('object_id', $data['object_id']);
            }
            
            $existing = $query->find();
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing) {
                // 更新现有设置
                $query->update($data);
            } else {
                // 创建新设置
                $data['created_at'] = date('Y-m-d H:i:s');
                Db::table('page_settings')->insert($data);
            }
            
            return json([
                'code' => 200,
                'message' => '保存成功'
            ]);
        }
        
        // GET请求，获取页面设置
        $page_id = $this->request->param('page_id', '', 'trim');
        $object_id = $this->request->param('object_id', '', 'trim');
        
        if (empty($page_id) && empty($object_id)) {
            return json(['code' => 400, 'message' => '页面ID或对象ID不能为空']);
        }
        
        // 检查页面设置表是否存在
        $this->createPageSettingsTable();
        
        // 根据页面ID或对象ID获取页面设置
        $query = Db::table('page_settings');
        if (!empty($page_id)) {
            $query->where('page_id', $page_id);
        } else {
            $query->where('object_id', $object_id);
        }
        
        $setting = $query->find();
        
        if (!$setting) {
            // 如果没有设置，返回默认设置
            $displayFields = [];
            
            // 如果提供了对象ID，获取对象的字段信息
            if (!empty($object_id)) {
                $fields = Db::table('object_fields')->where('object_id', $object_id)->where('status', 'active')->select();
                foreach ($fields as $field) {
                    $displayFields[] = [
                        'label' => $field['field_name_zh'],
                        'fieldName' => $field['field_name_en']
                    ];
                }
            }
            
            return json([
                'code' => 200,
                'data' => [
                    'page_name' => '列表页面',
                    'display_fields' => $displayFields,
                    'display_form' => '普通表格',
                    'fixedColumns' => '',
                    'defaultExpand' => '是',
                    'defaultLoad' => '是',
                    'countRecords' => '是',
                    'showNonIdle' => '是',
                    'supportIdleTime' => '否',
                    'showCondition' => '否',
                    'sql' => '',
                    'leftTreePage' => '',
                    'blockName' => '',
                    'page' => '',
                    'relatedObject' => '',
                    'relatedCondition' => '',
                    'showSearchCondition' => '',
                    'customRelatedCondition' => ''
                ]
            ]);
        }
        
        // 解析display_fields
        if (!empty($setting['display_fields'])) {
            $setting['display_fields'] = json_decode($setting['display_fields'], true);
        } else {
            $setting['display_fields'] = [];
        }
        
        return json([
            'code' => 200,
            'data' => $setting
        ]);
    }
    
    // 创建页面设置表
    private function createPageSettingsTable()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `page_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `page_id` varchar(50) DEFAULT NULL,
                `object_id` varchar(50) DEFAULT NULL,
                `page_name` varchar(100) DEFAULT NULL,
                `display_fields` text,
                `display_form` varchar(50) DEFAULT '普通表格',
                `fixedColumns` varchar(50) DEFAULT '',
                `defaultExpand` varchar(10) DEFAULT '是',
                `defaultLoad` varchar(10) DEFAULT '是',
                `countRecords` varchar(10) DEFAULT '是',
                `showNonIdle` varchar(10) DEFAULT '是',
                `supportIdleTime` varchar(10) DEFAULT '否',
                `showCondition` varchar(10) DEFAULT '否',
                `sql` text,
                `leftTreePage` varchar(255) DEFAULT '',
                `blockName` varchar(100) DEFAULT '',
                `page` varchar(50) DEFAULT '',
                `relatedObject` varchar(50) DEFAULT '',
                `relatedCondition` varchar(255) DEFAULT '',
                `showSearchCondition` varchar(10) DEFAULT '',
                `customRelatedCondition` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `page_id` (`page_id`),
                KEY `object_id` (`object_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            Db::execute($sql);
        } catch (\Exception $e) {
            // 记录错误信息
            \think\facade\Log::error('创建page_settings表失败: ' . $e->getMessage());
        }
    }
}
