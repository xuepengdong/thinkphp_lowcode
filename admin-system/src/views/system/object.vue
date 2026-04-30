<template>
  <div class="object-management">
    <div class="search-bar">
      <a-form layout="inline" :model="searchForm" @submit.prevent="handleSearch">
        <a-form-item label="对象Id:">
          <a-input v-model:value="searchForm.objectId" placeholder="请输入对象Id" />
        </a-form-item>
        <a-form-item label="对象名称:">
          <a-input v-model:value="searchForm.name" placeholder="请输入对象名称" />
        </a-form-item>
        <a-form-item label="对象中文名称:">
          <a-input v-model:value="searchForm.nameZh" placeholder="请输入对象中文名称" />
        </a-form-item>
        <a-form-item label="对象类型:">
          <a-select v-model:value="searchForm.type" placeholder="请选择对象类型">
            <a-select-option value="normal">普通</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="对象备注:">
          <a-input v-model:value="searchForm.remark" placeholder="请输入对象备注" />
        </a-form-item>
        <a-form-item>
          <a-button type="primary" html-type="submit">
            <a-icon type="search" /> 搜索
          </a-button>
          <a-button style="margin-left: 8px" @click="resetSearch">
            重置
          </a-button>
        </a-form-item>
        <a-form-item>
          <a-button type="primary" @click="handleAdd" style="margin-left: 8px">
            <a-icon type="plus" /> 新增对象
          </a-button>
        </a-form-item>
      </a-form>
    </div>

    <div class="table-container">
      <a-table
        :columns="columns"
        :data-source="objects"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
      >
        <template #headerCell="{ column }">
          <template v-if="column.key === 'selection'">
            <a-checkbox v-model:checked="allSelected" @change="handleSelectAll" />
          </template>
        </template>
        <template #bodyCell="{ record, column }">
          <template v-if="column.key === 'selection'">
            <a-checkbox :checked="selectedRowKeys.includes(record.id)" @change="(e) => handleSelect(record.id, e.target.checked)" />
          </template>
          <template v-else-if="column.key === 'actions'">
            <a-dropdown>
              <a-button size="small">
                操作 <a-icon type="down" />
              </a-button>
              <template #overlay>
                <a-menu>
                  <a-menu-item key="edit" @click="handleEdit(record)">
                    <a-icon type="edit" /> 修改
                  </a-menu-item>
                  <a-menu-item key="delete" @click="handleDelete(record)">
                    <a-icon type="delete" /> 删除
                  </a-menu-item>
                  <a-menu-item key="clear-data" @click="handleClearData(record)">
                    <a-icon type="delete" /> 清除数据
                  </a-menu-item>
                  <a-menu-item key="generate-page" @click="handleGeneratePage(record)">
                    <a-icon type="file-add" /> 生成页面
                  </a-menu-item>
                  <a-menu-item key="custom-trigger-setting" @click="handleCustomTriggerSetting(record)">
                    <a-icon type="edit" /> 自定义触发器设置
                  </a-menu-item>
                  <a-menu-item key="copy-object" @click="handleCopyObject(record)">
                    <a-icon type="copy" /> 复制对象
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>
        </template>
      </a-table>
    </div>

    <!-- 对象编辑弹窗 -->
    <a-modal
      v-model:visible="modalVisible"
      :title="modalTitle"
      width="1000px"
      @ok="handleModalOk"
      @cancel="handleModalCancel"
    >
      <a-form :model="formData" :rules="formRules" ref="formRef">
        <a-form-item label="对象Id" name="object_id" required>
          <a-input v-model:value="formData.object_id" placeholder="自动生成" readonly />
        </a-form-item>
        <a-form-item label="对象名称（英文）" name="name_en" required>
          <a-input v-model:value="formData.name_en" placeholder="请输入对象名称（英文）" />
        </a-form-item>
        <a-form-item label="对象名称（中文）" name="name_zh" required>
          <a-input v-model:value="formData.name_zh" placeholder="请输入对象名称（中文）" />
        </a-form-item>
        <a-form-item label="对象类型" name="type" required>
          <a-select v-model:value="formData.type" placeholder="请选择对象类型">
            <a-select-option value="normal">普通</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="是否支持父子级" name="is_parent">
          <a-select v-model:value="formData.is_parent" placeholder="请选择">
            <a-select-option :value="0">否</a-select-option>
            <a-select-option :value="1">是</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="所属项目" name="project" required>
          <a-select v-model:value="formData.project" placeholder="请选择所属项目">
            <a-select-option value="main">主项目</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="对象备注" name="remark">
          <a-textarea v-model:value="formData.remark" placeholder="请输入对象备注" />
        </a-form-item>

        <a-form-item label="属性内容类型">
          <a-table
            :columns="fieldColumns"
            :data-source="formData.fields"
            row-key="id"
          >
            <template #bodyCell="{ record, column }">
              <template v-if="column.key === 'field_name_zh'">
                <a-input v-model:value="record.field_name_zh" placeholder="请输入属性名称（汉语）" style="width: 100%" />
              </template>
              <template v-else-if="column.key === 'field_name_en'">
                <a-input v-model:value="record.field_name_en" placeholder="请输入属性名称（英文）" style="width: 100%" />
              </template>
              <template v-else-if="column.key === 'field_type'">
                <a-select v-model:value="record.field_type" placeholder="请选择字段类型" style="width: 100%">
                  <a-select-option v-for="type in fieldTypes" :key="type.value" :value="type.value">
                    {{ type.label }}
                  </a-select-option>
                </a-select>
              </template>
              <template v-else-if="column.key === 'is_unique'">
                <a-select v-model:value="record.is_unique" placeholder="请选择" style="width: 100%">
                  <a-select-option :value="0">否</a-select-option>
                  <a-select-option :value="1">是</a-select-option>
                </a-select>
              </template>
              <template v-else-if="column.key === 'remark'">
                <a-input v-model:value="record.remark" placeholder="请输入备注" style="width: 100%" />
              </template>
              <template v-else-if="column.key === 'actions'">
                <a-popconfirm
                  title="确定要删除这个字段吗？"
                  @confirm="handleDeleteField(record.id)"
                  ok-text="确定"
                  cancel-text="取消"
                >
                  <a-button size="small" danger type="text">
                    <a-icon type="delete" /> 删除
                  </a-button>
                </a-popconfirm>
                <a-button size="small" type="text" style="margin-left: 8px" @click="handleClearField(record.id)">
                  <a-icon type="reload" /> 清除校验
                </a-button>
              </template>
            </template>
          </a-table>
          <a-button type="dashed" style="width: 100%; margin-top: 16px" @click="handleAddField">
            <a-icon type="plus" /> 添加字段
          </a-button>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 生成页面弹窗 -->
    <a-modal
      v-model:visible="generateModalVisible"
      title="生成页面"
      width="800px"
      @ok="handleGenerateOk"
      @cancel="handleGenerateCancel"
      okText="保存"
      cancelText="取消"
    >
      <a-form :model="generateForm" :rules="generateRules" ref="generateFormRef" layout="inline" style="width: 100%">
        <a-form-item label="生成页面" name="page_type" required style="width: 200px">
          <a-select v-model:value="generateForm.page_type" placeholder="请选择" style="width: 100%">
            <a-select-option value="list">列表</a-select-option>
            <a-select-option value="add">添加</a-select-option>
            <a-select-option value="batch_add">批量添加</a-select-option>
            <a-select-option value="edit">修改</a-select-option>
            <a-select-option value="batch_edit">批量修改</a-select-option>
            <a-select-option value="view">查看</a-select-option>
            <a-select-option value="modal">弹窗</a-select-option>
            <a-select-option value="tree">下拉树</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="是否是接口" name="is_api" required style="width: 200px">
          <a-select v-model:value="generateForm.is_api" placeholder="请选择" style="width: 100%">
            <a-select-option :value="0">否</a-select-option>
            <a-select-option :value="1">是</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="是否生成栏目" name="is_menu" required style="width: 200px">
          <a-select v-model:value="generateForm.is_menu" placeholder="请选择" style="width: 100%">
            <a-select-option :value="0">否</a-select-option>
            <a-select-option :value="1">是</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="名称" name="name" required style="width: 300px">
          <a-input v-model:value="generateForm.name" placeholder="请输入页面名称" style="width: 100%" />
        </a-form-item>
        <a-form-item label="所属栏目" name="menu_id" required style="width: 400px">
          <div style="display: flex; align-items: center; width: 100%">
            <a-tree-select
              v-model:value="generateForm.menu_id"
              :tree-data="menuTree"
              placeholder="请选择栏目"
              style="flex: 1; margin-right: 8px"
              :tree-default-expand-all="true"
            />
            <a-button type="primary" icon="plus">添加</a-button>
          </div>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 自定义触发器设置弹窗 -->
    <a-modal
      v-model:visible="triggerModalVisible"
      title="自定义触发器设置"
      width="1000px"
      @cancel="handleTriggerCancel"
      okText="关闭"
      cancelText="取消"
    >
      <div style="margin-bottom: 16px">
        <a-button type="primary" @click="handleAddTrigger">
          <a-icon type="plus" /> 添加触发器
        </a-button>
      </div>
      <a-table
        :columns="triggerColumns"
        :data-source="triggers"
        row-key="id"
        :loading="triggerLoading"
      >
        <template #bodyCell="{ record, column }">
          <template v-if="column.key === 'action'">
            <a-button size="small" type="primary" style="margin-right: 8px" @click="handleEditTrigger(record)">
              <a-icon type="edit" /> 编辑
            </a-button>
            <a-popconfirm
              title="确定要删除该触发器吗？"
              @confirm="handleDeleteTrigger(record.id)"
              ok-text="确定"
              cancel-text="取消"
            >
              <a-button size="small" danger type="text">
                <a-icon type="delete" /> 删除
              </a-button>
            </a-popconfirm>
          </template>
        </template>
      </a-table>

      <!-- 触发器编辑弹窗 -->
      <a-modal
        v-model:visible="triggerEditModalVisible"
        :title="triggerEditTitle"
        width="800px"
        @ok="handleTriggerEditOk"
        @cancel="handleTriggerEditCancel"
        okText="保存"
        cancelText="取消"
      >
        <a-form :model="triggerForm" :rules="triggerRules" ref="triggerFormRef">
          <a-form-item label="触发器名称" name="name" required>
            <a-input v-model:value="triggerForm.name" placeholder="请输入触发器名称" />
          </a-form-item>
          <a-form-item label="描述" name="description">
            <a-textarea v-model:value="triggerForm.description" placeholder="请输入触发器描述" />
          </a-form-item>
          <a-form-item label="触发事件" name="event" required>
            <a-select v-model:value="triggerForm.event" placeholder="请选择触发事件">
              <a-select-option v-for="event in triggerEvents" :key="event.value" :value="event.value">
                {{ event.label }}
              </a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="触发条件" name="condition">
            <a-textarea v-model:value="triggerForm.condition" placeholder="请输入触发条件" />
          </a-form-item>
          <a-form-item label="触发动作" name="action" required>
            <a-textarea v-model:value="triggerForm.action" placeholder="请输入触发动作" rows="4" />
          </a-form-item>
          <a-form-item label="生成测试SQL">
            <a-button @click="generateTestSQL" type="primary" style="margin-right: 8px">
              生成测试SQL
            </a-button>
            <a-button @click="clearAction">
              清空
            </a-button>
          </a-form-item>
          <a-form-item label="状态" name="status">
            <a-select v-model:value="triggerForm.status" placeholder="请选择状态">
              <a-select-option value="active">启用</a-select-option>
              <a-select-option value="inactive">禁用</a-select-option>
            </a-select>
          </a-form-item>
        </a-form>
      </a-modal>
    </a-modal>


  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, computed, onMounted } from 'vue'
import { message, Modal as a_modal } from 'ant-design-vue'
import request from '@/utils/request'

export default defineComponent({
  name: 'ObjectManagement',
  setup() {
    const searchForm = reactive({
      objectId: '',
      name: '',
      nameZh: '',
      type: '',
      remark: ''
    })

    const objects = ref([])
    const loading = ref(false)
    const pagination = reactive({
      current: 1,
      pageSize: 10,
      total: 0
    })

    const selectedRowKeys = ref([])
    const allSelected = ref(false)

    const modalVisible = ref(false)
    const modalTitle = ref('新增对象')
    const formData = reactive({
      id: null,
      object_id: '',
      name_en: '',
      name_zh: '',
      type: 'normal',
      is_parent: 0,
      project: 'main',
      remark: '',
      fields: []
    })

    const formRef = ref()
    const formRules = reactive({
      object_id: [{ required: true, message: '请输入对象Id' }],
      name_en: [{ required: true, message: '请输入对象名称（英文）' }],
      name_zh: [{ required: true, message: '请输入对象名称（中文）' }],
      type: [{ required: true, message: '请选择对象类型' }],
      project: [{ required: true, message: '请选择所属项目' }]
    })

    const fieldTypes = ref([])
    const fieldColumns = [
      { title: '属性名称（汉语）', dataIndex: 'field_name_zh', key: 'field_name_zh', width: 200 },
      { title: '属性名称（英文）', dataIndex: 'field_name_en', key: 'field_name_en', width: 200 },
      { title: '属性内容类型', dataIndex: 'field_type', key: 'field_type', width: 150 },
      { title: '是否唯一', dataIndex: 'is_unique', key: 'is_unique', width: 100, render: (text) => (text ? '是' : '否') },
      { title: '备注', dataIndex: 'remark', key: 'remark', width: 200 },
      { title: '操作', key: 'actions', width: 120 }
    ]

    const generateModalVisible = ref(false)
    const generateForm = reactive({
      object_id: '',
      page_type: 'list',
      is_api: 0,
      is_menu: 1,
      menu_id: '',
      name: ''
    })

    const generateFormRef = ref()
    const generateRules = reactive({
      page_type: [{ required: true, message: '请选择生成页面类型' }],
      is_api: [{ required: true, message: '请选择是否是接口' }],
      is_menu: [{ required: true, message: '请选择是否生成栏目' }],
      name: [{ required: true, message: '请输入页面名称' }]
    })

    const menuTree = ref([])

    // 自定义触发器相关
    const triggerModalVisible = ref(false)
    const triggerEditModalVisible = ref(false)
    const triggerEditTitle = ref('添加触发器')
    const triggers = ref([])
    const triggerLoading = ref(false)
    const triggerForm = reactive({
      id: null,
      object_id: '',
      name: '',
      description: '',
      event: '',
      condition: '',
      action: '',
      status: 'active'
    })
    const triggerFormRef = ref()
    const triggerRules = reactive({
      name: [{ required: true, message: '请输入触发器名称' }],
      event: [{ required: true, message: '请选择触发事件' }],
      action: [{ required: true, message: '请输入触发动作' }]
    })
    const triggerEvents = ref([])
    const currentObjectId = ref('')

    const triggerColumns = [
      {
        title: 'ID',
        dataIndex: 'id',
        key: 'id'
      },
      {
        title: '触发器名称',
        dataIndex: 'name',
        key: 'name'
      },
      {
        title: '描述',
        dataIndex: 'description',
        key: 'description'
      },
      {
        title: '触发事件',
        dataIndex: 'event',
        key: 'event',
        customRender: ({ text }) => {
          const eventMap = {
            'create': '创建',
            'update': '更新',
            'delete': '删除'
          }
          return eventMap[text] || text
        }
      },
      {
        title: '状态',
        dataIndex: 'status',
        key: 'status',
        customRender: ({ text }) => {
          return text === 'active' ? '启用' : '禁用'
        }
      },
      {
        title: '创建时间',
        dataIndex: 'created_at',
        key: 'created_at'
      },
      {
        title: '操作',
        key: 'action',
        width: 150
      }
    ]

    const columns = [
      { title: '#', dataIndex: 'id', key: 'id', width: 60 },
      { title: '选择', key: 'selection', width: 60 },
      { title: '操作', key: 'actions', width: 120 },
      { title: '表ID', dataIndex: 'id', key: 'id' },
      { title: '表名', dataIndex: 'name_en', key: 'name_en' },
      { title: '中文名称', dataIndex: 'name_zh', key: 'name_zh' },
      { title: '是否父子级', dataIndex: 'is_parent', key: 'is_parent', render: (text) => (text ? '是' : '否') },
      { title: '对象备注', dataIndex: 'remark', key: 'remark' }
    ]

    // 获取对象列表
    const getObjects = async () => {
      loading.value = true
      try {
        const response = await request.get('/api/object/list', {
          params: {
            page: pagination.current,
            limit: pagination.pageSize,
            search: searchForm.name || searchForm.nameZh || searchForm.objectId
          }
        })
        if (response.code === 200) {
          objects.value = response.data
          pagination.total = response.total
        }
      } catch (error) {
        message.error('获取对象列表失败')
      } finally {
        loading.value = false
      }
    }

    // 获取字段类型
    const getFieldTypes = async () => {
      try {
        const response = await request.get('/api/object-field/types')
        if (response.code === 200) {
          fieldTypes.value = response.data
        }
      } catch (error) {
        message.error('获取字段类型失败')
      }
    }

    // 转换菜单数据为树选择组件需要的格式
    const convertMenuData = (menuData) => {
      return menuData.map(menu => {
        const convertedMenu = {
          value: menu.id,
          title: menu.name,
          key: menu.id
        }
        if (menu.children && menu.children.length > 0) {
          convertedMenu.children = convertMenuData(menu.children)
        }
        return convertedMenu
      })
    }

    // 获取菜单树
    const getMenuTree = async () => {
      try {
        const response = await request.get('/api/menu/list')
        if (response.code === 200) {
          // 转换数据格式
          menuTree.value = convertMenuData(response.data)
        }
      } catch (error) {
        message.error('获取菜单树失败')
      }
    }

    // 搜索
    const handleSearch = () => {
      pagination.current = 1
      getObjects()
    }

    // 重置搜索
    const resetSearch = () => {
      Object.keys(searchForm).forEach(key => {
        searchForm[key] = ''
      })
      pagination.current = 1
      getObjects()
    }

    // 表格变化
    const handleTableChange = (paginationObj) => {
      pagination.current = paginationObj.current
      pagination.pageSize = paginationObj.pageSize
      getObjects()
    }

    // 全选
    const handleSelectAll = (e) => {
      if (e.target.checked) {
        selectedRowKeys.value = objects.value.map(item => item.id)
      } else {
        selectedRowKeys.value = []
      }
    }

    // 选择
    const handleSelect = (id, checked) => {
      if (checked) {
        selectedRowKeys.value.push(id)
      } else {
        selectedRowKeys.value = selectedRowKeys.value.filter(item => item !== id)
      }
    }

    // 新增对象
    const handleAdd = () => {
      modalTitle.value = '新增对象'
      formData.id = null
      // 自动生成对象ID
      formData.object_id = 'OBJ_' + Date.now()
      formData.name_en = ''
      formData.name_zh = ''
      formData.type = 'normal'
      formData.is_parent = 0
      formData.project = 'main'
      formData.remark = ''
      formData.fields = []
      modalVisible.value = true
    }

    // 编辑对象
    const handleEdit = async (record) => {
      modalTitle.value = '修改对象'
      formData.id = record.id
      formData.object_id = record.object_id
      formData.name_en = record.name_en
      formData.name_zh = record.name_zh
      formData.type = record.type
      formData.is_parent = record.is_parent
      formData.project = record.project
      formData.remark = record.remark

      // 获取对象字段
      try {
        const response = await request.get(`/api/object/get/${record.id}`)
        if (response.code === 200) {
          formData.fields = response.data.fields || []
          // 为每个字段确保有唯一ID
          formData.fields.forEach(field => {
            if (!field.id) {
              field.id = Date.now() + Math.floor(Math.random() * 1000)
            }
          })
        }
      } catch (error) {
        message.error('获取对象字段失败')
      }

      modalVisible.value = true
    }

    // 保存对象
    const handleModalOk = async () => {
      if (formRef.value) {
        try {
          await formRef.value.validate()
          
          const data = {
            ...formData,
            fields: formData.fields
          }

          let response
          if (formData.id) {
            response = await request.put(`/api/object/update/${formData.id}`, data)
          } else {
            response = await request.post('/api/object/create', data)
          }

          if (response.code === 200) {
            message.success(formData.id ? '修改成功' : '创建成功')
            modalVisible.value = false
            getObjects()
          }
        } catch (error) {
          console.error(error)
        }
      }
    }

    // 取消弹窗
    const handleModalCancel = () => {
      modalVisible.value = false
    }

    // 添加字段
    const handleAddField = () => {
      // 生成唯一ID
      const uniqueId = Date.now() + Math.floor(Math.random() * 1000)
      formData.fields.push({
        id: uniqueId,
        field_name_zh: '',
        field_name_en: '',
        field_type: 'text',
        is_unique: 0,
        remark: ''
      })
    }

    // 删除字段
    const handleDeleteField = (id) => {
      formData.fields = formData.fields.filter(item => item.id !== id)
    }

    // 清除字段校验
    const handleClearField = (id) => {
      // 这里可以添加清除字段校验的逻辑
      message.info('清除校验成功')
    }

    // 生成页面
    const handleGeneratePage = (record) => {
      generateForm.object_id = record.object_id
      generateForm.name = record.name_zh
      generateModalVisible.value = true
    }

    // 保存生成页面
    const handleGenerateOk = async () => {
      if (generateFormRef.value) {
        try {
          await generateFormRef.value.validate()
          
          // 处理菜单ID，确保是整数
          let menuId = generateForm.menu_id
          if (typeof menuId === 'string' && menuId.includes('-')) {
            // 如果是字符串，取最后一个ID
            menuId = menuId.split('-').pop()
          }
          
          const data = {
            ...generateForm,
            menu_id: parseInt(menuId, 10)
          }

          const response = await request.post('/api/object/generate-page', data)
          if (response.code === 200) {
            message.success('页面生成成功')
            generateModalVisible.value = false
          }
        } catch (error) {
          console.error(error)
          message.error('生成页面失败，请检查数据格式')
        }
      }
    }

    // 取消生成页面
    const handleGenerateCancel = () => {
      generateModalVisible.value = false
    }

    // 删除对象
    const handleDelete = (record) => {
      a_modal.confirm({
        title: '确认删除',
        content: `确定要删除对象 <strong>${record.name_zh}</strong> 吗？<br/><br/><span style="color: #ff4d4f; font-size: 14px;">删除后将无法恢复，请谨慎操作！</span>`,
        okType: 'danger',
        okText: '确认删除',
        cancelText: '取消',
        onOk: async () => {
          try {
            const response = await request.delete(`/api/object/delete/${record.id}`)
            if (response.code === 200) {
              message.success('删除成功')
              getObjects()
            }
          } catch (error) {
            message.error('删除失败')
          }
        }
      })
    }

    // 清除数据
    const handleClearData = (record) => {
      a_modal.confirm({
        title: '确认清除数据',
        content: `确定要清除对象 <strong>${record.name_zh}</strong> 的所有数据吗？<br/><br/><span style="color: #ff4d4f; font-size: 14px;">清除后数据将无法恢复，请谨慎操作！</span>`,
        okType: 'warning',
        okText: '确认清除',
        cancelText: '取消',
        onOk: async () => {
          try {
            const response = await request.post(`/api/object/clear-data`, {
              object_id: record.object_id
            })
            if (response.code === 200) {
              message.success('清除数据成功')
            }
          } catch (error) {
            message.error('清除数据失败')
          }
        }
      })
    }

    // 触发器设置
    const handleTriggerSetting = (record) => {
      message.info('触发器设置功能开发中')
    }

    // 中间键设置
    const handleMiddlewareSetting = (record) => {
      message.info('中间键设置功能开发中')
    }

    // 自定义触发器设置
    const handleCustomTriggerSetting = (record) => {
      currentObjectId.value = record.object_id
      triggerModalVisible.value = true
      getTriggers(record.object_id)
      getTriggerEvents()
    }

    // 获取触发器列表
    const getTriggers = async (objectId) => {
      triggerLoading.value = true
      try {
        const response = await request.get(`/api/object-trigger/list/${objectId}`)
        if (response.code === 200) {
          triggers.value = response.data
        }
      } catch (error) {
        message.error('获取触发器列表失败')
      } finally {
        triggerLoading.value = false
      }
    }

    // 获取触发器事件类型
    const getTriggerEvents = async () => {
      try {
        const response = await request.get('/api/object-trigger/events')
        if (response.code === 200) {
          triggerEvents.value = response.data
        }
      } catch (error) {
        message.error('获取触发器事件类型失败')
      }
    }

    // 添加触发器
    const handleAddTrigger = () => {
      triggerEditTitle.value = '添加触发器'
      triggerForm.id = null
      triggerForm.object_id = currentObjectId.value
      triggerForm.name = ''
      triggerForm.description = ''
      triggerForm.event = ''
      triggerForm.condition = ''
      triggerForm.action = ''
      triggerForm.status = 'active'
      triggerEditModalVisible.value = true
    }

    // 编辑触发器
    const handleEditTrigger = (record) => {
      triggerEditTitle.value = '编辑触发器'
      triggerForm.id = record.id
      triggerForm.object_id = record.object_id
      triggerForm.name = record.name
      triggerForm.description = record.description
      triggerForm.event = record.event
      triggerForm.condition = record.condition
      triggerForm.action = record.action
      triggerForm.status = record.status
      triggerEditModalVisible.value = true
    }

    // 删除触发器
    const handleDeleteTrigger = async (id) => {
      try {
        const response = await request.delete(`/api/object-trigger/delete/${id}`)
        if (response.code === 200) {
          message.success('删除触发器成功')
          getTriggers(currentObjectId.value)
        }
      } catch (error) {
        message.error('删除触发器失败')
      }
    }

    // 保存触发器
    const handleTriggerEditOk = async () => {
      try {
        await triggerFormRef.value.validate()
        let response
        if (triggerForm.id) {
          // 更新触发器
          response = await request.put(`/api/object-trigger/update/${triggerForm.id}`, triggerForm)
        } else {
          // 创建触发器
          response = await request.post('/api/object-trigger/create', triggerForm)
        }
        if (response.code === 200) {
          message.success(triggerForm.id ? '更新触发器成功' : '创建触发器成功')
          triggerEditModalVisible.value = false
          getTriggers(currentObjectId.value)
        }
      } catch (error) {
        console.error(error)
      }
    }

    // 关闭触发器设置弹窗
    const handleTriggerCancel = () => {
      triggerModalVisible.value = false
    }

    // 关闭触发器编辑弹窗
    const handleTriggerEditCancel = () => {
      triggerEditModalVisible.value = false
    }

    // 生成测试SQL
    const generateTestSQL = () => {
      const event = triggerForm.event
      let sql = ''
      
      // 根据触发事件生成不同的测试SQL
      switch (event) {
        case 'create':
          sql = `-- 插入测试数据
INSERT INTO \`${currentObjectId.value}\` (\`created_at\`, \`updated_at\`, \`status\`) 
VALUES (NOW(), NOW(), 1);`
          break
        case 'update':
          sql = `-- 更新测试数据
UPDATE \`${currentObjectId.value}\` 
SET \`updated_at\` = NOW(), \`status\` = 1 
WHERE \`id\` = 1;`
          break
        case 'delete':
          sql = `-- 删除测试数据
DELETE FROM \`${currentObjectId.value}\` 
WHERE \`id\` = 1;`
          break
        default:
          sql = '-- 请选择触发事件'
      }
      
      triggerForm.action = sql
    }

    // 清空触发动作
    const clearAction = () => {
      triggerForm.action = ''
    }

    // 生成常用值插件
    const handleGenerateCommonValues = (record) => {
      message.info('生成常用值插件功能开发中')
    }

    // 关联资源对象
    const handleRelatedResources = (record) => {
      message.info('关联资源对象功能开发中')
    }

    // 审批流程设置
    const handleApprovalFlow = (record) => {
      message.info('审批流程设置功能开发中')
    }

    // 复制对象
    const handleCopyObject = (record) => {
      modalTitle.value = '复制对象'
      formData.id = null
      // 自动生成对象ID
      formData.object_id = 'OBJ_' + Date.now()
      formData.name_en = record.name_en + '_COPY'
      formData.name_zh = record.name_zh + '（复制）'
      formData.type = record.type
      formData.is_parent = record.is_parent
      formData.project = record.project
      formData.remark = record.remark
      formData.fields = []

      // 获取对象字段并复制
      try {
        request.get(`/api/object/get/${record.id}`).then(response => {
          if (response.code === 200) {
            formData.fields = response.data.fields || []
            // 为每个字段确保有唯一ID
            formData.fields.forEach(field => {
              field.id = Date.now() + Math.floor(Math.random() * 1000)
            })
          }
        })
      } catch (error) {
        message.error('获取对象字段失败')
      }

      modalVisible.value = true
    }

    onMounted(() => {
      getObjects()
      getFieldTypes()
      getMenuTree()
    })

    return {
      searchForm,
      objects,
      loading,
      pagination,
      selectedRowKeys,
      allSelected,
      columns,
      modalVisible,
      modalTitle,
      formData,
      formRef,
      formRules,
      fieldColumns,
      fieldTypes,
      generateModalVisible,
      generateForm,
      generateFormRef,
      generateRules,
      menuTree,
      triggerModalVisible,
      triggerEditModalVisible,
      triggerEditTitle,
      triggers,
      triggerLoading,
      triggerForm,
      triggerFormRef,
      triggerRules,
      triggerEvents,
      triggerColumns,
      handleSearch,
      resetSearch,
      handleTableChange,
      handleSelectAll,
      handleSelect,
      handleAdd,
      handleEdit,
      handleModalOk,
      handleModalCancel,
      handleAddField,
      handleDeleteField,
      handleClearField,
      handleGeneratePage,
      handleGenerateOk,
      handleGenerateCancel,
      handleCustomTriggerSetting,
      handleAddTrigger,
      handleEditTrigger,
      handleDeleteTrigger,
      handleTriggerEditOk,
      handleTriggerCancel,
      handleTriggerEditCancel,
      generateTestSQL,
      clearAction,
      handleDelete,
      handleClearData,
      handleTriggerSetting,
      handleMiddlewareSetting,
      handleCustomTriggerSetting,
      handleGenerateCommonValues,
      handleRelatedResources,
      handleApprovalFlow,
      handleCopyObject
    }
  }
})
</script>

<style scoped>
.object-management {
  padding: 24px;
  background: #f0f2f5;
  min-height: calc(100vh - 64px);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.page-header h1 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #333;
}

.search-bar {
  margin-bottom: 24px;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.table-container {
  margin-bottom: 24px;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* 表格样式调整 */
:deep(.ant-table) {
  border-radius: 8px;
  overflow: hidden;
}

:deep(.ant-table-thead > tr > th) {
  background: #fafafa;
  font-weight: 600;
  color: #333;
}

:deep(.ant-table-tbody > tr:hover > td) {
  background: #f5f5f5;
}

/* 按钮样式调整 */
:deep(.ant-btn-primary) {
  background: #1890ff;
  border-color: #1890ff;
}

:deep(.ant-btn-primary:hover) {
  background: #40a9ff;
  border-color: #40a9ff;
}

/* 弹窗样式调整 */
:deep(.ant-modal-header) {
  border-radius: 8px 8px 0 0;
}

:deep(.ant-modal-footer) {
  border-radius: 0 0 8px 8px;
}

/* 表单样式调整 */
:deep(.ant-form-item-label > label) {
  font-weight: 500;
  color: #333;
}

:deep(.ant-input:focus),
:deep(.ant-select-focused .ant-select-selector),
:deep(.ant-textarea:focus) {
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
  border-color: #1890ff;
}

/* 下拉菜单样式调整 */
:deep(.ant-dropdown-menu) {
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

:deep(.ant-dropdown-menu-item:hover) {
  background: #f0f2f5;
}
</style>