/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : xiaoniu

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-03-19 12:59:24
*/

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for `admin_admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin_admin`;
CREATE TABLE `admin_admin`
(
    `id`              int(11) unsigned NOT NULL AUTO_INCREMENT,
    `username`        varchar(60)      NOT NULL DEFAULT '' COMMENT '用户名',
    `password`        varchar(64)      NOT NULL DEFAULT '' COMMENT '登录密码；mb_password加密',
    `avatar`          varchar(255)     NOT NULL DEFAULT '' COMMENT '用户头像，相对于upload/avatar目录',
    `email`           varchar(100)     NOT NULL DEFAULT '' COMMENT '登录邮箱',
    `email_code`      varchar(60)               DEFAULT NULL COMMENT '激活码',
    `phone`           bigint(11) unsigned       DEFAULT NULL COMMENT '手机号',
    `status`          tinyint(1)       NOT NULL DEFAULT 1 COMMENT '用户状态 0：禁用； 1：正常',
    `register_time`   int(10) unsigned NOT NULL DEFAULT 0 COMMENT '注册时间',
    `last_login_ip`   varchar(16)      NOT NULL DEFAULT '' COMMENT '最后登录ip',
    `last_login_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后登录时间',
    `remark`          varchar(100)              DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_login_key` (`username`) USING BTREE
) ENGINE = MyISAM
  AUTO_INCREMENT = 24
  DEFAULT CHARSET = utf8;

-- ----------------------------
-- Records of admin_admin
-- ----------------------------
INSERT INTO `admin_admin`
VALUES ('1', 'admin', '16ce8ff0a1db1848ccb732fbffc1dc19', '', 'master_yichen@163.com', '', '', '1', '1449199996',
        '', '0', '11');

-- ----------------------------
-- Table structure for `admin_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `admin_auth_group`;
CREATE TABLE `admin_auth_group`
(
    `id`     int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title`  char(100)        NOT NULL DEFAULT '',
    `status` tinyint(1)       NOT NULL DEFAULT 1,
    `rules`  text                      DEFAULT NULL COMMENT '规则id',
    PRIMARY KEY (`id`)
) ENGINE = MyISAM
  AUTO_INCREMENT = 7
  DEFAULT CHARSET = utf8 COMMENT ='用户组表';

-- ----------------------------
-- Records of admin_auth_group
-- ----------------------------
INSERT INTO `admin_auth_group`
VALUES ('1', '超级管理员', '1', '1,1,2,3,4,5,6,7,8,9,10,11,12,13,14,14');

-- ----------------------------
-- Table structure for `admin_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `admin_auth_group_access`;
CREATE TABLE `admin_auth_group_access`
(
    `admin_id` int(11) unsigned NOT NULL COMMENT '用户id',
    `group_id` int(11) unsigned NOT NULL COMMENT '用户组id',
    UNIQUE KEY `uid_group_id` (`admin_id`, `group_id`),
    KEY `uid` (`admin_id`),
    KEY `group_id` (`group_id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8 COMMENT ='用户组明细表';

-- ----------------------------
-- Records of admin_auth_group_access
-- ----------------------------
INSERT INTO `admin_auth_group_access`
VALUES ('1', '1');

-- ----------------------------
-- Table structure for `admin_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `admin_auth_rule`;
CREATE TABLE `admin_auth_rule`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    `pid`       int(11) unsigned          DEFAULT 0 COMMENT '父级id',
    `name`      char(80)                  DEFAULT '' COMMENT '规则唯一标识',
    `title`     char(20)                  DEFAULT '' COMMENT '规则中文名称',
    `status`    tinyint(1)                DEFAULT 1 COMMENT '状态：为1正常，为0禁用',
    `is_menu`   tinyint(1) unsigned       DEFAULT 0 COMMENT '菜单显示',
    `condition` char(100)                 DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
    `type`      tinyint(1)                DEFAULT 1,
    `sort`      int(5)                    DEFAULT 999,
    `icon`      varchar(40)               DEFAULT '',
    `z_index`   int              NOT NULL DEFAULT '0' COMMENT '菜单位置 0 左侧  1 顶部(只有一级菜单 才能开启顶部展示)',
    PRIMARY KEY (`id`)
) ENGINE = MyISAM
  AUTO_INCREMENT = 23
  DEFAULT CHARSET = utf8 COMMENT ='规则表';

-- ----------------------------
-- Records of admin_auth_rule
-- ----------------------------
INSERT INTO `admin_auth_rule`
VALUES ('1', '15', 'admin/auth/index', '权限控制', '1', '1', '', '1', '50', 'layui-icon-auz', 0);
INSERT INTO `admin_auth_rule`
VALUES ('2', '1', 'admin/auth/index', '权限管理', '1', '1', '', '1', '1', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('3', '2', 'admin/auth/add', '添加', '1', '0', '', '1', '2', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('4', '2', 'admin/auth/edit', '编辑', '1', '0', '', '1', '3', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('5', '2', 'admin/auth/delete', '删除', '1', '0', '', '1', '4', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('6', '1', 'admin/AuthGroup/index', '用户组管理', '1', '1', '', '1', '999', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('7', '6', 'admin/AuthGroup/add', '添加', '1', '0', '', '1', '999', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('8', '6', 'admin/AuthGroup/edit', '编辑', '1', '0', '', '1', '999', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('9', '6', 'admin/AuthGroup/delete', '删除', '1', '0', '', '1', '39', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('10', '1', 'admin/admin/index', '后台管理员', '1', '1', '', '1', '3', '', 0);
INSERT INTO `admin_auth_rule`
VALUES ('11', '10', 'admin/admin/add', '添加', '1', '0', '', '1', '999', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('12', '10', 'admin/admin/edit', '编辑', '1', '0', '', '1', '999', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('13', '10', 'admin/admin/delete', '删除', '1', '0', '', '1', '999', null, 0);
INSERT INTO `admin_auth_rule`
VALUES ('14', '0', 'admin/index/home', '管理首页', '1', '1', '', '1', '1', 'layui-icon-home', 0);
INSERT INTO `admin_auth_rule`
VALUES ('15', '0', 'admin/config/base', '系统管理', '1', '1', '', '1', '999', 'layui-icon-set', 0);
INSERT INTO `admin_auth_rule`
VALUES ('16', '21', 'admin/config/base', '基本设置', '1', '1', '', '1', '999', '', 0);
INSERT INTO `admin_auth_rule`
VALUES ('17', '21', 'admin/config/upload', '上传配置', '1', '1', '', '1', '999', '', 0);
INSERT INTO `admin_auth_rule`
VALUES ('18', '0', 'admin/upload_files/index', '上传管理', '1', '1', '', '1', '40', 'layui-icon-picture', 0);
INSERT INTO `admin_auth_rule`
VALUES ('19', '18', 'admin/upload_files/delete', '删除文件', '1', '0', '', '1', '999', '', 0);
INSERT INTO `admin_auth_rule`
VALUES ('21', '15', '', '配置管理', '1', '1', '', '1', '999', '', 0);
INSERT INTO `admin_auth_rule`
VALUES ('22', '15', 'admin/webLog/index', '日志管理', '1', '1', '', '1', '999', '', 0);
INSERT INTO `admin_auth_rule`
VALUES ('24', '21', 'admin/config/payment', '支付配置', '1', '1', '', '1', '999', '', 0);

-- ----------------------------
-- Table structure for `admin_upload_files`
-- ----------------------------
DROP TABLE IF EXISTS `admin_upload_files`;
CREATE TABLE `admin_upload_files`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
    `storage`     tinyint(1)                DEFAULT 0 COMMENT '存储位置 0本地',
    `app`         smallint(4)               DEFAULT 0 COMMENT '来自应用 0前台 1后台',
    `user_id`     int(11)          NOT NULL DEFAULT 0 COMMENT '根据app类型判断用户类型',
    `file_name`   varchar(100)              DEFAULT '',
    `file_size`   int(11)                   DEFAULT 0,
    `extension`   varchar(10)               DEFAULT '' COMMENT '文件后缀',
    `url`         varchar(500)              DEFAULT '' COMMENT '图片路径',
    `create_time` int(11)                   DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = MyISAM
  AUTO_INCREMENT = 339
  DEFAULT CHARSET = utf8;

-- 日志记录
DROP TABLE IF EXISTS `admin_web_log`;
CREATE TABLE `admin_web_log`
(
    `id`            int unsigned                                            NOT NULL AUTO_INCREMENT COMMENT '日志主键',
    `uid`           smallint unsigned                                       NOT NULL COMMENT '用户id',
    `ip`            char(15) CHARACTER SET utf8 COLLATE utf8_general_ci     NOT NULL COMMENT '访客ip',
    `location`      varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '访客地址',
    `os`            varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作系统',
    `browser`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '浏览器',
    `url`           varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'url',
    `module`        char(20) CHARACTER SET utf8 COLLATE utf8_general_ci     NOT NULL COMMENT '模块',
    `controller`    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '控制器',
    `action`        varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作方法',
    `method`        varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL DEFAULT 'GET' COMMENT '请求类型',
    `data`          text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL COMMENT '请求的param数据，serialize后的',
    `otime`         int unsigned                                            NOT NULL COMMENT '操作时间',
    `response_data` text COMMENT '响应数据，serialize后的',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `uid` (`uid`) USING BTREE,
    KEY `ip` (`ip`) USING BTREE,
    KEY `otime` (`otime`) USING BTREE,
    KEY `module` (`module`) USING BTREE,
    KEY `controller` (`controller`) USING BTREE,
    KEY `method` (`method`) USING BTREE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='网站日志';