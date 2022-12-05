<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 9/23/21
 * Time: 10:41 PM
 */

namespace app\middleware;

use app\lib\Res;
use think\Request;
use think\Response;
use think\response\File;

class App
{
    public function handle(Request $request, \Closure $next)
    {
        // 支持跨域
        header('Access-Control-Allow-Origin: *');

        $response = $next($request);

        return $this->formatResponse($response);
    }

    /**
     * 格式化输出
     *
     * @param Response $response
     */
    public function formatResponse(Response $response)
    {
        $res = $response->getData();
        if (!($res instanceof Res)) {
            if (is_string($res)) {
                return $response;
            }
            // 不是 Res 的实例就转换为 Res 实例
            $res = new Res($res ?? []);
        }

        // 回调行为
        if ($response->getCode() != 200) {
            $response->code(200);
            if (!empty($res->data['message'])) {
                $res->msg      = $res->data['message'];
            }
            $res->errorCode    = 1;
        }

        $response->data($res);
        // $response->contentType('application/json');
        return $response;
    }
}