<?php
// +--
// | Date: 2021-09-25 19:37
// | Remark:
// |

namespace app\validate;

class ShopGoods extends Base
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'label' => 'max:128',
        'name' => 'max:128',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'id.require' => 'id 必须填写',
        'label.max' => '作者id数据格式必须为数字',
        'name.max' => '最大长度为128个字符',

    ];

    protected $scene = [
        'insert' => ['label', 'title', 'details', 'price'],
        'update' => ['id', 'label', 'title', 'details', 'price'],
    ];
}
