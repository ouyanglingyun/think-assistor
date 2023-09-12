# think-assistor

thinkphp6 扩展包开发的一些辅助工具。

## 安装

通过 Composer 包管理器安装 **think-assistor** :

```
composer require lingyun/think-assistor
```
## 服务辅助器

在服务类中获取服务辅助器实例

```
$assistor = $this->app->get('service.assistor');
```

通常扩展包的配置文件发布到应用程序的`config`目录,只需在`composer.json`中加入`extra.think.config`配置,当你的扩展包完成安装或者手动运行`assets:publish`命令时，你的文件将被复制到指定的发布位置。如：

```
"extra": {
    "think": {
        "config": {
            "passport": "src/config/passport.php"
        }
    }
}
```

服务辅助器提供了更多的扩展包资源发布方式

## 资源

### 配置

通常，您需要将包的配置文件发布到应用程序的 `config` 目录。 这将允许您的包的用户轻松覆盖您的默认配置选项。 要允许发布配置文件，请从服务提供者的 `boot` 方法中调用服务辅助器的 `publishes` 方法：

```
public function boot()
{
    $assistor = $this->app->get('service.assistor');
    $assistor->publishes([
        __DIR__.'/../config/courier.php' => config_path('courier.php'),
    ]);
}
```

现在，执行 `assets:publish` 命令时，你的文件将被复制到指定的发布位置。

### 数据库迁移

如果你的包包含数据库迁移，你可以使用服务辅助器的 `loadMigrationsFrom` 方法告诉 `think-migration` 如何加载它们。 `loadMigrationsFrom` 方法接受包迁移的路径作为其唯一参数：

```
public function boot()
{
    $assistor = $this->app->get('service.assistor');
    $assistor->loadMigrationsFrom(__DIR__.'/../database/migrations');
}
```

一旦您的包的迁移被注册，它们将在执行 `php think migrate:run` 命令时自动运行。 您不需要将它们导出到应用程序的 `database/migrations` 目录。
### 模型工厂

如果你的包包含模型工厂，你可以使用服务辅助器的 `loadFactoriesFrom` 方法告诉 `think-migration` 加载它们。 `loadFactoriesFrom` 方法接受包迁移的路径作为其唯一参数：

```
public function boot()
{
    $assistor = $this->app->get('service.assistor');
    $assistor->loadFactoriesFrom(__DIR__ . '/../database/factories/');
}
```
可以通过 `think\assistor\model\factories\HasFactory` 特征使用提供给你的模型的静态 `factory` 方法实例化该模型的工厂实例。

```
<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\assistor\model\factories\HasFactory;

class User extends Model
{
    use HasFactory;
}
```
此时模型实例化模型工厂可以使用：
```
use app\model\User;
use think\migration\Factory;
$user = app(Factory::class)->of(User::class)->make(); 
```
或者使用 `HasFactory` 特征使用提供静态 `factory` 方法
```
use app\model\User;
$user = User::factory()->make();
```
### 加载语言包

如果你的扩展包中包含 语言包文件 ，你需要使用 `loadTranslationsFrom` 方法告知 thinkphp 加载它们：

```
public function boot()
{
    $assistor = $this->app->get('service.assistor');
    $assistor->loadTranslationsFrom(__DIR__.'/../resources/lang');
}
```
### 发布语言包

如果你想要将扩展包中的语言包发布到应用的 `resources/lang/vendor` 目录中， 可以使用服务提供者的 `publishes` 方法。 `publishes` 方法接收一个包含语言包路径和对应发布位置的数组。例如，发布 courier 扩展包的语言包文件，然后使用 `loadTranslationsFrom` 方法告知 thinkphp 加载它们,方便之后对语言包的修改。
操作如下：

```
public function boot()
{
    $assistor = $this->app->get('service.assistor');
    $assistor->publishes([
        __DIR__.'/../resources/lang' => root_path('resources/lang/vendor/courier'),
    ]);
    $assistor->loadTranslationsFrom(root_path('resources/lang/vendor/courier'));
}
```

当扩展包的用户执行 `php think assets:publish` 命令，语言包将会被发布到指定的目录中。

### 视图

想要在 ThinkPHP 中注册你的扩展包的视图 ， 需要告知 ThinkPHP 视图文件的位置。 你可以使用服务辅助器的 `loadViewsFrom` 方法来实现。 `loadViewsFrom` 方法接收两个参数：视图模板的路径和扩展包名。例如，如果你的扩展包名为 `courier` ，你需要将下面的内容加入到服务提供者的 `boot` 方法中：

```
public function boot()
{
    $assistor = $this->app->get('service.assistor');

    $assistor->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
}
```

扩展包视图约定使用 package::view 语法进行引用。因此，一旦视图路径在服务提供者中注册成功，你可以通过下面的方式来加载 courier 扩展包中的 dashboard 视图：

```
use think\assistor\support\FileViewFinder as View;

Route::get('/dashboard', function () {
    return View::fetch('courier::dashboard', ['name' => 'thinkphp']);
});
```

或者使用

```
Route::get('/dashboard', function () {
     return app('view.finder')->fetch('courier::dashboard', ['name' => 'thinkphp']);
});
```

但是 `think\facade\View::fetch('courier::authorize')`方法不支持命名视图文件的输出,`think\facade\View::fetch`和`view`助手函数需要指定模板的完整路径,但是可以使用 `think\assistor\support\FileViewFinder::fetch('courier::authorize')`进行命名视图文件的输出,同时`FileViewFinder`支持`think\facade\View`的所有用法

### 公共资源文件

扩展包可能包含 JavaScript 、CSS 和图片之类的资源文件,可以使用`publishes`方法发布到指定位置，这样就可以在视图中使用指定位置的静态资源：

```
public function boot()
{
    if($this->app->has('service.assistor')){
        $assistor = $this->app->get('service.assistor');

        $assistor->loadViewsFrom(__DIR__.'/../resources/views', 'courier');

        $assistor->publishes([
            __DIR__.'/../resources/static' => public_path('vendor/courier'),
            ], 'courier-static');
    }
}
```

当执行 `php think assets:publish` 命令，扩展包资源文件将会被发布到指定的目录中。 由于每次更新扩展包时通常都需要覆盖资源文件，因此需要使用 `--force` 标签：

```
php think assets:publish --tag=courier-static --force
```

