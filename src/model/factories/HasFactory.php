<?php

namespace think\assistor\model\factories;

use think\migration\Factory;
use think\migration\FactoryBuilder;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     * 
     * @param int|array|null $count
     * @param string $name
     * @param array|string $state
     * 
     * @return FactoryBuilder
     * 
     */
    public static function factory(int | array |string $count = null, string $name = 'default', array | string $state = []): FactoryBuilder
    {
        $factories = static::newFactory() ?: get_called_class();

        /**
         * @var Factory $factory
         */
        $factory = app(Factory::class);
        return $factory->of($factories, is_string($count) ? $count : $name)->times(is_numeric($count) ? $count : null)
            ->states(is_array($count) ? $count : (array) $state);
    }

    /**
     * Create a new factory instance for  model.
     *
     */
    protected static function newFactory()
    {
    }
}
