<?php

namespace think\assistor\console\components;

use \think\console\Output;

class BulletList extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  array<int, string>  $elements
     * @param  int  $verbosity
     * @return void
     */
    public function render($elements, $verbosity = Output::VERBOSITY_QUIET)
    {
        $elements = $this->mutate($elements, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsureNoPunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        foreach ($elements as $element) {
            $this->output->writeln("  <fg=magenta;options=bold>⇂ $element</>", false, $verbosity);
        }
    }
}
