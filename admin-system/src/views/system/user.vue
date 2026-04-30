<template>
  <div>
    <a-card>
      <div class="table-toolbar">
        <a-space wrap>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            新增用户
          </a-button>
          <a-button @click="getUserList">
            <template #icon>
              <ReloadOutlined />
            </template>
            刷新
          </a-button>
        </a-space>
        
        <a-input-search
          v-model:value="searchText"
          placeholder="搜索用户..."
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
          <template v-if="column.key === 'avatar'">
            <a-avatar size="small">{{ record.name.charAt(0) }}</a-avatar>
          </template>
          
          <template v-if="column.key === 'status'">
            <a-tag :color="record.status === 'active' ? 'green' : 'red'">
              {{ record.status === 'active' ? '启用' : '禁用' }}
            </a-tag>
          </template>
          
          <template v-if="column.key === 'role'">
            {{ record.role_name || '普通用户' }}
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
      :title="editingRecord ? '编辑用户' : '新增用户'"
      @ok="handleSave"
      @cancel="handleCancel"
    >
      <a-form
        :model="editForm"
        :label-col="{ span: 6 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item label="用户名" name="username">
          <a-input v-model:value="editForm.username" />
        </a-form-item>
        <a-form-item label="姓名" name="name">
          <a-input v-model:value="editForm.name" />
        </a-form-item>
        <a-form-item label="邮箱" name="email">
          <a-input v-model:value="editForm.email" />
        </a-form-item>
        <a-form-item label="电话" name="phone">
          <a-input v-model:value="editForm.phone" />
        </a-form-item>
        <a-form-item label="角色" name="role_id">
          <a-select v-model:value="editForm.role_id" placeholder="请选择角色">
            <a-select-option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="状态" name="status">
          <a-select v-model:value="editForm.status">
            <a-select-option value="active">启用</a-select-option>
            <a-select-option value="inactive">禁用</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item v-if="!editingRecord" label="密码" name="password">
          <a-input-password v-model:value="editForm.password" placeholder="请输入密码" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { UserOutlined, PlusOutlined, ReloadOutlined } from '@ant-design/icons-vue'
import request from '@/utils/request'

const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    sorter: (a, b) => a.id - b.id
  },
  {
    title: '头像',
    dataIndex: 'avatar',
    key: 'avatar',
    width: 80
  },
  {
    title: '用户名',
    dataIndex: 'username',
    key: 'username',
    sorter: (a, b) => a.username.localeCompare(b.username)
  },
  {
    title: '姓名',
    dataIndex: 'name',
    key: 'name',
    sorter: (a, b) => a.name.localeCompare(b.name)
  },
  {
    title: '邮箱',
    dataIndex: 'email',
    key: 'email'
  },
  {
    title: '电话',
    dataIndex: 'phone',
    key: 'phone'
  },
  {
    title: '角色',
    dataIndex: 'role',
    key: 'role',
    customRender: ({ record }) => record.role_name || '普通用户'
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
const roles = ref([]);

const editForm = ref({
  id: null,
  username: '',
  name: '',
  email: '',
  phone: '',
  role_id: 3,
  status: 'active',
  password: ''
});

// 获取用户列表
const getUserList = async () => {
  loading.value = true;
  try {
    const res = await request.get('/api/user/list', {
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
    console.error('获取用户列表失败:', error);
  } finally {
    loading.value = false;
  }
};

// 获取角色列表
const getRoles = async () => {
  try {
    const res = await request.get('/api/role/all');
    if (res.code === 0) {
      roles.value = res.data;
    }
  } catch (error) {
    console.error('获取角色列表失败:', error);
  }
};

// 页面变化
const handlePageChange = (page, pageSize) => {
  currentPage.value = page;
  pageSize.value = pageSize;
  getUserList();
};

// 搜索
const handleSearch = () => {
  currentPage.value = 1;
  getUserList();
};

// 创建用户
const handleCreate = () => {
  editingRecord.value = null;
  editForm.value = {
    id: null,
    username: '',
    name: '',
    email: '',
    phone: '',
    role_id: 3,
    status: 'active',
    password: ''
  };
  modalVisible.value = true;
};

// 编辑用户
const handleEdit = (record) => {
  editingRecord.value = { ...record };
  editForm.value = {
    id: record.id,
    username: record.username,
    name: record.name,
    email: record.email,
    phone: record.phone,
    role_id: record.role_id || 3,
    status: record.status
  };
  modalVisible.value = true;
};

// 删除用户
const handleDelete = async (id) => {
  if (confirm('确定要删除这个用户吗？')) {
    try {
      const res = await request.delete(`/api/user/delete/${id}`);
      if (res.code === 0) {
        alert('删除成功');
        getUserList();
      } else {
        alert(res.message || '删除失败');
      }
    } catch (error) {
      console.error('删除用户失败:', error);
      alert('删除失败，请检查网络连接');
    }
  }
};

// 保存用户
const handleSave = async () => {
  if (!editForm.value.username) {
    alert('请输入用户名');
    return;
  }
  
  if (!editingRecord.value && !editForm.value.password) {
    alert('请输入密码');
    return;
  }
  
  try {
    let res;
    if (editingRecord.value) {
      // 更新用户
      res = await request.put(`/api/user/update/${editForm.value.id}`, editForm.value);
    } else {
      // 创建用户
      res = await request.post('/api/user/create', editForm.value);
    }
    
    if (res.code === 0) {
      alert('操作成功');
      modalVisible.value = false;
      getUserList();
    } else {
      alert(res.message || '操作失败');
    }
  } catch (error) {
    console.error('保存用户失败:', error);
    alert('操作失败，请检查网络连接');
  }
};

// 取消
const handleCancel = () => {
  modalVisible.value = false;
};

// 初始化
onMounted(() => {
  getUserList();
  getRoles();
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