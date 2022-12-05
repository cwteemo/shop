<?php
// +--
// | Date: 2021-09-15 15:14
// | Remark:
// |

namespace app\logic;

use app\model;
use think\db\BaseQuery;
use think\helper\Str;

class Resource
{
    // 特殊字段
    private $special_fields = [
        // 字段和模型函数的映射
        '_trashed' => null,
    ];

    /**
     * 解析查询参数
     *
     * @param array $param
     * @param model\Base $model
     * @return BaseQuery
     */
    function parseSelectParam(array $param, model\Base $model): BaseQuery
    {
        $tableFields = array_keys($model->getSchema());

        $where = [];

        if (in_array('uid', $tableFields) && request()->platform !== PLATFORM_APP) {
            // 筛选（筛选当前用户下的数据）
            $where[] = ['uid', '=', request()->uid];
        }

        foreach ($param as $field => $value) {
            // !empty($value) &&
            if (in_array($field, $tableFields)) {
                $this->parseSelectField($field, $value, $where);
            }
        }

        return $this->parseSpecialField($param, $model)->where($where);
    }


    /**
     * 特殊参数处理（主要处理 Query 的链式操作）
     *
     * （软删）_trashed: only | with
     * @return BaseQuery
     */
    function parseSpecialField(array $param, model\Base $model): BaseQuery
    {
        foreach ($this->special_fields as $field => $method) {
            if (!empty($param[$field])) {
                $value = $param[$field];
                if (!$method) {
                    $method = Str::camel($value . $field);
                }
                if (method_exists($model, $method)) {
                    $model = $model->$method($value);
                }
            }
        }

        if ($model instanceof model\Base) {
            $model = $model->db();
        }
        return $model;
    }

    /**
     * 解析查询字段
     *
     * @param $field
     * @param $value
     * @param array $where
     */
    function parseSelectField($field, $value, &$where=[]): array
    {

        $lowerField = strtolower($field);
        // 时间相关 $value 兼容 'date_str' | 'start_time~end_time' | ['start_time', 'end_time']
        if (Str::contains($lowerField, ['time', 'date'])) {
            if (is_string($value)) {
                if (strpos($value, '~')) {
                    $value = explode('~', $value);
                } else {
                    $value = [$value, date("Y-m-d 23:59:59", strtotime($value))];
                }
            }

            $where[] = [$field, 'between time', sortTime($value)];
        } elseif (is_array($value)) {
            if (strtoupper($value[0]) === 'OR') {
                // 支持 a => ['OR', 'a', 'b']
                $where[] = function($q) use ($field, $value) {
                    $q_where = [];
                    foreach ($value as $val) {
                        if (strtoupper($val) !== 'OR') {
                            $this->parseSelectField($field, $val, $q_where);
                        }
                    }
                    $q->whereOr($q_where);
                };

            } else {
                // ['>', 1]
                $where[] = [$field, $value[0], $value[1]];
            }
        } elseif (is_numeric($value)) {
            $where[] = [$field, '=', $value];
        } elseif (is_string($value)) {
            if (empty($value)) {
                $where[] = [$field, '=', null];
            } else {
                // 文本
                // 如果使用通配符%前置将忽略索引，可以通过覆盖索引解决
                $where[] = [$field, 'like', "$value%"];
            }
        } elseif (is_null($value)) {
            $where[] = [$field, '=', null];
        }

        return $where;
    }
}
