<?php

use think\Model;
use think\Response;
use think\facade\Session;
use think\helper\Arr;
use think\response\View;

if (!function_exists('old')) {
    /**
     * Retrieve an old input item.
     *
     * @param  string|null  $key
     * @param  \think\Model|string|array|null  $default
     * @return mixed
     */
    function old($key = null, $default = null)
    {
        $default = $default instanceof Model ?
            $default->getAttr($key) : $default;
        return getOldInput($key, $default);
    }
}

if (!function_exists('gravatar')) {

    function gravatar(string $email = null, int $size = 100, string $mode = null)
    {
        return \think\assistor\support\Gravatar::gravatarUrl($email, $size, $mode);
    }
}

if (!function_exists('getOldInput')) {
    /**
     * Get the requested item from the flashed input array.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function getOldInput($key = null, $default = null)
    {
        return Arr::get(Session::get('_old_input', []), $key, $default);
    }
}

if (!function_exists('flashInput')) {
    /**
     * Flash an input array to the session.
     *
     * @param  array  $value
     * @return void
     */
    function flashInput(array $value)
    {
        Session::flash('_old_input', $value);
    }
}
