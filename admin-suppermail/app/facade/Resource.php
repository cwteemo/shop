<?php
// +--
// | Date: 2021-09-25 19:06
// | Remark:
// |

namespace app\facade;

use app;
use think;
use think\Facade;

/**
 *
 * Class Resource
 * @package app\facade
 * @see \app\logic\Resource
 * @mixin \app\logic\Resource
 * @method static think\db\BaseQuery parseSelectParam(array $param, app\model\Base $model)
 * @method static think\db\BaseQuery parseSpecialField(array $param, app\model\Base $model)
 * @method static array parseSelectField($field, $value, $where=[])
 */
class Resource extends Facade
{
    protected static function getFacadeClass()
    {
        return '\app\logic\Resource';
    }
}
