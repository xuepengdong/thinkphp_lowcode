<template>
  <div class="overview-container">
    <div class="page-header">
      <h2 class="page-title">概览</h2>
      <p class="page-subtitle">系统运行状态总览</p>
    </div>
    
    <a-row :gutter="[24, 24]">
      <a-col :xs="24" :sm="12" :md="8" :lg="6">
        <a-card class="stat-card">
          <div class="stat-content">
            <h3>运行中</h3>
            <p>系统状态</p>
          </div>
        </a-card>
      </a-col>
      
      <a-col :xs="24" :sm="12" :md="8" :lg="6">
        <a-card class="stat-card">
          <div class="stat-content">
            <h3>99.9%</h3>
            <p>可用性</p>
          </div>
        </a-card>
      </a-col>
      
      <a-col :xs="24" :sm="12" :md="8" :lg="6">
        <a-card class="stat-card">
          <div class="stat-content">
            <h3>128MB</h3>
            <p>内存使用</p>
          </div>
        </a-card>
      </a-col>
      
      <a-col :xs="24" :sm="12" :md="8" :lg="6">
        <a-card class="stat-card">
          <div class="stat-content">
            <h3>2.1GB</h3>
            <p>磁盘使用</p>
          </div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
</script>

<style scoped>
.overview-container {
  width: 100%;
}

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

.stat-card {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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
</style>