<?php

namespace lingyun;

use lingyun\console\command\AssetsPublishCommand;
use lingyun\migration\faker\Gravatar;
use lingyun\support\ServiceProvider;
use lingyun\view\FileViewFinder;
use think\App;

class AccessibilityService extends \think\Service

{
    public function register()
    {
        $this->registerServiceAssistor();
        $this->registerViewFinder();
        $this->setExceptionTemplate();
    }
    public function boot()
    {
        $this->commands([
            AssetsPublishCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->app->get('service.assistor')->addFakerProvider(Gravatar::class);
        }
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

    public function registerServiceAssistor()
    {
        $this->app->bind('service.assistor', ServiceProvider::class);
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
}
