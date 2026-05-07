<template>
  <Modal
    :visible="visible"
    title="修改页面设置"
    width="1200px"
    :height="800"
    @ok="handleOk"
    @cancel="handleCancel"
    @update:visible="handleModalClose"
    okText="保存"
    cancelText="取消"
  >
    <div class="edit-page-setting">
      <!-- 左侧显示区域 -->
      <div class="left-panel">
        <div class="panel-header">
          <h4>显示区域</h4>
          <Button size="small" type="primary" @click="addDisplayBlock">
            <PlusOutlined /> 添加显示块
          </Button>
        </div>
        
        <!-- 显示块列表 -->
        <div class="display-blocks">
          <div 
            v-for="(block, blockIndex) in displayBlocks" 
            :key="block.id"
            class="display-block"
            :class="{ 'selected': selectedBlock === block.id }"
            @click="selectBlock(block.id)"
          >
            <div class="block-header">
              <Input 
                v-model:value="block.name" 
                placeholder="显示块名称"
                class="block-name-input"
              />
              <div class="block-actions">
                <Button size="small" @click.stop="moveBlockUp(blockIndex)">
                  <UpOutlined />
                </Button>
                <Button size="small" @click.stop="moveBlockDown(blockIndex)">
                  <DownOutlined />
                </Button>
                <Button size="small" danger @click.stop="deleteBlock(blockIndex)">
                  <DeleteOutlined />
                </Button>
              </div>
            </div>
            <div 
              class="block-fields"
              :class="{ 'drag-over': dropTargetBlock === block.id }"
              @dragover.prevent="handleDragOver($event, block.id)"
              @dragleave="handleDragLeave"
              @drop="handleDrop($event, block.id)"
            >
              <div 
                v-for="(field, fieldIndex) in block.fields" 
                :key="field.fieldName"
                class="field-item"
                @click.stop="selectField(block.id, field.fieldName)"
              >
                <component :is="field.icon" v-if="field.icon" />
                <span>{{ field.label }}</span>
                <a @click.stop="removeField(block.id, fieldIndex)">×</a>
              </div>
              <div v-if="block.fields.length === 0" class="empty-block">
                拖拽字段到这里
              </div>
            </div>
            <!-- 列数设置 -->
            <div class="block-columns">
              <label>列数：</label>
              <Select v-model:value="block.columns" style="width: 80px">
                <Select.Option :value="1">1列</Select.Option>
                <Select.Option :value="2">2列</Select.Option>
                <Select.Option :value="3">3列</Select.Option>
              </Select>
            </div>
          </div>
        </div>
      </div>

      <!-- 右侧字段选择 -->
      <div class="right-panel">
        <div class="panel-header">
          <h4>字段选择</h4>
        </div>
        <div class="field-groups">
          <div v-for="group in fieldGroups" :key="group.name" class="field-group">
            <h5>{{ group.name }}</h5>
            <div class="field-list">
              <div 
                v-for="field in group.fields" 
                :key="field.fieldName"
                class="field-option"
                :class="{ 'dragging': draggingField?.fieldName === field.fieldName }"
                draggable="true"
                @dragstart="handleDragStart($event, field)"
                @dragend="handleDragEnd"
              >
                <component :is="field.icon" v-if="field.icon" />
                <span>{{ field.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import { Modal, Button, Input, Select } from 'ant-design-vue';
import { PlusOutlined, UpOutlined, DownOutlined, DeleteOutlined, TagOutlined, CalendarOutlined, FileTextOutlined } from '@ant-design/icons-vue';

const props = defineProps({
  visible: Boolean,
  objectId: String,
  fields: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:visible', 'saved']);

// 显示块列表
const displayBlocks = ref([
  { id: 'block1', name: '基本信息', columns: 2, fields: [] },
  { id: 'block2', name: '其他信息', columns: 2, fields: [] }
]);

// 当前选中的块
const selectedBlock = ref('block1');

// 当前拖拽的字段
const draggingField = ref(null);

// 当前拖放目标块
const dropTargetBlock = ref(null);

// 字段分组
const fieldGroups = ref([]);

// 初始化字段分组
const initFieldGroups = () => {
  const groups = [];
  // 将字段按类型分组
  const systemFields = [
    { fieldName: 'id', label: 'ID', icon: TagOutlined },
    { fieldName: 'created_at', label: '创建时间', icon: CalendarOutlined },
    { fieldName: 'updated_at', label: '更新时间', icon: CalendarOutlined }
  ];
  
  const customFields = props.fields.map(f => ({
    fieldName: f.fieldName || f.field_name_en,
    label: f.label || f.field_name_zh,
    icon: FileTextOutlined
  }));
  
  groups.push({ name: '系统字段', fields: systemFields });
  groups.push({ name: '自定义字段', fields: customFields });
  
  fieldGroups.value = groups;
};

// 选择块
const selectBlock = (blockId) => {
  selectedBlock.value = blockId;
};

// 添加显示块
const addDisplayBlock = () => {
  const newId = 'block' + Date.now();
  displayBlocks.value.push({
    id: newId,
    name: '新显示块',
    columns: 2,
    fields: []
  });
  selectedBlock.value = newId;
};

// 删除显示块
const deleteBlock = (index) => {
  if (displayBlocks.value.length > 1) {
    displayBlocks.value.splice(index, 1);
    selectedBlock.value = displayBlocks.value[0]?.id || '';
  }
};

// 向上移动显示块
const moveBlockUp = (index) => {
  if (index > 0) {
    const temp = displayBlocks.value[index];
    displayBlocks.value[index] = displayBlocks.value[index - 1];
    displayBlocks.value[index - 1] = temp;
  }
};

// 向下移动显示块
const moveBlockDown = (index) => {
  if (index < displayBlocks.value.length - 1) {
    const temp = displayBlocks.value[index];
    displayBlocks.value[index] = displayBlocks.value[index + 1];
    displayBlocks.value[index + 1] = temp;
  }
};

// 选择字段
const selectField = (blockId, fieldName) => {
  // 可以添加字段配置逻辑
};

// 从块中移除字段
const removeField = (blockId, fieldIndex) => {
  const block = displayBlocks.value.find(b => b.id === blockId);
  if (block) {
    block.fields.splice(fieldIndex, 1);
  }
};

// 拖拽开始
const handleDragStart = (event, field) => {
  draggingField.value = field;
  event.dataTransfer.effectAllowed = 'copy';
  event.dataTransfer.setData('text/plain', JSON.stringify(field));
};

// 拖拽经过
const handleDragOver = (event, blockId) => {
  event.preventDefault();
  dropTargetBlock.value = blockId;
};

// 拖拽离开
const handleDragLeave = () => {
  dropTargetBlock.value = null;
};

// 放置字段
const handleDrop = (event, blockId) => {
  event.preventDefault();
  
  if (draggingField.value) {
    const block = displayBlocks.value.find(b => b.id === blockId);
    if (block) {
      // 检查字段是否已存在
      const exists = block.fields.some(f => f.fieldName === draggingField.value.fieldName);
      if (!exists) {
        block.fields.push({ ...draggingField.value });
      }
    }
  }
  
  draggingField.value = null;
  dropTargetBlock.value = null;
};

// 拖拽结束
const handleDragEnd = () => {
  draggingField.value = null;
  dropTargetBlock.value = null;
};

// 保存设置
const handleOk = () => {
  const settings = {
    displayBlocks: displayBlocks.value,
    objectId: props.objectId,
    updatedAt: Date.now()
  };
  localStorage.setItem(`editPageSettings_${props.objectId}`, JSON.stringify(settings));
  emit('saved', settings);
  emit('update:visible', false);
};

// 取消
const handleCancel = () => {
  emit('update:visible', false);
};

// 弹窗关闭处理
const handleModalClose = (val) => {
  emit('update:visible', val);
};

// 监听visible变化，加载保存的设置
import { watch } from 'vue';
watch(() => props.visible, (val) => {
  if (val) {
    initFieldGroups();
    loadSettings();
  }
});

// 加载保存的设置
const loadSettings = () => {
  try {
    const saved = localStorage.getItem(`editPageSettings_${props.objectId}`);
    if (saved) {
      const settings = JSON.parse(saved);
      displayBlocks.value = settings.displayBlocks || [
        { id: 'block1', name: '基本信息', columns: 2, fields: [] }
      ];
    }
  } catch (e) {
    console.error('加载修改页面设置失败:', e);
  }
};
</script>

<style scoped>
.edit-page-setting {
  display: flex;
  gap: 20px;
  height: 650px;
}

.left-panel, .right-panel {
  flex: 1;
  border: 1px solid #e8e8e8;
  border-radius: 8px;
  overflow: hidden;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #fafafa;
  border-bottom: 1px solid #e8e8e8;
}

.panel-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
}

.display-blocks {
  padding: 12px;
  overflow-y: auto;
  height: calc(100% - 52px);
}

.display-block {
  margin-bottom: 12px;
  padding: 12px;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.display-block:hover {
  border-color: #1890ff;
}

.display-block.selected {
  border-color: #1890ff;
  background: #e6f7ff;
}

.block-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.block-name-input {
  flex: 1;
  max-width: 150px;
}

.block-actions {
  display: flex;
  gap: 4px;
}

.block-fields {
  min-height: 80px;
  padding: 8px;
  background: #fafafa;
  border-radius: 4px;
  border: 2px dashed #d9d9d9;
  transition: all 0.2s;
}

.block-fields.drag-over {
  background: #e6f7ff;
  border-color: #1890ff;
  border-style: solid;
}

.field-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  margin: 4px;
  font-size: 13px;
}

.field-item a {
  cursor: pointer;
  color: #999;
  margin-left: 4px;
}

.field-item a:hover {
  color: #ff4d4f;
}

.empty-block {
  color: #999;
  font-size: 13px;
  text-align: center;
  line-height: 64px;
}

.block-columns {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  font-size: 13px;
  color: #666;
}

.field-groups {
  padding: 12px;
  overflow-y: auto;
  height: calc(100% - 52px);
}

.field-group {
  margin-bottom: 16px;
}

.field-group h5 {
  margin: 0 0 8px 0;
  font-size: 13px;
  font-weight: 600;
  color: #333;
}

.field-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.field-option {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 4px;
  cursor: grab;
  font-size: 13px;
  color: #666;
  transition: all 0.2s;
}

.field-option:hover {
  background: #e6f7ff;
  border-color: #1890ff;
}

.field-option:active {
  cursor: grabbing;
}

.field-option.dragging {
  opacity: 0.5;
  transform: scale(0.95);
}
</style>
