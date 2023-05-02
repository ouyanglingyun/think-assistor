<?php

namespace lingyun\support;

use Faker\Generator as FakerGenerator;
use lingyun\view\FileViewFinder;
use think\App;
use think\Lang;
use think\migration\Factory;
use think\migration\MigratorProvider;

class ServiceProvider
{
    /**
     * The paths that should be published.
     *
     * @var array
     */
    public static $publishes = [];

    /**
     * The paths that should be published by group.
     *
     * @var array
     */
    public static $publishGroups = [];

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Register paths to be published by the publish command.
     *
     * @param  array  $paths
     * @param  mixed  $groups
     * @return void
     */
    public function publishes(array $paths, $groups = null)
    {
        $this->ensurePublishArrayInitialized($class = debug_backtrace()[1]['class']);

        static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);

        foreach ((array) $groups as $group) {
            $this->addPublishGroup($group, $paths);
        }
    }

    /**
     * Add a publish group / tag to the service provider.
     *
     * @param  string  $group
     * @param  array  $paths
     * @return void
     */
    protected function addPublishGroup($group, $paths)
    {
        if (!array_key_exists($group, static::$publishGroups)) {
            static::$publishGroups[$group] = [];
        }

        static::$publishGroups[$group] = array_merge(
            static::$publishGroups[$group],
            $paths
        );
    }
    /**
     * Get the paths to publish.
     *
     * @param  string|null  $provider
     * @param  string|null  $group
     * @return array
     */
    public static function pathsToPublish($provider = null, $group = null)
    {
        if (!is_null($paths = static::pathsForProviderOrGroup($provider, $group))) {
            return $paths;
        }

        return collect(static::$publishes)->reduce(function ($paths, $p) {
            return array_merge($paths, $p);
        }, []);
    }

    /**
     * Get the paths for the provider or group (or both).
     *
     * @param  string|null  $provider
     * @param  string|null  $group
     * @return array
     */
    protected static function pathsForProviderOrGroup($provider, $group)
    {
        if ($provider && $group) {
            return static::pathsForProviderAndGroup($provider, $group);
        } elseif ($group && array_key_exists($group, static::$publishGroups)) {
            return static::$publishGroups[$group];
        } elseif ($provider && array_key_exists($provider, static::$publishes)) {
            return static::$publishes[$provider];
        } elseif ($group || $provider) {
            return [];
        }
    }

    /**
     * Get the paths for the provider and group.
     *
     * @param  string  $provider
     * @param  string  $group
     * @return array
     */
    protected static function pathsForProviderAndGroup($provider, $group)
    {
        if (!empty(static::$publishes[$provider]) && !empty(static::$publishGroups[$group])) {
            return array_intersect_key(static::$publishes[$provider], static::$publishGroups[$group]);
        }

        return [];
    }

    /**
     * Get the service providers available for publishing.
     *
     * @return array
     */
    public static function publishableProviders()
    {
        return array_keys(static::$publishes);
    }
    /**
     * Get the groups available for publishing.
     *
     * @return array
     */
    public static function publishableGroups()
    {
        return array_keys(static::$publishGroups);
    }
    /**
     * Ensure the publish array for the service provider is initialized.
     *
     * @param  string  $class
     * @return void
     */
    protected function ensurePublishArrayInitialized($class)
    {
        if (!array_key_exists($class, static::$publishes)) {
            static::$publishes[$class] = [];
        }
    }

    /**
     * Setup an after resolving listener, or fire immediately if already resolved.
     *
     * @param   $name
     * @param  callable  $callback
     * @return void
     */
    protected function callAfterResolving($name, $callback)
    {
        if ($this->app->has($name)) {
            $callback($this->app->make($name), $this->app);
        }
        $this->app->resolving($name, $callback);
    }

    /**
     * Register a view file.
     *
     * @param  string|array  $path
     * @param  string  $namespace
     * @return void
     */
    public function loadViewsFrom($path, $namespace)
    {
        $this->callAfterResolving('view.finder', function (FileViewFinder $viewFinder) use ($path, $namespace) {
            $config = $this->app->config->get('view', []);
            $view = $config['view_dir_name'];
            $appViewPath = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;

            if (is_dir($appPath = $appViewPath . '/vendor/' . $namespace)) {
                $viewFinder->addNamespace($namespace, $appPath);
            }

            $appName = $this->app->http->getName();
            $rootViewPath    = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');

            if (is_dir($appPath = $rootViewPath . '/vendor/' . $namespace)) {
                $viewFinder->addNamespace($namespace, $appPath);
            }

            $viewFinder->addNamespace($namespace, $path);
        });
    }

    /**
     * Register database migration paths.
     *
     * @param  array|string  $paths
     * @return void
     */
    public function loadMigrationsFrom($paths)
    {
        $this->callAfterResolving('migration.migrator', function (MigratorProvider $migrator) use ($paths) {
            foreach ((array) $paths as $path) {
                $migrator->path($path);
            }
        });
    }

    /**
     * Register model factory paths.
     *
     *
     * @param  array|string  $paths
     * @return void
     */
    public function loadFactoriesFrom($paths)
    {
        $this->callAfterResolving(Factory::class, function (Factory $factory) use ($paths) {
            foreach ((array) $paths as $path) {
                $factory->load($path);
            }
        });
    }

    /**
     * load a translation file.
     *
     * @param  string  $path
     * @return void
     */
    public function loadTranslationsFrom($path)
    {
        $this->callAfterResolving('lang', function (Lang $lang) use ($path) {
            $files = glob($path . DIRECTORY_SEPARATOR .  '*.*');

            foreach ((array)$files as $file) {
                $lang->load($file, str_replace('.' . pathinfo($file, PATHINFO_EXTENSION), '', basename($file)));
            }
        });
    }

    public function addFakerProvider($provider)
    {
        $this->callAfterResolving(FakerGenerator::class, function (FakerGenerator $faker) use ($provider) {
            if (is_string($provider)) {
                $provider =  new $provider($faker);
            }
            $faker->addProvider($provider);
        });
    }
}
