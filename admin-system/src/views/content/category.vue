
<template>
  <div>
    <a-card>
      <div class="table-toolbar">
        <a-space wrap>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            新增分类
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
          placeholder="搜索分类..."
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
          <template v-if="column.key === 'status'">
            <a-tag :color="record.status === 'enabled' ? 'green' : 'red'">
              {{ record.status === 'enabled' ? '启用' : '禁用' }}
            </a-tag>
          </template>
          
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
      :title="editingRecord ? '编辑分类' : '新增分类'"
      @ok="handleSave"
      @cancel="handleCancel"
      width="600px"
    >
      <a-form
        :model="editForm"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item label="分类名称" name="name">
          <a-input v-model:value="editForm.name" />
        </a-form-item>
        <a-form-item label="分类标识" name="code">
          <a-input v-model:value="editForm.code" />
        </a-form-item>
        <a-form-item label="父级分类" name="parentId">
          <a-select v-model:value="editForm.parentId" placeholder="请选择父级分类">
            <a-select-option :value="null">无</a-select-option>
            <a-select-option v-for="item in dataSource" :key="item.id" :value="item.id">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="排序" name="sortOrder">
          <a-input-number v-model:value="editForm.sortOrder" style="width: 100%" />
        </a-form-item>
        <a-form-item label="状态" name="status">
          <a-select v-model:value="editForm.status">
            <a-select-option value="enabled">启用</a-select-option>
            <a-select-option value="disabled">禁用</a-select-option>
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
    title: '分类名称',
    dataIndex: 'name',
    key: 'name',
    sorter: (a, b) => a.name.localeCompare(b.name)
  },
  {
    title: '分类标识',
    dataIndex: 'code',
    key: 'code',
    sorter: (a, b) => a.code.localeCompare(b.code)
  },
  {
    title: '父级分类',
    dataIndex: 'parentName',
    key: 'parentName'
  },
  {
    title: '文章数量',
    dataIndex: 'articleCount',
    key: 'articleCount',
    sorter: (a, b) => a.articleCount - b.articleCount
  },
  {
    title: '排序',
    dataIndex: 'sortOrder',
    key: 'sortOrder',
    sorter: (a, b) => a.sortOrder - b.sortOrder
  },
  {
    title: '状态',
    dataIndex: 'status',
    key: 'status',
    filters: [
      { text: '启用', value: 'enabled' },
      { text: '禁用', value: 'disabled' }
    ],
    onFilter: (value, record) => record.status.indexOf(value) === 0
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

// 模拟分类数据
const mockCategoryData = [
  {
    id: 1,
    name: '技术分享',
    code: 'tech',
    parentId: null,
    parentName: '-',
    articleCount: 15,
    sortOrder: 1,
    status: 'enabled',
    description: '技术相关的分享文章',
    createTime: '2023-01-15 10:30:00'
  },
  {
    id: 2,
    name: '生活随笔',
    code: 'life',
    parentId: null,
    parentName: '-',
    articleCount: 8,
    sortOrder: 2,
    status: 'enabled',
    description: '生活感悟和随笔',
    createTime: '2023-02-20 14:20:00'
  },
  {
    id: 3,
    name: '行业资讯',
    code: 'news',
    parentId: null,
    parentName: '-',
    articleCount: 12,
    sortOrder: 3,
    status: 'enabled',
    description: '行业动态和新闻',
    createTime: '2023-03-10 09:15:00'
  },
  {
    id: 4,
    name: '前端开发',
    code: 'frontend',
    parentId: 1,
    parentName: '技术分享',
    articleCount: 7,
    sortOrder: 1,
    status: 'enabled',
    description: '前端技术分享',
    createTime: '2023-01-20 11:30:00'
  },
  {
    id: 5,
    name: '后端开发',
    code: 'backend',
    parentId: 1,
    parentName: '技术分享',
    articleCount: 5,
    sortOrder: 2,
    status: 'enabled',
    description: '后端技术分享',
    createTime: '2023-01-25 15:20:00'
  },
  {
    id: 6,
    name: '移动开发',
    code: 'mobile',
    parentId: 1,
    parentName: '技术分享',
    articleCount: 3,
    sortOrder: 3,
    status: 'enabled',
    description: '移动开发技术',
    createTime: '2023-02-10 10:15:00'
  },
  {
    id: 7,
    name: '美食分享',
    code: 'food',
    parentId: 2,
    parentName: '生活随笔',
    articleCount: 4,
    sortOrder: 1,
    status: 'enabled',
    description: '美食制作和分享',
    createTime: '2023-02-25 13:45:00'
  },
  {
    id: 8,
    name: '旅游攻略',
    code: 'travel',
    parentId: 2,
    parentName: '生活随笔',
    articleCount: 4,
    sortOrder: 2,
    status: 'disabled',
    description: '旅游经验和攻略',
    createTime: '2023-03-15 09:20:00'
  }
];

const dataSource = ref(mockCategoryData);
const searchText = ref('');
const modalVisible = ref(false);
const editingRecord = ref(null);

const editForm = ref({
  id: null,
  name: '',
  code: '',
  parentId: null,
  articleCount: 0,
  sortOrder: 0,
  status: 'enabled',
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
    parentId: null,
    articleCount: 0,
    sortOrder: 0,
    status: 'enabled',
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
  if (confirm('确定要删除这个分类吗？')) {
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
        articleCount: 0,
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