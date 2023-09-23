<?php

namespace think\assistor\console\components;

use InvalidArgumentException;
use think\assistor\console\components\Line;

/**
 * @method void alert(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method mixed ask(string $question, string $default = null)
 * @method void bulletList(array $elements, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method mixed choice(string $question, array $choices, $default = null, int $attempts = null, bool $multiple = false)
 * @method bool confirm(string $question, bool $default = false)
 * @method void error(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void info(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void line(string $style, string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void task(string $description, ?callable $task = null, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void twoColumnDetail(string $first, ?string $second = null, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void warning(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void highlight(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void question(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 * @method void comment(string $string, int $verbosity = \think\console\Output::VERBOSITY_NORMAL)
 */
class Factory
{
    /**
     * The output interface implementation.
     *
     * @var \think\console\Output
     */
    protected $output;

    /**
     * Creates a new factory instance.
     *
     * @param  \think\console\Output  $output
     * @return void
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Dynamically handle calls into the component instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __call($method, $parameters)
    {
        $component = '\think\assistor\console\components\\' . ucfirst($method);

        if (in_array($method, Line::STYLES)) {
            array_unshift($parameters, $method);
            return  tap(new Line($this->output))->render(...$parameters);
        }

        throw_unless(class_exists($component), new InvalidArgumentException(sprintf(
            'Console component [%s] not found.',
            $method
        )));

        tap(new $component($this->output))->render(...$parameters);
    }
}
