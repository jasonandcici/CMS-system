/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : website

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2019-07-01 13:30:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for wp_auth_assignment
-- ----------------------------
DROP TABLE IF EXISTS `wp_auth_assignment`;
CREATE TABLE `wp_auth_assignment` (
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  CONSTRAINT `wp_auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `wp_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of wp_auth_assignment
-- ----------------------------
INSERT INTO `wp_auth_assignment` VALUES ('admin', '2', '1499957667');

-- ----------------------------
-- Table structure for wp_auth_item
-- ----------------------------
DROP TABLE IF EXISTS `wp_auth_item`;
CREATE TABLE `wp_auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `type` (`type`),
  CONSTRAINT `wp_auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `wp_auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of wp_auth_item
-- ----------------------------
INSERT INTO `wp_auth_item` VALUES ('admin', '1', '管理员', null, null, '1498463125', '1498463125');
INSERT INTO `wp_auth_item` VALUES ('auth/role', '2', '角色列表', null, null, '1498381185', '1498381185');
INSERT INTO `wp_auth_item` VALUES ('auth/role-auth', '2', '权限设置', null, null, '1498381993', '1498381993');
INSERT INTO `wp_auth_item` VALUES ('auth/role-create', '2', '创建角色', null, null, '1498381959', '1498381959');
INSERT INTO `wp_auth_item` VALUES ('auth/role-delete', '2', '删除角色', null, null, '1498382001', '1498382001');
INSERT INTO `wp_auth_item` VALUES ('auth/role-update', '2', '更新角色', null, null, '1498381987', '1498381987');
INSERT INTO `wp_auth_item` VALUES ('comment/delete', '2', '删除评论', null, null, null, null);
INSERT INTO `wp_auth_item` VALUES ('comment/index', '2', '评论列表', null, null, null, null);
INSERT INTO `wp_auth_item` VALUES ('comment/status', '2', '评论审核', null, null, null, null);
INSERT INTO `wp_auth_item` VALUES ('comment/view', '2', '查看评论', null, null, null, null);
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=custom', '2', '全局碎片管理', null, null, '1498382575', '1498382575');
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=email', '2', '邮件设置', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=member', '2', '用户配置', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=site', '2', '系统设置', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=sms', '2', '短信设置', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=third', '2', '第三方账号设置', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('config/index?scope=upload', '2', '上传设置', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('fragment/fragment/edit?category_id=20', '2', '修改', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('import/prototype?site_id=1989', '2', '批量导入数据', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('log/delete', '2', '删除日志', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('log/index', '2', '日志列表', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('member/create', '2', '新增用户', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('member/delete', '2', '删除用户', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('member/index', '2', '用户列表', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('member/status', '2', '更改用户状态', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('member/update', '2', '修改用户信息', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('member/view', '2', '查看用户信息', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('prototype/category/create?site_id=1989', '2', '添加栏目', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/category/delete?site_id=1989', '2', '删除栏目', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/category/index?site_id=1989', '2', '栏目列表', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/category/sort?site_id=1989', '2', '栏目排序', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/category/status?site_id=1989', '2', '栏目状态', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/category/update?site_id=1989', '2', '修改栏目', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/form/delete?site_id=1989&model_id=8', '2', '删除', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/form/index?site_id=1989&model_id=8', '2', '列表', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/form/status?site_id=1989&model_id=8', '2', '状态', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/form/view?site_id=1989&model_id=8', '2', '详情', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/create?category_id=184', '2', '添加', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/delete?category_id=184', '2', '删除', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/index?category_id=184', '2', '列表', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/move?category_id=184', '2', '移动', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/page?category_id=185', '2', '修改', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/sort?category_id=184', '2', '排序', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/status?category_id=184', '2', '状态', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('prototype/node/update?category_id=184', '2', '修改', null, null, '1522220442', '1522220442');
INSERT INTO `wp_auth_item` VALUES ('sensitive-words/create', '2', '创建敏感词', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('sensitive-words/delete', '2', '删除敏感词', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('sensitive-words/index', '2', '敏感词列表', null, null, '1498382233', '1498382233');
INSERT INTO `wp_auth_item` VALUES ('site-manage/index', '2', '站点列表', null, null, '1498380993', '1498380993');
INSERT INTO `wp_auth_item` VALUES ('site-manage/set-default', '2', '设置默认站点', null, null, '1498381177', '1498381177');
INSERT INTO `wp_auth_item` VALUES ('site-manage/status', '2', '站点状态', null, null, '1498381100', '1498381100');
INSERT INTO `wp_auth_item` VALUES ('site-manage/update', '2', '编辑站点信息', null, null, '1498381002', '1498381002');
INSERT INTO `wp_auth_item` VALUES ('tag/create', '2', '创建标签', null, null, '1498382586', '1498382586');
INSERT INTO `wp_auth_item` VALUES ('tag/delete', '2', '删除标签', null, null, '1498382597', '1498382597');
INSERT INTO `wp_auth_item` VALUES ('tag/index', '2', '标签列表', null, null, '1498382584', '1498382584');
INSERT INTO `wp_auth_item` VALUES ('tag/update', '2', '编辑标签', null, null, '1498382593', '1498382593');
INSERT INTO `wp_auth_item` VALUES ('user/create', '2', '创建管理员', null, null, '1498382247', '1498382247');
INSERT INTO `wp_auth_item` VALUES ('user/delete', '2', '删除管理员', null, null, '1498382271', '1498382271');
INSERT INTO `wp_auth_item` VALUES ('user/index', '2', '管理员列表', null, null, '1498382227', '1498382227');
INSERT INTO `wp_auth_item` VALUES ('user/status', '2', '管理员状态', null, null, '1498382240', '1498382240');
INSERT INTO `wp_auth_item` VALUES ('user/update', '2', '编辑管理员信息', null, null, '1498382233', '1498382233');

-- ----------------------------
-- Table structure for wp_auth_item_child
-- ----------------------------
DROP TABLE IF EXISTS `wp_auth_item_child`;
CREATE TABLE `wp_auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  CONSTRAINT `wp_auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `wp_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `wp_auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `wp_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of wp_auth_item_child
-- ----------------------------
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'auth/role');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'auth/role-auth');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'auth/role-create');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'auth/role-delete');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'auth/role-update');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'comment/delete');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'comment/index');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'comment/status');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'comment/view');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=custom');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=email');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=member');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=site');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=sms');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=third');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'config/index?scope=upload');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'fragment/fragment/edit?category_id=20');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'import/prototype?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'member/create');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'member/delete');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'member/index');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'member/status');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'member/update');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'member/view');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/category/create?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/category/delete?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/category/index?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/category/sort?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/category/status?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/category/update?site_id=1989');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/form/delete?site_id=1989&model_id=8');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/form/index?site_id=1989&model_id=8');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/form/status?site_id=1989&model_id=8');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/form/view?site_id=1989&model_id=8');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/create?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/delete?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/index?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/move?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/page?category_id=185');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/sort?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/status?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'prototype/node/update?category_id=184');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'sensitive-words/create');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'sensitive-words/delete');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'sensitive-words/index');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'site-manage/index');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'site-manage/set-default');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'site-manage/status');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'site-manage/update');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'tag/create');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'tag/delete');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'tag/index');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'tag/update');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'user/create');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'user/delete');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'user/index');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'user/status');
INSERT INTO `wp_auth_item_child` VALUES ('admin', 'user/update');

-- ----------------------------
-- Table structure for wp_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `wp_auth_rule`;
CREATE TABLE `wp_auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of wp_auth_rule
-- ----------------------------

-- ----------------------------
-- Table structure for wp_comment
-- ----------------------------
DROP TABLE IF EXISTS `wp_comment`;
CREATE TABLE `wp_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父级评论',
  `category_id` smallint(5) unsigned DEFAULT NULL,
  `data_id` int(10) unsigned NOT NULL COMMENT '评论对象id',
  `content` mediumtext COLLATE utf8_unicode_ci NOT NULL COMMENT '评论内容',
  `atlas` text COLLATE utf8_unicode_ci COMMENT '图集',
  `user_id` int(10) unsigned NOT NULL,
  `count_like` int(10) unsigned DEFAULT '0' COMMENT '点赞数',
  `count_bad` int(10) unsigned DEFAULT '0' COMMENT '不喜欢数',
  `is_enable` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用,1：启用，0：禁用',
  `create_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wp_comment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `wp_prototype_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `wp_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='评论表';

-- ----------------------------
-- Records of wp_comment
-- ----------------------------

-- ----------------------------
-- Table structure for wp_editor_category
-- ----------------------------
DROP TABLE IF EXISTS `wp_editor_category`;
CREATE TABLE `wp_editor_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='编辑器模板分类';

-- ----------------------------
-- Records of wp_editor_category
-- ----------------------------
INSERT INTO `wp_editor_category` VALUES ('1', '0', '标题', '1');
INSERT INTO `wp_editor_category` VALUES ('2', '0', '正文', '2');
INSERT INTO `wp_editor_category` VALUES ('3', '0', '图文', '3');
INSERT INTO `wp_editor_category` VALUES ('4', '0', '图形', '4');
INSERT INTO `wp_editor_category` VALUES ('5', '0', '提示', '5');
INSERT INTO `wp_editor_category` VALUES ('6', '0', '其他', '6');

-- ----------------------------
-- Table structure for wp_editor_template
-- ----------------------------
DROP TABLE IF EXISTS `wp_editor_template`;
CREATE TABLE `wp_editor_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `remote_id` int(10) unsigned DEFAULT NULL COMMENT '远程id，用于判断是下载的',
  `category_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '模板标题',
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '预览图',
  `color` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '模板颜色',
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '模板标签',
  `content` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '模板内容',
  `sort` int(10) unsigned DEFAULT NULL,
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `wp_editor_template_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `wp_editor_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='编辑器模板';

-- ----------------------------
-- Records of wp_editor_template
-- ----------------------------

-- ----------------------------
-- Table structure for wp_files
-- ----------------------------
DROP TABLE IF EXISTS `wp_files`;
CREATE TABLE `wp_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL COMMENT '所属分类',
  `type` enum('image','attachment','media') COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件名',
  `username` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件路径',
  `filename` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件名',
  `extension` char(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '扩展名',
  `file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '完整文件路径',
  `width` double unsigned DEFAULT NULL,
  `height` double unsigned DEFAULT NULL,
  `size` double unsigned DEFAULT NULL COMMENT '文件大小',
  `sort` int(10) unsigned DEFAULT NULL,
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `wp_files_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `wp_files_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文件管理';

-- ----------------------------
-- Records of wp_files
-- ----------------------------

-- ----------------------------
-- Table structure for wp_files_category
-- ----------------------------
DROP TABLE IF EXISTS `wp_files_category`;
CREATE TABLE `wp_files_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0',
  `type` enum('image','attachment','media') COLLATE utf8_unicode_ci NOT NULL COMMENT '类型',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名',
  `sort` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文件分类';

-- ----------------------------
-- Records of wp_files_category
-- ----------------------------

-- ----------------------------
-- Table structure for wp_fragment
-- ----------------------------
DROP TABLE IF EXISTS `wp_fragment`;
CREATE TABLE `wp_fragment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` smallint(5) unsigned NOT NULL DEFAULT '1989',
  `category_id` smallint(5) unsigned NOT NULL COMMENT '所属组',
  `title` char(30) NOT NULL COMMENT '标题',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '名称（英文字母）',
  `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '值',
  `style` tinyint(1) unsigned DEFAULT '1' COMMENT '表单控件样式,0：自定义，1：text，2：password,3：textarea,4：select，5：radio单行，6：radio多行，7：checkbox单行，8：checkbox多行，9：单图片上传，10：多图片上传',
  `setting` text COMMENT '其他更多设置',
  `sort` int(10) unsigned DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `wp_fragment_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `wp_site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_fragment_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `wp_fragment_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='单字段碎片';

-- ----------------------------
-- Records of wp_fragment
-- ----------------------------
INSERT INTO `wp_fragment` VALUES ('14', '1989', '20', '电话', 'hotLine', '4008-228-408', '1', null, '18');
INSERT INTO `wp_fragment` VALUES ('18', '1989', '20', '备案号', 'beian', '沪ICP备12018056号-1', '1', null, '19');
INSERT INTO `wp_fragment` VALUES ('19', '1989', '20', '地址', 'address', '上海市宝山区逸仙路3000号6号楼103-105', '1', null, '20');
INSERT INTO `wp_fragment` VALUES ('20', '1989', '20', '版权', 'copyright', '©2017 <a href=\"http://www.dookay.com\" target=\"_blank\">稻壳互联</a> 版权所有', '1', null, '14');

-- ----------------------------
-- Table structure for wp_fragment_category
-- ----------------------------
DROP TABLE IF EXISTS `wp_fragment_category`;
CREATE TABLE `wp_fragment_category` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` smallint(5) unsigned NOT NULL,
  `type` tinyint(1) unsigned DEFAULT '0' COMMENT '栏目类型（0：广告，1：碎片）',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `slug` varchar(100) NOT NULL COMMENT '分类标识',
  `sort` int(8) unsigned DEFAULT NULL,
  `enable_sub_title` tinyint(1) unsigned DEFAULT '0' COMMENT '启用子标题',
  `enable_thumb` tinyint(1) unsigned DEFAULT '1' COMMENT '启用图片上传',
  `multiple_thumb` tinyint(1) unsigned DEFAULT '0' COMMENT '多图上传',
  `enable_attachment` tinyint(1) unsigned DEFAULT '0' COMMENT '启用附件上传',
  `multiple_attachment` tinyint(1) unsigned DEFAULT '0' COMMENT '多附件上传',
  `enable_ueditor` tinyint(1) unsigned DEFAULT '0' COMMENT '启用富文本编辑器',
  `enable_link` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用链接',
  `is_disabled_opt` tinyint(1) unsigned DEFAULT '0' COMMENT '是否禁用新增和删除操作',
  `is_global` tinyint(1) unsigned DEFAULT '0' COMMENT '是否全局（type=1时有效）',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  KEY `slug` (`slug`) USING BTREE,
  CONSTRAINT `wp_fragment_category_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `wp_site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='碎片分类';

-- ----------------------------
-- Records of wp_fragment_category
-- ----------------------------
INSERT INTO `wp_fragment_category` VALUES ('20', '1989', '1', '网站信息', 'siteInfo', '20', '0', '1', '0', '0', '0', '0', '1', '0', '1');

-- ----------------------------
-- Table structure for wp_fragment_list
-- ----------------------------
DROP TABLE IF EXISTS `wp_fragment_list`;
CREATE TABLE `wp_fragment_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` smallint(5) unsigned NOT NULL,
  `category_id` smallint(5) unsigned NOT NULL COMMENT '幻灯片栏目id',
  `title` varchar(255) NOT NULL,
  `title_sub` varchar(255) DEFAULT NULL,
  `thumb` text,
  `attachment` text COMMENT '附件',
  `related_data_model` smallint(5) DEFAULT '0' COMMENT '要关联的数据node模型id，如果为0表示自定义',
  `related_data_id` int(10) unsigned DEFAULT NULL COMMENT '要关联的数据id',
  `link` varchar(255) DEFAULT NULL COMMENT '链接',
  `sort` int(8) unsigned DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '1',
  `description` text,
  `create_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `site_id` (`site_id`),
  CONSTRAINT `wp_fragment_list_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `wp_fragment_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_fragment_list_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `wp_site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='列表类型碎片';

-- ----------------------------
-- Records of wp_fragment_list
-- ----------------------------

-- ----------------------------
-- Table structure for wp_node_feedback
-- ----------------------------
DROP TABLE IF EXISTS `wp_node_feedback`;
CREATE TABLE `wp_node_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` smallint(5) unsigned NOT NULL,
  `model_id` smallint(5) unsigned NOT NULL COMMENT '栏目所属模型id',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `status` tinyint(1) unsigned DEFAULT '0',
  `count_user_relations` text,
  `create_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  CONSTRAINT `wp_node_feedback_ibfk_site` FOREIGN KEY (`site_id`) REFERENCES `wp_site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='意见反馈';

-- ----------------------------
-- Records of wp_node_feedback
-- ----------------------------

-- ----------------------------
-- Table structure for wp_node_news
-- ----------------------------
DROP TABLE IF EXISTS `wp_node_news`;
CREATE TABLE `wp_node_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` smallint(5) unsigned NOT NULL,
  `model_id` smallint(5) unsigned NOT NULL COMMENT '栏目所属模型id',
  `category_id` smallint(5) unsigned NOT NULL,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `thumb` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '缩略图',
  `atlas` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '图集',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '内容',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  `attachment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '附件',
  `sort` int(10) unsigned DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) unsigned DEFAULT '1',
  `template_content` char(50) DEFAULT NULL COMMENT '内容模板',
  `is_push` tinyint(1) unsigned DEFAULT '0' COMMENT '是否推荐',
  `is_comment` tinyint(1) DEFAULT '1' COMMENT '是否允许评论',
  `views` int(10) unsigned DEFAULT '0' COMMENT '浏览数',
  `jump_link` varchar(255) DEFAULT NULL COMMENT '跳转链接',
  `is_login` tinyint(1) unsigned DEFAULT '0' COMMENT '访问是否需登录',
  `layouts` char(50) DEFAULT NULL COMMENT '页面布局',
  `count_user_relations` text,
  `create_time` int(10) unsigned DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `wp_node_news_ibfk_category` FOREIGN KEY (`category_id`) REFERENCES `wp_prototype_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_node_news_ibfk_site` FOREIGN KEY (`site_id`) REFERENCES `wp_site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章模型';

-- ----------------------------
-- Records of wp_node_news
-- ----------------------------

-- ----------------------------
-- Table structure for wp_prototype_category
-- ----------------------------
DROP TABLE IF EXISTS `wp_prototype_category`;
CREATE TABLE `wp_prototype_category` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目id',
  `site_id` smallint(5) unsigned NOT NULL,
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '栏目父级id',
  `model_id` smallint(5) unsigned DEFAULT '0' COMMENT '栏目所属模型id',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '栏目类型（0：列表，1：单页，2：自由页，3：链接型）',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '栏目名称',
  `sub_title` varchar(100) DEFAULT NULL COMMENT '子标题',
  `slug` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'url美化',
  `slug_rules` varchar(100) DEFAULT NULL COMMENT '控制器方法，例如：prototype/node/index',
  `slug_rules_detail` varchar(100) DEFAULT NULL COMMENT '详情控制器（slug_rules为数据列表有效）',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `link` varchar(100) DEFAULT NULL,
  `target` varchar(20) DEFAULT '' COMMENT '新窗口打开',
  `thumb` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '图片',
  `content` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '描述',
  `template` char(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '列表模板',
  `template_content` char(50) DEFAULT NULL COMMENT '内容模板',
  `layouts` char(50) DEFAULT NULL COMMENT '页面布局',
  `layouts_content` char(50) DEFAULT NULL COMMENT '详情页布局',
  `enable_tag` tinyint(1) unsigned DEFAULT '0' COMMENT '是否开启tag功能',
  `enable_push` tinyint(1) unsigned DEFAULT '0' COMMENT '是否启用推荐',
  `is_login` tinyint(1) unsigned DEFAULT '0' COMMENT '访问是否需要登录',
  `is_login_content` tinyint(1) unsigned DEFAULT '0' COMMENT '详情页访问是否需要登录',
  `is_comment` tinyint(1) unsigned DEFAULT '0' COMMENT '是否启用评论',
  `expand` text COMMENT '其他扩展数据',
  `system_mark` varchar(50) DEFAULT NULL COMMENT '系统标注',
  `seo_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'seo',
  `seo_keywords` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'seo',
  `seo_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'seo',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  CONSTRAINT `wp_prototype_category_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `wp_site` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8 COMMENT='栏目';

-- ----------------------------
-- Records of wp_prototype_category
-- ----------------------------
INSERT INTO `wp_prototype_category` VALUES ('1', '1989', '0', '0', '2', '首页', '', 'index', 'site/index', '', '1', '1', '', '', '', '', 'index', null, 'main', null, '0', '0', '0', '0', '0', null, '', '', '', '');
INSERT INTO `wp_prototype_category` VALUES ('2', '1989', '0', '0', '2', '搜索', '', 'search', 'search/index', '', '2', '0', '', '', '', '', 'index', null, 'main', null, '0', '0', '0', '0', '0', null, '', '', '', '');
INSERT INTO `wp_prototype_category` VALUES ('184', '1989', '0', '7', '0', '数据列表', '', 'news', '', '', '184', '1', '', '', '', '<p>本页面用于示例数据列表及其详情、生成缩略图、多图显示和附件下载等功能。</p>', '', '', 'main', 'main', '0', '0', '0', '0', '0', '{\"enable_detail\":\"1\",\"enable_admin\":\"1\"}', null, '', '', '');
INSERT INTO `wp_prototype_category` VALUES ('185', '1989', '0', '0', '1', '单网页', '', 'about', '', '', '185', '1', '', '', '', '<p>本页面用于示例单网页。</p>', '', null, 'main', null, '0', '0', '0', '0', '0', '{\"enable_detail\":\"1\",\"enable_admin\":\"1\"}', null, '', '', '');

-- ----------------------------
-- Table structure for wp_prototype_field
-- ----------------------------
DROP TABLE IF EXISTS `wp_prototype_field`;
CREATE TABLE `wp_prototype_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model_id` smallint(5) unsigned NOT NULL,
  `title` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '字段标题',
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '字段名称',
  `field_type` enum('int','decimal','varchar','text','longtext','enum','date','datetime') COLLATE utf8_unicode_ci DEFAULT 'varchar' COMMENT '字段类型',
  `field_decimal_place` int(10) unsigned DEFAULT '0' COMMENT '小数点',
  `field_length` int(10) unsigned DEFAULT NULL COMMENT '字段长度',
  `type` enum('text','passport','date','datetime','number','int','captcha','textarea','radio','radio_inline','checkbox','checkbox_inline','select','select_multiple','tag','editor','image','image_multiple','attachment','attachment_multiple','relation_data','relation_category','city','city_multiple') COLLATE utf8_unicode_ci NOT NULL COMMENT '字段类型',
  `options` mediumtext COLLATE utf8_unicode_ci COMMENT '选项',
  `default_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '默认值',
  `is_required` tinyint(1) unsigned DEFAULT '0' COMMENT '是否必填',
  `is_show_list` tinyint(1) unsigned DEFAULT '0' COMMENT '是否显示在列表',
  `is_search` tinyint(1) unsigned DEFAULT '0' COMMENT '是否设为搜索',
  `hint` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '输入提示',
  `placeholder` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '占位符',
  `custom_verification_rules` mediumtext COLLATE utf8_unicode_ci COMMENT '自定义验证规则',
  `setting` text COLLATE utf8_unicode_ci COMMENT '其他设置',
  `sort` int(10) unsigned DEFAULT NULL COMMENT '排序',
  `is_updated` tinyint(1) unsigned DEFAULT '0' COMMENT '是否已更新',
  `updated_target` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '更新对象',
  `is_generate` tinyint(1) unsigned DEFAULT '0' COMMENT '是否已生成',
  `history` mediumtext COLLATE utf8_unicode_ci COMMENT '历史记录',
  PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`),
  CONSTRAINT `wp_prototype_field_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `wp_prototype_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='模型字段';

-- ----------------------------
-- Records of wp_prototype_field
-- ----------------------------
INSERT INTO `wp_prototype_field` VALUES ('4', '7', '缩略图', 'thumb', 'text', '0', null, 'image', '', '', '0', '0', '0', '', '', '', '', '4', '0', null, '1', '{\"id\":\"4\",\"model_id\":7,\"title\":\"\\u7f29\\u7565\\u56fe\",\"name\":\"thumb\",\"field_type\":\"text\",\"field_decimal_place\":\"0\",\"field_length\":null,\"type\":\"image\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":0,\"is_show_list\":0,\"is_search\":0,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":[],\"setting\":[],\"sort\":\"4\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');
INSERT INTO `wp_prototype_field` VALUES ('5', '7', '图集', 'atlas', 'text', '0', null, 'image_multiple', '', '', '0', '0', '0', '', '', '', '', '5', '0', null, '1', '{\"id\":\"5\",\"model_id\":7,\"title\":\"\\u56fe\\u96c6\",\"name\":\"atlas\",\"field_type\":\"text\",\"field_decimal_place\":\"0\",\"field_length\":null,\"type\":\"image_multiple\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":0,\"is_show_list\":0,\"is_search\":0,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":[],\"setting\":[],\"sort\":\"5\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');
INSERT INTO `wp_prototype_field` VALUES ('6', '7', '内容', 'content', 'longtext', '0', null, 'editor', '', '', '0', '0', '0', '', '', '', '', '6', '0', null, '1', '{\"id\":\"6\",\"model_id\":7,\"title\":\"\\u5185\\u5bb9\",\"name\":\"content\",\"field_type\":\"longtext\",\"field_decimal_place\":\"0\",\"field_length\":null,\"type\":\"editor\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":0,\"is_show_list\":0,\"is_search\":0,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":[],\"setting\":[],\"sort\":\"6\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');
INSERT INTO `wp_prototype_field` VALUES ('7', '7', '描述', 'description', 'varchar', '0', '255', 'textarea', '', '', '0', '0', '0', '', '', '{\"length\":\"255\"}', '', '7', '0', null, '1', '{\"id\":\"7\",\"model_id\":7,\"title\":\"\\u63cf\\u8ff0\",\"name\":\"description\",\"field_type\":\"varchar\",\"field_decimal_place\":\"0\",\"field_length\":\"255\",\"type\":\"textarea\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":0,\"is_show_list\":0,\"is_search\":0,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":{\"length\":\"255\"},\"setting\":[],\"sort\":\"7\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');
INSERT INTO `wp_prototype_field` VALUES ('8', '7', '附件', 'attachment', 'text', '0', null, 'attachment', '', '', '0', '0', '0', '', '', '', '', '8', '0', null, '1', '{\"id\":\"8\",\"model_id\":7,\"title\":\"\\u9644\\u4ef6\",\"name\":\"attachment\",\"field_type\":\"text\",\"field_decimal_place\":\"0\",\"field_length\":null,\"type\":\"attachment\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":0,\"is_show_list\":0,\"is_search\":0,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":[],\"setting\":[],\"sort\":\"8\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');
INSERT INTO `wp_prototype_field` VALUES ('9', '8', '内容', 'content', 'varchar', '0', null, 'textarea', '', '', '1', '1', '1', '', '', '', '', '9', '0', null, '1', '{\"id\":\"9\",\"model_id\":8,\"title\":\"\\u5185\\u5bb9\",\"name\":\"content\",\"field_type\":\"varchar\",\"field_decimal_place\":\"0\",\"field_length\":null,\"type\":\"textarea\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":1,\"is_show_list\":1,\"is_search\":1,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":[],\"setting\":[],\"sort\":\"9\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');
INSERT INTO `wp_prototype_field` VALUES ('10', '8', '验证码', 'captcha', 'varchar', '0', null, 'captcha', '', '', '1', '0', '0', '', '', '', '', '10', '0', null, '1', '{\"id\":\"10\",\"model_id\":8,\"title\":\"\\u9a8c\\u8bc1\\u7801\",\"name\":\"captcha\",\"field_type\":\"varchar\",\"field_decimal_place\":\"0\",\"field_length\":null,\"type\":\"captcha\",\"options\":{\"list\":[],\"default\":[]},\"default_value\":\"\",\"is_required\":1,\"is_show_list\":0,\"is_search\":0,\"hint\":\"\",\"placeholder\":\"\",\"custom_verification_rules\":[],\"setting\":[],\"sort\":\"10\",\"is_updated\":0,\"updated_target\":null,\"is_generate\":1}');

-- ----------------------------
-- Table structure for wp_prototype_model
-- ----------------------------
DROP TABLE IF EXISTS `wp_prototype_model`;
CREATE TABLE `wp_prototype_model` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型id',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '模型标题',
  `name` char(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '模型名称',
  `type` tinyint(1) unsigned DEFAULT '0' COMMENT '模型类型（0内容类型1前台表单类型2自由模型）',
  `is_login` tinyint(1) DEFAULT '0' COMMENT '是否需要登录可访问',
  `is_login_category` tinyint(1) unsigned DEFAULT '0' COMMENT '访问栏目是否需要登录',
  `is_login_download` tinyint(1) unsigned DEFAULT '0' COMMENT '附件下载是否需要登录',
  `description` varchar(100) DEFAULT NULL COMMENT '描述',
  `route` varchar(30) DEFAULT NULL COMMENT '自由模型对应路由',
  `is_generate` tinyint(1) unsigned DEFAULT '0' COMMENT '是否已经生成',
  `extend_code` mediumtext COMMENT '模型扩展代码',
  `setting` mediumtext COMMENT '扩展',
  `filter_sensitive_words_fields` varchar(80) DEFAULT NULL COMMENT '需过滤敏感词字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `栏目名称` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='模型';

-- ----------------------------
-- Records of wp_prototype_model
-- ----------------------------
INSERT INTO `wp_prototype_model` VALUES ('7', '文章模型', 'news', '0', '0', '0', '1', '', '', '1', null, '', 'content');
INSERT INTO `wp_prototype_model` VALUES ('8', '意见反馈', 'feedback', '1', '1', '0', '0', '', null, '1', '', '', 'content');

-- ----------------------------
-- Table structure for wp_prototype_page
-- ----------------------------
DROP TABLE IF EXISTS `wp_prototype_page`;
CREATE TABLE `wp_prototype_page` (
  `category_id` smallint(5) unsigned NOT NULL COMMENT '所属栏目id',
  `title` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `content` mediumtext COLLATE utf8_unicode_ci COMMENT '内容',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `wp_prototype_page_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `wp_prototype_category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='单页';

-- ----------------------------
-- Records of wp_prototype_page
-- ----------------------------
INSERT INTO `wp_prototype_page` VALUES ('185', '关于我们', '<p>稻壳互联在2015年末启动官网改造项目，经过为期5个月的策划、架构、设计、编码和测试。新版官网于2016年3月26日正式发布，全新的网站布局和展示，致力于为企业提供更优质的互联网化解决方案。从2006年开始，稻壳互联就开始在互联网的行业中发挥自己的一份力量。不管是初期的工作室模式，还是积累到现在为止庞大的团队，稻壳的工作氛围始终是积极向上的，我们不断推陈出新，不断学习，不断在互联网的领域中尝试新的变革，不断迎接挑战。<br/></p>', '1508940182');

-- ----------------------------
-- Table structure for wp_recommend
-- ----------------------------
DROP TABLE IF EXISTS `wp_recommend`;
CREATE TABLE `wp_recommend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '推荐位名称',
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '推荐位标识',
  `sort` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='推荐位';

-- ----------------------------
-- Records of wp_recommend
-- ----------------------------

-- ----------------------------
-- Table structure for wp_recommend_relation
-- ----------------------------
DROP TABLE IF EXISTS `wp_recommend_relation`;
CREATE TABLE `wp_recommend_relation` (
  `recommend_id` int(10) unsigned NOT NULL,
  `recommend_model_id` smallint(5) unsigned NOT NULL,
  `recommend_data_id` int(10) unsigned NOT NULL,
  KEY `recommend_id` (`recommend_id`),
  KEY `recommend_model_id` (`recommend_model_id`),
  CONSTRAINT `wp_recommend_relation_ibfk_1` FOREIGN KEY (`recommend_id`) REFERENCES `wp_recommend` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_recommend_relation_ibfk_2` FOREIGN KEY (`recommend_model_id`) REFERENCES `wp_prototype_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='推荐位和数据关联表';

-- ----------------------------
-- Records of wp_recommend_relation
-- ----------------------------

-- ----------------------------
-- Table structure for wp_sensitive_words
-- ----------------------------
DROP TABLE IF EXISTS `wp_sensitive_words`;
CREATE TABLE `wp_sensitive_words` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='敏感词';

-- ----------------------------
-- Records of wp_sensitive_words
-- ----------------------------

-- ----------------------------
-- Table structure for wp_session
-- ----------------------------
DROP TABLE IF EXISTS `wp_session`;
CREATE TABLE `wp_session` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='session存储';

-- ----------------------------
-- Records of wp_session
-- ----------------------------

-- ----------------------------
-- Table structure for wp_site
-- ----------------------------
DROP TABLE IF EXISTS `wp_site`;
CREATE TABLE `wp_site` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT '站点名称',
  `slug` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '站点标识',
  `domain` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '站点域名',
  `theme` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '主题',
  `logo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Logo',
  `language` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '语言',
  `is_enable` tinyint(1) unsigned DEFAULT '1',
  `is_default` tinyint(1) unsigned DEFAULT '0' COMMENT '是否默认站点',
  `enable_mobile` tinyint(1) unsigned DEFAULT '0' COMMENT '是否启用移动端',
  `devices_width` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '网页正文宽度，多个用”,“分隔',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=1990 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='站点';

-- ----------------------------
-- Records of wp_site
-- ----------------------------
INSERT INTO `wp_site` VALUES ('1989', '默认站点', 'zh-cn', null, 'zh-cn', '', 'zh-CN', '1', '1', '0', null);

-- ----------------------------
-- Table structure for wp_system_config
-- ----------------------------
DROP TABLE IF EXISTS `wp_system_config`;
CREATE TABLE `wp_system_config` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '范围',
  `title` char(30) NOT NULL COMMENT '标题',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '名称（英文字母）',
  `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '值',
  `style` tinyint(1) unsigned DEFAULT '1' COMMENT '表单控件样式,0：自定义，1：text，2：password,3：textarea,4：select，5：radio单行，6：radio多行，7：checkbox单行，8：checkbox多行，9：单图片上传，10：多图片上传',
  `setting` text COMMENT '其他更多设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
-- Records of wp_system_config
-- ----------------------------
INSERT INTO `wp_system_config` VALUES ('1', 'site', '网站名称', 'site_name', '测试站点', '1', null);
INSERT INTO `wp_system_config` VALUES ('2', 'site', '管理员邮箱', 'admin_email', '', '1', null);
INSERT INTO `wp_system_config` VALUES ('3', 'site', '后台logo', 'logo', '', '9', null);
INSERT INTO `wp_system_config` VALUES ('4', 'site', '技术支持', 'copyright', '', '1', null);
INSERT INTO `wp_system_config` VALUES ('5', 'site', 'URL后缀', 'urlSuffix', '.html', '0', null);
INSERT INTO `wp_system_config` VALUES ('6', 'site', '是否开启操作日志', 'log', '0', '5', '{\"list\":{\"1\":\"开启\",\"0\":\"关闭\"}}');
INSERT INTO `wp_system_config` VALUES ('7', 'site', '是否开启评论功能', 'enableComment', '0', '5', '{\"list\":{\"1\":\"开启(无需审核)\",\"2\":\"开启(需审核)\",\"0\":\"关闭\"}}');
INSERT INTO `wp_system_config` VALUES ('8', 'site', '是否开启API', 'enableApi', '0', '5', '{\"list\":{\"1\":\"开启\",\"0\":\"关闭\"},\"hint\":\"点击这里查看 <a href=\'/manage/web/index.php?r=doc/default/index\' target=\'_blank\'>Api手册</a>。\"}');
INSERT INTO `wp_system_config` VALUES ('20', 'email', '开启邮箱', 'enable', '1', '5', '{\"list\":{\"1\":\"开启\",\"0\":\"关闭\"}}');
INSERT INTO `wp_system_config` VALUES ('21', 'email', '服务器', 'host', 'smtp.163.com', '1', null);
INSERT INTO `wp_system_config` VALUES ('22', 'email', '端口', 'port', '25', '1', null);
INSERT INTO `wp_system_config` VALUES ('23', 'email', '加密方式', 'encryption', 'tls', '1', null);
INSERT INTO `wp_system_config` VALUES ('24', 'email', '邮箱', 'username', '', '1', null);
INSERT INTO `wp_system_config` VALUES ('25', 'email', '密码', 'password', '', '2', null);
INSERT INTO `wp_system_config` VALUES ('26', 'email', '收件邮箱', 'receive', '', '0', null);
INSERT INTO `wp_system_config` VALUES ('27', 'upload', '是否开放前台上传', 'enableFrontUpload', '0', '5', '{\"list\":{\"1\":\"开启\",\"0\":\"关闭\"},\"hint\":\"开启后前台页面上传无需登录。\"}');
INSERT INTO `wp_system_config` VALUES ('28', 'upload', '水印类型', 'watermarkType', '0', '5', '{\"list\":[\"文字水印\",\"图片水印\"]}');
INSERT INTO `wp_system_config` VALUES ('29', 'upload', '水印路径', 'watermarkPath', '', '9', null);
INSERT INTO `wp_system_config` VALUES ('30', 'upload', '图片水印透明度', 'watermarkOpacity', '50', '1', null);
INSERT INTO `wp_system_config` VALUES ('31', 'upload', '水印文字', 'watermarkText', '稻壳互联', '1', null);
INSERT INTO `wp_system_config` VALUES ('32', 'upload', '水印文字大小', 'watermarkTextSize', '30', '1', '{\"hint\":\"单位为像素“px”\"}');
INSERT INTO `wp_system_config` VALUES ('33', 'upload', '水印文字颜色', 'watermarkTextColor', '#ffffff', '1', null);
INSERT INTO `wp_system_config` VALUES ('34', 'upload', '图片大小限制', 'imageMaxSize', '2', '1', '{\"hint\":\"单位为兆“M”\"}');
INSERT INTO `wp_system_config` VALUES ('35', 'upload', '允许图片格式', 'imageAllowFiles', 'png,jpg,jpeg,gif,bmp', '13', null);
INSERT INTO `wp_system_config` VALUES ('36', 'upload', '是否压缩图片', 'imageCompressEnable', '1', '5', '{\"list\":{\"1\":\"开启\",\"0\":\"关闭\"}}');
INSERT INTO `wp_system_config` VALUES ('37', 'upload', '图片压缩最长边限制', 'imageCompressBorder', '3000', '1', '{\"hint\":\"单位为像素“px”\"}');
INSERT INTO `wp_system_config` VALUES ('38', 'upload', '附件大小限制', 'fileMaxSize', '500', '1', '{\"hint\":\"单位为兆“M”\"}');
INSERT INTO `wp_system_config` VALUES ('39', 'upload', '允许的附件格式', 'fileAllowFiles', 'png,jpg,jpeg,gif,bmp,flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid,rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml', '13', null);
INSERT INTO `wp_system_config` VALUES ('40', 'upload', '视频大小限制', 'videoMaxSize', '500', '1', '{\"hint\":\"单位为兆“M”\"}');
INSERT INTO `wp_system_config` VALUES ('41', 'upload', '允许的视频格式', 'videoAllowFiles', 'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid', '13', null);
INSERT INTO `wp_system_config` VALUES ('42', 'upload', '允许生成缩略图尺寸', 'imageAllowSize', '', '13', '{\"hint\":\"格式：宽*高，例如：100*100\"}');
INSERT INTO `wp_system_config` VALUES ('54', 'member', '用户模块功能', 'actionList', 'u/passport/register,u/passport/login,u/passport/third-auth,u/passport/logout,u/passport/find-password,u/default/index,u/account/profile,u/account/reset-password,u/account/reset-username,u/account/bind', '4', '{\"list\":{\"u/passport/register\":\"注册\",\"u/passport/login\":\"登录\",\"u/passport/third-auth\":\"第三方登录\",\"u/passport/logout\":\"退出\",\"u/passport/find-password\":\"找回密码\",\"u/default/index\":\"个人中心\",\"u/account/profile\":\"用户资料\",\"u/account/reset-password\":\"重置密码\",\"u/account/reset-username\":\"修改用户名\",\"u/account/bind\":\"账号绑定\",\"u/account/third-bind\":\"第三方账号绑定\"},\"other\":{\"multiple\":true,\"data-placeholder\":\"请选择开启的功能\"}}');
INSERT INTO `wp_system_config` VALUES ('55', 'member', '用户注册方式', 'registerMode', '', '4', '{\"list\":{\"cellphone\":\"短信注册\",\"fast\":\"短信快速登录\",\"email\":\"邮箱注册\",\"username\":\"用户名密码注册\",\"third\":\"第三方账户注册\"},\"other\":{\"multiple\":true,\"data-placeholder\":\"请选择用户注册方式\"}}');
INSERT INTO `wp_system_config` VALUES ('56', 'member', '内容关联配置', 'relationContent', '[{\"title\":\"我的收藏\",\"slug\":\"collection\",\"model_id\":\"7\",\"template\":\"\"},{\"title\":\"我的反馈\",\"slug\":\"feedback\",\"model_id\":\"8\",\"template\":\"\"}]', '0', null);
INSERT INTO `wp_system_config` VALUES ('57', 'member', '内容发布配置', 'publishContent', '', '0', null);
INSERT INTO `wp_system_config` VALUES ('58', 'member', '开启内容发布审核', 'examine', '0', '0', '{\"list\":{\"1\":\"开启\",\"0\":\"关闭\"}}');
INSERT INTO `wp_system_config` VALUES ('59', 'member', '用户模块页面布局', 'layout', 'user', '0', null);
INSERT INTO `wp_system_config` VALUES ('60', 'member', '通行证页面布局', 'layoutPassport', 'main', '0', null);
INSERT INTO `wp_system_config` VALUES ('61', 'member', '默认登录方式', 'defaultLogin', 'password', '4', '{\"list\":{\"password\":\"账户登录\",\"cellphone\":\"短信登录\",\"email\":\"邮箱登录\"},\"other\":{\"prety\":true,\"data-placeholder\":\"请选择默认登录方式\"}}');
INSERT INTO `wp_system_config` VALUES ('62', 'member', '默认注册方式', 'defaultRegister', 'cellphone', '4', '{\"list\":{\"username\":\"账户注册\",\"cellphone\":\"短信注册\",\"email\":\"邮箱注册\"},\"other\":{\"prety\":true,\"data-placeholder\":\"请选择默认注册方式\"}}');
INSERT INTO `wp_system_config` VALUES ('63', 'member', '默认找回密码方式', 'defaultFindPassword', 'cellphone', '4', '{\"list\":{\"cellphone\":\"短信找回\",\"email\":\"邮箱找回\"},\"other\":{\"prety\":true,\"data-placeholder\":\"请选择默认找回方式\"}}');
INSERT INTO `wp_system_config` VALUES ('64', 'member', '登录默认跳转位置', 'jumpLink', 'index', '1', '{\"hint\":\"请填写url链接或者<code>$this->generateUserUrl()</code>方法允许的值。\"}');
INSERT INTO `wp_system_config` VALUES ('70', 'sms', '开启短信', 'enable', '0', '5', '{\"list\":{\"1\":\"阿里云\",\"2\":\"腾讯云\",\"0\":\"关闭\"}}');
INSERT INTO `wp_system_config` VALUES ('71', 'sms', 'App Id', 'appid', '', '1', null);
INSERT INTO `wp_system_config` VALUES ('72', 'sms', 'App Key', 'appkey', '', '2', null);
INSERT INTO `wp_system_config` VALUES ('73', 'sms', '国内短信签名', 'signName', '', '1', null);
INSERT INTO `wp_system_config` VALUES ('74', 'sms', '国内验证码模板', 'tplCode', '', '1', '{\"hint\":\"短信模板\"}');
INSERT INTO `wp_system_config` VALUES ('78', 'sms', '国外短信签名', 'signNameAbroad', '', '1', null);
INSERT INTO `wp_system_config` VALUES ('79', 'sms', '国外验证码模板', 'tplCodeAbroad', '', '1', '{\"hint\":\"短信模板\"}');
INSERT INTO `wp_system_config` VALUES ('83', 'sms', '手机国际区号', 'cellphoneCode', '中国=0086\r\n中国香港=00852\r\n中国澳门=00853\r\n中国台湾=00886\r\n美国、加拿大=001\r\n巴西=0055\r\n马来西亚=0060\r\n澳洲=0061\r\n日本=0081\r\n韩国=0082\r\n新加坡=0065\r\n英国=0044\r\n法国=0033\r\n俄罗斯=007\r\n印度=0091\r\n泰国=0066\r\n德国=0049\r\n印尼=0062\r\n柬埔寨=00855\r\n缅甸=0095\r\n文莱=00673\r\n菲律宾=0063\r\n越南=0084\r\n老挝=00856', '3', '{\"hint\":\"格式：国家=代码，多个请换行。\"}');
INSERT INTO `wp_system_config` VALUES ('90', 'third', '第三方授权配置', 'setting', '', '0', null);
INSERT INTO `wp_system_config` VALUES ('91', 'third', 'Api端第三方授权回调', 'thirdJumpLink', '{\"success\":\"\",\"fail\":\"\"}', '0', '');
INSERT INTO `wp_system_config` VALUES ('92', 'third', 'Api端微信授权方式', 'wxScopes', 'snsapi_base', '5', '{\"list\":{\"snsapi_base\":\"静默授权\",\"snsapi_userinfo\":\"网页授权\"}}');
INSERT INTO `wp_system_config` VALUES ('93', 'third', '微信可分享客户端', 'wxShareApi', 'onMenuShareTimeline,onMenuShareAppMessage', '13', '');
INSERT INTO `wp_system_config` VALUES ('200', 'custom', 'Header部分代码', 'headerCode', '', '3', '{\"hint\":\"常用于放统计代码。\"}');
INSERT INTO `wp_system_config` VALUES ('201', 'custom', 'Footer部分代码', 'footerCode', '', '3', '{\"hint\":\"常用于放统计代码。\"}');

-- ----------------------------
-- Table structure for wp_system_fileslog
-- ----------------------------
DROP TABLE IF EXISTS `wp_system_fileslog`;
CREATE TABLE `wp_system_fileslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `savename` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件名',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件名',
  `folder` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default' COMMENT '所在文件夹',
  `savepath` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件路径',
  `width` smallint(5) unsigned DEFAULT NULL COMMENT '宽度',
  `height` smallint(5) unsigned DEFAULT NULL COMMENT '高度',
  `ext` char(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件扩展名',
  `size` int(10) unsigned NOT NULL COMMENT '文件大小kb',
  `type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件类型',
  `thumb` varchar(5000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '缩略图',
  PRIMARY KEY (`id`),
  KEY `type` (`savename`,`name`),
  KEY `name` (`name`),
  KEY `ext` (`ext`),
  KEY `size` (`size`),
  KEY `folder` (`folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文件上传记录';

-- ----------------------------
-- Records of wp_system_fileslog
-- ----------------------------

-- ----------------------------
-- Table structure for wp_system_log
-- ----------------------------
DROP TABLE IF EXISTS `wp_system_log`;
CREATE TABLE `wp_system_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` char(30) COLLATE utf8_unicode_ci NOT NULL,
  `crate_user` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `operation_type` enum('delete','update','create','login') COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `create_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='操作日志';

-- ----------------------------
-- Records of wp_system_log
-- ----------------------------

-- ----------------------------
-- Table structure for wp_system_menu
-- ----------------------------
DROP TABLE IF EXISTS `wp_system_menu`;
CREATE TABLE `wp_system_menu` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL COMMENT '父级id',
  `type` tinyint(1) unsigned NOT NULL COMMENT '菜单类型（0：控制器方法，1：链接：2：功能型）',
  `title` varchar(70) COLLATE utf8_unicode_ci NOT NULL COMMENT '菜单名',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态',
  `sort` smallint(5) unsigned DEFAULT NULL COMMENT '排序',
  `link` char(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '链接',
  `param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '参数',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`type`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='后台菜单';

-- ----------------------------
-- Records of wp_system_menu
-- ----------------------------
INSERT INTO `wp_system_menu` VALUES ('1', '0', '1', '内容管理', '1', '1', '#tab-content', '');
INSERT INTO `wp_system_menu` VALUES ('2', '0', '1', '内容设计', '1', '2', '#tab-design', '');
INSERT INTO `wp_system_menu` VALUES ('3', '0', '1', '用户管理', '1', '3', '#tab-user', '');
INSERT INTO `wp_system_menu` VALUES ('4', '0', '1', '系统设置', '1', '4', '#tab-system', '');
INSERT INTO `wp_system_menu` VALUES ('5', '1', '2', '内容管理', '1', '5', 'prototype/category/expand_nav', '');
INSERT INTO `wp_system_menu` VALUES ('6', '4', '0', '后台菜单管理', '1', '47', 'menu/index', 'group=0');
INSERT INTO `wp_system_menu` VALUES ('7', '2', '0', '模型管理', '1', '10', 'prototype/model/index', '');
INSERT INTO `wp_system_menu` VALUES ('8', '2', '0', '栏目管理', '1', '11', 'prototype/category/index', '');
INSERT INTO `wp_system_menu` VALUES ('9', '3', '1', '管理员管理', '1', '32', 'javascript:;', '');
INSERT INTO `wp_system_menu` VALUES ('10', '9', '0', '角色管理', '1', '37', 'auth/role', '');
INSERT INTO `wp_system_menu` VALUES ('11', '9', '0', '用户管理', '1', '39', 'user/index', '');
INSERT INTO `wp_system_menu` VALUES ('18', '4', '0', '系统设置', '1', '40', 'config/index', 'scope=site');
INSERT INTO `wp_system_menu` VALUES ('29', '3', '1', '用户管理', '1', '31', 'javascript:;', '');
INSERT INTO `wp_system_menu` VALUES ('30', '1', '2', '碎片管理', '1', '6', 'fragment/category/expand_nav', '');
INSERT INTO `wp_system_menu` VALUES ('31', '2', '0', '碎片设计', '1', '19', 'fragment/category/index', '');
INSERT INTO `wp_system_menu` VALUES ('32', '2', '0', '全局碎片设计', '1', '29', 'config/custom', '');
INSERT INTO `wp_system_menu` VALUES ('34', '1', '2', '表单管理', '1', '7', 'prototype/form/expand_nav', '');
INSERT INTO `wp_system_menu` VALUES ('35', '4', '0', '邮件设置', '1', '41', 'config/index', 'scope=email');
INSERT INTO `wp_system_menu` VALUES ('37', '4', '0', '标签管理', '1', '46', 'tag/index', '');
INSERT INTO `wp_system_menu` VALUES ('40', '4', '0', '上传设置', '1', '42', 'config/index', 'scope=upload');
INSERT INTO `wp_system_menu` VALUES ('41', '2', '0', '站点管理', '1', '9', 'site-manage/index', '');
INSERT INTO `wp_system_menu` VALUES ('42', '4', '0', '日志管理', '1', '45', 'log/index', '');
INSERT INTO `wp_system_menu` VALUES ('43', '2', '0', '数据导入', '1', '30', 'import/prototype', '');
INSERT INTO `wp_system_menu` VALUES ('44', '29', '0', '用户列表', '1', '33', 'member/index', '');
INSERT INTO `wp_system_menu` VALUES ('45', '29', '0', '用户配置', '1', '34', 'config/index', 'scope=member');
INSERT INTO `wp_system_menu` VALUES ('46', '4', '0', '短信设置', '1', '43', 'config/index', 'scope=sms');
INSERT INTO `wp_system_menu` VALUES ('47', '4', '0', '第三方账号', '1', '44', 'config/index', 'scope=third');
INSERT INTO `wp_system_menu` VALUES ('48', '1', '0', '评论管理', '1', '48', 'comment/index', '');
INSERT INTO `wp_system_menu` VALUES ('49', '4', '0', '铭感词管理', '1', '49', 'sensitive-words/index', '');

-- ----------------------------
-- Table structure for wp_system_user
-- ----------------------------
DROP TABLE IF EXISTS `wp_system_user`;
CREATE TABLE `wp_system_user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(80) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(70) COLLATE utf8_unicode_ci NOT NULL COMMENT '密码',
  `mobile` char(11) CHARACTER SET utf8 DEFAULT NULL COMMENT '手机号码',
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '用户状态',
  `auth_key` char(70) CHARACTER SET utf8 DEFAULT '' COMMENT '用户的（cookie）认证密钥',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status` (`status`),
  KEY `username_2` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='管理员用户表';

-- ----------------------------
-- Records of wp_system_user
-- ----------------------------
INSERT INTO `wp_system_user` VALUES ('2', 'admin', '$2y$13$adF9sxKv6pzO5WCl.yco9u1y/1SH6/Z1u/HJANXJaZ35v8IwsI7Xy', '', '', '1', 'hmbltqaakj0W36osnvQE5egnUIMOiGrw', '1457534391');

-- ----------------------------
-- Table structure for wp_tag
-- ----------------------------
DROP TABLE IF EXISTS `wp_tag`;
CREATE TABLE `wp_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='tag标签';

-- ----------------------------
-- Records of wp_tag
-- ----------------------------

-- ----------------------------
-- Table structure for wp_tag_relation
-- ----------------------------
DROP TABLE IF EXISTS `wp_tag_relation`;
CREATE TABLE `wp_tag_relation` (
  `model_id` smallint(5) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  `data_id` int(10) unsigned NOT NULL,
  KEY `tag_id` (`tag_id`),
  KEY `model_id` (`model_id`),
  CONSTRAINT `wp_tag_relation_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `wp_tag` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_tag_relation_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `wp_prototype_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='tag和内容关联表';

-- ----------------------------
-- Records of wp_tag_relation
-- ----------------------------

-- ----------------------------
-- Table structure for wp_user
-- ----------------------------
DROP TABLE IF EXISTS `wp_user`;
CREATE TABLE `wp_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_type` enum('user','admin') COLLATE utf8_unicode_ci DEFAULT 'user' COMMENT '账户类型',
  `username` char(36) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '用户名',
  `password` char(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '密码',
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `cellphone_code` char(11) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '手机号国际代码',
  `cellphone` char(11) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '手机',
  `is_enable` tinyint(1) unsigned DEFAULT '1',
  `create_time` int(10) unsigned DEFAULT NULL,
  `auth_key` char(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';

-- ----------------------------
-- Records of wp_user
-- ----------------------------

-- ----------------------------
-- Table structure for wp_user_auth_token
-- ----------------------------
DROP TABLE IF EXISTS `wp_user_auth_token`;
CREATE TABLE `wp_user_auth_token` (
  `token` char(70) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('login','register','reset','loginApi') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'token类型',
  `value` int(10) unsigned DEFAULT NULL COMMENT '值，存储用户id或验证码。',
  `create_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户认证表';

-- ----------------------------
-- Records of wp_user_auth_token
-- ----------------------------
INSERT INTO `wp_user_auth_token` VALUES ('0d22cba56d0f899aa546cca593996916', 'register', '591488', '1539589297');

-- ----------------------------
-- Table structure for wp_user_comment
-- ----------------------------
DROP TABLE IF EXISTS `wp_user_comment`;
CREATE TABLE `wp_user_comment` (
  `comment_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `type` enum('like','bad') DEFAULT NULL,
  KEY `comment_id` (`comment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wp_user_comment_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `wp_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_user_comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `wp_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户和评论关联表';

-- ----------------------------
-- Records of wp_user_comment
-- ----------------------------

-- ----------------------------
-- Table structure for wp_user_profile
-- ----------------------------
DROP TABLE IF EXISTS `wp_user_profile`;
CREATE TABLE `wp_user_profile` (
  `user_id` int(10) unsigned NOT NULL,
  `nickname` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '头像',
  `gender` enum('male','female','secrecy') COLLATE utf8_unicode_ci DEFAULT 'secrecy' COMMENT '性别',
  `birthday` char(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '生日',
  `blood` enum('A','B','O','AB') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '血型',
  `country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '国家',
  `province` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '省',
  `city` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '市',
  `area` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '县区',
  `street` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '街道',
  `signature` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '签名',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `wp_user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `wp_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户资料';

-- ----------------------------
-- Records of wp_user_profile
-- ----------------------------

-- ----------------------------
-- Table structure for wp_user_relation
-- ----------------------------
DROP TABLE IF EXISTS `wp_user_relation`;
CREATE TABLE `wp_user_relation` (
  `user_id` int(10) unsigned NOT NULL,
  `user_model_id` smallint(5) unsigned NOT NULL,
  `user_data_id` int(10) unsigned NOT NULL,
  `relation_type` char(60) COLLATE utf8_unicode_ci NOT NULL COMMENT '关系',
  `relation_create_time` int(10) unsigned DEFAULT NULL,
  KEY `relation` (`relation_type`),
  KEY `uesr_id` (`user_id`),
  KEY `model_id` (`user_model_id`),
  CONSTRAINT `wp_user_relation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `wp_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wp_user_relation_ibfk_2` FOREIGN KEY (`user_model_id`) REFERENCES `wp_prototype_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户和内容关系表';

-- ----------------------------
-- Records of wp_user_relation
-- ----------------------------

-- ----------------------------
-- Table structure for wp_user_third_account
-- ----------------------------
DROP TABLE IF EXISTS `wp_user_third_account`;
CREATE TABLE `wp_user_third_account` (
  `user_id` int(10) unsigned NOT NULL,
  `client_id` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '客户端',
  `open_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '唯一id',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `raw_data` text COLLATE utf8_unicode_ci COMMENT '原始数据，存储json字符串',
  KEY `user_id` (`user_id`),
  CONSTRAINT `wp_user_third_account_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `wp_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='第三方账户';

-- ----------------------------
-- Records of wp_user_third_account
-- ----------------------------
