<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class ObjectTrigger extends BaseController
{
    // 获取对象的触发器列表
    public function list($object_id): Json
    {
        $triggers = Db::table('object_triggers')
            ->where('object_id', $object_id)
            ->order('created_at', 'desc')
            ->select();
        
        return json([
            'code' => 200,
            'data' => $triggers
        ]);
    }
    
    // 创建触发器
    public function create(): Json
    {
        $data = $this->request->only([
            'object_id', 'name', 'description', 'event', 'condition', 'action', 'status'
        ]);
        
        $validate = $this->validate($data, [
            'object_id' => 'require',
            'name' => 'require',
            'event' => 'require',
            'action' => 'require',
            'status' => 'in:active,inactive'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $id = Db::table('object_triggers')->insertGetId($data);
        
        return json([
            'code' => 200,
            'message' => '创建成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 更新触发器
    public function update($id): Json
    {
        $data = $this->request->only([
            'name', 'description', 'event', 'condition', 'action', 'status'
        ]);
        
        $trigger = Db::table('object_triggers')->find($id);
        if (!$trigger) {
            return json(['code' => 404, 'message' => '触发器不存在']);
        }
        
        $validate = $this->validate($data, [
            'name' => 'require',
            'event' => 'require',
            'action' => 'require',
            'status' => 'in:active,inactive'
        ]);
        
        if ($validate !== true) {
            return json(['code' => 400, 'message' => $validate]);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        Db::table('object_triggers')->where('id', $id)->update($data);
        
        return json([
            'code' => 200,
            'message' => '更新成功',
            'data' => ['id' => $id] + $data
        ]);
    }
    
    // 删除触发器
    public function delete($id): Json
    {
        $trigger = Db::table('object_triggers')->find($id);
        if (!$trigger) {
            return json(['code' => 404, 'message' => '触发器不存在']);
        }
        
        Db::table('object_triggers')->delete($id);
        
        return json([
            'code' => 200,
            'message' => '删除成功'
        ]);
    }
    
    // 获取触发器事件类型
    public function events(): Json
    {
        $events = [
            ['value' => 'create', 'label' => '创建'],
            ['value' => 'update', 'label' => '更新'],
            ['value' => 'delete', 'label' => '删除']
        ];
        
        return json([
            'code' => 200,
            'data' => $events
        ]);
    }
}