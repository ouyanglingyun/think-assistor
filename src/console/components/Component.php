<?php

namespace lingyun\console\components;

use function Termwind\render;

abstract class Component
{
    /**
     * The output style implementation.
     *
     * @var \think\console\Output
     */
    protected $output;


    /**
     * The list of mutators to apply on the view data.
     *
     * @var array<int, callable(string): string>
     */
    protected $mutators;

    /**
     * Creates a new component instance.
     *
     * @param  \think\console\Output  $output
     * @return void
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Renders the given view.
     *
     * @param  string  $view
     * @param  \think\contract\Arrayable|array  $data
     * @param  int  $verbosity
     * @return void
     */
    protected function renderView($view, $data, $verbosity)
    {
        render((string) $this->compile($view, $data), $verbosity);
    }

    /**
     * Compile the given view contents.
     *
     * @param  string  $view
     * @param  array  $data
     * @return void
     */
    protected function compile($view, $data)
    {
        extract($data);

        ob_start();

        include __DIR__ . "/views/$view.php";

        return tap(ob_get_contents(), function () {
            ob_end_clean();
        });
    }

    /**
     * Mutates the given data with the given set of mutators.
     *
     * @param  array<int, string>|string  $data
     * @param  array<int, callable(string): string>  $mutators
     * @return array<int, string>|string
     */
    protected function mutate($data, $mutators)
    {
        foreach ($mutators as $mutator) {
            $mutator = new $mutator;

            if (is_iterable($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = $mutator($value);
                }
            } else {
                $data = $mutator($data);
            }
        }

        return $data;
    }
}
