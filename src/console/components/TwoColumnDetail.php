<?php

namespace think\assistor\console\components;

use think\console\Output as OutputInterface;

class TwoColumnDetail extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $first
     * @param  string|null  $second
     * @param  int  $verbosity
     * @return void
     */
    public function render($first, $second = null, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $first = $this->mutate($first, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsureNoPunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $second = $this->mutate($second, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsureNoPunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);


        $firstWidth = mb_strlen(preg_replace("/\<[\w=#\/\;,:.&,%?]+\>|\\e\[\d+m/", '$1', $first) ?? '');

        $this->output->write("  $first ", false, $verbosity);

        $dimensions = $this->output->getTerminalDimensions();

        $width = min($dimensions[0], 180);

        $secondWidth = mb_strlen(preg_replace("/\<[\w=#\/\;,:.&,%?]+\>|\\e\[\d+m/", '$1', $second) ?? '');

        $dots = max($width - $firstWidth - $secondWidth - 6, 0);

        $this->output->write(str_repeat('<fg=cyan>.</>', $dots), false, $verbosity);
        $this->output->writeln(" $second", false, $verbosity);
    }
}
