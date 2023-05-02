<?php

namespace lingyun\console\components;

use InvalidArgumentException;

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
        $component = '\lingyun\console\components\\' . ucfirst($method);

        throw_unless(class_exists($component), new InvalidArgumentException(sprintf(
            'Console component [%s] not found.',
            $method
        )));

        return tap(new $component($this->output))->render(...$parameters);
    }
}
