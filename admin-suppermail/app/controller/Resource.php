<?php
/**
 * Created by PhpStorm.
 * User: cw
 * Date: 9/15/21
 * Time: 11:17 AM
 */

namespace app\controller;

use app\BaseController;
use app\logic\SnowFlake;
use think\db\BaseQuery;
use think\helper\Str;
use think\Collection;
use app\Request;
use app\model;
use app\facade;

/**
 * Class Resource
 * @package app\controller
 */
class Resource extends BaseController
{

    // 是否需要分页 (默认为不分页)
    protected $paginate = false;

    // 列表字段
    protected $listField = 'list';

    // 详情返回字段
    protected $detailField = null;

    // list查询条件
    protected $listQuery = null;

    /**
     * 列表分组
     *
     * @param $data
     * @return mixed
     */
    protected function group(Collection $data) { return $data; }

    /**
     * 列表排序规则
     *
     * @return array
     */
    protected function order()
    {
        return [$this->model->getPk() => 'DESC'];
    }

    // list查询条件（子类可复写用来修改查询列表的条件）
    public function getListQuery()
    {
        return $this->request->param();
    }

    /**
     * 显示资源列表
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function index()
    {

        $request    = $this->request;
        $limit      = $request->param('limit', 30);
        $page       = $request->param('page', 0);
        $model      = $this->model;

        $list_field  = $this->listField;
        if ($request->platform === PLATFORM_APP) {
            // app 平台下统一使用 list 作为列表字段（强类型语言容易维护；）（admin结构问题导致不能全为list字段 node 和 link）
            $list_field = "list";
        }

        $list_query  = $this->getListQuery();

        // 筛选
        $model      = facade\Resource::parseSelectParam($list_query, $model);

        $list = $model->order($this->order());

        $res = ['loaded' => true];
        if ($this->paginate) {
            $list = $list->paginate([
                'list_rows' => $limit,
                'page'      => $page
            ]);

            $list = $list->items();
            $res['total'] = $list->total();
        } else {
            $list = $this->group($list->select());

        }
        $res[$list_field] = $list;
        $res['emptyText'] = count($list) > 0 ? ' ' : '没有更多数据了';
        return $res;
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create() {}

    /**
     * 保存新建的资源
     *
     * @param Request $request
     * @return array
     * @throws \think\Exception
     */
    public function save(Request $request)
    {
        $data = $request->post();

        $data['id'] = SnowFlake::generateParticle();
        $data['uid'] = $request->uid;

        // insert 验证
        $this->validate($data, class_basename($this).'.insert');

        $this->model->save($data);

        return $this->model->toArray();
    }

    /**
     * 显示指定的资源
     *
     * @return mixed
     */
    public function read()
    {
        $where = \request()->get();
        return $this->model->where($where)->find();
    }

    /**
     * 显示编辑资源
     *
     * @return mixed
     */
    public function edit()
    {
        $where = \request()->get();
        $res = $this->model_class::getEdit($where);
        if (is_null($this->detailField)) {
            return $res;
        }
        return [
            $this->detailField => $res
        ];
    }

    /**
     * 保存更新的资源
     *
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $data = $request->put();

        // update 验证
        $this->validate($data, class_basename($this).'.update');

        $model = $this->model;
        if (method_exists($model, 'getDeleteTimeField')) {
            $delete = $model->getDeleteTimeField();
            if (array_key_exists($delete, $data) && empty($data[$delete])) {
                // 软删恢复
                $model = $model->onlyTrashed();
            }
        }

        $model->update($data);

        return [];
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        $where = \request()->delete();

        // 条件不存在，就不进行删除
        if (count($where) == 0) {
            return [];
        }

        $this->model_class::destroy($where);

        // $res = $this->model->where($where)->select();
        // if (!$res->isEmpty()) {
        //     foreach ($res as $item) {
        //         $item->delete();
        //     }
        // }

        return [];
    }
}