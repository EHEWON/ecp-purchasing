<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Admin\Admin;
use Illuminate\Support\Facades\Route;

/**
 * 菜单收藏
 */
class QuickMenuController extends Controller
{
    public function getRules(){
        // TODO: Implement getRules() method.
    }

    /**
     * 列表页
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){

        return Admin::service('QuickMenuService')->run();
    }

    /**
     * 新增
     * @param Request $request 
     */
    public function add(Request $request){

        return Admin::service('QuickMenuService')->pass($request->post())->runTransaction('add');
    }

    /**
     * 删除
     * @param Request $request 
     */
    public function delete(Request $request){

        return Admin::service('QuickMenuService')->pass($request->post())->runTransaction('deleteData');
    }
}
