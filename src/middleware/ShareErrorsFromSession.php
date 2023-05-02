<?php

namespace lingyun\middleware;

use Closure;
use think\View;

class ShareErrorsFromSession
{
    /**
     * The view factory implementation.
     *
     * @var \think\View
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param  \think\View  $view
     * @return void
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \think\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->view->assign(
            'errors',
            $request->session('errors') ?: []
        );

        return $next($request);
    }
}
