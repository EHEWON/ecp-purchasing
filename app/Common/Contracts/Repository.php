<?php

namespace App\Common\Contracts;

use Illuminate\Database\Eloquent\Model;
use App\Common\Models\{
    Permissions,
    RoleHasPermissions,
    RoleUsers,
    Roles,
    UserSupplier
};
use Illuminate\Support\Facades\{
    Auth,
    Redis
};
use Illuminate\Http\Request;

abstract class Repository {

    protected $model;
    protected $lang = 'en';
    protected $supplierId = '';
    protected $purchaserId = '';

    /**
     * __construct.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model) {
        $this->lang = app('translator')->getLocale();
        $this->model = $model;
    }

    public function setLang($lang) {
        $this->lang = $lang;
    }

    /**
     * 数据插入
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function insert($data) {
        return $this->newQuery()->insert($data);
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*']) {
        return $this->newQuery()->find($id, $columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*']) {
        return $this->newQuery()->findMany($ids, $columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*']) {
        return $this->newQuery()->findOrFail($id, $columns);
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrNew($id, $columns = ['*']) {
        return $this->newQuery()->findOrNew($id, $columns);
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes, array $values = []) {
        return $this->newQuery()->firstOrNew($attributes, $values);
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes, array $values = []) {
        return $this->newQuery()->firstOrCreate($attributes, $values);
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = []) {
        return $this->newQuery()->updateOrCreate($attributes, $values);
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*']) {
        return $this->newQuery()->firstOrFail($columns);
    }

    /**
     * Mass 创建用户.
     *
     * @param  array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Throwable
     */
    public function create($attributes) {
        return tap($this->newInstance(), function ($instance) use ($attributes) {
            $instance->fill($attributes)->saveOrFail();
        });
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Throwable
     */
    public function forceCreate($attributes) {
        return tap($this->newInstance(), function ($instance) use ($attributes) {
            $instance->forceFill($attributes)->saveOrFail();
        });
    }

    /**
     * update.
     *
     * @param  array $attributes
     * @param  mixed $id
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Throwable
     */
    public function update($id, $attributes) {
        return tap($this->findOrFail($id), function ($instance) use ($attributes) {
            $instance->fill($attributes)->saveOrFail();
        });
    }

    /**
     * forceCreate.
     *
     * @param  array $attributes
     * @param  mixed $id
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Throwable
     */
    public function forceUpdate($id, $attributes) {
        return tap($this->findOrFail($id), function ($instance) use ($attributes) {
            $instance->forceFill($attributes)->saveOrFail();
        });
    }

    /**
     * delete.
     *
     * @param  mixed $id
     * @return bool|null
     */
    public function delete($id) {
        return $this->find($id)->delete();
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @param  mixed $id
     * @return bool|null
     */
    public function restore($id) {
        return $this->newQuery()->restore($id);
    }

    /**
     * Force a hard delete on a soft deleted model.
     *
     * This method protects developers from running forceDelete when trait is missing.
     *
     * @param  mixed $id
     * @return bool|null
     */
    public function forceDelete($id) {
        return $this->findOrFail($id)->forceDelete();
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newInstance($attributes = [], $exists = false) {
        return $this->getModel()->newInstance($attributes, $exists);
    }

    public function get($criteria = [], $columns = ['*']) {
        return $this->matching($criteria)->get($columns);
    }

    public function chunk($criteria, $count, callable $callback) {
        return $this->matching($criteria)->chunk($count, $callback);
    }

    public function each($criteria, callable $callback, $count = 1000) {
        return $this->matching($criteria)->each($callback, $count);
    }

    public function first($criteria = [], $columns = ['*']) {
        return $this->matching($criteria)->first($columns);
    }

    public function paginate($criteria = [], $perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
        return $this->matching($criteria)->paginate($perPage, $columns, $pageName, $page);
    }

    public function simplePaginate($criteria = [], $perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
        return $this->matching($criteria)->simplePaginate($perPage, $columns, $pageName, $page);
    }

    public function count($criteria = [], $columns = '*') {
        return (int) $this->matching($criteria)->count($columns);
    }

    public function min($criteria, $column) {
        return $this->matching($criteria)->min($column);
    }

    public function max($criteria, $column) {
        return $this->matching($criteria)->max($column);
    }

    public function sum($criteria, $column) {
        $result = $this->matching($criteria)->sum($column);
        return $result ?: 0;
    }

    public function avg($criteria, $column) {
        return $this->matching($criteria)->avg($column);
    }

    public function average($criteria, $column) {
        return $this->avg($criteria, $column);
    }

    public function matching($criteria) {
        $criteria = is_array($criteria) === false ? [$criteria] : $criteria;
        return array_reduce($criteria, function ($query, $criteria) {
            $criteria->each(function ($method) use ($query) {
                call_user_func_array([$query, $method->name], $method->parameters);
            });
            return $query;
        }, $this->newQuery());
    }

    public function getQuery($criteria = []) {
        return $this->matching($criteria)->getQuery();
    }

    public function getModel() {
        return $this->model instanceof Model ? clone $this->model : $this->model->getModel();
    }

    public function newQuery() {
        return $this->model instanceof Model ? $this->model->newQuery() : clone $this->model;
    }

    public function __toString() {
        return get_called_class();
    }

    public function matchs(&$qurey, $field, $matchKey, $matchValue) {
        switch ($matchKey) {
            case 'eq':
                $qurey->where($field, '=', $matchValue);
                break;
            case 'neq':
                $qurey->where($field, '<>', $matchValue);
                break;
            case 'like':
                $qurey->where($field, 'like', '%' . $matchValue . '%');
                break;
            case 'nlike':
                $qurey->where($field, 'like', '%' . $matchValue . '%');
                break;
            case 'empty':
                $qurey->where(function($q)use($field) {
                    $q->whereNull($field)
                            ->orWhere($field, '');
                });
                break;
            case 'nempty':
                $qurey->where(function($q)use($field) {
                    $q->whereNotNull($field)
                            ->where($field, '<>', '');
                });
                break;
            case 'pre':
                $qurey->where($field, 'like', $matchValue . '%');
                break;
            case 'last':
                $qurey->where($field, 'like', '%' . $matchValue);
                break;
        }
    }

    public function getPPurchaserId() {
        if (!empty($this->purchaserId)) {
            return $this->purchaserId;
        }
        $authorization = Auth::guard('admin')->getToken();

        $redisKey = md5($authorization);
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $this->purchaserId = Redis::get($redisKey);
        }

        return !empty($this->purchaserId) ? $this->purchaserId : 1;
    }

    public function getPSupplierId() {
        if (!empty($this->supplierId)) {
            return $this->supplierId;
        }
        $authorization = Auth::guard('admin')->getToken();
        if (empty($authorization)) {
            return;
        }
        $redisKey = md5($authorization) . ':supplier_id';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $this->supplierId = Redis::get($redisKey);
            if (!empty($this->supplierId)) {
                return $this->supplierId;
            }
        }
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $this->supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        Redis::set($redisKey, $this->supplierId, 86400);
        return $this->supplierId;
    }

    public function getScopes($route) {
        $authorization = Auth::guard('admin')->getToken();
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        if (empty($authorization)) {
            return ['scopes' => [], 'team_id' => '', 'user_id' => null];
        }
        return ['scopes' => ['ALL'], 'team_id' => '', 'user_id' => $userId];
    }

    public function getSupplierScopes($admin, $route) {
        $userId = $admin->user_id;

        $roleHasPermissions = (new RoleHasPermissions())->getTable();
        $permissions = (new Permissions())->getTable();
        $roleUsers = (new RoleUsers())->getTable();
        $roles = (new Roles)->getTable();
        $supplierId = $this->getPSupplierId();
        if (empty($supplierId)) {
            return ['scopes' => [], 'team_id' => $supplierId, 'user_id' => $userId];
        }
        $query = RoleUsers::from($roleUsers . ' as ru')
                ->join($roleHasPermissions . ' as rp', function($join) {
                    $join->on('ru.role_id', '=', 'rp.role_id')
                    ->where('rp.deleted_flag', 'N');
                })
                ->join($roles . ' as r', function($join) {
                    $join->on('r.id', '=', 'ru.role_id')
                    ->where('r.status', 'NORMAL')
                    ->whereIn('r.role_group', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                    ->where('r.deleted_flag', 'N');
                })
                ->join($permissions . ' as p', function($join) {
                    $join->on('p.id', '=', 'rp.perm_id')
                    ->whereIn('p.permission_type', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                    ->where('p.deleted_flag', 'N');
                })
                ->whereIn('ru.role_group', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                ->where('ru.deleted_flag', 'N')
                ->where(function($q)use($route) {
            $q->where('p.route', $route)
            ->orWhere('p.route', ltrim($route, '/'));
        });
        $query->where('ru.team_id', $supplierId);
        $query->where('ru.user_id', $userId);
        $query->groupBy('rp.scope');
        $obejct = $query->pluck('rp.scope');
        if (empty($obejct)) {
            return ['scopes' => [], 'team_id' => $supplierId, 'user_id' => $userId];
        }
        return ['scopes' => $obejct->toArray(), 'team_id' => $supplierId, 'user_id' => $userId];
    }

    public function getTimeByType($type) {
        $createAts = [];
        switch (strtolower($type)) {
            case 'today':
                $createAts[0] = date('Y-m-d');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'this_week':
                $time = time();
                $createAts[0] = date('Y-m-d', strtotime('this week Monday', $time));
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'past_week':
                $time = time();
                $createAts[0] = date('Y-m-d', strtotime('-6 days', $time));
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'last_week':
                $time = time();
                $createAts[0] = date('Y-m-d', strtotime('last week Monday', $time));
                $createAts[1] = date('Y-m-d 23:59:59', strtotime('this week Monday', $time) - 1);
                break;
            case 'this_month':
                $createAts[0] = date('Y-m-01');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'last_month':
                $createAts[0] = date('Y-m-01', strtotime('-1 months'));
                $createAts[1] = date('Y-m-d H:i:s', strtotime(date('Y-m-01')) - 1);
                break;
            case 'last_3_months':
                $createAts[0] = date('Y-m-d', strtotime('-3 months'));
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            default :
                $createAts[0] = date('Y-m-d');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
        }
        return $createAts;
    }

    protected function getPage(&$qurey, Request $request) {
        $condition = $request->all();
        $pageSize = 50;
        if (isset($condition['pagesize'])) {
            $pageSize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 50;
        } elseif (isset($condition['limit'])) {
            $pageSize = intval($condition['limit']) > 0 ? intval($condition['limit']) : 50;
        }
        $page = !empty($request->page) && (int) $request->page > 0 ? ((int) $request->page - 1) * $pageSize : 0;
        $qurey->offset($page)->limit($pageSize);
    }

}
