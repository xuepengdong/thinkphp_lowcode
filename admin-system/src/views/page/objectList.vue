<template>
  <div class="object-list-container">
    <!-- 顶部工具栏（仅超级管理员可见） -->
    <div class="top-toolbar" v-if="isAdmin">
      <div class="toolbar-left">
        <h2 class="module-title">{{ moduleTitle }}</h2>
        <p class="module-desc">{{ moduleDesc }}</p>
      </div>
      <div class="toolbar-right">
        <a-space size="small">
          <!-- 导入导出 -->
          <Dropdown>
            <Button size="small">
              数据管理 <DownOutlined />
            </Button>
            <template #overlay>
              <Menu>
                <Menu.Item key="import" @click="handleImport">
                  <UploadOutlined /> 导入
                </Menu.Item>
                <Menu.Item key="export" @click="handleExport">
                  <DownloadOutlined /> 导出
                </Menu.Item>
              </Menu>
            </template>
          </Dropdown>
          <!-- 列表按钮设置 -->
          <Button size="small" @click="handleListButtonSetting">
            <SettingOutlined /> 列表按钮设置
          </Button>
          <!-- 刷新按钮 -->
          <Button size="small" @click="handleRefresh">
            <ReloadOutlined /> 刷新
          </Button>
        </a-space>
      </div>
    </div>

    <!-- 搜索栏（可折叠） -->
    <div class="search-bar" v-if="showSearch && searchFields.length > 0">
      <div class="search-header" @click="toggleSearchCollapse">
        <div class="search-title-wrap">
          <component :is="searchCollapsed ? DownOutlined : UpOutlined" />
          <span class="search-title">搜索条件</span>
        </div>
        <div class="search-actions">
          <Button size="small" @click.stop="resetSearch">清空</Button>
          <Button size="small" @click.stop="openSearchSetting">设置动态查询条件</Button>
        </div>
      </div>
      <div class="search-content" v-show="!searchCollapsed">
        <Form layout="inline" :model="searchForm">
          <template v-for="(field, index) in searchFields" :key="field.fieldName">
            <Form.Item :label="field.label">
              <div class="search-item">
                <Select 
                  v-model:value="searchConditions[index].operator" 
                  style="width: 80px" 
                  size="small"
                >
                  <template v-if="isNumericField(field.fieldName)">
                    <Select.Option value="=">等于</Select.Option>
                    <Select.Option value="!=">不等于</Select.Option>
                    <Select.Option value=">">大于</Select.Option>
                    <Select.Option value="<">小于</Select.Option>
                    <Select.Option value=">=">大于等于</Select.Option>
                    <Select.Option value="<=">小于等于</Select.Option>
                    <Select.Option value="like">包含</Select.Option>
                  </template>
                  <template v-else>
                    <Select.Option value="like">包含</Select.Option>
                    <Select.Option value="=">等于</Select.Option>
                    <Select.Option value="!=">不等于</Select.Option>
                    <Select.Option value="start">开头是</Select.Option>
                    <Select.Option value="end">结尾是</Select.Option>
                  </template>
                </Select>
                <Input 
                  v-model:value="searchForm[field.fieldName]" 
                  :placeholder="`请输入${field.label}`" 
                  @pressEnter="handleSearch" 
                  style="width: 150px"
                  size="small"
                />
              </div>
            </Form.Item>
          </template>
          <Form.Item>
            <Button type="primary" size="small" @click="handleSearch">
              <SearchOutlined /> 搜索
            </Button>
          </Form.Item>
        </Form>
      </div>
    </div>

    <!-- 数据表格 -->
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
            <a-space size="small">
              <template v-for="group in buttonSettings.buttonGroups" :key="group.id">
                <Dropdown v-if="group.buttons.length > 0">
                  <template #overlay>
                    <Menu>
                      <template v-for="btn in group.buttons" :key="btn">
                        <Menu.Item 
                          v-if="btn === 'view'" 
                          @click="handleView(record)"
                        >
                          <EyeOutlined /> 查看
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'edit'" 
                          @click="handleEdit(record)"
                        >
                          <EditOutlined /> 修改
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'delete'" 
                          @click="handleDelete(record)"
                        >
                          <DeleteOutlined /> 删除
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'conditionDelete'" 
                          @click="handleConditionalDelete(record)"
                        >
                          <FilterOutlined /> 条件删除
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'copy'" 
                          @click="handleCopy(record)"
                        >
                          <CopyOutlined /> 复制
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'add'" 
                          @click="handleAdd(record)"
                        >
                          <PlusCircleOutlined /> 添加
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'print'" 
                          @click="handlePrint(record)"
                        >
                          <PrinterOutlined /> 打印
                        </Menu.Item>
                        <Menu.Item 
                          v-if="btn === 'download'" 
                          @click="handleDownload(record)"
                        >
                          <DownloadOutlined /> 下载
                        </Menu.Item>
                      </template>
                    </Menu>
                  </template>
                  <Button size="small" type="primary">
                    {{ group.name }} <DownOutlined />
                  </Button>
                </Dropdown>
              </template>
            </a-space>
          </template>
          <template v-else-if="column.key === 'status'">
            <a-tag :color="record.status === 'active' ? 'green' : 'red'">
              {{ record.status === 'active' ? '启用' : '禁用' }}
            </a-tag>
          </template>
          <template v-else>
            {{ record[column.dataIndex] !== undefined && record[column.dataIndex] !== null ? record[column.dataIndex] : '-' }}
          </template>
        </template>
      </Table>
    </div>

    <!-- 编辑/新增弹窗 -->
    <a-modal
      v-model:visible="modalVisible"
      :title="modalTitle"
      width="950px"
      :height="700"
      :footer="null"
    >
      <a-form :model="formData" :rules="formRules" ref="formRef">
        <!-- 根据页面设置渲染字段布局 -->
        <template v-for="(block, blockIndex) in editPageDisplayBlocks" :key="block.id">
          <!-- 显示块标题 -->
          <div class="display-block-header">
            <h4>{{ block.name }}</h4>
          </div>
          <!-- 显示块内容区域 -->
          <div 
            class="display-block-content"
            :style="{ gridTemplateColumns: `repeat(${block.columns}, 1fr)` }"
          >
            <a-form-item 
              v-for="field in block.fields" 
              :key="field.fieldName" 
              :label="field.label" 
              :name="field.fieldName"
              :required="field.required"
              class="form-field-item"
            >
              <a-input 
                v-model:value="formData[field.fieldName]" 
                :placeholder="`请输入${field.label}`" 
                :disabled="field.disabled"
              />
            </a-form-item>
          </div>
          <!-- 显示块分隔线（最后一个不显示） -->
          <div v-if="blockIndex < editPageDisplayBlocks.length - 1" class="display-block-divider"></div>
        </template>
        
        <!-- 如果没有配置，显示默认字段 -->
        <template v-if="editPageDisplayBlocks.length === 0 || editPageDisplayBlocks.every(b => b.fields.length === 0)">
          <a-form-item 
            v-for="field in formFields" 
            :key="field.fieldName" 
            :label="field.label" 
            :name="field.fieldName"
            :required="field.required"
          >
            <a-input 
              v-model:value="formData[field.fieldName]" 
              :placeholder="`请输入${field.label}`" 
              :disabled="field.disabled"
            />
          </a-form-item>
        </template>
      </a-form>
      <!-- 自定义底部按钮 -->
      <div class="modal-footer">
        <Button size="small" @click="handleEditPageSetting" v-if="isAdmin">
          <SettingOutlined /> 修改页面设置
        </Button>
        <Button size="small" @click="handleModalCancel">取消</Button>
        <Button size="small" type="primary" @click="handleModalOk">确定</Button>
      </div>
    </a-modal>

    <!-- 搜索设置弹窗 -->
    <a-modal
      title="搜索设置"
      v-model:visible="showSearchSetting"
      width="500px"
      :footer="null"
    >
      <div class="search-setting">
        <div class="setting-section">
          <div class="section-header">
            <h4>选择搜索字段</h4>
            <div class="section-actions">
              <a-button size="small" @click="selectAllFields">全选</a-button>
              <a-button size="small" @click="deselectAllFields">取消</a-button>
            </div>
          </div>
          <div class="field-grid">
            <div 
              v-for="field in availableSearchFields" 
              :key="field.field_name_en"
              class="field-item"
            >
              <label class="field-checkbox">
                <input 
                  type="checkbox" 
                  :checked="isSearchFieldSelected(field.field_name_en)"
                  @change="toggleSearchField(field)"
                />
                <span class="field-label">{{ field.field_name_zh || field.field_name_en }}</span>
              </label>
            </div>
          </div>
        </div>
        <div class="setting-footer">
          <a-button size="small" @click="showSearchSetting = false">取消</a-button>
          <a-button type="primary" size="small" @click="handleSearchSettingOk">保存</a-button>
        </div>
      </div>
    </a-modal>

    <!-- 列表按钮设置弹窗 -->
    <ListButtonSettingModal
      v-model:visible="showListButtonSettingModal"
      :object-id="objectId"
      :fields="formFields"
      @saved="handleListButtonSettingSaved"
    />

    <!-- 修改页面设置弹窗 -->
    <EditPageSettingModal
      v-model:visible="showEditPageSettingModal"
      :object-id="objectId"
      :fields="formFields"
      @saved="handleEditPageSettingSaved"
    />

    <!-- 导入导出弹窗 -->
    <ImportExportModal
      v-model:visible="showImportExportModal"
      :mode="importExportMode"
      :object-id="objectId"
      :fields="exportFields"
      @close="showImportExportModal = false"
      @success="handleImportExportSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Table, Button, Input, Form, Select, message, Modal, Dropdown, Menu, Space } from 'ant-design-vue';
import { SearchOutlined, ReloadOutlined, EyeOutlined, EditOutlined, DeleteOutlined, DownOutlined, UploadOutlined, DownloadOutlined, UpOutlined, SettingOutlined, FilterOutlined, CopyOutlined, PrinterOutlined, PlusCircleOutlined } from '@ant-design/icons-vue';
import request from '@/utils/request';
import ImportExportModal from '@/components/ImportExportModal.vue';
import ListButtonSettingModal from '@/components/ListButtonSettingModal.vue';
import EditPageSettingModal from '@/components/EditPageSettingModal.vue';
import { useUserStore } from '@/store/user';

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();

// 判断是否为超级管理员
const isAdmin = computed(() => {
  const username = userStore.userInfo.username;
  // 假设 username 为 'admin' 或包含 'admin' 的用户是超级管理员
  return username && (username === 'admin' || username.includes('admin'));
});

// 当前栏目ID
const menuId = ref('');
// 当前页面ID（栏目关联的页面）
const pageId = ref('');
// 当前对象ID（页面关联的对象）
const objectId = ref('');
// 页面标题
const pageTitle = ref('列表页面');
// 表格列配置
const columns = ref([]);
// 列表按钮设置弹窗
const showListButtonSettingModal = ref(false);
// 按钮配置 { displayMode: 'normal', buttonGroups: [...] }
const buttonSettings = ref({
  displayMode: 'normal',
  buttonGroups: [
    { id: 'group1', name: '基础操作', buttons: ['view', 'edit', 'delete'] },
    { id: 'group2', name: '其他操作', buttons: ['copy', 'print'] }
  ]
});
// 修改页面设置弹窗
const showEditPageSettingModal = ref(false);
// 编辑页面显示块配置
const editPageDisplayBlocks = ref([]);
// 数据列表
const data = ref([]);
// 加载状态
const loading = ref(false);
// 是否显示搜索栏
const showSearch = ref(true);
// 是否显示操作按钮
const showActions = ref(true);
// 搜索字段配置
const searchFields = ref([]);
// 搜索条件操作符
const searchConditions = ref([]);
// 表单字段配置
const formFields = ref([]);
// 搜索设置弹窗
const showSearchSetting = ref(false);
// 可用字段列表（用于搜索设置）
const availableSearchFields = ref([]);
// 已选搜索字段
const selectedSearchFields = ref([]);
// 搜索栏折叠状态
const searchCollapsed = ref(false);
// 模块标题
const moduleTitle = ref('列表页面');
// 模块描述
const moduleDesc = ref('');
// 导入导出弹窗
const showImportExportModal = ref(false);
const importExportMode = ref('import'); // 'import' or 'export'

// 弹窗状态
const modalVisible = ref(false);
const modalTitle = ref('');
const formRef = ref(null);
const formData = reactive({});
const formRules = reactive({});

// 分页配置
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total) => `共 ${total} 条记录`
});

const paginationConfig = computed(() => ({
  current: pagination.current,
  pageSize: pagination.pageSize,
  total: pagination.total,
  showSizeChanger: pagination.showSizeChanger,
  showQuickJumper: pagination.showQuickJumper,
  showTotal: pagination.showTotal
}));

// 获取栏目信息
const getMenuInfo = async (id) => {
  try {
    console.log('获取栏目信息, menuId:', id);
    const response = await request.get('/api/menu/list', {
      params: { id }
    });
    console.log('栏目信息响应:', response);
    if (response.code === 200 && response.data.length > 0) {
      const menu = response.data[0];
      pageTitle.value = menu.name || '列表页面';
      console.log('当前栏目:', menu.name, 'page_id:', menu.page_id, 'object_id:', menu.object_id);
      // 如果栏目关联了页面，获取页面信息
      if (menu.page_id) {
        pageId.value = menu.page_id;
        await getPageInfo(menu.page_id);
      } else if (menu.object_id) {
        // 如果栏目直接关联了对象，直接获取对象信息
        objectId.value = menu.object_id;
        await getObjectInfo(menu.object_id);
      } else {
        console.log('栏目没有关联页面或对象');
        message.warning('该栏目没有关联页面或对象，请先配置');
      }
    } else {
      console.log('未找到栏目信息');
      message.error('未找到栏目信息');
    }
  } catch (error) {
    console.error('获取栏目信息失败:', error);
    message.error('获取栏目信息失败');
  }
};

// 获取页面信息
const getPageInfo = async (id) => {
  try {
    console.log('获取页面信息, pageId:', id);
    const response = await request.get('/api/page/list', {
      params: { id }
    });
    console.log('页面信息响应:', response);
    if (response.code === 200 && response.data.length > 0) {
      const page = response.data[0];
      console.log('当前页面:', page.pageName, 'object_id:', page.object_id);
      // 如果页面关联了对象，获取对象信息
      if (page.object_id) {
        objectId.value = page.object_id;
        // 获取页面配置
        await getPageSettings(page.id);
        // 获取对象信息（用于获取字段定义）
        await getObjectInfo(page.object_id);
      } else {
        console.log('页面没有关联对象');
        message.warning('该页面没有关联对象，请先配置对象');
      }
    }
  } catch (error) {
    console.error('获取页面信息失败:', error);
    message.error('获取页面信息失败');
  }
};

// 获取页面设置
const getPageSettings = async (pageId) => {
  try {
    console.log('获取页面设置, pageId:', pageId);
    const response = await request.get('/api/page/setting', {
      params: { page_id: pageId }
    });
    console.log('页面设置响应:', response);
    if (response.code === 200 && response.data) {
      const setting = response.data;
      // 解析 display_fields
      if (setting.display_fields) {
        try {
          const displayFields = typeof setting.display_fields === 'string' 
            ? JSON.parse(setting.display_fields) 
            : setting.display_fields;
          console.log('解析的display_fields:', displayFields);
          // 根据配置的字段构建表格列
          buildColumnsFromDisplayFields(displayFields);
        } catch (error) {
          console.error('解析 display_fields 失败:', error);
        }
      }
    }
  } catch (error) {
    console.error('获取页面设置失败:', error);
  }
};

// 根据配置的显示字段构建表格列
const buildColumnsFromDisplayFields = (displayFields) => {
  console.log(displayFields, 11111111111);
  if (!displayFields || displayFields.length === 0) {
    console.log('displayFields为空');
    return;
  }

  // ID 始终在第一列，从 displayFields 中移除已有的 ID（避免重复）
  const fieldsWithoutId = displayFields.filter(f => {
    const name = (f.fieldName || f.field_name_en || '').toLowerCase();
    return name !== 'id';
  });

  // 构建列：ID 第一列 + 其他字段按配置顺序
  const fieldColumns = [
    { label: 'ID', fieldName: 'id' },
    ...fieldsWithoutId
  ];

  console.log('构建表格列:', fieldColumns);
  columns.value = fieldColumns.map(field => ({
    title: field.label || field.fieldName,
    dataIndex: field.fieldName || field.field_name_en,
    key: field.fieldName || field.field_name_en,
    ellipsis: true
  }));

  // 添加操作列（放到最前面）
  columns.value.unshift({
    title: '操作',
    key: 'actions',
    width: 280,
    fixed: 'left'
  });

  // 构建搜索字段（取前 3 个字段作为搜索条件，排除 ID）
  searchFields.value = fieldsWithoutId.slice(0, 3).map(field => ({
    fieldName: field.fieldName || field.field_name_en,
    label: field.label || field.field_name_zh
  }));

  // 初始化搜索条件操作符
  searchConditions.value = searchFields.value.map(() => ({ operator: 'like' }));

  // 构建表单字段
  formFields.value = fieldsWithoutId.map(field => ({
    fieldName: field.fieldName || field.field_name_en,
    label: field.label || field.field_name_zh,
    required: false,
    disabled: false
  }));

  // 初始化搜索表单
  searchFields.value.forEach(field => {
    if (!searchForm[field.fieldName]) {
      searchForm[field.fieldName] = '';
    }
  });
};

// 获取对象信息
const getObjectInfo = async (id) => {
  try {
    console.log('获取对象信息, objectId:', id);
    const response = await request.get(`/api/object/get/${id}`);
    console.log('对象信息响应:', response);
    if (response.code === 200) {
      const object = response.data;
      console.log('当前对象:', object.name_en);
      // 获取对象字段列表（如果失败也继续获取数据）
      try {
        await getObjectFields(object.object_id);
      } catch (e) {
        console.error('获取对象字段失败:', e);
      }
      // 获取列表数据
      getListData(object.object_id);
    }
  } catch (error) {
    console.error('获取对象信息失败:', error);
    message.error('获取对象信息失败');
  }
};

// 获取对象字段列表（仅在没有页面配置时使用）
const getObjectFields = async (objectId) => {
  try {
    const response = await request.get(`/api/object-field/list/${objectId}`);
    if (response.code === 200) {
      const fields = response.data;
      // 只有在 columns 为空时才使用对象的所有字段（即没有页面配置时）
      if (columns.value.length === 0) {
        console.log('使用对象字段作为默认列');
        // 构建表格列，默认先添加ID列
        columns.value = [{
          title: 'ID',
          dataIndex: 'id',
          key: 'id',
          ellipsis: true
        }];
        
        // 添加其他字段（排除ID，避免重复）
        const otherFields = fields.filter(field => field.field_name_en !== 'id');
        otherFields.forEach(field => {
          columns.value.push({
            title: field.field_name_zh || field.field_name_en,
            dataIndex: field.field_name_en,
            key: field.field_name_en,
            ellipsis: true
          });
        });
        
        // 添加操作列（放到最前面）
        columns.value.unshift({
          title: '操作',
          key: 'actions',
          width: 280,
          fixed: 'left'
        });
        // 构建搜索字段（取前 3 个字段作为搜索条件）
        searchFields.value = fields.slice(0, 3).map(field => ({
          fieldName: field.field_name_en,
          label: field.field_name_zh || field.field_name_en
        }));
        // 初始化搜索条件操作符
        searchConditions.value = searchFields.value.map(() => ({ operator: 'like' }));
        // 构建表单字段
        formFields.value = fields.map(field => ({
          fieldName: field.field_name_en,
          label: field.field_name_zh || field.field_name_en,
          required: false,
          disabled: false
        }));
        // 初始化搜索表单
        searchFields.value.forEach(field => {
          if (!searchForm[field.fieldName]) {
            searchForm[field.fieldName] = '';
          }
        });
      }
    }
  } catch (error) {
    console.error('获取对象字段失败:', error);
  }
};

// 获取列表数据
const getListData = async (objId) => {
  if (!objId) {
    console.log('objectId为空，无法获取数据');
    return;
  }
  
  loading.value = true;
  try {
    console.log('获取列表数据, object_id:', objId);
    const params = {
      object_id: objId,
      page: pagination.current,
      page_size: pagination.pageSize
    };
    // 添加搜索条件
    searchFields.value.forEach((field, index) => {
      if (searchForm[field.fieldName]) {
        const operator = searchConditions.value[index]?.operator || 'like';
        // 根据操作符格式化搜索值
        let value = searchForm[field.fieldName];
        if (operator === 'like') {
          params[field.fieldName] = `%${value}%`;
        } else if (operator === 'start') {
          params[field.fieldName] = `${value}%`;
        } else if (operator === 'end') {
          params[field.fieldName] = `%${value}`;
        } else {
          params[field.fieldName] = value;
        }
        // 传递操作符
        params[`${field.fieldName}_operator`] = operator;
      }
    });
    console.log('请求参数:', params);
    const response = await request.get('/api/object/data', { params });
    console.log('列表数据响应:', response);
    if (response.code === 200) {
      data.value = response.data.list || response.data || [];
      pagination.total = response.data.total || data.value.length;
      console.log('获取到的数据:', data.value.length, '条');
    } else {
      message.error(response.message || '获取列表数据失败');
    }
  } catch (error) {
    console.error('获取列表数据失败:', error);
    message.error('获取列表数据失败');
  } finally {
    loading.value = false;
  }
};

// 搜索表单
const searchForm = reactive({});

// 搜索
const handleSearch = () => {
  if (!objectId.value) {
    message.warning('请先选择一个栏目');
    return;
  }
  pagination.current = 1;
  getListData(objectId.value);
};

// 重置搜索
const resetSearch = () => {
  searchFields.value.forEach(field => {
    searchForm[field.fieldName] = '';
  });
  pagination.current = 1;
  getListData(objectId.value);
};

// 判断字段是否为数字类型
const isNumericField = (fieldName) => {
  // 常见的数字字段名
  const numericFields = ['id', 'num', 'count', 'amount', 'price', 'total', 'score', 'age', 'level', 'status'];
  // 检查字段名是否包含数字相关关键词
  const hasNumericKeyword = numericFields.some(keyword => fieldName.toLowerCase().includes(keyword));
  return hasNumericKeyword;
};

// 判断字段是否已选作为搜索字段
const isSearchFieldSelected = (fieldName) => {
  return selectedSearchFields.value.some(f => f.fieldName === fieldName);
};

// 切换搜索字段选择
const toggleSearchField = (field) => {
  const index = selectedSearchFields.value.findIndex(f => f.fieldName === field.field_name_en);
  if (index > -1) {
    selectedSearchFields.value.splice(index, 1);
  } else {
    selectedSearchFields.value.push({
      fieldName: field.field_name_en,
      label: field.field_name_zh || field.field_name_en
    });
  }
};

// 全选字段
const selectAllFields = () => {
  availableSearchFields.value.forEach(field => {
    if (!isSearchFieldSelected(field.field_name_en)) {
      selectedSearchFields.value.push({
        fieldName: field.field_name_en,
        label: field.field_name_zh || field.field_name_en
      });
    }
  });
};

// 取消全选
const deselectAllFields = () => {
  selectedSearchFields.value = [];
};

// 打开搜索设置弹窗
const openSearchSetting = async () => {
  if (objectId.value) {
    try {
      const response = await request.get(`/api/object-field/list/${objectId.value}`);
      if (response.code === 200) {
        availableSearchFields.value = response.data;
        // 初始化已选字段为当前搜索字段
        selectedSearchFields.value = [...searchFields.value];
        showSearchSetting.value = true;
      }
    } catch (error) {
      console.error('获取字段失败:', error);
      message.error('获取字段失败');
    }
  } else {
    message.warning('请先选择一个栏目');
  }
};

// 保存搜索设置
const handleSearchSettingOk = async () => {
  if (selectedSearchFields.value.length > 0) {
    searchFields.value = [...selectedSearchFields.value];
    searchConditions.value = searchFields.value.map(() => ({ operator: 'like' }));
    // 初始化新的搜索表单字段
    searchFields.value.forEach(field => {
      if (!searchForm[field.fieldName]) {
        searchForm[field.fieldName] = '';
      }
    });
    message.success('搜索条件设置成功');
  } else {
    message.warning('请至少选择一个搜索字段');
    return;
  }
  showSearchSetting.value = false;
};

// 分页变化
const handleTableChange = (pag) => {
  pagination.current = pag.current;
  pagination.pageSize = pag.pageSize;
  getListData(objectId.value);
};

// 刷新
const handleRefresh = () => {
  if (!objectId.value) {
    message.warning('请先选择一个栏目');
    return;
  }
  getListData(objectId.value);
};

// 切换搜索栏折叠状态
const toggleSearchCollapse = () => {
  searchCollapsed.value = !searchCollapsed.value;
};

// 导出字段（用于导出功能）
const exportFields = computed(() => {
  return columns.value
    .filter(col => col.dataIndex && col.key !== 'actions')
    .map(col => ({
      fieldName: col.dataIndex,
      label: col.title
    }));
});

// 导入
const handleImport = () => {
  importExportMode.value = 'import';
  showImportExportModal.value = true;
};

// 导出
const handleExport = () => {
  importExportMode.value = 'export';
  showImportExportModal.value = true;
};

// 导入导出成功回调
const handleImportExportSuccess = (data) => {
  if (data.type === 'import') {
    message.success(data.result.message || '导入成功');
    // 刷新数据
    getListData(objectId.value);
  } else if (data.type === 'export') {
    message.success(data.result.message || '导出成功');
  }
};

// 列表按钮设置
const handleListButtonSetting = () => {
  showListButtonSettingModal.value = true;
};

// 列表按钮设置保存成功回调
const handleListButtonSettingSaved = (settings) => {
  buttonSettings.value = settings;
  message.success('列表按钮设置已更新');
};

// 修改页面设置
const handleEditPageSetting = () => {
  showEditPageSettingModal.value = true;
};

// 修改页面设置保存成功回调
const handleEditPageSettingSaved = (settings) => {
  editPageDisplayBlocks.value = settings.displayBlocks || [];
  message.success('修改页面设置已更新');
};

// 加载编辑页面显示块配置
const loadEditPageDisplayBlocks = () => {
  try {
    const saved = localStorage.getItem(`editPageSettings_${objectId.value}`);
    if (saved) {
      const settings = JSON.parse(saved);
      editPageDisplayBlocks.value = settings.displayBlocks || [];
    }
  } catch (e) {
    console.error('加载编辑页面显示块配置失败:', e);
  }
};

// 条件删除
const handleConditionalDelete = (record) => {
  message.info(`条件删除记录: ${record.id}`);
};

// 复制
const handleCopy = (record) => {
  message.info(`复制记录: ${record.id}`);
};

// 打印
const handlePrint = (record) => {
  message.info(`打印记录: ${record.id}`);
};

// 下载
const handleDownload = (record) => {
  message.info(`下载记录: ${record.id}`);
};

// 查看详情
const handleView = (record) => {
  message.info(`查看记录: ${record.id}`);
};

// 编辑
const handleEdit = (record) => {
  modalTitle.value = '编辑记录';
  modalVisible.value = true;
  // 加载显示块配置
  loadEditPageDisplayBlocks();
  // 填充表单数据
  formFields.value.forEach(field => {
    formData[field.fieldName] = record[field.fieldName] || '';
  });
};

// 删除
const handleDelete = (record) => {
  Modal.confirm({
    title: '确认删除',
    content: `确定要删除这条记录吗？`,
    okText: '确定',
    cancelText: '取消',
    onOk: async () => {
      try {
        console.log('删除记录:', record.id, 'objectId:', objectId.value);
        // 使用POST方式模拟DELETE，确保参数能正确传递
        const response = await request.post(`/api/object/data/${record.id}/delete?object_id=${objectId.value}`);
        if (response.code === 200) {
          message.success('删除成功');
          getListData(objectId.value);
        } else {
          message.error(response.message || '删除失败');
        }
      } catch (error) {
        message.error('删除失败');
      }
    }
  });
};

// 弹窗确认
const handleModalOk = async () => {
  if (formRef.value) {
    try {
      await formRef.value.validate();
      // 判断是新增还是修改
      if (modalTitle.value.includes('新增')) {
        // 新增操作
        const response = await request.post('/api/object/data', {
          object_id: objectId.value,
          ...formData
        });
        if (response.code === 200) {
          message.success('新增成功');
          modalVisible.value = false;
          getListData(objectId.value);
        } else {
          message.error(response.message || '新增失败');
        }
      } else {
        // 修改操作
        const response = await request.put(`/api/object/data/${formData.id}`, {
          object_id: objectId.value,
          ...formData
        });
        if (response.code === 200) {
          message.success('修改成功');
          modalVisible.value = false;
          getListData(objectId.value);
        } else {
          message.error(response.message || '修改失败');
        }
      }
    } catch (error) {
      console.error('表单验证失败:', error);
    }
  }
};

// 弹窗取消
const handleModalCancel = () => {
  modalVisible.value = false;
};

// 监听路由变化
watch(() => route.params.menuId, (newMenuId) => {
  if (newMenuId) {
    console.log('路由变化，menuId:', newMenuId);
    menuId.value = newMenuId;
    initPage(newMenuId);
  }
});

// 初始化页面
const initPage = async (menuId) => {
  console.log('初始化页面, menuId:', menuId);
  // 重置状态
  pageId.value = '';
  objectId.value = '';
  columns.value = [];
  data.value = [];
  searchFields.value = [];
  formFields.value = [];
  // 获取栏目信息
  await getMenuInfo(menuId);
};

onMounted(() => {
  // 从路由或localStorage获取栏目ID
  const menuIdFromRoute = route.params.menuId || route.params.objectId;
  const menuIdFromStorage = localStorage.getItem('currentMenuId');
  const currentMenuId = menuIdFromRoute || menuIdFromStorage;
  
  console.log('页面加载，获取到的menuId:', currentMenuId);
  
  if (currentMenuId) {
    menuId.value = currentMenuId;
    initPage(currentMenuId);
    // 清除localStorage中的值
    localStorage.removeItem('currentMenuId');
  }
});
</script>

<style scoped>
.object-list-container {
  padding: 24px;
  background: #f0f2f5;
  min-height: 100vh;
}

/* 顶部工具栏 */
.top-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding: 20px 24px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.toolbar-left {
  flex: 1;
}

.module-title {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #1f1f1f;
}

.module-desc {
  margin: 4px 0 0;
  font-size: 14px;
  color: #8c8c8c;
}

.toolbar-right {
  flex-shrink: 0;
}

/* 搜索栏 */
.search-bar {
  margin-bottom: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.search-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
}

.search-header:hover {
  background: #fafafa;
}

.search-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.search-title {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.search-actions {
  display: flex;
  gap: 8px;
}

.search-content {
  padding: 16px 24px;
}

.action-bar {
  margin-bottom: 16px;
}

.table-container {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.table-header-actions {
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: flex-end;
}

:deep(.ant-table) {
  border-radius: 8px;
}

:deep(.ant-table-thead > tr > th) {
  background: #fafafa;
  font-weight: 600;
  color: #333;
}

:deep(.ant-table-tbody > tr:hover) {
  background: #f5f5f5;
}

:deep(.ant-btn) {
  margin-right: 8px;
}

:deep(.ant-space) {
  display: flex;
  align-items: center;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 12px 16px;
  border-top: 1px solid #e8e8e8;
  margin-top: 16px;
}

/* 显示块标题 */
.display-block-header {
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 2px solid #1890ff;
}

.display-block-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #1890ff;
}

/* 显示块内容区域 */
.display-block-content {
  display: grid;
  gap: 16px;
  padding: 16px;
  background: #fafafa;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  margin-bottom: 16px;
}

/* 表单字段项 */
.form-field-item {
  margin-bottom: 0;
}

/* 显示块分隔线 */
.display-block-divider {
  height: 1px;
  background: repeating-linear-gradient(
    to right,
    #d9d9d9,
    #d9d9d9 10px,
    transparent 10px,
    transparent 20px
  );
  margin: 20px 0;
}
</style>