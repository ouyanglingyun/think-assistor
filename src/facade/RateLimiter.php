<?php

declare(strict_types=1);

namespace lingyun\facade;

use think\Facade;

class RateLimiter extends Facade
{

    protected static function getFacadeClass()
    {
        return \lingyun\cache\RateLimiter::class;
    }
}
