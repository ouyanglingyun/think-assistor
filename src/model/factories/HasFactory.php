<?php

namespace lingyun\model\factories;

use think\migration\Factory;

trait HasFactory
{
    public static function factory(int | array $count = null, string $name = 'default', array $state = [])
    {
        /**
         * @var Factory $factory
         */
        $factory = app(Factory::class);

        $factories = static::newFactory() ?: get_called_class();
        return $factory->of($factories, $name)->times(is_numeric($count) ? $count : null)
            ->states(is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for  model.
     *
     */
    protected static function newFactory()
    {
    }
}
