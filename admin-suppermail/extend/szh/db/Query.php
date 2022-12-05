<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 8/6/21
 * Time: 10:15 AM
 */

namespace szh\db;

use think\Loader;

class Query extends \think\db\Query
{

    /**
     * 得到当前或者指定名称的数据表
     * @access public
     * @param string $name
     * @return string
     */
    public function getTable($name = '')
    {
        if ($name || empty($this->table)) {
            $name      = $name ?: $this->name;
            $tableName = $this->prefix;
            if ($name) {
                $tableName .= Loader::parseName($name);
            }
        } else {
            // todo: 修改源码部分
            $prefix = $this->prefix;
            $tableName = $prefix . $this->table;
        }
        return $tableName;
    }

    /**
     * 指定正则查询条件
     * @access public
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     * @return $this
     */
    public function whereRegexp($field, $condition, $logic = 'AND')
    {
        if (config('database.type') == 'mysql') {
            // mysql 语法
            $this->whereExp($field, "REGEXP '$condition'", $logic);
        } else {
            // oracle 语法
            $this->where("regexp_like($field, '$condition')", $logic);
        }

        return $this;
    }


//    /**
//     * 事件标识
//     * @param null $event (事件标识)
//     * @param null $eventData (事件额外参数)
//     */
//    public function track($event=null, $eventData=null)
//    {
//
//        // track();
//
//        // secho(json_encode($this->getOptions()));
//        print_r($this->getOptions());
//
//        return $this;
//    }

}