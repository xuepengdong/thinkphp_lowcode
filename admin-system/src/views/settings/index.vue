<template>
  <div>
    <div class="page-header">
      <h2 class="page-title">系统设置</h2>
    </div>
    
    <a-tabs default-active-key="basic" size="large">
      <a-tab-pane key="basic" tab="基础设置">
        <a-card title="基础信息" style="margin-bottom: 24px;">
          <a-form
            :model="basicForm"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 18 }"
            layout="horizontal"
            size="large"
          >
            <a-form-item label="系统名称">
              <a-input v-model:value="basicForm.systemName" />
            </a-form-item>
            <a-form-item label="系统标题">
              <a-input v-model:value="basicForm.systemTitle" />
            </a-form-item>
            <a-form-item label="系统描述">
              <a-textarea v-model:value="basicForm.systemDescription" :rows="4" />
            </a-form-item>
            <a-form-item label="网站域名">
              <a-input v-model:value="basicForm.domain" />
            </a-form-item>
            <a-form-item label="备案号">
              <a-input v-model:value="basicForm.icp" />
            </a-form-item>
            <a-form-item label="">
              <a-button type="primary" @click="saveBasicSettings">保存设置</a-button>
            </a-form-item>
          </a-form>
        </a-card>
      </a-tab-pane>
      
      <a-tab-pane key="security" tab="安全设置">
        <a-card title="安全配置" style="margin-bottom: 24px;">
          <a-form
            :model="securityForm"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 18 }"
            layout="horizontal"
            size="large"
          >
            <a-form-item label="登录失败锁定">
              <a-switch v-model:checked="securityForm.loginLock" />
            </a-form-item>
            <a-form-item label="登录失败次数">
              <a-input-number v-model:value="securityForm.loginMaxAttempts" :min="1" :max="10" />
            </a-form-item>
            <a-form-item label="密码最小长度">
              <a-input-number v-model:value="securityForm.passwordMinLength" :min="6" :max="20" />
            </a-form-item>
            <a-form-item label="密码复杂度">
              <a-checkbox-group v-model:value="securityForm.passwordComplexity">
                <a-checkbox value="uppercase">大写字母</a-checkbox>
                <a-checkbox value="lowercase">小写字母</a-checkbox>
                <a-checkbox value="number">数字</a-checkbox>
                <a-checkbox value="symbol">特殊符号</a-checkbox>
              </a-checkbox-group>
            </a-form-item>
            <a-form-item label="会话超时">
              <a-slider v-model:value="securityForm.sessionTimeout" :min="30" :max="720" :step="30" />
              <span>{{ securityForm.sessionTimeout }} 分钟</span>
            </a-form-item>
            <a-form-item label="">
              <a-button type="primary" @click="saveSecuritySettings">保存设置</a-button>
            </a-form-item>
          </a-form>
        </a-card>
      </a-tab-pane>
      
      <a-tab-pane key="mail" tab="邮件设置">
        <a-card title="邮件配置" style="margin-bottom: 24px;">
          <a-form
            :model="mailForm"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 18 }"
            layout="horizontal"
            size="large"
          >
            <a-form-item label="SMTP服务器">
              <a-input v-model:value="mailForm.smtpHost" />
            </a-form-item>
            <a-form-item label="SMTP端口">
              <a-input-number v-model:value="mailForm.smtpPort" :min="1" :max="65535" />
            </a-form-item>
            <a-form-item label="发件人邮箱">
              <a-input v-model:value="mailForm.fromEmail" />
            </a-form-item>
            <a-form-item label="发件人名称">
              <a-input v-model:value="mailForm.fromName" />
            </a-form-item>
            <a-form-item label="用户名">
              <a-input v-model:value="mailForm.username" />
            </a-form-item>
            <a-form-item label="密码">
              <a-input-password v-model:value="mailForm.password" />
            </a-form-item>
            <a-form-item label="">
              <a-space>
                <a-button type="primary" @click="saveMailSettings">保存设置</a-button>
                <a-button @click="testMailSettings">测试连接</a-button>
              </a-space>
            </a-form-item>
          </a-form>
        </a-card>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { message } from 'ant-design-vue'
import request from '@/utils/request'

// 基础设置表单数据
const basicForm = ref({
  systemName: 'Admin System',
  systemTitle: '后台管理系统',
  systemDescription: '基于Vue3和Ant Design的后台管理系统',
  domain: 'http://localhost:3000',
  icp: '京ICP备12345678号'
});

// 安全设置表单数据
const securityForm = ref({
  loginLock: true,
  loginMaxAttempts: 5,
  passwordMinLength: 8,
  passwordComplexity: ['uppercase', 'lowercase', 'number'],
  sessionTimeout: 120
});

// 邮件设置表单数据
const mailForm = ref({
  smtpHost: 'smtp.example.com',
  smtpPort: 587,
  fromEmail: 'noreply@example.com',
  fromName: 'Admin System',
  username: 'noreply@example.com',
  password: ''
});

// 获取系统设置
const getSettings = async () => {
  try {
    const response = await request.get('/api/settings/get')
    if (response.code === 200) {
      const data = response.data
      // 填充基础设置
      if (data.system_name) basicForm.value.systemName = data.system_name
      if (data.system_title) basicForm.value.systemTitle = data.system_title
      if (data.system_description) basicForm.value.systemDescription = data.system_description
      if (data.domain) basicForm.value.domain = data.domain
      if (data.icp) basicForm.value.icp = data.icp
      
      // 填充安全设置
      if (data.login_lock) securityForm.value.loginLock = data.login_lock === '1'
      if (data.login_max_attempts) securityForm.value.loginMaxAttempts = parseInt(data.login_max_attempts)
      if (data.password_min_length) securityForm.value.passwordMinLength = parseInt(data.password_min_length)
      if (data.password_complexity) securityForm.value.passwordComplexity = data.password_complexity.split(',')
      if (data.session_timeout) securityForm.value.sessionTimeout = parseInt(data.session_timeout)
      
      // 填充邮件设置
      if (data.smtp_host) mailForm.value.smtpHost = data.smtp_host
      if (data.smtp_port) mailForm.value.smtpPort = parseInt(data.smtp_port)
      if (data.from_email) mailForm.value.fromEmail = data.from_email
      if (data.from_name) mailForm.value.fromName = data.from_name
      if (data.smtp_username) mailForm.value.username = data.smtp_username
      if (data.smtp_password) mailForm.value.password = data.smtp_password
    }
  } catch (error) {
    message.error('获取系统设置失败')
  }
}

// 保存基础设置
const saveBasicSettings = async () => {
  try {
    const data = {
      system_name: basicForm.value.systemName,
      system_title: basicForm.value.systemTitle,
      system_description: basicForm.value.systemDescription,
      domain: basicForm.value.domain,
      icp: basicForm.value.icp
    }
    const response = await request.post('/api/settings/save', data)
    if (response.code === 200) {
      message.success('基础设置已保存')
    }
  } catch (error) {
    message.error('保存基础设置失败')
  }
};

// 保存安全设置
const saveSecuritySettings = async () => {
  try {
    const data = {
      login_lock: securityForm.value.loginLock ? '1' : '0',
      login_max_attempts: securityForm.value.loginMaxAttempts.toString(),
      password_min_length: securityForm.value.passwordMinLength.toString(),
      password_complexity: securityForm.value.passwordComplexity.join(','),
      session_timeout: securityForm.value.sessionTimeout.toString()
    }
    const response = await request.post('/api/settings/save', data)
    if (response.code === 200) {
      message.success('安全设置已保存')
    }
  } catch (error) {
    message.error('保存安全设置失败')
  }
};

// 保存邮件设置
const saveMailSettings = async () => {
  try {
    const data = {
      smtp_host: mailForm.value.smtpHost,
      smtp_port: mailForm.value.smtpPort.toString(),
      from_email: mailForm.value.fromEmail,
      from_name: mailForm.value.fromName,
      smtp_username: mailForm.value.username,
      smtp_password: mailForm.value.password
    }
    const response = await request.post('/api/settings/save', data)
    if (response.code === 200) {
      message.success('邮件设置已保存')
    }
  } catch (error) {
    message.error('保存邮件设置失败')
  }
};

// 测试邮件设置
const testMailSettings = async () => {
  try {
    const data = {
      smtp_host: mailForm.value.smtpHost,
      smtp_port: mailForm.value.smtpPort,
      from_email: mailForm.value.fromEmail,
      from_name: mailForm.value.fromName,
      smtp_username: mailForm.value.username,
      smtp_password: mailForm.value.password
    }
    const response = await request.post('/api/settings/test-mail', data)
    if (response.code === 200) {
      message.success('邮件设置测试成功')
    }
  } catch (error) {
    message.error('邮件设置测试失败')
  }
};

// 初始化默认设置
const initSettings = async () => {
  try {
    const response = await request.post('/api/settings/init')
    if (response.code === 200) {
      message.success('默认设置初始化成功')
      getSettings()
    }
  } catch (error) {
    message.error('默认设置初始化失败')
  }
}

// 页面加载时获取系统设置
onMounted(() => {
  getSettings()
  // 初始化默认设置（仅在第一次使用时调用）
  // initSettings()
});
</script>

<style scoped>
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f2d3d;
  margin: 0;
}

:deep(.ant-card-head-title) {
  font-size: 16px;
  font-weight: 600;
}
</style>