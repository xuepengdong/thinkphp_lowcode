<template>
  <a-modal
    :title="modalTitle"
    :visible="visible"
    @update:visible="handleModalClose"
    width="600px"
    :footer="null"
  >
    <!-- 导入模式 -->
    <template v-if="mode === 'import'">
      <div class="import-container">
        <div class="import-step">
          <h4>步骤1：下载导入模板</h4>
          <p>点击下方按钮下载导入模板，按照模板格式填写数据</p>
          <a-button type="primary" size="small" @click="handleDownloadTemplate">
            <DownloadOutlined /> 下载导入模板
          </a-button>
        </div>

        <div class="import-step">
          <h4>步骤2：选择关联表（可选）</h4>
          <p>选择需要关联的表，导入时会自动处理关联字段</p>
          <div v-if="relationTables.length > 0" class="relation-table-list">
            <div 
              v-for="table in relationTables" 
              :key="table.table_name"
              class="relation-table-item"
            >
              <input 
                type="checkbox" 
                :checked="selectedRelations.includes(table.table_name)"
                @change="toggleRelation(table.table_name)"
              />
              <span>{{ table.table_name }} ({{ table.display_field }})</span>
            </div>
          </div>
          <p v-else class="empty-tip">暂无可用的关联表</p>
        </div>

        <div class="import-step">
          <h4>步骤3：上传文件</h4>
          <p>选择已填写数据的Excel文件进行导入</p>
          <a-upload
            :show-upload-list="false"
            :before-upload="handleBeforeUpload"
            accept=".xls,.xlsx"
            :disabled="importing"
          >
            <a-button :disabled="importing">
              <UploadOutlined /> {{ importing ? '导入中...' : '选择文件' }}
            </a-button>
          </a-upload>
          <p v-if="uploadedFile" class="file-info">已选择：{{ uploadedFile.name }}</p>
        </div>

        <div v-if="importResult" class="import-result">
          <a-alert
            :type="importResult.success ? 'success' : 'error'"
            :message="importResult.success ? '导入成功' : '导入失败'"
            :description="importResult.message"
            show-icon
          />
          <div v-if="importResult.details" class="result-details">
            <p>成功导入：{{ importResult.details.successCount }} 条</p>
            <p>失败：{{ importResult.details.failCount }} 条</p>
            <p v-if="importResult.details.errors" class="errors">
              错误信息：{{ importResult.details.errors.join('; ') }}
            </p>
          </div>
        </div>

        <div class="modal-footer">
          <a-button size="small" @click="visible = false">取消</a-button>
          <a-button 
            type="primary" 
            size="small" 
            @click="handleImport"
            :disabled="!uploadedFile || importing"
          >
            <UploadOutlined /> 开始导入
          </a-button>
        </div>
      </div>
    </template>

    <!-- 导出模式 -->
    <template v-else>
      <div class="export-container">
        <div class="export-step">
          <h4>步骤1：选择导出字段</h4>
          <p>选择需要导出的字段</p>
          <div class="field-list">
            <div 
              v-for="field in availableFields" 
              :key="field.fieldName"
              class="field-item"
            >
              <input 
                type="checkbox" 
                :checked="selectedFields.includes(field.fieldName)"
                @change="toggleField(field.fieldName)"
              />
              <span>{{ field.label }}</span>
            </div>
          </div>
        </div>

        <div class="export-step">
          <h4>步骤2：选择关联表（可选）</h4>
          <p>选择需要关联导出的表，导出时会包含关联字段</p>
          <div v-if="relationTables.length > 0" class="relation-table-list">
            <div 
              v-for="table in relationTables" 
              :key="table.table_name"
              class="relation-table-item"
            >
              <input 
                type="checkbox" 
                :checked="selectedRelations.includes(table.table_name)"
                @change="toggleRelation(table.table_name)"
              />
              <span>{{ table.table_name }} ({{ table.display_field }})</span>
            </div>
          </div>
          <p v-else class="empty-tip">暂无可用的关联表</p>
        </div>

        <div class="export-step">
          <h4>步骤3：设置导出条件（可选）</h4>
          <p>可以设置筛选条件，只导出符合条件的数据</p>
          <a-form :model="exportFilters">
            <a-form-item label="导出条数限制">
              <a-input-number 
                v-model:value="exportFilters.limit" 
                :min="1" 
                :max="10000"
                placeholder="不限制则为空"
              />
            </a-form-item>
          </a-form>
        </div>

        <div class="modal-footer">
          <a-button size="small" @click="visible = false">取消</a-button>
          <a-button 
            type="primary" 
            size="small" 
            @click="handleExport"
            :disabled="selectedFields.length === 0"
          >
            <DownloadOutlined /> 开始导出
          </a-button>
        </div>
      </div>
    </template>
  </a-modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { UploadOutlined, DownloadOutlined } from '@ant-design/icons-vue';
import { ImportExport } from '@/utils/importExport';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  mode: {
    type: String,
    default: 'import', // 'import' or 'export'
    validator: (value) => ['import', 'export'].includes(value)
  },
  objectId: {
    type: String,
    required: true
  },
  fields: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'success']);

const modalTitle = computed(() => props.mode === 'import' ? '数据导入' : '数据导出');

const relationTables = ref([]);
const selectedRelations = ref([]);
const selectedFields = ref([]);
const uploadedFile = ref(null);
const importing = ref(false);
const importResult = ref(null);

const availableFields = computed(() => props.fields);

const exportFilters = ref({
  limit: null
});

// 监听模式变化
watch(() => props.mode, () => {
  resetState();
});

// 监听显示变化
watch(() => props.visible, (val) => {
  if (val) {
    resetState();
    loadRelationTables();
    if (props.mode === 'export') {
      // 默认全选所有字段
      selectedFields.value = props.fields.map(f => f.fieldName);
    }
  }
});

const resetState = () => {
  selectedRelations.value = [];
  uploadedFile.value = null;
  importing.value = false;
  importResult.value = null;
  exportFilters.value = { limit: null };
  if (props.mode === 'export') {
    selectedFields.value = props.fields.map(f => f.fieldName);
  }
};

const handleModalClose = () => {
  emit('close');
};

const loadRelationTables = async () => {
  const response = await ImportExport.getRelationTables(props.objectId);
  if (response.success) {
    relationTables.value = response.data || [];
  }
};

const toggleRelation = (tableName) => {
  const index = selectedRelations.value.indexOf(tableName);
  if (index > -1) {
    selectedRelations.value.splice(index, 1);
  } else {
    selectedRelations.value.push(tableName);
  }
};

const toggleField = (fieldName) => {
  const index = selectedFields.value.indexOf(fieldName);
  if (index > -1) {
    selectedFields.value.splice(index, 1);
  } else {
    selectedFields.value.push(fieldName);
  }
};

const handleDownloadTemplate = async () => {
  const result = await ImportExport.getImportTemplate(props.objectId);
  if (!result.success) {
    alert(result.message);
  }
};

const handleBeforeUpload = (file) => {
  uploadedFile.value = file;
  importResult.value = null;
  return false; // 阻止自动上传
};

const handleImport = async () => {
  if (!uploadedFile.value) return;
  
  importing.value = true;
  importResult.value = null;
  
  const relationInfo = relationTables.value
    .filter(t => selectedRelations.value.includes(t.table_name))
    .map(t => ({
      tableName: t.table_name,
      keyField: t.key_field,
      displayField: t.display_field
    }));
  
  const result = await ImportExport.importData(props.objectId, uploadedFile.value, relationInfo);
  
  importing.value = false;
  importResult.value = result;
  
  if (result.success) {
    emit('success', { type: 'import', result });
  }
};

const handleExport = async () => {
  if (selectedFields.value.length === 0) return;
  
  const exportFields = props.fields.filter(f => selectedFields.value.includes(f.fieldName));
  
  const filters = { ...exportFilters.value };
  
  const result = await ImportExport.exportData(props.objectId, exportFields, filters);
  
  if (result.success) {
    emit('success', { type: 'export', result });
    emit('close');
  }
};
</script>

<style scoped>
.import-container,
.export-container {
  padding: 16px;
}

.import-step,
.export-step {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.import-step:last-child,
.export-step:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.import-step h4,
.export-step h4 {
  margin: 0 0 8px;
  font-size: 14px;
  font-weight: 600;
}

.import-step p,
.export-step p {
  margin: 0 0 12px;
  font-size: 13px;
  color: #666;
}

.relation-table-list,
.field-list {
  max-height: 200px;
  overflow-y: auto;
  padding: 8px;
  background: #fafafa;
  border-radius: 4px;
}

.relation-table-item,
.field-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  cursor: pointer;
}

.relation-table-item:hover,
.field-item:hover {
  background: #f0f0f0;
}

.relation-table-item input,
.field-item input {
  margin: 0;
}

.empty-tip {
  color: #999 !important;
}

.file-info {
  margin-top: 8px;
  font-size: 13px;
  color: #1890ff;
}

.import-result {
  margin-bottom: 16px;
}

.result-details {
  margin-top: 12px;
  padding: 12px;
  background: #fafafa;
  border-radius: 4px;
}

.result-details p {
  margin: 4px 0;
  font-size: 13px;
}

.result-details .errors {
  color: #f5222d;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
}
</style>
