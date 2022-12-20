<?php

namespace app\lib;

use thans\jwt\facade\JWTAuth;
use think\Collection;
use think\Exception;
use think\Response;
use think\response\Json;

/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 2018/8/21
 * Time: 下午8:57
 */
class Res
{
    public $msg;

    /**
     * 10001 为登录失效
     * @var int
     */
    public $errorCode;

    public $data;

    public function __construct($data = [], $msg = '', $errorCode = 0)
    {
        $this->msg = $msg;

        $this->errorCode = $errorCode;

        $this->data = $data;

    }

    /**
     * 转字符串（所有的结果返回都会调用）
     *
     * @return string
     */
    function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * 转数组
     *
     * @return array
     */
    function toArray()
    {

        return [
            'msg' => $this->msg,
            'errorCode' => $this->errorCode,
            'data' => $this->data
        ];
    }

    /**
     * 设置 response header
     *
     * @param $token
     * @param $bearer
     */
    public static function setAuthentication($token, $bearer='Bearer')
    {
        header("Authorization:${bearer} $token");
    }

    /**
     * 告诉前端 token 失效
     *
     * @param $set_auth
     * @return array
     */
    public static function invalidateToken($set_auth=true)
    {
        try {
            // 刷新使之前的 token 失效
            $token = JWTAuth::token();
            if (JWTAuth::validate($token)) {
                JWTAuth::invalidate($token);
            }
        }
        finally {
            if ($set_auth) {
                static::setAuthentication("invalidate", "");
            }
        }
        return [];
    }

    /**
     * 返回错误
     *
     * @param string $msg
     * @param int $errorCode
     * @param $data
     * @return Res
     */
    public static function error($msg="", $errorCode=1, $data=[])
    {
        return new Res($data, $msg, $errorCode);
    }

    /**
     * 重新登录
     *
     * @param $msg
     */
    public static function reLogin($msg="登录状态已失效")
    {
        static::returnErr($msg,10001);
    }

    /**
     * 终止程序，返回错误
     *
     * @param int $errorCode
     * @param string $msg
     * @param $data
     */
    public static function returnErr($msg="", $errorCode=1, $data=[])
    {
        // abort(Json::create(static::error($msg, $errorCode), 'json'));
        abort(Response::create(static::error($msg, $errorCode, $data), 'Json'));
    }

}