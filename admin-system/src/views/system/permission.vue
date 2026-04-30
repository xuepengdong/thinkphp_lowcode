<template>
  <div>
    <a-card>
      <div class="table-toolbar">
        <a-space wrap>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            新增权限
          </a-button>
          <a-button @click="handleRefresh">
            <template #icon>
              <ReloadOutlined />
            </template>
            刷新
          </a-button>
        </a-space>
        
        <a-input-search
          v-model:value="searchText"
          placeholder="搜索权限..."
          style="width: 280px"
          @search="handleSearch"
        />
      </div>
      
      <a-table
        :columns="columns"
        :data-source="filteredData"
        :pagination="{
          pageSizeOptions: ['10', '20', '50'],
          showSizeChanger: true,
          showQuickJumper: true,
          total: filteredData.length,
          showTotal: (total) => `共 ${total} 条`
        }"
        row-key="id"
        :scroll="{ x: 1500, y: 500 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'action'">
            <a-space>
              <a-button type="link" size="small" @click="handleEdit(record)">编辑</a-button>
              <a-button type="link" size="small" danger @click="handleDelete(record.id)">删除</a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>
    
    <!-- 编辑弹窗 -->
    <a-modal
      v-model:open="modalVisible"
      :title="editingRecord ? '编辑权限' : '新增权限'"
      @ok="handleSave"
      @cancel="handleCancel"
      width="700px"
    >
      <a-form
        :model="editForm"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item label="权限名称" name="name">
          <a-input v-model:value="editForm.name" />
        </a-form-item>
        <a-form-item label="权限标识" name="code">
          <a-input v-model:value="editForm.code" />
        </a-form-item>
        <a-form-item label="类型" name="type">
          <a-select v-model:value="editForm.type">
            <a-select-option value="menu">菜单</a-select-option>
            <a-select-option value="button">按钮</a-select-option>
            <a-select-option value="api">接口</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="父级权限" name="parentId">
          <a-select v-model:value="editForm.parentId" placeholder="请选择父级权限">
            <a-select-option :value="null">无</a-select-option>
            <a-select-option v-for="item in dataSource" :key="item.id" :value="item.id">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="描述" name="description">
          <a-textarea v-model:value="editForm.description" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { PlusOutlined, ReloadOutlined } from '@ant-design/icons-vue'

const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    sorter: (a, b) => a.id - b.id
  },
  {
    title: '权限名称',
    dataIndex: 'name',
    key: 'name',
    sorter: (a, b) => a.name.localeCompare(b.name)
  },
  {
    title: '权限标识',
    dataIndex: 'code',
    key: 'code',
    sorter: (a, b) => a.code.localeCompare(b.code)
  },
  {
    title: '类型',
    dataIndex: 'type',
    key: 'type',
    customRender: ({ text }) => {
      const typeMap = {
        menu: '菜单',
        button: '按钮',
        api: '接口'
      };
      return typeMap[text] || text;
    },
    filters: [
      { text: '菜单', value: 'menu' },
      { text: '按钮', value: 'button' },
      { text: '接口', value: 'api' }
    ],
    onFilter: (value, record) => record.type.indexOf(value) === 0
  },
  {
    title: '父级权限',
    dataIndex: 'parentName',
    key: 'parentName'
  },
  {
    title: '描述',
    dataIndex: 'description',
    key: 'description',
    ellipsis: true
  },
  {
    title: '创建时间',
    dataIndex: 'createTime',
    key: 'createTime',
    sorter: (a, b) => new Date(a.createTime) - new Date(b.createTime)
  },
  {
    title: '操作',
    key: 'action',
    fixed: 'right',
    width: 150
  }
];

// 模拟权限数据
const mockPermissionData = [
  {
    id: 1,
    name: '用户管理',
    code: 'user:manage',
    type: 'menu',
    parentId: null,
    parentName: '-',
    description: '用户管理模块访问权限',
    createTime: '2023-01-15 10:30:00'
  },
  {
    id: 2,
    name: '用户查询',
    code: 'user:view',
    type: 'api',
    parentId: 1,
    parentName: '用户管理',
    description: '查询用户信息权限',
    createTime: '2023-01-15 10:30:00'
  },
  {
    id: 3,
    name: '用户新增',
    code: 'user:add',
    type: 'api',
    parentId: 1,
    parentName: '用户管理',
    description: '新增用户权限',
    createTime: '2023-01-15 10:30:00'
  },
  {
    id: 4,
    name: '用户编辑',
    code: 'user:edit',
    type: 'api',
    parentId: 1,
    parentName: '用户管理',
    description: '编辑用户信息权限',
    createTime: '2023-01-15 10:30:00'
  },
  {
    id: 5,
    name: '用户删除',
    code: 'user:delete',
    type: 'api',
    parentId: 1,
    parentName: '用户管理',
    description: '删除用户权限',
    createTime: '2023-01-15 10:30:00'
  },
];

const dataSource = ref(mockPermissionData);
const searchText = ref('');
const modalVisible = ref(false);
const editingRecord = ref(null);

const editForm = ref({
  id: null,
  name: '',
  code: '',
  type: 'menu',
  parentId: null,
  description: ''
});

const filteredData = computed(() => {
  if (!searchText.value) return dataSource.value;
  
  return dataSource.value.filter(item =>
    item.name.toLowerCase().includes(searchText.value.toLowerCase()) ||
    item.code.toLowerCase().includes(searchText.value.toLowerCase()) ||
    item.description.toLowerCase().includes(searchText.value.toLowerCase())
  );
});

const handleCreate = () => {
  editingRecord.value = null;
  editForm.value = {
    id: null,
    name: '',
    code: '',
    type: 'menu',
    parentId: null,
    description: ''
  };
  modalVisible.value = true;
};

const handleEdit = (record) => {
  editingRecord.value = { ...record };
  editForm.value = { ...record };
  modalVisible.value = true;
};

const handleDelete = (id) => {
  if (confirm('确定要删除这个权限吗？')) {
    dataSource.value = dataSource.value.filter(item => item.id !== id);
  }
};

const handleSave = () => {
  if (editingRecord.value) {
    // 更新现有记录
    const index = dataSource.value.findIndex(item => item.id === editingRecord.value.id);
    if (index !== -1) {
      dataSource.value[index] = { ...editForm.value };
    }
  } else {
    // 添加新记录
    const newId = Math.max(...dataSource.value.map(item => item.id)) + 1;
    const parentItem = dataSource.value.find(item => item.id === editForm.value.parentId);
    dataSource.value = [
      ...dataSource.value,
      {
        ...editForm.value,
        id: newId,
        parentName: parentItem ? parentItem.name : '-', 
        createTime: new Date().toLocaleString('zh-CN')
      }
    ];
  }
  
  modalVisible.value = false;
};

const handleCancel = () => {
  modalVisible.value = false;
};

const handleSearch = () => {
  // 搜索已经在 computed 属性中处理
};

const handleRefresh = () => {
  // 刷新数据
  searchText.value = '';
};
</script>

<style scoped>
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f2d3d;
  margin: 0;
}

.table-toolbar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 16px;
  align-items: center;
}

.table-toolbar .ant-input-search {
  width: 300px;
}
</style>