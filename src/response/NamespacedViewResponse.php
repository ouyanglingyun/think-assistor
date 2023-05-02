<?php

namespace lingyun\response;

use think\Response\View;

class NamespacedViewResponse extends View
{
    public function view($data, array $vars = [])
    {
        if (!is_callable($data) || is_string($data)) {
            parent::data($data);
        }
        if (!empty($vars)) {
            parent::assign($vars);
        }
        return $this;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return string
     */
    protected function output($data): string
    {
        if (!is_callable($data) || is_string($data)) {
            $finder = app('view.finder');
            $data =  $finder->find($data);
        }
        return parent::output($data);
    }
}
