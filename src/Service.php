<?php

namespace think\assistor;

use think\App;
use think\assistor\console\command\AssetsPublishCommand;
use think\assistor\support\AuthManager;
use think\assistor\support\FileViewFinder;
use think\assistor\support\ServiceAssistor;
use yunwuxin\Auth;

class Service extends \think\Service
{
    public function register()
    {
        $this->bindAuthManager();
        $this->registerServiceAssistor();
        $this->registerViewFinder();
        $this->setExceptionTemplate();
    }

    public function bindAuthManager()
    {
        $this->app->bind(Auth::class, AuthManager::class);
    }

    public function registerServiceAssistor()
    {
        $this->app->bind('service.assistor', ServiceAssistor::class);
    }
    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function (App $app) {
            return new FileViewFinder((array) $app->config->get('view.view_path'), (array) $app->config->get('view.view_suffix'));
        });
    }

    public function setExceptionTemplate()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'Exceptions' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        if (!$this->app->isDebug()) {
            $this->app->config->set([
                'exception_tmpl' => $path . 'think_exception.tpl',
                'http_exception_template' => [
                    '401' => $path . '401.html',
                    '402' => $path . '402.html',
                    '403' => $path . '403.html',
                    '404' => $path . '404.html',
                    '419' => $path . '419.html',
                    '429' => $path . '429.html',
                    '500' => $path . '500.html',
                    '503' => $path . '503.html',
                ]
            ], 'app');
        }
    }

    public function boot()
    {
        $this->commands([AssetsPublishCommand::class]);
    }
}
