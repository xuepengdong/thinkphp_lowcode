<template>
  <div class="page-management">
    <div class="content-container">
      <!-- 右侧内容 -->
      <div class="right-content">
        <div class="search-bar" style="margin-bottom: 16px">
          <Form layout="inline" :model="searchForm" @submit.prevent="handleSearch">
            <Form.Item label="页面ID:">
              <Input v-model:value="searchForm.pageId" placeholder="请输入页面ID" />
            </Form.Item>
            <Form.Item label="页面名称:">
              <Input v-model:value="searchForm.pageName" placeholder="请输入页面名称" />
            </Form.Item>
            <Form.Item label="表名:">
              <Input v-model:value="searchForm.tableName" placeholder="请输入表名" />
            </Form.Item>
            <Form.Item label="页面类型:">
              <Select v-model:value="searchForm.pageType" placeholder="请选择页面类型">
                <Select.Option value="">全部</Select.Option>
                <Select.Option value="列表">列表</Select.Option>
                <Select.Option value="添加页面">添加页面</Select.Option>
                <Select.Option value="修改页面">修改页面</Select.Option>
                <Select.Option value="弹出层页面">弹出层页面</Select.Option>
              </Select>
            </Form.Item>
            <Form.Item>
              <Button type="primary" html-type="submit">
                <SearchOutlined /> 搜索
              </Button>
              <Button style="margin-left: 8px" @click="resetSearch">
                重置
              </Button>
              <Button type="primary" style="margin-left: 8px" @click="openCreateModal">
                + 创建页面
              </Button>
            </Form.Item>
          </Form>
        </div>

        <div class="table-container">
          <Table
            :columns="columns"
            :data-source="pages"
            :loading="loading"
            :pagination="pagination"
            @change="handleTableChange"
            row-key="id"
          >
            <template #bodyCell="{ record, column }">
              <template v-if="column.key === 'actions'">
                <Dropdown>
                  <Button size="small" type="primary">
                    操作 <DownOutlined />
                  </Button>
                  <template #overlay>
                    <Menu>
                      <Menu.Item @click="handleCopy(record)">
                        复制
                      </Menu.Item>
                      <Menu.Item @click="handleDelete(record)" danger>
                        删除
                      </Menu.Item>
                      <Menu.Item @click="handleRefresh(record)">
                        刷新
                      </Menu.Item>
                      <Menu.Item @click="handleEdit(record)">
                        修改
                      </Menu.Item>
                      <Menu.Divider />
                      <Menu.Item @click="handlePageSetting(record)">
                        页面设置
                      </Menu.Item>
                      <Menu.Item @click="handleSearchSetting(record)">
                        搜索条件设置
                      </Menu.Item>
                      <Menu.Item @click="handleFilterSetting(record)">
                        列表过滤设置
                      </Menu.Item>
                      <Menu.Item @click="handlePermissionSetting(record)">
                        列表权限设置
                      </Menu.Item>
                      <Menu.Item @click="handleButtonSetting(record)">
                        按钮按按钮设置
                      </Menu.Item>
                      <Menu.Item @click="handleListButtonSetting(record)">
                        列表按钮设置
                      </Menu.Item>
                      <Menu.Item @click="handlePCSetting(record)">
                        设置PC展现形式
                      </Menu.Item>
                      <Menu.Item @click="handleH5Setting(record)">
                        设置H5展现形式
                      </Menu.Item>
                    </Menu>
                  </template>
                </Dropdown>
              </template>
            </template>
          </Table>
        </div>
      </div>
    </div>

    <!-- 复制页面弹窗 -->
    <Modal
      v-model:visible="copyModalVisible"
      title="复制页面"
      width="800px"
      @ok="handleCopyOk"
      @cancel="handleCopyCancel"
      okText="保存"
      cancelText="取消"
    >
      <Form :model="copyForm" :rules="copyRules" ref="copyFormRef">
        <Form.Item label="页面类型" name="pageType" required>
          <Select v-model:value="copyForm.pageType" placeholder="请选择页面类型" style="width: 200px">
            <Select.Option value="列表">列表</Select.Option>
            <Select.Option value="添加页面">添加页面</Select.Option>
            <Select.Option value="修改页面">修改页面</Select.Option>
            <Select.Option value="弹出层页面">弹出层页面</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item label="页面名称" name="pageName" required>
          <Input v-model:value="copyForm.pageName" placeholder="请输入页面名称" style="width: 300px" />
        </Form.Item>
        <Form.Item label="是否是接口" name="isApi" required>
          <Select v-model:value="copyForm.isApi" placeholder="请选择" style="width: 200px">
            <Select.Option :value="0">否</Select.Option>
            <Select.Option :value="1">是</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item label="是否生成栏目" name="isMenu" required>
          <Select v-model:value="copyForm.isMenu" placeholder="请选择" style="width: 200px">
            <Select.Option :value="0">否</Select.Option>
            <Select.Option :value="1">是</Select.Option>
          </Select>
        </Form.Item>
      </Form>
    </Modal>

    <!-- 修改页面弹窗 -->
    <Modal
      v-model:visible="editModalVisible"
      title="页面修改"
      width="800px"
      @ok="handleEditOk"
      @cancel="handleEditCancel"
      okText="保存"
      cancelText="取消"
    >
      <Form :model="editForm" :rules="editRules" ref="editFormRef">
        <Form.Item label="页面名称" name="pageName" required>
          <Input v-model:value="editForm.pageName" placeholder="请输入页面名称" style="width: 200px" />
        </Form.Item>
        <Form.Item label="页面备注" name="pageRemark">
          <Input v-model:value="editForm.pageRemark" placeholder="请输入页面备注" style="width: 300px" />
        </Form.Item>
        <Form.Item label="是否是接口" name="isApi" required>
          <Select v-model:value="editForm.isApi" placeholder="请选择" style="width: 200px">
            <Select.Option :value="0">否</Select.Option>
            <Select.Option :value="1">是</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item label="是否读写分离" name="isReadWriteSeparate" required>
          <Select v-model:value="editForm.isReadWriteSeparate" placeholder="请选择" style="width: 200px">
            <Select.Option :value="0">否</Select.Option>
            <Select.Option :value="1">是</Select.Option>
          </Select>
        </Form.Item>
      </Form>
    </Modal>

    <!-- 创建页面弹窗 -->
    <Modal
      v-model:visible="createModalVisible"
      title="创建页面"
      width="800px"
      @ok="handleCreateOk"
      @cancel="handleCreateCancel"
      okText="保存"
      cancelText="取消"
    >
      <Form :model="createForm" :rules="createRules" ref="createFormRef">
        <Form.Item label="页面类型" name="pageType" required>
          <Select v-model:value="createForm.pageType" placeholder="请选择页面类型" style="width: 200px">
            <Select.Option value="列表">列表</Select.Option>
            <Select.Option value="添加页面">添加页面</Select.Option>
            <Select.Option value="修改页面">修改页面</Select.Option>
            <Select.Option value="弹出层页面">弹出层页面</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item label="页面名称" name="pageName" required>
          <Input v-model:value="createForm.pageName" placeholder="请输入页面名称" style="width: 300px" />
        </Form.Item>
        <Form.Item label="关联对象" name="objectId" required>
          <Select v-model:value="createForm.objectId" placeholder="请选择关联对象" style="width: 300px">
            <Select.Option v-for="object in objects" :key="object.id" :value="object.id">{{ object.name }}</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item label="是否是接口" name="isApi" required>
          <Select v-model:value="createForm.isApi" placeholder="请选择" style="width: 200px">
            <Select.Option :value="0">否</Select.Option>
            <Select.Option :value="1">是</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item label="是否生成栏目" name="isMenu" required>
          <Select v-model:value="createForm.isMenu" placeholder="请选择" style="width: 200px">
            <Select.Option :value="0">否</Select.Option>
            <Select.Option :value="1">是</Select.Option>
          </Select>
        </Form.Item>
      </Form>
    </Modal>

    <!-- 页面设置弹窗 -->
    <Modal
      v-model:visible="pageSettingModalVisible"
      title="显示列设置"
      width="900px"
      @ok="handlePageSettingOk"
      @cancel="handlePageSettingCancel"
      okText="保存"
      cancelText="取消"
    >
      <Form :model="pageSettingForm" ref="pageSettingFormRef">
        <div class="setting-row">
          <Form.Item label="显示形式:">
            <Select v-model:value="pageSettingForm.displayForm" style="width: 200px">
              <Select.Option value="普通表格">普通表格</Select.Option>
              <Select.Option value="树形表格">树形表格</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="固定前几列:">
            <InputNumber v-model:value="pageSettingForm.fixedColumns" :min="0" style="width: 100px" />
          </Form.Item>
          <Form.Item label="是否默认展开:">
            <Select v-model:value="pageSettingForm.defaultExpand" style="width: 100px">
              <Select.Option value="是">是</Select.Option>
              <Select.Option value="否">否</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="是否默认加载:">
            <Select v-model:value="pageSettingForm.defaultLoad" style="width: 100px">
              <Select.Option value="是">是</Select.Option>
              <Select.Option value="否">否</Select.Option>
            </Select>
          </Form.Item>
        </div>

        <div class="field-section">
          <div class="field-header">
            <Button type="primary" size="small" @click="openFieldSelection">+ 打开字段选择区</Button>
            <Button size="small" @click="clearDisplayFields">清空显示字段</Button>
          </div>
          <div class="field-display-list">
            <div 
              v-for="(field, index) in pageSettingForm.displayFields" 
              :key="index" 
              class="field-display-item"
            >
              <span>{{ field.label }}</span>
              <a @click="removeDisplayField(index)">×</a>
            </div>
            <div v-if="pageSettingForm.displayFields.length === 0" class="empty-tip">
              暂无显示字段，请从下方字段选择区选择字段
            </div>
          </div>
        </div>

        <div class="field-section">
          <div class="field-header">
            <h4>字段选择</h4>
            <Button size="small" @click="refreshFields">刷新</Button>
          </div>
          <div class="field-tree-container">
            <a-tree
              v-model:checkedKeys="selectedFieldKeys"
              :tree-data="fieldTreeData"
              :checkable="true"
              @check="handleFieldCheck"
              :field-names="{ title: 'label', key: 'fieldId', children: 'children' }"
            />
          </div>
        </div>

        <div class="setting-row">
          <Form.Item label="是否统计记录数:">
            <Select v-model:value="pageSettingForm.countRecords" style="width: 100px">
              <Select.Option value="是">是</Select.Option>
              <Select.Option value="否">否</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="非空闲时段是否显示:">
            <Select v-model:value="pageSettingForm.showNonIdle" style="width: 100px">
              <Select.Option value="是">是</Select.Option>
              <Select.Option value="否">否</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="是否支持空闲时间段:">
            <Select v-model:value="pageSettingForm.supportIdleTime" style="width: 100px">
              <Select.Option value="否">否</Select.Option>
              <Select.Option value="是">是</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="显示条件是否走:">
            <Select v-model:value="pageSettingForm.showCondition" style="width: 100px">
              <Select.Option value="否">否</Select.Option>
              <Select.Option value="是">是</Select.Option>
            </Select>
          </Form.Item>
        </div>

        <div class="setting-row">
          <Form.Item label="sql:">
            <Input v-model:value="pageSettingForm.sql" style="width: 300px" />
          </Form.Item>
        </div>

        <div class="setting-section">
          <Button type="primary" @click="addRowColorDisplay">+ 添加行颜色显示</Button>
        </div>

        <div class="setting-row">
          <Form.Item label="左侧树页面:">
            <Input v-model:value="pageSettingForm.leftTreePage" style="width: 200px" />
          </Form.Item>
          <Form.Item label="显示块名称:">
            <Input v-model:value="pageSettingForm.blockName" style="width: 200px" />
          </Form.Item>
        </div>

        <div class="setting-row">
          <Form.Item label="页面:">
            <Select v-model:value="pageSettingForm.page" style="width: 200px">
              <Select.Option value="">--请选择--</Select.Option>
              <Select.Option v-for="page in pages" :key="page.id" :value="page.id">{{ page.pageName }}</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="关联对象:">
            <Input v-model:value="pageSettingForm.relatedObject" style="width: 200px" />
          </Form.Item>
        </div>

        <div class="setting-row">
          <Form.Item label="关联条件:">
            <Input v-model:value="pageSettingForm.relatedCondition" style="width: 200px" />
            <Button type="primary" size="small" style="margin-left: 8px">+</Button>
          </Form.Item>
        </div>

        <div class="setting-row">
          <Form.Item label="是否显示搜索条件:">
            <Select v-model:value="pageSettingForm.showSearchCondition" style="width: 100px">
              <Select.Option value="是">是</Select.Option>
              <Select.Option value="否">否</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item label="是否自定义关联条件:">
            <Select v-model:value="pageSettingForm.customRelatedCondition" style="width: 100px">
              <Select.Option value="否">否</Select.Option>
              <Select.Option value="是">是</Select.Option>
            </Select>
          </Form.Item>
        </div>

        <div class="setting-section">
          <Button type="primary" @click="addCondition">+ 启用搜索条件</Button>
        </div>

        <div class="setting-section">
          <Button type="primary" @click="addCondition">+ 添加</Button>
          <Button @click="deleteCondition">删除</Button>
          <Button @click="moveUpCondition">↑</Button>
          <Button @click="moveDownCondition">↓</Button>
        </div>
      </Form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, nextTick } from 'vue'
import { message, Button, Input, Select, Table, Form, Space, Dropdown, Menu, Modal, Tree } from 'ant-design-vue'
import { SearchOutlined, DownOutlined } from '@ant-design/icons-vue'
import request from '@/utils/request'

const searchForm = reactive({
  pageId: '',
  pageName: '',
  tableName: '',
  pageType: ''
})

const pages = ref([])
const loading = ref(false)
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  pageSizeOptions: ['10', '50', '100'],
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total) => `共 ${total} 条记录`
})

// 复制页面相关
const copyModalVisible = ref(false)
const copyForm = reactive({
  pageType: '列表',
  pageName: '',
  isApi: 0,
  isMenu: 0
})
const copyFormRef = ref()
const copyRules = reactive({
  pageType: [{ required: true, message: '请选择页面类型' }],
  pageName: [{ required: true, message: '请输入页面名称' }],
  isApi: [{ required: true, message: '请选择是否是接口' }],
  isMenu: [{ required: true, message: '请选择是否生成栏目' }]
})

// 创建页面相关
const createModalVisible = ref(false)
const createForm = reactive({
  pageType: '列表',
  pageName: '',
  objectId: '',
  isApi: 0,
  isMenu: 0
})
const createFormRef = ref()
const createRules = reactive({
  pageType: [{ required: true, message: '请选择页面类型' }],
  pageName: [{ required: true, message: '请输入页面名称' }],
  objectId: [{ required: true, message: '请选择关联对象' }],
  isApi: [{ required: true, message: '请选择是否是接口' }],
  isMenu: [{ required: true, message: '请选择是否生成栏目' }]
})

// 对象列表
const objects = ref([])

// 修改页面相关
const editModalVisible = ref(false)
const editForm = reactive({
  pageName: '',
  pageRemark: '',
  isApi: 0,
  isReadWriteSeparate: 0
})
const editFormRef = ref()
const editRules = reactive({
  pageName: [{ required: true, message: '请输入页面名称' }],
  isApi: [{ required: true, message: '请选择是否是接口' }],
  isReadWriteSeparate: [{ required: true, message: '请选择是否读写分离' }]
})

// 页面设置相关
const pageSettingModalVisible = ref(false)
const pageSettingForm = reactive({
  displayForm: '普通表格',
  fixedColumns: 0,
  defaultExpand: '是',
  defaultLoad: '是',
  displayFields: [],
  countRecords: '是',
  showNonIdle: '是',
  supportIdleTime: '否',
  showCondition: '否',
  sql: '',
  leftTreePage: '',
  blockName: '',
  page: '',
  relatedObject: '',
  relatedCondition: '',
  showSearchCondition: '是',
  customRelatedCondition: '否'
})
const pageSettingFormRef = ref()

// 字段树数据
const fieldTreeData = ref([])
const selectedFieldKeys = ref([])

// 可用字段列表
const availableFields = ref([])

// 显示字段表格列配置
const displayFieldsColumns = [
  {
    title: '字段名称',
    dataIndex: 'label',
    key: 'label',
    width: 200
  },
  {
    title: '操作',
    key: 'actions',
    width: 100,
    fixed: 'right'
  }
]

// 字段选择表格列配置
const fieldTableColumns = [
  {
    title: '选择',
    key: 'select',
    width: 60,
    fixed: 'left'
  },
  {
    title: '字段名称',
    dataIndex: 'fieldName',
    key: 'fieldName',
    ellipsis: true
  },
  {
    title: '类型',
    dataIndex: 'type',
    key: 'type',
    width: 100,
    customRender: (text) => {
      return text === 'current' ? '当前对象' : '外键对象'
    }
  },
  {
    title: '操作',
    key: 'actions',
    width: 80,
    fixed: 'right',
    scopedSlots: {
      customRender: 'action'
    }
  }
]

// 字段显示表格引用
const fieldDisplayTable = ref(null)

// 根据页面关联对象获取字段列表
const getObjectFields = async (objectId) => {
  try {
    console.log('Getting fields for object ID:', objectId)
    const response = await request.get(`/api/object-field/list/${objectId}`)
    console.log('Object fields response:', response)
    if (response.code === 200) {
      availableFields.value = response.data
      buildFieldTreeData(response.data)
    }
  } catch (error) {
    console.error('获取对象字段失败:', error)
  }
}

// 构建字段树数据
const buildFieldTreeData = (fields) => {
  const treeData = []
  
  // 当前对象的字段
  const currentFields = fields.filter(field => !field.is_foreign_key)
  currentFields.forEach(field => {
    treeData.push({
      fieldId: field.id,
      label: field.field_name_zh || field.field_name_en || field.name,
      fieldName: field.field_name_en || field.name,
      type: 'current',
      foreignField: null
    })
  })
  
  // 外键对象的字段
  const foreignKeyFields = fields.filter(field => field.is_foreign_key)
  foreignKeyFields.forEach(field => {
    const foreignFieldItem = {
      fieldId: field.id,
      label: field.field_name_zh || field.field_name_en || field.name,
      fieldName: field.field_name_en || field.name,
      type: 'foreign',
      foreignField: field.field_name_en || field.name,
      children: []
    }
    
    if (field.foreign_fields) {
      field.foreign_fields.forEach(foreignField => {
        foreignFieldItem.children.push({
          fieldId: foreignField.id,
          label: foreignField.field_name_zh || foreignField.field_name_en || foreignField.name,
          fieldName: foreignField.field_name_en || foreignField.name,
          type: 'foreign',
          foreignField: field.field_name_en || field.name
        })
      })
    }
    
    treeData.push(foreignFieldItem)
  })
  
  fieldTreeData.value = treeData
}

// 当前操作的页面记录
const currentRecord = ref(null)

// 获取对象列表
const getObjects = async () => {
  try {
    const response = await request.get('/api/object/list')
    console.log('Object list response:', response)
    if (response.code === 200) {
      objects.value = response.data
      console.log('Objects list:', objects.value)
    }
  } catch (error) {
    console.error('获取对象列表失败:', error)
  }
}

const columns = [
  { title: '#', dataIndex: 'id', key: 'id', width: 60 },
  { title: '操作', key: 'actions', width: 120 },
  { title: '页面ID', dataIndex: 'pageId', key: 'pageId' },
  { title: '表ID', dataIndex: 'tableId', key: 'tableId' },
  { title: '表名', dataIndex: 'tableName', key: 'tableName' },
  { title: '页面名称', dataIndex: 'pageName', key: 'pageName' },
  { title: '页面备注', dataIndex: 'pageRemark', key: 'pageRemark' },
  { title: '区域', dataIndex: 'area', key: 'area' },
  { title: '页面类型', dataIndex: 'pageType', key: 'pageType' },
  { title: '来源页面ID', dataIndex: 'sourcePageId', key: 'sourcePageId' }
]

const getPages = async () => {
  loading.value = true
  try {
    // 确保获取对象列表
    await getObjects()
    
    const params = {
      page: pagination.current,
      limit: pagination.pageSize,
      search: searchForm.pageName || searchForm.pageId || searchForm.tableName
    }
    const response = await request.get('/api/page/list', { params })
    if (response.code === 200) {
      pages.value = response.data.map(item => {
        // 根据 object_id 查找对象名称
        const object = objects.value.find(obj => obj.object_id === item.object_id)
        return {
          id: item.id,
          pageId: item.id,
          tableId: 0, // 这里需要根据实际情况从对象表中获取
          tableName: object ? object.name_en : item.object_id,
          objectId: item.object_id, // 保存对象的 object_id
          pageName: item.name,
          pageRemark: '',
          area: 'center',
          pageType: item.page_type,
          sourcePageId: ''
        }
      })
      pagination.total = response.total
    }
  } catch (error) {
    message.error('获取页面列表失败')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  pagination.current = 1
  getPages()
}

const resetSearch = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  pagination.current = 1
  getPages()
}

const handleTableChange = (paginationObj) => {
  pagination.current = paginationObj.current
  pagination.pageSize = paginationObj.pageSize
  getPages()
}

// 复制页面
const handleCopy = (record) => {
  currentRecord.value = record
  copyForm.pageType = record.pageType
  copyForm.pageName = record.pageName + '（复制）'
  copyForm.isApi = 0
  copyForm.isMenu = 0
  copyModalVisible.value = true
}

// 复制页面确认
const handleCopyOk = async () => {
  if (copyFormRef.value) {
    try {
      await copyFormRef.value.validate()
      // 这里可以添加实际的复制逻辑
      message.success('复制页面成功')
      copyModalVisible.value = false
      getPages()
    } catch (error) {
      console.error(error)
    }
  }
}

// 复制页面取消
const handleCopyCancel = () => {
  copyModalVisible.value = false
}

// 删除页面
const handleDelete = (record) => {
  Modal.confirm({
    title: '确认删除',
    content: `确定要删除页面 "${record.pageName}" 吗？<br/><br/><span style="color: #ff4d4f; font-size: 14px;">删除后将无法恢复，请谨慎操作！</span>`,
    okType: 'danger',
    okText: '确认删除',
    cancelText: '取消',
    onOk: async () => {
      try {
        const response = await request.delete(`/api/page/delete/${record.id}`)
        if (response.code === 200) {
          message.success('删除页面成功')
          getPages()
        } else {
          message.error(response.message || '删除页面失败')
        }
      } catch (error) {
        console.error('删除页面失败:', error)
        message.error('删除页面失败')
      }
    }
  })
}

// 刷新页面
const handleRefresh = (record) => {
  message.success('刷新页面成功')
  // 这里可以添加实际的刷新逻辑
}

// 修改页面
const handleEdit = (record) => {
  currentRecord.value = record
  editForm.pageName = record.pageName
  editForm.pageRemark = record.pageRemark || ''
  editForm.isApi = 0
  editForm.isReadWriteSeparate = 0
  editModalVisible.value = true
}

// 修改页面确认
const handleEditOk = async () => {
  if (editFormRef.value) {
    try {
      await editFormRef.value.validate()
      // 这里可以添加实际的修改逻辑
      message.success('修改页面成功')
      editModalVisible.value = false
      getPages()
    } catch (error) {
      console.error(error)
    }
  }
}

// 修改页面取消
const handleEditCancel = () => {
  editModalVisible.value = false
}

// 页面设置
const handlePageSetting = (record) => {
  currentRecord.value = record
  // 根据页面关联的对象获取字段列表
  if (record.objectId) {
    getObjectFields(record.objectId)
  }
  pageSettingModalVisible.value = true
}

// 页面设置确认
const handlePageSettingOk = async () => {
  try {
    // 构建保存数据
    const saveData = {
      page_id: currentRecord.value.id,
      object_id: currentRecord.value.objectId,
      display_fields: pageSettingForm.displayFields,
      display_form: pageSettingForm.displayForm,
      fixedColumns: pageSettingForm.fixedColumns,
      defaultExpand: pageSettingForm.defaultExpand,
      defaultLoad: pageSettingForm.defaultLoad,
      countRecords: pageSettingForm.countRecords,
      showNonIdle: pageSettingForm.showNonIdle,
      supportIdleTime: pageSettingForm.supportIdleTime,
      showCondition: pageSettingForm.showCondition,
      sql: pageSettingForm.sql,
      leftTreePage: pageSettingForm.leftTreePage,
      blockName: pageSettingForm.blockName,
      page: pageSettingForm.page,
      relatedObject: pageSettingForm.relatedObject,
      relatedCondition: pageSettingForm.relatedCondition,
      showSearchCondition: pageSettingForm.showSearchCondition,
      customRelatedCondition: pageSettingForm.customRelatedCondition
    }
    
    // 发送保存请求
    const response = await request.post('/api/page/setting', saveData)
    if (response.code === 200) {
      message.success('页面设置保存成功')
      pageSettingModalVisible.value = false
    } else {
      message.error('页面设置保存失败')
    }
  } catch (error) {
    console.error('页面设置保存失败:', error)
    message.error('页面设置保存失败')
  }
}

// 页面设置取消
const handlePageSettingCancel = () => {
  pageSettingModalVisible.value = false
}

// 打开字段选择区
const openFieldSelection = () => {
  // 这里可以添加实际的打开字段选择区逻辑
  message.info('打开字段选择区')
}

// 处理字段选择
const handleFieldCheck = (checkedKeys, info) => {
  const selectedFields = []
  info.checkedNodes.forEach(node => {
    if (node.fieldId) {
      selectedFields.push({
        fieldId: node.fieldId,
        fieldName: node.fieldName,
        label: node.label,
        type: node.type,
        foreignField: node.foreignField
      })
    }
  })
  pageSettingForm.displayFields = selectedFields
}

// 移除显示字段
const removeDisplayField = (index) => {
  pageSettingForm.displayFields.splice(index, 1)
}

// 清空显示字段
const clearDisplayFields = () => {
  pageSettingForm.displayFields = []
  selectedFieldKeys.value = []
}

// 刷新字段
const refreshFields = () => {
  if (currentRecord.value && currentRecord.value.objectId) {
    getObjectFields(currentRecord.value.objectId)
  }
}

// 添加行颜色显示
const addRowColorDisplay = () => {
  // 这里可以添加实际的添加行颜色显示逻辑
  message.info('添加行颜色显示')
}

// 添加条件
const addCondition = () => {
  // 这里可以添加实际的添加条件逻辑
  message.info('添加条件')
}

// 删除条件
const deleteCondition = () => {
  // 这里可以添加实际的删除条件逻辑
  message.info('删除条件')
}

// 上移条件
const moveUpCondition = () => {
  // 这里可以添加实际的上移条件逻辑
  message.info('上移条件')
}

// 下移条件
const moveDownCondition = () => {
  // 这里可以添加实际的下移条件逻辑
  message.info('下移条件')
}

// 打开创建页面弹窗
const openCreateModal = () => {
  getObjects()
  createModalVisible.value = true
}

// 创建页面确认
const handleCreateOk = async () => {
  if (createFormRef.value) {
    try {
      await createFormRef.value.validate()
      // 这里可以添加实际的创建页面逻辑
      message.success('创建页面成功')
      createModalVisible.value = false
      getPages()
    } catch (error) {
      console.error(error)
    }
  }
}

// 创建页面取消
const handleCreateCancel = () => {
  createModalVisible.value = false
}

// 搜索条件设置
const handleSearchSetting = (record) => {
  message.info('搜索条件设置功能开发中')
  // 这里可以添加实际的搜索条件设置逻辑
}

// 列表过滤设置
const handleFilterSetting = (record) => {
  message.info('列表过滤设置功能开发中')
  // 这里可以添加实际的列表过滤设置逻辑
}

// 列表权限设置
const handlePermissionSetting = (record) => {
  message.info('列表权限设置功能开发中')
  // 这里可以添加实际的列表权限设置逻辑
}

// 按钮按按钮设置
const handleButtonSetting = (record) => {
  message.info('按钮按按钮设置功能开发中')
  // 这里可以添加实际的按钮按按钮设置逻辑
}

// 列表按钮设置
const handleListButtonSetting = (record) => {
  message.info('列表按钮设置功能开发中')
  // 这里可以添加实际的列表按钮设置逻辑
}

// 设置PC展现形式
const handlePCSetting = (record) => {
  message.info('设置PC展现形式功能开发中')
  // 这里可以添加实际的设置PC展现形式逻辑
}

// 设置H5展现形式
const handleH5Setting = (record) => {
  message.info('设置H5展现形式功能开发中')
  // 这里可以添加实际的设置H5展现形式逻辑
}

onMounted(() => {
  getPages()
  buildTreeData()
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.page-header h1 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2d3d;
  margin: 0;
}

.content-container {
  display: flex;
  gap: 20px;
}

.left-sidebar {
  width: 300px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
  padding: 16px;
  height: calc(100vh - 180px);
  overflow-y: auto;
}

.tree-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f0f0f0;
}

.tree-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2d3d;
  margin: 0;
}

.tree-container {
  height: calc(100% - 40px);
  overflow-y: auto;
}

.right-content {
  flex: 1;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
  padding: 16px;
  min-height: calc(100vh - 180px);
}

.table-container {
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
  padding: 16px;
}
/* 页面设置弹窗样式 */
.setting-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 16px;
  align-items: center;
}

.setting-section {
  margin: 16px 0;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 4px;
}

.field-section {
  margin: 16px 0;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 4px;
}

.field-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.field-header h4 {
  margin: 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.field-display-list {
  min-height: 60px;
  max-height: 200px;
  overflow-y: auto;
  padding: 8px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
}

.field-display-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 12px;
  margin-bottom: 4px;
  background: #f0f0f0;
  border-radius: 4px;
  font-size: 14px;
}

.field-display-item:last-child {
  margin-bottom: 0;
}

.field-display-item a {
  color: #ff4d4f;
  cursor: pointer;
  margin-left: 8px;
  font-weight: bold;
}

.field-display-list {
  min-height: 60px;
  max-height: 200px;
  overflow-y: auto;
  padding: 8px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
}

.field-display-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 12px;
  margin-bottom: 4px;
  background: #f0f0f0;
  border-radius: 4px;
  font-size: 14px;
}

.field-display-item:last-child {
  margin-bottom: 0;
}

.field-display-item a {
  color: #ff4d4f;
  cursor: pointer;
  margin-left: 8px;
  font-weight: bold;
}

.empty-tip {
  text-align: center;
  color: #999;
  padding: 20px;
  font-size: 14px;
}

.field-tree-container {
  max-height: 300px;
  overflow-y: auto;
  padding: 12px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
}

.field-table-container {
  max-height: 300px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
}

.field-display {
  margin: 16px 0;
}

.field-display h4 {
  margin-bottom: 8px;
  color: #333;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 8px;
  margin: 8px 0;
}

.field-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 8px;
  background: #f0f0f0;
  border-radius: 4px;
  font-size: 14px;
}

.field-item a {
  color: #ff4d4f;
  cursor: pointer;
  margin-left: 8px;
}

.field-selection {
  margin: 16px 0;
}

.field-selection h4 {
  margin-bottom: 8px;
  color: #333;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .setting-row {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .field-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>