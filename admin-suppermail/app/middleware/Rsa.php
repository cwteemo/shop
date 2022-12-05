<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 9/24/21
 * Time: 5:04 PM
 */

namespace app\middleware;


use think\helper\Str;
use think\Request;

/**
 * 统一处理 Rsa 加密的数据
 *
 * Class Rsa
 * @package app\middleware
 */
class Rsa
{
    public function handle(Request $request, \Closure $next)
    {
        if ($request->has('_')) {
            // 加密数据
            $_ = $request->param('_');

            // 解密数据，并注入到 request 中
            $data = \app\facade\Rsa::decode($_);

            // 注入到请求参数中（对应请求类型的参数中）
            $method = 'with'.Str::title($request->method());
            if (method_exists($request, $method)) {
                // $request->$method(array_merge($data, $request->$method()));
                $request->$method($data);
            }
        }

        return $next($request);
    }

}