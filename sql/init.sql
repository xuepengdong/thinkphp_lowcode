-- 创建用户表
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(100) NOT NULL COMMENT '密码',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `phone` varchar(20) NOT NULL COMMENT '电话',
  `role_id` int(11) NOT NULL DEFAULT 3 COMMENT '角色ID',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 创建角色表
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `code` varchar(50) NOT NULL COMMENT '角色标识',
  `description` text COMMENT '描述',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- 创建对象表
CREATE TABLE IF NOT EXISTS `objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL COMMENT '对象ID',
  `name_en` varchar(100) NOT NULL COMMENT '对象名称英文',
  `name_zh` varchar(100) NOT NULL COMMENT '对象名称中文',
  `type` varchar(20) NOT NULL DEFAULT 'normal' COMMENT '对象类型',
  `is_parent` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否父子级',
  `remark` text COMMENT '对象备注',
  `project` varchar(50) NOT NULL DEFAULT 'main' COMMENT '所属项目',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='对象表';

-- 创建对象字段表
CREATE TABLE IF NOT EXISTS `object_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL COMMENT '对象ID',
  `field_name_en` varchar(100) NOT NULL COMMENT '字段名称英文',
  `field_name_zh` varchar(100) NOT NULL COMMENT '字段名称中文',
  `field_type` varchar(50) NOT NULL COMMENT '字段类型',
  `is_unique` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否唯一',
  `remark` text COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='对象字段表';

-- 创建菜单表
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `path` varchar(255) NOT NULL COMMENT '菜单路径',
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT '父菜单ID',
  `object_id` varchar(50) DEFAULT NULL COMMENT '关联对象ID',
  `type` varchar(20) NOT NULL DEFAULT 'menu' COMMENT '菜单类型',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜单表';

-- 创建自定义触发器表
CREATE TABLE IF NOT EXISTS `object_triggers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL COMMENT '对象ID',
  `name` varchar(100) NOT NULL COMMENT '触发器名称',
  `description` text COMMENT '触发器描述',
  `event` varchar(50) NOT NULL COMMENT '触发事件',
  `condition` text COMMENT '触发条件',
  `action` text NOT NULL COMMENT '触发动作',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义触发器表';

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL COMMENT '对象ID',
  `name` varchar(100) NOT NULL COMMENT '页面名称',
  `page_type` varchar(50) NOT NULL COMMENT '页面类型',
  `is_api` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是接口',
  `menu_id` int(11) NOT NULL COMMENT '所属栏目ID',
  `path` varchar(255) NOT NULL COMMENT '页面路径',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='页面表';

-- 创建测试数据
-- 角色数据
INSERT INTO `roles` (`id`, `name`, `code`, `description`, `status`) VALUES
(1, '超级管理员', 'super_admin', '拥有系统全部权限', 'active'),
(2, '管理员', 'admin', '拥有大部分系统权限', 'active'),
(3, '普通用户', 'user', '可以浏览和评论内容', 'active');

-- 用户数据
INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `phone`, `role_id`, `status`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '管理员', 'admin@example.com', '13800138000', 1, 'active'),
(2, 'user1', 'e10adc3949ba59abbe56e057f20f883e', '普通用户', 'user1@example.com', '13800138001', 3, 'active'),
(3, 'editor', 'e10adc3949ba59abbe56e057f20f883e', '编辑', 'editor@example.com', '13800138002', 2, 'active');

-- 菜单数据
INSERT INTO `menus` (`id`, `name`, `path`, `parent_id`, `object_id`, `type`, `status`, `sort`) VALUES
(1, '基础管理', '/admin/basic', 0, NULL, 'menu', 'active', 1),
(2, '对象管理', '/admin/objects', 1, NULL, 'menu', 'active', 2),
(3, '市场管理', '/admin/market', 0, NULL, 'menu', 'active', 3),
(4, '咨询管理', '/admin/consult', 0, NULL, 'menu', 'active', 4),
(5, '财务管理', '/admin/finance', 0, NULL, 'menu', 'active', 5),
(6, '教学管理', '/admin/education', 0, NULL, 'menu', 'active', 6),
(7, '教师管理', '/admin/teacher', 0, NULL, 'menu', 'active', 7),
(8, '报表管理', '/admin/report', 0, NULL, 'menu', 'active', 8),
(9, '学员管理', '/admin/student', 0, NULL, 'menu', 'active', 9);

-- 对象测试数据
INSERT INTO `objects` (`id`, `object_id`, `name_en`, `name_zh`, `type`, `is_parent`, `remark`, `project`, `status`) VALUES
(1, 'TEST_OBJECT', 'TEST_OBJECT', '测试对象', 'normal', 0, '测试对象', 'main', 'active'),
(2, 'STUDENT_TAGS', 'STUDENT_TAGS', '客户标签', 'normal', 0, '', 'main', 'active'),
(3, 'POSTGRADUATE_CONFIRMATION', 'POSTGRADUATE_CONFIRMATION', '保研院校最终确认', 'normal', 0, '', 'main', 'active'),
(4, 'SUBJECT_TYPE', 'SUBJECT_TYPE', '学科分类', 'normal', 1, '', 'main', 'active');

-- 对象字段测试数据
INSERT INTO `object_fields` (`id`, `object_id`, `field_name_en`, `field_name_zh`, `field_type`, `is_unique`, `remark`) VALUES
(1, 'TEST_OBJECT', 'NAME', '名称', '文本', 0, '对象名称'),
(2, 'TEST_OBJECT', 'DESCRIPTION', '描述', '富文本', 0, '对象描述'),
(3, 'TEST_OBJECT', 'STATUS', '状态', '单选选项', 0, '对象状态'),
(4, 'STUDENT_TAGS', 'COMM_REQUIREMENT', '沟通要求', '文本', 0, ''),
(5, 'STUDENT_TAGS', 'COMM_FREQUENCY', '沟通频次', '文本', 0, ''),
(6, 'STUDENT_TAGS', 'CURRENT_STATUS', '当前状态', '文本', 0, ''),
(7, 'STUDENT_TAGS', 'STU_ID', '学员', '文本', 0, '');