<template>
  <div class="object-list-container">
    <div class="list-header">
      <h2>{{ pageTitle }}</h2>
    </div>
    
    <div class="search-bar" v-if="showSearch">
      <Form layout="inline" :model="searchForm">
        <Form.Item label="搜索">
          <Input v-model:value="searchForm.keyword" placeholder="请输入搜索关键字" @pressEnter="handleSearch" />
        </Form.Item>
        <Form.Item>
          <Button type="primary" @click="handleSearch">
            <SearchOutlined /> 搜索
          </Button>
        </Form.Item>
      </Form>
    </div>

    <div class="table-container">
      <Table
        :columns="columns"
        :data-source="data"
        :loading="loading"
        :pagination="paginationConfig"
        @change="handleTableChange"
        row-key="id"
      >
        <template #bodyCell="{ record, column }">
          <template v-if="column.key === 'actions'">
            <Button size="small" type="primary" @click="handleView(record)">
              查看
            </Button>
          </template>
        </template>
      </Table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Table, Button, Input, Form, message } from 'ant-design-vue'
import { SearchOutlined } from '@ant-design/icons-vue'
import request from '@/utils/request'

const route = useRoute()
const objectId = ref('')

const pageTitle = ref('列表页面')
const columns = ref([])
const data = ref([])
const loading = ref(false)
const showSearch = ref(true)

const searchForm = reactive({
  keyword: ''
})

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total) => `共 ${total} 条记录`
})

const paginationConfig = computed(() => ({
  current: pagination.current,
  pageSize: pagination.pageSize,
  total: pagination.total,
  showSizeChanger: pagination.showSizeChanger,
  showQuickJumper: pagination.showQuickJumper,
  showTotal: pagination.showTotal
}))

const getPageSetting = async (id) => {
  try {
    const response = await request.get('/api/page/setting', {
      params: { object_id: id }
    })
    if (response.code === 200) {
      const setting = response.data
      pageTitle.value = setting.page_name || '列表页面'
      
      if (setting.display_fields && setting.display_fields.length > 0) {
        columns.value = setting.display_fields.map(field => ({
          title: field.label,
          dataIndex: field.fieldName,
          key: field.fieldName
        }))
      } else {
        columns.value = []
      }
      
      columns.value.push({
        title: '操作',
        key: 'actions',
        width: 150,
        fixed: 'right'
      })
    }
  } catch (error) {
    console.error('获取页面设置失败:', error)
    message.error('获取页面设置失败')
  }
}

const getListData = async (id) => {
  loading.value = true
  try {
    const response = await request.get('/api/object/data', {
      params: {
        object_id: id,
        page: pagination.current,
        page_size: pagination.pageSize
      }
    })
    if (response.code === 200) {
      data.value = response.data.list || []
      pagination.total = response.data.total || 0
    } else {
      message.error(response.message || '获取列表数据失败')
    }
  } catch (error) {
    console.error('获取列表数据失败:', error)
    message.error('获取列表数据失败')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  pagination.current = 1
  getListData(objectId.value)
}

const handleTableChange = (pag) => {
  pagination.current = pag.current
  pagination.pageSize = pag.pageSize
  getListData(objectId.value)
}

const handleView = (record) => {
  message.info('查看功能开发中')
}

onMounted(() => {
  objectId.value = route.params.objectId
  if (objectId.value) {
    getPageSetting(objectId.value)
    getListData(objectId.value)
  }
})
</script>

<style scoped>
.object-list-container {
  padding: 24px;
  background: #f0f2f5;
  min-height: 100vh;
}

.list-header {
  margin-bottom: 24px;
  padding: 16px 24px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
}

.list-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #1f2d3d;
}

.search-bar {
  margin-bottom: 16px;
  padding: 16px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
}

.table-container {
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
  padding: 16px;
}
</style>
