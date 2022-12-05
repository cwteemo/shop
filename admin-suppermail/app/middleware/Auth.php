<?php
declare (strict_types = 1);

namespace app\middleware;

use app\lib\Res;
use app\model\Project;
use app\model\UserProject;
use app\Request;
use thans\jwt\exception\JWTException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;
use thans\jwt\exception\TokenExpiredException;
use thans\jwt\middleware\JWTAuth as JWTMiddleware;
use think\helper\Str;
use think\Response;

/**
 * JWT 验证刷新token机制
 * Class Auth
 * @package app\middleware
 */
class Auth extends JWTMiddleware
{

    // 排除验证 token 的接口
    private $except = ['user/login', 'user/logout'];
    // app 平台无限制访问接口的白名单
    private $app_white_list = [ 'user/login', 'user/logout', 'user/info', 'wxapp/project' ];
    // 忽略请求方法校验
    private $ignore_method_list = ['user/node', 'wxapp/project'];

    /**
     * 处理请求
     *
     * @param \app\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // OPTIONS请求直接返回（这里不要依赖 AllowCrossDomain 中间件，因为中间件是可插拔的）
        if ($request->isOptions()) {
            return response();
        }

        if (!Str::contains($request->pathinfo(), $this->except)) {
            try {
                if (!$this->auth->getToken()) {
                    Res::reLogin('未登录');
                }
                $payload = $this->auth->auth();
            } catch (TokenExpiredException $e) { // 捕获token过期
                // 尝试刷新token，会将旧token加入黑名单
                try {
                    $this->auth->setRefresh();
                    $token = $this->auth->refresh();
                    // 新的token，则在响应头返回
                    Res::setAuthentication($token);

                    $payload = $this->auth->auth(false);
                } catch (TokenBlacklistGracePeriodException $e) {
                    $payload = $this->auth->auth(false);
                } catch (JWTException $exception) {
                    // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                    Res::reLogin('登录状态已过期');
                }
            } catch (TokenBlacklistGracePeriodException $e) { // 捕获黑名单宽限期
                $payload = $this->auth->auth(false);
            } catch (TokenBlacklistException $e) { // 捕获黑名单，退出登录或者已经自动刷新，当前token就会被拉黑
                Res::reLogin('未登录');
            }
            // 不带平台信息的直接无效
            if (empty($payload[KEY_PLATFORM])) {
                Res::reLogin('登录状态已过期');
            }

            $platform = $payload[KEY_PLATFORM]->getValue();
            $request->platform = $platform;
            $request->uid = $payload['id']->getValue();

            // 验证平台信息，APP 平台不允许 增、删、改
            $this->checkPlatform($request);
        }

        return $next($request);
    }

    // 验证平台
    protected function checkPlatform(Request $request): void
    {
        switch ($request->platform) {
            case PLATFORM_ADMIN:
                break;
            case PLATFORM_WX_APP:
            case PLATFORM_APP:
                // APP 平台接口白名单并且header中不带project（GET 接口不用拦截）
                $pathinfo = $request->pathinfo();
                if (in_array($pathinfo, $this->app_white_list) && !$request->header('project')) {
                    return;
                }

                // 验证项目访问权限
                $this->checkProject($request);

                // 不用校验请求方法的清单
                if (in_array($pathinfo, $this->ignore_method_list)) {
                    return;
                }
                // APP 平台禁用非GET请求，或者在白名单的请求
                if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
                    Res::returnErr('你没有权限访问', 2);
                }

                break;
            default:
                Res::returnErr('你没有权限访问');

        }
    }

    // 验证项目访问权限
    protected function checkProject(Request $request): void
    {
        $uid = $request->uid;
        $project_id = $request->header('project');
        if (!$project_id) {
            // header 中不附带 project 不合规
            Res::returnErr('请求不合规');
        }
        $request->project = $project_id;

        // 用户可访问项目的缓存
        $up_cache_key = "user:$uid:project:$project_id";

        if (cache($up_cache_key)) {
            return;
        }

        // 需要用户是否有指定项目的访问权限
        $project = UserProject::find([
            'uid' => $uid,
            'project_id' => $project_id
        ]);

        // 暂时不做作者逻辑验证（作者也统一走激活逻辑）
        // if (!$project || $project->isEmpty()) {
        //     // 验证是否为作者
        //     $project = Project::find(['uid' => $uid, 'id' => $project_id]);
        // }

        if (!$project || $project->isEmpty()) {
            Res::returnErr('无访问权限');
        }
        cache($up_cache_key, time());
    }

    protected function checkRole ()
    {

    }
}
