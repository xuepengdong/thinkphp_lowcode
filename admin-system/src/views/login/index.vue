<style scoped>
.login-container {
  display: flex;
  height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

.login-bg {
  flex: 1;
  background-image: url('@/assets/hero.png');
  background-size: cover;
  background-position: center;
  opacity: 0.8;
}

.login-form {
  width: 100%;
  max-width: 520px;
  padding: 48px 40px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  margin: auto;
  position: relative;
  z-index: 1;
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.logo-area {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.logo-img {
  height: 48px;
  margin-right: 12px;
}

.system-name {
  margin: 0;
  font-size: 22px;
  font-weight: 600;
  color: #1f2d3d;
}

.welcome-text {
  margin: 0;
  font-size: 16px;
  color: #606266;
  font-weight: normal;
}

.login-form-forgot {
  float: right;
}

.login-btn {
  margin-top: 16px;
  height: 44px;
  font-size: 16px;
  border-radius: 6px;
}

.login-footer {
  text-align: center;
  margin-top: 24px;
  color: #606266;
  font-size: 14px;
}

.login-footer a {
  margin-left: 8px;
  color: #1890ff;
  text-decoration: none;
}

@media (max-width: 768px) {
  .login-form {
    width: 90%;
    max-width: 380px;
  }
  
  .login-container {
    flex-direction: column;
  }
  
  .login-bg {
    height: 200px;
    width: 100%;
  }
}
</style><template>
  <div class="login-container">
    <div class="login-form">
      <div class="login-header">
        <div class="logo-area">
          <img src="@/assets/vite.svg" alt="Logo" class="logo-img" />
          <h2 class="system-name">管理系统</h2>
        </div>
        <h3 class="welcome-text">欢迎回来</h3>
      </div>
      
      <a-form
        :model="form"
        layout="vertical"
      >
        <a-form-item label="用户名">
          <a-input 
            v-model:value="form.username" 
            placeholder="请输入用户名" 
            size="large"
            @press-enter="login"
          >
            <template #prefix>
              <UserOutlined style="color: rgba(0, 0, 0, 0.25)" />
            </template>
          </a-input>
        </a-form-item>
        
        <a-form-item label="密码">
          <a-input-password 
            v-model:value="form.password" 
            placeholder="请输入密码" 
            size="large"
            @press-enter="login"
          >
            <template #prefix>
              <LockOutlined style="color: rgba(0, 0, 0, 0.25)" />
            </template>
          </a-input-password>
        </a-form-item>
        
        <a-form-item>
          <a-checkbox v-model:checked="rememberMe">记住我</a-checkbox>
          <a class="login-form-forgot" href="">忘记密码？</a>
        </a-form-item>
        
        <a-button
          type="primary"
          html-type="submit"
          block
          size="large"
          @click="login"
          :loading="loading"
          class="login-btn"
        >
          登录
        </a-button>
      </a-form>
      

    </div>
    
    <div class="login-bg"></div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import request from '@/utils/request'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/store/user'
import { UserOutlined, LockOutlined } from '@ant-design/icons-vue'

const router = useRouter()
const userStore = useUserStore()
const loading = ref(false)
const rememberMe = ref(false)

const form = reactive({
  username: '',
  password: ''
})

// 页面加载时读取记住的登录信息
onMounted(() => {
  const savedUser = localStorage.getItem('remember_user')
  const savedPwd = localStorage.getItem('remember_password')
  
  if (savedUser && savedPwd) {
    form.username = savedUser
    form.password = savedPwd
    rememberMe.value = true
  } else {
    // 默认显示admin
    form.username = 'admin'
    form.password = '123456'
  }
})

const login = async () => {
  if (!form.username || !form.password) {
    alert('请输入用户名和密码')
    return
  }
  
  loading.value = true
  try {
    const res = await request.post('/api/auth/login', form)
    
    if (res.code === 0) {
      // 存储token
      localStorage.setItem('access_token', res.token)
      
      // 存储用户信息
      userStore.setUserInfo(res.user)
      
      // 如果勾选了"记住我"，保存用户名和密码
      if (rememberMe.value) {
        localStorage.setItem('remember_user', form.username)
        localStorage.setItem('remember_password', form.password)
      } else {
        // 清除之前保存的登录信息
        localStorage.removeItem('remember_user')
        localStorage.removeItem('remember_password')
      }
      
      // 跳转到首页
      router.push('/')
    } else {
      alert(res.message || '登录失败')
    }
  } catch (error) {
    console.error('登录失败:', error)
    alert('登录失败，请检查网络连接')
  } finally {
    loading.value = false
  }
}
</script>