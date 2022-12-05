<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 12/21/21
 * Time: 10:32 PM
 */

namespace app\middleware;


use app\Request;

class AutoCache
{
    private $controller = ['Stage', 'Node', 'Link', 'Material'];

    public function handle(Request $request, \Closure $next)
    {

        // if (in_array($request->controller(), $this->controller)) {
        //     // 缓存白名单
        //     print_r($request);
        //     // print_r($request->controller());
        // }

        $response = $next($request);

        return $response;
    }

    private function cacheKey($key)
    {
        return \request()->project . ';key:'. '$key';
    }


}