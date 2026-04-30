<template>
  <div class="analytics-container">
    <div class="page-header">
      <h2 class="page-title">数据分析</h2>
      <p class="page-subtitle">数据统计与分析报表</p>
    </div>
    
    <a-row :gutter="[24, 24]">
      <a-col :xs="24" :lg="12">
        <a-card title="访问趋势">
          <div class="chart-area">
            <p>访问量趋势图表区域</p>
          </div>
        </a-card>
      </a-col>
      
      <a-col :xs="24" :lg="12">
        <a-card title="用户分布">
          <div class="chart-area">
            <p>用户分布图表区域</p>
          </div>
        </a-card>
      </a-col>
      
      <a-col :xs="24" :lg="12">
        <a-card title="销售数据">
          <div class="chart-area">
            <p>销售数据图表区域</p>
          </div>
        </a-card>
      </a-col>
      
      <a-col :xs="24" :lg="12">
        <a-card title="转化率">
          <div class="chart-area">
            <p>转化率图表区域</p>
          </div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
</script>

<style scoped>
.analytics-container {
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

.chart-area {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fafafa;
  border-radius: 4px;
  color: #909399;
}
</style>