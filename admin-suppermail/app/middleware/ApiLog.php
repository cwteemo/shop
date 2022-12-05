<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 9/24/21
 * Time: 6:29 PM
 */

namespace app\middleware;

use app\lib\Res;
use think\facade\Db;
use think\facade\Log;
use think\Request;
use think\Response;

/**
 * 接口日志
 *
 * Class ApiLog
 * @package app\middleware
 */
class ApiLog
{

    public function handle(Request $request, \Closure $next)
    {
        $request_time = getTime();
        $sql_arr = [];

        Db::listen(function($sql, $runtime, $master) use (&$sql_arr) {
            // 进行监听处理
            array_push($sql_arr, [
                'sql'       => $sql,
                'runtime'   => $runtime,
                'master'    => $master
            ]);
        });

        G('begin');
        $response = $next($request);
        G('end');

        $resData = $response->getData();
        if (!($resData instanceof Res)) {
            $resData = "file";
        }
        Log::record([
            'request'       => $this->getRequestInfo($request, $request_time), // 请求
            'sql'           => $sql_arr,  // sql 内容
            'response'      => $resData, // 响应结果
            'performance'   => $this->getPerformanceInfo(), // 性能指标
        ], 'api');

        return $response;
    }

    /**
     * 性能记录
     *
     * @return array
     */
    private function getPerformanceInfo()
    {

        $runtime      = G('begin','end') + 0;
        $reqs         = $runtime > 0 ? number_format(1 / $runtime, 2) + 0 : '∞';
        $memory_use   = G('begin', 'end', 'm');
        if (is_numeric($memory_use)) {
            $memory_use += 0;
        }

        return [
            'runtime' => $runtime,      // 单位 's'
            'reqs'    => $reqs,         // 单位 'req/s'
            'memory'  => $memory_use,   // 单位 'kb'
            'file'    => count(get_included_files()),
        ];
    }

    /**
     * 请求信息记录
     *
     * @param Request $request
     * @return array
     */
    private function getRequestInfo(Request $request, $time)
    {
        return [
            'time'      => $time,
            'ip'        => $request->ip(),
            'url'       => $request->pathinfo(),
            // todo： 解决白名单接口没有记录 uid 的问题
            'uid'       => empty($request->uid) ? null : $request->uid,
            'method'    => $request->method(),
            'param'     => $request->except(['s']), // s 为 Request $request->varPathinfo 的值
            'header'    => $request->header(),
        ];
    }
}