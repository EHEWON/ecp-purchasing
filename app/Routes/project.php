<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$router->group(['prefix' => 'admin', 'middleware' => [
        'permission',
        'admin_log']], function () use ($router) {
    /*
     * 招标立项
     */
    $router->get('bidmodes', 'BidModeController@getAll');
    $router->get('purtypes', 'PurTypeController@getAll');
    $router->get('project', 'ProjectController@getList');
    $router->get('project/suppliers', 'ProjectController@suppliers'); //招标供应商
    $router->get('project/supplier/project', 'ProjectController@supplierList'); //入围 供应商 中标供应商 招标项目
    $router->post('project/shortlist/{id:[0-9]+}', 'ProjectController@shortlist'); //招标供应商
  
    $router->get('project/members', 'ProjectController@members'); //招标小组
    $router->get('project/entry/export/{id:[0-9]+}', 'ProjectController@entryExport');
    $router->get('project/entry/template', 'ProjectController@entryTemplate');
    $router->get('project/export', 'ProjectController@export');
    $router->get('project/export/{id:[0-9]+}', 'ProjectController@entryExport');
    $router->get('project/number', 'ProjectController@number');
    $router->get('project/pays', 'ProjectPayController@getList');
    $router->get('project/pays/{id:[0-9]+}', 'ProjectPayController@info');
    $router->get('project/paysByProject/{id:[0-9]+}', 'ProjectPayController@getListByProject');
    $router->get('project/{id:[0-9]+}', 'ProjectController@info');
    $router->get('project/decision', 'ProjectDecisionController@getList');
    $router->get('project/doc/{id:[0-9]+}', 'ProjectDocController@info');
    $router->get('project/{group:COMMERCIAL|TECHNICAL|COMMITMENT_LETTER|OTHER}/{quote_id:[0-9]+}', 'ProjectController@download');
    $router->post('project/doc/edited/{id:[0-9]+}', 'ProjectDocController@edited');
    $router->get('project/open/{id:[0-9]+}', 'ProjectOpenController@info');
    $router->post('project/open/edited/{id:[0-9]+}', 'ProjectOpenController@edited');
    $router->get('project/decision/{id:[0-9]+}', 'ProjectDecisionController@info');
    $router->post('project/decision/delete', 'ProjectDecisionController@delete');
    $router->post('project/decision/edited/{id:[0-9]+}', 'ProjectDecisionController@edited');
    $router->post('project/change/{id:[0-9]+}', 'ProjectController@change');
    $router->post('project/delete', 'ProjectController@delete');
    $router->post('project/edited/{id:[0-9]+}', 'ProjectController@edited');
    $router->post('project/entry/import', 'ProjectController@entryImport');
    $router->get('project/invalid/{id:[0-9]+}', 'ProjectController@invalidInfo');
    $router->get('project/quote/{id:[0-9]+}', 'ProjectController@quoteInfo');
    $router->post('project/invalid', 'ProjectController@invalid');
    $router->post('project/pays/audit/{id:[0-9]+}', 'ProjectPayController@audit');
    $router->post('project/pays/returnAudit/{id:[0-9]+}', 'ProjectPayController@returnAudit');
    $router->put('project/add', 'ProjectController@add');
    $router->get('supplier/project/pays', 'SupplierProjectPayController@getList');
    $router->get('supplier/project/pays/{id:[0-9]+}', 'SupplierProjectPayController@info');
    $router->post('supplier/project/pays/edit', 'SupplierProjectPayController@edit');
    $router->get('supplier/project/paysByProject/{id:[0-9]+}', 'SupplierProjectPayController@getListByProject');
    $router->get('supplier/project', 'SupplierProjectController@getList');
    $router->get('supplier/project/{id:[0-9]+}', 'SupplierProjectController@info');
    $router->get('supplier/project/notice/{id:[0-9]+}', 'SupplierProjectController@noticeInfo');
    $router->get('supplier/project/cmfnotice/{id:[0-9]+}', 'SupplierProjectController@cmfInfo');
    $router->post('supplier/project/signup/{id:[0-9]+}', 'SupplierProjectController@signUp');
    $router->post('supplier/project/unsignup/{id:[0-9]+}', 'SupplierProjectController@unSignUp');
    $router->post('project/shortlist/{id:[0-9]+}', 'ProjectController@shortlist'); //入围供应商
    $router->post('project/addshortlist/{id:[0-9]+}', 'ProjectController@shortlistaddData'); //增补供应商
    $router->get('project/publish/{id:[0-9]+}', 'ProjectPublishController@info');
    $router->post('project/publish/edited/{id:[0-9]+}', 'ProjectPublishController@edited');
    $router->post('supplier/project/quote/{id:[0-9]+}', 'SupplierProjectController@quote');
    $router->post('supplier/project/quote/edited/{id:[0-9]+}', 'SupplierProjectController@quoteedited');
    $router->get('supplier/project/quote/info/{id:[0-9]+}', 'SupplierProjectController@quoteinfo');
    $router->get('supplier/project/publishDownload/{id:[0-9]+}', 'SupplierProjectController@publishDownload');
    $router->post('supplier/project/{group:TECHNICAL|COMMERCIAL|PUBLISH_DOWNLOAD}/{id:[0-9]+}', 'SupplierProjectController@docDownload');
    $router->get('supplier/project/download/{group:TECHNICAL|COMMERCIAL|PUBLISH_DOWNLOAD}/{id:[0-9]+}', 'SupplierProjectController@download');
   

    /*
     * 采购项目
     */
    $router->get('purprojects', 'PurProjectController@getAll');
    $router->get('purproject', 'PurProjectController@getList');

    /*
     * 采购类型
     */
    $router->get('purtype', 'PurTypeController@getList');

    /*
     * 采购方式
     */
    $router->get('bidmode', 'BidModeController@getList');
    $router->post('bidmode/enable', 'BidModeController@enable');

    /**
     * 计价模式
     */
    $router->get('valuationmodes', 'ValuationModeController@getAll');
    $router->get('valuationmode', 'ValuationModeController@getList');

   
});
