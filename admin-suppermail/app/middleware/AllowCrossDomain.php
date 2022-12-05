<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 9/24/21
 * Time: 11:28 PM
 */

namespace app\middleware;


use think\Request;
use think\Response;

class AllowCrossDomain extends \think\middleware\AllowCrossDomain
{
    protected $header = [
        'Access-Control-Allow-Origin'       => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, token, version, project, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
        'Access-Control-Expose-Headers'    => 'Authorization', // 支持前端获取的header内容
    ];

    /**
     * 允许跨域请求
     * @access public
     * @param Request $request
     * @param \Closure $next
     * @param array   $header
     * @return Response
     */
    public function handle($request, \Closure $next, ? array $header = [])
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;

        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request->header('origin');

            if ($origin && ('' == $this->cookieDomain || strpos($origin, $this->cookieDomain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }

        if ($request->isOptions()) {
            return Response::create('', 'html', 200)->header($header);
        }

        return $next($request)->header($header);
    }
}