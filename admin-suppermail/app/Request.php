<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
// 用户id
    public $uid = null;
    // 平台信息
    public $platform = '';

    // 项目id
    public $project  = '';
}
