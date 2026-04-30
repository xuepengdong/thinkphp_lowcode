<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class ObjectField extends BaseController
{
    // 获取对象字段列表
    public function list($object_id): Json
    {
        $fields = Db::table('object_fields')
            ->where('object_id', $object_id)
            ->order('created_at', 'asc')
            ->select();
        
        return json([
            'code' => 200,
            'data' => $fields
        ]);
    }
    
    // 创建字段
    public function create(): Json
    {
        $data = $this->request->only([
            'object_id', 'field_name_en', 'field_name_zh', 'field_type', 'is_unique', 'remark'
        ]);
        
        $validate = $this->validate($data, [
            'object_id' => 'require',
            'field_name_en' => 'require|alpha_dash',
            'field_name_zh' => 'require',
            'field_type' => 'require',
            'is_unique' => 'boolean'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $id = Db::table('object_fields')->insertGetId($data);
        
        // 获取对象信息，用于获取表名
        $object = Db::table('objects')->where('object_id', $data['object_id'])->find();
        if ($object) {
            $tableName = $object['name_en'];
            // 向数据库表添加字段
            $this->addFieldToTable($tableName, $data['field_name_en'], $data['field_type'], $data['is_unique']);
        }
        
        return json([
            'code' => 200,
            'message' => '创建成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 更新字段
    public function update($id): Json
    {
        $data = $this->request->only([
            'field_name_en', 'field_name_zh', 'field_type', 'is_unique', 'remark'
        ]);
        
        $field = Db::table('object_fields')->find($id);
        if (!$field) {
            return json(['code' => 404, 'message' => '字段不存在']);
        }
        
        $validate = $this->validate($data, [
            'field_name_en' => 'require|alpha_dash',
            'field_name_zh' => 'require',
            'field_type' => 'require',
            'is_unique' => 'boolean'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        Db::table('object_fields')->where('id', $id)->update($data);
        
        // 获取对象信息，用于获取表名
        $object = Db::table('objects')->where('object_id', $field['object_id'])->find();
        if ($object) {
            $tableName = $object['name_en'];
            // 如果字段名或类型发生变化，更新数据库表结构
            if ($field['field_name_en'] !== $data['field_name_en'] || $field['field_type'] !== $data['field_type'] || $field['is_unique'] !== $data['is_unique']) {
                // 先删除旧字段
                $this->removeFieldFromTable($tableName, $field['field_name_en']);
                // 再添加新字段
                $this->addFieldToTable($tableName, $data['field_name_en'], $data['field_type'], $data['is_unique']);
            }
        }
        
        return json([
            'code' => 200,
            'message' => '更新成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 删除字段
    public function delete($id): Json
    {
        $field = Db::table('object_fields')->find($id);
        if (!$field) {
            return json(['code' => 404, 'message' => '字段不存在']);
        }
        
        // 获取对象信息，用于获取表名
        $object = Db::table('objects')->where('object_id', $field['object_id'])->find();
        if ($object) {
            $tableName = $object['name_en'];
            // 从数据库表中删除字段
            $this->removeFieldFromTable($tableName, $field['field_name_en']);
        }
        
        Db::table('object_fields')->delete($id);
        
        return json([
            'code' => 200,
            'message' => '删除成功'
        ]);
    }
    
    // 获取字段类型列表
    public function types(): Json
    {
        $types = [
            ['value' => 'text', 'label' => '文本'],
            ['value' => 'rich_text', 'label' => '富文本'],
            ['value' => 'id_card', 'label' => '身份证'],
            ['value' => 'email', 'label' => 'Email'],
            ['value' => 'url', 'label' => 'URL'],
            ['value' => 'mobile', 'label' => '手机'],
            ['value' => 'phone', 'label' => '电话'],
            ['value' => 'address', 'label' => '地址'],
            ['value' => 'pinyin', 'label' => '拼音'],
            ['value' => 'number', 'label' => '数字'],
            ['value' => 'amount', 'label' => '金额'],
            ['value' => 'date', 'label' => '日期'],
            ['value' => 'time', 'label' => '时间'],
            ['value' => 'radio', 'label' => '单选选项'],
            ['value' => 'document', 'label' => '单文档'],
            ['value' => 'internal_object', 'label' => '内部对象'],
            ['value' => 'week', 'label' => '星期'],
            ['value' => 'username', 'label' => '用户名']
        ];
        
        return json([
            'code' => 200,
            'data' => $types
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
}