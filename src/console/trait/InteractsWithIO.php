<?php

namespace think\assistor\console\trait;

use think\assistor\console\components\Factory;
use think\console\Input;
use think\console\Output;
use think\facade\Console;
use think\helper\Str;

trait InteractsWithIO
{
    /**
     * The console components factory.
     *
     * @var \think\assistor\console\components\Factory
     *
     * @internal This property is not meant to be used or overwritten outside the framework.
     */
    protected $components;

    /**
     * The input interface implementation.
     *
     * @var \think\console\Input
     */
    protected $input;

    /**
     * The output interface implementation.
     *
     * @var \think\console\Output
     */
    protected $output;


    protected function option(string $name)
    {
        return $this->input->getOption($name);
    }

    protected function info($message)
    {
        return $this->output->info($message);
    }
    protected function error($message)
    {
        return $this->output->error($message);
    }
    protected function warn($message)
    {
        return $this->output->warning($message);
    }
    protected function question($message)
    {
        return $this->output->question($message);
    }
    protected function highlight($message)
    {
        return $this->output->highlight($message);
    }

    protected function comment($message)
    {
        return $this->output->comment($message);
    }
    protected function confirm($message, $default = true)
    {
        return $this->output->confirm($this->input, $message, $default);
    }

    protected function newLine()
    {
        return $this->output->newLine();
    }

    protected function ask($question, $default = null, $validator = null)
    {
        return $this->output->ask($this->input, $question, $default, $validator);
    }

    protected function choice($question, array $choices = null, $default = null)
    {
        return $this->output->choice($this->input, $question, $choices, $default);
    }

    protected function call(string $command, array $parameters = [], string $driver = 'Console')
    {
        if (strtolower($driver) == 'buffer') {
            $this->output->write(Console::call($command, $parameters, $driver)->fetch());
        } else {
            Console::call($command, $parameters, $driver);
        }
        $this->newLine();
    }

    /**
     * Write a string in an alert box.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function alert($string, $verbosity = null)
    {
        $length = Str::length(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length), $verbosity);
        $this->comment('*     ' . $string . '     *', $verbosity);
        $this->comment(str_repeat('*', $length), $verbosity);

        $this->comment('', $verbosity);
    }

    /**
     * 初始化
     * @param Input  $input  An InputInterface instance
     * @param Output $output An OutputInterface instance
     */
    protected function initialize(Input $input, Output $output)
    {
        $this->components = $this->app->make(Factory::class, ['output' => $output]);
    }
}
