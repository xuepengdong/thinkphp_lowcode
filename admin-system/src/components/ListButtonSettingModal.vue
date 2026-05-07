<template>
  <Modal
    :visible="visible"
    title="列表页面按钮设置"
    width="900px"
    @ok="handleOk"
    @cancel="handleCancel"
    @update:visible="handleModalClose"
    okText="保存"
    cancelText="取消"
  >
    <div class="button-setting-container">
      <!-- 按钮展现形式 -->
      <div class="form-row">
        <label class="form-label">按钮展现形式:</label>
        <Select v-model:value="displayMode" style="width: 150px">
          <Option value="normal">普通</Option>
          <Option value="icon">图标</Option>
        </Select>
      </div>

      <!-- 表格按钮组 -->
      <div class="button-groups-section">
        <h4>按钮组配置</h4>
        <Table
          :data-source="buttonGroups"
          :columns="groupColumns"
          :pagination="false"
          bordered
          row-key="id"
        >
          <template #bodyCell="{ record, column, index }">
            <template v-if="column.key === 'index'">
              {{ index + 1 }}
            </template>
            <template v-else-if="column.key === 'buttons'">
              <div 
                class="group-buttons"
                @dragover.prevent="handleDragOver($event, record.id)"
                @drop="handleDrop($event, record.id)"
                :class="{ 'drag-over': dropTargetGroup === record.id }"
              >
                <span 
                  v-for="btn in record.buttons" 
                  :key="btn"
                  class="btn-tag"
                >
                  {{ getButtonLabel(btn) }}
                  <a @click="removeButtonFromGroup(record.id, btn)">×</a>
                </span>
                <span v-if="record.buttons.length === 0" class="empty-hint">
                  拖拽按钮到这里
                </span>
              </div>
            </template>
            <template v-else-if="column.key === 'name'">
              <Input 
                v-model:value="record.name" 
                placeholder="输入按钮组名称"
                style="width: 120px"
              />
            </template>
            <template v-else-if="column.key === 'actions'">
              <Space size="small">
                <Button size="small" @click="moveGroupUp(record.id)">
                  <UpOutlined />
                </Button>
                <Button size="small" @click="moveGroupDown(record.id)">
                  <DownOutlined />
                </Button>
                <Button size="small" danger @click="deleteGroup(record.id)">
                  <DeleteOutlined />
                </Button>
              </Space>
            </template>
          </template>
        </Table>
        <Button size="small" type="primary" @click="addButtonGroup">
          <PlusOutlined /> 添加按钮组
        </Button>
      </div>

      <!-- 按钮选择区 -->
      <div class="button-select-section">
        <h4>按钮选择（拖动到按钮组中）</h4>
        <div class="button-pool">
          <div 
            v-for="btn in availableButtons" 
            :key="btn.key"
            class="available-btn"
            draggable="true"
            @dragstart="handleDragStart($event, btn.key)"
            @dragend="handleDragEnd"
          >
            <component :is="btn.icon" />
            <span>{{ btn.label }}</span>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>import { ref, reactive } from 'vue';
import { Modal, Table, Select, Input, Button, Space } from 'ant-design-vue';
const { Option } = Select;
import { PlusOutlined, UpOutlined, DownOutlined, DeleteOutlined, EyeOutlined, EditOutlined, CopyOutlined, PrinterOutlined, PlusCircleOutlined, BarChartOutlined, DownloadOutlined, FilterOutlined } from '@ant-design/icons-vue';
const props = defineProps({
 visible: Boolean,
 objectId: String
});
const emit = defineEmits(['update:visible', 'saved']);
// 按钮展现形式
const displayMode = ref('normal');
// 可用按钮列表
const availableButtons = [
 { key: 'view', label: '查看', icon: EyeOutlined },
 { key: 'edit', label: '修改', icon: EditOutlined },
 { key: 'delete', label: '删除', icon: DeleteOutlined },
 { key: 'conditionDelete', label: '条件删除', icon: FilterOutlined },
 { key: 'copy', label: '复制', icon: CopyOutlined },
 { key: 'add', label: '添加', icon: PlusCircleOutlined },
 { key: 'custom', label: '自定义按钮', icon: BarChartOutlined },
 { key: 'print', label: '打印', icon: PrinterOutlined },
 { key: 'direct', label: '直接操作', icon: BarChartOutlined },
 { key: 'download', label: '一键下载', icon: DownloadOutlined },
 { key: 'stage', label: '阶段', icon: BarChartOutlined }
];
// 按钮组列表
const buttonGroups = ref([
 { id: 'group1', name: '基础操作', buttons: ['view', 'edit', 'delete'] },
 { id: 'group2', name: '其他操作', buttons: ['copy', 'print'] }
]);
// 当前拖拽的按钮
const draggingButton = ref(null);
// 当前放置目标组
const dropTargetGroup = ref(null);
// 按钮组表格列配置
const groupColumns = [
 { title: '序号', key: 'index', width: 60 },
 { title: '按钮组名称', key: 'name', width: 150 },
 { title: '包含按钮', key: 'buttons' },
 { title: '操作', key: 'actions', width: 120 }
];
// 获取按钮标签
const getButtonLabel = (key) => {
 const btn = availableButtons.find(b => b.key === key);
 return btn ? btn.label : key;
};
// 添加按钮组
const addButtonGroup = () => {
 const newId = 'group' + Date.now();
 buttonGroups.value.push({
 id: newId,
 name: '新按钮组',
 buttons: []
 });
};
// 删除按钮组
const deleteGroup = (groupId) => {
 const index = buttonGroups.value.findIndex(g => g.id === groupId);
 if (index > -1) {
 buttonGroups.value.splice(index, 1);
 }
};
// 向上移动按钮组
const moveGroupUp = (groupId) => {
 const index = buttonGroups.value.findIndex(g => g.id === groupId);
 if (index > 0) {
 const temp = buttonGroups.value[index];
 buttonGroups.value[index] = buttonGroups.value[index - 1];
 buttonGroups.value[index - 1] = temp;
 }
};
// 向下移动按钮组
const moveGroupDown = (groupId) => {
 const index = buttonGroups.value.findIndex(g => g.id === groupId);
 if (index < buttonGroups.value.length - 1) {
 const temp = buttonGroups.value[index];
 buttonGroups.value[index] = buttonGroups.value[index + 1];
 buttonGroups.value[index + 1] = temp;
 }
};
// 从按钮组移除按钮
const removeButtonFromGroup = (groupId, buttonKey) => {
 const group = buttonGroups.value.find(g => g.id === groupId);
 if (group) {
 const index = group.buttons.indexOf(buttonKey);
 if (index > -1) {
 group.buttons.splice(index, 1);
 }
 }
};
// 拖拽开始
const handleDragStart = (event, buttonKey) => {
 draggingButton.value = buttonKey;
 event.dataTransfer.effectAllowed = 'copy';
 event.dataTransfer.setData('text/plain', buttonKey);
};
// 拖拽结束
const handleDragEnd = () => {
 draggingButton.value = null;
 dropTargetGroup.value = null;
};
// 拖拽经过
const handleDragOver = (event, groupId) => {
 event.preventDefault();
 dropTargetGroup.value = groupId;
};
// 放置按钮
const handleDrop = (event, groupId) => {
 event.preventDefault();
 if (draggingButton.value) {
 const group = buttonGroups.value.find(g => g.id === groupId);
 if (group && !group.buttons.includes(draggingButton.value)) {
 group.buttons.push(draggingButton.value);
 }
 }
 draggingButton.value = null;
 dropTargetGroup.value = null;
};
// 保存设置
const handleOk = () => {
 const settings = {
 displayMode: displayMode.value,
 buttonGroups: buttonGroups.value,
 objectId: props.objectId,
 updatedAt: Date.now()
 };
 localStorage.setItem(`listButtonSettings_${props.objectId}`, JSON.stringify(settings));
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
 if (val && props.objectId) {
 loadSettings();
 }
});
// 加载保存的设置
const loadSettings = () => {
 try {
 const saved = localStorage.getItem(`listButtonSettings_${props.objectId}`);
 if (saved) {
 const settings = JSON.parse(saved);
 displayMode.value = settings.displayMode || 'normal';
 buttonGroups.value = settings.buttonGroups || [
 { id: 'group1', name: '基础操作', buttons: ['view', 'edit', 'delete'] }
 ];
 }
 }
 catch (e) {
 console.error('加载列表按钮设置失败:', e);
 }
};
</script>

<style scoped>
.button-setting-container {
  padding: 10px;
}

.form-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.form-label {
  font-size: 14px;
  font-weight: 500;
}

.button-groups-section {
  margin-bottom: 20px;
}

.button-groups-section h4 {
  margin-bottom: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.group-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  min-height: 32px;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.group-buttons.drag-over {
  background: #e6f7ff;
  border: 2px dashed #1890ff;
}

.empty-hint {
  color: #999;
  font-size: 12px;
  line-height: 24px;
}

.btn-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: #e6f7ff;
  border-radius: 4px;
  font-size: 12px;
  color: #1890ff;
}

.btn-tag a {
  cursor: pointer;
  color: #91caff;
}

.btn-tag a:hover {
  color: #1890ff;
}

.button-select-section {
  border-top: 1px solid #e8e8e8;
  padding-top: 16px;
}

.button-select-section h4 {
  margin-bottom: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.button-pool {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 15px;
  background: #fafafa;
  border: 2px dashed #d9d9d9;
  border-radius: 8px;
  min-height: 60px;
}

.available-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #fff;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  cursor: grab;
  font-size: 13px;
  color: #666;
  transition: all 0.2s;
}

.available-btn:hover {
  background: #e6f7ff;
  border-color: #1890ff;
}

.available-btn:active {
  cursor: grabbing;
}
</style>
