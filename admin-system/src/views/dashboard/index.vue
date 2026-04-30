<style scoped>
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f2d3d;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #606266;
  margin: 0;
}

.stats-cards {
  margin-bottom: 24px;
}

.stat-card {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  padding: 20px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  font-size: 24px;
  color: #fff;
}

.bg-blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-green {
  background: linear-gradient(135deg, #68df88 0%, #56b674 100%);
}

.bg-orange {
  background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
}

.bg-purple {
  background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
}

.stat-content h3 {
  margin: 0 0 4px 0;
  font-size: 22px;
  font-weight: 600;
  color: #1f2d3d;
}

.stat-content p {
  margin: 0;
  font-size: 14px;
  color: #909399;
}

.chart-placeholder {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fafafa;
  border-radius: 4px;
}

.quick-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.action-item {
  text-align: center;
  padding: 16px 8px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
}

.action-item:hover {
  background: #f5f5f5;
}

.action-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f0f7ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px;
  color: #1890ff;
  font-size: 18px;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
}

.activity-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #1890ff;
  margin: 8px 12px 0 0;
}

.activity-content h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  color: #303133;
}

.activity-content p {
  margin: 0 0 4px 0;
  font-size: 12px;
  color: #606266;
}

.time {
  font-size: 12px;
  color: #909399;
}

@media (max-width: 768px) {
  .stat-card {
    padding: 16px;
  }
  
  .stat-icon {
    width: 48px;
    height: 48px;
    margin-right: 12px;
  }
  
  .stat-content h3 {
    font-size: 18px;
  }
  
  .quick-actions {
    grid-template-columns: 1fr;
  }
}
</style><template>
  <div>
    <div class="page-header">
      <h2 class="page-title">仪表盘</h2>
      <p class="page-subtitle">欢迎使用后台管理系统</p>
    </div>
    
    <div class="stats-cards">
      <a-row :gutter="[24, 24]">
        <a-col :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <div class="stat-icon bg-blue">
              <UserOutlined />
            </div>
            <div class="stat-content">
              <h3>10,245</h3>
              <p>总用户数</p>
            </div>
          </a-card>
        </a-col>
        
        <a-col :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <div class="stat-icon bg-green">
              <ShopOutlined />
            </div>
            <div class="stat-content">
              <h3>2,560</h3>
              <p>订单量</p>
            </div>
          </a-card>
        </a-col>
        
        <a-col :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <div class="stat-icon bg-orange">
              <DollarOutlined />
            </div>
            <div class="stat-content">
              <h3>¥124,560</h3>
              <p>总收入</p>
            </div>
          </a-card>
        </a-col>
        
        <a-col :xs="24" :sm="12" :md="8" :lg="6">
          <a-card class="stat-card">
            <div class="stat-icon bg-purple">
              <RiseOutlined />
            </div>
            <div class="stat-content">
              <h3>98%</h3>
              <p>增长率</p>
            </div>
          </a-card>
        </a-col>
      </a-row>
    </div>
    
    <div class="content-section">
      <a-row :gutter="[24, 24]">
        <a-col :xs="24" :lg="16">
          <a-card title="数据图表" class="chart-card">
            <div class="chart-placeholder">
              <p>这里将显示数据图表</p>
            </div>
          </a-card>
        </a-col>
        
        <a-col :xs="24" :lg="8">
          <a-card title="快速操作" class="quick-actions-card">
            <div class="quick-actions">
              <div class="action-item">
                <div class="action-icon">
                  <PlusOutlined />
                </div>
                <div class="action-text">添加用户</div>
              </div>
              <div class="action-item">
                <div class="action-icon">
                  <EditOutlined />
                </div>
                <div class="action-text">编辑内容</div>
              </div>
              <div class="action-item">
                <div class="action-icon">
                  <SettingOutlined />
                </div>
                <div class="action-text">系统设置</div>
              </div>
              <div class="action-item">
                <div class="action-icon">
                  <ExportOutlined />
                </div>
                <div class="action-text">导出数据</div>
              </div>
            </div>
          </a-card>
          
          <a-card title="最近活动" class="recent-activity-card">
            <div class="activity-list">
              <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                  <h4>新用户注册</h4>
                  <p>张三刚刚注册了账户</p>
                  <span class="time">2分钟前</span>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                  <h4>订单支付成功</h4>
                  <p>订单#12345已支付成功</p>
                  <span class="time">1小时前</span>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-dot"></div>
                <div class="activity-content">
                  <h4>系统更新</h4>
                  <p>系统已更新至最新版本</p>
                  <span class="time">昨天</span>
                </div>
              </div>
            </div>
          </a-card>
        </a-col>
      </a-row>
    </div>
  </div>
</template>

<script setup>
import { UserOutlined, ShopOutlined, DollarOutlined, RiseOutlined, PlusOutlined, EditOutlined, SettingOutlined, ExportOutlined } from '@ant-design/icons-vue'
import { Row as ARow, Col as ACol, Card } from 'ant-design-vue'
</script>