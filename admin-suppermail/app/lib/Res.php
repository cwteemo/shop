<?php

namespace app\lib;

use think\Exception;
//use think\Log;

/**
 * Class Res
 * @package app\index\lib
 * 返回统一结构类
 */
class Res extends Exception
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

    public static function reLogin()
    {
        return new Res(null, '', 10001);
    }

    public function __destruct()
    {
        //$date = date("Y-m-d H:i:s", time());
        //Log::record('[request_time][' . $date . '][ RETURN_JSON ] ' . json_encode([$this->data, $this->msg, $this->errorCode]), 'info');

    }

}