<template>
  <div class="menu-management">
    <div class="content-container">
      <div class="left-sidebar">
        <div class="sidebar-header">
          <h3>框架结构</h3>
          <a-button size="small" @click="refreshTree">
            <a-icon type="reload" /> 刷新
          </a-button>
        </div>
        <div class="tree-container">
          <a-tree
            :tree-data="treeData"
            :default-expand-all="false"
            :expanded-keys="expandedKeys"
            @select="handleTreeSelect"
            @expand="handleTreeExpand"
            :field-names="{ title: 'name', key: 'id', children: 'children' }"
          >
            <template #switcherIcon="{ expanded }">
              <span class="tree-switcher">
                {{ expanded ? '−' : '+' }}
              </span>
            </template>
          </a-tree>
        </div>
      </div>
      <div class="right-content">
        <div class="search-bar">
          <div class="search-bar-right">
            <a-button type="primary" @click="handleAdd">
              <a-icon type="plus" /> 新增栏目
            </a-button>
          </div>
          <a-form layout="inline" :model="searchForm" @submit.prevent="handleSearch">
            <a-form-item label="栏目名称:">
              <a-input v-model:value="searchForm.name" placeholder="请输入栏目名称" />
            </a-form-item>
            <a-form-item label="父级栏目:">
              <a-select v-model:value="searchForm.parent_id" placeholder="请选择父级栏目">
                <a-select-option :value="0">无父级</a-select-option>
                <a-select-option v-for="menu in menuOptions" :key="menu.id" :value="menu.id">
                  {{ menu.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
            <a-form-item label="栏目类型:">
              <a-select v-model:value="searchForm.type" placeholder="请选择栏目类型">
                <a-select-option value="">请选择</a-select-option>
                <a-select-option value="menu">菜单</a-select-option>
                <a-select-option value="system">系统</a-select-option>
              </a-select>
            </a-form-item>
            <a-form-item label="是否区分角色:">
              <a-select v-model:value="searchForm.is_role" placeholder="请选择">
                <a-select-option value="">请选择</a-select-option>
                <a-select-option value="1">是</a-select-option>
                <a-select-option value="0">否</a-select-option>
              </a-select>
            </a-form-item>
            <a-form-item label="页面ID:">
              <a-input v-model:value="searchForm.page_id" placeholder="请输入页面ID" />
            </a-form-item>
            <a-form-item label="对象ID:">
              <a-input v-model:value="searchForm.object_id" placeholder="请输入对象ID" />
            </a-form-item>
            <a-form-item>
              <a-button type="primary" html-type="submit">
                <a-icon type="search" /> 搜索
              </a-button>
              <a-button style="margin-left: 8px" @click="resetSearch">
                重置
              </a-button>
              <a-button style="margin-left: 8px" @click="batchAdjustDisplay">
                <a-icon type="setting" /> 批量调整栏目显示
              </a-button>
            </a-form-item>
          </a-form>
        </div>

    <div class="table-container">
      <a-table
        :columns="columns"
        :data-source="menus"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        :expand-icon-column-index="-1"
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
            <a-space size="small">
              <a-button size="small" type="primary" @click="handleEdit(record)">
                修改
              </a-button>
              <a-button size="small" danger @click="handleDelete(record.id)">
                删除
              </a-button>
            </a-space>
          </template>
          <template v-else-if="column.key === 'parent_id'">
            {{ getParentName(record.parent_id) }}
          </template>
          <template v-else-if="column.key === 'object_id'">
            {{ record.object_id || '-' }}
          </template>
          <template v-else-if="column.key === 'page_name'">
            {{ record.page_name || record.path || '-' }}
          </template>
          <template v-else-if="column.key === 'object_name'">
            {{ getObjectName(record.object_id) }}
          </template>
          <template v-else-if="column.key === 'table_name'">
            {{ getTableName(record.object_id) }}
          </template>
        </template>
      </a-table>
    </div>

    <!-- 菜单编辑弹窗 -->
    <a-modal
      v-model:visible="modalVisible"
      :title="modalTitle"
      width="800px"
      @ok="handleModalOk"
      @cancel="handleModalCancel"
    >
      <a-form :model="formData" :rules="formRules" ref="formRef">
        <a-form-item label="栏目名称" name="name" required>
          <a-input v-model:value="formData.name" placeholder="请输入栏目名称" />
        </a-form-item>
        <a-form-item label="调用方式" name="call_type" required>
          <a-select v-model:value="formData.call_type" placeholder="请选择调用方式">
            <a-select-option value="page">页面</a-select-option>
            <a-select-option value="url">调用链接</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item v-if="formData.call_type === 'url'" label="调用地址" name="path" required>
          <a-input v-model:value="formData.path" placeholder="请输入调用地址" />
        </a-form-item>
        <a-form-item v-else-if="formData.call_type === 'page'" label="页面" name="page_name" required>
          <a-space>
            <a-input v-model:value="formData.page_name" placeholder="请选择页面" :disabled="true" />
            <a-button type="primary" @click="openPageSelectModal">选择页面</a-button>
          </a-space>
        </a-form-item>
        <a-form-item label="父级栏目" name="parent_id">
          <a-tree-select
            v-model:value="formData.parent_id"
            placeholder="请选择父级栏目"
            :tree-data="menuTreeData"
            :field-names="{ title: 'name', value: 'id', children: 'children' }"
            show-search
            allow-clear
            style="width: 100%"
          >
            <template #title="{ name }">
              {{ name }}
            </template>
          </a-tree-select>
        </a-form-item>


        <a-form-item label="排序" name="sort">
          <a-input-number v-model:value="formData.sort" :min="0" />
        </a-form-item>
        <a-form-item label="状态" name="status">
          <a-select v-model:value="formData.status" placeholder="请选择状态">
            <a-select-option value="active">激活</a-select-option>
            <a-select-option value="inactive">禁用</a-select-option>
          </a-select>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 批量调整栏目显示弹窗 -->
    <a-modal
      v-model:visible="batchModalVisible"
      title="批量调整栏目显示"
      width="600px"
      @ok="handleBatchAdjust"
      @cancel="handleBatchModalCancel"
    >
      <a-form :model="batchForm" :rules="batchFormRules" ref="batchFormRef">
        <a-form-item label="显示状态" name="display_status">
          <a-select v-model:value="batchForm.display_status" placeholder="请选择显示状态">
            <a-select-option value="1">显示</a-select-option>
            <a-select-option value="0">隐藏</a-select-option>
          </a-select>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- 页面选择弹窗 -->
    <a-modal
      v-model:visible="pageSelectModalVisible"
      title="选择页面"
      width="1000px"
      @cancel="handlePageSelectCancel"
    >
      <div class="search-bar" style="margin-bottom: 16px">
        <a-form layout="inline" :model="pageSearchForm" @submit.prevent="handlePageSearch">
          <a-form-item label="页面ID:">
            <a-input v-model:value="pageSearchForm.pageId" placeholder="请输入页面ID" />
          </a-form-item>
          <a-form-item label="页面名称:">
            <a-input v-model:value="pageSearchForm.pageName" placeholder="请输入页面名称" />
          </a-form-item>
          <a-form-item>
            <a-button type="primary" html-type="submit">搜索</a-button>
            <a-button style="margin-left: 8px" @click="resetPageSearch">重置</a-button>
          </a-form-item>
        </a-form>
      </div>
      <a-table
        :columns="pageColumns"
        :data-source="pages"
        :loading="pageLoading"
        :pagination="pagePagination"
        row-key="id"
        @change="handlePageChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'select'">
            <a-button type="primary" size="small" @click="selectPage(record)">选择</a-button>
          </template>
        </template>
      </a-table>
    </a-modal>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, computed, onMounted, h } from 'vue'
import { message, Table, Space, Button, Popconfirm } from 'ant-design-vue'
import request from '@/utils/request'

export default defineComponent({
  name: 'MenuManagement',
  setup() {
    const searchForm = reactive({
      name: '',
      parent_id: '',
      type: '',
      is_role: '',
      page_id: '',
      object_id: ''
    })

    const menus = ref([])
    const loading = ref(false)
    const pagination = reactive({
      current: 1,
      pageSize: 10,
      total: 0
    })

    const selectedRowKeys = ref([])
    const allSelected = ref(false)

    const modalVisible = ref(false)
    const modalTitle = ref('新增栏目')
    const formData = reactive({
      id: null,
      name: '',
      path: '',
      call_type: 'url',
      page_name: '',
      page_id: '',
      parent_id: 0,
      object_id: '',
      type: 'menu',
      sort: 0,
      status: 'active'
    })

    const formRef = ref()
    
    const validateUrl = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入调用地址'))
      } else {
        const urlRegex = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w.-]*)*\/?$/
        if (!urlRegex.test(value)) {
          callback(new Error('请输入有效的URL地址'))
        } else {
          callback()
        }
      }
    }
    
    const formRules = reactive({
      name: [{ required: true, message: '请输入栏目名称' }],
      call_type: [{ required: true, message: '请选择调用方式' }],
      path: [{ required: true, validator: validateUrl }],
      page_name: [{ required: true, message: '请选择页面' }]
    })

    // 页面选择弹窗相关
    const pageSelectModalVisible = ref(false)
    const pageSearchForm = reactive({
      pageId: '',
      pageName: ''
    })
    const pages = ref([])
    const pageLoading = ref(false)
    const pagePagination = reactive({
      current: 1,
      pageSize: 10,
      total: 0
    })
    const pageColumns = [
      { title: '页面ID', dataIndex: 'id', key: 'id', width: 100 },
      { title: '页面名称', dataIndex: 'name', key: 'name', width: 200 },
      { title: '对象名称', dataIndex: 'object_name', key: 'object_name', width: 150 },
      { title: '表名', dataIndex: 'table_name', key: 'table_name', width: 150 },
      { title: '操作', key: 'select', width: 100 }
    ]

    const batchModalVisible = ref(false)
    const batchForm = reactive({
      display_status: '1'
    })
    const batchFormRef = ref()
    const batchFormRules = reactive({
      display_status: [{ required: true, message: '请选择显示状态' }]
    })

    const menuOptions = ref([])
    const objects = ref([])
    const treeData = ref([])
    const menuTreeData = ref([])
    const selectedMenuId = ref(null)
    const expandedKeys = ref<Array<string | number>>([])
    
    // 处理树展开/折叠
    const handleTreeExpand = (keys) => {
      expandedKeys.value = keys
    }

    const columns = [
      { title: '选择', key: 'selection', width: 60 },
      { title: '操作', key: 'actions', width: 120, fixed: 'right' },
      { title: '栏目ID', dataIndex: 'id', key: 'id', width: 100 },
      { title: '栏目名称', dataIndex: 'name', key: 'name', width: 200 },
      { title: '父级栏目', dataIndex: 'parent_id', key: 'parent_id', width: 150 },
      { title: '栏目类型', dataIndex: 'type', key: 'type', width: 100 },
      { title: '页面ID/调用地址', dataIndex: 'path', key: 'path', width: 200 },
      { title: '所属页面', key: 'page_name', width: 150 },
      { title: '对象ID', dataIndex: 'object_id', key: 'object_id', width: 100 },
      { title: '对象名称', key: 'object_name', width: 150 },
      { title: '表名', key: 'table_name', width: 150 }
    ]

    // 获取菜单列表
    const getMenus = async () => {
      loading.value = true
      try {
        const params = {}
        if (selectedMenuId.value) {
          params.parent_id = selectedMenuId.value
        }
        
        const response = await request.get('/api/menu/list', { params })
        if (response.code === 200) {
          // 转换为树形结构
          const menuList = []
          const menuMap = {}
          
          // 先将所有菜单放入 map
          response.data.forEach(menu => {
            menuMap[menu.id] = { ...menu, children: [] }
          })
          
          // 构建树形结构
          response.data.forEach(menu => {
            if (menu.parent_id === 0) {
              // 根菜单
              menuList.push(menuMap[menu.id])
            } else if (menuMap[menu.parent_id]) {
              // 子菜单
              menuMap[menu.parent_id].children.push(menuMap[menu.id])
            }
          })
          
          // 构建父级栏目选择的树形数据
          menuTreeData.value = menuList
          
          // 如果没有选择菜单，显示所有根菜单
          if (!selectedMenuId.value) {
            menus.value = menuList
          } else {
            // 如果选择了菜单，显示该菜单及其所有子菜单
            const selectedMenu = menuMap[selectedMenuId.value]
            if (selectedMenu) {
              // 递归获取所有子节点
              const getAllChildren = (menu) => {
                const result = [menu]
                if (menu.children && menu.children.length > 0) {
                  menu.children.forEach(child => {
                    result.push(...getAllChildren(child))
                  })
                }
                return result
              }
              menus.value = getAllChildren(selectedMenu)
            }
          }
          
          menuOptions.value = response.data
          pagination.total = menus.value.length
        }
      } catch (error) {
        message.error('获取菜单列表失败')
      } finally {
        loading.value = false
      }
    }

    // 获取树形结构数据
    const getTreeData = async () => {
      try {
        const response = await request.get('/api/menu/tree')
        if (response.code === 200) {
          // 处理数据，确保只有当 children 存在且不为空时才设置 children 字段
          const processTreeData = (data) => {
            return data.map(item => {
              const processedItem = {
                ...item
              }
              if (item.children && Array.isArray(item.children) && item.children.length > 0) {
                processedItem.children = processTreeData(item.children)
              }
              return processedItem
            })
          }
          treeData.value = processTreeData(response.data)
        }
      } catch (error) {
        message.error('获取树形结构失败')
      }
    }

    // 构建树数据
    const buildTreeData = () => {
      // 调用获取树形结构的API
      getTreeData()
    }

    // 处理树节点选择
    const handleTreeSelect = (selectedKeys, info) => {
      selectedMenuId.value = selectedKeys[0] || null
      getMenus()
    }

    // 刷新树结构
    const refreshTree = () => {
      buildTreeData()
    }

    // 获取对象列表
    const getObjects = async () => {
      try {
        const response = await request.get('/api/object/list', {
          params: {
            page: 1,
            limit: 100
          }
        })
        if (response.code === 200) {
          objects.value = response.data
        }
      } catch (error) {
        message.error('获取对象列表失败')
      } finally {
        // 构建树数据
        buildTreeData()
      }
    }

    // 获取父级菜单名称
    const getParentName = (parentId) => {
      if (parentId === 0) return '无'
      const parent = menuOptions.value.find(menu => menu.id === parentId)
      return parent ? parent.name : '未知'
    }

    // 获取对象名称
    const getObjectName = (objectId) => {
      if (!objectId) return '-'
      const object = objects.value.find(obj => obj.object_id === objectId)
      return object ? object.name_zh : '未知'
    }

    // 获取表名
    const getTableName = (objectId) => {
      if (!objectId) return '-'
      const object = objects.value.find(obj => obj.object_id === objectId)
      return object ? object.name_en.toUpperCase() : '未知'
    }

    // 批量调整栏目显示
    const batchAdjustDisplay = () => {
      if (selectedRowKeys.value.length === 0) {
        message.warning('请选择要调整的栏目')
        return
      }
      batchModalVisible.value = true
    }

    // 处理批量调整
    const handleBatchAdjust = async () => {
      if (batchFormRef.value) {
        try {
          await batchFormRef.value.validate()
          
          const data = {
            ids: selectedRowKeys.value,
            display_status: batchForm.display_status
          }

          const response = await request.post('/api/menu/batch-adjust', data)
          if (response.code === 200) {
            message.success('批量调整成功')
            batchModalVisible.value = false
            getMenus()
          }
        } catch (error) {
          console.error(error)
        }
      }
    }

    // 取消批量调整弹窗
    const handleBatchModalCancel = () => {
      batchModalVisible.value = false
    }

    // 搜索
    const handleSearch = () => {
      // 这里可以添加搜索逻辑
      getMenus()
    }

    // 重置搜索
    const resetSearch = () => {
      Object.keys(searchForm).forEach(key => {
        searchForm[key] = ''
      })
      getMenus()
    }

    // 表格变化
    const handleTableChange = (paginationObj) => {
      pagination.current = paginationObj.current
      pagination.pageSize = paginationObj.pageSize
      getMenus()
    }

    // 全选
    const handleSelectAll = (e) => {
      if (e.target.checked) {
        selectedRowKeys.value = menus.value.map(item => item.id)
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

    // 新增菜单
    const handleAdd = () => {
      modalTitle.value = '新增栏目'
      formData.id = null
      formData.name = ''
      formData.path = ''
      formData.call_type = 'url'
      formData.page_name = ''
      formData.page_id = ''
      formData.parent_id = 0
      formData.object_id = ''
      formData.type = 'menu'
      formData.sort = 0
      formData.status = 'active'
      modalVisible.value = true
    }

    // 编辑菜单
    const handleEdit = (record) => {
      modalTitle.value = '修改栏目'
      formData.id = record.id
      formData.name = record.name
      formData.path = record.path
      formData.call_type = record.call_type || 'url'
      formData.page_name = record.page_name || record.path || ''
      formData.page_id = record.page_id || ''
      formData.parent_id = record.parent_id
      formData.object_id = record.object_id || ''
      formData.type = record.type
      formData.sort = record.sort
      formData.status = record.status
      modalVisible.value = true
    }

    // 保存菜单
    const handleModalOk = async () => {
      if (formRef.value) {
        try {
          await formRef.value.validate()
          
          const data = {
            ...formData
          }

          let response
          if (formData.id) {
            response = await request.put(`/api/menu/update/${formData.id}`, data)
          } else {
            response = await request.post('/api/menu/create', data)
          }

          if (response.code === 200) {
            message.success(formData.id ? '修改成功' : '创建成功')
            modalVisible.value = false
            getMenus()
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

    // 删除菜单
    const handleDelete = async (id) => {
      try {
        console.log('删除菜单ID:', id)
        const response = await request.delete(`/api/menu/delete/${id}`)
        console.log('删除响应:', response)
        if (response.code === 200) {
          message.success('删除成功')
          // 刷新右侧列表
          getMenus()
          // 刷新左侧树结构
          refreshTree()
        } else {
          message.error(response.message || '删除失败')
        }
      } catch (error) {
        console.error('删除错误:', error)
        message.error('删除失败: ' + (error.message || '网络错误'))
      }
    }

    // 打开页面选择弹窗
    const openPageSelectModal = () => {
      pageSelectModalVisible.value = true
      getPages()
    }

    // 获取页面列表
    const getPages = async () => {
      pageLoading.value = true
      try {
        const params = {
          page: pagePagination.current,
          limit: pagePagination.pageSize
        }
        if (pageSearchForm.pageId) {
          params.pageId = pageSearchForm.pageId
        }
        if (pageSearchForm.pageName) {
          params.pageName = pageSearchForm.pageName
        }
        
        const response = await request.get('/api/page/list', { params })
        console.log('页面列表响应:', response)
        if (response.code === 200) {
          // 尝试不同的数据结构
          if (response.data.list) {
            pages.value = response.data.list
            pagePagination.total = response.data.total
          } else if (Array.isArray(response.data)) {
            pages.value = response.data
            pagePagination.total = response.data.length
          } else if (response.data.data) {
            pages.value = response.data.data.list || response.data.data
            pagePagination.total = response.data.data.total || pages.value.length
          } else {
            pages.value = response.data
            pagePagination.total = Array.isArray(response.data) ? response.data.length : 0
          }
          console.log('页面数据:', pages.value)
        }
      } catch (error) {
        console.error('获取页面列表错误:', error)
        message.error('获取页面列表失败：' + (error.message || '未知错误'))
      } finally {
        pageLoading.value = false
      }
    }

    // 搜索页面
    const handlePageSearch = () => {
      pagePagination.current = 1
      getPages()
    }

    // 重置页面搜索
    const resetPageSearch = () => {
      pageSearchForm.pageId = ''
      pageSearchForm.pageName = ''
      pagePagination.current = 1
      getPages()
    }

    // 页面表格变化
    const handlePageChange = (paginationObj) => {
      pagePagination.current = paginationObj.current
      pagePagination.pageSize = paginationObj.pageSize
      getPages()
    }

    // 选择页面
    const selectPage = (record) => {
      formData.path = record.id.toString()
      formData.page_name = record.name || record.page_name || '未知页面'
      // 保存页面ID
      formData.page_id = record.id.toString()
      // 同时保存页面关联的对象ID
      formData.object_id = record.object_id || ''
      pageSelectModalVisible.value = false
    }

    // 取消页面选择弹窗
    const handlePageSelectCancel = () => {
      pageSelectModalVisible.value = false
    }

    onMounted(() => {
      getMenus()
      getObjects()
      buildTreeData()
    })

    return {
      searchForm,
      menus,
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
      batchModalVisible,
      batchForm,
      batchFormRef,
      batchFormRules,
      menuOptions,
      objects,
      treeData,
      menuTreeData,
      selectedMenuId,
      handleSearch,
      resetSearch,
      handleTableChange,
      handleSelectAll,
      handleSelect,
      handleAdd,
      handleEdit,
      handleModalOk,
      handleModalCancel,
      handleDelete,
      getParentName,
      getObjectName,
      getTableName,
      batchAdjustDisplay,
      handleBatchAdjust,
      handleBatchModalCancel,
      handleTreeSelect,
      refreshTree,
      expandedKeys,
      handleTreeExpand,
      pageSelectModalVisible,
      pageSearchForm,
      pages,
      pageLoading,
      pagePagination,
      pageColumns,
      openPageSelectModal,
      handlePageSearch,
      resetPageSearch,
      handlePageChange,
      selectPage,
      handlePageSelectCancel
    }
  }
})
</script>

<style scoped>
.menu-management {
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

.content-container {
  display: flex;
  gap: 24px;
  margin-bottom: 24px;
}

.left-sidebar {
  width: 300px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f0f0;
  background: #fafafa;
}

.sidebar-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.tree-container {
  padding: 16px;
  max-height: 600px;
  overflow-y: auto;
}

.search-bar {
  position: relative;
}

.search-bar-right {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
}

.tree-switcher {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
  color: #666;
  cursor: pointer;
  transition: all 0.2s;
  margin-right: 4px;
}

.tree-switcher:hover {
  background: #f5f5f5;
  border-color: #1890ff;
  color: #1890ff;
}

.right-content {
  flex: 1;
  min-width: 0;
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

/* 树形结构样式 */
:deep(.ant-tree) {
  font-size: 14px;
}

:deep(.ant-tree-node-selected .ant-tree-node-content-wrapper) {
  background-color: #e6f7ff;
  border-radius: 2px;
}

:deep(.ant-tree-node-content-wrapper:hover) {
  background-color: #f0f0f0;
  border-radius: 2px;
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
</style>