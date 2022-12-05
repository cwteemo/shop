<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 10/25/21
 * Time: 6:01 PM
 */

namespace app\middleware;


class Search
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     */
    public function handle($request, \Closure $next)
    {
        // $request->modelWhere = \app\facade\Resource::parseSelectParam($request->param());
    }
}