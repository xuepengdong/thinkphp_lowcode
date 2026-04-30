<template>
  <div>
    <a-card>
      <div class="table-toolbar">
        <a-space wrap>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            新增文章
          </a-button>
          <a-button @click="getArticleList">
            <template #icon>
              <ReloadOutlined />
            </template>
            刷新
          </a-button>
        </a-space>
        
        <a-input-search
          v-model:value="searchText"
          placeholder="搜索文章..."
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
            <a-tag :color="record.status === 'published' ? 'green' : record.status === 'draft' ? 'orange' : 'red'">
              {{ {
                published: '已发布',
                draft: '草稿',
                archived: '归档'
              }[record.status] || record.status }}
            </a-tag>
          </template>
          
          <template v-if="column.key === 'tags'">
            {{ record.tags?.join(', ') || '-' }}
          </template>
          
          <template v-if="column.key === 'action'">
            <a-space>
              <a-button type="link" size="small" @click="handleView(record)">查看</a-button>
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
      :title="editingRecord ? '编辑文章' : '新增文章'"
      @ok="handleSave"
      @cancel="handleCancel"
      width="800px"
    >
      <a-form
        :model="editForm"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 18 }"
      >
        <a-form-item label="标题" name="title">
          <a-input v-model:value="editForm.title" />
        </a-form-item>
        <a-form-item label="分类" name="category_id">
          <a-select v-model:value="editForm.category_id" placeholder="请选择分类">
            <a-select-option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="标签" name="tags">
          <a-select
            v-model:value="editForm.tags"
            mode="tags"
            placeholder="请输入标签"
          >
          </a-select>
        </a-form-item>
        <a-form-item label="状态" name="status">
          <a-select v-model:value="editForm.status">
            <a-select-option value="draft">草稿</a-select-option>
            <a-select-option value="published">已发布</a-select-option>
            <a-select-option value="archived">归档</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item label="摘要" name="summary">
          <a-textarea v-model:value="editForm.summary" :rows="4" />
        </a-form-item>
        <a-form-item label="内容" name="content">
          <a-textarea v-model:value="editForm.content" :rows="8" placeholder="请输入文章内容" />
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
    title: '标题',
    dataIndex: 'title',
    key: 'title',
    ellipsis: true,
    sorter: (a, b) => a.title.localeCompare(b.title)
  },
  {
    title: '作者',
    dataIndex: 'authorName',
    key: 'authorName',
    sorter: (a, b) => a.authorName.localeCompare(b.authorName)
  },
  {
    title: '分类',
    dataIndex: 'categoryName',
    key: 'categoryName'
  },
  {
    title: '标签',
    dataIndex: 'tags',
    key: 'tags',
    customRender: ({ record }) => record.tags?.join(', ') || '-'
  },
  {
    title: '状态',
    dataIndex: 'status',
    key: 'status',
    filters: [
      { text: '草稿', value: 'draft' },
      { text: '已发布', value: 'published' },
      { text: '归档', value: 'archived' }
    ],
    onFilter: (value, record) => record.status === value
  },
  {
    title: '浏览量',
    dataIndex: 'view_count',
    key: 'view_count',
    sorter: (a, b) => a.view_count - b.view_count
  },
  {
    title: '发布时间',
    dataIndex: 'published_at',
    key: 'published_at',
    sorter: (a, b) => new Date(a.published_at) - new Date(b.published_at)
  },
  {
    title: '操作',
    key: 'action',
    fixed: 'right',
    width: 180
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
const categories = ref([]);

const editForm = ref({
  id: null,
  title: '',
  content: '',
  summary: '',
  category_id: null,
  tags: [],
  status: 'draft'
});

// 获取文章列表
const getArticleList = async () => {
  loading.value = true;
  try {
    const res = await request.get('/api/article/list', {
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
    console.error('获取文章列表失败:', error);
  } finally {
    loading.value = false;
  }
};

// 获取分类列表
const getCategories = async () => {
  try {
    const res = await request.get('/api/category/all');
    if (res.code === 0) {
      categories.value = res.data;
    }
  } catch (error) {
    console.error('获取分类列表失败:', error);
  }
};

// 页面变化
const handlePageChange = (page, pageSize) => {
  currentPage.value = page;
  pageSize.value = pageSize;
  getArticleList();
};

// 搜索
const handleSearch = () => {
  currentPage.value = 1;
  getArticleList();
};

// 创建文章
const handleCreate = () => {
  editingRecord.value = null;
  editForm.value = {
    id: null,
    title: '',
    content: '',
    summary: '',
    category_id: null,
    tags: [],
    status: 'draft'
  };
  modalVisible.value = true;
};

// 查看文章
const handleView = (record) => {
  // 查看文章详情
  console.log('查看文章:', record);
};

// 编辑文章
const handleEdit = async (record) => {
  try {
    const res = await request.get(`/api/article/info/${record.id}`);
    if (res.code === 0) {
      editingRecord.value = { ...res.data };
      editForm.value = {
        id: res.data.id,
        title: res.data.title,
        content: res.data.content,
        summary: res.data.summary,
        category_id: res.data.category_id,
        tags: res.data.tags,
        status: res.data.status
      };
      modalVisible.value = true;
    }
  } catch (error) {
    console.error('获取文章详情失败:', error);
    alert('获取文章详情失败');
  }
};

// 删除文章
const handleDelete = async (id) => {
  if (confirm('确定要删除这篇文章吗？')) {
    try {
      const res = await request.delete(`/api/article/delete/${id}`);
      if (res.code === 0) {
        alert('删除成功');
        getArticleList();
      } else {
        alert(res.message || '删除失败');
      }
    } catch (error) {
      console.error('删除文章失败:', error);
      alert('删除失败，请检查网络连接');
    }
  }
};

// 保存文章
const handleSave = async () => {
  if (!editForm.value.title || !editForm.value.content) {
    alert('请输入标题和内容');
    return;
  }
  
  try {
    let res;
    if (editingRecord.value) {
      // 更新文章
      res = await request.put(`/api/article/update/${editForm.value.id}`, editForm.value);
    } else {
      // 创建文章
      res = await request.post('/api/article/create', editForm.value);
    }
    
    if (res.code === 0) {
      alert('操作成功');
      modalVisible.value = false;
      getArticleList();
    } else {
      alert(res.message || '操作失败');
    }
  } catch (error) {
    console.error('保存文章失败:', error);
    alert('操作失败，请检查网络连接');
  }
};

// 取消
const handleCancel = () => {
  modalVisible.value = false;
};

// 初始化
onMounted(() => {
  getArticleList();
  getCategories();
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