/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 80045
Source Host           : localhost:3306
Source Database       : thinkphp

Target Server Type    : MYSQL
Target Server Version : 80045
File Encoding         : 65001

Date: 2026-04-30 20:11:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for menus
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'èœå•åç§°',
  `path` varchar(255) NOT NULL COMMENT 'èœå•è·¯å¾„',
  `parent_id` int NOT NULL DEFAULT '0' COMMENT 'çˆ¶èœå•ID',
  `object_id` varchar(50) DEFAULT NULL COMMENT 'å…³è”å¯¹è±¡ID',
  `type` varchar(20) NOT NULL DEFAULT 'menu' COMMENT 'èœå•ç±»åž‹',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'çŠ¶æ€',
  `sort` int NOT NULL DEFAULT '0' COMMENT 'æŽ’åº',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'åˆ›å»ºæ—¶é—´',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'æ›´æ–°æ—¶é—´',
  `call_type` varchar(20) DEFAULT 'url' COMMENT 'è°ƒç”¨æ–¹å¼: page/url',
  `page_name` varchar(100) DEFAULT '' COMMENT 'é¡µé¢åç§°',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1015 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='èœå•è¡¨';

-- ----------------------------
-- Records of menus
-- ----------------------------
INSERT INTO `menus` VALUES ('2', '对象管理', '/admin/objects', '0', null, 'menu', 'inactive', '2', '2026-04-18 09:28:59', '2026-04-30 07:27:49', 'url', '');
INSERT INTO `menus` VALUES ('3', '市场', '/admin/market', '0', null, 'menu', 'inactive', '3', '2026-04-18 09:28:59', '2026-04-30 15:28:16', 'url', '');
INSERT INTO `menus` VALUES ('4', '咨询', '/admin/consult', '0', null, 'menu', 'active', '4', '2026-04-18 09:28:59', '2026-04-23 02:19:31', 'url', '');
INSERT INTO `menus` VALUES ('5', '金融', '/admin/finance', '0', null, 'menu', 'inactive', '5', '2026-04-18 09:28:59', '2026-04-30 15:28:24', 'url', '');
INSERT INTO `menus` VALUES ('6', '教育', '/admin/education', '0', null, 'menu', 'inactive', '6', '2026-04-18 09:28:59', '2026-04-30 15:28:26', 'url', '');
INSERT INTO `menus` VALUES ('7', '教师', '/admin/teacher', '0', null, 'menu', 'inactive', '7', '2026-04-18 09:28:59', '2026-04-30 15:28:28', 'url', '');
INSERT INTO `menus` VALUES ('8', '导出', '/admin/report', '0', null, 'menu', 'inactive', '8', '2026-04-18 09:28:59', '2026-04-30 15:28:31', 'url', '');
INSERT INTO `menus` VALUES ('9', '学生', '/admin/student', '0', null, 'menu', 'inactive', '9', '2026-04-18 09:28:59', '2026-04-30 15:28:33', 'url', '');
INSERT INTO `menus` VALUES ('11', 'test2', '/page/OBJ_1776840141351/list', '4', 'OBJ_1776840141351', 'menu', 'active', '0', '2026-04-23 10:39:28', '2026-04-23 10:39:28', 'url', '');
INSERT INTO `menus` VALUES ('1001', '系统管理', '/system', '0', '', 'menu', 'active', '1', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1002', '用户管理', '/system/user', '1001', '', 'menu', 'active', '1', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1003', '角色管理', '/system/role', '1001', '', 'menu', 'active', '2', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1004', '权限管理', '/system/permission', '1001', '', 'menu', 'active', '3', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1005', '对象管理', '/system/object', '1001', '', 'menu', 'active', '4', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1006', '页面管理', '/system/page', '1001', '', 'menu', 'active', '5', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1007', '栏目管理', '/system/menu', '1001', '', 'menu', 'active', '6', '2026-04-23 05:35:49', '2026-04-23 05:35:49', 'url', '');
INSERT INTO `menus` VALUES ('1008', '内容管理', '/content', '0', '', 'menu', 'active', '2', '2026-04-30 08:01:29', '2026-04-30 08:01:29', 'url', '');
INSERT INTO `menus` VALUES ('1009', '文章管理', '/content/article', '1008', '', 'menu', 'active', '1', '2026-04-30 08:01:29', '2026-04-30 08:01:29', 'url', '');
INSERT INTO `menus` VALUES ('1010', '分类管理', '/content/category', '1008', '', 'menu', 'active', '2', '2026-04-30 08:01:29', '2026-04-30 08:01:29', 'url', '');
INSERT INTO `menus` VALUES ('1013', 'test2_1', '1', '11', 'OBJ_1776840141351', 'menu', 'active', '0', '2026-04-30 15:01:38', '2026-04-30 09:22:27', 'page', 'test2（复制）');
INSERT INTO `menus` VALUES ('1014', '测试', '1', '1013', 'OBJ_1776850542729', 'menu', 'active', '0', '2026-04-30 17:46:08', '2026-04-30 17:46:08', 'page', 'test2（复制）');

-- ----------------------------
-- Table structure for object_fields
-- ----------------------------
DROP TABLE IF EXISTS `object_fields`;
CREATE TABLE `object_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL COMMENT 'å¯¹è±¡ID',
  `field_name_en` varchar(100) NOT NULL COMMENT 'å­—æ®µåç§°è‹±æ–‡',
  `field_name_zh` varchar(100) NOT NULL COMMENT 'å­—æ®µåç§°ä¸­æ–‡',
  `field_type` varchar(50) NOT NULL COMMENT 'å­—æ®µç±»åž‹',
  `is_unique` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'æ˜¯å¦å”¯ä¸€',
  `remark` text COMMENT 'å¤‡æ³¨',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'åˆ›å»ºæ—¶é—´',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'æ›´æ–°æ—¶é—´',
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='å¯¹è±¡å­—æ®µè¡¨';

-- ----------------------------
-- Records of object_fields
-- ----------------------------
INSERT INTO `object_fields` VALUES ('1', 'TEST_OBJECT', 'NAME', 'åç§°', 'æ–‡æœ¬', '0', 'å¯¹è±¡åç§°', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('2', 'TEST_OBJECT', 'DESCRIPTION', 'æè¿°', 'å¯Œæ–‡æœ¬', '0', 'å¯¹è±¡æè¿°', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('3', 'TEST_OBJECT', 'STATUS', 'çŠ¶æ€', 'å•é€‰é€‰é¡¹', '0', 'å¯¹è±¡çŠ¶æ€', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('4', 'STUDENT_TAGS', 'COMM_REQUIREMENT', 'æ²Ÿé€šè¦æ±‚', 'æ–‡æœ¬', '0', '', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('5', 'STUDENT_TAGS', 'COMM_FREQUENCY', 'æ²Ÿé€šé¢‘æ¬¡', 'æ–‡æœ¬', '0', '', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('6', 'STUDENT_TAGS', 'CURRENT_STATUS', 'å½“å‰çŠ¶æ€', 'æ–‡æœ¬', '0', '', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('7', 'STUDENT_TAGS', 'STU_ID', 'å­¦å‘˜', 'æ–‡æœ¬', '0', '', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `object_fields` VALUES ('8', 'OBJ_1776505438739', 'test', 'test', 'text', '0', '', '2026-04-18 17:44:29', '2026-04-18 17:44:29');
INSERT INTO `object_fields` VALUES ('9', 'OBJ_1776505438739', 'test2', 'test2', 'text', '0', '', '2026-04-18 17:44:29', '2026-04-18 17:44:29');
INSERT INTO `object_fields` VALUES ('30', 'OBJ_1776840141351', 'isqianyue', '是否已签约', 'radio', '0', '', '2026-04-22 16:13:57', '2026-04-22 16:13:57');
INSERT INTO `object_fields` VALUES ('31', 'OBJ_1776840141351', 'lunbo', '轮播图', 'document', '0', '', '2026-04-22 16:13:57', '2026-04-22 16:13:57');
INSERT INTO `object_fields` VALUES ('32', 'OBJ_1776840141351', 'fuwenben', '富文本', 'rich_text', '0', '', '2026-04-22 16:13:57', '2026-04-22 16:13:57');
INSERT INTO `object_fields` VALUES ('33', 'OBJ_1776840141351', 'fuwenben2', '富文本2', 'rich_text', '0', '', '2026-04-22 16:13:58', '2026-04-22 16:13:58');
INSERT INTO `object_fields` VALUES ('34', 'OBJ_1776840141351', 'fuwenben3', '富文本3', 'rich_text', '0', '', '2026-04-22 16:13:58', '2026-04-22 16:13:58');
INSERT INTO `object_fields` VALUES ('40', 'OBJ_1776850542729', 'isqianyue', '是否已签约', 'radio', '0', '', '2026-04-25 16:48:06', '2026-04-25 16:48:06');
INSERT INTO `object_fields` VALUES ('41', 'OBJ_1776850542729', 'lunbo', '轮播图', 'document', '0', '', '2026-04-25 16:48:06', '2026-04-25 16:48:06');
INSERT INTO `object_fields` VALUES ('42', 'OBJ_1776850542729', 'fuwenben', '富文本', 'rich_text', '0', '', '2026-04-25 16:48:06', '2026-04-25 16:48:06');
INSERT INTO `object_fields` VALUES ('43', 'OBJ_1776850542729', 'fuwenben2', '富文本2', 'rich_text', '0', '', '2026-04-25 16:48:07', '2026-04-25 16:48:07');
INSERT INTO `object_fields` VALUES ('44', 'OBJ_1776850542729', 'fuwenben3', '富文本3', 'rich_text', '0', '', '2026-04-25 16:48:07', '2026-04-25 16:48:07');
INSERT INTO `object_fields` VALUES ('45', 'OBJ_1776850542729', 'fuwenben4', '富文本4', 'rich_text', '0', '', '2026-04-25 16:48:07', '2026-04-25 16:48:07');
INSERT INTO `object_fields` VALUES ('46', 'OBJ_1776850542729', 'fuwenben5', '富文本5', 'rich_text', '0', '', '2026-04-25 16:48:08', '2026-04-25 16:48:08');

-- ----------------------------
-- Table structure for objects
-- ----------------------------
DROP TABLE IF EXISTS `objects`;
CREATE TABLE `objects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL COMMENT 'å¯¹è±¡ID',
  `name_en` varchar(100) NOT NULL COMMENT 'å¯¹è±¡åç§°è‹±æ–‡',
  `name_zh` varchar(100) NOT NULL COMMENT 'å¯¹è±¡åç§°ä¸­æ–‡',
  `type` varchar(20) NOT NULL DEFAULT 'normal' COMMENT 'å¯¹è±¡ç±»åž‹',
  `is_parent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'æ˜¯å¦çˆ¶å­çº§',
  `remark` text COMMENT 'å¯¹è±¡å¤‡æ³¨',
  `project` varchar(50) NOT NULL DEFAULT 'main' COMMENT 'æ‰€å±žé¡¹ç›®',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'çŠ¶æ€',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'åˆ›å»ºæ—¶é—´',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'æ›´æ–°æ—¶é—´',
  PRIMARY KEY (`id`),
  UNIQUE KEY `object_id` (`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='å¯¹è±¡è¡¨';

-- ----------------------------
-- Records of objects
-- ----------------------------
INSERT INTO `objects` VALUES ('1', 'TEST_OBJECT', 'TEST_OBJECT', 'æµ‹è¯•å¯¹è±¡', 'normal', '0', 'æµ‹è¯•å¯¹è±¡', 'main', 'inactive', '2026-04-18 09:28:59', '2026-04-18 17:52:09');
INSERT INTO `objects` VALUES ('2', 'STUDENT_TAGS', 'STUDENT_TAGS', 'å®¢æˆ·æ ‡ç­¾', 'normal', '0', '', 'main', 'active', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `objects` VALUES ('3', 'POSTGRADUATE_CONFIRMATION', 'POSTGRADUATE_CONFIRMATION', 'ä¿ç ”é™¢æ ¡æœ€ç»ˆç¡®è®¤', 'normal', '0', '', 'main', 'active', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `objects` VALUES ('4', 'SUBJECT_TYPE', 'SUBJECT_TYPE', 'å­¦ç§‘åˆ†ç±»', 'normal', '1', '', 'main', 'active', '2026-04-18 09:28:59', '2026-04-18 09:28:59');
INSERT INTO `objects` VALUES ('5', 'OBJ_1776505438739', 'tst', 'test', 'normal', '0', '', 'main', 'active', '2026-04-18 17:44:29', '2026-04-18 17:44:29');
INSERT INTO `objects` VALUES ('6', 'OBJ_1776840141351', 'test2', 'test2', 'normal', '0', 'trest2备注', 'main', 'active', '2026-04-22 14:43:19', '2026-04-22 08:15:23');
INSERT INTO `objects` VALUES ('7', 'OBJ_1776850542729', 'test2_COPY', 'test2（复制）', 'normal', '0', 'trest2备注', 'main', 'active', '2026-04-22 17:35:48', '2026-04-25 16:48:04');

-- ----------------------------
-- Table structure for page_settings
-- ----------------------------
DROP TABLE IF EXISTS `page_settings`;
CREATE TABLE `page_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` varchar(50) DEFAULT NULL,
  `object_id` varchar(50) DEFAULT NULL,
  `page_name` varchar(100) DEFAULT NULL,
  `display_fields` text,
  `display_form` varchar(50) DEFAULT '普通表格',
  `fixedColumns` varchar(50) DEFAULT '',
  `defaultExpand` varchar(10) DEFAULT '是',
  `defaultLoad` varchar(10) DEFAULT '是',
  `countRecords` varchar(10) DEFAULT '是',
  `showNonIdle` varchar(10) DEFAULT '是',
  `supportIdleTime` varchar(10) DEFAULT '否',
  `showCondition` varchar(10) DEFAULT '否',
  `sql` text,
  `leftTreePage` varchar(255) DEFAULT '',
  `blockName` varchar(100) DEFAULT '',
  `page` varchar(50) DEFAULT '',
  `relatedObject` varchar(50) DEFAULT '',
  `relatedCondition` varchar(255) DEFAULT '',
  `showSearchCondition` varchar(10) DEFAULT '',
  `customRelatedCondition` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Records of page_settings
-- ----------------------------
INSERT INTO `page_settings` VALUES ('1', '2', 'OBJ_1776840141351', null, '[{\"fieldId\":30,\"fieldName\":\"isqianyue\",\"label\":\"\\u662f\\u5426\\u5df2\\u7b7e\\u7ea6\",\"type\":\"current\",\"foreignField\":null},{\"fieldId\":32,\"fieldName\":\"fuwenben\",\"label\":\"\\u5bcc\\u6587\\u672c\",\"type\":\"current\",\"foreignField\":null},{\"fieldId\":31,\"fieldName\":\"lunbo\",\"label\":\"\\u8f6e\\u64ad\\u56fe\",\"type\":\"current\",\"foreignField\":null},{\"fieldId\":33,\"fieldName\":\"fuwenben2\",\"label\":\"\\u5bcc\\u6587\\u672c2\",\"type\":\"current\",\"foreignField\":null},{\"fieldId\":34,\"fieldName\":\"fuwenben3\",\"label\":\"\\u5bcc\\u6587\\u672c3\",\"type\":\"current\",\"foreignField\":null}]', '普通表格', '0', '是', '是', '是', '是', '否', '否', '', '', '', '', '', '', '是', '否', '2026-04-30 16:54:17', '2026-04-30 16:54:17');
INSERT INTO `page_settings` VALUES ('2', '1', 'OBJ_1776850542729', null, '[{\"fieldId\":40,\"fieldName\":\"isqianyue\",\"label\":\"\\u662f\\u5426\\u5df2\\u7b7e\\u7ea6\",\"sort\":6},{\"fieldId\":41,\"fieldName\":\"lunbo\",\"label\":\"\\u8f6e\\u64ad\\u56fe\",\"sort\":12},{\"fieldId\":42,\"fieldName\":\"fuwenben\",\"label\":\"\\u5bcc\\u6587\\u672c\",\"sort\":18},{\"fieldId\":43,\"fieldName\":\"fuwenben2\",\"label\":\"\\u5bcc\\u6587\\u672c2\",\"sort\":19},{\"fieldId\":44,\"fieldName\":\"fuwenben3\",\"label\":\"\\u5bcc\\u6587\\u672c3\",\"sort\":19},{\"fieldId\":46,\"fieldName\":\"fuwenben5\",\"label\":\"\\u5bcc\\u6587\\u672c5\",\"sort\":22}]', '普通表格', '0', '是', '是', '是', '是', '否', '否', '', '', '', '', '', '', '是', '否', '2026-04-30 18:21:11', '2026-04-30 18:21:11');

-- ----------------------------
-- Table structure for pages
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `object_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `is_api` tinyint(1) NOT NULL DEFAULT '0',
  `menu_id` int NOT NULL,
  `path` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES ('1', 'OBJ_1776850542729', 'test2（复制）', 'list', '0', '1001', '/page/OBJ_1776850542729/list', 'active', '2026-04-23 19:43:22', '2026-04-23 19:43:22');
INSERT INTO `pages` VALUES ('2', 'OBJ_1776840141351', 'test2', 'list', '0', '4', '/page/OBJ_1776840141351/list', 'active', '2026-04-23 10:39:28', '2026-04-23 10:39:28');

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'è§’è‰²åç§°',
  `code` varchar(50) NOT NULL COMMENT 'è§’è‰²æ ‡è¯†',
  `description` text COMMENT 'æè¿°',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'çŠ¶æ€',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'åˆ›å»ºæ—¶é—´',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'æ›´æ–°æ—¶é—´',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='è§’è‰²è¡¨';

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES ('1', '系统管理员', 'super_admin', '系统管理员', 'active', '2026-04-18 09:28:59', '2026-04-23 02:14:37');
INSERT INTO `roles` VALUES ('2', '超管', 'admin', '超管', 'active', '2026-04-18 09:28:59', '2026-04-23 02:14:20');
INSERT INTO `roles` VALUES ('3', '普通管理员', 'user', '普通管理员', 'active', '2026-04-18 09:28:59', '2026-04-23 02:14:22');

-- ----------------------------
-- Table structure for test2
-- ----------------------------
DROP TABLE IF EXISTS `test2`;
CREATE TABLE `test2` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint DEFAULT '1',
  `isqianyue` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lunbo` text COLLATE utf8mb4_unicode_ci,
  `fuwenben` text COLLATE utf8mb4_unicode_ci,
  `fuwenben2` text COLLATE utf8mb4_unicode_ci,
  `fuwenben3` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of test2
-- ----------------------------
INSERT INTO `test2` VALUES ('1', '2026-04-30 08:56:51', '2026-04-30 08:56:59', '1', null, null, '1', null, null);
INSERT INTO `test2` VALUES ('2', '2026-04-30 08:56:52', '2026-04-30 08:56:59', '1', null, null, '2', null, null);
INSERT INTO `test2` VALUES ('3', '2026-04-30 08:56:53', '2026-04-30 08:57:00', '1', null, null, '3', null, null);
INSERT INTO `test2` VALUES ('4', '2026-04-30 08:56:54', '2026-04-30 08:57:00', '1', null, null, '4', null, null);
INSERT INTO `test2` VALUES ('5', '2026-04-30 08:56:54', '2026-04-30 08:57:01', '1', null, null, '5', null, null);
INSERT INTO `test2` VALUES ('6', '2026-04-30 08:56:55', '2026-04-30 08:57:02', '1', null, null, '6', null, null);
INSERT INTO `test2` VALUES ('7', '2026-04-30 08:56:56', '2026-04-30 08:57:03', '1', null, null, '67', null, null);

-- ----------------------------
-- Table structure for test2_COPY
-- ----------------------------
DROP TABLE IF EXISTS `test2_COPY`;
CREATE TABLE `test2_COPY` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint DEFAULT '1',
  `isqianyue` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lunbo` text COLLATE utf8mb4_unicode_ci,
  `fuwenben` text COLLATE utf8mb4_unicode_ci,
  `fuwenben2` text COLLATE utf8mb4_unicode_ci,
  `fuwenben3` text COLLATE utf8mb4_unicode_ci,
  `fuwenben4` text COLLATE utf8mb4_unicode_ci,
  `fuwenben5` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of test2_COPY
-- ----------------------------
INSERT INTO `test2_COPY` VALUES ('1', '2026-04-25 09:31:51', '2026-04-25 11:29:55', '1', '11', null, null, '1', null, null, null);
INSERT INTO `test2_COPY` VALUES ('2', '2026-04-25 09:31:55', '2026-04-25 11:29:56', '1', '2', null, null, '1', null, null, null);
INSERT INTO `test2_COPY` VALUES ('3', '2026-04-25 09:31:56', '2026-04-25 11:29:56', '1', '3', null, null, '1', null, null, null);
INSERT INTO `test2_COPY` VALUES ('4', '2026-04-25 09:31:57', '2026-04-25 11:29:57', '1', '4', null, null, '2', null, null, null);
INSERT INTO `test2_COPY` VALUES ('5', '2026-04-25 09:31:57', '2026-04-25 11:29:57', '1', '5', null, null, '3', null, null, null);
INSERT INTO `test2_COPY` VALUES ('6', '2026-04-25 09:31:58', '2026-04-25 11:29:59', '1', '6', null, null, '5', null, null, null);
INSERT INTO `test2_COPY` VALUES ('7', '2026-04-25 09:31:58', '2026-04-25 11:29:58', '1', '7', null, null, '4', null, null, null);
INSERT INTO `test2_COPY` VALUES ('8', '2026-04-25 09:31:59', '2026-04-25 09:31:59', '1', '8', null, null, null, null, null, null);
INSERT INTO `test2_COPY` VALUES ('9', '2026-04-25 09:31:59', '2026-04-25 09:31:59', '1', '9', null, null, null, null, null, null);
INSERT INTO `test2_COPY` VALUES ('10', '2026-04-25 09:32:01', '2026-04-25 09:32:01', '1', '10', null, null, null, null, null, null);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'ç”¨æˆ·å',
  `password` varchar(100) NOT NULL COMMENT 'å¯†ç ',
  `name` varchar(50) NOT NULL COMMENT 'åç§°',
  `email` varchar(100) NOT NULL COMMENT 'é‚®ç®±',
  `phone` varchar(20) NOT NULL COMMENT 'ç”µè¯',
  `role_id` int NOT NULL DEFAULT '3' COMMENT 'è§’è‰²ID',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'çŠ¶æ€',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'åˆ›å»ºæ—¶é—´',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'æ›´æ–°æ—¶é—´',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='ç”¨æˆ·è¡¨';

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'admin', 'e10adc3949ba59abbe56e057f20f883e', '超级管理员', 'admin@example.com', '13800138000', '1', 'active', '2026-04-18 09:28:59', '2026-04-23 02:13:08');
INSERT INTO `users` VALUES ('2', 'user1', 'e10adc3949ba59abbe56e057f20f883e', 'user1', 'user1@example.com', '13800138001', '3', 'active', '2026-04-18 09:28:59', '2026-04-23 02:13:15');
INSERT INTO `users` VALUES ('3', 'editor', 'e10adc3949ba59abbe56e057f20f883e', 'editor', 'editor@example.com', '13800138002', '2', 'active', '2026-04-18 09:28:59', '2026-04-23 02:13:19');
