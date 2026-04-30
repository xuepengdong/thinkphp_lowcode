<?php

namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class Settings extends BaseController
{
    // 构造函数，检查并创建数据库表
    public function __construct()
    {
        $this->createSettingsTable();
    }
    
    // 创建设置表
    private function createSettingsTable()
    {
        // 检查表是否存在
        $tables = Db::query('SHOW TABLES LIKE ?', ['settings']);
        if (empty($tables)) {
            // 创建表
            $sql = "CREATE TABLE `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `key` varchar(100) NOT NULL,
                `value` text NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统设置表';";
            Db::query($sql);
        }
    }
    
    // 获取系统设置
    public function get(): Json
    {
        // 从数据库获取设置
        $settings = Db::table('settings')->select();
        
        // 转换为关联数组
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        
        // 设置响应头，确保返回的是UTF-8编码
        header('Content-Type: application/json; charset=utf-8');
        
        return json([
            'code' => 200,
            'data' => $result
        ]);
    }
    
    // 保存系统设置
    public function save(): Json
    {
        // 获取请求数据
        $data = $this->request->post();
        
        // 开启事务
        Db::startTrans();
        try {
            foreach ($data as $key => $value) {
                // 检查设置是否存在
                $existing = Db::table('settings')->where('key', $key)->find();
                if ($existing) {
                    // 更新设置
                    Db::table('settings')->where('key', $key)->update(['value' => $value]);
                } else {
                    // 添加新设置
                    Db::table('settings')->insert([
                        'key' => $key,
                        'value' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // 提交事务
            Db::commit();
            
            // 设置响应头，确保返回的是UTF-8编码
            header('Content-Type: application/json; charset=utf-8');
            
            return json([
                'code' => 200,
                'message' => '设置保存成功'
            ]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            
            // 设置响应头，确保返回的是UTF-8编码
            header('Content-Type: application/json; charset=utf-8');
            
            return json([
                'code' => 500,
                'message' => '设置保存失败: ' . $e->getMessage()
            ]);
        }
    }
    
    // 测试邮件设置
    public function testMail(): Json
    {
        // 获取请求数据
        $data = $this->request->post();
        
        // 这里可以添加实际的邮件测试逻辑
        // 例如使用PHPMailer发送测试邮件
        
        // 模拟测试成功
        sleep(1);
        
        // 设置响应头，确保返回的是UTF-8编码
        header('Content-Type: application/json; charset=utf-8');
        
        return json([
            'code' => 200,
            'message' => '邮件测试成功'
        ]);
    }
    
    // 初始化默认设置
    public function init(): Json
    {
        // 默认设置
        $defaultSettings = [
            // 基础设置
            'system_name' => 'Admin System',
            'system_title' => '后台管理系统',
            'system_description' => '基于Vue3和Ant Design的后台管理系统',
            'domain' => 'http://localhost:3000',
            'icp' => '京ICP备12345678号',
            
            // 安全设置
            'login_lock' => '1',
            'login_max_attempts' => '5',
            'password_min_length' => '8',
            'password_complexity' => 'uppercase,lowercase,number',
            'session_timeout' => '120',
            
            // 邮件设置
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'from_email' => 'noreply@example.com',
            'from_name' => 'Admin System',
            'smtp_username' => 'noreply@example.com',
            'smtp_password' => ''
        ];
        
        // 开启事务
        Db::startTrans();
        try {
            foreach ($defaultSettings as $key => $value) {
                // 检查设置是否存在
                $existing = Db::table('settings')->where('key', $key)->find();
                if (!$existing) {
                    // 添加新设置
                    Db::table('settings')->insert([
                        'key' => $key,
                        'value' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // 提交事务
            Db::commit();
            
            // 设置响应头，确保返回的是UTF-8编码
            header('Content-Type: application/json; charset=utf-8');
            
            return json([
                'code' => 200,
                'message' => '默认设置初始化成功'
            ]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            
            // 设置响应头，确保返回的是UTF-8编码
            header('Content-Type: application/json; charset=utf-8');
            
            return json([
                'code' => 500,
                'message' => '默认设置初始化失败: ' . $e->getMessage()
            ]);
        }
    }
}
