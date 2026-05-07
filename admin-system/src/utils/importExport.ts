import request from './request';

// 导入导出工具类
export const ImportExport = {
  // 导出数据
  async exportData(objectId: string, fields: Array<{ fieldName: string; label: string }>, filters: Record<string, any> = {}) {
    try {
      const response = await request.get('/api/object/export', {
        params: {
          object_id: objectId,
          fields: JSON.stringify(fields),
          filters: JSON.stringify(filters)
        },
        responseType: 'blob'
      });
      
      // 处理文件下载
      const blob = new Blob([response], { type: 'application/vnd.ms-excel' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `export_${objectId}_${Date.now()}.xls`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
      
      return { success: true, message: '导出成功' };
    } catch (error) {
      console.error('导出失败:', error);
      return { success: false, message: '导出失败' };
    }
  },

  // 获取导入模板
  async getImportTemplate(objectId: string) {
    try {
      const response = await request.get('/api/object/import/template', {
        params: { object_id: objectId },
        responseType: 'blob'
      });
      
      const blob = new Blob([response], { type: 'application/vnd.ms-excel' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `import_template_${objectId}.xls`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
      
      return { success: true, message: '模板下载成功' };
    } catch (error) {
      console.error('获取模板失败:', error);
      return { success: false, message: '获取模板失败' };
    }
  },

  // 导入数据
  async importData(objectId: string, file: File, relationTables: Array<{ tableName: string; keyField: string; displayField: string }> = []) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('object_id', objectId);
      if (relationTables.length > 0) {
        formData.append('relation_tables', JSON.stringify(relationTables));
      }
      
      const response = await request.post('/api/object/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      return response;
    } catch (error) {
      console.error('导入失败:', error);
      return { success: false, message: '导入失败' };
    }
  },

  // 获取关联表列表
  async getRelationTables(objectId: string) {
    try {
      const response = await request.get('/api/object/relation-tables', {
        params: { object_id: objectId }
      });
      return response;
    } catch (error) {
      console.error('获取关联表失败:', error);
      return { success: false, message: '获取关联表失败', data: [] };
    }
  }
};

export default ImportExport;
