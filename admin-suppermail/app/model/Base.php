<?php


namespace app\model;


use think\Model;
use think\model\concern\SoftDelete;

class Base extends Model
{
    use SoftDelete;
    protected $pk = 'id';

    protected $deleteTime = 'delete_time';

    const edit_fields = [];

    // 当前模型过滤的字段
    public function getIgnoreFields(): array
    {
        return [];
    }

    /**
     * 获取软删字段
     *
     * @return string
     */
    public function getDeleteTimeField()
    {
        return $this->deleteTime;
    }


    /**
     * 获取 edit 详情
     *
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function getEdit($where)
    {
        // 添加一些额外的 xxxable 属性
        return (new static)->append(static::edit_fields)->find($where);
    }

    /**
     * 获取数据库字段
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }
}