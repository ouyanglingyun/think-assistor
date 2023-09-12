<?php

namespace think\assistor\console\components;

class Error extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $string
     * @param  int  $verbosity
     * @return void
     */
    public function render($string, $verbosity = 0)
    {
        tap(new Line($this->output))->render('error', $string, $verbosity);
    }
}
