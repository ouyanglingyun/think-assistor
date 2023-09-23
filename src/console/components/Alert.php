<?php

namespace think\assistor\console\components;

use think\console\Output;

class Alert extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $string
     * @param  int  $verbosity
     * @return void
     */
    public function render($string, $verbosity = Output::VERBOSITY_QUIET)
    {
        $string = $this->mutate($string, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsurePunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $dimensions = $this->output->getTerminalDimensions();

        $width = $dimensions[0];

        $dots = max($width - 6, 0);

        $this->output->writeln(
            "   " . str_repeat('<bg=yellow;fg=black;options=bold;> </>', $dots) . "   ",
            $verbosity
        );

        $stringWidth = mb_strlen(preg_replace("/\<[\w=#\/\;,:.&,%?]+\>|\\e\[\d+m/", '$1', $string) ?? '');

        $spaces = max(($width - $stringWidth - 10) / 2, 0);

        $this->output->write(
            "   " . str_repeat('<bg=yellow;fg=black;options=bold;> </>', $spaces),
            $verbosity
        );
        $this->output->write(
            "<bg=yellow;fg=black;options=bold;>  {$string}  </>",
            $verbosity
        );


        $this->output->writeln(
            str_repeat('<bg=yellow;fg=black;options=bold;> </>', $spaces) . "   ",
            $verbosity
        );

        $this->output->writeln(
            "   " . str_repeat('<bg=yellow;fg=black;options=bold;> </>', $dots) . "   ",
            $verbosity
        );
    }
}
