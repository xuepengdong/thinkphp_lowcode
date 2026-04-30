<template>
  <div>
    <a-card>
      <div class="table-toolbar">
        <a-space wrap>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            新增角色
          </a-button>
          <a-button @click="getRoleList">
            <template #icon>
              <ReloadOutlined />
            </template>
            刷新
          </a-button>
        </a-space>
        
        <a-input-search
          v-model:value="searchText"
          placeholder="搜索角色..."
          style="width: 280px"
          @search="handleSearch"
        />
      </div>
      
      <a-table
        :columns="columns"
        :data-source="dataSource"
        :pagination="{
          pageSizeOptions: ['10', '20', '50'],
          showSizeChanger: true,
          showQuickJumper: true,
          total: total.value,
          showTotal: (total) => `共 ${total} 条`,
          onChange: handlePageChange
        }"
        row-key="id"
        :scroll="{ x: 1500, y: 500 }"
        :loading="loading"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'">
            <a-tag :color="record.status === 'active' ? 'green' : 'red'">
              {{ record.status === 'active' ? '启用' : '禁用' }}
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
      :title="editingRecord ? '编辑角色' : '新增角色'"
      @ok="handleSave"
      @cancel="handleCancel"
      width="600px"
    >
      <a-form
        :model="editForm"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item label="角色名称" name="name">
          <a-input v-model:value="editForm.name" />
        </a-form-item>
        <a-form-item label="角色标识" name="code">
          <a-input v-model:value="editForm.code" />
        </a-form-item>
        <a-form-item label="描述" name="description">
          <a-textarea v-model:value="editForm.description" />
        </a-form-item>
        <a-form-item label="状态" name="status">
          <a-select v-model:value="editForm.status">
            <a-select-option value="active">启用</a-select-option>
            <a-select-option value="inactive">禁用</a-select-option>
          </a-select>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { PlusOutlined, ReloadOutlined } from '@ant-design/icons-vue'
import request from '@/utils/request'

const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    sorter: (a, b) => a.id - b.id
  },
  {
    title: '角色名称',
    dataIndex: 'name',
    key: 'name',
    sorter: (a, b) => a.name.localeCompare(b.name)
  },
  {
    title: '角色标识',
    dataIndex: 'code',
    key: 'code',
    sorter: (a, b) => a.code.localeCompare(b.code)
  },
  {
    title: '描述',
    dataIndex: 'description',
    key: 'description',
    ellipsis: true
  },
  {
    title: '用户数量',
    dataIndex: 'userCount',
    key: 'userCount',
    sorter: (a, b) => a.userCount - b.userCount
  },
  {
    title: '状态',
    dataIndex: 'status',
    key: 'status',
    filters: [
      { text: '启用', value: 'active' },
      { text: '禁用', value: 'inactive' }
    ],
    onFilter: (value, record) => record.status === value
  },
  {
    title: '创建时间',
    dataIndex: 'created_at',
    key: 'created_at',
    sorter: (a, b) => new Date(a.created_at) - new Date(b.created_at)
  },
  {
    title: '操作',
    key: 'action',
    fixed: 'right',
    width: 150
  }
];

const dataSource = ref([]);
const total = ref(0);
const loading = ref(false);
const searchText = ref('');
const modalVisible = ref(false);
const editingRecord = ref(null);
const currentPage = ref(1);
const pageSize = ref(10);

const editForm = ref({
  id: null,
  name: '',
  code: '',
  description: '',
  status: 'active'
});

// 获取角色列表
const getRoleList = async () => {
  loading.value = true;
  try {
    const res = await request.get('/api/role/list', {
      params: {
        page: currentPage.value,
        limit: pageSize.value,
        search: searchText.value
      }
    });
    if (res.code === 0) {
      dataSource.value = res.data;
      total.value = res.total;
    }
  } catch (error) {
    console.error('获取角色列表失败:', error);
  } finally {
    loading.value = false;
  }
};

// 页面变化
const handlePageChange = (page, pageSize) => {
  currentPage.value = page;
  pageSize.value = pageSize;
  getRoleList();
};

// 搜索
const handleSearch = () => {
  currentPage.value = 1;
  getRoleList();
};

// 创建角色
const handleCreate = () => {
  editingRecord.value = null;
  editForm.value = {
    id: null,
    name: '',
    code: '',
    description: '',
    status: 'active'
  };
  modalVisible.value = true;
};

// 编辑角色
const handleEdit = (record) => {
  editingRecord.value = { ...record };
  editForm.value = {
    id: record.id,
    name: record.name,
    code: record.code,
    description: record.description,
    status: record.status
  };
  modalVisible.value = true;
};

// 删除角色
const handleDelete = async (id) => {
  if (confirm('确定要删除这个角色吗？')) {
    try {
      const res = await request.delete(`/api/role/delete/${id}`);
      if (res.code === 0) {
        alert('删除成功');
        getRoleList();
      } else {
        alert(res.message || '删除失败');
      }
    } catch (error) {
      console.error('删除角色失败:', error);
      alert('删除失败，请检查网络连接');
    }
  }
};

// 保存角色
const handleSave = async () => {
  if (!editForm.value.name || !editForm.value.code) {
    alert('请输入角色名称和标识');
    return;
  }
  
  try {
    let res;
    if (editingRecord.value) {
      // 更新角色
      res = await request.put(`/api/role/update/${editForm.value.id}`, editForm.value);
    } else {
      // 创建角色
      res = await request.post('/api/role/create', editForm.value);
    }
    
    if (res.code === 0) {
      alert('操作成功');
      modalVisible.value = false;
      getRoleList();
    } else {
      alert(res.message || '操作失败');
    }
  } catch (error) {
    console.error('保存角色失败:', error);
    alert('操作失败，请检查网络连接');
  }
};

// 取消
const handleCancel = () => {
  modalVisible.value = false;
};

// 初始化
onMounted(() => {
  getRoleList();
});
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