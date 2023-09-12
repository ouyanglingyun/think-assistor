<?php

namespace think\assistor\traits;

use think\Response;
use think\response\View;

trait NamespacedView
{
    /**
     * 渲染模板输出
     * @param string   $template 模板文件
     * @param array    $vars     模板变量
     * @param int      $code     状态码
     * @param callable $filter   内容过滤
     * @return \think\response\View
     */
    public function view(string $template = '', array $vars = [], int $code = 200, callable $filter = null): View
    {
        if (app()->has('view.finder')) {
            $finder = app('view.finder');
            $template = $finder->find($template);
        }
        /** @var View $view */
        $view = Response::create($template, 'view', $code);
        return $view->assign($vars)->filter($filter);
    }
}
