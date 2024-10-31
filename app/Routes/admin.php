<?php

/** @var \Laravel\Lumen\Routing\Router $router */
/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */


$router->post('/admin/auth/login', ['uses' => 'AuthController@login', 'middleware' => 'admin_log']);
$router->get('/admin/auth/bossLogin', ['uses' => 'AuthController@bossLogin', 'middleware' => 'admin_log']);
$router->post('/admin/auth/bossLogin', ['uses' => 'AuthController@bossLogin', 'middleware' => 'admin_log']);
$router->post('upload', ['uses' => 'UploadFile@upload', 'as' => 'upload']);
$router->get('/admin/version', ['uses' => 'Version@index', 'as' => 'version']);
$router->group(['prefix' => 'admin/forget'], function () use ($router) {
    $router->post('email', 'ForgetController@email');
    $router->post('valid', 'ForgetController@valid');
    $router->post('reset', 'ForgetController@reset');
    $router->post('account', 'ForgetController@account');
    $router->post('account/valid', 'ForgetController@accountValid');
    $router->post('account/reset', 'ForgetController@accountReset');
    $router->post('phone', 'ForgetController@phone');
    $router->post('phone/valid', 'ForgetController@phonevalid');
    $router->post('phone/reset', 'ForgetController@phoneReset');
});
$router->group(['prefix' => 'admin', 'middleware' => [
        'permission',
        'admin_log']], function () use ($router) {
    /**
     * 权限
     */
    $router->get('auth/me', 'AuthController@me');
    $router->post('auth/me', 'AuthController@me');
    $router->post('auth/getRabcList', 'AuthController@getRabcList');
    $router->get('auth/info', 'AuthController@info');
    $router->post('auth/change/password', 'AuthController@change');
    $router->post('auth/change/org', 'AuthController@changeOrg');
    $router->post('auth/avatar', 'AuthController@avatar');
    $router->post('auth/logout', 'AuthController@logout');
    $router->get('auth/purchasers', 'AuthController@purchasers');

    /**
     * 用户分组
     */
    $router->get('index/statistics', 'IndexController@statistics');
    $router->get('index/todo', 'IndexController@todo');
    $router->get('index/inquiry/statistics', 'IndexController@inquiryStatistics');
    $router->get('index/quote/statistics', 'IndexController@quoteStatistics');
    $router->get('quick/menu', 'QuickMenuController@index');
    $router->put('quick/menu/add', 'QuickMenuController@add');
    $router->post('quick/menu/delete/{id:[0-9]+}', 'QuickMenuController@delete');
    $router->get('usertype/{id:[0-9]+}', 'UserTypeController@info');
    $router->get('usertype', 'UserTypeController@getList');
    $router->get('usertype/number', 'UserTypeController@number');
    $router->post('usertype/edited/{id:[0-9]+}', 'UserTypeController@edited');
    $router->put('usertype/add', 'UserTypeController@add');
    $router->post('usertype/delete', 'UserTypeController@delete');
    $router->post('usertype/disable', 'UserTypeController@disable');
    $router->post('usertype/enable', 'UserTypeController@enable');
    $router->post('usertype/import', 'UserTypeController@import');
    $router->get('usertype/export', 'UserTypeController@export');
    /**
     * 国家
     */
    $router->get('country/{id:[0-9]+}', 'CountryController@info');
    $router->get('country', 'CountryController@getList');
    $router->post('country/edited/{id:[0-9]+}', 'CountryController@edited');
    $router->put('country/add', 'CountryController@add');
    $router->post('country/delete', 'CountryController@delete');
    $router->post('country/disable', 'CountryController@disable');
    $router->post('country/enable', 'CountryController@enable');
    $router->post('country/import', 'CountryController@import');
    $router->get('country/export', 'CountryController@export');
    /**
     * 地区
     */
    $router->get('division/{id:[0-9]+}', 'DivisionController@info');
    $router->get('division', 'DivisionController@getList');
    $router->get('division/tree', 'DivisionController@tree');
    $router->get('division/china', 'DivisionController@china');
    $router->get('division/number', 'DivisionController@number');
    $router->post('division/edited/{id:[0-9]+}', 'DivisionController@edited');
    $router->put('division/add', 'DivisionController@add');
    $router->post('division/delete', 'DivisionController@delete');
    $router->post('division/disable', 'DivisionController@disable');
    $router->post('division/enable', 'DivisionController@enable');
    $router->post('division/import', 'DivisionController@import');
    $router->get('division/export', 'DivisionController@export');
    /**
     * 地区等级
     */
    $router->get('divisionlevel/{id:[0-9]+}', 'DivisionLevelController@info');
    $router->get('divisionlevel', 'DivisionLevelController@getList');
    $router->get('divisionlevel/number', 'DivisionLevelController@number');
    $router->post('divisionlevel/edited/{id:[0-9]+}', 'DivisionLevelController@edited');
    $router->put('divisionlevel/add', 'DivisionLevelController@add');
    $router->post('divisionlevel/delete', 'DivisionLevelController@delete');
    $router->post('divisionlevel/disable', 'DivisionLevelController@disable');
    $router->post('divisionlevel/enable', 'DivisionLevelController@enable');
    $router->post('divisionlevel/import', 'DivisionLevelController@import');
    $router->get('divisionlevel/export', 'DivisionLevelController@export');
    /**
     * 组织
     */
    $router->get('org/{id:[0-9]+}', 'OrgController@info');
    $router->get('org/list', 'OrgController@getList');
    $router->get('orgs', 'OrgController@getAll');
    $router->get('org', 'OrgController@tree');
    $router->get('org/number', 'OrgController@number');
    $router->post('org/edited/{id:[0-9]+}', 'OrgController@edited');
    $router->put('org/add', 'OrgController@add');
    $router->post('org/delete', 'OrgController@delete');
    $router->post('org/disable', 'OrgController@disable');
    $router->post('org/enable', 'OrgController@enable');
    $router->post('org/import', 'OrgController@import');
    $router->get('org/export', 'OrgController@export');
    /**
     * 单位
     */
    $router->get('unit/{id:[0-9]+}', 'UnitController@info');
    $router->get('unit', 'UnitController@getList');
    $router->get('unit/number', 'UnitController@number');
    $router->post('unit/edited/{id:[0-9]+}', 'UnitController@edited');
    $router->put('unit/add', 'UnitController@add');
    $router->post('unit/delete', 'UnitController@delete');
    $router->post('unit/disable', 'UnitController@disable');
    $router->post('unit/enable', 'UnitController@enable');
    $router->post('unit/import', 'UnitController@import');
    $router->get('unit/export', 'UnitController@export');
    /**
     * 行名行号
     */
    $router->get('bank/{id:[0-9]+}', 'BankController@info');
    $router->get('bank', 'BankController@getList');
    $router->get('bank/number', 'BankController@number');
    $router->post('bank/edited/{id:[0-9]+}', 'BankController@edited');
    $router->put('bank/add', 'BankController@add');
    $router->post('bank/delete', 'BankController@delete');
    $router->post('bank/disable', 'BankController@disable');
    $router->post('bank/enable', 'BankController@enable');
    $router->post('bank/import', 'BankController@import');
    $router->get('bank/export', 'BankController@export');

    /**
     * 银行类别
     */
    $router->get('bank/setting/{id:[0-9]+}', 'BankSettingController@info');
    $router->get('bank/setting', 'BankSettingController@getList');
    $router->get('bank/setting/number', 'BankSettingController@number');
    $router->post('bank/setting/edited/{id:[0-9]+}', 'BankSettingController@edited');
    $router->put('bank/setting/add', 'BankSettingController@add');
    $router->post('bank/setting/delete', 'BankSettingController@delete');
    $router->post('bank/setting/disable', 'BankSettingController@disable');
    $router->post('bank/setting/enable', 'BankSettingController@enable');
    $router->post('bank/setting/import', 'BankSettingController@import');
    $router->get('bank/setting/export', 'BankSettingController@export');
    /**
     * 币种
     */
    $router->get('currency/{id:[0-9]+}', 'CurrencyController@info');
    $router->get('currency', 'CurrencyController@getList');
    $router->get('currencys', 'CurrencyController@currencys');
    $router->post('currency/edited/{id:[0-9]+}', 'CurrencyController@edited');
    $router->put('currency/add', 'CurrencyController@add');
    $router->post('currency/delete', 'CurrencyController@delete');
    $router->post('currency/disable', 'CurrencyController@disable');
    $router->post('currency/enable', 'CurrencyController@enable');
    $router->post('currency/import', 'CurrencyController@import');
    $router->get('currency/export', 'CurrencyController@export');

    /**
     * 结算方式
     */
    $router->get('settlementtype', 'SettleMentTypeController@getList');
    $router->get('settlementtype/{id:[0-9]+}', 'SettleMentTypeController@info');
    $router->post('settlementtype/edited/{id:[0-9]+}', 'SettleMentTypeController@edited');
    $router->put('settlementtype/add', 'SettleMentTypeController@add');
    $router->post('settlementtype/delete', 'SettleMentTypeController@delete');
    $router->post('settlementtype/disable', 'SettleMentTypeController@disable');
    $router->post('settlementtype/enable', 'SettleMentTypeController@enable');
    $router->post('settlementtype/import', 'SettleMentTypeController@import');
    $router->get('settlementtype/export', 'SettleMentTypeController@export');
    /**
     * 采购商
     */
    $router->get('purchaser/{id:[0-9]+}', 'PurchaserController@info');
    $router->get('purchaser/list', 'PurchaserController@getList');
    $router->get('purchasers', 'PurchaserController@getAll');
    $router->get('purchaser', 'PurchaserController@tree');
    $router->get('purchaser/number', 'OrgController@number');
    $router->post('purchaser/edited/{id:[0-9]+}', 'PurchaserController@edited');
    $router->put('purchaser/add', 'PurchaserController@add');
    $router->post('purchaser/delete', 'PurchaserController@delete');
    $router->post('purchaser/disable', 'PurchaserController@disable');
    $router->post('purchaser/enable', 'PurchaserController@enable');
    $router->post('purchaser/import', 'PurchaserController@import');
    $router->get('purchaser/export', 'PurchaserController@export');
    /**
     * 用户
     */
    $router->get('user/{id:[0-9]+}', 'UserController@info');
    $router->get('user', 'UserController@getList');
    $router->get('user/persons', 'UserController@persons');
    $router->post('user/edited/{id:[0-9]+}', 'UserController@edited');
    $router->put('user/add', 'UserController@add');
    $router->post('user/delete', 'UserController@delete');
    $router->post('user/disable', 'UserController@disable');
    $router->post('user/enable', 'UserController@enable');

//    $router->post('user/import', 'UserController@import');
    $router->post('user/change/password', 'UserController@change');
    $router->get('user/export', 'UserController@export');
    $router->get('user/pinyin', 'UserController@pinyin');
    $router->get('user/roles', 'UserController@roles');
    $router->get('user/menus', 'UserController@menus');
    $router->get('user/orgs', 'UserController@orgs');
    $router->get('user/orgtree', 'UserController@orgTree');
    $router->get('user/orglist', 'UserController@orgList');
    $router->get('user/getUserByRole', 'UserController@getUserByRole');
    /**
     * 菜单
     */
    $router->get('menus/{id:[0-9]+}', 'MenusController@info');
    $router->get('menus', 'MenusController@getList');
    $router->get('menus/tree', 'MenusController@tree');
    $router->put('menus/add', 'MenusController@add');
    $router->post('menus/disable', 'MenusController@disable');
    $router->post('menus/enable', 'MenusController@enable');
    $router->post('menus/delete', 'MenusController@delete');
    $router->post('menus/edited/{id:[0-9]+}', 'MenusController@edited');
    $router->get('menus/userTree', 'MenusController@userTree');
    $router->get('menus/userMenus', 'MenusController@userMenus');

    /**
     * 权限
     */
    $router->get('permissions/{id:[0-9]+}', 'PermissionsController@info');
    $router->get('permissions', 'PermissionsController@getList');
    $router->get('permissions/tree', 'PermissionsController@tree');
    $router->put('permissions/add', 'PermissionsController@add');
    $router->post('permissions/disable', 'PermissionsController@disable');
    $router->post('permissions/enable', 'PermissionsController@enable');
    $router->post('permissions/delete', 'PermissionsController@delete');
    $router->post('permissions/edited/{id:[0-9]+}', 'PermissionsController@edited');
    /**
     * 供应商分类
     */
    $router->get('supplier/group/{id:[0-9]+}', 'SupplierGroupController@info');
    $router->get('supplier/group', 'SupplierGroupController@getList');
    $router->post('supplier/group/edited/{id:[0-9]+}', 'SupplierGroupController@edited');
    $router->put('supplier/group/add', 'SupplierGroupController@add');
    $router->post('supplier/group/delete', 'SupplierGroupController@delete');
    $router->post('supplier/group/disable', 'SupplierGroupController@disable');
    $router->post('supplier/group/enable', 'SupplierGroupController@enable');
    $router->post('supplier/group/import', 'SupplierGroupController@import');
    $router->get('supplier/group/export', 'SupplierGroupController@export');

    /**
     * 物料分类
     */
    $router->get('material/group/{id:[0-9]+}', 'MaterialGroupController@info');
    $router->get('material/group', 'MaterialGroupController@getList');
    $router->post('material/group/edited/{id:[0-9]+}', 'MaterialGroupController@edited');
    $router->put('material/group/add', 'MaterialGroupController@add');
    $router->post('material/group/delete', 'MaterialGroupController@delete');
    $router->post('material/group/disable', 'MaterialGroupController@disable');
    $router->post('material/group/enable', 'MaterialGroupController@enable');
    $router->post('material/group/import', 'MaterialGroupController@import');
    $router->get('material/group/export', 'MaterialGroupController@export');
    /**
     * 付款条件
     */
    $router->get('paycond/{id:[0-9]+}', 'PaycondController@info');
    $router->get('paycond', 'PaycondController@getList');
    $router->post('paycond/edited/{id:[0-9]+}', 'PaycondController@edited');
    $router->put('paycond/add', 'PaycondController@add');
    $router->post('paycond/delete', 'PaycondController@delete');
    $router->post('paycond/disable', 'PaycondController@disable');
    $router->post('paycond/enable', 'PaycondController@enable');
    $router->post('paycond/import', 'PaycondController@import');
    $router->get('paycond/export', 'PaycondController@export');
    $router->get('paycond/number', 'PaycondController@number');
    /**
     * 角色
     */
    $router->get('roles/{id:[0-9]+}', 'RolesController@info');
    $router->get('roles', 'RolesController@getList');
    $router->get('roles/company', 'RolesController@getRoleByCompany');
    $router->put('roles/add', 'RolesController@add');
    $router->post('roles/disable', 'RolesController@disable');
    $router->post('roles/enable', 'RolesController@enable');
    $router->post('roles/delete', 'RolesController@delete');
    $router->post('roles/edited/{id:[0-9]+}', 'RolesController@edited');
    $router->put('roles/menus/{id:[0-9]+}', 'RolesController@hasMenus');
    $router->put('roles/user/{id:[0-9]+}', 'RolesController@userHasRoles');
    $router->get('roles/menuslist/{id:[0-9]+}', 'RolesController@menusList');
    $router->put('roles/listbyuser/{id:[0-9]+}', 'RolesController@listbyuser');


    /**
     * 评估等级
     */
    $router->get('supplier/evagrade/{id:[0-9]+}', 'SupplierEvaGradeController@info');
    $router->get('supplier/evagrade', 'SupplierEvaGradeController@getList');
    $router->get('supplier/evagrade/number', 'SupplierEvaGradeController@number');
    $router->post('supplier/evagrade/edited/{id:[0-9]+}', 'SupplierEvaGradeController@edited');
    $router->put('supplier/evagrade/add', 'SupplierEvaGradeController@add');
    $router->post('supplier/evagrade/delete', 'SupplierEvaGradeController@delete');
    $router->post('supplier/evagrade/disable', 'SupplierEvaGradeController@disable');
    $router->post('supplier/evagrade/enable', 'SupplierEvaGradeController@enable');
    $router->post('supplier/evagrade/import', 'SupplierEvaGradeController@import');
    $router->get('supplier/evagrade/export', 'SupplierEvaGradeController@export');
    /**
     * 分级方案
     */
    $router->get('supplier/grade/{id:[0-9]+}', 'SupplierGradeController@info');
    $router->get('supplier/grade', 'SupplierGradeController@getList');
    $router->get('supplier/grade/number', 'SupplierGradeController@number');
    $router->post('supplier/grade/edited/{id:[0-9]+}', 'SupplierGradeController@edited');
    $router->put('supplier/grade/add', 'SupplierGradeController@add');
    $router->post('supplier/grade/delete', 'SupplierGradeController@delete');
    $router->post('supplier/grade/disable', 'SupplierGradeController@disable');
    $router->post('supplier/grade/enable', 'SupplierGradeController@enable');
    $router->post('supplier/grade/import', 'SupplierGradeController@import');
    $router->get('supplier/grade/export', 'SupplierGradeController@export');
    /**
     * 配置
     */
//    $router->get('setting/{id:[0-9]+}', 'SettingController@info');
//    $router->get('setting', 'SettingController@getList');
//    $router->put('setting', 'SettingController@updateOrAdd');
    /**
     * 供应商
     */
    $router->get('supplier/{id:[0-9]+}', 'SupplierController@info');
    $router->get('supplier/enterprise_type', 'SupplierController@enterpriseType');
    $router->get('supplier/audit/{id:[0-9]+}', 'SupplierBaseController@auditInfo');
    $router->get('supplier/base/{id:[0-9]+}', 'SupplierBaseController@info');

    $router->get('supplier', 'SupplierBaseController@getList');
    $router->get('supplier/list', 'SupplierBaseController@suppliers');
    $router->get('supplier/manage', 'SupplierController@manage');
    $router->get('supplier/status', 'SupplierBaseController@status');
//    $router->get('supplier/number', 'SupplierBaseController@number');
    $router->post('supplier/company', 'SupplierController@company');
    $router->post('supplier/edited/{id:[0-9]+}', 'SupplierController@edited');
    $router->post('supplier/base/edited/{id:[0-9]+}', 'SupplierBaseController@edited');
    $router->put('supplier/base/add', 'SupplierBaseController@add');
    $router->post('supplier/delete', 'SupplierBaseController@delete');

    $router->post('supplier/disable', 'SupplierBaseController@disable');
    $router->post('supplier/enable', 'SupplierBaseController@enable');
    $router->get('supplier/template', 'SupplierBaseController@template');
    $router->post('supplier/import', 'SupplierBaseController@import');
    $router->get('supplier/export', 'SupplierBaseController@export');
//    $router->get('suppliers', 'SupplierBaseController@getList');
    $router->get('supplier/register', 'SupplierRegisterController@getList');
    $router->get('supplier/notice', 'SupplierController@notice');
    $router->get('supplier/notice/{notice_id:[0-9]+}', 'SupplierController@noticeInfo');
    $router->post('supplier/formal', 'SupplierBaseController@formal');
    /**
     * 
     */
    $router->post('rolesuser', 'RolesUserController@updateOrAdd');
    /**
     * 供应商审核
     */
    $router->get('supplier/audit/pending', 'SupplierAuditController@pending');
    $router->post('supplier/audit', 'SupplierAuditController@audit');
    $router->post('supplier/audit/verify', 'SupplierAuditController@verify');
    $router->get('supplier/audit/history', 'SupplierAuditController@history');
    $router->get('supplier/audit/comments', 'SupplierAuditController@comments');
    $router->get('supplier/audit/status', 'SupplierAuditController@status');
    $router->get('supplier/audit/todo', 'SupplierAuditController@todo');
    $router->get('supplier/audit/progress', 'SupplierAuditController@progress');
    /*
     * 招采
     */
    $router->get('kingdee/kingdeenoticelist', 'KingdeeController@noticeList');
    $router->get('kingdee/noticedetail/{id:[0-9]+}', 'KingdeeController@noticeDetail');
    $router->get('kingdee/announcementList', 'KingdeeController@announcementList');
    $router->get('kingdee/announcementDetail/{id:[0-9]+}', 'KingdeeController@announcementDetail');
    $router->get('kingdee/bidDetail/{id:[0-9]+}', 'KingdeeController@bidDetail');
    $router->get('kingdee/groupList', 'KingdeeController@groupList');
    $router->get('kingdee/search', 'KingdeeController@search');


    /**
     * 税率
     */
    $router->get('taxcategory/{id:[0-9]+}', 'TaxCategoryController@info');
    $router->get('taxcategory', 'TaxCategoryController@getList');
    $router->get('taxcategory/number', 'TaxCategoryController@number');
    $router->post('taxcategory/edited/{id:[0-9]+}', 'TaxCategoryController@edited');
    $router->put('taxcategory/add', 'TaxCategoryController@add');
    $router->post('taxcategory/delete', 'TaxCategoryController@delete');
    $router->post('taxcategory/disable', 'TaxCategoryController@disable');
    $router->post('taxcategory/enable', 'TaxCategoryController@enable');
    $router->post('taxcategory/import', 'TaxCategoryController@import');
    $router->get('taxcategory/export', 'TaxCategoryController@export');

    /**
     * 税种
     */
    $router->get('taxrate/{id:[0-9]+}', 'TaxRateController@info');
    $router->get('taxrate', 'TaxRateController@getList');
    $router->get('taxrate/number', 'TaxRateController@number');
    $router->post('taxrate/edited/{id:[0-9]+}', 'TaxRateController@edited');
    $router->put('taxrate/add', 'TaxRateController@add');
    $router->post('taxrate/delete', 'TaxRateController@delete');
    $router->post('taxrate/disable', 'TaxRateController@disable');
    $router->post('taxrate/enable', 'TaxRateController@enable');
    $router->post('taxrate/import', 'TaxRateController@import');
    $router->get('taxrate/export', 'TaxRateController@export');
    /**
     * 税收制度
     */
    $router->get('taxationsys/{id:[0-9]+}', 'TaxationSysController@info');
    $router->get('taxationsys', 'TaxationSysController@getList');
    $router->get('taxationsys/number', 'TaxationSysController@number');
    $router->post('taxationsys/edited/{id:[0-9]+}', 'TaxationSysController@edited');
    $router->put('taxationsys/add', 'TaxationSysController@add');
    $router->post('taxationsys/delete', 'TaxationSysController@delete');
    $router->post('taxationsys/disable', 'TaxationSysController@disable');
    $router->post('taxationsys/enable', 'TaxationSysController@enable');
    $router->post('taxationsys/import', 'TaxationSysController@import');
    $router->get('taxationsys/export', 'TaxationSysController@export');
    /**
     * 发票类型分组
     */
    $router->get('invoicetype/group/{id:[0-9]+}', 'InvoiceTypeGroupController@info');
    $router->get('invoicetype/group', 'InvoiceTypeGroupController@getList');
    $router->get('invoicetype/group/number', 'InvoiceTypeGroupController@number');
    $router->post('invoicetype/group/edited/{id:[0-9]+}', 'InvoiceTypeGroupController@edited');
    $router->put('invoicetype/group/add', 'InvoiceTypeGroupController@add');
    $router->post('invoicetype/group/delete', 'InvoiceTypeGroupController@delete');
    $router->post('invoicetype/group/disable', 'InvoiceTypeGroupController@disable');
    $router->post('invoicetype/group/enable', 'InvoiceTypeGroupController@enable');
    $router->post('invoicetype/group/import', 'InvoiceTypeGroupController@import');
    $router->get('invoicetype/group/export', 'InvoiceTypeGroupController@export');
    /**
     * 发票类型
     */
    $router->get('invoicetype/{id:[0-9]+}', 'InvoiceTypeController@info');
    $router->get('invoicetype', 'InvoiceTypeController@getList');
    $router->get('invoicetype/number', 'InvoiceTypeController@number');
    $router->post('invoicetype/edited/{id:[0-9]+}', 'InvoiceTypeController@edited');
    $router->put('invoicetype/add', 'InvoiceTypeController@add');
    $router->post('invoicetype/delete', 'InvoiceTypeController@delete');
    $router->post('invoicetype/disable', 'InvoiceTypeController@disable');
    $router->post('invoicetype/enable', 'InvoiceTypeController@enable');
    $router->post('invoicetype/import', 'InvoiceTypeController@import');
    $router->get('invoicetype/export', 'InvoiceTypeController@export');
    /**
     * 询报价
     */
    $router->get('inquiry/{id:[0-9]+}', 'InquiryController@info');
    $router->post('inquiry/{id:[0-9]+}', 'InquiryController@info');
    $router->get('inquiry', 'InquiryController@getList');
    $router->post('inquiry', 'InquiryController@getList');
    $router->get('inquiry/supplier/{id:[0-9]+}', 'InquiryController@supplier');
    $router->get('inquiry/mulquote/{id:[0-9]+}', 'InquiryController@mulquote');
    $router->get('inquiry/number', 'InquiryController@number');
    $router->post('inquiry/number', 'InquiryController@number');
    $router->get('inquiry/open_type', 'InquiryController@openType');
    $router->get('inquiry/bill_status', 'InquiryController@billStatus');
    $router->get('inquiry/sup_scope', 'InquiryController@supScope');
    $router->get('inquiry/tax_cal_type', 'InquiryController@taxCalType');
    $router->get('inquiry/biz_status', 'InquiryController@bizStatus');
    $router->get('inquiry/inv_type', 'InquiryController@invType');
    $router->post('inquiry/edited/{id:[0-9]+}', 'InquiryController@edited');
    $router->put('inquiry/add', 'InquiryController@add');
    $router->post('inquiry/add', 'InquiryController@add');
    $router->post('inquiry/delete', 'InquiryController@delete');
    $router->post('inquiry/disable', 'InquiryController@disable');
    $router->post('inquiry/enable', 'InquiryController@enable');
    $router->post('inquiry/audit', 'InquiryController@audit');
    $router->post('inquiry/import', 'InquiryController@import');
    $router->post('inquiry/copy/{id:[0-9]+}', 'InquiryController@copy');
    $router->post('inquiry/change/{id:[0-9]+}', 'InquiryController@change');
    $router->post('inquiry/revoke/{id:[0-9]+}', 'InquiryController@revoke');
    $router->post('inquiry/stop/{id:[0-9]+}', 'InquiryController@stop');
    $router->get('inquiry/export', 'InquiryController@export');
    $router->post('inquiry/mulround/{id:[0-9]+}', 'InquiryController@mulRound');
    $router->post('inquiry/opening/{id:[0-9]+}', 'InquiryController@opening');
    $router->get('inquiry/notice/{id:[0-9]+}', 'InquiryController@notice');
    $router->get('inquiry/entry/export/{id:[0-9]+}', 'InquiryController@entryExport');
    $router->post('inquiry/entry/import', 'InquiryController@entryImport');
    $router->get('inquiry/entry/template', 'InquiryController@entryTemplate');
    /**
     * 报价
     */
    $router->get('quote/{id:[0-9]+}', 'QuoteController@info');
    $router->post('quote/{id:[0-9]+}', 'QuoteController@info');
    $router->get('quote', 'QuoteController@getList');
    $router->post('quote', 'QuoteController@getList');
    $router->get('quote/export', 'QuoteController@export');
    $router->get('quote/sum_quote/{inquiry_id}', 'QuoteController@sum_quote');

    /**
     * 询价公告
     */
    $router->get('notice/{id:[0-9]+}', 'NoticeController@info');
    $router->get('notice', 'NoticeController@getList');
    $router->get('notice/manage/{id:[0-9]+}', 'NoticeManageController@info');
    $router->get('notice/manage', 'NoticeManageController@getList');
    $router->get('notice/manage/number', 'NoticeManageController@number');
    $router->post('notice/manage/edited/{id:[0-9]+}', 'NoticeManageController@edited');
    $router->put('notice/manage/add', 'NoticeManageController@add');
    $router->post('notice/manage/delete', 'NoticeManageController@delete');
    $router->post('notice/manage/topping', 'NoticeManageController@topping');
    $router->post('notice/manage/cancel ', 'NoticeManageController@cancel');
    $router->get('notice/manage/export', 'NoticeManageController@export');
    $router->get('notice/manage/audit', 'NoticeManageController@audit');
    /**
     * 消息
     */
    $router->get('message/{id:[0-9]+}', 'MessageController@info');
    $router->get('message', 'MessageController@getList');
    $router->post('message/delete', 'MessageController@delete');
    $router->post('message/read', 'MessageController@read');
    $router->post('message/unread', 'MessageController@unread');
    $router->get('message/notReadCount', 'MessageController@notReadCount');
    /**
     * 供应商报价
     */
    $router->get('supplier/quote/{id:[0-9]+}', 'SupplierQuoteController@info');
    $router->get('supplier/quote', 'SupplierQuoteController@getList');

    $router->get('supplier/quote/export', 'SupplierQuoteController@export');
    $router->get('supplier/quote/info/{inquiry_id:[0-9]+}', 'SupplierQuoteController@infoByInquiryId');
    $router->get('supplier/quote/list/{inquiry_id:[0-9]+}', 'SupplierQuoteController@listByInquiryId');
    $router->post('supplier/quote/delete', 'SupplierQuoteController@delete');
//    $router->get('supplier/quote/number', 'SupplierQuoteController@number');
    $router->post('supplier/quote/edited/{id:[0-9]+}', 'SupplierQuoteController@edited');
    $router->put('supplier/quote/add', 'SupplierQuoteController@add');
    $router->get('supplier/quote/entry/export/{id:[0-9]+}', 'SupplierQuoteController@entryExport');
    $router->post('supplier/quote/entry/import', 'SupplierQuoteController@entryImport');
    /**
     * 供应商消息
     */
    $router->get('supplier/message/{id:[0-9]+}', 'SupplierMessageController@info');
    $router->get('supplier/message', 'SupplierMessageController@getList');
    $router->post('supplier/message/delete', 'SupplierMessageController@delete');
    $router->post('supplier/message/read', 'SupplierMessageController@read');
    $router->post('supplier/message/unread', 'SupplierMessageController@unread');
    $router->get('supplier/message/notReadCount', 'SupplierMessageController@notReadCount');

    /**
     * 默认联系人
     */
    $router->get('supplier/default/contact', 'SupplierController@defaultContact');
    /**
     * 比价
     */
    $router->get('compare/{id:[0-9]+}', 'CompareController@info');
    $router->post('compare/{id:[0-9]+}', 'CompareController@info');
    $router->post('compare', 'CompareController@getList');
    $router->get('compare', 'CompareController@getList');
    $router->get('compare/number', 'CompareController@number');
    $router->post('compare/edited/{id:[0-9]+}', 'CompareController@edited');
    $router->put('compare/add', 'CompareController@add');
    $router->post('compare/delete', 'CompareController@delete');
    $router->post('compare/audit/verify/{id:[0-9]+}', 'CompareController@verify');
    $router->post('compare/audit/stop/{id:[0-9]+}', 'CompareController@stop');
    $router->get('compare/notice', 'CompareController@notice');
    $router->get('compare/export', 'CompareController@export');
    $router->get('compare/getListGroupBySupplier', 'CompareController@getListGroupBySupplier');
    $router->post('compare/getListGroupBySupplier', 'CompareController@getListGroupBySupplier');
    /**
     * 供应商询单
     */
    $router->get('supplier/inquiry', 'SupplierInquiryController@getList');
    $router->get('supplier/inquiry/{id:[0-9]+}', 'SupplierInquiryController@info');
    $router->get('supplier/inquiry/export', 'SupplierInquiryController@export');
    $router->post('supplier/inquiry/quote/{id:[0-9]+}', 'SupplierInquiryController@quote');
    $router->post('supplier/inquiry/unquote/{id:[0-9]+}', 'SupplierInquiryController@unQuote');


    /**
     * 供应商人员管理
     */
    $router->get('supplier/user', 'SupplierUserController@getList');
    $router->get('supplier/user/{id:[0-9]+}', 'SupplierUserController@info');
    $router->post('supplier/user/edited/{id:[0-9]+}', 'SupplierUserController@edited');
    $router->put('supplier/user/add', 'SupplierUserController@add');
    $router->post('supplier/user/delete', 'SupplierUserController@delete');
    $router->post('supplier/user/disable', 'SupplierUserController@disable');
    $router->post('supplier/user/enable', 'SupplierUserController@enable');
    $router->get('supplier/user/export', 'SupplierUserController@export');
    $router->post('supplier/user/change/password', 'SupplierUserController@change');
    $router->get('supplier/user/pinyin', 'SupplierUserController@pinyin');
    $router->get('supplier/user/roles', 'SupplierUserController@roles');
    $router->get('supplier/user/menus', 'SupplierUserController@menus');
    $router->post('supplier/rolesuser', 'SupplierUserController@rolesuser');
    /*
     * 修改账号
     */
    $router->post('change_account/email', 'ChangeAccountController@email');
    $router->post('change_account/phone', 'ChangeAccountController@phone');
    $router->post('change_account/account', 'ChangeAccountController@index');
    $router->post('change_account/phoneVerify', 'ChangeAccountController@phoneVerify');
    $router->post('change_account/emailVerify', 'ChangeAccountController@emailVerify');
    /*
     * 竞价
     */
    $router->get('bidbill/{id:[0-9]+}', 'BidBillController@info');
    $router->get('bidbill', 'BidBillController@getList');
    $router->get('bidbill/number', 'BidBillController@number');
    $router->post('bidbill/edited/{id:[0-9]+}', 'BidBillController@edited');
    $router->put('bidbill/add', 'BidBillController@add');
    $router->post('bidbill/delete', 'BidBillController@delete');
    $router->get('bidbill/export', 'BidBillController@export');
    $router->post('bidbill/change/{id:[0-9]+}', 'BidBillController@change');
    $router->get('bidbill/hall', 'BidBillController@hall');
    $router->get('bidbill/suppliers/{id:[0-9]+}', 'BidBillController@suppliers');
    $router->get('bidbill/pays/{id:[0-9]+}', 'BidBillController@pays');
    $router->get('bidbill/pays', 'BidBillPayController@getList');
    $router->get('bidbill/decision', 'BidBillController@decision_list');
    $router->get('bidbill/winning/{id:[0-9]+}', 'BidBillController@winning');
    $router->post('bidbill/check/{id:[0-9]+}', 'BidBillController@check');
    $router->post('bidbill/pay/{id:[0-9]+}', 'BidBillController@pay');
    $router->get('bidbill/payinfo/{id:[0-9]+}', 'BidBillPayController@info');
    $router->post('bidbill/termination/{id:[0-9]+}', 'BidBillController@termination');
    $router->post('bidbill/decision/{id:[0-9]+}', 'BidBillController@decision');
    $router->post('bidbill/start/{id:[0-9]+}', 'BidBillController@start');
    $router->post('bidbill/stop/{id:[0-9]+}', 'BidBillController@stop');
    $router->post('bidbill/begin/{id:[0-9]+}', 'BidBillController@begin');
    $router->post('bidbill/returns/{id:[0-9]+}', 'BidBillController@returns');
    $router->post('bidbill/return/{id:[0-9]+}', 'BidBillController@returnDeposit');
    $router->get('bidbill/hall/{id:[0-9]+}', 'BidBillController@hallInfo');
    $router->post('bidbill/bindUid/{id:[0-9]+}', 'BidBillController@bindUid');
    $router->post('bidbill/bindGroup/{id:[0-9]+}', 'BidBillController@bindGroup');
    $router->post('bidbill/offline/{id:[0-9]+}', 'BidBillController@offline');
    $router->post('bidbill/finished/{id:[0-9]+}', 'BidBillController@finished');
    $router->post('bidbill/pay/audit/{id:[0-9]+}', 'BidBillPayController@payAudit');
    $router->post('bidbill/return/audit/{id:[0-9]+}', 'BidBillPayController@returnAudit');
    $router->get('supplier/bidbill/{id:[0-9]+}', 'SupplierBidBillController@info');
    $router->get('supplier/bidbill', 'SupplierBidBillController@getList');
    $router->get('supplier/bidbill/pays', 'SupplierBidBillController@pays');
    $router->post('supplier/bidbill/pay/{id:[0-9]+}', 'SupplierBidBillController@pay');
    $router->get('supplier/bidbill/payinfo/{id:[0-9]+}', 'SupplierBidBillController@payInfo');
    $router->get('supplier/bidbill/hall', 'SupplierBidBillController@hall');
    $router->get('supplier/bidbill/hall/{id:[0-9]+}', 'SupplierBidBillController@hallInfo');
    $router->post('supplier/bidbill/quote/{id:[0-9]+}', 'SupplierBidBillController@quote');
    $router->post('supplier/bidbill/bindUid/{id:[0-9]+}', 'SupplierBidBillController@bindUid');
    $router->post('supplier/bidbill/bindGroup/{id:[0-9]+}', 'SupplierBidBillController@bindGroup');
    $router->post('supplier/bidbill/offline/{id:[0-9]+}', 'SupplierBidBillController@offline');
    $router->post('supplier/bidbill/signup/{id:[0-9]+}', 'SupplierBidBillController@signUp');
    $router->post('supplier/bidbill/unsignup/{id:[0-9]+}', 'SupplierBidBillController@unSignUp');
    $router->get('bidbill/export/{id:[0-9]+}', 'BidBillController@entryExport');
    $router->post('bidbill/import', 'BidBillController@entryImport');
    $router->get('bidbill/entry/export/{id:[0-9]+}', 'BidBillController@entryExport');
    $router->post('bidbill/entry/import', 'BidBillController@entryImport');
    $router->get('bidbill/entry/template', 'BidBillController@entryTemplate');

    /*
     * 供应商准入配置
     */
    $router->get('access/setting/{purchaser_id:[0-9]+}', 'AccessSettingController@getList');
    $router->put('access/setting', 'AccessSettingController@edited');
});
$router->group(['prefix' => 'admin', 'middleware' => [
        'admin_log']], function () use ($router) {
    $router->put('supplier/register', 'SupplierController@Register');
    $router->post('supplier/phone_email', 'SupplierController@phoneEmail');
});

$router->group(['prefix' => 'admin', 'middleware' => [
        'admin_log']], function () use ($router) {
    $router->put('purchaser/register', 'PurchaserController@Register');
    $router->post('purchaser/phone_email', 'PurchaserController@phoneEmail');
});
