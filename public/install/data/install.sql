/*
 Navicat Premium Data Transfer

 Source Server         : 生产
 Source Server Type    : MySQL
 Source Server Version : 50742
 Source Host           : rm-2zeh33z3495757a36.mysql.rds.aliyuncs.com:3306
 Source Schema         : erui_ecp_demo

 Target Server Type    : MySQL
 Target Server Version : 50742
 File Encoding         : 65001

 Date: 28/10/2024 13:51:18
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs`  (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员Id',
  `created_ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时的IP',
  `browser_type` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时浏览器类型',
  `log_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1：成功；0：失败',
  `description` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `log_action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '请求方法',
  `log_method` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '请求类型/请求方式',
  `request_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '请求参数',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N' COMMENT '是否删除：1：是；0：否',
  `created_at` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `log_duration` decimal(20, 12) UNSIGNED NULL DEFAULT 0.000000000000,
  `request_url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  PRIMARY KEY (`log_id`) USING BTREE,
  INDEX `admin_id`(`user_id`) USING BTREE,
  INDEX `log_status`(`log_status`) USING BTREE,
  INDEX `is_delete`(`deleted_flag`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员操作日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_logs
-- ----------------------------

-- ----------------------------
-- Table structure for bank
-- ----------------------------
DROP TABLE IF EXISTS `bank`;
CREATE TABLE `bank`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '编码',
  `country_id` bigint(20) NULL DEFAULT NULL COMMENT '国家地区',
  `admin_division_id` bigint(20) NULL DEFAULT NULL,
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '使用状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '数据状态',
  `creator_id` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modify_org_id` bigint(20) NULL DEFAULT NULL,
  `modifier_id` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NULL DEFAULT NULL COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `province` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '省份',
  `municipality` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `county` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '国家地区',
  `master_id` bigint(20) NULL DEFAULT NULL COMMENT '主数据内码',
  `city_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '城市',
  `province_id` bigint(20) NOT NULL DEFAULT 1,
  `fin_type_id` bigint(20) NOT NULL DEFAULT 1,
  `union_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '联行号',
  `swift_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT 'Swfit Code',
  `name_eng` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称英文',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '地址',
  `tele_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '电话',
  `fax` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '传真',
  `bank_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `routing_num` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT 'Routing Number',
  `iban` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `address_eng` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '地址(英文)',
  `other_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '其他代码',
  `bank_categ` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `bank_cate_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '银行类别名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_T_BD_BEBANK_NUMBER`(`number`) USING BTREE,
  INDEX `IDX_T_BD_BEBANK_ES`(`enable`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '行名行号-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bank
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill`;
CREATE TABLE `bid_bill`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '竞价单号',
  `bill_date` datetime(0) NULL DEFAULT NULL COMMENT '发布时间',
  `biz_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '竞价范围\r\n1:所有供应商\r\n2:指定供应商',
  `check_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '资审方式\r\n1:资格预审\r\n2:资格后审\r\n3:资格免审',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  `person_id` bigint(20) NULL DEFAULT 0 COMMENT '采购员',
  `deli_date` datetime(0) NULL DEFAULT NULL COMMENT '交货日期',
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交货地址',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '联系电话',
  `pay_cond_id` bigint(20) NULL DEFAULT 0 COMMENT '付款条件',
  `settle_type_id` bigint(20) NULL DEFAULT 0 COMMENT '结算方式',
  `curr_id` bigint(20) NULL DEFAULT 0 COMMENT '结算币别',
  `tax_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '计税类型',
  `inv_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发票种类',
  `sum_amount` decimal(26, 6) NULL DEFAULT 0.000000 COMMENT '汇总金额',
  `sum_tax` decimal(26, 6) NULL DEFAULT 0.000000 COMMENT '汇总税额',
  `sum_tax_amount` decimal(26, 6) NULL DEFAULT 0.000000 COMMENT '竞价基准金额',
  `sum_qty` decimal(20, 6) NULL DEFAULT 0.000000 COMMENT '合计数量',
  `cash_deposit` decimal(20, 6) NULL DEFAULT 0.000000 COMMENT '竞价保证金',
  `deposit_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否收取保证金 Y 是 N  否',
  `enroll_date` datetime(0) NULL DEFAULT NULL COMMENT '报名截止时间',
  `open_date` datetime(0) NULL DEFAULT NULL COMMENT '预计竞价开始时间',
  `result_date` datetime(0) NULL DEFAULT NULL COMMENT '预计公布结果时间',
  `bid_time` int(11) NULL DEFAULT 0 COMMENT '每轮竞价时长(分钟)',
  `last_time` int(11) NULL DEFAULT 0 COMMENT '倒计时内有报价/分钟',
  `delay_time` int(11) NULL DEFAULT 0 COMMENT '竞价自动延时/分钟',
  `bid_count` int(11) NULL DEFAULT 0 COMMENT '最多报价次数',
  `max_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价最高限额',
  `min_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价最低限额',
  `reducepct` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '每次降价幅度',
  `open1` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '竞价中公开竞价公司',
  `open2` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '竞价中公开竞价排名',
  `open3` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '竞价后公布胜出公司',
  `is_free_quote` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否供应商自由报价',
  `bill_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '单据状态\r\nA:保存\r\nB:已提交\r\nC:已审核',
  `cfm_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '确认状态\r\nA:待确认\r\nB:已确认\r\nC:已打回',
  `bid_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '项目状态\r\nA:报名中\r\nB:已资审\r\nC:竞价中\r\nD:评标中\r\nE:已定标\r\nF:已执行\r\nG:已终止\r\nH:已暂停\r\nI:报名截止\r\nJ:定标审批中\r\nK:已收保证金\r\nL: 待收保证金\r\nM: 已终止',
  `biz_partner_id` bigint(20) NULL DEFAULT 0 COMMENT '商务伙伴',
  `biz_model` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '经营模式',
  `certificate` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '证照要求',
  `biz_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '经营地址',
  `regcapital` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '注册资金(万元)',
  `bill_type_id` bigint(20) NULL DEFAULT 1270377872653029376 COMMENT '单据类型',
  `reduce_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '每次降价方式\r\nA:按比例(%)\r\nB:按金额',
  `bid_number` int(11) NULL DEFAULT 0 COMMENT '参与竞价至少应有几家',
  `auto_confirm` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '自动定标',
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '发布人',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '项目名称',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '备注',
  `quote_mode` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '竞价模式',
  `add_remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '补充说明',
  `multiple_rounds` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '多轮竞价',
  `total_rounds` bigint(20) NULL DEFAULT 1 COMMENT '竞价总轮次',
  `interval_duration` bigint(20) NULL DEFAULT NULL COMMENT '间隔时长',
  `promotion_ratio` bigint(20) NULL DEFAULT NULL COMMENT '晋级比例%',
  `decision_info` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '定标信息',
  `current_round` bigint(20) NULL DEFAULT NULL COMMENT '当前轮次',
  `final_price` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '最终定价',
  `supplier_list` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '供应商名单',
  `ex_price_explain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '异常定价说明',
  `ex_price_explain_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '异常定价说明_详情',
  `combo_field` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `is_filter` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否使用供应商过滤',
  `quotation_trend` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '报价趋势\r\n1:不限制\r\n2:仅允许降价\r\n3:仅允许加价',
  `required_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求分类',
  `required_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求等级',
  `is_msg` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `pur_officer` bigint(20) NULL DEFAULT NULL COMMENT '采购员（工作流）',
  `pur_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '采购品类',
  `business_type_id` bigint(20) NULL DEFAULT 0 COMMENT '业务类型',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `date_from` date NULL DEFAULT NULL COMMENT '价格有效期从',
  `date_to` date NULL DEFAULT NULL COMMENT '价格有效期至',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_BIDBILL_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `IDX_PUR_BIDBILL_FBILLDATE`(`bill_date`) USING BTREE,
  INDEX `enroll_date`(`enroll_date`) USING BTREE,
  INDEX `open_date`(`open_date`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '竞价发布-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_attach
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_attach`;
CREATE TABLE `bid_bill_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bid_bill_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `inquiry_id`(`bid_bill_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '询单附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_attach
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_entry
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_entry`;
CREATE TABLE `bid_bill_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bid_bill_id` bigint(20) NOT NULL DEFAULT 0,
  `seq` bigint(20) NULL DEFAULT 0,
  `material_id` bigint(20) NULL DEFAULT 0,
  `material_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `asstpro_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `unit_id` bigint(20) NULL DEFAULT 0,
  `qty` decimal(19, 6) NULL DEFAULT 0.000000,
  `price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `tax_rate` decimal(19, 6) NULL DEFAULT 0.000000,
  `tax` decimal(19, 6) NULL DEFAULT 0.000000,
  `tax_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `deli_date` datetime(0) NULL DEFAULT NULL,
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `note` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `entry_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `win_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `win_tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `po_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `pc_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `project_id` bigint(20) NULL DEFAULT 0,
  `trace_id` bigint(20) NULL DEFAULT 0,
  `dct_rate` decimal(19, 6) NULL DEFAULT 0.000000,
  `dct_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `is_update_asinfo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `tax_rate_id` bigint(20) NULL DEFAULT 0,
  `win_supplier_id` bigint(20) NULL DEFAULT 0,
  `win_option` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `win_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `material_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `specification_model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `material_unit_id` bigint(20) NULL DEFAULT NULL,
  `material_name_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `line_type_id` bigint(20) NULL DEFAULT 0,
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `precision` int(11) NULL DEFAULT NULL COMMENT '单位精度',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_BIDBILLENTRY_FID_FSEQ`(`bid_bill_id`, `seq`) USING BTREE,
  INDEX `IDX_PUR_BIDBILLENTRY_FMATID`(`material_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商品分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_entry
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_pay
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_pay`;
CREATE TABLE `bid_bill_pay`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ' COMMENT '单据编号',
  `bid_bill_id` bigint(20) NULL DEFAULT NULL COMMENT '竞价单id',
  `bid_bill_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ' COMMENT '竞价项目单号',
  `bid_bill_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ' COMMENT '竞价项目名称',
  `org_id` bigint(20) NULL DEFAULT NULL COMMENT '组织',
  `supplier_id` bigint(20) NULL DEFAULT NULL COMMENT '供应商',
  `bid_entry_id` bigint(20) NULL DEFAULT NULL COMMENT '竞价单明细id',
  `sure_amount` decimal(23, 2) NULL DEFAULT NULL COMMENT '应缴纳保证金',
  `real_amount` decimal(23, 2) NULL DEFAULT NULL COMMENT '缴纳金额',
  `bill_status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'A:未缴费,B:已缴费未确认,C:已缴费已确认,D:缴费已打回',
  `return_status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT '退还状态 E:退费中 F:已退费',
  `return_id` bigint(20) NULL DEFAULT NULL COMMENT '退保证金人',
  `return_date` datetime(0) NULL DEFAULT NULL COMMENT '退保证金时间',
  `return_certificate` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '退款凭证',
  `return_certificate_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ' COMMENT '款项类型',
  `certificate_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `certificate` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '缴费凭证',
  `remark` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `pay_date` datetime(0) NULL DEFAULT NULL COMMENT '缴纳时间',
  `pay_id` bigint(20) NULL DEFAULT NULL COMMENT '缴纳人',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `audited_by` bigint(20) NULL DEFAULT NULL COMMENT '审核人',
  `audited_at` datetime(0) NULL DEFAULT NULL COMMENT '审核日期',
  `contact_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `contact_phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `contact_email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `bid_bill_id_2`(`bid_bill_id`, `supplier_id`) USING BTREE,
  INDEX `bid_bill_id`(`bid_bill_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '竞价缴费信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_pay
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_quote
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_quote`;
CREATE TABLE `bid_bill_quote`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '报价单ID',
  `bid_bill_id` bigint(20) NOT NULL DEFAULT 0,
  `ranking` bigint(20) NOT NULL DEFAULT 0,
  `supplier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `amount` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价金额',
  `quote_date` datetime(0) NULL DEFAULT NULL COMMENT '报价时间',
  `reduceamt` decimal(19, 2) NOT NULL DEFAULT 0.00 COMMENT '降价额度',
  `sup_preduceamt` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '降价额度',
  `quo_currency_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价币别',
  `quo_supp_amount` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价金额',
  `exchange` decimal(19, 6) NOT NULL DEFAULT 1.000000 COMMENT '汇率',
  `quote_material` bigint(20) NOT NULL DEFAULT 0 COMMENT '物料编码',
  `material_row_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '物料分录ID',
  `quote_tax_price` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '报价含税单价',
  `quote_price` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '报价未税单价',
  `quo_tax_rate_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '税率',
  `material_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '物料名称',
  `material_name_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '物料名称',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_BIDBILLQUOTE_FID_FSEQ`(`bid_bill_id`, `ranking`) USING BTREE,
  INDEX `IDX_PUR_BIDBILLQUOTE_FSUPID`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_quote
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_quote_attach
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_quote_attach`;
CREATE TABLE `bid_bill_quote_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bid_bill_quote_id` bigint(20) NOT NULL,
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '竞价报价附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_quote_attach
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_sub
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_sub`;
CREATE TABLE `bid_bill_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bid_bill_id` bigint(20) NOT NULL DEFAULT 0,
  `created_by` bigint(20) NOT NULL DEFAULT 0,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `auditor_by` bigint(20) NULL DEFAULT 0,
  `auditor_at` datetime(0) NULL DEFAULT NULL,
  `cfm_id` bigint(20) NULL DEFAULT 0,
  `cfm_at` datetime(0) NULL DEFAULT NULL,
  `decider_by` bigint(20) NULL DEFAULT 0,
  `decider_at` datetime(0) NULL DEFAULT NULL,
  `orgin` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `push_notice` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `push_price` int(11) NULL DEFAULT 0,
  `push_souce` int(11) NULL DEFAULT 0,
  `enroll_number` int(11) NULL DEFAULT 0 COMMENT '报名/确认数',
  `bid_num` int(11) NULL DEFAULT 0 COMMENT '参与竞价数',
  `amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '中标金额',
  `bid_profit` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '竞价利润',
  `supplier_id` bigint(20) NULL DEFAULT 0 COMMENT '中标供应商',
  `audit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '定标意见',
  `terminate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '异常处理意见',
  `terminate_by` bigint(20) NULL DEFAULT NULL COMMENT '终止人',
  `terminate_at` datetime(0) NULL DEFAULT NULL COMMENT '终止时间',
  `finished_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '结束原因',
  `finished_at` date NULL DEFAULT NULL,
  `finished_by` bigint(20) NULL DEFAULT NULL COMMENT '结束人',
  `amount1` decimal(19, 6) NULL DEFAULT 0.000000,
  `supplier_id1` bigint(20) NULL DEFAULT 0,
  `paused_at` datetime(0) NULL DEFAULT NULL COMMENT '竞价暂停时间',
  `paused_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '暂停原因',
  `pause_amt` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '竞价暂停最新报价',
  `paused_by` bigint(20) NULL DEFAULT NULL COMMENT '暂停人',
  `pause_start_at` datetime(0) NULL DEFAULT NULL COMMENT '竞价暂停开始时间',
  `bid_rest_at` bigint(20) NULL DEFAULT 0 COMMENT '竞价剩余时长(毫秒)',
  `supplier_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `push_result` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `is_free_quote` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  PRIMARY KEY (`id`, `bid_bill_id`) USING BTREE,
  UNIQUE INDEX `unx_bid_bill_id`(`bid_bill_id`) USING BTREE,
  INDEX `idx_pause_amt`(`created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '竞价发布-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_sub
-- ----------------------------

-- ----------------------------
-- Table structure for bid_bill_supplier
-- ----------------------------
DROP TABLE IF EXISTS `bid_bill_supplier`;
CREATE TABLE `bid_bill_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bid_bill_id` bigint(20) NOT NULL DEFAULT 0,
  `seq` bigint(20) NOT NULL DEFAULT 0,
  `supplier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '供应商',
  `enroll_date` datetime(0) NULL DEFAULT NULL COMMENT '报名时间',
  `audit_date` datetime(0) NULL DEFAULT NULL COMMENT '资审时间',
  `pay_date` datetime(0) NULL DEFAULT NULL COMMENT '收保证金时间',
  `return_date` datetime(0) NULL DEFAULT NULL COMMENT '退保证金时间',
  `amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '竞价报价',
  `ranking` int(11) NULL DEFAULT 0 COMMENT '竞价排名',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '供应商备注',
  `entry_status` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '当前状态\r\nA:待资审\r\nB:资审通过\r\nC:资审未通过\r\nD:保证金已审\r\nE:保证金已退\r\nF:已中标\r\nG:未中标\r\nH:报名截止\r\nJ:未竞价\r\nK:保证金未收\r\nL:待缴费\r\nWQR:已缴费未确认\r\nM:竞价中\r\nN:未报名\r\nO:已缴费\r\nP:已暂停\r\nQ:评标中\r\nT:待报名\r\nWCY:未参与\r\nY:已报名\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n',
  `enroll_id` bigint(20) NULL DEFAULT 0 COMMENT '报名人',
  `audit_id` bigint(20) NULL DEFAULT NULL COMMENT '资审人',
  `pay_id` bigint(20) NULL DEFAULT 0 COMMENT '收保证金人',
  `return_id` bigint(20) NULL DEFAULT 0 COMMENT '退保证金人',
  `allow_bid` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '允许竞价',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '附件备注',
  `result` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '竞价结果',
  `contact_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `contact_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `bid_bill_id`(`bid_bill_id`, `supplier_id`) USING BTREE,
  INDEX `IDX_PUR_BIDBILLSUP_FSUPPLIERID`(`supplier_id`) USING BTREE,
  INDEX `IDX_PUR_BIDBILLSUP_FID`(`bid_bill_id`, `seq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '竞价情况分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_bill_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for bid_mode
-- ----------------------------
DROP TABLE IF EXISTS `bid_mode`;
CREATE TABLE `bid_mode`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `org_id` bigint(20) NOT NULL DEFAULT 1,
  `name` varchar(84) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `is_pre_setting` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_BIDMODE_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '采购方式' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bid_mode
-- ----------------------------
INSERT INTO `bid_mode` VALUES (1, 'bid00001', 1, '公开招标', '1', 'C', 1, '2018-09-07 14:28:52', 1, '2018-09-12 17:33:07', '1');
INSERT INTO `bid_mode` VALUES (2, 'bid00002', 1, '邀请招标', '1', 'C', 1, '2024-08-19 15:10:00', 1, '2024-08-19 15:10:00', '1');

-- ----------------------------
-- Table structure for compare
-- ----------------------------
DROP TABLE IF EXISTS `compare`;
CREATE TABLE `compare`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '编号',
  `bill_date` datetime(0) NULL DEFAULT NULL COMMENT '业务日期',
  `biz_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '竞价范围',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  `person_id` bigint(20) NULL DEFAULT 0 COMMENT '采购员id',
  `payment_terms` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '付款条件',
  `settle_type_id` bigint(20) NULL DEFAULT 0 COMMENT '结算方式',
  `curr_id` bigint(20) NULL DEFAULT 0 COMMENT '结算币别',
  `loc_curr_id` bigint(20) NULL DEFAULT 0 COMMENT '本位币',
  `exch_type_id` bigint(20) NULL DEFAULT 0 COMMENT '汇率表',
  `exch_rate` decimal(19, 6) NULL DEFAULT 1.000000 COMMENT '汇率',
  `tax_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '计税类型',
  `sum_tax` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计税额',
  `sum_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计金额',
  `sum_tax_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '价税合计',
  `sum_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计数量',
  `bill_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '单据状态 A保存 B 已提交 C已审核',
  `cfm_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '确认状态',
  `biz_partner_id` bigint(20) NULL DEFAULT 0 COMMENT '商务伙伴',
  `inquiry_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '询单ID',
  `inquiry_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '询价单号',
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交货地址',
  `sup_curr_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '2' COMMENT '报价币种选项',
  `rate_date` datetime(0) NULL DEFAULT NULL COMMENT '汇率日期',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '备注',
  `inquiry_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '询价标题',
  `opinion` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '采纳意见',
  `opinion_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '采纳意见详细',
  `delivery_date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交货期',
  `buyer` bigint(20) NULL DEFAULT NULL,
  `text_field` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `product_owne` bigint(20) NULL DEFAULT NULL,
  `approve` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `related_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '关联单号',
  `required_cat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求分类',
  `business_type_id` bigint(20) NULL DEFAULT 0 COMMENT '业务类型',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '修改人',
  `quoted_num` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '应报价数量',
  `sup_quo_num` bigint(20) NULL DEFAULT NULL COMMENT '报价供应商数量',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '来源',
  `end_date` datetime(0) NULL DEFAULT NULL COMMENT '报价截止日期',
  `sup_scope` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '询价范围',
  `open_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开标方式',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `total_inquiry` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '整单询价',
  `inv_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '发票类型',
  `created_by` bigint(20) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `tax_cal_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `warranty_period` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `date_from` datetime(0) NULL DEFAULT NULL,
  `date_to` datetime(0) NULL DEFAULT NULL,
  `deli_date` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `other_pay_terms_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `annual` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `audit_status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'DRAFT' COMMENT '审核状态 REJECTED 审核未通过PASS 通过 REVIEW待审核 DRAFT草稿',
  `audit_flag` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '审核节点',
  `audit_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前审核人',
  `audit_flag_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `project_manager` bigint(255) NULL DEFAULT NULL COMMENT '易瑞项目经理',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_COMPARE_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `IDX_PUR_COMPARE_FBILLDATE`(`bill_date`) USING BTREE,
  INDEX `IDX_PUR_COMPARE_FBIZPARTNERID`(`biz_partner_id`) USING BTREE,
  INDEX `idx_inquiry_id`(`inquiry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '比价单内容-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compare
-- ----------------------------

-- ----------------------------
-- Table structure for compare_attach
-- ----------------------------
DROP TABLE IF EXISTS `compare_attach`;
CREATE TABLE `compare_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `compare_id` bigint(20) NOT NULL COMMENT '比价id',
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `compare_id`(`compare_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '比单附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compare_attach
-- ----------------------------

-- ----------------------------
-- Table structure for compare_audit
-- ----------------------------
DROP TABLE IF EXISTS `compare_audit`;
CREATE TABLE `compare_audit`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `compare_id` bigint(20) NOT NULL COMMENT '询单id',
  `compare_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '比价单',
  `base` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '基本信息',
  `user_id` bigint(20) NULL DEFAULT NULL COMMENT '后台用户id',
  `status` enum('DRAFT','REJECTED','PASS','REVIEW') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'REVIEW' COMMENT '审核状态\r\nDRAFT:草稿,用于预览\r\nREVIEW：待审核\r\nPASS：审核通过\r\nREJECTED：审核拒绝',
  `audit_flag` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '审核节点 根据组织和审核节点不同',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `audit_at` datetime(0) NULL DEFAULT NULL COMMENT '审核日期',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '修改时间',
  `deleted_at` datetime(0) NULL DEFAULT NULL COMMENT '删除时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  `created_by` bigint(20) NULL DEFAULT NULL,
  `updated_by` bigint(20) NULL DEFAULT NULL,
  `audit_flag_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '比价审核' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compare_audit
-- ----------------------------

-- ----------------------------
-- Table structure for compare_entry
-- ----------------------------
DROP TABLE IF EXISTS `compare_entry`;
CREATE TABLE `compare_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `compare_id` bigint(20) NOT NULL DEFAULT 0,
  `seq` bigint(20) NOT NULL DEFAULT 0 COMMENT '商品分录.分录行号',
  `material_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '商品分录.物料编码',
  `inquiry_entry_id` bigint(20) NULL DEFAULT 0 COMMENT '询价物料ID',
  `quote_entry_id` bigint(20) NULL DEFAULT NULL,
  `quote_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '报价单ID',
  `material_desc` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料描述',
  `asstpro_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.辅助数量',
  `unit_id` bigint(20) NULL DEFAULT 0 COMMENT '单位id',
  `inquiry_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.询价数量（隐藏）',
  `deli_date` datetime(0) NULL DEFAULT NULL COMMENT '交付日期',
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交付地址',
  `deli_type_id` int(11) NULL DEFAULT 0,
  `qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价数量',
  `price` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '商品分录.单价',
  `tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '商品分录.含税单价',
  `dct_rate` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.折扣率(%)',
  `dct_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.折扣额',
  `amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.金额',
  `tax_rate` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.税率(%)',
  `tax` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.税额',
  `tax_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.价税合计',
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `req_org_id` bigint(20) NULL DEFAULT 0,
  `pur_org_id` bigint(20) NULL DEFAULT 0,
  `rcv_org_id` bigint(20) NULL DEFAULT 0,
  `settle_org_id` bigint(20) NULL DEFAULT 0,
  `pay_org_id` bigint(20) NULL DEFAULT 0,
  `note` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.备注（隐藏）',
  `entry_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '商品分录.行状态',
  `po_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `pc_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `project_id` bigint(20) NULL DEFAULT 0,
  `trace_id` bigint(20) NULL DEFAULT 0,
  `tax_rate_id` bigint(20) NULL DEFAULT 0,
  `exclude_unit_tax` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `include_unit_tax` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `quote_curr` bigint(20) NULL DEFAULT 0,
  `exrate` decimal(23, 10) NULL DEFAULT 1.0000000000,
  `valid_num` int(11) NULL DEFAULT 0 COMMENT '有效报价供应商数量',
  `quote_unit_id` bigint(20) NULL DEFAULT NULL COMMENT '商品分录.报价单位',
  `inquiry_unit_id` bigint(20) NULL DEFAULT NULL COMMENT '商品分录.询价单位',
  `fk_erui_zbq` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `material_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `specification_model` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `deli_at` datetime(0) NULL DEFAULT NULL COMMENT '商品分录.交货日期',
  `delive_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.交货方式',
  `deli_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.交货地址',
  `inquire_qty` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.询价数量',
  `big_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.备注',
  `big_note_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品分录.备注_详情',
  `stock_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.存货编码',
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.品牌',
  `material_name_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料名称',
  `line_type_id` bigint(20) NULL DEFAULT 0 COMMENT '商品分录.行类型',
  `budget_price` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.预算单价',
  `budget_amount` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.预算金额',
  `new_material_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料编码',
  `material_code` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料编码',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `unit_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `created_by` bigint(20) NULL DEFAULT NULL,
  `updated_by` bigint(20) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `adopt_flag` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `warranty_period` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_COMPARENTRY_FID_FSEQ`(`compare_id`, `seq`) USING BTREE,
  INDEX `IDX_PUR_COMPARENTRY_FMATID`(`material_id`) USING BTREE,
  INDEX `inquiry_entry_id`(`inquiry_entry_id`) USING BTREE,
  INDEX `quote_id`(`quote_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价详情信息-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compare_entry
-- ----------------------------

-- ----------------------------
-- Table structure for compare_quote
-- ----------------------------
DROP TABLE IF EXISTS `compare_quote`;
CREATE TABLE `compare_quote`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NULL DEFAULT NULL COMMENT '供应商id',
  `quote_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '报价单号',
  `compare_id` bigint(20) NULL DEFAULT NULL COMMENT '比价单号',
  `quote_id` bigint(20) NULL DEFAULT NULL COMMENT '报价单id',
  `total_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价总金额',
  `adopt_total_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '采纳总金额',
  `tax_cal_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '计税类型',
  `delivery_date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交货期',
  `warranty_period` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '质保期（月）',
  `adopt_flag` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '采纳标识',
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_by` bigint(20) NULL DEFAULT NULL,
  `updated_by` bigint(20) NULL DEFAULT NULL,
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `payment_terms` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `other_pay_terms_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `quote_curr` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '报价币种',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `supplier_id`(`supplier_id`) USING BTREE,
  INDEX `quote_no`(`quote_no`) USING BTREE,
  INDEX `compare_id`(`compare_id`) USING BTREE,
  INDEX `quote_id`(`quote_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compare_quote
-- ----------------------------

-- ----------------------------
-- Table structure for country
-- ----------------------------
DROP TABLE IF EXISTS `country`;
CREATE TABLE `country`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `createor_gid` bigint(20) NOT NULL DEFAULT 0,
  `orgid` bigint(20) NOT NULL DEFAULT 0,
  `creator_id` bigint(20) NOT NULL DEFAULT 0,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modifyor_gid` bigint(20) NOT NULL DEFAULT 0,
  `modifier_id` bigint(20) NOT NULL DEFAULT 0,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `disabler_id` bigint(20) NOT NULL DEFAULT 0,
  `disable_date` datetime(0) NULL DEFAULT NULL,
  `master_id` bigint(20) NOT NULL DEFAULT 0,
  `simple_spell` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `two_country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `three_country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `format_plan_id` bigint(20) NOT NULL DEFAULT 0,
  `area_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `numeric_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_T_BD_COUNTRY_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '国家地区-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of country
-- ----------------------------

-- ----------------------------
-- Table structure for currency
-- ----------------------------
DROP TABLE IF EXISTS `currency`;
CREATE TABLE `currency`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '货币代码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `amt_precision` int(11) NOT NULL DEFAULT 0 COMMENT '金额精度',
  `price_precision` int(11) NOT NULL DEFAULT 0 COMMENT '单价精度',
  `is_show_sign` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '显示货币符号',
  `sign` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '币别符号',
  `format` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '显示格式',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '是否系统预置',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '使用状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT 'LOGO',
  `master_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '主数据内码',
  `positive_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `negative_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `separator` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `group_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `sort_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '排序码',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '描述',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_T_BD_CURRENCY_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '币别-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of currency
-- ----------------------------
INSERT INTO `currency` VALUES (1, 'CNY', '人民币', 2, 4, '1', '￥', ' ', '1', '1', 'C', 1, '2024-10-22 10:12:26', 1, '2024-10-21 13:49:46', 1, '2024-10-21 13:49:41', '', 0, ' ', ' ', ' ', ' ', ' ', '');
INSERT INTO `currency` VALUES (2, '123', '123', 2, 4, '', '13', ' ', '0', '1', 'C', 1, '2024-10-25 15:37:50', 0, NULL, 0, NULL, '', 0, ' ', ' ', ' ', ' ', ' ', '');

-- ----------------------------
-- Table structure for division
-- ----------------------------
DROP TABLE IF EXISTS `division`;
CREATE TABLE `division`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '编码',
  `country_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '所属国家或地区',
  `divisionlv_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '行政基础ID',
  `full_spell` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '全拼',
  `simple_spell` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '简拼',
  `city_number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '城市编码',
  `long_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '长编码',
  `parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '父ID',
  `level` int(11) NOT NULL DEFAULT 0 COMMENT '级次',
  `is_leaf` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '是否叶子节点',
  `is_city` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '是否城市',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '是否系统预设',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '使用状态',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `master_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '主数据内码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `full_name` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '全名',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '描述',
  `area_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '参考码',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_BD_ADMINDIVIS_NUM`(`number`) USING BTREE,
  INDEX `IDX_BD_ADMINDIVIS_COUNRYNUM`(`country_id`, `number`) USING BTREE,
  INDEX `IDX_BD_ADMINDIVIS_NAME`(`name`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '行政区划-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of division
-- ----------------------------

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `failed_at` datetime(0) NULL DEFAULT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry
-- ----------------------------
DROP TABLE IF EXISTS `inquiry`;
CREATE TABLE `inquiry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '询价单号',
  `related_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '关联单号',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '询价标题',
  `bill_date` datetime(0) NULL DEFAULT NULL COMMENT '业务日期',
  `deli_date` date NULL DEFAULT NULL COMMENT '交货日期',
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交货地址',
  `req_org_id` bigint(20) NULL DEFAULT 0 COMMENT '需求组织',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  `rcv_org_id` bigint(20) NULL DEFAULT 0 COMMENT '收货组织',
  `settle_org_id` bigint(20) NULL DEFAULT 0 COMMENT '核算组织',
  `pay_org_id` bigint(20) NULL DEFAULT 0 COMMENT '付款组织',
  `person_id` bigint(20) NULL DEFAULT 0 COMMENT '采购员',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '联系电话',
  `pay_cond_id` bigint(20) NULL DEFAULT 0 COMMENT '付款条件',
  `settle_type_id` bigint(20) NULL DEFAULT 0 COMMENT '结算方式',
  `curr_id` bigint(20) NULL DEFAULT 0 COMMENT '结算币别',
  `loc_curr_id` bigint(20) NULL DEFAULT 0 COMMENT '本位币',
  `exch_type_id` bigint(20) NULL DEFAULT 0 COMMENT '汇率表',
  `exch_rate` decimal(19, 6) NULL DEFAULT 1.000000 COMMENT '汇率',
  `tax_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '计税类型',
  `inv_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发票类型',
  `sum_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计金额',
  `sum_tax` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计税额',
  `sum_tax_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '价税合计',
  `sum_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计数量',
  `bill_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '单据状态 A保存 B 已提交 C已审核',
  `cfm_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '确认状态',
  `biz_partner_id` bigint(20) NULL DEFAULT 0 COMMENT '商务伙伴',
  `sup_scope` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '询价范围',
  `end_date` datetime(0) NULL DEFAULT NULL COMMENT '报价截止日期',
  `date_from` date NULL DEFAULT NULL COMMENT '价格有效期从',
  `date_to` date NULL DEFAULT NULL COMMENT '价格有效期至',
  `bill_type_id` bigint(20) NULL DEFAULT 1270375173299638272 COMMENT '单据类型',
  `biz_model` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '经营模式',
  `certificate` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '证照要求',
  `biz_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '经营地址',
  `reg_capital` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '注册资金(万元)',
  `biz_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '项目状态 A报价中 B已开标 C 已定标 D已执行  E已终止',
  `open_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '开标方式',
  `total_inquiry` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '整单询价',
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '发布人',
  `turns` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '询价轮次',
  `sup_quo_num` int(11) NULL DEFAULT 0 COMMENT '报价供应商数量',
  `sup_curr_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '2' COMMENT '报价币种选项',
  `rate_date` datetime(0) NULL DEFAULT NULL COMMENT '汇率日期',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '备注',
  `inquiry_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '询价标题-隐藏',
  `delivery_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交货方式',
  `purchaser_approva` bigint(20) NULL DEFAULT NULL COMMENT '采购员审批',
  `other_pay_terms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '其他付款条件说明',
  `other_pay_terms_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '其他付款条件说明_详情',
  `payment_terms` bigint(20) NULL DEFAULT NULL COMMENT '付款条件',
  `settlement_method` bigint(20) NULL DEFAULT NULL COMMENT '结算方式',
  `settlement_cur` bigint(20) NULL DEFAULT NULL COMMENT '结算币种',
  `tax_cal_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '计税类型',
  `base_cur` bigint(20) NULL DEFAULT NULL COMMENT '本位币',
  `exchange_rate_date` datetime(0) NULL DEFAULT NULL COMMENT '汇率日期',
  `required_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求等级',
  `required_cat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求分类',
  `is_filter` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否使用供应商过滤',
  `text_field` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `check_box_field` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `quoted_num` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '应报价数量',
  `delivery_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '交货期',
  `warranty_period` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '质保期（月）',
  `business_type_id` bigint(20) NULL DEFAULT 0 COMMENT '业务类型',
  `stopped_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '终止原因 终止意见',
  `stopped_by` bigint(20) NULL DEFAULT NULL COMMENT '终止人',
  `stopped_at` datetime(0) NULL DEFAULT NULL COMMENT '终止时间',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '来源',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_INQUIRY_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `IDX_PUR_INQUIRY_FBILLDATE`(`bill_date`) USING BTREE,
  INDEX `IDX_PUR_INQUIRY_FBIZPARTNERID`(`biz_partner_id`) USING BTREE,
  INDEX `bill_status`(`bill_status`, `biz_status`, `open_type`, `end_date`) USING BTREE,
  INDEX `end_date`(`end_date`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '询价单-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry_attach
-- ----------------------------
DROP TABLE IF EXISTS `inquiry_attach`;
CREATE TABLE `inquiry_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inquiry_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `inquiry_id`(`inquiry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '询单附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry_attach
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry_entry
-- ----------------------------
DROP TABLE IF EXISTS `inquiry_entry`;
CREATE TABLE `inquiry_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inquiry_id` bigint(20) NOT NULL,
  `turns` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '询价轮次 多个轮次逗号分隔',
  `seq` bigint(20) NOT NULL DEFAULT 0 COMMENT '商品分录.分录行号',
  `material_id` bigint(20) NULL DEFAULT 0 COMMENT '商品分录.物料编码',
  `material_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '询价轮次 多个轮次逗号分隔',
  `asstpro_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.辅助数量',
  `unit_id` bigint(20) NULL DEFAULT 0,
  `qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.询价数量（隐藏）',
  `deli_date` date NULL DEFAULT NULL,
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `deli_type_id` int(11) NULL DEFAULT 0,
  `price` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '商品分录.单价',
  `tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '商品分录.含税单价',
  `dct_rate` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.折扣率(%)',
  `dct_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.折扣额',
  `amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.金额',
  `tax_rate` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.税率(%)',
  `tax` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.税额',
  `tax_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.价税合计',
  `req_org_id` bigint(20) NULL DEFAULT 0,
  `pur_org_id` bigint(20) NULL DEFAULT 0,
  `rcv_org_id` bigint(20) NULL DEFAULT 0,
  `settle_org_id` bigint(20) NULL DEFAULT 0,
  `pay_org_id` bigint(20) NULL DEFAULT 0,
  `note` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.备注（隐藏）',
  `entry_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '商品分录.行状态',
  `po_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `pc_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `project_id` bigint(20) NULL DEFAULT 0,
  `trace_id` bigint(20) NULL DEFAULT 0,
  `tax_rate_id` bigint(20) NULL DEFAULT 0,
  `inquiry_unit_id` bigint(20) NULL DEFAULT NULL COMMENT '商品分录.询价单位',
  `precision` int(11) NULL DEFAULT NULL COMMENT '单位精度',
  `quote_unit_id` bigint(20) NULL DEFAULT NULL COMMENT '商品分录.报价单位',
  `quote_qty` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.报价数量',
  `material_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料名称',
  `specification_model` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '规格型号',
  `deli_at` datetime(0) NULL DEFAULT NULL COMMENT '商品分录.交货日期',
  `deli_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.交货地址',
  `delive_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.交货方式',
  `inquire_qty` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.询价数量',
  `valid_num` int(11) NULL DEFAULT 0 COMMENT '有效报价供应商数量',
  `big_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.备注',
  `big_note_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品分录.备注_详情',
  `supplier_id` bigint(20) NULL DEFAULT NULL,
  `new_tax_rate_id` bigint(20) NULL DEFAULT NULL,
  `new_qty` decimal(19, 6) NULL DEFAULT NULL,
  `new_tax_amount` decimal(23, 10) NULL DEFAULT NULL,
  `stock_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.存货编码',
  `text_field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.品牌',
  `material_name_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料名称',
  `line_type_id` bigint(20) NULL DEFAULT 0 COMMENT '商品分录.行类型',
  `base_data_field` bigint(20) NULL DEFAULT NULL,
  `material` bigint(20) NULL DEFAULT NULL,
  `budget_price` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.预算单价',
  `budget_amount` decimal(23, 10) NULL DEFAULT NULL COMMENT '商品分录.预算金额',
  `amount_field` decimal(23, 10) NULL DEFAULT NULL,
  `new_material_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.物料编码',
  `boss_goods_id` bigint(20) NULL DEFAULT NULL COMMENT '商品分录.商品ID',
  `material_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '物料编码',
  `warranty_period` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '质保期',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_INQUIRYENTRY_FMATERIALID`(`material_id`) USING BTREE,
  INDEX `IDX_INQUIRYENTRY_FID_FSEQ`(`inquiry_id`, `seq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '物料分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry_entry
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry_entry_sub
-- ----------------------------
DROP TABLE IF EXISTS `inquiry_entry_sub`;
CREATE TABLE `inquiry_entry_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'ID',
  `inquiry_id` bigint(20) NOT NULL,
  `goods_id` bigint(20) NULL DEFAULT 0,
  `goods_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `basic_unit_id` bigint(20) NULL DEFAULT 0,
  `basic_qty` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `asst_unit_id` bigint(20) NULL DEFAULT 0 COMMENT '商品分录.辅助单位',
  `asst_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.辅助数量',
  `loc_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `loc_tax` decimal(19, 6) NULL DEFAULT 0.000000,
  `loc_taxamount` decimal(19, 6) NULL DEFAULT 0.000000,
  `act_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `act_tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `po_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.订单ID',
  `po_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.订单分录ID',
  `pc_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.合同ID',
  `pc_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.合同分录ID',
  `src_bill_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.源单类型',
  `src_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.源单ID',
  `src_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.源单分录ID',
  `sum_quote_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '商品分录.关联报价数量',
  `pr_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.申请单id',
  `pr_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.申请单分录id',
  `pr_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.申请单编号',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `entry_id`(`entry_id`) USING BTREE,
  INDEX `IDX_INQUIRYENTRY_A_FPOENTRYID`(`po_entry_id`) USING BTREE,
  INDEX `IDX_INQUIRY_ID`(`inquiry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '物料分录-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry_entry_sub
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry_sub
-- ----------------------------
DROP TABLE IF EXISTS `inquiry_sub`;
CREATE TABLE `inquiry_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inquiry_id` bigint(20) NOT NULL,
  `creator_id` bigint(20) NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NULL DEFAULT 0 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `auditor_id` bigint(20) NULL DEFAULT 0 COMMENT '审核人',
  `audit_date` datetime(0) NULL DEFAULT NULL COMMENT '审核时间',
  `audit_remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '审核意见',
  `cfm_id` bigint(20) NULL DEFAULT 0 COMMENT '确认人',
  `cfm_date` datetime(0) NULL DEFAULT NULL COMMENT '确认时间',
  `origin` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '来源',
  `quote_num` bigint(20) NULL DEFAULT 0 COMMENT '收到报价数',
  `min_sum_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '最低总价',
  `max_sum_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '最高总价',
  `avg_sum_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '平均总价',
  `min_supplier_id` bigint(20) NULL DEFAULT 0 COMMENT '最低价供应商',
  `max_supplier_id` bigint(20) NULL DEFAULT 0 COMMENT '最高价供应商',
  `push_notice` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否发布公告',
  `decider` bigint(20) NULL DEFAULT 0 COMMENT '定标人',
  `decide_date` datetime(0) NULL DEFAULT NULL COMMENT '定标时间',
  `audit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '定标意见',
  `terminate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '终止意见',
  `push_1688` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否发布1688',
  `buy_offer_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '1688询价单ID',
  `opener` bigint(20) NULL DEFAULT 0 COMMENT '开标人',
  `open_date` datetime(0) NULL DEFAULT NULL COMMENT '开标时间',
  `supplier_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '当前状态',
  `supplier_pro_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '备注',
  `fis_auto_fill_price` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `inquiry_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '询价标题',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_inquiry_id`(`inquiry_id`) USING BTREE,
  INDEX `IDX_PUR_INQUIRY_A_FCREATETIME`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '询价单-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry_sub
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry_supplier
-- ----------------------------
DROP TABLE IF EXISTS `inquiry_supplier`;
CREATE TABLE `inquiry_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inquiry_id` bigint(20) NOT NULL,
  `seq` bigint(20) NOT NULL DEFAULT 0,
  `supplier_id` bigint(20) NOT NULL DEFAULT 0,
  `quote_date` datetime(0) NULL DEFAULT NULL COMMENT '报价日期',
  `quoter_id` bigint(20) NULL DEFAULT 0,
  `quote_id` bigint(20) NULL DEFAULT NULL COMMENT '报价单ID',
  `entry_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '状态\r\nA:已报价\r\nB:已开标\r\nC:已采纳\r\nD:部分采纳\r\nE:未采纳\r\nF:不报价\r\n',
  `supplier_biz_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '报价分录.供应商轮次状态\r\nA:待报价\r\nB:已报价\r\nC:不报价\r\nD:未参与\r\nE:已终止',
  `entry_turns` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价轮次',
  `entry_count` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '报价次数',
  `can_show` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1',
  `dead_line` datetime(0) NULL DEFAULT NULL,
  `contact_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `contact_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `inquiry_id`(`inquiry_id`, `supplier_id`, `entry_turns`) USING BTREE,
  INDEX `IDX_PUR_INQUIRYSUP_FID_FSEQ`(`inquiry_id`, `seq`) USING BTREE,
  INDEX `IDX_PUR_INQUIRYSUP_FSUPID`(`supplier_id`) USING BTREE,
  INDEX `quote_id`(`quote_id`) USING BTREE,
  INDEX `supplier_biz_status`(`inquiry_id`, `supplier_biz_status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for inquiry_turns_log
-- ----------------------------
DROP TABLE IF EXISTS `inquiry_turns_log`;
CREATE TABLE `inquiry_turns_log`  (
  `inquiry_id` bigint(20) NOT NULL,
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `seq` bigint(20) NOT NULL DEFAULT 0,
  `turns` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `entry_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否修改物料 Y:是 N:否',
  `handler_id` bigint(20) NULL DEFAULT 0,
  `handle_time` datetime(0) NULL DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `log_dead_line` datetime(0) NULL DEFAULT NULL,
  `entry_log_scope` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_INQUIRYTURNSLOG_FID`(`inquiry_id`, `seq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '多轮报价面板分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inquiry_turns_log
-- ----------------------------

-- ----------------------------
-- Table structure for invoice_type
-- ----------------------------
DROP TABLE IF EXISTS `invoice_type`;
CREATE TABLE `invoice_type`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '编码',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建人',
  `modifier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '使用状态',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `master_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '主数据内码',
  `group_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '发票类型分组',
  `is_system` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '系统预设',
  `full_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '全名',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `desc` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '描述',
  `tax_control_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '发票税控编码',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BD_INVOICETYPE_NUM`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '发票类型-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of invoice_type
-- ----------------------------

-- ----------------------------
-- Table structure for material_group
-- ----------------------------
DROP TABLE IF EXISTS `material_group`;
CREATE TABLE `material_group`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '编码',
  `parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '分分类ID',
  `level` int(11) NULL DEFAULT NULL COMMENT '级层',
  `long_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '长编码',
  `is_leaf` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '是否存在子节点',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '是否启用',
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '状态',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人ID',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人ID',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '禁用人ID',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '描述',
  `create_org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建组织',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_T_BD_MATERIALGROUP_NUMBER`(`number`) USING BTREE,
  INDEX `IDX_T_BD_MATERIALGROUP_COMBINE`(`parent_id`, `create_org_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '物料分类-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of material_group
-- ----------------------------

-- ----------------------------
-- Table structure for menus
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_type` enum('COMMON','PLATFORM','SUPPLIER') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'PLATFORM' COMMENT 'PURCHASER:采购商 COMMON 公共菜单 SUPPLIER 供应商菜单',
  `parent_id` bigint(20) NULL DEFAULT 0 COMMENT '上级菜单ID',
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'MENU' COMMENT '功能类型\r\nBUTTON:按钮\r\nMENU:菜单\r\nIF:接口',
  `weight` int(10) UNSIGNED NULL DEFAULT 0,
  `sort` int(11) NULL DEFAULT 0 COMMENT '排序',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'NORMAL' COMMENT '状态\r\nNORMAL:正常;\r\nDISABLED:停用',
  `platform` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'BOSS' COMMENT '所属平台\r\nBOSS-后台\r\nAPP-手机\r\nMALL-商城\r\nORDER:订单',
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否删除',
  `perm_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '功能ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent_tree` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `perm_tree` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `button_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2138 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menus
-- ----------------------------
INSERT INTO `menus` VALUES (10, 'COMMON', 0, '权限管理', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVfSAYlmIAAALSXq6ZII916.png', '/permissions', 'MENU', 0, 1, 'NORMAL', '', '2024-02-28 13:31:36', 136, '2024-05-22 10:18:29', 1875271515914638372, 'N', 5, '权限管理', NULL, '5', NULL);
INSERT INTO `menus` VALUES (11, 'PLATFORM', 10, '权限配置', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHjSAFAwRAAAJeEV8mdo330.png', '/permissions/permissions_list', 'MENU', 0, 0, 'NORMAL', '', '2024-02-28 13:32:22', 136, '2024-07-10 13:37:57', 1945082105276930210, 'N', 6, '权限配置', '10', '5,6', NULL);
INSERT INTO `menus` VALUES (12, 'PLATFORM', 10, '菜单管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHiyAD_psAAAJQ75bSd8235.png', '/permissions/menu_list', 'MENU', 0, 0, 'NORMAL', '', '2024-02-28 13:32:43', 136, '2024-07-10 13:37:48', 1945082105276930210, 'N', 23, '菜单管理', '10', '5,23', NULL);
INSERT INTO `menus` VALUES (13, 'COMMON', 10, '角色管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHiKACswjAAAKf3YFGwA951.png', '/permissions/roles_list', 'MENU', 0, 0, 'NORMAL', '', '2024-02-28 13:33:02', 136, '2024-07-10 13:37:39', 1945082105276930210, 'N', 24, '角色管理', '10', '5,24', NULL);
INSERT INTO `menus` VALUES (15, 'PLATFORM', 11, '新增', '', '/admin/permissions/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-02-28 13:34:42', 136, '2024-04-22 14:34:57', 136, 'N', 27, '新增', '10,11', '5,6,27', NULL);
INSERT INTO `menus` VALUES (16, 'PLATFORM', 11, '修改权限', '', '/admin/permissions/edited', 'BUTTON', 0, 0, 'NORMAL', '', '2024-02-28 13:35:07', 136, '2024-02-28 13:35:07', 136, 'N', 28, '修改权限', '10,11', '5,6,28', NULL);
INSERT INTO `menus` VALUES (17, 'PLATFORM', 11, '删除权限', '', '/admin/permissions/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-02-28 14:03:36', 136, '2024-03-06 10:20:38', 136, 'N', 26, '删除权限', '10,11', '5,6,28', NULL);
INSERT INTO `menus` VALUES (21, 'PLATFORM', 0, '基础服务', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVduAfcD_AAAGYPkH2gs338.png', '/base', 'MENU', 0, 1, 'NORMAL', '', '2024-03-05 16:17:24', 136, '2024-05-22 15:41:44', 136, 'N', 30, '基础服务', NULL, '30', NULL);
INSERT INTO `menus` VALUES (23, 'PLATFORM', 21, '组织机构', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHb2AHU9IAAAGj70PLoA559.png', '/base/organize', 'MENU', 0, 0, 'NORMAL', '', '2024-03-05 16:20:37', 136, '2024-07-10 13:35:58', 1945082105276930210, 'N', 32, '组织机构', '21', '30,32', NULL);
INSERT INTO `menus` VALUES (24, 'PLATFORM', 21, '人员管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHa-AP1P2AAAKjXQAnlc755.png', '/base/personnelList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-05 16:20:51', 136, '2024-07-10 13:35:44', 1945082105276930210, 'N', 33, '人员管理', '21', '30,33', NULL);
INSERT INTO `menus` VALUES (25, 'PLATFORM', 22, '采购商列表', '', '/admin/purchaser/list', 'IF', 0, 1, 'NORMAL', '', '2024-03-06 14:05:51', 1201230216341488640, '2024-03-06 14:05:51', 1201230216341488640, 'N', 42, '采购商列表', '21,22', '30,31,42', '');
INSERT INTO `menus` VALUES (32, 'PLATFORM', 0, '基础数据', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNmmuAYvZBAAACs0bLxTg129.png', '/basic', 'MENU', 0, 4, 'NORMAL', '', '2024-03-07 09:54:46', 136, '2024-05-22 15:10:36', 1875271515914638372, 'N', 55, '基础数据', NULL, '55', NULL);
INSERT INTO `menus` VALUES (33, 'PLATFORM', 32, '计量单位', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHnWAKfTNAAAKtdzkgW8995.png', '/basic/unitList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-07 09:55:49', 136, '2024-07-10 13:39:02', 1945082105276930210, 'N', 56, '计量单位', '32', '55,56', NULL);
INSERT INTO `menus` VALUES (34, 'PLATFORM', 32, '币种管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHmuAARXqAAAKAuH-2Rg419.png', '/basic/currencyList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-08 10:16:38', 136, '2024-07-10 13:38:52', 1945082105276930210, 'N', 57, '币种管理', '32', '55,57', NULL);
INSERT INTO `menus` VALUES (35, 'PLATFORM', 32, '银行管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHmGABSZ8AAAFyiCFeVc448.png', '/basic/bankList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-08 10:35:00', 136, '2024-07-10 13:38:41', 1945082105276930210, 'N', 58, '银行管理', '32', '55,58', NULL);
INSERT INTO `menus` VALUES (36, 'SUPPLIER', 0, '准入协同', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVeeAQznQAAAJ1eZuD0Q145.png', '/supplierClient', 'MENU', 0, 5, 'NORMAL', '', '2024-03-08 15:54:18', 136, '2024-05-22 10:18:16', 1875271515914638372, 'N', 59, '准入协同', NULL, '59', NULL);
INSERT INTO `menus` VALUES (37, 'SUPPLIER', 36, '注册管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHpyARyNLAAAJwxdhuIE378.png', '/supplierClient/registerList', 'MENU', 0, 2, 'NORMAL', '', '2024-03-08 15:56:42', 136, '2024-07-10 13:39:41', 1945082105276930210, 'N', 60, '注册管理', '36', '59,60', NULL);
INSERT INTO `menus` VALUES (38, 'PLATFORM', 32, '结算方式', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHleAGsqNAAAGQJwdjcw964.png', '/basic/settlementTypeList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-11 09:34:54', 136, '2024-07-10 13:38:32', 1945082105276930210, 'N', 61, '结算方式', '32', '55,61', NULL);
INSERT INTO `menus` VALUES (39, 'PLATFORM', 32, '付款条件', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHkqAYkqJAAAIbrErdrg294.png', '/basic/paymentClause', 'MENU', 0, 0, 'NORMAL', '', '2024-03-12 09:48:48', 136, '2024-07-10 13:38:19', 1945082105276930210, 'N', 62, '付款条件', '32', '55,62', NULL);
INSERT INTO `menus` VALUES (40, 'PLATFORM', 0, '询比价管理', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVbeAE7YIAAAI0ftYH8g718.png', '/inquiryRate', 'MENU', 0, 0, 'NORMAL', '', '2024-03-14 14:51:39', 136, '2024-05-22 10:17:28', 1875271515914638372, 'N', 63, '询比价管理', NULL, '63', NULL);
INSERT INTO `menus` VALUES (41, 'PLATFORM', 40, '询价单', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHZGAUS1YAAAJAOhF-9Q328.png', '/inquiryRate/inquiryList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-14 14:52:01', 136, '2024-07-10 13:35:14', 1945082105276930210, 'N', 64, '询价单', '40', '63,64', NULL);
INSERT INTO `menus` VALUES (42, 'PLATFORM', 0, '供应商管理', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVaWAVC49AAAJDIAn15Q477.png', '/supplierManage', 'MENU', 0, 0, 'NORMAL', '', '2024-03-15 17:07:29', 136, '2024-05-22 10:17:11', 1875271515914638372, 'N', 65, '供应商管理', NULL, '65', NULL);
INSERT INTO `menus` VALUES (43, 'PLATFORM', 42, '注册管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHWiAP0pJAAAJwxdhuIE670.png', '/supplierManage/registerManage', 'MENU', 0, 0, 'NORMAL', '', '2024-03-15 17:08:24', 136, '2024-07-10 13:34:33', 1945082105276930210, 'N', 66, '注册管理', '42', '65,66', NULL);
INSERT INTO `menus` VALUES (44, 'PLATFORM', 42, '供应商库', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHU2AZ3KFAAAKLXs5tPI844.png', '/supplierManage/supplierBase', 'MENU', 0, 0, 'NORMAL', '', '2024-03-15 17:08:42', 136, '2024-07-10 13:34:07', 1945082105276930210, 'N', 67, '供应商库', '42', '65,67', NULL);
INSERT INTO `menus` VALUES (45, 'SUPPLIER', 36, '企业信息', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHpSAD8mnAAAF_59LgV8311.png', '/supplierClient/companyInfo', 'MENU', 0, 1, 'NORMAL', '', '2024-03-16 10:04:01', 136, '2024-07-10 13:39:33', 1945082105276930210, 'N', 72, '企业信息', '36', '59,72', NULL);
INSERT INTO `menus` VALUES (46, 'SUPPLIER', 0, '供应商工作台', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNmnKAYOO5AAAJvWpqS7c812.png', '/supplierApply', 'MENU', 0, 6, 'DISABLED', '', '2024-03-19 08:12:37', 136, '2024-05-22 15:10:43', 1875271515914638372, 'N', 73, '供应商工作台', NULL, '73', NULL);
INSERT INTO `menus` VALUES (47, 'SUPPLIER', 46, '工作台', '', '/supplierApply/staging', 'MENU', 0, 1, 'DISABLED', '', '2024-03-19 08:13:13', 136, '2024-05-13 15:51:49', 136, 'N', 74, '工作台', '46', '73,74', NULL);
INSERT INTO `menus` VALUES (48, 'SUPPLIER', 46, '评审意见', '', '/supplierApply/review', 'MENU', 0, 2, 'DISABLED', '', '2024-03-19 08:13:49', 136, '2024-05-13 15:51:56', 136, 'N', 75, '评审意见', '46', '73,75', NULL);
INSERT INTO `menus` VALUES (49, 'PLATFORM', 41, '新建询单', '', '/admin/inquiry/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-03-19 14:34:25', 136, '2024-03-20 15:51:34', 136, 'N', 76, '新建询单', '40,41', '63,64,76', NULL);
INSERT INTO `menus` VALUES (53, 'PLATFORM', 32, '供应商分级', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHj6AaGDlAAAIoY1m4do566.png', '/basic/supplierClassification', 'MENU', 0, 0, 'NORMAL', '', '2024-03-19 17:20:13', 136, '2024-07-10 13:38:07', 1945082105276930210, 'N', 84, '供应商分级', '32', '55,84', NULL);
INSERT INTO `menus` VALUES (55, 'PLATFORM', 40, '报价单', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHYaAGq4jAAAJC7EOHlg355.png', '/inquiryRate/quoteList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-28 14:44:14', 136, '2024-07-10 13:35:03', 1945082105276930210, 'N', 87, '报价单', '40', '63,87', NULL);
INSERT INTO `menus` VALUES (57, 'PLATFORM', 40, '比价单', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHXuAd8yTAAAJe5jQth4936.png', '/inquiryRate/competitiveList', 'MENU', 0, 0, 'NORMAL', '', '2024-03-29 14:00:42', 136, '2024-07-10 13:34:53', 1945082105276930210, 'N', 88, '比价单', '40', '63,88', NULL);
INSERT INTO `menus` VALUES (59, 'PLATFORM', 41, '询价列表', '', '/admin/inquiry', 'BUTTON', 0, 1, 'NORMAL', '', '2024-04-02 09:46:33', 136, '2024-04-22 14:10:49', 136, 'N', 64, '询价列表', '40,41', '63,64', NULL);
INSERT INTO `menus` VALUES (60, 'PLATFORM', 57, '比价助手', '', '/inquiryRate/newCompetitive', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-02 11:06:20', 136, '2024-04-02 16:34:17', 136, 'N', 94, '比价助手', '40,57', '63,88,94', NULL);
INSERT INTO `menus` VALUES (61, 'PLATFORM', 23, '组织机构列表', '', '/admin/org/list', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-02 13:12:40', 136, '2024-04-02 13:12:40', 136, 'N', 109, '组织机构列表', '21,23', '30,32,109', NULL);
INSERT INTO `menus` VALUES (64, 'SUPPLIER', 0, '报价管理', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVgKAV88KAAAI-rsd0rQ300.png', '/quoteManage', 'MENU', 0, 6, 'NORMAL', '', '2024-04-02 16:34:35', 1875271515914638372, '2024-05-22 10:18:43', 1875271515914638372, 'N', 82, '报价管理', NULL, '68,82', NULL);
INSERT INTO `menus` VALUES (65, 'SUPPLIER', 64, '询价单', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHr-ASzg6AAAHIJRidZk589.png', '/quoteManage/inquiryList', 'MENU', 0, 0, 'NORMAL', '', '2024-04-02 16:37:10', 1875271515914638372, '2024-07-10 13:40:15', 1945082105276930210, 'N', 288, '询价单', '64', '68,82,288', NULL);
INSERT INTO `menus` VALUES (67, 'PLATFORM', 55, '报价列表', '', '/admin/quote', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-03 16:40:33', 136, '2024-04-03 16:40:33', 136, 'N', 113, '报价列表', '40,55', '63,87,113', NULL);
INSERT INTO `menus` VALUES (69, 'PLATFORM', 24, '人员采购商组织列表', '', '/admin/user/orglist', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-07 17:47:46', 136, '2024-04-28 10:11:40', 136, 'N', 115, '人员采购商组织列表', '21,24', '30,33,115', NULL);
INSERT INTO `menus` VALUES (70, 'PLATFORM', 33, '启用单位', '', '/admin/unit/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-08 10:48:00', 136, '2024-04-08 10:48:00', 136, 'N', 119, '启用单位', '32,33', '55,56,119', NULL);
INSERT INTO `menus` VALUES (71, 'PLATFORM', 11, '修改权限', '', '/admin/permissions/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-08 10:48:39', 136, '2024-04-22 14:45:54', 136, 'N', 28, '修改权限', '10,11', '5,6,28', NULL);
INSERT INTO `menus` VALUES (72, 'PLATFORM', 57, '比价详情', '', '/inquiryRate/competitiveDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-08 10:52:22', 136, '2024-04-08 10:52:22', 136, 'N', 125, '比价详情', '40,57', '63,88,125', NULL);
INSERT INTO `menus` VALUES (328, 'PLATFORM', 11, '权限详情', '', '/admin/permissions/', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 25, '权限详情', '10,11', '5,6,25', 'IF');
INSERT INTO `menus` VALUES (329, 'PLATFORM', 12, '菜单详情', '', '/admin/menus', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-16 10:45:41', 136, 'N', 277, '菜单详情', '10,12', '5,23,29', 'IF');
INSERT INTO `menus` VALUES (330, 'PLATFORM', 22, '新增采购商', '', '/admin/purchaser/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 43, '新增采购商', '21,22', '30,31,43', 'IF');
INSERT INTO `menus` VALUES (331, 'PLATFORM', 22, '编辑采购商', '', '/admin/purchaser/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 44, '编辑采购商', '21,22', '30,31,44', 'IF');
INSERT INTO `menus` VALUES (332, 'PLATFORM', 22, '解冻采购商', '', '/admin/purchaser/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 45, '解冻采购商', '21,22', '30,31,45', 'IF');
INSERT INTO `menus` VALUES (333, 'PLATFORM', 22, '冻结采购商', '', '/admin/purchaser/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 46, '冻结采购商', '21,22', '30,31,46', 'IF');
INSERT INTO `menus` VALUES (334, 'PLATFORM', 22, '删除采购商', '', '/admin/purchaser/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 47, '删除采购商', '21,22', '30,31,47', 'IF');
INSERT INTO `menus` VALUES (335, 'PLATFORM', 22, '采购商导入', '', '/admin/purchaser/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 49, '采购商导入', '21,22', '30,31,49', 'IF');
INSERT INTO `menus` VALUES (336, 'PLATFORM', 22, '采购商详情', '', '/admin/purchaser/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 50, '采购商详情', '21,22', '30,31,50', 'IF');
INSERT INTO `menus` VALUES (337, 'PLATFORM', 22, '获取采购商编码', '', '/admin/purchaser/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 51, '获取采购商编码', '21,22', '30,31,51', 'IF');
INSERT INTO `menus` VALUES (338, 'PLATFORM', 44, '供应商列表', '', '/admin/supplier', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 77, '供应商列表', '42,44', '65,67,77', 'MENU');
INSERT INTO `menus` VALUES (340, 'PLATFORM', 44, '解冻供应商', '', 'admin/supplier/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 79, '解冻供应商', '42,44', '65,67,79', 'BUTTON');
INSERT INTO `menus` VALUES (341, 'PLATFORM', 44, '冻结供应商', '', 'admin/supplier/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 80, '冻结供应商', '42,44', '65,67,80', 'BUTTON');
INSERT INTO `menus` VALUES (342, 'PLATFORM', 44, '供应商审核', '', '/admin/supplier/audit/verify', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 85, '供应商审核', '42,44', '65,67,85', 'BUTTON');
INSERT INTO `menus` VALUES (343, 'PLATFORM', 44, '供应商导入', '', '/admin/supplier/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 95, '供应商导入', '42,44', '65,67,95', 'BUTTON');
INSERT INTO `menus` VALUES (344, 'PLATFORM', 41, '编辑询价', '', '/admin/inquiry/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:05:27', 136, 'N', 96, '编辑询价', '40,41', '63,64,96', 'MENU');
INSERT INTO `menus` VALUES (345, 'PLATFORM', 41, '详情', '', '/admin/inquiry/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:05:06', 136, 'N', 97, '详情', '40,41', '63,64,97', 'MENU');
INSERT INTO `menus` VALUES (346, 'PLATFORM', 41, '删除询价', '', '/admin/inquiry/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:11:33', 136, 'N', 98, '删除询价', '40,41', '63,64,98', 'BUTTON');
INSERT INTO `menus` VALUES (347, 'PLATFORM', 41, '复制询价单', '', '/admin/inquiry/copy/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 99, '复制询价单', '40,41', '63,64,99', 'BUTTON');
INSERT INTO `menus` VALUES (348, 'PLATFORM', 41, '变更时间', '', '/admin/inquiry/change/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:06:36', 136, 'N', 100, '变更时间', '40,41', '63,64,100', 'BUTTON');
INSERT INTO `menus` VALUES (349, 'PLATFORM', 41, '撤销询价', '', '/admin/inquiry/revoke/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:06:50', 136, 'N', 102, '撤销询价', '40,41', '63,64,102', 'BUTTON');
INSERT INTO `menus` VALUES (350, 'PLATFORM', 41, '询价终止', '', '/admin/inquiry/stop/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:07:09', 136, 'N', 103, '询价终止', '40,41', '63,64,103', 'BUTTON');
INSERT INTO `menus` VALUES (351, 'PLATFORM', 41, '询单编码', '', '/admin/inquiry/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:07:21', 136, 'N', 104, '询单编码', '40,41', '63,64,104', 'BUTTON');
INSERT INTO `menus` VALUES (352, 'PLATFORM', 41, '多轮报价', '', '/admin/inquiry/mulround/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:08:19', 136, 'N', 105, '多轮报价', '40,41', '63,64,105', 'BUTTON');
INSERT INTO `menus` VALUES (353, 'PLATFORM', 41, '开标', '', '/admin/inquiry/opening/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:10:03', 136, 'N', 106, '开标', '40,41', '63,64,106', 'BUTTON');
INSERT INTO `menus` VALUES (354, 'PLATFORM', 41, '询价单供应商', '', '/admin/inquiry/supplier/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 14:10:19', 136, 'N', 107, '询价单供应商', '40,41', '63,64,107', 'BUTTON');
INSERT INTO `menus` VALUES (355, 'PLATFORM', 41, '询价单物料引入模板', '', '/admin/inquiry/entry/template', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 108, '询价单物料引入模板', '40,41', '63,64,108', 'BUTTON');
INSERT INTO `menus` VALUES (356, 'PLATFORM', 25, '采购商列表', '', '/admin/purchaser/list', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 111, '采购商列表', '21,22,25', '30,31,42,111', 'BUTTON');
INSERT INTO `menus` VALUES (358, 'PLATFORM', 33, '单位列表', '', '/admin/unit', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 116, '单位列表', '32,33', '55,56,116', 'BUTTON');
INSERT INTO `menus` VALUES (359, 'PLATFORM', 33, '单位编码', '', '/admin/unit/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 117, '单位编码', '32,33', '55,56,117', 'BUTTON');
INSERT INTO `menus` VALUES (360, 'PLATFORM', 33, '删除单位', '', '/admin/unit/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 118, '删除单位', '32,33', '55,56,118', 'BUTTON');
INSERT INTO `menus` VALUES (361, 'PLATFORM', 33, '禁用单位', '', '/admin/unit/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 120, '禁用单位', '32,33', '55,56,120', 'BUTTON');
INSERT INTO `menus` VALUES (362, 'PLATFORM', 33, '新增单位', '', '/admin/unit/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 121, '新增单位', '32,33', '55,56,121', 'BUTTON');
INSERT INTO `menus` VALUES (363, 'PLATFORM', 33, '修改单位', '', '/admin/unit/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 122, '修改单位', '32,33', '55,56,122', 'BUTTON');
INSERT INTO `menus` VALUES (365, 'PLATFORM', 33, '单位导入', '', '/admin/unit/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 124, '单位导入', '32,33', '55,56,124', 'BUTTON');
INSERT INTO `menus` VALUES (366, 'PLATFORM', 34, '币种详情', '', '/admin/currency/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 126, '币种详情', '32,34', '55,57,126', 'BUTTON');
INSERT INTO `menus` VALUES (367, 'PLATFORM', 34, '币种列表', '', '/admin/currency', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 127, '币种列表', '32,34', '55,57,127', 'BUTTON');
INSERT INTO `menus` VALUES (368, 'PLATFORM', 34, '币种列表', '', '/admin/currencys', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 128, '币种列表', '32,34', '55,57,128', 'BUTTON');
INSERT INTO `menus` VALUES (369, 'PLATFORM', 34, '修改币种', '', '/admin/currency/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 129, '修改币种', '32,34', '55,57,129', 'BUTTON');
INSERT INTO `menus` VALUES (370, 'PLATFORM', 34, '新增币种', '', '/admin/currency/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 130, '新增币种', '32,34', '55,57,130', 'BUTTON');
INSERT INTO `menus` VALUES (371, 'PLATFORM', 34, '删除币种', '', '/admin/currency/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 131, '删除币种', '32,34', '55,57,131', 'BUTTON');
INSERT INTO `menus` VALUES (372, 'PLATFORM', 34, '禁用币种', '', '/admin/currency/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 132, '禁用币种', '32,34', '55,57,132', 'BUTTON');
INSERT INTO `menus` VALUES (373, 'PLATFORM', 34, '启用币种', '', '/admin/currency/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 133, '启用币种', '32,34', '55,57,133', 'BUTTON');
INSERT INTO `menus` VALUES (374, 'PLATFORM', 34, '币种导入', '', '/admin/currency/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 134, '币种导入', '32,34', '55,57,134', 'BUTTON');
INSERT INTO `menus` VALUES (376, 'PLATFORM', 35, '银行详情', '', '/admin/bank/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 136, '银行详情', '32,35', '55,58,136', 'BUTTON');
INSERT INTO `menus` VALUES (377, 'PLATFORM', 35, '银行列表', '', '/admin/bank', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 137, '银行列表', '32,35', '55,58,137', 'BUTTON');
INSERT INTO `menus` VALUES (378, 'PLATFORM', 35, '银行编码', '', '/admin/bank/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 138, '银行编码', '32,35', '55,58,138', 'BUTTON');
INSERT INTO `menus` VALUES (379, 'PLATFORM', 35, '编辑银行', '', '/admin/bank/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 139, '编辑银行', '32,35', '55,58,139', 'BUTTON');
INSERT INTO `menus` VALUES (380, 'PLATFORM', 35, '新增银行', '', '/admin/bank/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 140, '新增银行', '32,35', '55,58,140', 'BUTTON');
INSERT INTO `menus` VALUES (381, 'PLATFORM', 35, '删除银行', '', '/admin/bank/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 141, '删除银行', '32,35', '55,58,141', 'BUTTON');
INSERT INTO `menus` VALUES (382, 'PLATFORM', 35, '禁用银行', '', '/admin/bank/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 142, '禁用银行', '32,35', '55,58,142', 'BUTTON');
INSERT INTO `menus` VALUES (383, 'PLATFORM', 35, '启用银行', '', '/admin/bank/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 143, '启用银行', '32,35', '55,58,143', 'BUTTON');
INSERT INTO `menus` VALUES (384, 'PLATFORM', 35, '银行导入', '', '/admin/bank/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 144, '银行导入', '32,35', '55,58,144', 'BUTTON');
INSERT INTO `menus` VALUES (386, 'PLATFORM', 38, '列表', '', '/admin/settlementtype', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 146, '列表', '32,38', '55,61,146', 'BUTTON');
INSERT INTO `menus` VALUES (387, 'PLATFORM', 38, '详情', '', '/admin/settlementtype/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 147, '详情', '32,38', '55,61,147', 'BUTTON');
INSERT INTO `menus` VALUES (388, 'PLATFORM', 38, '编辑', '', '/admin/settlementtype/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 148, '编辑', '32,38', '55,61,148', 'BUTTON');
INSERT INTO `menus` VALUES (389, 'PLATFORM', 38, '新增', '', '/admin/settlementtype/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 149, '新增', '32,38', '55,61,149', 'BUTTON');
INSERT INTO `menus` VALUES (390, 'PLATFORM', 38, '删除', '', '/admin/settlementtype/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 150, '删除', '32,38', '55,61,150', 'BUTTON');
INSERT INTO `menus` VALUES (391, 'PLATFORM', 38, '禁用', '', '/admin/settlementtype/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 151, '禁用', '32,38', '55,61,151', 'BUTTON');
INSERT INTO `menus` VALUES (392, 'PLATFORM', 38, '启用', '', '/admin/settlementtype/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 152, '启用', '32,38', '55,61,152', 'BUTTON');
INSERT INTO `menus` VALUES (393, 'PLATFORM', 38, '导入', '', '/admin/settlementtype/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, 136, NULL, 136, 'N', 153, '导入', '32,38', '55,61,153', 'BUTTON');
INSERT INTO `menus` VALUES (395, 'PLATFORM', 39, '详情', '', '/admin/paycond/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 155, '详情', '32,39', '55,62,155', 'BUTTON');
INSERT INTO `menus` VALUES (396, 'PLATFORM', 39, '列表', '', '/admin/paycond', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 156, '列表', '32,39', '55,62,156', 'BUTTON');
INSERT INTO `menus` VALUES (397, 'PLATFORM', 39, '编辑', '', '/admin/paycond/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 157, '编辑', '32,39', '55,62,157', 'BUTTON');
INSERT INTO `menus` VALUES (398, 'PLATFORM', 39, '新增', '', '/admin/paycond/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 158, '新增', '32,39', '55,62,158', 'BUTTON');
INSERT INTO `menus` VALUES (399, 'PLATFORM', 39, '删除', '', '/admin/paycond/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 159, '删除', '32,39', '55,62,159', 'BUTTON');
INSERT INTO `menus` VALUES (400, 'PLATFORM', 39, '禁用', '', '/admin/paycond/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 160, '禁用', '32,39', '55,62,160', 'BUTTON');
INSERT INTO `menus` VALUES (401, 'PLATFORM', 39, '启用', '', '/admin/paycond/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 161, '启用', '32,39', '55,62,161', 'BUTTON');
INSERT INTO `menus` VALUES (402, 'PLATFORM', 39, '导入', '', '/admin/paycond/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 162, '导入', '32,39', '55,62,162', 'BUTTON');
INSERT INTO `menus` VALUES (404, 'PLATFORM', 39, '编码', '', '/admin/paycond/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 164, '编码', '32,39', '55,62,164', 'BUTTON');
INSERT INTO `menus` VALUES (405, 'PLATFORM', 53, '详情', '', '/admin/supplier/evagrade/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 165, '详情', '32,53', '55,84,165', 'BUTTON');
INSERT INTO `menus` VALUES (406, 'PLATFORM', 53, '列表', '', '/admin/supplier/evagrade', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 166, '列表', '32,53', '55,84,166', 'BUTTON');
INSERT INTO `menus` VALUES (407, 'PLATFORM', 53, '编码', '', '/admin/supplier/evagrade/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 167, '编码', '32,53', '55,84,167', 'BUTTON');
INSERT INTO `menus` VALUES (408, 'PLATFORM', 53, '编辑', '', '/admin/supplier/evagrade/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 168, '编辑', '32,53', '55,84,168', 'BUTTON');
INSERT INTO `menus` VALUES (409, 'PLATFORM', 53, '新增', '', '/admin/supplier/evagrade/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 169, '新增', '32,53', '55,84,169', 'BUTTON');
INSERT INTO `menus` VALUES (410, 'PLATFORM', 53, '删除', '', '/admin/supplier/evagrade/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 170, '删除', '32,53', '55,84,170', 'BUTTON');
INSERT INTO `menus` VALUES (411, 'PLATFORM', 53, '禁用', '', '/admin/supplier/evagrade/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 171, '禁用', '32,53', '55,84,171', 'BUTTON');
INSERT INTO `menus` VALUES (412, 'PLATFORM', 53, '启用', '', '/admin/supplier/evagrade/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 172, '启用', '32,53', '55,84,172', 'BUTTON');
INSERT INTO `menus` VALUES (413, 'PLATFORM', 53, '导入', '', '/admin/supplier/evagrade/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 173, '导入', '32,53', '55,84,173', 'BUTTON');
INSERT INTO `menus` VALUES (415, 'PLATFORM', 23, '详情', '', '/admin/org/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 175, '详情', '21,23', '30,32,175', 'BUTTON');
INSERT INTO `menus` VALUES (416, 'PLATFORM', 23, '列表(树形)', '', '/admin/org', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 176, '列表(树形)', '21,23', '30,32,176', 'BUTTON');
INSERT INTO `menus` VALUES (417, 'PLATFORM', 23, '编码', '', '/admin/org/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 177, '编码', '21,23', '30,32,177', 'BUTTON');
INSERT INTO `menus` VALUES (418, 'PLATFORM', 23, '编辑', '', '/admin/org/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 178, '编辑', '21,23', '30,32,178', 'BUTTON');
INSERT INTO `menus` VALUES (419, 'PLATFORM', 23, '新增', '', '/admin/org/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 179, '新增', '21,23', '30,32,179', 'BUTTON');
INSERT INTO `menus` VALUES (420, 'PLATFORM', 23, '删除', '', '/admin/org/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 180, '删除', '21,23', '30,32,180', 'BUTTON');
INSERT INTO `menus` VALUES (421, 'PLATFORM', 23, '禁用', '', '/admin/org/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 181, '禁用', '21,23', '30,32,181', 'BUTTON');
INSERT INTO `menus` VALUES (422, 'PLATFORM', 23, '启用', '', '/admin/org/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 182, '启用', '21,23', '30,32,182', 'BUTTON');
INSERT INTO `menus` VALUES (423, 'PLATFORM', 23, '导入', '', '/admin/org/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 183, '导入', '21,23', '30,32,183', 'BUTTON');
INSERT INTO `menus` VALUES (424, 'PLATFORM', 24, '详情', '', '/admin/user/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 184, '详情', '21,24', '30,33,184', 'BUTTON');
INSERT INTO `menus` VALUES (425, 'PLATFORM', 24, '列表', '', '/admin/user', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 185, '列表', '21,24', '30,33,185', 'BUTTON');
INSERT INTO `menus` VALUES (426, 'PLATFORM', 24, '业务员', '', '/admin/user/persons', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 186, '业务员', '21,24', '30,33,186', 'BUTTON');
INSERT INTO `menus` VALUES (427, 'PLATFORM', 24, '编辑', '', '/admin/user/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 187, '编辑', '21,24', '30,33,187', 'BUTTON');
INSERT INTO `menus` VALUES (428, 'PLATFORM', 24, '新增', '', '/admin/user/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 188, '新增', '21,24', '30,33,188', 'BUTTON');
INSERT INTO `menus` VALUES (429, 'PLATFORM', 24, '删除', '', '/admin/user/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 189, '删除', '21,24', '30,33,189', 'BUTTON');
INSERT INTO `menus` VALUES (430, 'PLATFORM', 24, '禁用', '', '/admin/user/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 190, '禁用', '21,24', '30,33,190', 'BUTTON');
INSERT INTO `menus` VALUES (431, 'PLATFORM', 24, '启用', '', '/admin/user/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 191, '启用', '21,24', '30,33,191', 'BUTTON');
INSERT INTO `menus` VALUES (432, 'PLATFORM', 24, '修改密码', '', '/admin/user/change/password', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 192, '修改密码', '21,24', '30,33,192', 'BUTTON');
INSERT INTO `menus` VALUES (434, 'PLATFORM', 24, '拼音', '', '/admin/user/pinyin', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 194, '拼音', '21,24', '30,33,194', 'BUTTON');
INSERT INTO `menus` VALUES (435, 'PLATFORM', 24, '角色', '', '/admin/user/roles', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 195, '角色', '21,24', '30,33,195', 'BUTTON');
INSERT INTO `menus` VALUES (436, 'PLATFORM', 24, '菜单', '', '/admin/user/menus', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 196, '菜单', '21,24', '30,33,196', 'BUTTON');
INSERT INTO `menus` VALUES (437, 'PLATFORM', 24, '组织机构ID', '', '/admin/user/orgs', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 197, '组织机构ID', '21,24', '30,33,197', 'BUTTON');
INSERT INTO `menus` VALUES (438, 'PLATFORM', 24, '组织机构(树形)', '', '/admin/user/orgtree', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 198, '组织机构(树形)', '21,24', '30,33,198', 'BUTTON');
INSERT INTO `menus` VALUES (439, 'PLATFORM', 43, '注册管理列表', '', '/admin/supplier/register', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 200, '注册管理列表', '42,43', '65,66,200', 'BUTTON');
INSERT INTO `menus` VALUES (440, 'PLATFORM', 55, '报价详情', '', '/admin/quote/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 201, '报价详情', '40,55', '63,87,201', 'BUTTON');
INSERT INTO `menus` VALUES (441, 'PLATFORM', 55, '报价汇总信息', '', '/admin/quote/sum_quote/{inquiry_id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 202, '报价汇总信息', '40,55', '63,87,202', 'BUTTON');
INSERT INTO `menus` VALUES (442, 'PLATFORM', 13, '角色详情', '', '/admin/roles/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 203, '角色详情', '10,13', '5,24,203', 'BUTTON');
INSERT INTO `menus` VALUES (444, 'PLATFORM', 13, '公司', '', '/admin/roles/company', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 205, '公司', '10,13', '5,24,205', 'BUTTON');
INSERT INTO `menus` VALUES (445, 'PLATFORM', 13, '新增角色', '', '/admin/roles/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 206, '新增角色', '10,13', '5,24,206', 'BUTTON');
INSERT INTO `menus` VALUES (446, 'PLATFORM', 13, '禁用角色', '', '/admin/roles/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 207, '禁用角色', '10,13', '5,24,207', 'BUTTON');
INSERT INTO `menus` VALUES (447, 'PLATFORM', 13, '启用角色', '', '/admin/roles/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 208, '启用角色', '10,13', '5,24,208', 'BUTTON');
INSERT INTO `menus` VALUES (448, 'PLATFORM', 13, '删除角色', '', '/admin/roles/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 209, '删除角色', '10,13', '5,24,209', 'BUTTON');
INSERT INTO `menus` VALUES (449, 'PLATFORM', 13, '编辑角色', '', '/admin/roles/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 210, '编辑角色', '10,13', '5,24,210', 'BUTTON');
INSERT INTO `menus` VALUES (450, 'PLATFORM', 13, '角色授权', '', '/admin/roles/menus/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 211, '角色授权', '10,13', '5,24,211', 'BUTTON');
INSERT INTO `menus` VALUES (451, 'PLATFORM', 13, '添加用户角色', '', '/admin/roles/user/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 212, '添加用户角色', '10,13', '5,24,212', 'BUTTON');
INSERT INTO `menus` VALUES (452, 'PLATFORM', 13, '获取菜单', '', '/admin/roles/menuslist/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 213, '获取菜单', '10,13', '5,24,213', 'BUTTON');
INSERT INTO `menus` VALUES (453, 'PLATFORM', 13, '获取用户角色', '', '/admin/roles/listbyuser/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 214, '获取用户角色', '10,13', '5,24,214', 'BUTTON');
INSERT INTO `menus` VALUES (454, 'PLATFORM', 12, '菜单列表', '', '/admin/menus', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 215, '菜单列表', '10,12', '5,23,215', 'BUTTON');
INSERT INTO `menus` VALUES (455, 'PLATFORM', 12, '菜单列表(树状)', '', '/admin/menus/tree', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 216, '菜单列表(树状)', '10,12', '5,23,216', 'BUTTON');
INSERT INTO `menus` VALUES (456, 'PLATFORM', 12, '新增菜单', '', '/admin/menus/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 217, '新增菜单', '10,12', '5,23,217', 'BUTTON');
INSERT INTO `menus` VALUES (457, 'PLATFORM', 12, '禁用菜单', '', '/admin/menus/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 218, '禁用菜单', '10,12', '5,23,218', 'BUTTON');
INSERT INTO `menus` VALUES (458, 'PLATFORM', 12, '启用菜单', '', '/admin/menus/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 219, '启用菜单', '10,12', '5,23,219', 'BUTTON');
INSERT INTO `menus` VALUES (459, 'PLATFORM', 12, '删除菜单', '', '/admin/menus/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 220, '删除菜单', '10,12', '5,23,220', 'BUTTON');
INSERT INTO `menus` VALUES (460, 'PLATFORM', 12, '编辑菜单', '', '/admin/menus/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 221, '编辑菜单', '10,12', '5,23,221', 'BUTTON');
INSERT INTO `menus` VALUES (590, 'PLATFORM', 11, '权限详情', '', '/admin/permissions/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 229, '权限详情', '10,11', '5,6,229', 'BUTTON');
INSERT INTO `menus` VALUES (591, 'PLATFORM', 11, '权限(树形)', '', '/admin/permissions/tree', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 231, '权限(树形)', '10,11', '5,6,231', 'BUTTON');
INSERT INTO `menus` VALUES (592, 'PLATFORM', 11, '禁用', '', '/admin/permissions/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 233, '禁用', '10,11', '5,6,233', 'BUTTON');
INSERT INTO `menus` VALUES (593, 'PLATFORM', 11, '启用', '', '/admin/permissions/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 234, '启用', '10,11', '5,6,234', 'BUTTON');
INSERT INTO `menus` VALUES (594, 'PLATFORM', 11, '编辑', '', '/admin/permissions/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 236, '编辑', '10,11', '5,6,236', 'BUTTON');
INSERT INTO `menus` VALUES (595, 'PLATFORM', 24, '登录用户信息获取', '', '/admin/auth/me', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-28 10:11:44', 136, 'N', 222, '登录用户信息获取', '21,24', '5,33,222', 'BUTTON');
INSERT INTO `menus` VALUES (596, 'PLATFORM', 24, '用户信息', '', '/admin/auth/info', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-28 10:11:48', 136, 'N', 223, '用户信息', '21,24', '5,33,223', 'BUTTON');
INSERT INTO `menus` VALUES (597, 'PLATFORM', 24, '修改密码', '', '/admin/auth/change/password', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-28 10:11:51', 136, 'N', 224, '修改密码', '21,24', '5,33,224', 'BUTTON');
INSERT INTO `menus` VALUES (598, 'PLATFORM', 24, '变更组织', '', '/admin/auth/change/org', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-28 10:11:32', 136, 'N', 225, '变更组织', '21,24', '5,33,225', 'BUTTON');
INSERT INTO `menus` VALUES (599, 'PLATFORM', 24, '头像', '', '/admin/auth/avatar', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 226, '头像', '21,24', '5,33,226', 'BUTTON');
INSERT INTO `menus` VALUES (600, 'PLATFORM', 24, '登出', '', '/admin/auth/logout', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 227, '登出', '21,24', '5,33,227', 'BUTTON');
INSERT INTO `menus` VALUES (601, 'PLATFORM', 24, '采购商', '', '/admin/auth/purchasers', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 228, '采购商', '21,24', '5,33,228', 'BUTTON');
INSERT INTO `menus` VALUES (602, 'PLATFORM', 11, '获取菜单', '', '/admin/roles/menuslist/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', '2024-04-09 08:31:06', 136, '2024-04-09 08:31:06', 136, 'N', 213, '获取菜单', '10,11', '5,24,213', 'BUTTON');
INSERT INTO `menus` VALUES (603, 'PLATFORM', 22, '采购商列表', '', '/admin/purchaser', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 237, '采购商列表', '21,22', '30,31,237', 'BUTTON');
INSERT INTO `menus` VALUES (604, 'PLATFORM', 44, '供应商列表', '', '/admin/supplier', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 238, '供应商列表', '42,44', '65,67,238', 'BUTTON');
INSERT INTO `menus` VALUES (606, 'PLATFORM', 44, '供应商列表', '', '/admin/supplier/list', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 239, '供应商列表', '42,44', '65,67,239', 'BUTTON');
INSERT INTO `menus` VALUES (607, 'COMMON', 44, '供应商详情', '', '/admin/supplier/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 240, '供应商详情', '42,44', '65,67,240', 'BUTTON');
INSERT INTO `menus` VALUES (608, 'PLATFORM', 44, '供应商详情', '', '/admin/supplier/base/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 241, '供应商详情', '42,44', '65,67,241', 'BUTTON');
INSERT INTO `menus` VALUES (610, 'SUPPLIER', 64, '报价单', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHreASYWnAAAIKWJXe6k114.png', '/quoteManage/quoteList', 'MENU', 0, 0, 'NORMAL', '', '2024-04-09 10:55:34', 136, '2024-07-10 13:40:08', 1945082105276930210, 'N', 289, '报价单', '64', '68,82,289', NULL);
INSERT INTO `menus` VALUES (613, 'PLATFORM', 674, '公告管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHZqAEffZAAAJIeAC0nM783.png', '/message/notice_manage', 'MENU', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-07-10 13:35:23', 1945082105276930210, 'N', 244, '公告管理', '674', '30,244', 'BUTTON');
INSERT INTO `menus` VALUES (614, 'PLATFORM', 674, '消息管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHaSAeGaZAAAJXEl5W0s501.png', '/message/message_manage', 'MENU', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-07-10 13:35:33', 1945082105276930210, 'N', 245, '消息管理', '674', '30,245', 'BUTTON');
INSERT INTO `menus` VALUES (615, 'PLATFORM', 21, '用户管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHhWAeTIZAAAKjXQAnlc632.png', '/admin/auth/me', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-07-10 13:37:26', 1945082105276930210, 'N', 259, '用户管理', '21', '30,259', 'BUTTON');
INSERT INTO `menus` VALUES (616, 'PLATFORM', 44, '供应商分类', '', '/admin/supplier/group', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 271, '供应商分类', '42,44', '65,67,271', 'BUTTON');
INSERT INTO `menus` VALUES (617, 'PLATFORM', 44, '企业类型', '', '/admin/supplier/enterprise_type', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 272, '企业类型', '42,44', '65,67,272', 'BUTTON');
INSERT INTO `menus` VALUES (618, 'PLATFORM', 44, '供应商审核', '', '/admin/supplier/audit/verify', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 273, '供应商审核', '42,44', '65,67,273', 'BUTTON');
INSERT INTO `menus` VALUES (619, 'PLATFORM', 44, '当前供应商审核记录', '', '/admin/supplier/audit/history', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 274, '当前供应商审核记录', '42,44', '65,67,274', 'BUTTON');
INSERT INTO `menus` VALUES (620, 'PLATFORM', 44, '供应商审核记录', '', '/admin/supplier/audit/comments', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-22 15:01:56', 136, 'N', 275, '供应商审核记录', '42,44', '65,67,275', 'BUTTON');
INSERT INTO `menus` VALUES (621, 'PLATFORM', 12, '菜单详情', '', '/admin/menus/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 277, '菜单详情', '10,12', NULL, 'BUTTON');
INSERT INTO `menus` VALUES (622, 'SUPPLIER', 610, '报价详情', '', '/admin/supplier/quote/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:07:44', 136, 'N', 278, '报价详情', '64,610', '68,82,289,278', 'BUTTON');
INSERT INTO `menus` VALUES (624, 'SUPPLIER', 610, '根据询单ID获取报价详情', '', '/admin/supplier/quote/info/{inquiry_id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:08:17', 136, 'N', 281, '根据询单ID获取报价详情', '64,610', '68,82,289,281', 'BUTTON');
INSERT INTO `menus` VALUES (625, 'SUPPLIER', 610, '删除报价', '', '/admin/supplier/quote/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:10:22', 136, 'N', 282, '删除报价', '64,610', '68,82,289,282', 'BUTTON');
INSERT INTO `menus` VALUES (626, 'SUPPLIER', 610, '获取报价编码', '', '/admin/supplier/quote/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:10:30', 136, 'N', 283, '获取报价编码', '64,610', '68,82,289,283', 'BUTTON');
INSERT INTO `menus` VALUES (627, 'SUPPLIER', 610, '编辑报价', '', '/admin/supplier/quote/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:10:38', 136, 'N', 284, '编辑报价', '64,610', '68,82,289,284', 'BUTTON');
INSERT INTO `menus` VALUES (628, 'SUPPLIER', 610, '新增报价', '', '/admin/supplier/quote/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:10:46', 136, 'N', 285, '新增报价', '64,610', '68,82,289,285', 'BUTTON');
INSERT INTO `menus` VALUES (630, 'SUPPLIER', 610, '导入报价物料', '', '/admin/supplier/quote/entry/import', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-08 09:11:00', 136, 'N', 287, '导入报价物料', '64,610', '68,82,289,287', 'BUTTON');
INSERT INTO `menus` VALUES (631, 'SUPPLIER', 65, '询价详情', '', '/quoteManage/inquiryDetail', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-28 14:11:45', 136, 'N', 290, '询价详情', '64,65', '68,82,288,290', 'BUTTON');
INSERT INTO `menus` VALUES (632, 'SUPPLIER', 65, '不报价', '', '/admin/supplier/inquiry/unquote/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-04-28 14:12:17', 136, 'N', 291, '不报价', '64,65', '68,82,288,291', 'BUTTON');
INSERT INTO `menus` VALUES (642, 'SUPPLIER', 610, '供应商默认联系人', '', '/admin/supplier/default/contact', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 292, '供应商默认联系人', '64,610', '68,82,289,292', 'BUTTON');
INSERT INTO `menus` VALUES (643, 'PLATFORM', 613, '公告详情', '', '/admin/notice/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 250, '公告详情', '21,613', '63,244,250', 'BUTTON');
INSERT INTO `menus` VALUES (644, 'PLATFORM', 613, '公告编码', '', '/admin/notice/number', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 251, '公告编码', '21,613', '63,244,251', 'BUTTON');
INSERT INTO `menus` VALUES (645, 'PLATFORM', 613, '公告编辑', '', '/admin/notice/edited/{id:[0-9]+}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 252, '公告编辑', '21,613', '63,244,252', 'BUTTON');
INSERT INTO `menus` VALUES (646, 'PLATFORM', 613, '发布公告', '', '/admin/notice/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 253, '发布公告', '21,613', '63,244,253', 'BUTTON');
INSERT INTO `menus` VALUES (647, 'PLATFORM', 613, '删除公告', '', '/admin/notice/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 254, '删除公告', '21,613', '63,244,254', 'BUTTON');
INSERT INTO `menus` VALUES (648, 'PLATFORM', 613, '置顶公告', '', '/admin/notice/topping', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 255, '置顶公告', '21,613', '63,244,255', 'BUTTON');
INSERT INTO `menus` VALUES (649, 'PLATFORM', 613, '取消公告', '', '/admin/notice/cancel', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 256, '取消公告', '21,613', '63,244,256', 'BUTTON');
INSERT INTO `menus` VALUES (651, 'PLATFORM', 613, '公告审核', '', '/admin/notice/audit', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 258, '公告审核', '21,613', '63,244,258', 'BUTTON');
INSERT INTO `menus` VALUES (652, 'PLATFORM', 614, '消息详情', '', '/admin/message/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 246, '消息详情', '21,614', '63,245,246', 'BUTTON');
INSERT INTO `menus` VALUES (653, 'PLATFORM', 614, '删除消息', '', '/admin/message/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 247, '删除消息', '21,614', '63,245,247', 'BUTTON');
INSERT INTO `menus` VALUES (654, 'PLATFORM', 614, '消息已读', '', '/admin/message/read', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 248, '消息已读', '21,614', '63,245,248', 'BUTTON');
INSERT INTO `menus` VALUES (655, 'PLATFORM', 614, '消息未读', '', '/admin/message/unread', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 249, '消息未读', '21,614', '63,245,249', 'BUTTON');
INSERT INTO `menus` VALUES (656, 'PLATFORM', 615, '修改邮箱', '', '/admin/change_account/email', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 260, '修改邮箱', '21,615', '30,259,260', 'BUTTON');
INSERT INTO `menus` VALUES (657, 'PLATFORM', 615, '修改手机号', '', '/admin/change_account/phone', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 261, '修改手机号', '21,615', '30,259,261', 'BUTTON');
INSERT INTO `menus` VALUES (658, 'PLATFORM', 615, '修改账号', '', '/admin/change_account/account', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 262, '修改账号', '21,615', '30,259,262', 'BUTTON');
INSERT INTO `menus` VALUES (659, 'PLATFORM', 615, '手机号验证', '', '/admin/change_account/phoneVerify', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 263, '手机号验证', '21,615', '30,259,263', 'BUTTON');
INSERT INTO `menus` VALUES (660, 'PLATFORM', 615, '邮箱验证', '', '/admin/change_account/emailVerify', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 264, '邮箱验证', '21,615', '30,259,264', 'BUTTON');
INSERT INTO `menus` VALUES (661, 'PLATFORM', 615, '用户信息', '', '/admin/auth/info', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 265, '用户信息', '21,615', '30,259,265', 'BUTTON');
INSERT INTO `menus` VALUES (662, 'PLATFORM', 615, '修改密码', '', '/admin/auth/change/password', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 266, '修改密码', '21,615', '30,259,266', 'BUTTON');
INSERT INTO `menus` VALUES (663, 'PLATFORM', 615, '切换采购商', '', '/admin/auth/change/org', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 267, '切换采购商', '21,615', '30,259,267', 'BUTTON');
INSERT INTO `menus` VALUES (664, 'PLATFORM', 615, '头像', '', '/admin/auth/avatar', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 268, '头像', '21,615', '30,259,268', 'BUTTON');
INSERT INTO `menus` VALUES (665, 'PLATFORM', 615, '登出', '', '/admin/auth/logout', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 269, '登出', '21,615', '30,259,269', 'BUTTON');
INSERT INTO `menus` VALUES (666, 'PLATFORM', 615, '获取采购商', '', '/admin/auth/purchasers', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 270, '获取采购商', '21,615', '30,259,270', 'BUTTON');
INSERT INTO `menus` VALUES (673, 'COMMON', 24, '用户角色', '', '/admin/rolesuser', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:02:01', 136, 'N', 293, '用户角色', '21,24', '30,33,293', 'BUTTON');
INSERT INTO `menus` VALUES (674, 'PLATFORM', 0, '消息公告', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVcSAPsEGAAAJAeMeTAk913.png', '/message', 'MENU', 0, 1, 'NORMAL', '', '2024-04-09 13:18:13', 136, '2024-05-22 10:17:41', 1875271515914638372, 'N', 294, '消息公告', NULL, '294', NULL);
INSERT INTO `menus` VALUES (693, 'SUPPLIER', 0, '消息公告', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNVcyAUMM9AAAJAeMeTAk978.png', '/supplierMesssge', 'MENU', 0, 6, 'NORMAL', '', '2024-04-09 14:40:39', 136, '2024-05-22 10:17:49', 1875271515914638372, 'N', 295, '消息公告', NULL, '68,295', NULL);
INSERT INTO `menus` VALUES (694, 'SUPPLIER', 693, '公告管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHq-AYYn3AAAF5MEkb68651.png', '/supplierMesssge/notice', 'MENU', 0, 0, 'NORMAL', '', '2024-04-09 14:41:45', 136, '2024-07-10 13:40:01', 1945082105276930210, 'N', 304, '公告管理', '693', '68,295,298,304', NULL);
INSERT INTO `menus` VALUES (695, 'SUPPLIER', 693, '消息管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHqWASeLLAAAJP4EdE2g817.png', '/supplierMesssge/message', 'MENU', 0, 0, 'NORMAL', '', '2024-04-09 14:42:38', 136, '2024-07-10 13:39:50', 1945082105276930210, 'N', 297, '消息管理', '693', '68,295,297', NULL);
INSERT INTO `menus` VALUES (698, 'SUPPLIER', 694, '公告列表', '', '/admin/supplier/notice', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 304, '公告列表', '693,694', '68,295,298,304', 'BUTTON');
INSERT INTO `menus` VALUES (699, 'SUPPLIER', 694, '公告详情', '', '/admin/supplier/notice/{notice_id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 305, '公告详情', '693,694', '68,295,298,305', 'BUTTON');
INSERT INTO `menus` VALUES (700, 'SUPPLIER', 695, '消息详情', '', '/admin/supplier/message/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 299, '消息详情', '693,695', '68,295,297,299', 'BUTTON');
INSERT INTO `menus` VALUES (701, 'SUPPLIER', 695, '消息列表', '', '/admin/supplier/message', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 300, '消息列表', '693,695', '68,295,297,300', 'BUTTON');
INSERT INTO `menus` VALUES (702, 'SUPPLIER', 695, '删除消息', '', '/admin/supplier/message/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 301, '删除消息', '693,695', '68,295,297,301', 'BUTTON');
INSERT INTO `menus` VALUES (703, 'SUPPLIER', 695, '消息批量已读', '', '/admin/supplier/message/read', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 302, '消息批量已读', '693,695', '68,295,297,302', 'BUTTON');
INSERT INTO `menus` VALUES (704, 'SUPPLIER', 695, '消息批量未读', '', '/admin/supplier/message/unread', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, NULL, NULL, 'N', 303, '消息批量未读', '693,695', '68,295,297,303', 'BUTTON');
INSERT INTO `menus` VALUES (711, 'SUPPLIER', 771, '人员管理', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHR2APz6gAAAKjXQAnlc698.png', '/supplierPersonnel/personnel_list', 'MENU', 0, 0, 'NORMAL', '', '2024-04-09 14:44:40', 136, '2024-07-10 13:33:18', 1945082105276930210, 'N', 296, '人员管理', '771', '68,296', NULL);
INSERT INTO `menus` VALUES (712, 'PLATFORM', 57, '获取比价编码', '', '/admin/compare/number', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-10 13:53:33', 136, '2024-04-10 13:53:33', 136, 'N', 317, '获取比价编码', '40,57', '63,88,317', NULL);
INSERT INTO `menus` VALUES (713, 'PLATFORM', 57, '编辑比价', '', '/admin/compare/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-10 13:54:15', 136, '2024-04-24 09:50:22', 136, 'N', 314, '编辑比价', '40,57', '63,88,314', NULL);
INSERT INTO `menus` VALUES (714, 'PLATFORM', 57, '新增比价', '', '/admin/compare/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-10 13:55:04', 136, '2024-04-10 13:55:04', 136, 'N', 315, '新增比价', '40,57', '63,88,315', NULL);
INSERT INTO `menus` VALUES (715, 'PLATFORM', 57, '删除比价', '', '/admin/compare/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-10 13:55:33', 136, '2024-04-10 13:55:33', 136, 'N', 316, '删除比价', '40,57', '63,88,316', NULL);
INSERT INTO `menus` VALUES (719, 'SUPPLIER', 711, '人员详情', '', '/admin/supplier/user/{id}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:09:08', 136, 'N', 307, '人员详情', '711', '68,296,307', 'BUTTON');
INSERT INTO `menus` VALUES (720, 'SUPPLIER', 711, '编辑人员', '', '/admin/supplier/user/edited/{id:[0-9]+}', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:09:17', 136, 'N', 308, '编辑人员', '711', '68,296,308', 'BUTTON');
INSERT INTO `menus` VALUES (721, 'SUPPLIER', 711, '新增人员', '', '/admin/supplier/user/add', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:08:51', 136, 'N', 309, '新增人员', '711', '68,296,309', 'BUTTON');
INSERT INTO `menus` VALUES (722, 'SUPPLIER', 711, '删除人员', '', '/admin/supplier/user/delete', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:09:27', 136, 'N', 310, '删除人员', '711', '68,296,310', 'BUTTON');
INSERT INTO `menus` VALUES (723, 'SUPPLIER', 711, '禁用人员', '', '/admin/supplier/user/disable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:08:35', 136, 'N', 311, '禁用人员', '711', '68,296,311', 'BUTTON');
INSERT INTO `menus` VALUES (724, 'SUPPLIER', 711, '启用人员', '', '/admin/supplier/user/enable', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:09:39', 136, 'N', 312, '启用人员', '711', '68,296,312', 'BUTTON');
INSERT INTO `menus` VALUES (726, 'SUPPLIER', 711, '更改用户密码', '', '/admin/supplier/user/change/password', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:10:04', 136, 'N', 318, '更改用户密码', '711', '68,296,318', 'BUTTON');
INSERT INTO `menus` VALUES (727, 'SUPPLIER', 711, '获取拼音', '', '/admin/supplier/user/pinyin', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:10:44', 136, 'N', 319, '获取拼音', '711', '68,296,319', 'BUTTON');
INSERT INTO `menus` VALUES (728, 'SUPPLIER', 711, '获取角色', '', '/admin/supplier/user/roles', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:10:21', 136, 'N', 320, '获取角色', '711', '68,296,320', 'BUTTON');
INSERT INTO `menus` VALUES (729, 'SUPPLIER', 711, '获取用户菜单', '', '/admin/supplier/user/menus', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 10:10:34', 136, 'N', 321, '获取用户菜单', '711', '68,296,321', 'BUTTON');
INSERT INTO `menus` VALUES (730, 'SUPPLIER', 711, '角色', '', '/admin/supplier/rolesuser', 'BUTTON', 0, 0, 'NORMAL', 'BOSS', NULL, NULL, '2024-05-11 09:38:16', 136, 'N', 195, '角色', '711', '30,33,195', 'BUTTON');
INSERT INTO `menus` VALUES (731, 'PLATFORM', 35, '新增银行', '', '/admin/bank/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:24:12', 136, '2024-04-19 09:24:12', 136, 'N', 140, '新增银行', '32,35', '55,58,140', NULL);
INSERT INTO `menus` VALUES (732, 'PLATFORM', 35, '编辑银行', '', '/admin/bank/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:24:43', 136, '2024-04-19 09:24:43', 136, 'N', 139, '编辑银行', '32,35', '55,58,139', NULL);
INSERT INTO `menus` VALUES (733, 'PLATFORM', 35, '删除银行', '', '/admin/bank/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:25:12', 136, '2024-04-19 09:25:12', 136, 'N', 141, '删除银行', '32,35', '55,58,141', NULL);
INSERT INTO `menus` VALUES (734, 'PLATFORM', 35, '禁用银行', '', '/admin/bank/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:25:33', 136, '2024-04-19 09:25:33', 136, 'N', 142, '禁用银行', '32,35', '55,58,142', NULL);
INSERT INTO `menus` VALUES (735, 'PLATFORM', 35, '启用银行', '', '/admin/bank/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:25:59', 136, '2024-04-19 09:25:59', 136, 'N', 143, '启用银行', '32,35', '55,58,143', NULL);
INSERT INTO `menus` VALUES (736, 'PLATFORM', 34, '新增币种', '', '/admin/currency/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:47:38', 136, '2024-04-19 09:47:38', 136, 'N', 130, '新增币种', '32,34', '55,57,130', NULL);
INSERT INTO `menus` VALUES (737, 'PLATFORM', 34, '修改币种', '', '/admin/currency/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:47:53', 136, '2024-04-19 09:47:53', 136, 'N', 129, '修改币种', '32,34', '55,57,129', NULL);
INSERT INTO `menus` VALUES (738, 'PLATFORM', 34, '删除币种', '', '/admin/currency/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:48:08', 136, '2024-04-19 09:48:08', 136, 'N', 131, '删除币种', '32,34', '55,57,131', NULL);
INSERT INTO `menus` VALUES (739, 'PLATFORM', 34, '禁用币种', '', '/admin/currency/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:48:24', 136, '2024-04-19 09:48:24', 136, 'N', 132, '禁用币种', '32,34', '55,57,132', NULL);
INSERT INTO `menus` VALUES (740, 'PLATFORM', 34, '启用币种', '', '/admin/currency/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 09:48:40', 136, '2024-04-19 09:48:40', 136, 'N', 133, '启用币种', '32,34', '55,57,133', NULL);
INSERT INTO `menus` VALUES (741, 'PLATFORM', 39, '新增', '', '/admin/paycond/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 10:06:54', 136, '2024-04-19 10:06:54', 136, 'N', 158, '新增', '32,39', '55,62,158', NULL);
INSERT INTO `menus` VALUES (742, 'PLATFORM', 39, '编辑', '', '/admin/paycond/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 10:07:13', 136, '2024-04-19 10:07:13', 136, 'N', 157, '编辑', '32,39', '55,62,157', NULL);
INSERT INTO `menus` VALUES (743, 'PLATFORM', 39, '删除', '', '/admin/paycond/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 10:07:36', 136, '2024-04-19 10:07:36', 136, 'N', 159, '删除', '32,39', '55,62,159', NULL);
INSERT INTO `menus` VALUES (744, 'PLATFORM', 39, '禁用', '', '/admin/paycond/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 10:07:59', 136, '2024-04-19 10:07:59', 136, 'N', 160, '禁用', '32,39', '55,62,160', NULL);
INSERT INTO `menus` VALUES (745, 'PLATFORM', 39, '启用', '', '/admin/paycond/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-19 10:08:15', 136, '2024-04-19 10:08:15', 136, 'N', 161, '启用', '32,39', '55,62,161', NULL);
INSERT INTO `menus` VALUES (746, 'PLATFORM', 41, '比价', '', '/inquiryRate/newCompetitive', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-22 13:27:38', 136, '2024-05-06 14:03:49', 136, 'N', 339, '比价', '40,41', '63,64,339', NULL);
INSERT INTO `menus` VALUES (747, 'PLATFORM', 44, '获取企业注册信息', '', '/admin/supplier/company', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-22 14:34:15', 136, '2024-04-22 14:34:15', 136, 'N', 326, '获取企业注册信息', '42,44', '65,67,326', NULL);
INSERT INTO `menus` VALUES (749, 'PLATFORM', 44, '供应商详情(编辑)', '', '/admin/supplier/audit/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-22 16:48:47', 136, '2024-04-22 16:49:39', 136, 'N', 329, '供应商详情(编辑)', '42,44', '65,67,329', NULL);
INSERT INTO `menus` VALUES (750, 'PLATFORM', 44, '供应商详情', '', '/admin/supplier/base/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-22 17:13:06', 136, '2024-04-22 17:13:06', 136, 'N', 241, '供应商详情', '42,44', '65,67,241', NULL);
INSERT INTO `menus` VALUES (751, 'PLATFORM', 57, '比价审批', '', '/admin/compare/audit/verify', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-24 08:09:47', 136, '2024-04-30 13:36:07', 136, 'N', 331, '比价审批', '40,57', '63,88,331', NULL);
INSERT INTO `menus` VALUES (752, 'COMMON', 37, '供应商详情', '', '/admin/supplier/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-26 08:13:40', 136, '2024-04-26 08:13:40', 136, 'N', 240, '供应商详情', '36,37', '65,67,240', NULL);
INSERT INTO `menus` VALUES (753, 'COMMON', 47, '供应商详情', '', '/admin/supplier/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-26 08:14:41', 136, '2024-04-26 08:14:41', 136, 'N', 240, '供应商详情', '46,47', '65,67,240', NULL);
INSERT INTO `menus` VALUES (754, 'COMMON', 45, '供应商详情', '', '/admin/supplier/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-26 08:15:19', 136, '2024-04-26 08:15:19', 136, 'N', 240, '供应商详情', '36,45', '65,67,240', NULL);
INSERT INTO `menus` VALUES (755, 'PLATFORM', 72, '比价审批', '', '/inquiryRate/competitiveDetails2', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-26 15:10:18', 136, '2024-04-30 11:11:01', 136, 'N', 331, '比价审批', '40,57,72', '63,88,125,331', NULL);
INSERT INTO `menus` VALUES (756, 'PLATFORM', 72, '终止审批', '', '/inquiryRate/competitiveDetails1', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-26 15:10:47', 136, '2024-04-30 11:10:52', 136, 'N', 332, '终止审批', '40,57,72', '63,88,125,332', NULL);
INSERT INTO `menus` VALUES (757, 'SUPPLIER', 631, '不报价', '', '/quoteManage/inquiryDetail22', 'BUTTON', 0, 0, 'NORMAL', '', '2024-04-28 13:53:45', 136, '2024-05-08 09:08:55', 136, 'N', 291, '不报价', '64,65,631', '68,82,288,291', NULL);
INSERT INTO `menus` VALUES (758, 'PLATFORM', 44, '编辑', '', '/admin/supplier/base/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-10 16:09:07', 136, '2024-05-10 16:09:07', 136, 'N', 330, '编辑', '42,44', '65,67,330', NULL);
INSERT INTO `menus` VALUES (759, 'PLATFORM', 44, '新增供应商', '', '/admin/supplier/base/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-10 16:11:35', 136, '2024-05-10 16:11:35', 136, 'N', 340, '新增供应商', '42,44', '65,67,340', NULL);
INSERT INTO `menus` VALUES (760, 'SUPPLIER', 711, '用户角色', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZDFeCABkCoAAAH6hQuUzY716.png', '/admin/rolesuser', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-11 10:06:22', 136, '2024-05-14 15:42:25', 1555652485874454528, 'N', 293, '用户角色', '711', '30,33,293', NULL);
INSERT INTO `menus` VALUES (762, 'SUPPLIER', 47, '进度', '', '/admin/supplier/audit/progress', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-13 15:50:31', 136, '2024-05-13 15:52:13', 136, 'N', 342, '进度', '46,47', '73,74,342', NULL);
INSERT INTO `menus` VALUES (763, 'SUPPLIER', 47, '审核记录', '', '/admin/supplier/audit/history', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-13 15:50:50', 136, '2024-05-13 15:52:05', 136, 'N', 343, '审核记录', '46,47', '73,74,343', NULL);
INSERT INTO `menus` VALUES (764, 'SUPPLIER', 45, '提交准入协同', '', 'SupplierController@company', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-13 16:05:06', 136, '2024-05-13 16:05:33', 136, 'N', 347, '提交准入协同', '36,45', '59,347', NULL);
INSERT INTO `menus` VALUES (765, 'SUPPLIER', 45, '获取企业信息', '', '/admin/supplier/company', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-13 16:06:02', 136, '2024-05-13 16:06:02', 136, 'N', 348, '获取企业信息', '36,45', '59,348', NULL);
INSERT INTO `menus` VALUES (766, 'SUPPLIER', 45, '企业类型', '', '/admin/supplier/enterprise_type', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-13 16:06:22', 136, '2024-05-13 16:07:38', 136, 'N', 346, '企业类型', '36,45', '59,60,346', NULL);
INSERT INTO `menus` VALUES (769, 'SUPPLIER', 768, '准入申请', '', '/supplierClient/apply', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-15 08:56:12', 136, '2024-05-15 09:26:30', 136, 'N', 351, '准入申请', '36,768', '59,350,351', NULL);
INSERT INTO `menus` VALUES (770, 'SUPPLIER', 768, '准入详情', '', '/supplierClient/applyInfo', 'BUTTON', 0, 0, 'NORMAL', '', '2024-05-15 08:56:35', 136, '2024-05-15 09:26:22', 136, 'N', 352, '准入详情', '36,768', '59,350,352', NULL);
INSERT INTO `menus` VALUES (771, 'SUPPLIER', 0, '基础服务', 'https://file5.erui.com/group1/M00/00/B2/rBGYHGZNoWKAGWOZAAAGYPkH2gs949.png', '/jichufuw', 'MENU', 0, 0, 'NORMAL', '', '2024-05-22 15:38:56', 136, '2024-05-22 15:50:03', 136, 'N', 68, '基础服务', '0', '68', NULL);
INSERT INTO `menus` VALUES (774, 'SUPPLIER', 0, '竞价管理', 'https://file5.erui.com/group1/M00/00/B4/rBGYHGZe2TeAWcyMAAADm2WcFRI158.png', '/biddingManage', 'MENU', 0, 7, 'NORMAL', '', '2024-06-03 13:47:55', 1555652485874454528, '2024-06-04 17:07:04', 1555652485874454528, 'N', 378, '竞价管理', '0', '68,378', NULL);
INSERT INTO `menus` VALUES (775, 'SUPPLIER', 774, '竞价查询', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHEiAOTUsAAAF-H4w9pA873.png', '/biddingManage/biddingList', 'MENU', 0, 1, 'NORMAL', '', '2024-06-03 13:50:16', 1555652485874454528, '2024-07-10 13:29:45', 1945082105276930210, 'N', 380, '竞价查询', '774', '68,378,380', NULL);
INSERT INTO `menus` VALUES (776, 'PLATFORM', 0, '竞价管理', 'https://file5.erui.com/group1/M00/00/B4/rBGYHGZe2SKADxK5AAADm2WcFRI154.png', '/bidding', 'MENU', 0, 0, 'NORMAL', '', '2024-06-03 14:51:17', 136, '2024-06-18 11:26:10', 136, 'N', 354, '竞价管理', '0', '354', NULL);
INSERT INTO `menus` VALUES (777, 'PLATFORM', 776, '竞价发布', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHC-AIsehAAAB0rLjkxE753.png', '/bidding/BiddingList', 'MENU', 0, 3, 'NORMAL', '', '2024-06-03 14:52:44', 136, '2024-07-10 13:29:21', 1945082105276930210, 'N', 354, '竞价发布', '776', '354', NULL);
INSERT INTO `menus` VALUES (780, 'PLATFORM', 776, '竞价定标', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHB2ABzI1AAACX4GHCvw966.png', '/bidding/PicketageList', 'MENU', 0, 1, 'NORMAL', '', '2024-06-11 08:42:08', 1555652485874454528, '2024-07-10 13:29:01', 1945082105276930210, 'N', 365, '竞价定标', '776', '354,365', NULL);
INSERT INTO `menus` VALUES (781, 'SUPPLIER', 774, '竞价大厅', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHDmAfD0hAAAEAjBVe8c753.png', '/biddingManage/biddingHall', 'MENU', 0, 0, 'NORMAL', '', '2024-06-11 14:34:19', 136, '2024-07-10 13:29:29', 1945082105276930210, 'N', 383, '竞价大厅', '774', '68,378,383', NULL);
INSERT INTO `menus` VALUES (782, 'SUPPLIER', 775, '缴纳保证金', '', '/admin/supplier/bidbill/pay/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-13 13:43:18', 1555652485874454528, '2024-06-13 13:43:18', 1555652485874454528, 'N', 382, '缴纳保证金', '774,775', '68,378,382', NULL);
INSERT INTO `menus` VALUES (783, 'SUPPLIER', 775, '竞价大厅', '', '/admin/supplier/bidbill/hall/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-13 13:47:53', 1555652485874454528, '2024-06-13 13:47:53', 1555652485874454528, 'N', 384, '竞价大厅', '774,775', '68,378,384', NULL);
INSERT INTO `menus` VALUES (784, 'SUPPLIER', 778, '缴纳保证金', '', '/admin/supplier/bidbill/pay/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-13 13:55:04', 1555652485874454528, '2024-06-13 13:55:04', 1555652485874454528, 'N', 382, '缴纳保证金', '774,778', '68,378,382', NULL);
INSERT INTO `menus` VALUES (785, 'PLATFORM', 776, '竞价大厅', 'https://file5.erui.com/group1/M00/00/BC/rBGYHGaOHBKANcUtAAAEAjBVe8c716.png', '/bidding/BiddingSaloon', 'MENU', 0, 0, 'NORMAL', '', '2024-06-17 10:02:40', 1875271515914638372, '2024-07-10 13:28:52', 1945082105276930210, 'N', 362, '竞价大厅', '776', '354,362', NULL);
INSERT INTO `menus` VALUES (786, 'PLATFORM', 777, '竞价详情', '', '/admin/bidbill/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:37:51', 136, '2024-06-18 13:37:51', 136, 'N', 355, '竞价详情', '776,785', '354,355', NULL);
INSERT INTO `menus` VALUES (787, 'PLATFORM', 777, '修改竞价', '', '/admin/bidbill/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:38:13', 136, '2024-06-18 13:38:13', 136, 'N', 356, '修改竞价', '776,785', '354,356', NULL);
INSERT INTO `menus` VALUES (788, 'PLATFORM', 777, '新增竞价', '', '/admin/bidbill/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:38:39', 136, '2024-06-18 13:38:39', 136, 'N', 357, '新增竞价', '776,785', '354,357', NULL);
INSERT INTO `menus` VALUES (789, 'PLATFORM', 785, '获取竞价编码', '', '/admin/bidbill/number', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:38:59', 136, '2024-06-18 16:07:32', 1875271515914638372, 'N', 358, '获取竞价编码', '776,785', '354,358', NULL);
INSERT INTO `menus` VALUES (790, 'PLATFORM', 777, '删除竞价', '', '/admin/bidbill/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:39:17', 136, '2024-06-18 13:39:17', 136, 'N', 359, '删除竞价', '776,785', '354,359', NULL);
INSERT INTO `menus` VALUES (792, 'PLATFORM', 785, '变更报名截止日期', '', '/admin/bidbill/change/{id}', 'BUTTON', 0, 0, 'DISABLED', '', '2024-06-18 13:39:59', 136, '2024-06-25 15:37:28', 136, 'N', 361, '变更报名截止日期', '776,785', '354,361', NULL);
INSERT INTO `menus` VALUES (793, 'PLATFORM', 777, '资审供应商列表', '', '/admin/bidbill/suppliers/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:40:23', 136, '2024-06-18 13:40:23', 136, 'N', 363, '资审供应商列表', '776,785', '354,363', NULL);
INSERT INTO `menus` VALUES (794, 'PLATFORM', 785, '付款供应商列表', '', '/admin/bidbill/pays/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:40:48', 136, '2024-06-25 15:36:31', 136, 'N', 364, '付款供应商列表', '776,785', '354,364', NULL);
INSERT INTO `menus` VALUES (795, 'PLATFORM', 777, '收取保证金', '', '/admin/bidbill/pay/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:41:18', 136, '2024-06-18 13:41:18', 136, 'N', 368, '收取保证金', '776,785', '354,368', NULL);
INSERT INTO `menus` VALUES (796, 'PLATFORM', 777, '竞价资审', '', '/admin/bidbill/check/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:41:37', 136, '2024-06-18 13:41:37', 136, 'N', 367, '竞价资审', '776,785', '354,367', NULL);
INSERT INTO `menus` VALUES (797, 'PLATFORM', 785, '竞价离线', '', '/admin/bidbill/offline/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:44:20', 136, '2024-06-18 13:44:20', 136, 'N', 377, '竞价离线', '776,785', '354,377', NULL);
INSERT INTO `menus` VALUES (798, 'PLATFORM', 785, '绑定用户ID和竞价ID', '', '/admin/bidbill/bindUid/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:44:47', 136, '2024-06-18 13:44:47', 136, 'N', 375, '绑定用户ID和竞价ID', '776,785', '354,375', NULL);
INSERT INTO `menus` VALUES (799, 'PLATFORM', 785, '竞价大厅详情', '', '/admin/bidbill/hall/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:45:16', 136, '2024-06-18 13:45:16', 136, 'N', 374, '竞价大厅详情', '776,785', '354,374', NULL);
INSERT INTO `menus` VALUES (800, 'PLATFORM', 785, '竞价暂停', '', '/admin/bidbill/stop/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:46:05', 136, '2024-06-18 13:46:05', 136, 'N', 371, '竞价暂停', '776,785', '354,371', NULL);
INSERT INTO `menus` VALUES (801, 'PLATFORM', 785, '竞价开始', '', '/admin/bidbill/begin/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:46:45', 136, '2024-06-18 13:46:45', 136, 'N', 372, '竞价开始', '776,785', '354,372', NULL);
INSERT INTO `menus` VALUES (802, 'PLATFORM', 780, '中标供应商', '', '/admin/bidbill/winning/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-18 13:48:26', 136, '2024-07-08 08:48:28', 1875271515914638372, 'N', 366, '中标供应商', '776,780', '354,365,366', NULL);
INSERT INTO `menus` VALUES (805, 'PLATFORM', 777, '启动竞价', '', '/admin/bidbill/start/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-25 15:26:48', 136, '2024-07-08 08:48:51', 1875271515914638372, 'N', 370, '启动竞价', '776,777', '354,446,370', NULL);
INSERT INTO `menus` VALUES (806, 'PLATFORM', 780, '退还保证金', '', '/admin/bidbill/returns/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-25 15:55:26', 136, '2024-06-25 15:55:26', 136, 'N', 373, '退还保证金', '776,780', '354,373', NULL);
INSERT INTO `menus` VALUES (807, 'PLATFORM', 780, '竞价终止', '', '/admin/bidbill/termination/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-25 15:57:23', 136, '2024-06-25 15:57:23', 136, 'N', 369, '竞价终止', '776,780', '354,369', NULL);
INSERT INTO `menus` VALUES (808, 'PLATFORM', 780, '定标', '', '/admin/bidbill/decision/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-06-25 16:04:50', 136, '2024-07-08 08:48:12', 1875271515914638372, 'N', 392, '定标', '776,780', '354,365,392', NULL);
INSERT INTO `menus` VALUES (809, 'SUPPLIER', 775, '报名', '', '/admin/supplier/bidbill/signup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 09:29:46', 136, '2024-07-04 09:55:59', 136, 'N', 395, '报名', '774,775', '68,378,395', NULL);
INSERT INTO `menus` VALUES (810, 'SUPPLIER', 775, '不报名', '', '/admin/supplier/bidbill/unsignup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 09:33:17', 136, '2024-07-04 09:56:24', 136, 'N', 396, '不报名', '774,775', '68,378,396', NULL);
INSERT INTO `menus` VALUES (811, 'SUPPLIER', 775, '竞价详情', '', '/biddingManage/biddingDetail', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 09:46:32', 136, '2024-07-04 10:25:34', 136, 'N', 379, '竞价详情', '774,775', '68,378,379', NULL);
INSERT INTO `menus` VALUES (812, 'SUPPLIER', 811, '报名', '', '/admin/supplier/bidbill/signup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 09:55:13', 136, '2024-07-04 09:55:13', 136, 'N', 395, '报名', '774,775,811', '68,378,395', NULL);
INSERT INTO `menus` VALUES (813, 'SUPPLIER', 811, '不报名', '', '/admin/supplier/bidbill/unsignup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 10:01:36', 136, '2024-07-04 10:01:36', 136, 'N', 396, '不报名', '774,775,811', '68,378,396', NULL);
INSERT INTO `menus` VALUES (814, 'SUPPLIER', 811, '缴纳保证金', '', '/admin/supplier/bidbill/pay/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 10:02:09', 136, '2024-07-04 10:02:09', 136, 'N', 382, '缴纳保证金', '774,775,811', '68,378,382', NULL);
INSERT INTO `menus` VALUES (815, 'SUPPLIER', 811, '竞价大厅详情', '', '/admin/supplier/bidbill/hall/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-04 10:02:53', 136, '2024-07-04 10:02:53', 136, 'N', 384, '竞价大厅详情', '774,775,811', '68,378,384', NULL);
INSERT INTO `menus` VALUES (817, 'SUPPLIER', 781, '竞价报价', '', '/admin/supplier/bidbill/quote/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-05 14:26:41', 1875271515914638372, '2024-07-05 14:26:41', 1875271515914638372, 'N', 385, '竞价报价', '774,781', '68,378,383,385', NULL);
INSERT INTO `menus` VALUES (818, 'SUPPLIER', 781, '竞价大厅详情', '', '/admin/supplier/bidbill/hall/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-05 14:27:53', 1875271515914638372, '2024-07-05 14:27:53', 1875271515914638372, 'N', 384, '竞价大厅详情', '774,781', '68,378,383,384', NULL);
INSERT INTO `menus` VALUES (819, 'SUPPLIER', 781, '绑定用户ID和竞价ID', '', '/admin/supplier/bidbill/bindUid/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-05 14:28:22', 1875271515914638372, '2024-07-05 14:28:22', 1875271515914638372, 'N', 386, '绑定用户ID和竞价ID', '774,781', '68,378,383,386', NULL);
INSERT INTO `menus` VALUES (820, 'SUPPLIER', 781, '绑定竞价ID', '', '/admin/supplier/bidbill/bindGroup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-05 14:28:41', 1875271515914638372, '2024-07-05 14:28:41', 1875271515914638372, 'N', 387, '绑定竞价ID', '774,781', '68,378,383,387', NULL);
INSERT INTO `menus` VALUES (821, 'SUPPLIER', 781, '竞价离线', '', '/admin/supplier/bidbill/offline/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-05 14:28:59', 1875271515914638372, '2024-07-05 14:28:59', 1875271515914638372, 'N', 388, '竞价离线', '774,781', '68,378,383,388', NULL);
INSERT INTO `menus` VALUES (822, 'PLATFORM', 777, '竞价终止', '', '/admin/bidbill/termination/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-05 14:37:59', 1875271515914638372, '2024-07-05 14:37:59', 1875271515914638372, 'N', 369, '竞价终止', '776,777', '354,446,369', NULL);
INSERT INTO `menus` VALUES (823, 'PLATFORM', 785, '结束竞价', '', '/admin/bidbill/finished/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-08 08:55:02', 1875271515914638372, '2024-07-09 15:59:22', 1875271515914638372, 'N', 425, '结束竞价', '776,785', '354,446,425', NULL);
INSERT INTO `menus` VALUES (824, 'PLATFORM', 777, '竞价商品导入', '', '/bidding/BiddingApproval', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-09 08:53:39', 1875271515914638372, '2024-07-09 08:53:39', 1875271515914638372, 'N', 444, '竞价商品导入', '776,777', '354,446,444', NULL);
INSERT INTO `menus` VALUES (2000, 'PLATFORM', 2000, '招标管理', '', '/inviteTenders/tenderCenter', 'MENU', 0, 0, 'NORMAL', '', '2024-07-09 16:22:42', 136, '2024-07-10 14:24:03', 136, 'N', 1001, '招标管理', '2000', '1001', NULL);
INSERT INTO `menus` VALUES (2003, 'PLATFORM', 2000, '招标立项', '', '/admin/project', 'MENU', 0, 0, 'NORMAL', '', '2024-07-09 16:32:14', 136, '2024-07-09 16:32:14', 136, 'N', 1001, '招标立项', '2000', '1001', NULL);
INSERT INTO `menus` VALUES (2004, 'PLATFORM', 0, '招标管理', 'https://file5.erui.com/group1/M00/00/C1/rBGYHGa1zTyALvWtAAACBTe2DbQ600.png', '/inviteTenders', 'MENU', 0, 0, 'NORMAL', '', '2024-07-10 14:32:14', 136, '2024-08-09 16:03:09', 1875271515914638372, 'N', 1001, '招标管理', '0', '1001', NULL);
INSERT INTO `menus` VALUES (2005, 'PLATFORM', 2004, '招标中心', 'https://file5.erui.com/group1/M00/00/C2/rBGYHGa1zceAKRBlAAAD_TsxJwM095.png', '/inviteTenders/tendersCenter', 'MENU', 0, 1, 'NORMAL', '', '2024-07-10 14:33:30', 136, '2024-08-09 16:05:28', 1875271515914638372, 'N', 1001, '招标中心', '2004', '1001', NULL);
INSERT INTO `menus` VALUES (2007, 'PLATFORM', 2005, '供方入围', '', '/inviteTenders/supplier_shortlistedDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-11 16:08:20', 136, '2024-08-16 13:58:13', 136, 'N', 1005, '供方入围', '2004,2005', '1001,1004,1005', NULL);
INSERT INTO `menus` VALUES (2008, 'SUPPLIER', 0, '投标管理', 'https://file5.erui.com/group1/M00/00/C1/rBGYHGa1zUSAWp5xAAADdArd94U369.png', '/supplierTenders', 'MENU', 0, 8, 'NORMAL', '', '2024-07-17 15:39:42', 136, '2024-08-09 16:03:17', 1875271515914638372, 'N', 1019, '投标管理', '0', '68,1019', NULL);
INSERT INTO `menus` VALUES (2009, 'SUPPLIER', 2008, '招标查询', 'https://file5.erui.com/group1/M00/00/C2/rBGYHGa1zeSAUe4NAAAEJmQcfko586.png', '/supplierTenders/biddingInquiry', 'MENU', 0, 0, 'NORMAL', '', '2024-07-17 15:40:50', 136, '2024-08-09 16:05:57', 1875271515914638372, 'N', 1025, '招标查询', '2008', '68,1019,1025', NULL);
INSERT INTO `menus` VALUES (2010, 'SUPPLIER', 2008, '报名', '', '/admin/supplier/project/signup/{id}', 'BUTTON', 0, 0, 'DISABLED', '', '2024-07-17 15:41:24', 136, '2024-07-17 15:41:46', 136, 'Y', 1037, '报名', '2008', '68,1019,1025,1037', NULL);
INSERT INTO `menus` VALUES (2011, 'SUPPLIER', 2009, '报名', '', '/admin/supplier/project/signup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-17 15:42:15', 136, '2024-07-17 15:42:15', 136, 'N', 1037, '报名', '2008,2009', '68,1019,1025,1037', NULL);
INSERT INTO `menus` VALUES (2012, 'SUPPLIER', 2009, ' 不报名', '', '/admin/supplier/project/unsignup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-17 15:42:44', 136, '2024-07-17 15:42:44', 136, 'N', 1038, ' 不报名', '2008,2009', '68,1019,1025,1038', NULL);
INSERT INTO `menus` VALUES (2013, 'SUPPLIER', 2009, '招标详情', '', '/supplierTenders/entryDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-17 15:44:03', 136, '2024-07-17 15:44:03', 136, 'N', 1036, '招标详情', '2008,2009', '68,1019,1025,1036', NULL);
INSERT INTO `menus` VALUES (2014, 'SUPPLIER', 2008, '我的投标', 'https://file5.erui.com/group1/M00/00/C2/rBGYHGa1zdyAVV7MAAAC9hMwwFs427.png', '/supplierTenders/myBid', 'MENU', 0, 0, 'NORMAL', '', '2024-07-19 16:53:18', 136, '2024-08-09 16:05:49', 1875271515914638372, 'N', 1025, '我的投标', '2008', '68,1019,1025', NULL);
INSERT INTO `menus` VALUES (2020, 'PLATFORM', 2005, '编辑发标', '', '/admin/project/publish/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-25 09:41:54', 136, '2024-07-25 09:41:54', 136, 'N', 1029, '编辑发标', '2004,2005', '1001,1004,1029', NULL);
INSERT INTO `menus` VALUES (2022, 'PLATFORM', 2005, '编辑标书编制', '', '/admin/project/doc/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-07-31 08:42:00', 136, '2024-07-31 08:42:00', 136, 'N', 1027, '编辑标书编制', '2004,2005', '1001,1004,1027', NULL);
INSERT INTO `menus` VALUES (2026, 'PLATFORM', 2025, '收取保证金', '', '/admin/project/pays/audit/{id}', 'MENU', 0, 0, 'DISABLED', '', '2024-08-01 13:50:58', 136, '2024-08-01 13:51:22', 136, 'Y', 1114, '收取保证金', '2004,2006,2025', '1001,1002,1114', NULL);
INSERT INTO `menus` VALUES (2034, 'PLATFORM', 2004, '定标列表', 'https://file5.erui.com/group1/M00/00/C2/rBGYHGa1zbeAYfIfAAAD38AZV6E294.png', '/inviteTenders/InvitePicketageList', 'MENU', 0, 1, 'NORMAL', '', '2024-08-05 15:23:45', 136, '2024-08-27 11:12:17', 1875271515914638372, 'N', 1111, '定标列表', '2004', '1001,1111', NULL);
INSERT INTO `menus` VALUES (2035, 'PLATFORM', 2005, '招标编码', '', '/admin/project/number', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 11:08:33', 136, '2024-08-08 11:08:33', 136, 'N', 1121, '招标编码', '2004,2005', '1001,1004,1121', NULL);
INSERT INTO `menus` VALUES (2036, 'PLATFORM', 2005, '招标作废', '', '/admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 11:12:01', 136, '2024-08-08 11:12:01', 136, 'N', 1009, '招标作废', '2004,2005', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2037, 'PLATFORM', 2005, '删除招标', '', '/admin/project/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 11:12:33', 136, '2024-08-08 11:12:33', 136, 'N', 1006, '删除招标', '2004,2005', '1001,1004,1006', NULL);
INSERT INTO `menus` VALUES (2038, 'PLATFORM', 2005, '立项调整', '', '/admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 11:13:04', 136, '2024-08-08 11:13:04', 136, 'N', 1011, '立项调整', '2004,2005', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2039, 'PLATFORM', 2005, '新增招标', '', '/admin/project/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 11:13:30', 136, '2024-08-08 11:13:30', 136, 'N', 1007, '新增招标', '2004,2005', '1001,1004,1007', NULL);
INSERT INTO `menus` VALUES (2040, 'SUPPLIER', 2013, '报名', '', '/admin/supplier/project/signup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 15:34:30', 136, '2024-08-08 15:34:30', 136, 'N', 1037, '报名', '2008,2009,2013', '68,1019,1025,1037', NULL);
INSERT INTO `menus` VALUES (2041, 'SUPPLIER', 2013, ' 不报名', '', '/admin/supplier/project/unsignup/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 15:35:12', 136, '2024-08-08 15:35:12', 136, 'N', 1038, ' 不报名', '2008,2009,2013', '68,1019,1025,1038', NULL);
INSERT INTO `menus` VALUES (2046, 'SUPPLIER', 2014, '下载招标文件', '', '/admin/supplier/project/publishDownload/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 15:42:38', 136, '2024-08-08 15:42:38', 136, 'N', 1118, '下载招标文件', '2008,2014', '68,1019,1025,1118', NULL);
INSERT INTO `menus` VALUES (2047, 'SUPPLIER', 2014, '新增报价', '', '/admin/supplier/project/quote/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 15:44:00', 136, '2024-08-08 15:44:00', 136, 'N', 1088, '新增报价', '2008,2014', '68,1019,1025,1088', NULL);
INSERT INTO `menus` VALUES (2048, 'SUPPLIER', 2014, '招标详情', '', '/supplierTenders/bidDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 15:45:17', 136, '2024-08-08 15:50:16', 136, 'N', 1036, '招标详情', '2008,2014', '68,1019,1025,1036', NULL);
INSERT INTO `menus` VALUES (2050, 'SUPPLIER', 2048, '新增报价', '', '/admin/supplier/project/quote/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 15:52:59', 136, '2024-08-08 15:52:59', 136, 'N', 1088, '新增报价', '2008,2014,2048', '68,1019,1025,1088', NULL);
INSERT INTO `menus` VALUES (2051, 'PLATFORM', 2005, '开标详情', '', '/inviteTenders/BidOpeningDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 16:26:41', 136, '2024-08-16 13:56:43', 136, 'N', 1030, '开标详情', '2004,2005', '1001,1004,1030', NULL);
INSERT INTO `menus` VALUES (2052, 'PLATFORM', 2005, '编辑开标', '', '/admin/project/open/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 16:27:00', 136, '2024-08-08 16:27:00', 136, 'N', 1031, '编辑开标', '2004,2005', '1001,1004,1031', NULL);
INSERT INTO `menus` VALUES (2053, 'PLATFORM', 2005, '标书编制详情', '', '/inviteTenders/TenderPreparationDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 16:32:33', 136, '2024-08-16 13:56:02', 136, 'N', 1026, '标书编制详情', '2004,2005', '1001,1004,1026', NULL);
INSERT INTO `menus` VALUES (2054, 'PLATFORM', 2005, '编辑标书编制', '', '/admin/project/doc/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 16:32:54', 136, '2024-08-08 16:32:54', 136, 'N', 1027, '编辑标书编制', '2004,2005', '1001,1004,1027', NULL);
INSERT INTO `menus` VALUES (2055, 'SUPPLIER', 2048, '投标详情', '', '/admin/supplier/project/quote/info/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 16:57:15', 136, '2024-08-08 16:57:15', 136, 'N', 1090, '投标详情', '2008,2014,2048', '68,1019,1025,1090', NULL);
INSERT INTO `menus` VALUES (2057, 'SUPPLIER', 2048, '下载招标文件', '', '/admin/supplier/project/publishDownload/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 17:02:49', 136, '2024-08-08 17:02:49', 136, 'N', 1118, '下载招标文件', '2008,2014,2048', '68,1019,1025,1118', NULL);
INSERT INTO `menus` VALUES (2058, 'PLATFORM', 2034, '删除', '', '/admin/project/decision/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:32:11', 136, '2024-08-08 18:32:11', 136, 'N', 1120, '删除', '2004,2034', '1001,1111,1120', NULL);
INSERT INTO `menus` VALUES (2059, 'PLATFORM', 2005, '编辑招标', '', '/admin/project/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1008, '编辑招标', '2004,2005', '1001,1004,1008', NULL);
INSERT INTO `menus` VALUES (2061, 'PLATFORM', 2005, '物料模板', '', '/admin/project/entry/template', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1012, '物料模板', '2004,2005', '1001,1004,1012', NULL);
INSERT INTO `menus` VALUES (2062, 'PLATFORM', 2005, '物料引入', '', '/admin/project/entry/import', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1013, '物料引入', '2004,2005', '1001,1004,1013', NULL);
INSERT INTO `menus` VALUES (2063, 'PLATFORM', 2005, '标书编制', '', '/admin/project/doc/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-16 16:14:46', 136, 'N', 1015, '标书编制', '2004,2005', '1001,1004,1015', NULL);
INSERT INTO `menus` VALUES (2064, 'PLATFORM', 2005, '招标入围', '', '/admin/project/shortlist/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1024, '招标入围', '2004,2005', '1001,1004,1024', NULL);
INSERT INTO `menus` VALUES (2065, 'PLATFORM', 2005, '发标详情', '', '/inviteTenders/InvitationIssuingDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-09-05 18:29:40', 1875271515914638372, 'N', 1028, '发标详情', '2004,2005', '1001,1004,1028', NULL);
INSERT INTO `menus` VALUES (2066, 'PLATFORM', 2005, '评标详情', '', '/inviteTenders/EvaluationOfBidDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-16 13:55:17', 136, 'N', 1032, '评标详情', '2004,2005', '1001,1004,1032', NULL);
INSERT INTO `menus` VALUES (2067, 'PLATFORM', 2005, '编辑评标', '', '/admin/project/evaluation/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1033, '编辑评标', '2004,2005', '1001,1004,1033', NULL);
INSERT INTO `menus` VALUES (2068, 'PLATFORM', 2005, '定标详情', '', '/admin/project/decision/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1034, '定标详情', '2004,2005', '1001,1004,1034', NULL);
INSERT INTO `menus` VALUES (2069, 'PLATFORM', 2005, '编辑定标', '', '/admin/project/decision/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1035, '编辑定标', '2004,2005', '1001,1004,1035', NULL);
INSERT INTO `menus` VALUES (2070, 'PLATFORM', 2005, '增补供应商', '', '/admin/project/addshortlist/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1100, '增补供应商', '2004,2005', '1001,1004,1100', NULL);
INSERT INTO `menus` VALUES (2071, 'PLATFORM', 2005, '发标编辑', '', '/admin/project/publisih/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1113, '发标编辑', '2004,2005', '1001,1004,1113', NULL);
INSERT INTO `menus` VALUES (2072, 'PLATFORM', 2005, '评标信息', '', '/admin/project/evaluation/list/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1117, '评标信息', '2004,2005', '1001,1004,1117', NULL);
INSERT INTO `menus` VALUES (2073, 'PLATFORM', 2005, '作废记录', '', '/admin/project/invalid/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1119, '作废记录', '2004,2005', '1001,1004,1119', NULL);
INSERT INTO `menus` VALUES (2090, 'PLATFORM', 2030, '详情', '', '/admin/purproject/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1048, '详情', '2015,2030', '1001,1079,1047,1048', NULL);
INSERT INTO `menus` VALUES (2091, 'PLATFORM', 2030, '新增', '', '/admin/purproject/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1049, '新增', '2015,2030', '1001,1079,1047,1049', NULL);
INSERT INTO `menus` VALUES (2092, 'PLATFORM', 2030, '编码', '', '/admin/purproject/number', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1050, '编码', '2015,2030', '1001,1079,1047,1050', NULL);
INSERT INTO `menus` VALUES (2093, 'PLATFORM', 2030, '编辑', '', '/admin/purproject/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1051, '编辑', '2015,2030', '1001,1079,1047,1051', NULL);
INSERT INTO `menus` VALUES (2094, 'PLATFORM', 2030, '删除', '', '/admin/purproject/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1052, '删除', '2015,2030', '1001,1079,1047,1052', NULL);
INSERT INTO `menus` VALUES (2095, 'PLATFORM', 2030, '禁用', '', '/admin/purproject/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1053, '禁用', '2015,2030', '1001,1079,1047,1053', NULL);
INSERT INTO `menus` VALUES (2096, 'PLATFORM', 2030, '启用', '', '/admin/purproject/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1054, '启用', '2015,2030', '1001,1079,1047,1054', NULL);
INSERT INTO `menus` VALUES (2097, 'PLATFORM', 2031, '详情', '', '/admin/purtype/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1056, '详情', '2015,2031', '1001,1079,1055,1056', NULL);
INSERT INTO `menus` VALUES (2098, 'PLATFORM', 2031, '新增', '', '/admin/purtype/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1057, '新增', '2015,2031', '1001,1079,1055,1057', NULL);
INSERT INTO `menus` VALUES (2099, 'PLATFORM', 2031, '编辑', '', '/admin/purtype/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1059, '编辑', '2015,2031', '1001,1079,1055,1059', NULL);
INSERT INTO `menus` VALUES (2100, 'PLATFORM', 2031, '删除', '', '/admin/purtype/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1060, '删除', '2015,2031', '1001,1079,1055,1060', NULL);
INSERT INTO `menus` VALUES (2101, 'PLATFORM', 2031, '禁用', '', '/admin/purtype/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1061, '禁用', '2015,2031', '1001,1079,1055,1061', NULL);
INSERT INTO `menus` VALUES (2102, 'PLATFORM', 2031, '启用', '', '/admin/purtype/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1062, '启用', '2015,2031', '1001,1079,1055,1062', NULL);
INSERT INTO `menus` VALUES (2103, 'PLATFORM', 2032, '详情', '', '/admin/bidmode/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1064, '详情', '2015,2032', '1001,1079,1063,1064', NULL);
INSERT INTO `menus` VALUES (2104, 'PLATFORM', 2032, '新增', '', '/admin/bidmode/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1065, '新增', '2015,2032', '1001,1079,1063,1065', NULL);
INSERT INTO `menus` VALUES (2105, 'PLATFORM', 2032, '编辑', '', '/admin/bidmode/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1067, '编辑', '2015,2032', '1001,1079,1063,1067', NULL);
INSERT INTO `menus` VALUES (2106, 'PLATFORM', 2032, '删除', '', '/admin/bidmode/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1068, '删除', '2015,2032', '1001,1079,1063,1068', NULL);
INSERT INTO `menus` VALUES (2107, 'PLATFORM', 2032, '禁用', '', '/admin/bidmode/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1069, '禁用', '2015,2032', '1001,1079,1063,1069', NULL);
INSERT INTO `menus` VALUES (2108, 'PLATFORM', 2032, '启用', '', '/admin/bidmode/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1070, '启用', '2015,2032', '1001,1079,1063,1070', NULL);
INSERT INTO `menus` VALUES (2109, 'PLATFORM', 2033, '详情', '', '/admin/valuationmode/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1072, '详情', '2015,2033', '1001,1079,1071,1072', NULL);
INSERT INTO `menus` VALUES (2110, 'PLATFORM', 2033, '新增', '', '/admin/valuationmode/add', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1073, '新增', '2015,2033', '1001,1079,1071,1073', NULL);
INSERT INTO `menus` VALUES (2111, 'PLATFORM', 2033, '编辑', '', '/admin/valuationmode/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1075, '编辑', '2015,2033', '1001,1079,1071,1075', NULL);
INSERT INTO `menus` VALUES (2112, 'PLATFORM', 2033, '删除', '', '/admin/valuationmode/delete', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1076, '删除', '2015,2033', '1001,1079,1071,1076', NULL);
INSERT INTO `menus` VALUES (2113, 'PLATFORM', 2033, '禁用', '', '/admin/valuationmode/disable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1077, '禁用', '2015,2033', '1001,1079,1071,1077', NULL);
INSERT INTO `menus` VALUES (2114, 'PLATFORM', 2033, '启用', '', '/admin/valuationmode/enable', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-08 18:38:21', 136, '2024-08-08 18:38:21', 136, 'N', 1078, '启用', '2015,2033', '1001,1079,1071,1078', NULL);
INSERT INTO `menus` VALUES (2115, 'SUPPLIER', 2048, '投标修改', '', ' /admin/supplier/project/quote/edited/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-09 16:35:16', 136, '2024-08-09 16:35:16', 136, 'N', 1089, '投标修改', '2008,2014,2048', '68,1019,1025,1089', NULL);
INSERT INTO `menus` VALUES (2116, 'PLATFORM', 2034, '招标作废', '', ' /admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-12 11:00:20', 1875271515914638372, '2024-08-12 11:00:20', 1875271515914638372, 'N', 1009, '招标作废', '2004,2034', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2117, 'PLATFORM', 2065, '招标作废', '', '/admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:27:44', 136, '2024-08-16 13:55:04', 136, 'N', 1009, '招标作废', '2004,2005,2065', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2118, 'PLATFORM', 2063, '招标作废', '', '/admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:28:43', 136, '2024-08-16 13:54:08', 136, 'N', 1009, '招标作废', '2004,2005,2063', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2119, 'PLATFORM', 2053, '招标作废', '', ' /admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:29:18', 136, '2024-08-16 13:56:28', 136, 'N', 1009, '招标作废', '2004,2005,2053', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2120, 'PLATFORM', 2007, '招标作废', '', ' /admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:30:06', 136, '2024-08-16 13:59:02', 136, 'N', 1009, '招标作废', '2004,2005,2007', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2121, 'PLATFORM', 2066, '招标作废', '', ' /admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:30:40', 136, '2024-08-16 13:55:45', 136, 'N', 1009, '招标作废', '2004,2005,2066', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2122, 'PLATFORM', 2051, '招标作废', '', ' /admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:31:18', 136, '2024-08-16 13:57:13', 136, 'N', 1009, '招标作废', '2004,2005,2051', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2123, 'PLATFORM', 2007, '立项调整', '', ' /admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:32:30', 136, '2024-08-16 13:58:50', 136, 'N', 1011, '立项调整', '2004,2005,2007', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2124, 'PLATFORM', 2063, '立项调整', '', '/admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:33:46', 136, '2024-08-16 13:53:52', 136, 'N', 1011, '立项调整', '2004,2005,2063', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2125, 'PLATFORM', 2065, '立项调整', '', ' /admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:34:25', 136, '2024-08-16 13:54:40', 136, 'N', 1011, '立项调整', '2004,2005,2065', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2126, 'PLATFORM', 2066, '立项调整', '', '/admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:34:56', 136, '2024-08-16 13:55:30', 136, 'N', 1011, '立项调整', '2004,2005,2066', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2127, 'PLATFORM', 2053, '立项调整', '', ' /admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:35:29', 136, '2024-08-16 13:56:14', 136, 'N', 1011, '立项调整', '2004,2005,2053', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2128, 'PLATFORM', 2051, '立项调整', '', ' /admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 11:35:58', 136, '2024-08-16 13:56:57', 136, 'N', 1011, '立项调整', '2004,2005,2051', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2129, 'PLATFORM', 2005, '招标详情', '', '/inviteTenders/ProjectApprovalDetails', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 14:15:48', 136, '2024-08-16 14:15:48', 136, 'N', 1005, '招标详情', '2004,2005', '1001,1004,1005', NULL);
INSERT INTO `menus` VALUES (2130, 'PLATFORM', 2129, '招标作废', '', ' /admin/admin/project/invalid', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 14:16:18', 136, '2024-08-16 14:16:18', 136, 'N', 1009, '招标作废', '2004,2005,2129', '1001,1004,1009', NULL);
INSERT INTO `menus` VALUES (2131, 'PLATFORM', 2129, '立项调整', '', ' /admin/project/change/{id}', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-16 14:16:35', 136, '2024-08-16 14:16:35', 136, 'N', 1011, '立项调整', '2004,2005,2129', '1001,1004,1011', NULL);
INSERT INTO `menus` VALUES (2132, 'PLATFORM', 2005, '计价模式', '', ' /admin/valuationmodes', 'BUTTON', 0, 0, 'DISABLED', '', '2024-08-20 08:40:06', 136, '2024-08-20 10:06:59', 1875271515914638372, 'N', 1122, '计价模式', '2004,2005', '1001,1079,1071,1122', NULL);
INSERT INTO `menus` VALUES (2133, 'PLATFORM', 2005, '采购类型', '', '/admin/purtypes', 'MENU', 0, 0, 'DISABLED', '', '2024-08-20 08:49:36', 136, '2024-08-20 10:06:51', 1875271515914638372, 'N', 1123, '采购类型', '2004,2005', '1001,1004,1123', NULL);
INSERT INTO `menus` VALUES (2135, 'PLATFORM', 2005, '招标专家', '', '/admin/project/proficients', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-20 17:57:57', 136, '2024-08-20 17:57:57', 136, 'N', 1017, '招标专家', '2004,2005', '1001,1004,1017', NULL);
INSERT INTO `menus` VALUES (2137, 'PLATFORM', 2005, '一级采购商', '', ' /admin/orgs', 'BUTTON', 0, 0, 'NORMAL', '', '2024-08-21 09:33:47', 136, '2024-08-21 09:33:47', 136, 'N', 1126, '一级采购商', '2004,2005', '1001,1079,1080,1126', NULL);

-- ----------------------------
-- Table structure for message
-- ----------------------------
DROP TABLE IF EXISTS `message`;
CREATE TABLE `message`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `receiver_type` enum('PLATFORM','PURCHASER','SUPPLIER') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'SUPPLIER' COMMENT '接收人类型。PLATFORM:平台 PURCHASER:采购商,SUPPLIER 供应商',
  `content_operate` enum('INQUIRY_AUDIT','BIDDING_AUDIT','SUPPLIER_AUDIT','COMPARE_AUDIT','QUOTE_AUDIT','SUPPLIER_CHANGE','SUPPLIER_ACCESS','BIDDING_PAY','TENDERING_PAY') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作类型\r\nSUPPLIER_AUDIT:供应商准入审核\r\nSUPPLIER_CHANGE:供应商变更审核\r\nSUPPLIER_ACCESS:供应商准入审核\r\nBIDDING_AUDIT:竞价资审\r\nBIDDING_PAY:竞价缴纳保证金\r\n',
  `content_id` bigint(20) NULL DEFAULT NULL COMMENT '关联操作ID',
  `content_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `sender_id` bigint(20) NULL DEFAULT NULL COMMENT '采购商ID',
  `message_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息编号',
  `message_type` enum('SYSTEM','PURCHASER','PLATFORM') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'SYSTEM' COMMENT '消息类型SYSTEM系统发送,PURCHASER采购商发送,Subscribe订阅商机, PLATFORM:平台消息',
  `message_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息标题',
  `message` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '消息内容',
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '状态NOT_SENT：未发送；HAS_SENT：已发送；SUCCESS：成功；FAIL：失败',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_at` datetime(0) NULL DEFAULT NULL COMMENT '删除时间',
  `deleted_flag` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_sender_id`(`sender_id`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `content_id`(`content_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '消息管理' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of message
-- ----------------------------

-- ----------------------------
-- Table structure for message_receiver
-- ----------------------------
DROP TABLE IF EXISTS `message_receiver`;
CREATE TABLE `message_receiver`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `message_id` bigint(20) NOT NULL COMMENT '消息编号',
  `receiver_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '接收方ID',
  `supplier_id` bigint(20) NULL DEFAULT NULL COMMENT '接收供应商ID',
  `org_id` bigint(20) NULL DEFAULT NULL COMMENT '组织',
  `read_flag` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT '用户是否读取',
  `created_by` bigint(20) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_at` datetime(0) NULL DEFAULT NULL COMMENT '删除时间',
  `deleted_flag` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_message_id`(`message_id`) USING BTREE,
  INDEX `idx_receiver_id`(`receiver_id`) USING BTREE,
  INDEX `supplier_id`(`supplier_id`) USING BTREE,
  INDEX `idx_org_id`(`org_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '消息接收者' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of message_receiver
-- ----------------------------

-- ----------------------------
-- Table structure for notice
-- ----------------------------
DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '单据编号',
  `bill_date` datetime(0) NULL DEFAULT NULL COMMENT '发布时间',
  `due_date` datetime(0) NULL DEFAULT NULL COMMENT '到期时间',
  `biz_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '公告类型 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告',
  `important` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '重要性',
  `urgent` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '紧急度',
  `sup_scope` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '公告范围',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容',
  `content_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容_详情',
  `org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '发布组织',
  `bill_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '发布状态 A 保存 B 已提交 C已审核',
  `cfm_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '确认状态',
  `biz_partner_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '商务伙伴',
  `bill_type_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '源单类型',
  `is_top` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '置顶',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '主题',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '备注',
  `notice_tpl_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '公告模板',
  `updated_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_NOTICE_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `IDX_PUR_NOTICE_FBILLDATE`(`bill_date`) USING BTREE,
  INDEX `biz_type`(`biz_type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标公告组件-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notice
-- ----------------------------

-- ----------------------------
-- Table structure for notice_attach
-- ----------------------------
DROP TABLE IF EXISTS `notice_attach`;
CREATE TABLE `notice_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `notice_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '公告ID',
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`, `notice_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标公告附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notice_attach
-- ----------------------------

-- ----------------------------
-- Table structure for notice_sub
-- ----------------------------
DROP TABLE IF EXISTS `notice_sub`;
CREATE TABLE `notice_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `notice_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '公告ID',
  `creator_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `auditor_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '审核人',
  `audit_date` datetime(0) NULL DEFAULT NULL COMMENT '审核时间',
  `cfm_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '确认人',
  `cfm_date` datetime(0) NULL DEFAULT NULL COMMENT '确认时间',
  `src_bill_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '源单ID',
  `src_bill_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '源单单号',
  `src_bill_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '源单类型',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '主题',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '备注',
  `parent_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '父单据ID',
  `pentity_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '父单据标识',
  `entity_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '组件标识',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `notice_id`(`notice_id`) USING BTREE,
  INDEX `IDX_create_time`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标公告-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notice_sub
-- ----------------------------

-- ----------------------------
-- Table structure for notice_supplier
-- ----------------------------
DROP TABLE IF EXISTS `notice_supplier`;
CREATE TABLE `notice_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `notice_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '公告ID',
  `seq` bigint(20) NOT NULL DEFAULT 0,
  `supplier_id` bigint(20) NOT NULL DEFAULT 0,
  `contacter` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '联系人',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '手机',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '邮箱',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `checked_at` datetime(0) NULL DEFAULT NULL COMMENT '审核时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `read_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '用户是否读取',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_notice_id`(`notice_id`) USING BTREE,
  INDEX `IDX_FSUPPLIERID`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商公告读取情况' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notice_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for notice_tpl
-- ----------------------------
DROP TABLE IF EXISTS `notice_tpl`;
CREATE TABLE `notice_tpl`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `creator_id` bigint(20) NOT NULL DEFAULT 0,
  `modifier_id` bigint(20) NOT NULL DEFAULT 0,
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `master_id` bigint(20) NOT NULL DEFAULT 0,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `content_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `is_sys_preset` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `biz_object` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `comp_biz_object` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `notice_content_plugin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `is_default` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `auditor_id` bigint(20) NOT NULL DEFAULT 0,
  `audit_date` datetime(0) NULL DEFAULT NULL,
  `updated_by` bigint(20) NOT NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PDS_NOTICETPL_BIZ`(`biz_object`) USING BTREE,
  INDEX `IDX_PDS_NOTICETPL_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '公告模板-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notice_tpl
-- ----------------------------

-- ----------------------------
-- Table structure for notice_user
-- ----------------------------
DROP TABLE IF EXISTS `notice_user`;
CREATE TABLE `notice_user`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `notice_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '公告ID',
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `read_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '用户是否读取',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_notice_id`(`notice_id`) USING BTREE,
  INDEX `IDX_FSUPPLIERID`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商公告读取情况' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notice_user
-- ----------------------------

-- ----------------------------
-- Table structure for paycond
-- ----------------------------
DROP TABLE IF EXISTS `paycond`;
CREATE TABLE `paycond`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '编码',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '数据状态',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '使用状态',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '描述',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `disabled_at` datetime(0) NULL DEFAULT NULL,
  `disabled_by` bigint(20) NOT NULL DEFAULT 1,
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_PUR_PAYCOND_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '付款条件-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of paycond
-- ----------------------------

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_type` enum('COMMON','PLATFORM','SUPPLIER') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'PLATFORM' COMMENT '权限类型 PURCHASER:采购商 COMMON 公共权限 SUPPLIER 供应商菜单',
  `parent_id` bigint(20) NULL DEFAULT 0 COMMENT '上级功能ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '功能名称',
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '展示名称',
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '路由',
  `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '方法',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'MENU' COMMENT '功能类型\r\nBUTTON:按钮\r\nMENU:菜单\r\nIF:接口\r\nPAGE:页面',
  `weight` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '权重',
  `is_share` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否公共开放接口\r\nY:是公共接口，不需要鉴权\r\nN:不是公共接口，需要鉴权',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'NORMAL' COMMENT '状态,NORMAL-正常;DISABLED-停用',
  `shortcut_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否快捷按钮，Y：是 N：否',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `updated_by` bigint(20) NULL DEFAULT NULL,
  `parent_tree` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `permission_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1127 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES (5, 'COMMON', 0, '权限管理', '权限管理', '/permissions', '/permissions', 'MENU', 0, 'Y', 'NORMAL', '', 2, '2024-04-29 16:19:57', 'N', 136, '2024-02-01 17:04:27', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (6, 'COMMON', 5, '权限配置', '权限配置', '/admin/permissions', 'Permissions/getList', 'MENU', 0, 'Y', 'NORMAL', '', 1, '2024-05-28 14:46:14', 'N', 136, '2024-02-01 17:12:33', NULL, NULL, 136, '5', NULL);
INSERT INTO `permissions` VALUES (23, 'COMMON', 5, '菜单管理', '菜单管理', '/admin/menus', 'Menus/getList', 'MENU', 0, 'Y', 'NORMAL', '', 3, '2024-04-12 13:50:39', 'N', 136, '2024-02-28 11:19:24', NULL, NULL, 136, '5', NULL);
INSERT INTO `permissions` VALUES (24, 'COMMON', 5, '角色管理', '角色管理', '/admin/roles', 'Roles/getList', 'MENU', 0, 'Y', 'NORMAL', '', 3, '2024-08-19 16:15:08', 'N', 136, '2024-02-28 11:25:20', NULL, NULL, 1181715960860513280, '5', NULL);
INSERT INTO `permissions` VALUES (25, 'PLATFORM', 6, '权限详情', '权限详情', '/admin/permissions/{id}', 'Permissions/info', 'IF', 0, 'Y', 'NORMAL', '', 0, '2024-03-07 08:52:13', 'N', 136, '2024-02-28 11:41:42', NULL, NULL, 1533256682778396672, '5,6', NULL);
INSERT INTO `permissions` VALUES (26, 'PLATFORM', 6, '删除权限', '删除权限', '/admin/permissions/delete', 'Permissions/delete', 'IF', 0, 'Y', 'NORMAL', '', 1, '2024-04-12 13:51:01', 'N', 136, '2024-02-28 11:42:03', NULL, NULL, 136, '5,6', NULL);
INSERT INTO `permissions` VALUES (27, 'PLATFORM', 6, '新增权限', '新增权限', '/admin/permissions/add', 'Permissions/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-12 13:50:28', 'N', 136, '2024-02-28 11:44:31', NULL, NULL, 136, '5,6', NULL);
INSERT INTO `permissions` VALUES (28, 'PLATFORM', 6, '修改权限', '修改权限', '/admin/permissions/edited/{id}', 'Permissions/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 2, '2024-04-22 13:23:45', 'N', 136, '2024-02-28 11:45:05', NULL, NULL, 136, '5,6', NULL);
INSERT INTO `permissions` VALUES (30, 'PLATFORM', 0, '基础服务', '基础服务', '/base', '/base', 'MENU', 0, 'Y', 'NORMAL', '', 3, '2024-03-06 09:03:01', 'N', 136, '2024-03-05 16:04:59', NULL, NULL, 240, NULL, NULL);
INSERT INTO `permissions` VALUES (31, 'PLATFORM', 30, '采购商管理', '采购商管理', '/admin/purchaser/list', 'Purchaser/getList', 'MENU', 0, 'Y', 'NORMAL', '', 5, '2024-04-22 15:28:06', 'N', 136, '2024-03-05 16:05:44', NULL, NULL, 136, '30', NULL);
INSERT INTO `permissions` VALUES (32, 'PLATFORM', 30, '组织机构', '组织机构', '/admin/org/list', 'Org/getList', 'MENU', 0, 'Y', 'NORMAL', '', 1, '2024-04-22 15:26:54', 'N', 136, '2024-03-05 16:06:51', NULL, NULL, 136, '30', NULL);
INSERT INTO `permissions` VALUES (33, 'PLATFORM', 30, '人员管理', '人员管理', '/admin/user', 'User/getList', 'MENU', 0, 'Y', 'NORMAL', '', 4, '2024-04-12 11:40:02', 'N', 136, '2024-03-05 16:07:15', NULL, NULL, 136, '30', NULL);
INSERT INTO `permissions` VALUES (43, 'PLATFORM', 31, '新增采购商', '新增采购商', '/admin/purchaser/add', 'Purchaser/add', 'IF', 0, 'Y', 'NORMAL', '', 2, '2024-03-06 13:59:17', 'N', 1201230216341488640, '2024-03-06 13:59:17', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (44, 'PLATFORM', 31, '编辑采购商', '编辑采购商', '/admin/purchaser/edited/{id}', 'Purchaser/edited', 'IF', 0, 'Y', 'NORMAL', '', 3, '2024-03-06 13:59:43', 'N', 1201230216341488640, '2024-03-06 13:59:43', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (45, 'PLATFORM', 31, '解冻采购商', '解冻采购商', '/admin/purchaser/enable', 'Purchaser/enable', 'IF', 0, 'Y', 'NORMAL', '', 4, '2024-03-06 14:00:28', 'N', 1201230216341488640, '2024-03-06 14:00:28', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (46, 'PLATFORM', 31, '冻结采购商', '冻结采购商', '/admin/purchaser/disable', 'Purchaser/disable', 'IF', 0, 'Y', 'NORMAL', '', 5, '2024-03-06 14:01:00', 'N', 1201230216341488640, '2024-03-06 14:01:00', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (47, 'PLATFORM', 31, '删除采购商', '删除采购商', '/admin/purchaser/delete', 'Purchaser/delete', 'IF', 0, 'Y', 'NORMAL', '', 6, '2024-03-06 14:01:23', 'N', 1201230216341488640, '2024-03-06 14:01:23', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (49, 'PLATFORM', 31, '采购商导入', '采购商导入', '/admin/purchaser/import', 'Purchaser/import', 'IF', 0, 'Y', 'NORMAL', '', 8, '2024-03-06 14:02:17', 'N', 1201230216341488640, '2024-03-06 14:02:17', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (50, 'PLATFORM', 31, '采购商详情', '采购商详情', '/admin/purchaser/{id}', 'Purchaser/info', 'IF', 0, 'Y', 'NORMAL', '', 9, '2024-03-06 14:02:47', 'N', 1201230216341488640, '2024-03-06 14:02:47', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (51, 'PLATFORM', 31, '获取采购商编码', '获取采购商编码', '/admin/purchaser/number', 'Org/number', 'IF', 0, 'Y', 'NORMAL', '', 10, '2024-03-06 14:03:12', 'N', 1201230216341488640, '2024-03-06 14:03:12', NULL, NULL, 1201230216341488640, '30,31', NULL);
INSERT INTO `permissions` VALUES (55, 'COMMON', 0, '基础数据', '基础数据', '/basic', '/basic', 'MENU', 0, 'Y', 'NORMAL', '', 1, '2024-04-29 16:20:08', 'N', 136, '2024-03-07 09:53:15', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (56, 'PLATFORM', 55, '计量单位', '计量单位', '/admin/unit', 'Unit/getList', 'MENU', 0, 'N', 'NORMAL', '', 0, '2024-03-07 09:53:56', 'N', 136, '2024-03-07 09:53:56', NULL, NULL, 136, '55', NULL);
INSERT INTO `permissions` VALUES (57, 'COMMON', 55, '币种管理', '币种管理', '/admin/currencys', 'Currency/currencys', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-05-10 10:20:59', 'N', 136, '2024-03-08 10:16:02', NULL, NULL, 136, '55', NULL);
INSERT INTO `permissions` VALUES (58, 'PLATFORM', 55, '银行管理', '银行管理', '/admin/bank', 'Bank/getList', 'MENU', 0, 'N', 'NORMAL', '', 0, '2024-03-08 10:34:27', 'N', 136, '2024-03-08 10:34:27', NULL, NULL, 136, '55', NULL);
INSERT INTO `permissions` VALUES (59, 'SUPPLIER', 0, '准入协同', '准入协同', '/supplierClient', '/supplierClient', 'MENU', 0, 'Y', 'NORMAL', '', 5, '2024-05-13 15:47:24', 'N', 136, '2024-03-08 15:44:37', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (60, 'PLATFORM', 59, '注册管理', '注册管理', '/admin/supplier/manage', 'Supplier/manage', 'MENU', 0, 'N', 'NORMAL', '', 2, '2024-03-16 10:03:17', 'N', 136, '2024-03-08 15:52:19', NULL, NULL, 136, '59', NULL);
INSERT INTO `permissions` VALUES (61, 'PLATFORM', 55, '结算方式', '结算方式', '/admin/settlementtype', 'SettleMentType/getList', 'MENU', 0, 'N', 'NORMAL', '', 0, '2024-03-11 09:34:28', 'N', 136, '2024-03-11 09:34:28', NULL, NULL, 136, '55', NULL);
INSERT INTO `permissions` VALUES (62, 'PLATFORM', 55, '付款条件', '付款条件', '/admin/paycond', 'Paycond/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-03-12 09:48:25', 'N', 136, '2024-03-12 09:48:25', NULL, NULL, 136, '55', NULL);
INSERT INTO `permissions` VALUES (63, 'PLATFORM', 0, '询比价管理', '询比价管理', '/inquiryRate', '/inquiryRate', 'MENU', 0, 'Y', 'NORMAL', '', 7, '2024-04-08 10:40:46', 'N', 136, '2024-03-14 14:50:48', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (64, 'PLATFORM', 63, '询价单', '询价单', '/admin/inquiry', 'Inquiry/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-12 11:45:58', 'N', 136, '2024-03-14 14:51:14', NULL, NULL, 136, '63', NULL);
INSERT INTO `permissions` VALUES (65, 'PLATFORM', 0, '供应商管理', '供应商管理', '/supplierManage', 'supplierManage', 'MENU', 0, 'Y', 'NORMAL', '', 8, '2024-04-25 14:01:49', 'N', 136, '2024-03-15 17:02:54', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (66, 'PLATFORM', 65, '注册管理', '注册管理', '/admin/supplier/register', 'SupplierRegister/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-22 14:53:36', 'N', 136, '2024-03-15 17:04:18', NULL, NULL, 136, '65', NULL);
INSERT INTO `permissions` VALUES (67, 'PLATFORM', 65, '供应商库', '供应商库', '/admin/supplier', 'SupplierBase/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-25 11:14:09', 'N', 136, '2024-03-15 17:04:50', NULL, NULL, 136, '65', NULL);
INSERT INTO `permissions` VALUES (68, 'SUPPLIER', 0, '供应商后台', '供应商后台', '/supplierApply/staging', '/supplierApply/staging', 'MENU', 0, 'Y', 'NORMAL', '', 9, '2024-04-08 10:40:54', 'N', 136, '2024-03-15 17:13:01', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (70, 'SUPPLIER', 68, '评审意见', '评审意见', '/supplierApply/review', '/supplierApply/review', 'MENU', 0, 'Y', 'NORMAL', '', 3, '2024-04-09 14:30:17', 'N', 136, '2024-03-15 17:29:54', NULL, NULL, 136, '68', NULL);
INSERT INTO `permissions` VALUES (71, 'SUPPLIER', 68, '企业信息', '企业信息', '/supplierClient/companyInfo', '/supplierClient/companyInfo', 'MENU', 0, 'Y', 'NORMAL', '', 2, '2024-04-09 14:30:10', 'N', 136, '2024-03-15 17:30:26', NULL, NULL, 136, '68', NULL);
INSERT INTO `permissions` VALUES (73, 'SUPPLIER', 0, '供应商工作台', '供应商工作台', '/supplier', 'supplier', 'MENU', 0, 'Y', 'NORMAL', '', 6, '2024-03-19 08:09:13', 'N', 136, '2024-03-19 08:09:03', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (74, 'SUPPLIER', 73, '工作台', '工作台', '/supplier/audit', 'supplier', 'MENU', 0, 'N', 'NORMAL', '', 1, '2024-05-23 09:11:10', 'N', 136, '2024-03-19 08:09:43', NULL, NULL, 1875271515914638372, '73', NULL);
INSERT INTO `permissions` VALUES (75, 'SUPPLIER', 73, '评审意见', '评审意见', '/supplier/audit/history', 'supplier', 'MENU', 0, 'N', 'NORMAL', '', 2, '2024-05-23 09:11:21', 'N', 136, '2024-03-19 08:10:45', NULL, NULL, 1875271515914638372, '73', NULL);
INSERT INTO `permissions` VALUES (76, 'PLATFORM', 64, '新建询单', '新建询单', '/admin/inquiry/add', 'Inquiry/add', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:16:44', 'N', 136, '2024-03-19 14:33:29', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (79, 'PLATFORM', 67, '解冻供应商', '解冻供应商', 'admin/supplier/enable', 'SupplierBase/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-22 14:55:40', 'N', 136, '2024-03-19 14:38:12', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (80, 'PLATFORM', 67, '冻结供应商', '冻结供应商', 'admin/supplier/disable', 'SupplierBase/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-22 14:55:32', 'N', 136, '2024-03-19 14:39:31', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (82, 'SUPPLIER', 68, '报价管理', '报价管理', 'admin/supplier/inquiry', 'Inquiry/getList', 'MENU', 0, 'Y', 'NORMAL', '', 4, '2024-05-08 09:03:39', 'N', 1875271515914638372, '2024-03-19 16:09:29', NULL, NULL, 136, '68', NULL);
INSERT INTO `permissions` VALUES (84, 'PLATFORM', 55, '供应商分级', '供应商分级', '/admin/supplier/evagrade', 'SupplierEvaGrade/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-03-19 17:18:47', 'N', 136, '2024-03-19 17:18:47', NULL, NULL, 136, '55', NULL);
INSERT INTO `permissions` VALUES (85, 'PLATFORM', 67, '供应商审核', '供应商审核', '/admin/supplier/audit/verify', 'SupplierAudit/verify', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-09 10:10:57', 'N', 136, '2024-03-21 19:19:48', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (87, 'PLATFORM', 63, '报价单', '报价单', '/admin/quote', 'Quote/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-03-28 14:43:10', 'N', 136, '2024-03-28 14:43:10', NULL, NULL, 136, '63', NULL);
INSERT INTO `permissions` VALUES (88, 'PLATFORM', 63, '比价单', '比价单', '/admin/compare', 'Compare/getList', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-18 11:06:13', 'N', 136, '2024-03-29 13:59:54', NULL, NULL, 136, '63', NULL);
INSERT INTO `permissions` VALUES (95, 'PLATFORM', 67, '供应商导入', '供应商导入', '/admin/supplier/import', 'SupplierBase/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-22 14:55:20', 'N', 136, '2024-04-02 11:15:25', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (96, 'PLATFORM', 64, '编辑询价', '编辑询价', '/admin/inquiry/edited/{id}', 'Inquiry/edited', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:17:36', 'N', 136, '2024-04-02 11:17:36', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (97, 'PLATFORM', 64, '详情', '详情', '/admin/inquiry/{id}', 'Inquiry/info', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:18:25', 'N', 136, '2024-04-02 11:18:25', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (98, 'PLATFORM', 64, '删除询价', '删除询价', '/admin/inquiry/delete', 'Inquiry/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:19:11', 'N', 136, '2024-04-02 11:19:11', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (99, 'PLATFORM', 64, '复制询价单', '复制询价单', '/admin/inquiry/copy/{id}', 'Inquiry/copy', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:20:26', 'N', 136, '2024-04-02 11:20:26', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (100, 'PLATFORM', 64, '变更时间', '变更时间', '/admin/inquiry/change/{id}', 'Inquiry/change', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:24:06', 'N', 136, '2024-04-02 11:24:06', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (102, 'PLATFORM', 64, '撤销询价', '撤销询价', '/admin/inquiry/revoke/{id}', 'Inquiry/revoke', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:26:04', 'N', 136, '2024-04-02 11:26:04', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (103, 'PLATFORM', 64, '询价终止', '询价终止', '/admin/inquiry/stop/{id}', 'Inquiry/stop', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:26:43', 'N', 136, '2024-04-02 11:26:43', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (104, 'PLATFORM', 64, '询单编码', '询单编码', '/admin/inquiry/number', 'Inquiry/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:27:38', 'N', 136, '2024-04-02 11:27:38', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (105, 'PLATFORM', 64, '多轮报价', '多轮报价', '/admin/inquiry/mulround/{id}', 'Inquiry/mulRound', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:28:14', 'N', 136, '2024-04-02 11:28:14', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (106, 'PLATFORM', 64, '开标', '开标', '/admin/inquiry/opening/{id}', 'Inquiry/opening', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:28:49', 'N', 136, '2024-04-02 11:28:49', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (107, 'PLATFORM', 64, '询价单供应商', '询价单供应商', '/admin/inquiry/supplier/{id}', 'Inquiry/supplier', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:29:34', 'N', 136, '2024-04-02 11:29:34', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (108, 'PLATFORM', 64, '询价单物料引入模板', '询价单物料引入模板', '/admin/inquiry/entry/template', 'Inquiry/entryTemplate', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-02 11:30:28', 'N', 136, '2024-04-02 11:30:28', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (115, 'PLATFORM', 33, '人员采购商组织列表', '人员采购商组织列表', '/admin/user/orglist', 'User/orgList', 'BUTTON', 0, 'N', 'NORMAL', '', 0, '2024-04-07 17:47:16', 'N', 136, '2024-04-07 17:47:16', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (117, 'PLATFORM', 56, '单位编码', '单位编码', '/admin/unit/number', 'Unit/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 2, '2024-04-08 10:41:23', 'N', 136, '2024-04-08 09:54:44', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (118, 'PLATFORM', 56, '删除单位', '删除单位', '/admin/unit/delete', 'Unit/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 3, '2024-04-08 10:41:33', 'N', 136, '2024-04-08 09:55:18', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (119, 'PLATFORM', 56, '启用单位', '启用单位', '/admin/unit/enable', 'Unit/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 4, '2024-04-08 10:41:44', 'N', 136, '2024-04-08 09:55:58', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (120, 'PLATFORM', 56, '禁用单位', '禁用单位', '/admin/unit/disable', 'Unit/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 6, '2024-04-08 10:42:06', 'N', 136, '2024-04-08 09:56:25', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (121, 'PLATFORM', 56, '新增单位', '新增单位', '/admin/unit/add', 'Unit/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 3, '2024-04-08 10:43:25', 'N', 136, '2024-04-08 10:43:10', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (122, 'PLATFORM', 56, '修改单位', '修改单位', '/admin/unit/edited/{id}', 'Unit/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 3, '2024-04-08 10:44:05', 'N', 136, '2024-04-08 10:44:05', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (124, 'PLATFORM', 56, '单位导入', '单位导入', '/admin/unit/import', 'Unit/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,56', NULL);
INSERT INTO `permissions` VALUES (125, 'PLATFORM', 88, '比价详情', '比价详情', '/admin/compare/{id}', 'Compare/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-18 11:05:05', 'N', 136, '2024-04-08 10:51:24', NULL, NULL, 136, '63,88', NULL);
INSERT INTO `permissions` VALUES (126, 'PLATFORM', 57, '币种详情', '币种详情', '/admin/currency/{id}', 'Currency/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (128, 'PLATFORM', 57, '币种列表', '币种列表', '/admin/currency', 'Currency/getList', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-05-10 10:23:43', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (129, 'PLATFORM', 57, '修改币种', '修改币种', '/admin/currency/edited/{id}', 'Currency/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (130, 'PLATFORM', 57, '新增币种', '新增币种', '/admin/currency/add', 'Currency/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (131, 'PLATFORM', 57, '删除币种', '删除币种', '/admin/currency/delete', 'Currency/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (132, 'PLATFORM', 57, '禁用币种', '禁用币种', '/admin/currency/disable', 'Currency/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (133, 'PLATFORM', 57, '启用币种', '启用币种', '/admin/currency/enable', 'Currency/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (134, 'PLATFORM', 57, '币种导入', '币种导入', '/admin/currency/import', 'Currency/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,57', NULL);
INSERT INTO `permissions` VALUES (136, 'PLATFORM', 58, '银行详情', '银行详情', '/admin/bank/{id}', 'Bank/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (138, 'PLATFORM', 58, '银行编码', '银行编码', '/admin/bank/number', 'Bank/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (139, 'PLATFORM', 58, '编辑银行', '编辑银行', '/admin/bank/edited/{id}', 'Bank/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (140, 'PLATFORM', 58, '新增银行', '新增银行', '/admin/bank/add', 'Bank/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (141, 'PLATFORM', 58, '删除银行', '删除银行', '/admin/bank/delete', 'Bank/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (142, 'PLATFORM', 58, '禁用银行', '禁用银行', '/admin/bank/disable', 'Bank/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (143, 'PLATFORM', 58, '启用银行', '启用银行', '/admin/bank/enable', 'Bank/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (144, 'PLATFORM', 58, '银行导入', '银行导入', '/admin/bank/import', 'Bank/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,58', NULL);
INSERT INTO `permissions` VALUES (147, 'PLATFORM', 61, '详情', '详情', '/admin/settlementtype/{id}', 'SettleMentType/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (148, 'PLATFORM', 61, '编辑', '编辑', '/admin/settlementtype/edited/{id}', 'SettleMentType/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (149, 'PLATFORM', 61, '新增', '新增', '/admin/settlementtype/add', 'SettleMentType/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (150, 'PLATFORM', 61, '删除', '删除', '/admin/settlementtype/delete', 'SettleMentType/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (151, 'PLATFORM', 61, '禁用', '禁用', '/admin/settlementtype/disable', 'SettleMentType/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (152, 'PLATFORM', 61, '启用', '启用', '/admin/settlementtype/enable', 'SettleMentType/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (153, 'PLATFORM', 61, '导入', '导入', '/admin/settlementtype/import', 'SettleMentType/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,61', NULL);
INSERT INTO `permissions` VALUES (155, 'PLATFORM', 62, '详情', '详情', '/admin/paycond/{id}', 'Paycond/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (157, 'PLATFORM', 62, '编辑', '编辑', '/admin/paycond/edited/{id}', 'Paycond/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (158, 'PLATFORM', 62, '新增', '新增', '/admin/paycond/add', 'Paycond/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (159, 'PLATFORM', 62, '删除', '删除', '/admin/paycond/delete', 'Paycond/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (160, 'PLATFORM', 62, '禁用', '禁用', '/admin/paycond/disable', 'Paycond/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (161, 'PLATFORM', 62, '启用', '启用', '/admin/paycond/enable', 'Paycond/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (162, 'PLATFORM', 62, '导入', '导入', '/admin/paycond/import', 'Paycond/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (164, 'PLATFORM', 62, '付款条件编码', '付款条件编码', '/admin/paycond/number', 'Paycond/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,62', NULL);
INSERT INTO `permissions` VALUES (165, 'PLATFORM', 84, '详情', '详情', '/admin/supplier/evagrade/{id}', 'SupplierEvaGrade/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (167, 'PLATFORM', 84, '评估等级编码', '评估等级编码', '/admin/supplier/evagrade/number', 'SupplierEvaGrade/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (168, 'PLATFORM', 84, '编辑', '编辑', '/admin/supplier/evagrade/edited/{id}', 'SupplierEvaGrade/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (169, 'PLATFORM', 84, '新增', '新增', '/admin/supplier/evagrade/add', 'SupplierEvaGrade/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (170, 'PLATFORM', 84, '删除', '删除', '/admin/supplier/evagrade/delete', 'SupplierEvaGrade/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (171, 'PLATFORM', 84, '禁用', '禁用', '/admin/supplier/evagrade/disable', 'SupplierEvaGrade/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (172, 'PLATFORM', 84, '启用', '启用', '/admin/supplier/evagrade/enable', 'SupplierEvaGrade/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (173, 'PLATFORM', 84, '导入', '导入', '/admin/supplier/evagrade/import', 'SupplierEvaGrade/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '55,84', NULL);
INSERT INTO `permissions` VALUES (175, 'PLATFORM', 32, '详情', '详情', '/admin/org/{id}', 'Org/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (176, 'PLATFORM', 32, '列表(树形)', '列表(树形)', '/admin/org', 'Org/tree', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (177, 'PLATFORM', 32, '组织机构编码', '组织机构编码', '/admin/org/number', 'Org/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (178, 'PLATFORM', 32, '编辑', '编辑', '/admin/org/edited/{id}', 'Org/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-22 13:15:45', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (179, 'PLATFORM', 32, '新增', '新增', '/admin/org/add', 'Org/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (180, 'PLATFORM', 32, '删除', '删除', '/admin/org/delete', 'Org/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (181, 'PLATFORM', 32, '禁用', '禁用', '/admin/org/disable', 'Org/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (182, 'PLATFORM', 32, '启用', '启用', '/admin/org/enable', 'Org/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (183, 'PLATFORM', 32, '导入', '导入', '/admin/org/import', 'Org/import', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (184, 'PLATFORM', 33, '详情', '详情', '/admin/user/{id}', 'User/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (186, 'PLATFORM', 33, '业务员', '业务员', '/admin/user/persons', 'User/persons', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (187, 'PLATFORM', 33, '编辑', '编辑', '/admin/user/edited/{id}', 'User/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (188, 'PLATFORM', 33, '新增', '新增', '/admin/user/add', 'User/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (189, 'PLATFORM', 33, '删除', '删除', '/admin/user/delete', 'User/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (190, 'PLATFORM', 33, '禁用', '禁用', '/admin/user/disable', 'User/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (191, 'PLATFORM', 33, '启用', '启用', '/admin/user/enable', 'User/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (192, 'PLATFORM', 33, '修改密码', '修改密码', '/admin/user/change/password', 'User/change', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (194, 'PLATFORM', 33, '拼音', '拼音', '/admin/user/pinyin', 'User/pinyin', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (195, 'COMMON', 33, '角色', '角色', '/admin/user/roles', 'User/roles', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-05-11 09:43:48', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (196, 'COMMON', 33, '菜单', '菜单', '/admin/user/menus', 'User/menus', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (197, 'PLATFORM', 33, '组织机构ID', '组织机构ID', '/admin/user/orgs', 'User/orgs', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (198, 'PLATFORM', 33, '组织机构(树形)', '组织机构(树形)', '/admin/user/orgtree', 'User/orgTree', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (201, 'PLATFORM', 87, '报价详情', '报价详情', '/admin/quote/{id}', 'Quote/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '63,87', NULL);
INSERT INTO `permissions` VALUES (202, 'PLATFORM', 87, '报价汇总信息', '报价汇总信息', '/admin/quote/sum_quote/{inquiry_id}', 'Quote/sum_quote', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '63,87', NULL);
INSERT INTO `permissions` VALUES (203, 'PLATFORM', 24, '角色详情', '角色详情', '/admin/roles/{id}', 'Roles/info', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:52:40', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (205, 'COMMON', 24, '公司', '公司', '/admin/roles/company', 'Roles/getRoleByCompany', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-05-11 09:57:06', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (206, 'PLATFORM', 24, '新增角色', '新增角色', '/admin/roles/add', 'Roles/add', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:53:23', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (207, 'PLATFORM', 24, '禁用角色', '禁用角色', '/admin/roles/disable', 'Roles/disable', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:53:41', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (208, 'PLATFORM', 24, '启用角色', '启用角色', '/admin/roles/enable', 'Roles/enable', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:53:54', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (209, 'PLATFORM', 24, '删除角色', '删除角色', '/admin/roles/delete', 'Roles/delete', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:54:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (210, 'PLATFORM', 24, '编辑角色', '编辑角色', '/admin/roles/edited/{id}', 'Roles/edited', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:54:30', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (211, 'PLATFORM', 24, '角色授权', '角色授权', '/admin/roles/menus/{id}', 'Roles/hasMenus', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:56:03', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (212, 'PLATFORM', 24, '添加用户角色', '添加用户角色', '/admin/roles/user/{id}', 'Roles/userHasRoles', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:55:52', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (213, 'PLATFORM', 24, '获取菜单', '获取菜单', '/admin/roles/menuslist/{id}', 'Roles/menusList', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:55:10', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (214, 'PLATFORM', 24, '获取用户角色', '获取用户角色', '/admin/roles/listbyuser/{id}', 'Roles/listbyuser', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-12 13:55:28', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,24', NULL);
INSERT INTO `permissions` VALUES (216, 'COMMON', 23, '菜单列表(树状)', '菜单列表(树状)', '/admin/menus/tree', 'Menus/tree', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-28 11:21:33', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (217, 'PLATFORM', 23, '新增菜单', '新增菜单', '/admin/menus/add', 'Menus/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-12 14:09:26', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (218, 'PLATFORM', 23, '禁用菜单', '禁用菜单', '/admin/menus/disable', 'Menus/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (219, 'PLATFORM', 23, '启用菜单', '启用菜单', '/admin/menus/enable', 'Menus/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (220, 'PLATFORM', 23, '删除菜单', '删除菜单', '/admin/menus/delete', 'Menus/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (221, 'PLATFORM', 23, '编辑菜单', '编辑菜单', '/admin/menus/edited/{id}', 'Menus/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (222, 'PLATFORM', 33, '用户管理', '用户管理', '/admin/auth/me', 'Auth/me', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,33', NULL);
INSERT INTO `permissions` VALUES (223, 'PLATFORM', 33, '用户信息', '用户信息', '/admin/auth/info', 'Auth/info', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,33', NULL);
INSERT INTO `permissions` VALUES (224, 'PLATFORM', 33, '修改密码', '修改密码', '/admin/auth/change/password', 'Auth/change', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,33', NULL);
INSERT INTO `permissions` VALUES (225, 'PLATFORM', 33, '变更组织', '变更组织', '/admin/auth/change/org', 'Auth/changeOrg', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,33', NULL);
INSERT INTO `permissions` VALUES (226, 'PLATFORM', 33, '头像', '头像', '/admin/auth/avatar', 'Auth/avatar', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,33', NULL);
INSERT INTO `permissions` VALUES (227, 'PLATFORM', 33, '登出', '登出', '/admin/auth/logout', 'Auth/logout', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,33', NULL);
INSERT INTO `permissions` VALUES (228, 'PLATFORM', 32, '获取采购商', '采购商', '/admin/auth/purchasers', 'Auth/purchasers', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-22 15:27:36', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,32', NULL);
INSERT INTO `permissions` VALUES (231, 'PLATFORM', 6, '权限(树形)', '权限(树形)', '/admin/permissions/tree', 'Permissions/tree', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-28 11:21:48', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,6', NULL);
INSERT INTO `permissions` VALUES (233, 'PLATFORM', 6, '禁用', '禁用', '/admin/permissions/disable', 'Permissions/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 17:40:58', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,6', NULL);
INSERT INTO `permissions` VALUES (234, 'PLATFORM', 6, '启用', '启用', '/admin/permissions/enable', 'Permissions/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 17:40:41', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,6', NULL);
INSERT INTO `permissions` VALUES (237, 'PLATFORM', 31, '采购商列表', '采购商列表', '/admin/purchaser', 'Purchaser/tree', 'BUTTON', 0, 'Y', 'NORMAL', 'N', NULL, '2024-04-09 08:49:13', 'N', NULL, NULL, NULL, NULL, NULL, '30,31', NULL);
INSERT INTO `permissions` VALUES (239, 'PLATFORM', 67, '供应商列表', '供应商列表', '/admin/supplier/list', 'SupplierBase/suppliers', 'BUTTON', 0, 'Y', 'NORMAL', 'N', NULL, NULL, 'N', 136, NULL, NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (240, 'COMMON', 67, '供应商详情', '供应商详情', '/admin/supplier/{id}', 'Supplier/info', 'BUTTON', 0, 'N', 'NORMAL', 'N', NULL, '2024-04-26 10:08:33', 'N', NULL, NULL, NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (241, 'PLATFORM', 67, '供应商详情', '供应商详情', '/admin/supplier/base/{id}', 'SupplierBase/info', 'BUTTON', 0, 'Y', 'NORMAL', 'N', NULL, '2024-04-22 14:54:16', 'N', NULL, NULL, NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (244, 'PLATFORM', 294, '公告', '公告', '/admin/notice', 'Notice/getList', 'MENU', 0, 'N', 'NORMAL', '', 0, '2024-04-09 13:25:04', 'N', 136, '2024-04-09 09:43:24', NULL, NULL, 136, '294', NULL);
INSERT INTO `permissions` VALUES (245, 'PLATFORM', 294, '消息', '消息', '/admin/message', 'Message/getList', 'MENU', 0, 'N', 'NORMAL', '', 0, '2024-04-09 13:24:53', 'N', 136, '2024-04-09 09:44:32', NULL, NULL, 136, '294', NULL);
INSERT INTO `permissions` VALUES (246, 'PLATFORM', 245, '消息详情', '消息详情', '/admin/message/{id}', 'Message/info', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-09 13:25:33', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,245', NULL);
INSERT INTO `permissions` VALUES (247, 'PLATFORM', 245, '删除消息', '删除消息', '/admin/message/delete', 'Message/delete', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-09 13:25:48', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,245', NULL);
INSERT INTO `permissions` VALUES (248, 'PLATFORM', 245, '消息已读', '消息已读', '/admin/message/read', 'Message/read', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-09 13:26:01', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,245', NULL);
INSERT INTO `permissions` VALUES (249, 'PLATFORM', 245, '消息未读', '消息未读', '/admin/message/unread', 'Message/unread', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-09 13:26:13', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,245', NULL);
INSERT INTO `permissions` VALUES (250, 'PLATFORM', 244, '公告详情', '公告详情', '/admin/notice/{id}', 'Notice/info', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-09 13:27:22', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (251, 'PLATFORM', 244, '公告编码', '公告编码', '/admin/notice/number', 'Notice/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:27:33', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (252, 'PLATFORM', 244, '公告编辑', '公告编辑', '/admin/notice/edited/{id}', 'Notice/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:27:48', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (253, 'PLATFORM', 244, '发布公告', '发布公告', '/admin/notice/add', 'Notice/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:28:05', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (254, 'PLATFORM', 244, '删除公告', '删除公告', '/admin/notice/delete', 'Notice/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:28:16', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (255, 'PLATFORM', 244, '置顶公告', '置顶公告', '/admin/notice/topping', 'Notice/topping', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:26:55', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (256, 'PLATFORM', 244, '取消公告', '取消公告', '/admin/notice/cancel ', 'Notice/cancel', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:26:28', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (258, 'PLATFORM', 244, '公告审核', '公告审核', '/admin/notice/audit', 'Notice/audit', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 13:27:12', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '294,244', NULL);
INSERT INTO `permissions` VALUES (260, 'PLATFORM', 33, '修改邮箱', '修改邮箱', '/admin/change_account/email', 'ChangeAccount/email', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-25 15:03:16', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (261, 'PLATFORM', 33, '修改手机号', '修改手机号', '/admin/change_account/phone', 'ChangeAccount/phone', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-25 15:03:24', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (262, 'PLATFORM', 33, '修改账号', '修改账号', '/admin/change_account/account', 'ChangeAccount/index', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-25 15:03:34', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (263, 'PLATFORM', 33, '手机号验证', '手机号验证', '/admin/change_account/phoneVerify', 'ChangeAccount/phoneVerify', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-25 15:03:42', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (264, 'PLATFORM', 33, '邮箱验证', '邮箱验证', '/admin/change_account/emailVerify', 'ChangeAccount/emailVerify', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-25 15:03:55', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (271, 'PLATFORM', 67, '供应商分类', '供应商分类', '/admin/supplier/group', 'SupplierGroup/getList', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-22 14:56:45', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (272, 'PLATFORM', 67, '企业类型', '企业类型', '/admin/supplier/enterprise_type', 'Supplier/enterpriseType', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-22 14:56:05', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (274, 'PLATFORM', 67, '当前供应商审核记录', '当前供应商审核记录', '/admin/supplier/audit/history', 'SupplierAudit/history', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-22 14:56:27', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (275, 'PLATFORM', 67, '供应商审核记录', '供应商审核记录', '/admin/supplier/audit/comments', 'SupplierAudit/comments', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-22 14:53:48', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (277, 'PLATFORM', 23, '菜单详情', '菜单详情', '/admin/menus/{id}', 'Menus/info', 'BUTTON', 0, 'Y', 'NORMAL', 'N', 5, '2024-04-16 10:46:20', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (278, 'SUPPLIER', 289, '报价详情', '报价详情', '/admin/supplier/quote/{id}', 'SupplierQuote/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-09 11:09:38', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (281, 'SUPPLIER', 289, '根据询单ID获取报价详情', '根据询单ID获取报价详情', '/admin/supplier/quote/info/{inquiry_id}', 'SupplierQuote/infoByInquiryId', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (282, 'SUPPLIER', 289, '删除报价', '删除报价', '/admin/supplier/quote/delete', 'SupplierQuote/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (283, 'SUPPLIER', 289, '获取报价编码', '获取报价编码', '/admin/supplier/quote/number', 'SupplierQuote/number', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (284, 'SUPPLIER', 289, '编辑报价', '编辑报价', '/admin/supplier/quote/edited/{id}', 'SupplierQuote/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (285, 'SUPPLIER', 289, '新增报价', '新增报价', '/admin/supplier/quote/add', 'SupplierQuote/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (287, 'SUPPLIER', 289, '导入报价物料', '导入报价物料', '/admin/supplier/quote/entry/import', 'SupplierQuote/entryImport', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (288, 'SUPPLIER', 82, '询价单', '询价单', '/admin/supplier/inquiry', 'SupplierInquiry/getList', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-09 11:08:35', 'N', 136, '2024-04-09 11:08:35', NULL, NULL, 136, '68,82', NULL);
INSERT INTO `permissions` VALUES (289, 'SUPPLIER', 82, '报价单', '报价单', '/admin/supplier/quote', 'SupplierQuote/getList', 'BUTTON', 0, 'Y', 'NORMAL', '', 0, '2024-04-09 11:09:17', 'N', 136, '2024-04-09 11:09:17', NULL, NULL, 136, '68,82', NULL);
INSERT INTO `permissions` VALUES (290, 'SUPPLIER', 288, '询价详情', '询价详情', '/admin/supplier/inquiry/{id}', 'SupplierInquiry/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '62,82,288', NULL);
INSERT INTO `permissions` VALUES (291, 'SUPPLIER', 288, '不报价', '不报价', '/admin/supplier/inquiry/unquote/{id}', 'SupplierInquiry/unQuote', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '62,82,288', NULL);
INSERT INTO `permissions` VALUES (292, 'SUPPLIER', 289, '供应商默认联系人', '供应商默认联系人', '/admin/supplier/default/contact', 'Supplier/defaultContact', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-11 09:54:21', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,82,289', NULL);
INSERT INTO `permissions` VALUES (293, 'COMMON', 33, '用户角色', '用户角色', '/admin/rolesuser', 'RolesUser/updateOrAdd', 'BUTTON', 0, 'N', 'NORMAL', '', 0, '2024-04-09 13:13:30', 'N', 136, '2024-04-09 13:11:50', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (294, 'PLATFORM', 0, '消息公告', '消息公告', '/message_notice', '/message_notice', 'MENU', 0, 'N', 'NORMAL', '', 0, '2024-04-09 13:19:49', 'N', 136, '2024-04-09 13:19:49', NULL, NULL, 136, NULL, NULL);
INSERT INTO `permissions` VALUES (295, 'SUPPLIER', 68, '消息公告', '消息公告', 'message', 'message', 'MENU', 0, 'Y', 'NORMAL', '', 5, '2024-05-08 09:03:48', 'N', 136, '2024-04-09 14:29:05', NULL, NULL, 136, '68', NULL);
INSERT INTO `permissions` VALUES (296, 'SUPPLIER', 68, '人员管理', '人员管理', '/admin/supplier/user', 'SupplierUser/getList', 'BUTTON', 0, 'Y', 'NORMAL', '', 3, '2024-05-10 15:56:11', 'N', 136, '2024-04-09 14:31:09', NULL, NULL, 1875271515914638372, '68', NULL);
INSERT INTO `permissions` VALUES (297, 'SUPPLIER', 295, '消息管理', '消息管理', '/messageManage', 'messageManage', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-05-08 09:04:27', 'N', 136, '2024-04-09 14:34:15', NULL, NULL, 136, '68,295', NULL);
INSERT INTO `permissions` VALUES (298, 'SUPPLIER', 295, '公告管理', '公告管理', 'notice', 'notice', 'MENU', 0, 'Y', 'NORMAL', '', 0, '2024-05-08 09:04:35', 'N', 136, '2024-04-09 14:34:36', NULL, NULL, 136, '68,295', NULL);
INSERT INTO `permissions` VALUES (299, 'SUPPLIER', 297, '消息详情', '消息详情', '/admin/supplier/message/{id}', 'SupplierMessage/info', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,297', NULL);
INSERT INTO `permissions` VALUES (300, 'SUPPLIER', 297, '消息列表', '消息列表', '/admin/supplier/message', 'SupplierMessage/getList', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,297', NULL);
INSERT INTO `permissions` VALUES (301, 'SUPPLIER', 297, '删除消息', '删除消息', '/admin/supplier/message/delete', 'SupplierMessage/delete', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,297', NULL);
INSERT INTO `permissions` VALUES (302, 'SUPPLIER', 297, '消息批量已读', '消息批量已读', '/admin/supplier/message/read', 'SupplierMessage/read', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,297', NULL);
INSERT INTO `permissions` VALUES (303, 'SUPPLIER', 297, '消息批量未读', '消息批量未读', '/admin/supplier/message/unread', 'SupplierMessage/unread', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,297', NULL);
INSERT INTO `permissions` VALUES (304, 'SUPPLIER', 298, '公告列表', '公告列表', '/admin/supplier/notice', 'Supplier/notice', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,298', NULL);
INSERT INTO `permissions` VALUES (305, 'SUPPLIER', 298, '公告详情', '公告详情', '/admin/supplier/notice/{notice_id}', 'Supplier/noticeInfo', 'BUTTON', 0, 'N', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,298', NULL);
INSERT INTO `permissions` VALUES (307, 'SUPPLIER', 296, '人员详情', '人员详情', '/admin/supplier/user/{id}', 'SupplierUser/info', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (308, 'SUPPLIER', 296, '编辑人员', '编辑人员', '/admin/supplier/user/edited/{id}', 'SupplierUser/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (309, 'SUPPLIER', 296, '新增人员', '新增人员', '/admin/supplier/user/add', 'SupplierUser/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (310, 'SUPPLIER', 296, '删除人员', '删除人员', '/admin/supplier/user/delete', 'SupplierUser/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (311, 'SUPPLIER', 296, '禁用人员', '禁用人员', '/admin/supplier/user/disable', 'SupplierUser/disable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (312, 'SUPPLIER', 296, '启用人员', '启用人员', '/admin/supplier/user/enable', 'SupplierUser/enable', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (314, 'PLATFORM', 88, '编辑比价', '编辑比价', '/admin/compare/edited/{id}', 'Compare/edited', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '63,88', NULL);
INSERT INTO `permissions` VALUES (315, 'PLATFORM', 88, '新增比价', '新增比价', '/admin/compare/add', 'Compare/add', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '63,88', NULL);
INSERT INTO `permissions` VALUES (316, 'PLATFORM', 88, '删除比价', '删除比价', '/admin/compare/delete', 'Compare/delete', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '63,88', NULL);
INSERT INTO `permissions` VALUES (317, 'PLATFORM', 88, '获取比价编码', '获取比价编码', '/admin/compare/number', 'Compare/number', 'BUTTON', 0, 'N', 'NORMAL', '', 0, '2024-04-18 11:11:51', 'N', 136, '2024-04-10 13:52:54', NULL, NULL, 136, '63,88', NULL);
INSERT INTO `permissions` VALUES (318, 'SUPPLIER', 296, '更改用户密码', '更改用户密码', '/admin/supplier/user/change/password', 'SupplierUser/change', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (319, 'SUPPLIER', 296, '获取拼音', '获取拼音', '/admin/supplier/user/pinyin', 'SupplierUser/pinyin', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (320, 'SUPPLIER', 296, '获取角色', '获取角色', '/admin/supplier/user/roles', 'SupplierUser/roles', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (321, 'SUPPLIER', 296, '获取用户菜单', '获取用户菜单', '/admin/supplier/user/menus', 'SupplierUser/menus', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (322, 'SUPPLIER', 296, '用户授权', '用户授权', '/admin/supplier/rolesuser', 'SupplierUser/rolesuser', 'BUTTON', 0, 'Y', 'NORMAL', '', 5, '2024-04-08 10:45:07', 'N', 136, '2024-04-08 10:45:07', NULL, NULL, 136, '68,295,296', NULL);
INSERT INTO `permissions` VALUES (326, 'PLATFORM', 67, '取企业注册信息', '取企业注册信息', '/admin/supplier/company', 'Supplier/company', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-22 14:31:54', 'N', 136, '2024-04-22 14:31:54', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (329, 'PLATFORM', 67, '供应商详情(编辑)', '供应商详情(编辑)', '/admin/supplier/audit/{id}', 'SupplierBase/auditInfo', 'BUTTON', 0, 'Y', 'NORMAL', 'N', NULL, '2024-04-25 13:57:51', 'N', NULL, NULL, NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (330, 'PLATFORM', 67, '编辑', '编辑', '/admin/supplier/base/edited/{id}', 'SupplierBase/edited', 'BUTTON', 0, 'Y', 'NORMAL', 'N', NULL, '2024-04-25 13:57:42', 'N', NULL, NULL, NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (331, 'PLATFORM', 125, '比价审批', '比价审批', '/admin/compare/audit/verify/{id}', 'Compare/verify', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-24 16:47:07', 'N', 136, '2024-04-23 17:13:11', NULL, NULL, 136, '63,88,125', NULL);
INSERT INTO `permissions` VALUES (332, 'PLATFORM', 125, '终止审批', '终止审批', '/admin/compare/audit/stop/{id}', 'Compare/stop', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-25 08:59:51', 'N', 136, '2024-04-24 16:46:38', NULL, NULL, 136, '63,88,125', NULL);
INSERT INTO `permissions` VALUES (333, 'PLATFORM', 71, '企业详情', '企业详情', '/admin/supplier/{id}', 'Supplier/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-26 13:21:02', 'N', 136, '2024-04-26 13:21:02', NULL, NULL, 136, '68,71', NULL);
INSERT INTO `permissions` VALUES (334, 'PLATFORM', 71, '供应商审核详情', '供应商审核详情', '/admin/supplier/audit/{id}', 'SupplierBase/auditInfo', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-26 13:22:02', 'N', 136, '2024-04-26 13:22:02', NULL, NULL, 136, '68,71', NULL);
INSERT INTO `permissions` VALUES (335, 'PLATFORM', 71, '供应商编辑', '供应商编辑', '/admin/supplier/edited/{id}', 'Supplier/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-26 13:23:21', 'N', 136, '2024-04-26 13:23:21', NULL, NULL, 136, '68,71', NULL);
INSERT INTO `permissions` VALUES (336, 'PLATFORM', 70, '审核意见', '审核意见', '/admin/supplier/audit/comments', 'SupplierAudit/comments', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-26 13:24:25', 'N', 136, '2024-04-26 13:24:25', NULL, NULL, 136, '68,70', NULL);
INSERT INTO `permissions` VALUES (337, 'PLATFORM', 70, '评审意见', '评审意见', '/admin/supplier/audit/history', 'Supplier/history', '', 0, 'Y', 'NORMAL', '', 0, '2024-04-26 13:25:23', 'N', 136, '2024-04-26 13:25:23', NULL, NULL, 136, '68,70', NULL);
INSERT INTO `permissions` VALUES (338, 'PLATFORM', 23, '获取当前用户菜单树', '获取当前用户菜单树', 'admin/menus/userTree', 'menus/userTree', '', 0, 'N', 'NORMAL', '', 0, '2024-04-28 15:24:44', 'N', 136, '2024-04-28 15:24:44', NULL, NULL, 136, '5,23', NULL);
INSERT INTO `permissions` VALUES (339, 'PLATFORM', 64, '比价', '比价', '/admin/compare/add', 'Compare/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-05-06 14:03:31', 'N', 136, '2024-05-06 14:03:31', NULL, NULL, 136, '63,64', NULL);
INSERT INTO `permissions` VALUES (340, 'PLATFORM', 67, '新增供应商', '新增供应商', '/admin/supplier/base/add', 'SupplierBase/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-05-10 16:11:12', 'N', 136, '2024-05-10 16:11:12', NULL, NULL, 136, '65,67', NULL);
INSERT INTO `permissions` VALUES (342, 'SUPPLIER', 74, '进度', '进度', '/admin/supplier/audit/progress', 'SupplierAudit/progress', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 08:58:30', 'N', 136, '2024-05-13 15:49:08', NULL, NULL, 136, '73,74', NULL);
INSERT INTO `permissions` VALUES (343, 'SUPPLIER', 74, '审核记录', '审核记录', '/admin/supplier/audit/history', 'Supplier/history', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:10:50', 'N', 136, '2024-05-13 15:49:56', NULL, NULL, 1875271515914638372, '73,74', NULL);
INSERT INTO `permissions` VALUES (344, 'SUPPLIER', 74, '评审意见', '评审意见', '/admin/supplier/audit/comments', 'SupplierAudit/comments', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:10:57', 'N', 136, '2024-05-13 15:58:50', NULL, NULL, 1875271515914638372, '73,74', NULL);
INSERT INTO `permissions` VALUES (345, 'SUPPLIER', 60, '供应商详情', '供应商详情', '/admin/supplier/{id}', 'Supplier/info', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:12:46', 'N', 136, '2024-05-13 16:01:29', NULL, NULL, 136, '59,60', NULL);
INSERT INTO `permissions` VALUES (346, 'COMMON', 60, '企业类型', '企业类型', '/admin/supplier/enterprise_type', 'Supplier/enterpriseType', '', 0, 'N', 'NORMAL', '', 0, '2024-05-13 16:02:04', 'N', 136, '2024-05-13 16:02:04', NULL, NULL, 136, '59,60', NULL);
INSERT INTO `permissions` VALUES (347, 'SUPPLIER', 59, '提交准入协同', '提交准入协同', '/admin/supplier/base/edited/{id}', 'SupplierBase/edited', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:12:33', 'N', 136, '2024-05-13 16:03:12', NULL, NULL, 136, '59', NULL);
INSERT INTO `permissions` VALUES (348, 'SUPPLIER', 59, '获取企业信息', '获取企业信息', '/admin/supplier/company', 'Supplier/company', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:12:21', 'N', 136, '2024-05-13 16:04:28', NULL, NULL, 136, '59', NULL);
INSERT INTO `permissions` VALUES (349, 'PLATFORM', 65, '准入管理', '准入管理', '/admin/supplier/audit/pending', 'SupplierAudit/pending', '', 0, 'Y', 'NORMAL', '', 0, '2024-05-13 16:20:13', 'N', 136, '2024-05-13 16:16:06', NULL, NULL, 136, '65', NULL);
INSERT INTO `permissions` VALUES (350, 'SUPPLIER', 59, '准入申请', '准入申请', '/supplierClient/applyList', '/supplierClient/applyList', '', 0, 'N', 'NORMAL', '', 0, '2024-05-15 09:25:39', 'N', 136, '2024-05-15 08:53:54', NULL, NULL, 136, '59', NULL);
INSERT INTO `permissions` VALUES (351, 'SUPPLIER', 350, '准入申请', '准入申请', '/supplierClient/apply', '/supplierClient/apply', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:12:11', 'N', 136, '2024-05-15 08:54:29', NULL, NULL, 136, '59,350', NULL);
INSERT INTO `permissions` VALUES (352, 'SUPPLIER', 350, '准入详情', '准入详情', '/supplierClient/applyInfo', '/supplierClient/applyInfo', '', 0, 'N', 'NORMAL', '', 0, '2024-05-23 09:12:02', 'N', 136, '2024-05-15 08:55:02', NULL, NULL, 136, '59,350', NULL);
INSERT INTO `permissions` VALUES (353, 'COMMON', 33, '获取登录人菜单', '获取登录人菜单', '/admin/menus/userMenus', 'menus/userMenus', '', 0, 'N', 'NORMAL', '', 0, '2024-05-21 14:00:16', 'N', 136, '2024-05-21 14:00:16', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (354, 'PLATFORM', 0, '竞价管理', '竞价管理', '/admin/bidbill', 'BidBill/getList', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:36:32', 'N', 136, '2024-06-03 11:36:32', NULL, NULL, 136, '0', NULL);
INSERT INTO `permissions` VALUES (355, 'PLATFORM', 446, '竞价详情', '竞价详情', '/admin/bidbill/{id}', 'BidBill/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:37:04', 'N', 136, '2024-06-03 11:37:04', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (356, 'PLATFORM', 446, '修改竞价', '修改竞价', '/admin/bidbill/edited/{id}', 'BidBill/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:37:41', 'N', 136, '2024-06-03 11:37:41', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (357, 'PLATFORM', 446, '新增竞价', '新增竞价', '/admin/bidbill/add', 'BidBill/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (358, 'PLATFORM', 446, '获取竞价编码', '获取竞价编码', '/admin/bidbill/number', 'BidBill/number', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:51:37', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (359, 'PLATFORM', 446, '删除竞价', '删除竞价', '/admin/bidbill/delete', 'BidBill/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:51:47', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (361, 'PLATFORM', 446, '变更报名截止日期', '变更报名截止日期', '/admin/bidbill/change/{id}', 'BidBill/change', '', 0, 'N', 'NORMAL', '', 0, '2024-07-05 14:22:37', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 1875271515914638372, '354,446', NULL);
INSERT INTO `permissions` VALUES (362, 'PLATFORM', 354, '竞价大厅列表', '竞价大厅列表', '/admin/bidbill/hall', 'BidBill/hall', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354', NULL);
INSERT INTO `permissions` VALUES (363, 'PLATFORM', 446, '资审供应商列表', '资审供应商列表', '/admin/bidbill/suppliers/{id}', 'BidBill/suppliers', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (365, 'PLATFORM', 354, '竞价定标列表', '竞价定标列表', '/admin/bidbill/decision', 'BidBill/decision_list', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354', NULL);
INSERT INTO `permissions` VALUES (366, 'PLATFORM', 365, '中标供应商', '中标供应商', '/admin/bidbill/winning/{id}', 'BidBill/winning', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,365', NULL);
INSERT INTO `permissions` VALUES (367, 'PLATFORM', 446, '竞价资审', '竞价资审', '/admin/bidbill/check/{id}', 'BidBill/check', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (369, 'PLATFORM', 446, '竞价终止', '竞价终止', '/admin/bidbill/termination/{id}', 'BidBill/termination', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (370, 'PLATFORM', 446, '启动竞价', '启动竞价', '/admin/bidbill/start/{id}', 'BidBill/start', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (371, 'PLATFORM', 362, '竞价暂停', '竞价暂停', '/admin/bidbill/stop/{id}', 'BidBill/stop', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,362', NULL);
INSERT INTO `permissions` VALUES (372, 'PLATFORM', 362, '竞价开始', '竞价开始', '/admin/bidbill/begin/{id}', 'BidBill/begin', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,362', NULL);
INSERT INTO `permissions` VALUES (374, 'PLATFORM', 362, '竞价大厅详情', '竞价大厅详情', '/admin/bidbill/hall/{id}', 'BidBill/hallInfo', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,362', NULL);
INSERT INTO `permissions` VALUES (375, 'PLATFORM', 362, '绑定用户ID和竞价ID', '绑定用户ID和竞价ID', '/admin/bidbill/bindUid/{id}', 'BidBill/bindUid', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,362', NULL);
INSERT INTO `permissions` VALUES (377, 'PLATFORM', 362, '竞价离线', '竞价离线', '/admin/bidbill/offline/{id}', 'BidBill/offline', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '354,362', NULL);
INSERT INTO `permissions` VALUES (378, 'SUPPLIER', 68, '竞价管理', '竞价管理', '/admin/supplier/bidbill', 'SupplierBidBill/getList', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:44:58', 'N', 136, '2024-06-03 13:44:58', NULL, NULL, 136, '68', NULL);
INSERT INTO `permissions` VALUES (379, 'SUPPLIER', 378, '竞价详情', '竞价详情', '/admin/supplier/bidbill/{id}', 'SupplierBidBill/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:45:40', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 136, '68,378', NULL);
INSERT INTO `permissions` VALUES (382, 'SUPPLIER', 381, '缴纳保证金', '缴纳保证金', '/admin/supplier/bidbill/pay/{id}', 'SupplierBidBill/pay', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378,381', NULL);
INSERT INTO `permissions` VALUES (383, 'SUPPLIER', 378, '竞价大厅列表', '竞价大厅列表', '/admin/supplier/bidbill/hall', 'SupplierBidBill/hall', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378', NULL);
INSERT INTO `permissions` VALUES (384, 'SUPPLIER', 383, '竞价大厅详情', '竞价大厅详情', '/admin/supplier/bidbill/hall/{id}', 'SupplierBidBill/hallInfo', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378,383', NULL);
INSERT INTO `permissions` VALUES (385, 'SUPPLIER', 383, '竞价报价', '竞价报价', '/admin/supplier/bidbill/quote/{id}', 'SupplierBidBill/quote', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378,383', NULL);
INSERT INTO `permissions` VALUES (386, 'SUPPLIER', 383, '绑定用户ID和竞价ID', '绑定用户ID和竞价ID', '/admin/supplier/bidbill/bindUid/{id}', 'SupplierBidBill/bindUid', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378,383', NULL);
INSERT INTO `permissions` VALUES (387, 'SUPPLIER', 383, '绑定竞价ID', '绑定竞价ID', '/admin/supplier/bidbill/bindGroup/{id}', 'SupplierBidBill/bindGroup', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378,383', NULL);
INSERT INTO `permissions` VALUES (388, 'SUPPLIER', 383, '竞价离线', '竞价离线', '/admin/supplier/bidbill/offline/{id}', 'SupplierBidBill/offline', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 11:38:09', 'N', 136, '2024-06-03 11:38:09', NULL, NULL, 136, '68,378,383', NULL);
INSERT INTO `permissions` VALUES (390, 'COMMON', 33, '通过权限获取人员', '通过权限获取人员', '/admin/user/getUserByRole', 'user/getUserByRole', '', 0, 'N', 'NORMAL', '', 0, '2024-06-18 14:06:49', 'N', 136, '2024-06-18 14:04:11', NULL, NULL, 136, '30,33', NULL);
INSERT INTO `permissions` VALUES (391, 'COMMON', 88, '比价供应商展示', '比价供应商展示', 'admin/compare/getListGroupBySupplier', 'compare/getListGroupBySupplier', '', 0, 'N', 'NORMAL', '', 0, '2024-06-19 15:05:43', 'N', 1875271515914638372, '2024-06-19 15:05:43', NULL, NULL, 1875271515914638372, '63,88', NULL);
INSERT INTO `permissions` VALUES (392, 'PLATFORM', 365, '定标', '定标', '/admin/bidbill/decision/{id}', 'BidBill/decision', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-25 16:04:30', 'N', 136, '2024-06-25 16:04:30', NULL, NULL, 136, '354,365', NULL);
INSERT INTO `permissions` VALUES (393, 'PLATFORM', 245, '未读消息数量', '未读消息数量', '/admin/message/notReadCount', 'message/notReadCount', '', 0, 'N', 'NORMAL', '', 0, '2024-07-01 15:33:49', 'N', 136, '2024-07-01 15:33:49', NULL, NULL, 136, '294,245', NULL);
INSERT INTO `permissions` VALUES (394, 'SUPPLIER', 297, '未读消息数量', '未读消息数量', '/admin/supplier/message/notReadCount', 'SupplierMessag/notReadCount', '', 0, 'N', 'NORMAL', '', 0, '2024-07-01 15:50:19', 'N', 136, '2024-07-01 15:50:19', NULL, NULL, 136, '68,295,297', NULL);
INSERT INTO `permissions` VALUES (395, 'SUPPLIER', 378, '报名', '报名', '/admin/supplier/bidbill/signup/{id}', 'supplierbidbill/signup', '', 0, 'N', 'NORMAL', '', 0, '2024-07-04 09:13:54', 'N', 1875271515914638372, '2024-07-04 09:13:54', NULL, NULL, 1875271515914638372, '68,378', NULL);
INSERT INTO `permissions` VALUES (396, 'SUPPLIER', 378, '不报名', '不报名', '/admin/supplier/bidbill/unsignup/{id}', 'supplierBidBill/unsignup', '', 0, 'N', 'NORMAL', '', 0, '2024-07-04 09:15:05', 'N', 1875271515914638372, '2024-07-04 09:15:05', NULL, NULL, 1875271515914638372, '68,378', NULL);
INSERT INTO `permissions` VALUES (420, 'PLATFORM', 446, '退还保证金', '退还保证金', '/admin/bidbill/return/{id}', 'BidBill/returnDeposit', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:45:40', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (425, 'PLATFORM', 446, '结束竞价', '结束竞价', '/admin/bidbill/finished/{id}', 'BidBill/finished', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:45:40', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 136, '354,446', NULL);
INSERT INTO `permissions` VALUES (427, 'PLATFORM', 389, '退还保证金', '退还保证金', '/admin/bidbill/return/audit/{id}', 'BidBillPay/returnAudit', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:45:40', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 136, '354,389', NULL);
INSERT INTO `permissions` VALUES (432, 'SUPPLIER', 378, '缴纳详情', '缴纳详情', '/admin/supplier/bidbill/payinfo/{id}', 'SupplierBidBill/payInfo', '', 0, 'Y', 'NORMAL', '', 0, '2024-06-03 13:45:40', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 136, '68,378', NULL);
INSERT INTO `permissions` VALUES (444, 'PLATFORM', 446, '竞价商品导入', '竞价商品导入', '/admin/bidbill/entry/import', 'BidBill/entryImport', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-09 08:47:06', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 1875271515914638372, '354,446', NULL);
INSERT INTO `permissions` VALUES (445, 'PLATFORM', 446, '商品导入模板', '商品导入模板', '/admin/bidbill/entry/template', 'BidBill/entryTemplate', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-09 08:42:16', 'N', 136, '2024-06-03 13:45:40', NULL, NULL, 1875271515914638372, '354,446', NULL);
INSERT INTO `permissions` VALUES (446, 'PLATFORM', 354, '竞价发布', '竞价发布', '/admin/bidbill', 'BidBill/getList', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-04 13:40:33', 'N', 1875271515914638372, '2024-07-04 13:40:33', NULL, NULL, 1875271515914638372, '354', NULL);
INSERT INTO `permissions` VALUES (1001, 'PLATFORM', 0, '招标管理', '招标管理', '/admin/project', '/project/getlist', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-08 15:32:10', 'N', 1875271515914638372, '2024-07-08 15:31:46', NULL, NULL, 1875271515914638372, '0', NULL);
INSERT INTO `permissions` VALUES (1004, 'PLATFORM', 1001, '招标中心', '招标中心', '/admin/project', 'Project/getList', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:40:06', 'N', 1945082105276930210, '2024-07-11 09:40:06', NULL, NULL, 1945082105276930210, '1001', NULL);
INSERT INTO `permissions` VALUES (1005, 'PLATFORM', 1004, '招标详情', '招标详情', '/admin/project/{id}', 'Project/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:40:58', 'N', 1945082105276930210, '2024-07-11 09:40:58', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1006, 'PLATFORM', 1004, '删除招标', '删除招标', '/admin/project/delete', 'Project/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:41:39', 'N', 1945082105276930210, '2024-07-11 09:41:39', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1007, 'PLATFORM', 1004, '新增招标', '新增招标', '/admin/project/add', 'Project/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:42:21', 'N', 1945082105276930210, '2024-07-11 09:42:21', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1008, 'PLATFORM', 1004, '编辑招标', '编辑招标', '/admin/project/edited/{id}', 'Project/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:43:02', 'N', 1945082105276930210, '2024-07-11 09:43:02', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1009, 'PLATFORM', 1004, '招标作废', '招标作废', '/admin/project/invalid', 'Project/invalid', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:43:35', 'N', 1945082105276930210, '2024-07-11 09:43:35', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1011, 'PLATFORM', 1004, '立项调整', '立项调整', '/admin/project/change/{id}', 'Project/change', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:44:52', 'N', 1945082105276930210, '2024-07-11 09:44:52', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1012, 'PLATFORM', 1004, '物料模板', '物料模板', '/admin/project/entry/template', 'Project/entryTemplate', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:47:03', 'N', 1945082105276930210, '2024-07-11 09:45:51', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1013, 'PLATFORM', 1004, '物料引入', '物料引入', '/admin/project/entry/import', 'Project/entryImport', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:46:47', 'N', 1945082105276930210, '2024-07-11 09:46:47', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1014, 'COMMON', 1004, '标书详情', '标书详情', '/admin/project/doc/{id}', 'ProjectDoc/info', '', 0, 'N', 'NORMAL', '', 0, '2024-08-08 17:10:53', 'N', 1945082105276930210, '2024-07-11 09:48:12', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1015, 'PLATFORM', 1004, '标书编制', '标书编制', '/admin/project/doc/edited/{id}', 'ProjectDoc/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-11 09:49:10', 'N', 1945082105276930210, '2024-07-11 09:49:10', NULL, NULL, 1945082105276930210, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1016, 'PLATFORM', 1004, '推荐供应商', '推荐供应商', '/admin/project/suppliers', 'Project/suppliers', '', 0, 'N', 'NORMAL', '', 0, '2024-07-12 09:20:42', 'N', 1875271515914638372, '2024-07-12 09:20:42', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1017, 'PLATFORM', 1004, '招标专家', '招标专家', '/admin/project/proficients', 'Project/proficients', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-12 09:21:10', 'N', 1875271515914638372, '2024-07-12 09:21:10', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1018, 'PLATFORM', 1004, '招标小组', '招标小组', '/admin/project/members', 'Project/members', '', 0, 'N', 'NORMAL', '', 0, '2024-07-12 09:21:38', 'N', 1875271515914638372, '2024-07-12 09:21:38', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1019, 'SUPPLIER', 68, '招标管理', '招标管理', 'admin/supplier/project', 'supplier/project', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 10:30:14', 'N', 136, '2024-07-15 10:30:14', NULL, NULL, 136, '68', NULL);
INSERT INTO `permissions` VALUES (1024, 'PLATFORM', 1004, '招标入围', '招标入围', '/admin/project/shortlist/{id}', '/project/shortlist', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 13:59:02', 'N', 136, '2024-07-15 13:58:21', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1025, 'SUPPLIER', 1019, '招标查询', '招标查询', '/admin/supplier/project', '/supplierproject/getList', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 15:49:28', 'N', 136, '2024-07-15 15:49:28', NULL, NULL, 136, '68,1019', NULL);
INSERT INTO `permissions` VALUES (1026, 'PLATFORM', 1004, '标书编制详情', '标书编制详情', '/admin/project/doc/{id}', 'ProjectDoc/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:46:19', 'N', 1875271515914638372, '2024-07-15 20:46:19', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1027, 'PLATFORM', 1004, '编辑标书编制', '编辑标书编制', '/admin/project/doc/edited/{id}', 'ProjectDoc/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:46:56', 'N', 1875271515914638372, '2024-07-15 20:46:56', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1028, 'PLATFORM', 1004, '发标详情', '发标详情', '/admin/project/publish/{id}', 'ProjectPublish/info', '', 0, 'N', 'NORMAL', '', 0, '2024-09-05 20:49:06', 'N', 1875271515914638372, '2024-07-15 20:47:25', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1029, 'PLATFORM', 1004, '编辑发标', '编辑发标', '/admin/project/publish/edited/{id}', 'ProjectPublish/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:48:01', 'N', 1875271515914638372, '2024-07-15 20:48:01', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1030, 'PLATFORM', 1004, '开标详情', '开标详情', '/admin/project/open/{id}', 'ProjectOpen/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:48:28', 'N', 1875271515914638372, '2024-07-15 20:48:28', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1031, 'PLATFORM', 1004, '编辑开标', '编辑开标', '/admin/project/open/edited/{id}', 'ProjectOpen/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:48:55', 'N', 1875271515914638372, '2024-07-15 20:48:55', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1032, 'PLATFORM', 1004, '评标详情', '评标详情', '/admin/project/evaluation/{id}', 'ProjectEvaluation/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:49:23', 'N', 1875271515914638372, '2024-07-15 20:49:23', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1033, 'PLATFORM', 1004, '编辑评标', '编辑评标', '/admin/project/evaluation/edited/{id}', 'ProjectEvaluation/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:50:38', 'N', 1875271515914638372, '2024-07-15 20:50:38', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1034, 'PLATFORM', 1004, '定标详情', '定标详情', '/admin/project/decision/{id}', 'ProjectDecision/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:51:05', 'N', 1875271515914638372, '2024-07-15 20:51:05', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1035, 'PLATFORM', 1004, '编辑定标', '编辑定标', '/admin/project/decision/edited/{id}', 'ProjectDecision/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-15 20:51:33', 'N', 1875271515914638372, '2024-07-15 20:51:33', NULL, NULL, 1875271515914638372, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1036, 'SUPPLIER', 1025, '招标详情', '招标详情', '/admin/supplier/project/{id}', '/supplierproject/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-16 13:52:50', 'N', 136, '2024-07-16 13:52:50', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1037, 'SUPPLIER', 1025, '报名', '报名', '/admin/supplier/project/signup/{id}', '/SupplierProject/signUp', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-16 16:36:20', 'N', 136, '2024-07-16 16:36:20', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1038, 'SUPPLIER', 1025, ' 不报名', ' 不报名', '/admin/supplier/project/unsignup/{id}', '/SupplierProject/unsignUp', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-16 16:37:27', 'N', 136, '2024-07-16 16:37:27', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1048, 'PLATFORM', 1047, '详情', '详情', '/admin/purproject/{id}', 'PurProject/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:14:33', 'N', 136, '2024-07-17 10:14:33', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1049, 'PLATFORM', 1047, '新增', '新增', '/admin/purproject/add', 'PurProject/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:09', 'N', 136, '2024-07-17 10:15:09', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1050, 'PLATFORM', 1047, '编码', '编码', '/admin/purproject/number', 'PurProject/number', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:51', 'N', 136, '2024-07-17 10:15:51', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1051, 'PLATFORM', 1047, '编辑', '编辑', '/admin/purproject/edited/{id}', 'PurProject/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:01', 'N', 136, '2024-07-17 10:16:37', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1052, 'PLATFORM', 1047, '删除', '删除', '/admin/purproject/delete', 'PurProject/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:37', 'N', 136, '2024-07-17 10:17:37', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1053, 'PLATFORM', 1047, '禁用', '禁用', '/admin/purproject/disable', 'PurProject/disable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:14', 'N', 136, '2024-07-17 10:18:14', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1054, 'PLATFORM', 1047, '启用', '启用', '/admin/purproject/enable', 'PurProject/enable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:46', 'N', 136, '2024-07-17 10:18:46', NULL, NULL, 136, '1001,1047', NULL);
INSERT INTO `permissions` VALUES (1056, 'PLATFORM', 1055, '详情', '详情', '/admin/purtype/{id}', 'PurType/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:14:33', 'N', 136, '2024-07-17 10:14:33', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1057, 'PLATFORM', 1055, '新增', '新增', '/admin/purtype/add', 'PurType/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:09', 'N', 136, '2024-07-17 10:15:09', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1058, 'PLATFORM', 1055, '编码', '编码', '/admin/purtype/number', 'PurType/number', '', 0, 'N', 'NORMAL', '', 0, '2024-07-17 10:15:51', 'N', 136, '2024-07-17 10:15:51', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1059, 'PLATFORM', 1055, '编辑', '编辑', '/admin/purtype/edited/{id}', 'PurType/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:01', 'N', 136, '2024-07-17 10:16:37', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1060, 'PLATFORM', 1055, '删除', '删除', '/admin/purtype/delete', 'PurType/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:37', 'N', 136, '2024-07-17 10:17:37', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1061, 'PLATFORM', 1055, '禁用', '禁用', '/admin/purtype/disable', 'PurType/disable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:14', 'N', 136, '2024-07-17 10:18:14', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1062, 'PLATFORM', 1055, '启用', '启用', '/admin/purtype/enable', 'PurType/enable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:46', 'N', 136, '2024-07-17 10:18:46', NULL, NULL, 136, '1001,1055', NULL);
INSERT INTO `permissions` VALUES (1064, 'PLATFORM', 1063, '详情', '详情', '/admin/bidmode/{id}', 'BidMode/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:14:33', 'N', 136, '2024-07-17 10:14:33', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1065, 'PLATFORM', 1063, '新增', '新增', '/admin/bidmode/add', 'BidMode/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:09', 'N', 136, '2024-07-17 10:15:09', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1066, 'PLATFORM', 1063, '编码', '编码', '/admin/bidmode/number', 'BidMode/number', '', 0, 'N', 'NORMAL', '', 0, '2024-07-17 10:15:51', 'N', 136, '2024-07-17 10:15:51', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1067, 'PLATFORM', 1063, '编辑', '编辑', '/admin/bidmode/edited/{id}', 'BidMode/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:01', 'N', 136, '2024-07-17 10:16:37', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1068, 'PLATFORM', 1063, '删除', '删除', '/admin/bidmode/delete', 'BidMode/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:37', 'N', 136, '2024-07-17 10:17:37', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1069, 'PLATFORM', 1063, '禁用', '禁用', '/admin/bidmode/disable', 'BidMode/disable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:14', 'N', 136, '2024-07-17 10:18:14', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1070, 'PLATFORM', 1063, '启用', '启用', '/admin/bidmode/enable', 'BidMode/enable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:46', 'N', 136, '2024-07-17 10:18:46', NULL, NULL, 136, '1001,1063', NULL);
INSERT INTO `permissions` VALUES (1072, 'PLATFORM', 1071, '详情', '详情', '/admin/valuationmode/{id}', 'ValuationMode/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:14:33', 'N', 136, '2024-07-17 10:14:33', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1073, 'PLATFORM', 1071, '新增', '新增', '/admin/valuationmode/add', 'ValuationMode/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:09', 'N', 136, '2024-07-17 10:15:09', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1074, 'PLATFORM', 1071, '编码', '编码', '/admin/valuationmode/number', 'ValuationMode/number', '', 0, 'N', 'NORMAL', '', 0, '2024-07-17 10:15:51', 'N', 136, '2024-07-17 10:15:51', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1075, 'PLATFORM', 1071, '编辑', '编辑', '/admin/valuationmode/edited/{id}', 'ValuationMode/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:01', 'N', 136, '2024-07-17 10:16:37', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1076, 'PLATFORM', 1071, '删除', '删除', '/admin/valuationmode/delete', 'ValuationMode/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:37', 'N', 136, '2024-07-17 10:17:37', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1077, 'PLATFORM', 1071, '禁用', '禁用', '/admin/valuationmode/disable', 'ValuationMode/disable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:14', 'N', 136, '2024-07-17 10:18:14', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1078, 'PLATFORM', 1071, '启用', '启用', '/admin/valuationmode/enable', 'ValuationMode/enable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:46', 'N', 136, '2024-07-17 10:18:46', NULL, NULL, 136, '1001,1071', NULL);
INSERT INTO `permissions` VALUES (1088, 'SUPPLIER', 1025, '新增报价', '新增报价', '/admin/supplier/project/quote/{id}', '/SupplierProject/quote', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-18 15:12:09', 'N', 136, '2024-07-18 15:12:09', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1089, 'SUPPLIER', 1025, '投标修改', '投标修改', '/admin/supplier/project/quote/edited/{id}', '/supplierProject/quoteedited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-18 15:36:34', 'N', 136, '2024-07-18 15:36:34', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1090, 'SUPPLIER', 1025, '投标详情', '投标详情', '/admin/supplier/project/quote/info/{id}', '/SupplierProject/quoteinfo', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-18 16:27:00', 'N', 136, '2024-07-18 16:27:00', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1092, 'PLATFORM', 1091, '列表', '列表', '/admin/templates', 'Template/getAll', '', 0, 'N', 'NORMAL', '', 0, '2024-07-17 10:14:33', 'N', 136, '2024-07-17 10:14:33', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1093, 'PLATFORM', 1091, '详情', '详情', '/admin/template/{id}', 'Template/info', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:14:33', 'N', 136, '2024-07-17 10:14:33', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1094, 'PLATFORM', 1091, '新增', '新增', '/admin/template/add', 'Template/add', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:09', 'N', 136, '2024-07-17 10:15:09', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1095, 'PLATFORM', 1091, '编码', '编码', '/admin/template/number', 'Template/number', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:15:51', 'N', 136, '2024-07-17 10:15:51', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1096, 'PLATFORM', 1091, '编辑', '编辑', '/admin/template/edited/{id}', 'Template/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:01', 'N', 136, '2024-07-17 10:16:37', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1097, 'PLATFORM', 1091, '删除', '删除', '/admin/template/delete', 'Template/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:17:37', 'N', 136, '2024-07-17 10:17:37', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1098, 'PLATFORM', 1091, '禁用', '禁用', '/admin/template/disable', 'Template/disable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:14', 'N', 136, '2024-07-17 10:18:14', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1099, 'PLATFORM', 1091, '启用', '启用', '/admin/template/enable', 'Template/enable', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-17 10:18:46', 'N', 136, '2024-07-17 10:18:46', NULL, NULL, 136, '1001,1079,1091', NULL);
INSERT INTO `permissions` VALUES (1100, 'PLATFORM', 1004, '增补供应商', '增补供应商', '/admin/project/addshortlist/{id}', '/Project/shortlistaddData', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-18 17:12:32', 'N', 136, '2024-07-18 17:09:20', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1110, 'PLATFORM', 362, '竞价结束', '竞价结束', '/admin/bidbill/finished/{id}', 'bidbill/finished', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-24 16:22:12', 'N', 136, '2024-07-24 16:22:12', NULL, NULL, 136, '354,362', NULL);
INSERT INTO `permissions` VALUES (1111, 'PLATFORM', 1001, '定标列表', '定标列表', '/admin/project/decision', 'Project/decision', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-24 20:13:29', 'N', 136, '2024-07-24 20:13:29', NULL, NULL, 136, '1001', NULL);
INSERT INTO `permissions` VALUES (1112, 'PLATFORM', 1004, '发标详情', '发标详情', '/admin/project/publisih/{id}', 'ProjectPublish/info', '', 0, 'Y', 'DISABLED', '', 0, '2024-07-25 09:17:24', 'Y', 136, '2024-07-25 09:16:50', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1113, 'PLATFORM', 1004, '发标编辑', '发标编辑', '/admin/project/publisih/edited/{id}', 'ProjectPublish/edited', '', 0, 'Y', 'NORMAL', '', 0, '2024-07-25 11:14:15', 'N', 136, '2024-07-25 11:14:15', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1117, 'PLATFORM', 1004, '评标信息', '评标信息', '/admin/project/evaluation/list/{id}', 'ProjectEvaluation/getList', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-01 09:44:14', 'N', 136, '2024-07-29 19:17:33', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1118, 'SUPPLIER', 1025, '下载招标文件', '下载招标文件', '/admin/supplier/project/publishDownload/{id}', 'SupplierProject/publishDownload', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-02 14:49:59', 'N', 136, '2024-08-02 14:49:59', NULL, NULL, 136, '68,1019,1025', NULL);
INSERT INTO `permissions` VALUES (1119, 'PLATFORM', 1004, '作废记录', '作废记录', '/admin/project/invalid/{id}', 'Project/invalidInfo', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-05 16:04:13', 'N', 136, '2024-08-05 16:04:13', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1120, 'PLATFORM', 1111, '删除', '删除', '/admin/project/decision/delete', 'ProjectDecision/delete', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-06 13:06:47', 'N', 136, '2024-08-06 13:06:47', NULL, NULL, 136, '1001,1111', NULL);
INSERT INTO `permissions` VALUES (1121, 'PLATFORM', 1004, '招标编码', '招标编码', '/admin/project/number', 'Project/number', '', 0, 'N', 'NORMAL', '', 0, '2024-08-08 11:04:52', 'N', 136, '2024-08-08 11:04:52', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1122, 'PLATFORM', 1071, '计价模式', '计价模式', '/admin/valuationmodes', 'Valuation/getAll', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-20 08:39:29', 'N', 136, '2024-08-20 08:39:29', NULL, NULL, 136, '1001,1079,1071', NULL);
INSERT INTO `permissions` VALUES (1123, 'PLATFORM', 1004, '采购类型', '采购类型', '/admin/purtypes', 'PurType/getAll', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-20 08:48:32', 'N', 136, '2024-08-20 08:48:32', NULL, NULL, 136, '1001,1004', NULL);
INSERT INTO `permissions` VALUES (1126, 'PLATFORM', 1080, '一级采购商', '一级采购商', '/admin/orgs', 'Org/getAll', '', 0, 'Y', 'NORMAL', '', 0, '2024-08-21 09:33:08', 'N', 136, '2024-08-21 09:30:05', NULL, NULL, 1875271515914638372, '1001,1079,1080', NULL);

-- ----------------------------
-- Table structure for project
-- ----------------------------
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '招标编号',
  `bill_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '单据状态单据状态 A:暂存B:已提交C:已审核I:审核中X:已流标 F:作废',
  `setup_date` datetime(0) NULL DEFAULT NULL COMMENT '立项日期',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '招标名称',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地址',
  `answer_complete_at` datetime(0) NULL DEFAULT NULL COMMENT '答疑完成日期',
  `answered_at` datetime(0) NULL DEFAULT NULL COMMENT '答疑时间',
  `answered_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '答疑',
  `approach_date` date NULL DEFAULT NULL COMMENT '进场日期',
  `audited_date` datetime(0) NULL DEFAULT NULL COMMENT '审核日期',
  `audited_by` bigint(20) NULL DEFAULT 0 COMMENT '审核人',
  `bid_bus_talk` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '商务谈判',
  `bid_decision_date` date NULL DEFAULT NULL COMMENT '定标日期',
  `bid_decision` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '定标',
  `bid_document` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '标书编制',
  `bid_evaluation_date` date NULL DEFAULT NULL COMMENT '评标日期',
  `bid_evaluation` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '评标',
  `bid_mode_id` bigint(20) NULL DEFAULT NULL COMMENT '采购方式 1:公开招标 2:邀请招标',
  `bid_open_deadline` datetime(0) NULL DEFAULT NULL COMMENT '截标开标时间',
  `bid_open` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '开标',
  `bid_project` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '招标立项',
  `bid_publish_date` date NULL DEFAULT NULL COMMENT '发标日期',
  `bid_publish` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '发标',
  `bid_valuation_id` bigint(20) NULL DEFAULT 0 COMMENT '计价模式 1.清单计价 2.定额计价 3.项目管理模式',
  `click_content` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `commercial_doc_end_date` datetime(0) NULL DEFAULT NULL COMMENT '商务标编制完成日期',
  `contact_id` bigint(20) NULL DEFAULT 0 COMMENT '招标联系人',
  `contact_tel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '联系电话',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `created_by` bigint(20) NULL DEFAULT 0 COMMENT '创建人',
  `current_step` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '当前阶段 A:招标立项 B:供方入围 C:标书编制D:标底编制E:招标交底F:发标G:答疑 H:开标I:评标J:商务谈判K:定标',
  `design_drawing_end_date` datetime(0) NULL DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '电子邮箱',
  `enable_list` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `enable_multi_section` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '多标段',
  `enroll_deadline` datetime(0) NULL DEFAULT NULL COMMENT '投标报名截止时间',
  `entity_type_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '实体类型ID',
  `entrustment_org_unit` bigint(20) NULL DEFAULT 0 COMMENT '委托采购组织',
  `entrustment_supplier` bigint(20) NULL DEFAULT 0 COMMENT '委托代理机构',
  `entrustment_way` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '委托方式 1.不委托、2.委托代理机构、3.委托采购组织',
  `fax` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '传真',
  `invalided_at` datetime(0) NULL DEFAULT NULL COMMENT '作废时间',
  `invalided_by` bigint(20) NULL DEFAULT 0 COMMENT '作废人',
  `invalided_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '处理意见',
  `is_filter` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否使用供应商过滤',
  `is_separate_doc` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '标书分卷编制',
  `license_requirements` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '证照要求',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  `pur_description` varchar(1004) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '采购说明',
  `pur_mode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '采购模式',
  `pur_project_set` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '采购项目集',
  `pur_type_id` bigint(20) NULL DEFAULT NULL COMMENT '采购类型 1.清单采购 2.项目采购',
  `qualification_required` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '资质要求',
  `registered_capital` decimal(23, 10) NULL DEFAULT NULL COMMENT '注册资金(万元)',
  `required_cat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求分类',
  `required_evel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '要求等级',
  `required_list` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `source_project_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '来源id',
  `supplier_invi_end_date` datetime(0) NULL DEFAULT NULL COMMENT '入围完成日期',
  `supplier_invitation` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '供方入围',
  `technical_doc_end_date` datetime(0) NULL DEFAULT NULL COMMENT '技术标编制完成日期',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '修改人',
  `shortlist_at` datetime(0) NULL DEFAULT NULL COMMENT '实际入围时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PROJECT_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `setup_date`(`setup_date`) USING BTREE,
  INDEX `current_step`(`current_step`) USING BTREE,
  INDEX `org_id`(`org_id`) USING BTREE,
  INDEX `bid_evaluation_date`(`bid_evaluation_date`) USING BTREE,
  INDEX `enroll_deadline`(`enroll_deadline`) USING BTREE,
  INDEX `bid_open_deadline`(`bid_open_deadline`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标立项-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project
-- ----------------------------

-- ----------------------------
-- Table structure for project_attach
-- ----------------------------
DROP TABLE IF EXISTS `project_attach`;
CREATE TABLE `project_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件分组 技术标文件:TECHNICAL 商务标文件:COMMERCIAL 招标立项:PROJECT  OPEN 开标 EVALUATION:评标 DECISION:定标 PUBLISH:发标 INVITATION:入围 DOCUMENT:标书编制',
  `project_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `evaluation_id` bigint(20) NULL DEFAULT NULL,
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `inquiry_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_attach
-- ----------------------------

-- ----------------------------
-- Table structure for project_bid_attach
-- ----------------------------
DROP TABLE IF EXISTS `project_bid_attach`;
CREATE TABLE `project_bid_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group` enum('TECHNICAL','COMMITMENT_LETTER','OTHER','COMMERCIAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件分组 技术标文件:TECHNICAL 商务标文件:COMMERCIAL COMMITMENT_LETTER:投标承诺函 OTHER:其他附件',
  `project_id` bigint(20) NULL DEFAULT NULL COMMENT '供应商ID',
  `supplier_id` bigint(20) NULL DEFAULT NULL,
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `quote_id` bigint(20) NULL DEFAULT NULL COMMENT '报价id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `inquiry_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '投标附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_bid_attach
-- ----------------------------

-- ----------------------------
-- Table structure for project_bid_entry
-- ----------------------------
DROP TABLE IF EXISTS `project_bid_entry`;
CREATE TABLE `project_bid_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NULL DEFAULT 0,
  `quote_id` bigint(20) NULL DEFAULT NULL,
  `entry_id` bigint(20) NULL DEFAULT 0 COMMENT '采购明细ID',
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `material_id` bigint(20) NULL DEFAULT 0,
  `qty` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `inclu_tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `inclu_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_rate` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `except_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `created_by` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `deleted_flag` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N',
  `tax_rate_id` bigint(20) NULL DEFAULT 0,
  `purentry_content` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pur_project_id` bigint(20) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_DECIBACKDE_FENTRY_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '投标报价明细-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_bid_entry
-- ----------------------------

-- ----------------------------
-- Table structure for project_bid_quote
-- ----------------------------
DROP TABLE IF EXISTS `project_bid_quote`;
CREATE TABLE `project_bid_quote`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NULL DEFAULT 0,
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `inclu_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '投标报价（含税价）',
  `tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '税额',
  `except_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '投标报价（不含税价）',
  `project_manager` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '项目经理',
  `work_load` int(10) NULL DEFAULT 0 COMMENT '工期（天数）',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '报价说明',
  `bid_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'A' COMMENT '投标状态 A暂存 C:已提交',
  `tended_at` datetime(0) NULL DEFAULT NULL,
  `created_by` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `deleted_flag` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N',
  `tax_rate` decimal(20, 4) NOT NULL DEFAULT 0.0000 COMMENT '税率',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_DECIBACKDE_FENTRY_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '投标报价' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_bid_quote
-- ----------------------------

-- ----------------------------
-- Table structure for project_bottom_make
-- ----------------------------
DROP TABLE IF EXISTS `project_bottom_make`;
CREATE TABLE `project_bottom_make`  (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `bill_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ',
  `bill_status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ',
  `created_by` bigint(20) NOT NULL DEFAULT 0,
  `updated_by` bigint(20) NOT NULL DEFAULT 0,
  `audited_by` bigint(20) NOT NULL DEFAULT 0,
  `audited_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `org_id` bigint(20) NOT NULL DEFAULT 0,
  `project_id` bigint(20) NOT NULL DEFAULT 0,
  `bid_model_id` bigint(20) NOT NULL DEFAULT 0,
  `doc_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ',
  `evaluate_decide_way` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ',
  `bottom_make_at` datetime(0) NULL DEFAULT NULL,
  `bottom_amount` decimal(23, 10) NOT NULL DEFAULT 0.0000000000,
  `entity_type_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ',
  `setup_date` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_BOTTOM_MAKE_FBILLNO`(`bill_no`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '标底编制-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_bottom_make
-- ----------------------------

-- ----------------------------
-- Table structure for project_decision
-- ----------------------------
DROP TABLE IF EXISTS `project_decision`;
CREATE TABLE `project_decision`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `org_id` bigint(20) NULL DEFAULT 0,
  `bill_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `created_by` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `audited_by` bigint(20) NULL DEFAULT 0,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `audited_at` datetime(0) NULL DEFAULT NULL,
  `project_id` bigint(20) NULL DEFAULT 0,
  `exc_tax_control_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `control_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `bid_open_deadline` datetime(0) NULL DEFAULT NULL,
  `evaluate_decide_way` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `exc_tax_amount` decimal(19, 6) NULL DEFAULT 0.000000,
  `need_flag_new_supplier` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `entity_type_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `base_price` decimal(19, 6) NULL DEFAULT 0.000000,
  `fk_erui_approve` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `fk_erui_product_owne` bigint(20) NULL DEFAULT NULL,
  `fk_erui_fqr` bigint(20) NULL DEFAULT NULL,
  `fk_erui_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `data_source` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `split_bill_no` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `input_param` bigint(20) NULL DEFAULT 0,
  `synxk_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `decision_status` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '定标状态 A:暂存 ;C:已提交',
  `decision_at` datetime(0) NULL DEFAULT NULL COMMENT '定标日期',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_id`(`project_id`) USING BTREE,
  INDEX `IDX_BID_DECISION_FBILLNO`(`bill_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '定标-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_decision
-- ----------------------------

-- ----------------------------
-- Table structure for project_decision_entry
-- ----------------------------
DROP TABLE IF EXISTS `project_decision_entry`;
CREATE TABLE `project_decision_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) NULL DEFAULT 0,
  `project_id` bigint(20) NULL DEFAULT 0,
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `seq` int(11) NULL DEFAULT 0,
  `qty` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `inclu_tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `inclu_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_rate_id` bigint(20) NULL DEFAULT NULL,
  `tax_rate` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `except_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `cost_rate` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `adopt_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '是否中标  1 中标 2 未中标',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unx_pro_entry_supplier_id`(`project_id`, `entry_id`, `supplier_id`) USING BTREE,
  INDEX `IDX_BID_DECIFINALDET_FENTRY_id`(`entry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '最终报价明细-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_decision_entry
-- ----------------------------

-- ----------------------------
-- Table structure for project_decision_final
-- ----------------------------
DROP TABLE IF EXISTS `project_decision_final`;
CREATE TABLE `project_decision_final`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) NULL DEFAULT 0,
  `project_id` bigint(20) NULL DEFAULT 0,
  `seq` int(11) NULL DEFAULT 0,
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `material_id` bigint(20) NULL DEFAULT 0,
  `qty` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `inclu_tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `inclu_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_rate` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `except_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000,
  `cost_rate` decimal(23, 10) NULL DEFAULT 0.0000000000,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_DECIFINALDET_FENTRY_id`(`entry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '最终报价明细-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_decision_final
-- ----------------------------

-- ----------------------------
-- Table structure for project_decision_supplier
-- ----------------------------
DROP TABLE IF EXISTS `project_decision_supplier`;
CREATE TABLE `project_decision_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NULL DEFAULT 0,
  `seq` int(11) NULL DEFAULT 0,
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `technical_opinion` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '技术标评标意见',
  `commercial_opinion` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '',
  `technical_score` decimal(16, 9) NULL DEFAULT 0.000000000 COMMENT '技术标评分',
  `commercial_score` decimal(16, 9) NULL DEFAULT 0.000000000 COMMENT '商务标评分',
  `total_score` decimal(16, 9) NULL DEFAULT NULL COMMENT '综合得分',
  `tax_rate` decimal(20, 4) NOT NULL DEFAULT 0.0000 COMMENT '税率',
  `inclu_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '投标报价（含税价）',
  `tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '税额',
  `exc_tax_amount` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '投标报价（不含税价）',
  `adopt_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '1 中标  2 未中标',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unx_pro_supplier_id`(`project_id`, `supplier_id`) USING BTREE,
  INDEX `IDX_BID_BIDEVALENTRY_FSUPP_id`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '评标供应商分录-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_decision_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for project_document
-- ----------------------------
DROP TABLE IF EXISTS `project_document`;
CREATE TABLE `project_document`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `bill_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '单据状态单据状态 A:暂存；已提交:C',
  `document_at` datetime(0) NULL DEFAULT NULL COMMENT '标书编制',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `inquiry_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '标书编制' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_document
-- ----------------------------

-- ----------------------------
-- Table structure for project_entry
-- ----------------------------
DROP TABLE IF EXISTS `project_entry`;
CREATE TABLE `project_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '采购明细.id',
  `project_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '招标ID',
  `pur_project_id` bigint(20) NULL DEFAULT NULL COMMENT '采购明细.采购项目',
  `work_load` int(11) NULL DEFAULT 0 COMMENT '采购明细.总工期（天）',
  `purentry_content` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '采购明细.招标内容',
  `comment` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '说明',
  `seq` int(11) NULL DEFAULT 0 COMMENT '分录行号',
  `program_contract_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `control_amount` decimal(20, 4) NULL DEFAULT NULL COMMENT '采购明细.采购控制金额',
  `tax_rate_id` bigint(20) NULL DEFAULT NULL COMMENT '税率ID',
  `tax_rate` decimal(10, 2) NULL DEFAULT NULL COMMENT '税率',
  `control_vat` decimal(20, 4) NULL DEFAULT NULL COMMENT '采购明细.采购控制税额',
  `ctrl_amt_except_vat` decimal(20, 4) NULL DEFAULT NULL COMMENT '采购明细.采购控制金额（不含税）',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `checked_at` datetime(0) NULL DEFAULT NULL COMMENT '审核时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `checked_by` bigint(20) NULL DEFAULT NULL COMMENT '审核人',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '封禁人',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '封禁时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PROJECTENTRY_FENTRYID`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '采购明细-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_entry
-- ----------------------------

-- ----------------------------
-- Table structure for project_invitation
-- ----------------------------
DROP TABLE IF EXISTS `project_invitation`;
CREATE TABLE `project_invitation`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `org_id` bigint(20) NULL DEFAULT 0,
  `project_id` bigint(20) NULL DEFAULT 0,
  `template_id` bigint(20) NULL DEFAULT 0,
  `name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `deadline_date` datetime(0) NULL DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `enable` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `publish_date` datetime(0) NULL DEFAULT NULL,
  `entity_type_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `user_field` bigint(20) NULL DEFAULT 0,
  `enable_new` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `release_user` bigint(20) NULL DEFAULT 0,
  `release_status` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `checked_by` bigint(20) NULL DEFAULT 0,
  `checked_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_invitation_billno`(`number`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '邀请函列表-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_invitation
-- ----------------------------

-- ----------------------------
-- Table structure for project_invitation_entry
-- ----------------------------
DROP TABLE IF EXISTS `project_invitation_entry`;
CREATE TABLE `project_invitation_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `invitation_id` bigint(20) NULL DEFAULT 0,
  `seq` int(11) NULL DEFAULT 0,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `content_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `invitation_status` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `confirm_man` bigint(20) NULL DEFAULT 0,
  `confirm_date` datetime(0) NULL DEFAULT NULL,
  `invitation_user` bigint(20) NULL DEFAULT 0,
  `user_field` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_invitation_entry_id`(`invitation_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商邀请函-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_invitation_entry
-- ----------------------------

-- ----------------------------
-- Table structure for project_member
-- ----------------------------
DROP TABLE IF EXISTS `project_member`;
CREATE TABLE `project_member`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '招标ID',
  `seq` int(11) NULL DEFAULT 0 COMMENT '招标小组.分录行号',
  `user_id` bigint(20) NULL DEFAULT 0 COMMENT '招标小组.姓名',
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '招标小组.职位编码',
  `is_director` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '招标小组.是否负责人',
  `comment` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '招标小组.说明',
  `resp_business` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '招标小组.经办业务',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `purchaser_id` bigint(20) NULL DEFAULT NULL COMMENT '组织ID',
  `purchaser_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '组织名称',
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '姓名',
  `user_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PROJECTMEMBERENTRY_FID`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标小组-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_member
-- ----------------------------

-- ----------------------------
-- Table structure for project_online_eval
-- ----------------------------
DROP TABLE IF EXISTS `project_online_eval`;
CREATE TABLE `project_online_eval`  (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `bill_status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `created_by` bigint(20) NOT NULL DEFAULT 0,
  `updated_by` bigint(20) NOT NULL DEFAULT 0,
  `audited_by` bigint(20) NOT NULL DEFAULT 0,
  `audited_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `org_id` bigint(20) NOT NULL DEFAULT 0,
  `project_id` bigint(20) NOT NULL DEFAULT 0,
  `bid_evaluation_date` datetime(0) NULL DEFAULT NULL,
  `technical` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `commercial` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `score_mode` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `entity_type_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `scoretype` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `bidevaluator` bigint(20) NOT NULL DEFAULT 0,
  `evaltype` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `rounds` int(11) NOT NULL DEFAULT 0,
  `list_rounds` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `source_bill_id` bigint(20) NOT NULL DEFAULT 0,
  `supplier_invitation_id` bigint(20) NOT NULL DEFAULT 0,
  `bid_publish_id` bigint(20) NOT NULL DEFAULT 0,
  `bid_open_id` bigint(20) NOT NULL DEFAULT 0,
  `source_bill_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `evaluated_method` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_ONLINEBIDEVAL_BILLNO`(`bill_no`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '在线评标-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_online_eval
-- ----------------------------

-- ----------------------------
-- Table structure for project_open
-- ----------------------------
DROP TABLE IF EXISTS `project_open`;
CREATE TABLE `project_open`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '招标编号',
  `project_id` bigint(20) NULL DEFAULT NULL COMMENT '招标ID',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  `base_price` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '标底金额（元）',
  `bill_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '单据状态单据状态 A:暂存B:已提交C:已审核I:审核中X:已流标 F:作废',
  `eval_template` bigint(20) NULL DEFAULT NULL COMMENT '评标模板',
  `evaluate_decide_way_id` bigint(20) NULL DEFAULT 0 COMMENT '评定标方法 1.综合评分法、2.合理低价法',
  `evaluated_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '1.定量评审、2.定性评审、3.定量+定性评审',
  `bid_open_deadline` datetime(0) NULL DEFAULT NULL COMMENT '截标开标时间',
  `open_status` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开标状态 A:暂存 ;C:已提交',
  `open_type_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开标方式 描述',
  `bid_open_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '开标方式 1.先开技术、后开商务 2. 统一开标',
  `publish_id` bigint(20) NULL DEFAULT 0 COMMENT '发布人',
  `score_mode` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分值标准 1 标准分制 2 权重制',
  `score_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分值方式 1:技术标加商务标满分100分 2:技术标和商务标满分各100分 ',
  `tech_weight` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '技术标权重',
  `com_weight` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '商务标权重',
  `open_at` datetime(0) NULL DEFAULT NULL,
  `created_by` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_id`(`project_id`) USING BTREE,
  INDEX `IDX_BID_BIDOPEN_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `IDX_BID_BIDOPEN_FBIDEVALTEMP`(`eval_template`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '开标' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_open
-- ----------------------------

-- ----------------------------
-- Table structure for project_open_proficient
-- ----------------------------
DROP TABLE IF EXISTS `project_open_proficient`;
CREATE TABLE `project_open_proficient`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NOT NULL DEFAULT 0,
  `seq` int(11) NULL DEFAULT 0,
  `technical_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N' COMMENT '技术标  Y 是 N 否',
  `commercial_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N' COMMENT '商务标   Y 是 N 否',
  `proficient_id` bigint(20) NULL DEFAULT 0,
  `gender` enum('FEMALE','MALE','SECRECY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'SECRECY' COMMENT '性别',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '专家来源',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '说明',
  `major_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '专业分类',
  `proficient_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机号',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `T_BIDPRO_PROFICIENT_IND`(`proficient_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '开标评标专家' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_open_proficient
-- ----------------------------

-- ----------------------------
-- Table structure for project_open_score
-- ----------------------------
DROP TABLE IF EXISTS `project_open_score`;
CREATE TABLE `project_open_score`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `seq` int(11) NULL DEFAULT 0 COMMENT '分录行号',
  `project_id` bigint(20) NULL DEFAULT 0 COMMENT '招标ID',
  `score_id` bigint(20) NULL DEFAULT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '类别',
  `score_item` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评分项',
  `score_standard` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评分标准',
  `score` decimal(20, 2) NULL DEFAULT 0.00 COMMENT '标准分',
  `weight` decimal(20, 2) NULL DEFAULT 0.00,
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_OPENEVALITEMENTRY_F_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '评标项' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_open_score
-- ----------------------------

-- ----------------------------
-- Table structure for project_open_supplier
-- ----------------------------
DROP TABLE IF EXISTS `project_open_supplier`;
CREATE TABLE `project_open_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `seq` int(11) NOT NULL DEFAULT 0,
  `project_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '招标ID',
  `supplier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `supplier_contact` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商联系人',
  `contact_phone` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '电话',
  `contact_email` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商联系邮箱',
  `supplier_comment` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商评价',
  `supplier_source` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商来源',
  `supplier_name` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商名称',
  `supplier_deposit` decimal(20, 2) NULL DEFAULT 0.00 COMMENT '投标保证金金额',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '当前状态F:已中标G:未中标H:报名截止K:应缴未缴L:待缴费N:未报名T:待报名WCY:未参与Y:已报名',
  `is_tender` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否投标 0 否 1 是',
  `is_inval_id` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否淘汰 0 否 1 是',
  `inval_reason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '淘汰原因',
  `tended_at` datetime(0) NULL DEFAULT NULL COMMENT '投标日期',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `pay_flag` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '未缴纳:N 保证金：EARNEST 标书费：DOCUMENT , 已缴纳:Y',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_id`(`project_id`, `supplier_id`) USING BTREE,
  INDEX `IDX_BID_PROJSUPPDETA_FENTRYID`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商回标信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_open_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for project_pay
-- ----------------------------
DROP TABLE IF EXISTS `project_pay`;
CREATE TABLE `project_pay`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ' ' COMMENT '单据编号',
  `project_id` bigint(20) NULL DEFAULT NULL COMMENT '竞价单id',
  `project_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ' ' COMMENT '竞价项目单号',
  `project_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ' ' COMMENT '竞价项目名称',
  `org_id` bigint(20) NULL DEFAULT NULL COMMENT '组织',
  `supplier_id` bigint(20) NULL DEFAULT NULL COMMENT '供应商',
  `sure_amount` decimal(23, 2) NULL DEFAULT NULL COMMENT '应缴金额',
  `real_amount` decimal(23, 2) NULL DEFAULT NULL COMMENT '缴纳金额',
  `bill_status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'A:未缴费,B:已缴费未确认,C:已缴费已确认,D:缴费已打回,F:已退费',
  `return_status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT '退还状态 E:退费中 F:已退费',
  `return_id` bigint(20) NULL DEFAULT NULL COMMENT '退保证金人',
  `return_date` datetime(0) NULL DEFAULT NULL COMMENT '退保证金时间',
  `return_certificate` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '退款凭证',
  `return_certificate_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' ' COMMENT '款项类型保证金：EARNEST标书费：DOCUMENT',
  `certificate_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `certificate` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '缴费凭证',
  `remark` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `pay_date` datetime(0) NULL DEFAULT NULL COMMENT '缴纳时间',
  `pay_id` bigint(20) NULL DEFAULT NULL COMMENT '缴纳人',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `audited_by` bigint(20) NULL DEFAULT NULL COMMENT '审核人',
  `audited_at` datetime(0) NULL DEFAULT NULL COMMENT '审核日期',
  `contact_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `contact_phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `contact_email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_id`(`project_id`, `supplier_id`, `type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '招标缴费信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_pay
-- ----------------------------

-- ----------------------------
-- Table structure for project_publish
-- ----------------------------
DROP TABLE IF EXISTS `project_publish`;
CREATE TABLE `project_publish`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `publish_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '单据状态单据状态 A:暂存；已提交:C',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '说明',
  `publish_at` datetime(0) NULL DEFAULT NULL COMMENT '发标日期',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `inquiry_id`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '发标' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_publish
-- ----------------------------

-- ----------------------------
-- Table structure for project_sub
-- ----------------------------
DROP TABLE IF EXISTS `project_sub`;
CREATE TABLE `project_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NOT NULL,
  `total_control` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '采购控制总金额',
  `total_ctrl_exc_vat` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '采购控制总金额（不含税）',
  `is_rate_bidding` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '费率招标',
  `invitation_deadline` datetime(0) NULL DEFAULT NULL COMMENT '邀请函确认截止时间',
  `is_allow_revoke` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '允许撤标',
  `is_material_pur` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否材料采购',
  `bid_bus_talk` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '商务谈判',
  `bid_bus_talk_date` date NULL DEFAULT NULL COMMENT '商务谈判日期',
  `bid_open_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开标方式 1.先开技术、后开商务 2. 统一开标',
  `tender_fee` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '标书费',
  `deposit` decimal(20, 2) NULL DEFAULT 0.00 COMMENT '投标保证金',
  `deposit_stage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '保证金收取阶段',
  `is_deposit` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否按照供应商收取',
  `need_flag_new_supplier` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '是否需要标记新增的供应商',
  `entity_type_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '实体类型ID',
  `doc_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '招标范围 1.技术标+商务标 2.仅商务标',
  `evaluate_decide_way_id` bigint(20) NULL DEFAULT 0 COMMENT '评定标方法 1.综合评分法、2.合理低价法',
  `tech_weight` decimal(20, 2) NULL DEFAULT 0.00 COMMENT '技术标权重',
  `com_weight` decimal(20, 2) NULL DEFAULT 0.00 COMMENT '商务标权重',
  `is_online_eval` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '启用在线评标',
  `bid_eval_template` bigint(20) NULL DEFAULT 0 COMMENT '评标模板',
  `score_mode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '分值标准',
  `score_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '分值方式',
  `extract_reco_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '抽取记录id',
  `bid_bottom_make` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '启用标底编制',
  `clarificaiton` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '启用招标交底',
  `bid_bottom_make_date` datetime(0) NULL DEFAULT NULL COMMENT '标底编制完成日期',
  `clarificaiton_date` datetime(0) NULL DEFAULT NULL COMMENT '招标交底完成日期',
  `evaluated_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '1.定量评审、2.定性评审、3.定量+定性评审',
  `charging_stage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '保证金收取阶段',
  `is_supplier_get` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '按供应商收取',
  `current_bill_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '招标中心当前阶段状态',
  `id_and_status` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '招标中心当前阶段状态',
  `rebm_current_bill_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '数据来源',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `investigate` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '入围考察分析',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_project_id`(`project_id`) USING BTREE,
  INDEX `IDX_BID_PROJECT_A_FTOTALC`(`total_control`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '招标立项-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_sub
-- ----------------------------

-- ----------------------------
-- Table structure for project_supplier
-- ----------------------------
DROP TABLE IF EXISTS `project_supplier`;
CREATE TABLE `project_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `seq` int(11) NOT NULL DEFAULT 0,
  `project_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '招标ID',
  `supplier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `supplier_contact` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商联系人',
  `contact_phone` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '电话',
  `contact_email` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商联系邮箱',
  `supplier_fax` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商联系传真',
  `supplier_statistic` bigint(20) NULL DEFAULT 0 COMMENT '供应商统计',
  `winning_num` bigint(20) NULL DEFAULT 0 COMMENT '中标次数',
  `nomination_num` bigint(20) NULL DEFAULT NULL,
  `supplier_comment` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商评价',
  `supplier_source` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商来源',
  `supplier_name` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商名称',
  `invitation_status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'A' COMMENT '邀请状态  A:未发送  C:已发送 N:已拒绝 Y:已接受',
  `supplier_deposit` decimal(20, 2) NULL DEFAULT 0.00 COMMENT '应收供应商保证金',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `pay_flag` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '未缴费N 保证金：EARNEST标书费：DOCUMENT , 缴费完成 Y',
  `doc_download_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否已下载标书文件',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '当前状态X:已流标 N:不报名 Y:已报名WCY:待报名 E:已入围 J:未入围 K:待投标 I:已投标 H:弃标 F:中标 G:未中标',
  `enroll_id` bigint(20) NULL DEFAULT NULL COMMENT '报名人',
  `enroll_status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'WCY' COMMENT '报名状态  N:不报名 Y:已报名WCY:待报名',
  `is_tender` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '2' COMMENT '是否投标 1 是  2否',
  `winning_at` datetime(0) NULL DEFAULT NULL COMMENT '中标时间',
  `winning_amount` decimal(20, 2) NULL DEFAULT NULL COMMENT '中标金额',
  `tended_at` datetime(0) NULL DEFAULT NULL,
  `tended_by` bigint(20) NULL DEFAULT NULL COMMENT '投标人',
  `shortlist_flag` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '入围标识',
  `shortlist_describe` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '入围描述',
  `shortlist_at` datetime(0) NULL DEFAULT NULL COMMENT '入围时间',
  `enroll_at` datetime(0) NULL DEFAULT NULL COMMENT '报名时间',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '报名备注',
  `tender_fee` decimal(23, 10) NULL DEFAULT NULL COMMENT '应收标书费',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PROJSUPPDETA_FENTRYID`(`project_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商信息-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for project_supplier_download
-- ----------------------------
DROP TABLE IF EXISTS `project_supplier_download`;
CREATE TABLE `project_supplier_download`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) NULL DEFAULT 0 COMMENT '招标ID',
  `supplier_id` bigint(20) NULL DEFAULT 0 COMMENT '供应商ID',
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件分组 技术标文件:TECHNICAL 商务标文件:COMMERCIAL,PUBLISH_DOWNLOAD:商务技术打包文件',
  `doc_id` bigint(20) NULL DEFAULT NULL COMMENT '文档ID',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PROJSUPPDETA_FENTRYID`(`project_id`, `supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商信息-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_supplier_download
-- ----------------------------

-- ----------------------------
-- Table structure for project_supplier_invitation
-- ----------------------------
DROP TABLE IF EXISTS `project_supplier_invitation`;
CREATE TABLE `project_supplier_invitation`  (
  `id` bigint(20) NOT NULL,
  `org_id` bigint(20) NOT NULL DEFAULT 0,
  `creator_id` bigint(20) NOT NULL DEFAULT 0,
  `modifier_id` bigint(20) NOT NULL DEFAULT 0,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `bill_status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `auditor_id` bigint(20) NOT NULL DEFAULT 0,
  `audit_date` datetime(0) NULL DEFAULT NULL,
  `bidproject_id` bigint(20) NOT NULL DEFAULT 0,
  `supplierqty` bigint(20) NOT NULL DEFAULT 0,
  `bill_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `needflagnewsupplier` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `entitytype_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `supplierenrollqty` bigint(20) NOT NULL DEFAULT 0,
  `rounds` int(11) NOT NULL DEFAULT 0,
  `listrounds` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bidstep` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `sourcebill_id` bigint(20) NOT NULL DEFAULT 0,
  `listcurrentstep` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `listprojectname` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `sourcebill_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sortfield` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_SUPPINVI_FBILL_no`(`bill_no`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '供方入围-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_supplier_invitation
-- ----------------------------

-- ----------------------------
-- Table structure for project_supplier_statistic
-- ----------------------------
DROP TABLE IF EXISTS `project_supplier_statistic`;
CREATE TABLE `project_supplier_statistic`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL,
  `invited_qty` bigint(20) NOT NULL DEFAULT 0 COMMENT '邀请次数',
  `won_qty` bigint(20) NOT NULL DEFAULT 0 COMMENT '中标次数',
  `nomination_qty` bigint(20) NOT NULL DEFAULT 0 COMMENT '入围次数',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `project_SUPPSTAT_FSUPPLIER_id`(`supplier_id`, `org_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '供应商统计-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_supplier_statistic
-- ----------------------------

-- ----------------------------
-- Table structure for project_template
-- ----------------------------
DROP TABLE IF EXISTS `project_template`;
CREATE TABLE `project_template`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `name` varchar(104) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `created_by` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `org_id` bigint(20) NULL DEFAULT 0,
  `type_id` bigint(20) NULL DEFAULT 0,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `content_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `default` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `use_org` bigint(20) NULL DEFAULT 0,
  `checked_by` bigint(20) NULL DEFAULT 0,
  `checked_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_TEMPLATEMANAGE_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '函件公告模板' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_template
-- ----------------------------

-- ----------------------------
-- Table structure for project_template_type
-- ----------------------------
DROP TABLE IF EXISTS `project_template_type`;
CREATE TABLE `project_template_type`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `long_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `full_name` varchar(104) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `name` varchar(104) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `parent_id` bigint(20) NOT NULL DEFAULT 0,
  `model_type` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `entity_type_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `is_leaf` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `level` bigint(20) NOT NULL DEFAULT 0,
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_bid_templatetype_fnumber`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '函件公告模板类型' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_template_type
-- ----------------------------

-- ----------------------------
-- Table structure for project_thanks
-- ----------------------------
DROP TABLE IF EXISTS `project_thanks`;
CREATE TABLE `project_thanks`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bid_mode_id` bigint(20) NULL DEFAULT NULL,
  `org_id` bigint(20) NULL DEFAULT NULL,
  `project_id` bigint(20) NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `enable` bigint(20) NULL DEFAULT 0,
  `template_id` bigint(20) NULL DEFAULT 0,
  `publish_date` datetime(0) NULL DEFAULT NULL,
  `created_by` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '感谢信-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_thanks
-- ----------------------------

-- ----------------------------
-- Table structure for project_thanks_entry
-- ----------------------------
DROP TABLE IF EXISTS `project_thanks_entry`;
CREATE TABLE `project_thanks_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `thanks_id` bigint(20) NOT NULL DEFAULT 0,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `supplier_id` bigint(20) NULL DEFAULT 0,
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `thanks_id`(`thanks_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商感谢信-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project_thanks_entry
-- ----------------------------

-- ----------------------------
-- Table structure for pur_project
-- ----------------------------
DROP TABLE IF EXISTS `pur_project`;
CREATE TABLE `pur_project`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `name` varchar(84) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `org_id` bigint(20) NULL DEFAULT 0,
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `is_pre_setting` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PURPROJECT_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '采购项目-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pur_project
-- ----------------------------
INSERT INTO `pur_project` VALUES (4, 'tyxm_001', '通用项目', 100000, '1', 'C', 136, 136, '2024-08-19 16:09:52', '2024-08-19 16:09:52', '1');
INSERT INTO `pur_project` VALUES (12, 'cgxm', '采购项目', 1181122624508396544, '1', 'C', 136, 136, '2024-08-20 09:00:36', '2024-08-20 09:00:36', '0');

-- ----------------------------
-- Table structure for pur_type
-- ----------------------------
DROP TABLE IF EXISTS `pur_type`;
CREATE TABLE `pur_type`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '采购类型编码',
  `org_id` bigint(20) NOT NULL DEFAULT 0,
  `name` varchar(84) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '采购类型名称',
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '是否启用',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `is_pre_setting` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_PURTYPE_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '采购类型-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pur_type
-- ----------------------------
INSERT INTO `pur_type` VALUES (2, 'CGLX2024071700002', 100000, '清单采购', '1', 'C', 136, 136, '2024-08-19 16:20:27', '2024-08-19 16:20:27', '0');
INSERT INTO `pur_type` VALUES (3, 'CGLX2024071700001', 100000, '项目采购', '1', 'C', 136, 136, '2024-08-19 16:08:46', '2024-08-19 16:08:46', '1');

-- ----------------------------
-- Table structure for purchaser
-- ----------------------------
DROP TABLE IF EXISTS `purchaser`;
CREATE TABLE `purchaser`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `purchaser_type` enum('ORG','PLATFORM','PURCHASER') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'PURCHASER' COMMENT '采购商类型 ORG组织 PURCHASER:采购商 PLATFORM 平台',
  `number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '采购商编码',
  `parent_id` bigint(20) NULL DEFAULT 0 COMMENT '父采购商ID',
  `parent_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `bottom_id` bigint(20) NULL DEFAULT NULL COMMENT '组织机构最底层采购商',
  `enable` tinyint(1) NULL DEFAULT 1 COMMENT '是否启用 ',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '采购商名称',
  `long_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '长名称',
  `contact_name` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '联系人姓名',
  `contact_phone` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '联系人电话',
  `contact_email` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '联系人邮箱',
  `describe` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '描述',
  `province_id` bigint(20) NULL DEFAULT NULL COMMENT '省 ID',
  `city_id` bigint(20) NULL DEFAULT NULL COMMENT '市ID',
  `contact_address` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '联系地址',
  `created_by` bigint(20) NULL DEFAULT 0 COMMENT '创建人ID',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '修改人ID',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '封禁人',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '封禁时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'APPROVED' COMMENT '状态DRAFT: 草稿  REVIEW 待审核 APPROVING 审核中  APPROVED 通过  INVALID 驳回',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_number`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '采购商' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of purchaser
-- ----------------------------

-- ----------------------------
-- Table structure for purchaser_business
-- ----------------------------
DROP TABLE IF EXISTS `purchaser_business`;
CREATE TABLE `purchaser_business`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `purchaser_id` bigint(20) NOT NULL COMMENT '采购商ID',
  `social_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '统一社会信用代码',
  `legal_person` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '法人',
  `company_type` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '公司类型',
  `establishment_date` date NULL DEFAULT NULL COMMENT '成立日期',
  `business_scope` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '经营范围',
  `taxpayer_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '纳税人识别号',
  `bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开户行',
  `bank_account` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '银行账户',
  `created_by` bigint(20) NULL DEFAULT 0 COMMENT '创建人ID',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '修改人ID',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_number`(`purchaser_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '采购商工商税务信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of purchaser_business
-- ----------------------------

-- ----------------------------
-- Table structure for quick_menus
-- ----------------------------
DROP TABLE IF EXISTS `quick_menus`;
CREATE TABLE `quick_menus`  (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `menu_type` enum('PURCHASER','COMMON','SUPPLIER') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'PURCHASER' COMMENT 'PURCHASER:采购商 COMMON 公共菜单 SUPPLIER 供应商菜单',
  `team_id` bigint(20) NOT NULL DEFAULT 100000 COMMENT '采购商ID',
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '路由名称',
  `url` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '路由地址',
  `created_by` bigint(20) NOT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '修改时间',
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'title名,中文',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '快捷菜单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quick_menus
-- ----------------------------

-- ----------------------------
-- Table structure for quote
-- ----------------------------
DROP TABLE IF EXISTS `quote`;
CREATE TABLE `quote`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '单据编号',
  `inquiry_id` bigint(20) NOT NULL COMMENT '询单ID',
  `bill_date` datetime(0) NULL DEFAULT NULL COMMENT '报价日期',
  `biz_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '业务类型',
  `end_date` datetime(0) NULL DEFAULT NULL COMMENT '报价截止时间',
  `req_org_id` bigint(20) NULL DEFAULT 0 COMMENT '需求组织',
  `org_id` bigint(20) NULL DEFAULT 0 COMMENT '采购组织',
  `rcv_org_id` bigint(20) NULL DEFAULT 0 COMMENT '收货组织',
  `settle_org_id` bigint(20) NULL DEFAULT 0 COMMENT '核算组织',
  `pay_org_id` bigint(20) NULL DEFAULT 0 COMMENT '付款组织',
  `person_id` bigint(20) NULL DEFAULT 0 COMMENT '业务员',
  `supplier_id` bigint(20) NULL DEFAULT 0 COMMENT '供应商',
  `contacter_id` bigint(20) NULL DEFAULT 0 COMMENT '供应商联系人',
  `date_from` date NULL DEFAULT NULL COMMENT '价格有效期从',
  `date_to` date NULL DEFAULT NULL COMMENT '价格有效期至',
  `pay_cond_id` bigint(20) NULL DEFAULT 0 COMMENT '付款条件-隐藏',
  `settle_type_id` bigint(20) NULL DEFAULT 0 COMMENT '结算方式-隐藏',
  `curr_id` bigint(20) NULL DEFAULT 0 COMMENT '币种',
  `loc_curr_id` bigint(20) NULL DEFAULT 0 COMMENT '本位币',
  `exch_type_id` bigint(20) NULL DEFAULT 0 COMMENT '汇率类型',
  `exch_rate` decimal(19, 6) NULL DEFAULT 1.000000 COMMENT '汇率',
  `tax_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '计税类型-隐藏',
  `sum_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计金额',
  `sum_tax` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计税额',
  `sum_tax_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '价税合计',
  `sum_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '合计数量',
  `bill_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '单据状态\r\nA:保存\r\nB:已提交\r\nC:已审核\r\nD:已关闭\r\nZ:已作废',
  `cfm_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '确认状态',
  `biz_partner_id` bigint(20) NULL DEFAULT 0 COMMENT '商务伙伴',
  `inquiry_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '询价单号',
  `deli_date` date NULL DEFAULT NULL COMMENT '交货日期',
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '交货地址',
  `sum_cost` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '费用合计',
  `total_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '总价',
  `bill_type_id` bigint(20) NULL DEFAULT 0 COMMENT '业务类型',
  `biz_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '报价结果\r\nA:已报价\r\nB:已开标\r\nC:已采纳\r\nD:部分采纳\r\nE:未采纳',
  `inv_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '发票类型',
  `total_inquiry` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '整单询价',
  `turns` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '询价轮次',
  `turns_count` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '轮次',
  `sup_curr_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '2' COMMENT '报价币种选项',
  `rate_date` datetime(0) NULL DEFAULT NULL COMMENT '汇率日期-隐藏',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '来源',
  `inquiry_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '询价标题',
  `base_data_field` bigint(20) NULL DEFAULT NULL COMMENT '来源',
  `payment_terms` bigint(20) NULL DEFAULT NULL COMMENT '付款条件',
  `settlement_method` bigint(20) NULL DEFAULT NULL COMMENT '结算方式',
  `settlement_cur` bigint(20) NULL DEFAULT NULL COMMENT '结算币种',
  `tax_cal_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '计税类型',
  `base_cur` bigint(20) NULL DEFAULT NULL COMMENT '本位币',
  `exchange_rate_date` datetime(0) NULL DEFAULT NULL COMMENT '汇率日期',
  `delivery_date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '交货期',
  `text_field` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '来源',
  `warranty_period` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '质保期（月）',
  `business_type_id` bigint(20) NULL DEFAULT 0 COMMENT '业务类型',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '来源',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  `contact_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `contact_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `other_pay_terms_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '其他付款条件说明_详情',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_FBILLNO`(`bill_no`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_FBILLDATE`(`bill_date`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_FBIZPARTNERID`(`biz_partner_id`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_FINQUIRYNO`(`inquiry_no`) USING BTREE,
  INDEX `inquiry_id`(`inquiry_id`) USING BTREE,
  INDEX `end_date`(`end_date`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价单-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote
-- ----------------------------

-- ----------------------------
-- Table structure for quote_attach
-- ----------------------------
DROP TABLE IF EXISTS `quote_attach`;
CREATE TABLE `quote_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `quote_id` bigint(20) NOT NULL COMMENT '报价D',
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `supplier_id`(`quote_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商联系人' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote_attach
-- ----------------------------

-- ----------------------------
-- Table structure for quote_entry
-- ----------------------------
DROP TABLE IF EXISTS `quote_entry`;
CREATE TABLE `quote_entry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inquiry_entry_id` bigint(20) NULL DEFAULT NULL COMMENT '询价商品ID',
  `inquiry_id` bigint(20) NULL DEFAULT NULL,
  `quote_id` bigint(20) NOT NULL,
  `seq` bigint(20) NULL DEFAULT 0 COMMENT '报价单分录.分录行号',
  `material_id` bigint(20) NULL DEFAULT 0 COMMENT '报价单分录.物料编码',
  `material_desc` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.物料描述',
  `asstpro_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.辅助数量',
  `unit_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.计量单位',
  `inquiry_qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价单分录.询价数量（隐藏）',
  `inquiry_precision` int(11) NULL DEFAULT 0 COMMENT '询价单位精度',
  `deli_date` date NULL DEFAULT NULL COMMENT '报价单分录.交货日期（隐藏）',
  `deli_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.交货地址（隐藏）',
  `deli_type_id` int(11) NULL DEFAULT 0 COMMENT '报价单分录.交货方式（隐藏）',
  `qty` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价单分录.报价数量',
  `price` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '报价单分录.单价',
  `tax_price` decimal(23, 10) NULL DEFAULT 0.0000000000 COMMENT '报价单分录.含税单价',
  `dct_rate` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.折扣率(%)',
  `dct_amount` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.折扣额',
  `amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价单分录.金额',
  `tax_rate` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价单分录.税率(%)',
  `tax` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价单分录.税额',
  `tax_amount` decimal(19, 6) NULL DEFAULT 0.000000 COMMENT '报价单分录.价税合计',
  `req_org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '需求组织',
  `pur_org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.采购组织',
  `rcv_org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '收货组织',
  `settle_org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '核算组织',
  `pay_org_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '付款组织',
  `note` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.备注（隐藏）',
  `entry_status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '报价单分录.行状态',
  `pobill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.订单编号',
  `pcbill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.合同编号',
  `project_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.项目号',
  `trace_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.跟踪号',
  `tax_rate_id` bigint(20) NULL DEFAULT 0 COMMENT '报价单分录.税率(%)',
  `quote_curr` bigint(20) NULL DEFAULT 0 COMMENT '报价单分录.报价币种',
  `exrate` decimal(23, 10) NOT NULL DEFAULT 1.0000000000 COMMENT '报价单分录.汇率',
  `quote_unit_id` bigint(20) NULL DEFAULT NULL COMMENT '报价单分录.报价单位',
  `precision` int(11) NULL DEFAULT NULL COMMENT '单位精度',
  `warranty_period` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.质保期',
  `inquiry_unit_id` bigint(20) NULL DEFAULT NULL COMMENT '报价单分录.询价单位',
  `material_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.物料名称',
  `specification_model` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.规格型号',
  `deli_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.交货地址',
  `delive_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.交货方式',
  `deli_at` date NULL DEFAULT NULL COMMENT '报价单分录.交货日期',
  `new_qty` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '询价数量',
  `inquire_qty` decimal(23, 10) NULL DEFAULT NULL COMMENT '询价数量',
  `big_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '商品分录.备注',
  `big_note_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '报价单分录.备注_详情',
  `stock_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.存货编码',
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '品牌',
  `material_name_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.物料名称',
  `line_type_id` bigint(20) NULL DEFAULT 0 COMMENT '报价单分录.行类型',
  `price_field` decimal(23, 10) NULL DEFAULT NULL,
  `amount_field` decimal(23, 10) NULL DEFAULT NULL,
  `budget_price` decimal(23, 10) NULL DEFAULT NULL COMMENT '报价单分录.预算单价',
  `budget_amount` decimal(23, 10) NULL DEFAULT NULL COMMENT '报价单分录.预算金额',
  `new_material_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.物料编码',
  `boss_goods_id` bigint(20) NULL DEFAULT NULL COMMENT '报价单分录.商品id',
  `boss_goods` bigint(20) NULL DEFAULT NULL COMMENT '报价单分录.商品id',
  `spec_model` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ' COMMENT '报价单分录.规格型号',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_PUR_QUOTENTRY_FID_FSEQ`(`quote_id`, `inquiry_entry_id`) USING BTREE,
  INDEX `inquiry_entry_id`(`inquiry_entry_id`) USING BTREE,
  INDEX `IDX_PUR_QUOTENTRY_FMATERIALID`(`material_id`, `seq`) USING BTREE,
  INDEX `inquiry_id`(`inquiry_id`, `seq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价单分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote_entry
-- ----------------------------

-- ----------------------------
-- Table structure for quote_entry_relate
-- ----------------------------
DROP TABLE IF EXISTS `quote_entry_relate`;
CREATE TABLE `quote_entry_relate`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) NOT NULL COMMENT '关联子实体.id',
  `seq` int(11) NOT NULL DEFAULT 0 COMMENT '关联子实体.分录行号',
  `stable_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单主实体编码',
  `sbill_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单内码',
  `s_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单主实体内码',
  `qty_old` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '关联子实体.报价数量_原始携带值',
  `qty` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '关联子实体.报价数量_确认携带值',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `entry_id`(`entry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '关联子实体-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote_entry_relate
-- ----------------------------

-- ----------------------------
-- Table structure for quote_entry_sub
-- ----------------------------
DROP TABLE IF EXISTS `quote_entry_sub`;
CREATE TABLE `quote_entry_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `quote_id` bigint(20) NOT NULL,
  `entry_id` bigint(20) NOT NULL DEFAULT 0,
  `goods_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.供方物料编码',
  `goodsdesc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.供方物料描述',
  `basic_unit_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.基本单位',
  `basic_qty` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '报价单分录.基本数量',
  `asst_unit_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.辅助单位',
  `asst_qty` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.辅助数量',
  `loc_amount` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.本位币金额',
  `loc_tax` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.本位币金额',
  `loc_tax_amount` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.本位币价税合计',
  `act_price` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '报价单分录.实际单价',
  `act_tax_price` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '报价单分录.实际含税单价',
  `po_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.订单ID',
  `po_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.订单分录ID',
  `pc_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.合同ID',
  `pc_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.合同分录ID',
  `src_bill_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.源单类型',
  `src_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.源单ID',
  `src_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.源单分录ID',
  `sum_compare_qty` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.关联比价数量',
  `sum_order_qty` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.关联订单数量',
  `result` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '报价单分录.报价结果',
  `cfm_qty` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.确认数量',
  `cfm_price` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.确认单价',
  `cfm_tax_price` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.确认含税单价',
  `cfm_tax_rate` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.确认税率(%)',
  `cfm_note` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.确认意见',
  `pr_bill_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.申请单分录id',
  `pr_entry_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.申请单id',
  `pr_bill_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '报价单分录.申请单编号',
  `cfm_tax_rate_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '报价单分录.确认税率ID',
  `min_order_qty` decimal(19, 6) NOT NULL DEFAULT 0.000000 COMMENT '报价单分录.最小起订量',
  `pur_lead_day` int(11) NOT NULL DEFAULT 0 COMMENT '报价单分录.采购提前期',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_QUOTENTRY_A_FPOENTRYID`(`po_entry_id`) USING BTREE,
  INDEX `IDX_PUR_QUOTENTRY_A_FID`(`entry_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价单分录-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote_entry_sub
-- ----------------------------

-- ----------------------------
-- Table structure for quote_relate
-- ----------------------------
DROP TABLE IF EXISTS `quote_relate`;
CREATE TABLE `quote_relate`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '关联子实体.id',
  `tbill_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.分录行号',
  `ttable_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单主实体编码',
  `t_id` bigint(20) NOT NULL DEFAULT 0,
  `sbill_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单内码',
  `stable_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单主实体编码',
  `s_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '关联子实体.源单主实体内码',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_TC_TBILL`(`tbill_id`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_TC_TID`(`t_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价单-关联追踪表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote_relate
-- ----------------------------

-- ----------------------------
-- Table structure for quote_sub
-- ----------------------------
DROP TABLE IF EXISTS `quote_sub`;
CREATE TABLE `quote_sub`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `quote_id` bigint(20) NOT NULL,
  `creator_id` bigint(20) NOT NULL DEFAULT 0,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modifier_id` bigint(20) NOT NULL DEFAULT 0,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `auditor_id` bigint(20) NOT NULL DEFAULT 0,
  `audit_date` datetime(0) NULL DEFAULT NULL,
  `cfm_id` bigint(20) NOT NULL DEFAULT 0,
  `cfm_date` datetime(0) NULL DEFAULT NULL,
  `origin` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sup_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `contact_or` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `contact_way` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `quote_from` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `open_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `remark` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `quote_id`(`quote_id`) USING BTREE,
  INDEX `IDX_PUR_QUOTE_A_FCREATETIME`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报价单-分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quote_sub
-- ----------------------------

-- ----------------------------
-- Table structure for role_has_menus
-- ----------------------------
DROP TABLE IF EXISTS `role_has_menus`;
CREATE TABLE `role_has_menus`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `scope` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'ALL' COMMENT '数据范围\r\nALL   ：全部\r\nDEPARTMENT ：部门，\r\nCOUNTRY  ：国家，\r\nUSER ：本人',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unix_menu_id`(`menu_id`, `role_id`) USING BTREE,
  INDEX `idx_role_id`(`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色菜单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_has_menus
-- ----------------------------

-- ----------------------------
-- Table structure for role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `perm_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `scope` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'ALL' COMMENT '数据范围\r\nALL   ：全部\r\nDEPARTMENT ：部门，\r\nCOUNTRY  ：国家，\r\nUSER ：本人',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `menu_id` bigint(20) NULL DEFAULT NULL,
  `deleted_flag` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `perm_id`(`perm_id`, `role_id`, `menu_id`) USING BTREE,
  INDEX `role_has_permissions_role_id_foreign`(`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色权限' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_has_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for role_user
-- ----------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `role_group` enum('COMMON','PURCHASER','SYSTEM','SUPPLIER','PLATFORM') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'PURCHASER' COMMENT '权限类型 PURCHASER:采购商 COMMON 公共权限 SUPPLIER 供应商菜单',
  `role_id` bigint(20) NOT NULL COMMENT '角色ID',
  `content_id` bigint(20) NULL DEFAULT NULL COMMENT '采购商\\组织ID 供应商ID',
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `team_id` bigint(20) NULL DEFAULT NULL COMMENT '采购商\\组织ID 供应商ID',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `role_id`(`role_id`, `team_id`, `user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色成员' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_user
-- ----------------------------

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'SYSTEM',
  `team_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `role_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限字符',
  `role_group` enum('COMMON','PURCHASER','SYSTEM','SUPPLIER','PLATFORM') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'PURCHASER' COMMENT '权限类型 PURCHASER:采购商 COMMON 公共权限 SUPPLIER 供应商菜单',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'NORMAL' COMMENT '状态,NORMAL-正常;DISABLED-停用',
  `sort` int(10) NULL DEFAULT 0 COMMENT '数字大到小排序',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '维护人',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `roles_team_foreign_key_index`(`team_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------

-- ----------------------------
-- Table structure for send_log
-- ----------------------------
DROP TABLE IF EXISTS `send_log`;
CREATE TABLE `send_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NULL DEFAULT NULL COMMENT '客户ID',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'EMAIL' COMMENT '消息类型：（默认）PHONE、EMAIL',
  `message_to` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '接收消息的手机号或邮箱',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '消息主题',
  `message` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '消息内容',
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '默认：未处理，SUCCESS:成功，ERROR:失败',
  `return` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '返回信息',
  `send_at` datetime(0) NULL DEFAULT NULL COMMENT '发送时间',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_message_to`(`message_to`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '邮件短信发送记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of send_log
-- ----------------------------

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一标识符',
  `group` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分组名',
  `alias` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '配置字段别名(保证唯一性)',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置字段值',
  `serialized` tinyint(1) NULL DEFAULT 0 COMMENT '是否序列化value',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '修改时间',
  `deleted_flag` enum('N','Y') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `group_alias_index`(`group`, `alias`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of setting
-- ----------------------------

-- ----------------------------
-- Table structure for settle_ment_type
-- ----------------------------
DROP TABLE IF EXISTS `settle_ment_type`;
CREATE TABLE `settle_ment_type`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `settle_ment_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '编码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `is_person_pay` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '对私支付',
  `pay_throughbe` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '银企直联支付',
  `is_agency_person_pay` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '并笔入账',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '系统预设',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '使用状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `create_org_id` bigint(20) NULL DEFAULT NULL,
  `org_id` bigint(20) NULL DEFAULT NULL,
  `creator_id` bigint(20) NULL DEFAULT NULL,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modifier_id` bigint(20) NULL DEFAULT NULL,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `disabler_id` bigint(20) NULL DEFAULT NULL,
  `disable_date` datetime(0) NULL DEFAULT NULL,
  `master_id` bigint(20) NULL DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `is_default` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_T_BD_SETTLTYPE_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '结算方式-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of settle_ment_type
-- ----------------------------

-- ----------------------------
-- Table structure for simple_upload
-- ----------------------------
DROP TABLE IF EXISTS `simple_upload`;
CREATE TABLE `simple_upload`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件名',
  `totalsize` int(11) NOT NULL DEFAULT 0 COMMENT '文件大小',
  `identifier` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件md5',
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件类型',
  `filepath` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件保存路径',
  `created_at` datetime(0) NOT NULL COMMENT '上传时间',
  `chunknumber` int(11) NULL DEFAULT NULL,
  `totalchunks` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of simple_upload
-- ----------------------------

-- ----------------------------
-- Table structure for supplier
-- ----------------------------
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `source` enum('BOSS','PURCHASER','REGISTER') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'BOSS' COMMENT '来源：\r\nBOSS-瑞招采平台；\r\nREGISTER:前台注册,PURCHASER:采购商',
  `number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '供应商临时编号',
  `purchaser_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '注册审核单位ID',
  `supplier_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商正式编码',
  `enable` tinyint(1) NULL DEFAULT NULL COMMENT '是否启用 ',
  `supplier_group_id` bigint(20) NULL DEFAULT 1 COMMENT '供应商分类',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商名称',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '注册地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `scope_of_operation` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '经营范围',
  `industry_classification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '行业分类',
  `legal_representative` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '法定代表人',
  `social_credit_code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '统一社会信用代码',
  `reg_capital` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '注册资本（万元）',
  `profile` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商简介',
  `enterprise_type` enum('ENTERPRISE','STATE_ORGANS','PUBLIC_INSTITUTIONS','SOCIAL_GROUPS','OTHER_ORGANIZATIONAL_STRUCTURES','INDIVIDUAL_BUSINESSES','NATURAL_PERSON') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'ENTERPRISE' COMMENT '企业类型\r\nENTERPRISE:企业\r\nSTATE_ORGANS:国家机关\r\nPUBLIC_INSTITUTIONS:事业单位\r\nSOCIAL_GROUPS:社会团体\r\nOTHER_ORGANIZATIONAL_STRUCTURES:其他组织机构\r\nINDIVIDUAL_BUSINESSES:个体户\r\n自然人',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '状态DRAFT: 临时供应商  REVIEW 待审核 APPROVING 审核中  APPROVED 通过  INVALID 驳回',
  `registered_at` datetime(0) NULL DEFAULT NULL COMMENT '注册时间',
  `filled_at` datetime(0) NULL DEFAULT NULL COMMENT '填写企业信息时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `checked_at` datetime(0) NULL DEFAULT NULL COMMENT '审核时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `checked_by` bigint(20) NULL DEFAULT NULL COMMENT '审核人',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '封禁人',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '封禁时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `supplier_status`(`status`) USING BTREE,
  INDEX `supplier_checked_by`(`checked_by`) USING BTREE,
  INDEX `supplier_created_at`(`created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_attach
-- ----------------------------
DROP TABLE IF EXISTS `supplier_attach`;
CREATE TABLE `supplier_attach`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `attach_type` enum('OPENS_ACCOUNT_LICENCE','OTHER','LEGAL_PERSON_ID2','LEGAL_PERSON_ID','LEGAL_PERSON_ID1','BUSINESS_LICENSE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'OTHER' COMMENT '资质类型  LEGAL_PERSON_ID1:法人身份证正面,LEGAL_PERSON_ID2:法人身份证反面 BUSINESS_LICENSE:营业执照 OTHER 其它  ',
  `attach_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名称',
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件地址',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `supplier_id`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商联系人' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_attach
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_audit
-- ----------------------------
DROP TABLE IF EXISTS `supplier_audit`;
CREATE TABLE `supplier_audit`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL COMMENT '询单id',
  `supplier_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '供应商编码',
  `audit_type` enum('UNFREEZE','FREEZE','CHANGE','SUBMIT','ACCESS','CREATE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'CREATE' COMMENT '审核类型 CHANGE：变更审核，CREATE：创建审核 FREEZE：冻结审核，UNFREEZE：解冻审核 SUBMIT:提交企业信息 ACCESS:准入',
  `base` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '基本信息',
  `attachs` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '资质附件',
  `banks` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '银行信息',
  `contacts` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '联系人信息',
  `agent_id` bigint(20) NULL DEFAULT NULL COMMENT '冻结/解冻申请人',
  `user_id` bigint(20) NULL DEFAULT NULL COMMENT '后台用户id',
  `purchaser_id` bigint(20) NULL DEFAULT NULL COMMENT '准入采购商ID',
  `status` enum('DRAFT','REJECTED','PASS','REVIEW') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'REVIEW' COMMENT '审核状态\r\nDRAFT:草稿,用于预览\r\nREVIEW：待审核\r\nPASS：审核通过\r\nREJECTED：审核拒绝',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `audit_at` datetime(0) NULL DEFAULT NULL COMMENT '审核日期',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '修改时间',
  `deleted_at` datetime(0) NULL DEFAULT NULL COMMENT '删除时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_supplier_id`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商审核' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_audit
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_bank
-- ----------------------------
DROP TABLE IF EXISTS `supplier_bank`;
CREATE TABLE `supplier_bank`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `bank_account` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '银行账号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '账号名称',
  `opening_bank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开户行',
  `currency_id` bigint(20) NULL DEFAULT NULL COMMENT '币种ID',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `tenant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'erui' COMMENT '租户，默认“erui”',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `supplier_id_2`(`supplier_id`, `bank_account`, `currency_id`) USING BTREE,
  INDEX `supplier_id`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商联系人' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_bank
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_contact
-- ----------------------------
DROP TABLE IF EXISTS `supplier_contact`;
CREATE TABLE `supplier_contact`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系人姓名',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系人电话',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系人邮箱',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `default_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否默认',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `supplier_id`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商联系人' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_contact
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_eva_grade
-- ----------------------------
DROP TABLE IF EXISTS `supplier_eva_grade`;
CREATE TABLE `supplier_eva_grade`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `creator_id` bigint(20) NOT NULL DEFAULT 0,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modifier_id` bigint(20) NOT NULL DEFAULT 0,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `auditor_id` bigint(20) NOT NULL DEFAULT 0,
  `audit_date` datetime(0) NULL DEFAULT NULL,
  `disabler_id` bigint(20) NOT NULL DEFAULT 0,
  `disable_date` datetime(0) NULL DEFAULT NULL,
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT ' ',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_PUR_EVAGRADE_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '评估等级-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_eva_grade
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_extend
-- ----------------------------
DROP TABLE IF EXISTS `supplier_extend`;
CREATE TABLE `supplier_extend`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `access_tpl_id` bigint(20) NULL DEFAULT NULL,
  `purchaser_id` bigint(20) NOT NULL,
  `access_setting_id` bigint(20) NOT NULL COMMENT '供应商准入配置ID',
  `extend_value` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '扩展值',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `supplier_id`(`supplier_id`, `access_setting_id`) USING BTREE,
  INDEX `idx_supplier_id`(`supplier_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商企业信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_extend
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_grade
-- ----------------------------
DROP TABLE IF EXISTS `supplier_grade`;
CREATE TABLE `supplier_grade`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `creator_id` bigint(20) NOT NULL DEFAULT 0,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `modifier_id` bigint(20) NOT NULL DEFAULT 0,
  `modify_time` datetime(0) NULL DEFAULT NULL,
  `auditor_id` bigint(20) NOT NULL DEFAULT 0,
  `audit_date` datetime(0) NULL DEFAULT NULL,
  `disabler_id` bigint(20) NOT NULL DEFAULT 0,
  `disable_date` datetime(0) NULL DEFAULT NULL,
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `scope` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '禁用人ID',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_PUR_GRADE_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分级方案-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_grade
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_gradentry
-- ----------------------------
DROP TABLE IF EXISTS `supplier_gradentry`;
CREATE TABLE `supplier_gradentry`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `grade_id` bigint(20) NOT NULL,
  `eva_grade_id` bigint(20) NOT NULL DEFAULT 0,
  `seq` bigint(20) NOT NULL DEFAULT 0,
  `score_from` decimal(19, 6) NOT NULL DEFAULT 0.000000,
  `score_to` decimal(19, 6) NOT NULL DEFAULT 0.000000,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_grade_seq`(`grade_id`, `seq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分级规则分录-子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_gradentry
-- ----------------------------

-- ----------------------------
-- Table structure for supplier_group
-- ----------------------------
DROP TABLE IF EXISTS `supplier_group`;
CREATE TABLE `supplier_group`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '编码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否启用',
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '状态',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人ID',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '修改人ID',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '禁用人ID',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '描述',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_T_BD_SUPPLIERGROUP_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '供应商分类-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of supplier_group
-- ----------------------------

-- ----------------------------
-- Table structure for tax_category
-- ----------------------------
DROP TABLE IF EXISTS `tax_category`;
CREATE TABLE `tax_category`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `master_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '主数据内码',
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '编码',
  `taxation_sys_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '税收制度',
  `simple_code` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '简码',
  `active_date` datetime(0) NULL DEFAULT NULL COMMENT '生效日期',
  `exp_date` datetime(0) NULL DEFAULT NULL COMMENT '失效日期',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '系统预设',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '描述',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_T_BD_TAXCATEGORY_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '税种-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tax_category
-- ----------------------------

-- ----------------------------
-- Table structure for tax_rate
-- ----------------------------
DROP TABLE IF EXISTS `tax_rate`;
CREATE TABLE `tax_rate`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `master_id` bigint(20) NOT NULL DEFAULT 1,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '编码',
  `tax_rate` decimal(23, 10) NOT NULL DEFAULT 0.0000000000 COMMENT '税率(%)',
  `tax_category_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '税种',
  `active_date` datetime(0) NULL DEFAULT NULL COMMENT '生效日期',
  `exp_date` datetime(0) NULL DEFAULT NULL COMMENT '失效日期',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '系统预设',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '使用状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  `org_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '用户选择组织',
  `create_org_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '创建组织',
  `user_org` bigint(20) NOT NULL DEFAULT 1 COMMENT '组织',
  `ctrl_strategy` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '控制策略',
  `country` bigint(20) NOT NULL DEFAULT 1 COMMENT '国家或地区',
  `source_data_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '原资料id',
  `bit_index` int(11) NOT NULL DEFAULT 1 COMMENT '位图',
  `source_bit_index` int(11) NOT NULL DEFAULT 1,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_T_BD_TAXRATE_FNUMBER`(`number`) USING BTREE,
  INDEX `IDX_T_BD_TAXRATE_CREATEORG`(`create_org_id`) USING BTREE,
  INDEX `IDX_T_BD_TAXRATE_MASTER`(`master_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '税率-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tax_rate
-- ----------------------------
INSERT INTO `tax_rate` VALUES (1, 1, 'V13', 13.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税13%', '1', 'C', 1, '2019-01-01 00:00:00', 1, '2019-01-01 00:00:00', 1, NULL, 1, 1, 1, '1', 1, 1, 1, 1, '');
INSERT INTO `tax_rate` VALUES (2, 1, 'V9', 9.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税9%', '1', 'C', 1, '2019-01-01 00:00:00', 1, '2019-01-01 00:00:00', 1, NULL, 1, 1, 1, '1', 1, 1, 1, 1, '');
INSERT INTO `tax_rate` VALUES (3, 1, 'V6', 6.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税6%', '1', 'C', 1, '2019-01-01 00:00:00', 1, '2019-01-01 00:00:00', 1, NULL, 1, 1, 1, '1', 1, 1, 1, 1, '');
INSERT INTO `tax_rate` VALUES (4, 1, 'V5', 5.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税5%', '1', 'C', 1, '2019-01-01 00:00:00', 1, '2019-01-01 00:00:00', 1, NULL, 1, 1, 1, '1', 1, 1, 1, 1, '');
INSERT INTO `tax_rate` VALUES (5, 1, 'V3', 3.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税3%', '1', 'C', 1, '2019-01-01 00:00:00', 1, '2019-01-01 00:00:00', 1, NULL, 1, 1, 1, '1', 1, 1, 1, 1, '');
INSERT INTO `tax_rate` VALUES (6, 1, 'V0', 0.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税0%', '1', 'C', 1, '2019-01-01 00:00:00', 1, '2019-01-01 00:00:00', 1, NULL, 1, 1, 1, '1', 1, 1, 1, 1, '');
INSERT INTO `tax_rate` VALUES (7, 1, 'V15', 15.0000000000, 1, '2000-01-01 00:00:00', '2099-12-31 23:59:59', '1', '增值税15%', '1', 'C', 1, NULL, 1, '2024-02-28 17:42:42', 1, NULL, 1, 1, 1, ' ', 1, 1, 1, 1, '');

-- ----------------------------
-- Table structure for taxation_sys
-- ----------------------------
DROP TABLE IF EXISTS `taxation_sys`;
CREATE TABLE `taxation_sys`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `master_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '主数据内码',
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '编码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
  `active_date` datetime(0) NULL DEFAULT NULL COMMENT '生效日期',
  `exp_date` datetime(0) NULL DEFAULT NULL COMMENT '失效日期',
  `country_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '国家地区',
  `is_system` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '系统预设',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '使用状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_T_BD_TAXATIONSYS_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '税收制度-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of taxation_sys
-- ----------------------------

-- ----------------------------
-- Table structure for unit
-- ----------------------------
DROP TABLE IF EXISTS `unit`;
CREATE TABLE `unit`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '编码',
  `precision` int(11) NOT NULL DEFAULT 1 COMMENT '单位精度',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '名称',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '使用状态',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ' ' COMMENT '数据状态',
  `creator_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '创建人',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `modifier_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '修改人',
  `modify_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `disabler_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '禁用人',
  `disable_date` datetime(0) NULL DEFAULT NULL COMMENT '禁用时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `IDX_T_BD_MEASUREUNIT_NUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 42 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '计量单位-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of unit
-- ----------------------------
INSERT INTO `unit` VALUES (1, 'm', 1, '米', '1', 'C', 1, '2024-03-19 09:46:16', 1, '2024-03-27 07:59:38', 1, '2024-03-27 07:59:23');
INSERT INTO `unit` VALUES (2, 'cm', 1, '厘米', '1', 'C', 1, '2024-03-19 09:46:30', 1, '2024-03-27 07:59:38', 1, '2024-03-27 07:59:29');
INSERT INTO `unit` VALUES (3, 'mm', 1, '毫米', '1', 'C', 1, '2024-03-19 09:14:24', 1, '2024-03-20 15:30:33', 1, '2024-03-19 09:38:06');
INSERT INTO `unit` VALUES (4, 'kg', 1, '千克', '1', 'C', 1, '2024-05-06 11:10:43', 1, '2024-05-22 16:22:16', 1, '2024-05-22 16:22:09');
INSERT INTO `unit` VALUES (5, 'g', 1, '克', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (6, 'l', 1, '升', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (7, 'ml', 1, '毫升', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-20 15:30:33', 1, '2024-03-20 15:30:24');
INSERT INTO `unit` VALUES (8, 'second', 1, '秒', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (9, 'minute', 1, '分', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (10, 'hour', 1, '小时', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (11, 'pcs', 1, '个', '1', 'C', 1, '2018-03-05 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (12, 'mg', 1, '毫克', '1', 'C', 1, '2018-08-21 00:00:00', 1, '2024-03-18 15:23:20', 1, NULL);
INSERT INTO `unit` VALUES (13, 'to', 1, '吨', '1', 'C', 1, '2018-08-21 00:00:00', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (14, 'km', 1, '千米', '1', 'C', 1, '2018-08-21 00:00:00', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (15, 'double', 1, '双', '1', 'C', 1, '2018-08-21 00:00:00', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (16, 'dozen', 1, '打', '1', 'C', 1, '2018-08-21 00:00:00', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (17, 'PC', 1, '件', '1', 'C', 1, '2021-07-02 14:10:22', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (18, 'BOX', 1, '盒', '1', 'C', 1, '2021-07-02 14:13:46', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (19, 'M2', 1, '平方米', '1', 'C', 1, '2024-01-26 13:59:07', 1, '2024-01-26 13:59:07', 1, NULL);
INSERT INTO `unit` VALUES (20, 'M3', 1, '立方米', '1', 'C', 1, '2021-07-02 14:16:20', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (21, 'PAC', 1, '件', '1', 'C', 1, '2021-07-02 14:17:52', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (22, 'ping', 1, '瓶', '1', 'C', 1, '2021-07-02 14:20:03', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (23, 'BAG', 1, '包', '1', 'C', 1, '2021-07-02 14:38:14', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (24, 'stick', 1, '根', '1', 'C', 1, '2021-07-03 09:54:56', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (25, 'roll', 1, '卷', '1', 'C', 1, '2021-07-03 10:04:23', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (26, 'bundle', 1, '捆', '1', 'C', 1, '2021-07-03 10:05:21', 1, '2021-07-03 10:08:17', 1, NULL);
INSERT INTO `unit` VALUES (27, 'batch', 1, '批', '1', 'C', 1, '2021-07-03 10:07:50', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (28, 'set', 1, '台', '1', 'C', 1, '2021-07-03 10:09:49', 1, '2021-07-03 10:10:02', 1, NULL);
INSERT INTO `unit` VALUES (29, 'barrel', 1, '桶', '1', 'C', 1, '2021-07-03 10:12:48', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (30, 'day', 1, '天', '1', 'C', 1, '2021-07-31 10:32:23', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (31, 'tao', 1, '套', '1', 'C', 1, '2021-08-14 15:36:05', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (32, 'Sheet', 1, '张', '1', 'C', 1, '2021-12-21 15:00:53', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (33, 'pcs-02', 1, '块', '1', 'C', 1, '2021-12-21 15:22:31', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (34, 'pcs-03', 1, '副', '1', 'C', 1, '2024-08-02 08:57:10', 1, '2024-08-02 08:57:10', 1, NULL);
INSERT INTO `unit` VALUES (35, 'pcs-04', 1, '片', '1', 'C', 1, '2022-04-27 08:54:15', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (36, 'pcs-05', 1, '斤', '1', 'C', 1, '2022-04-27 08:57:20', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (37, 'pcs-06', 1, '只', '1', 'C', 1, '2022-05-30 14:41:38', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (38, 'pcs-07', 1, '箱', '1', 'C', 1, '2022-06-28 14:37:35', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (39, 'pcs-08', 1, '袋', '1', 'C', 1, '2022-07-14 14:27:44', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (40, 'pcs-09', 1, '盘', '1', 'C', 1, '2022-07-14 14:28:09', 1, '2024-01-22 13:41:23', 1, NULL);
INSERT INTO `unit` VALUES (41, 'pcs-10', 1, '支', '1', 'C', 1, '2022-07-14 14:44:01', 1, '2024-01-22 13:41:23', 1, NULL);

-- ----------------------------
-- Table structure for upload_file
-- ----------------------------
DROP TABLE IF EXISTS `upload_file`;
CREATE TABLE `upload_file`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `identifier` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `identifier`(`identifier`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '上传文件记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of upload_file
-- ----------------------------

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_type` enum('SUPPLIER','PLATFORM') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'PLATFORM' COMMENT '用户类型SUPPLIER:供应商 PURCHASER:采购商 PLATFORM:平台 ORG:组织',
  `phone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '联系电话',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `email` varchar(96) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系邮箱',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `realname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '姓名',
  `full_pinyin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '姓名全拼',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `gender` enum('FEMALE','MALE','SECRECY') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'SECRECY' COMMENT '性别',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_flag` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要修改密码0：不需要1：需要',
  `status` tinyint(1) NOT NULL,
  `is_super` tinyint(1) NULL DEFAULT 0 COMMENT '是否是超级管理员',
  `sub` tinyint(8) NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `created_by` bigint(20) NULL DEFAULT 0 COMMENT '创建人ID',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '修改人ID',
  `disabled_by` bigint(20) NULL DEFAULT NULL COMMENT '封禁人',
  `disabled_at` datetime(0) NULL DEFAULT NULL COMMENT '封禁时间',
  `enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '使用状态',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------

-- ----------------------------
-- Table structure for user_contact
-- ----------------------------
DROP TABLE IF EXISTS `user_contact`;
CREATE TABLE `user_contact`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `contact_type` enum('ADDRESS','EMAIL','PHONE') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系方式 可选择手机、邮箱、地址',
  `contact_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '联系人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_user_id`(`user_id`, `contact_type`, `contact_value`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '联系方式' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_contact
-- ----------------------------

-- ----------------------------
-- Table structure for user_login
-- ----------------------------
DROP TABLE IF EXISTS `user_login`;
CREATE TABLE `user_login`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '登录用户id',
  `ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ip地址',
  `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `city` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '定位地址',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员登录日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_login
-- ----------------------------

-- ----------------------------
-- Table structure for user_purchaser
-- ----------------------------
DROP TABLE IF EXISTS `user_purchaser`;
CREATE TABLE `user_purchaser`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `purchaser_id` bigint(20) NOT NULL COMMENT '采购商组织ID',
  `bot_purchaser_id` bigint(20) NULL DEFAULT NULL COMMENT '采购商ID',
  `position` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '职位',
  `is_manager` tinyint(4) NULL DEFAULT 0 COMMENT '是否管理人员、负责人',
  `is_default` tinyint(4) NULL DEFAULT 0 COMMENT '是否是默认组织',
  `sort` tinyint(4) NULL DEFAULT 0 COMMENT '序号',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_id_2`(`user_id`, `purchaser_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户关联组织或者采购商' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_purchaser
-- ----------------------------

-- ----------------------------
-- Table structure for user_supplier
-- ----------------------------
DROP TABLE IF EXISTS `user_supplier`;
CREATE TABLE `user_supplier`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `is_default` tinyint(4) NULL DEFAULT 0 COMMENT '是否是默认组织',
  `is_manager` tinyint(1) NULL DEFAULT 0 COMMENT '是否是超级管理员',
  `sort` tinyint(4) NULL DEFAULT 0 COMMENT '序号',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_flag` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '删除状态',
  `position` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '职位',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unx_supplier_user`(`supplier_id`, `user_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户关联供应商' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for valuation_mode
-- ----------------------------
DROP TABLE IF EXISTS `valuation_mode`;
CREATE TABLE `valuation_mode`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `enable` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `org_id` bigint(20) NOT NULL DEFAULT 0,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建人',
  `created_at` datetime(0) NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新人',
  `updated_at` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `is_pre_setting` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_BID_VALUATIONMODE_FNUMBER`(`number`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '计价模式-主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of valuation_mode
-- ----------------------------
INSERT INTO `valuation_mode` VALUES (1, 'JJ202109170001', '清单计价', '1', 100000, 'C', 136, '2024-08-19 16:15:02', 136, '2024-08-19 16:15:02', '1');
INSERT INTO `valuation_mode` VALUES (2, 'JJ202109170002', '定额计价', '1', 100000, 'C', 136, '2024-08-19 16:16:57', 136, '2024-08-19 16:16:57', '0');
INSERT INTO `valuation_mode` VALUES (3, 'JJ202109170003', '项目管理模式', '1', 100000, 'C', 136, '2024-08-19 16:16:54', 136, '2024-08-19 16:16:54', '0');

SET FOREIGN_KEY_CHECKS = 1;
