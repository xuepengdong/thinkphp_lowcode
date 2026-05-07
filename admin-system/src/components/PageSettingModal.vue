<template>
  <Modal
    :visible="visible"
    title="显示列设置"
    width="900px"
    @ok="handleOk"
    @cancel="handleCancel"
    @update:visible="handleModalClose"
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

      <!-- 显示字段表格 -->
      <div class="field-section">
        <div class="field-header">
          <Button type="primary" size="small" @click="openFieldSelectionModal">+ 打开字段选择区</Button>
          <Button size="small" @click="clearDisplayFields">清空显示字段</Button>
        </div>
        <div class="field-grid-container">
          <table class="field-grid-table">
            <thead>
              <tr>
                <th>显示字段</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rowIndex) in displayFieldRows" :key="rowIndex">
                <td 
                  v-for="(field, colIndex) in row" 
                  :key="colIndex || 'empty'" 
                  class="field-cell"
                  @dragover.prevent="handleCellDragOver(rowIndex, colIndex, $event)"
                  @dragleave="handleCellDragLeave"
                  @drop="handleCellDrop(rowIndex, colIndex, $event)"
                  :class="{ 'drop-cell': isDropTarget && dropPosition.rowIndex === rowIndex && dropPosition.colIndex === colIndex }"
                >
                  <template v-if="field">
                    <span
                      class="field-tag"
                      draggable="true"
                      @dragstart="handleDisplayFieldDragStart($event, field, getFieldIndex(rowIndex, colIndex))"
                    >{{ field.label }}<span class="field-tag-close" @click.stop="removeDisplayField(getFieldIndex(rowIndex, colIndex))">×</span></span>
                  </template>
                  <template v-else>
                    <span class="empty-cell-hint">空</span>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 字段选择表格 -->
      <div class="field-section">
        <div class="field-header">
          <h4>字段选择</h4>
          <Button size="small" @click="refreshFields">刷新</Button>
        </div>
        <div class="field-grid-container">
          <table class="field-grid-table field-selection-grid">
            <thead>
              <tr>
                <th>字段选择</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rowIndex) in availableFieldRows" :key="rowIndex">
                <td v-for="(field, colIndex) in row" :key="colIndex || 'empty'" class="field-cell">
                  <template v-if="field">
                    <div 
                      class="field-draggable" 
                      :class="{ 'dragging': draggingField?.id === field.id }"
                      draggable="true"
                      @dragstart="handleDragStart($event, field)"
                      @dragend="handleDragEnd"
                    >
                      <label class="field-checkbox" :class="{ 'checked': isFieldSelected(field.id) }">
                        <input type="checkbox" :checked="isFieldSelected(field.id)" @change="toggleFieldSelection(field)" />
                        <span>{{ field.field_name_zh || field.field_name_en }}</span>
                      </label>
                    </div>
                  </template>
                </td>
              </tr>
              <tr v-if="availableFields.length === 0">
                <td colspan="5" class="empty-cell">暂无可用字段</td>
              </tr>
            </tbody>
          </table>
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
    </Form>
  </Modal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { message, Button, Input, Select, Form, InputNumber, Modal } from 'ant-design-vue'
import request from '@/utils/request'

const props = defineProps({
  visible: Boolean,
  pageId: String,
  objectId: String
})

const emit = defineEmits(['update:visible', 'saved'])

const pages = ref([])

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
const availableFields = ref([])

// 拖拽相关状态
const draggingField = ref(null)
const isDropTarget = ref(false)
const dropPosition = reactive({ rowIndex: 0, colIndex: 0 })

// 获取页面列表
const getPages = async () => {
  try {
    const response = await request.get('/api/page/list')
    if (response.code === 200) {
      pages.value = response.data
    }
  } catch (error) {
    console.error('获取页面列表失败:', error)
  }
}

// 根据对象ID获取字段列表
const getObjectFields = async (objectId) => {
  if (!objectId) return
  
  try {
    const response = await request.get(`/api/object-field/list/${objectId}`)
    if (response.code === 200) {
      // 过滤掉已在显示字段中的字段
      const displayFieldIds = pageSettingForm.displayFields.map(f => f.fieldId)
      availableFields.value = response.data.filter(field => !displayFieldIds.includes(field.id))
    } else {
      availableFields.value = []
    }
  } catch (error) {
    console.error('获取字段列表失败:', error)
    availableFields.value = []
  }
}

// 刷新字段
const refreshFields = async () => {
  if (props.objectId) {
    await getObjectFields(props.objectId)
  }
}

// 将显示字段数组转换为5列的二维数组
const displayFieldRows = computed(() => {
  const rows = []
  const fields = pageSettingForm.displayFields
  for (let i = 0; i < 6; i++) {
    const row = []
    for (let j = 0; j < 5; j++) {
      const index = i * 5 + j
      row.push(fields[index] || null)
    }
    rows.push(row)
  }
  return rows
})

// 将可用字段数组转换为5列的二维数组
const availableFieldRows = computed(() => {
  const rows = []
  const fields = availableFields.value
  const totalRows = Math.ceil(fields.length / 5)
  for (let i = 0; i < Math.max(totalRows, 1); i++) {
    const row = []
    for (let j = 0; j < 5; j++) {
      const index = i * 5 + j
      row.push(fields[index] || null)
    }
    rows.push(row)
  }
  return rows
})

// 获取字段在一维数组中的索引
const getFieldIndex = (rowIndex, colIndex) => {
  return rowIndex * 5 + colIndex
}

// 检查字段是否被选中
const isFieldSelected = (fieldId) => {
  return pageSettingForm.displayFields.some(f => f.fieldId === fieldId)
}

// 切换字段选择
const toggleFieldSelection = (field) => {
  const index = pageSettingForm.displayFields.findIndex(f => f.fieldId === field.id)
  if (index > -1) {
    // 移除
    pageSettingForm.displayFields.splice(index, 1)
    availableFields.value.push(field)
  } else {
    // 添加
    if (pageSettingForm.displayFields.length >= 30) {
      message.warning('最多只能选择30个字段')
      return
    }
    pageSettingForm.displayFields.push({
      fieldId: field.id,
      fieldName: field.field_name_en,
      label: field.field_name_zh || field.field_name_en,
      sort: pageSettingForm.displayFields.length + 1
    })
    availableFields.value = availableFields.value.filter(f => f.id !== field.id)
  }
}

// 拖拽开始
const handleDragStart = (event, field) => {
  draggingField.value = field
  event.dataTransfer.effectAllowed = 'move'
}

// 拖拽结束
const handleDragEnd = () => {
  draggingField.value = null
  isDropTarget.value = false
}

// 显示字段拖拽开始
const handleDisplayFieldDragStart = (event, field, index) => {
  draggingField.value = { ...field, originalIndex: index }
  event.dataTransfer.effectAllowed = 'move'
}

// 单元格拖拽经过
const handleCellDragOver = (rowIndex, colIndex, event) => {
  isDropTarget.value = true
  dropPosition.rowIndex = rowIndex
  dropPosition.colIndex = colIndex
}

// 单元格拖拽离开
const handleCellDragLeave = () => {
  isDropTarget.value = false
}

// 单元格放置
const handleCellDrop = (rowIndex, colIndex, event) => {
  isDropTarget.value = false
  
  if (!draggingField.value) return
  
  const targetIndex = getFieldIndex(rowIndex, colIndex)
  
  if (draggingField.value.originalIndex !== undefined) {
    // 从显示字段拖到显示字段
    const originalIndex = draggingField.value.originalIndex
    const field = pageSettingForm.displayFields.splice(originalIndex, 1)[0]
    pageSettingForm.displayFields.splice(targetIndex, 0, field)
  } else {
    // 从可用字段拖到显示字段
    if (pageSettingForm.displayFields.length >= 30) {
      message.warning('最多只能选择30个字段')
      return
    }
    
    const field = draggingField.value
    pageSettingForm.displayFields.splice(targetIndex, 0, {
      fieldId: field.id,
      fieldName: field.field_name_en,
      label: field.field_name_zh || field.field_name_en,
      sort: pageSettingForm.displayFields.length + 1
    })
    availableFields.value = availableFields.value.filter(f => f.id !== field.id)
  }
  
  draggingField.value = null
}

// 移除显示字段
const removeDisplayField = (index) => {
  const removedField = pageSettingForm.displayFields.splice(index, 1)[0]
  if (removedField && removedField.fieldId !== 'id') {
    // 查找原始字段信息
    const originalField = {
      id: removedField.fieldId,
      field_name_en: removedField.fieldName,
      field_name_zh: removedField.label
    }
    availableFields.value.push(originalField)
  }
}

// 清空显示字段
const clearDisplayFields = () => {
  // 保留ID字段
  const idField = pageSettingForm.displayFields.find(f => f.fieldId === 'id')
  pageSettingForm.displayFields = idField ? [idField] : []
  
  // 将移除的字段放回可用字段列表
  const removedFields = pageSettingForm.displayFields.filter(f => f.fieldId !== 'id')
  removedFields.forEach(field => {
    const originalField = {
      id: field.fieldId,
      field_name_en: field.fieldName,
      field_name_zh: field.label
    }
    availableFields.value.push(originalField)
  })
}

// 字段选择弹窗
const fieldSelectionModalVisible = ref(false)

const openFieldSelectionModal = () => {
  fieldSelectionModalVisible.value = true
}

// 添加行颜色显示
const addRowColorDisplay = () => {
  message.info('行颜色显示功能开发中')
}

// 保存设置
const handleOk = async () => {
  try {
    const data = {
      id: props.pageId,
      ...pageSettingForm
    }
    
    const response = await request.put('/api/page/update', data)
    if (response.code === 200) {
      message.success('保存成功')
      emit('saved')
      emit('update:visible', false)
    } else {
      message.error('保存失败')
    }
  } catch (error) {
    console.error('保存失败:', error)
    message.error('保存失败')
  }
}

// 取消
const handleCancel = () => {
  emit('update:visible', false)
}

// 弹窗关闭处理
const handleModalClose = (val) => {
  emit('update:visible', val)
}

// 监听visible变化，初始化表单
watch(() => props.visible, async (val) => {
  if (val && props.pageId) {
    // 获取页面信息
    try {
      const response = await request.get(`/api/page/info/${props.pageId}`)
      if (response.code === 200) {
        const page = response.data
        pageSettingForm.displayForm = page.displayForm || '普通表格'
        pageSettingForm.fixedColumns = page.fixedColumns || 0
        pageSettingForm.defaultExpand = page.defaultExpand || '是'
        pageSettingForm.defaultLoad = page.defaultLoad || '是'
        pageSettingForm.displayFields = page.displayFields ? JSON.parse(page.displayFields) : []
        pageSettingForm.countRecords = page.countRecords || '是'
        pageSettingForm.showNonIdle = page.showNonIdle || '是'
        pageSettingForm.supportIdleTime = page.supportIdleTime || '否'
        pageSettingForm.showCondition = page.showCondition || '否'
        pageSettingForm.sql = page.sql || ''
        pageSettingForm.leftTreePage = page.leftTreePage || ''
        pageSettingForm.blockName = page.blockName || ''
        pageSettingForm.page = page.page || ''
        pageSettingForm.relatedObject = page.relatedObject || ''
        pageSettingForm.relatedCondition = page.relatedCondition || ''
        pageSettingForm.showSearchCondition = page.showSearchCondition || '是'
        pageSettingForm.customRelatedCondition = page.customRelatedCondition || '否'
        
        // 默认添加ID字段
        const hasIdField = pageSettingForm.displayFields.some(f => f.fieldName === 'id' || f.fieldId === 'id')
        if (!hasIdField) {
          pageSettingForm.displayFields.unshift({
            fieldId: 'id',
            fieldName: 'id',
            label: 'ID',
            sort: 1
          })
        }
      }
    } catch (error) {
      console.error('获取页面信息失败:', error)
    }
    
    // 获取字段列表
    await getObjectFields(props.objectId)
  }
})

// 初始化
getPages()
</script>

<style scoped>
.setting-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 16px;
  align-items: center;
}

.setting-section {
  margin-bottom: 16px;
}

.field-section {
  margin-bottom: 16px;
  border: 1px solid #e8e8e8;
  border-radius: 4px;
  padding: 12px;
}

.field-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.field-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 500;
}

.field-grid-container {
  overflow-x: auto;
}

.field-grid-table {
  width: 100%;
  border-collapse: collapse;
}

.field-grid-table th,
.field-grid-table td {
  border: 1px solid #e8e8e8;
  padding: 8px;
  text-align: left;
}

.field-grid-table th {
  background: #fafafa;
  font-weight: 500;
  font-size: 13px;
}

.field-cell {
  min-height: 40px;
  vertical-align: middle;
}

.field-tag {
  display: inline-flex;
  align-items: center;
  background: #1890ff;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  cursor: move;
}

.field-tag-close {
  margin-left: 8px;
  cursor: pointer;
  opacity: 0.8;
}

.field-tag-close:hover {
  opacity: 1;
}

.empty-cell-hint {
  color: #ccc;
  font-size: 12px;
}

.drop-cell {
  background: #e6f7ff;
  border-color: #1890ff;
}

.field-selection-grid .field-cell {
  padding: 4px;
}

.field-draggable {
  cursor: move;
  padding: 4px;
}

.field-draggable.dragging {
  opacity: 0.5;
}

.field-checkbox {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
}

.field-checkbox:hover {
  background: #f5f5f5;
}

.field-checkbox.checked {
  background: #e6f7ff;
  color: #1890ff;
}

.field-checkbox input {
  margin-right: 6px;
}

.empty-cell {
  text-align: center;
  color: #ccc;
  padding: 20px;
}
</style>