<?php

namespace think\assistor\console\components;

class Line extends Component
{
    const STYLES = [
        'info',
        'error',
        'comment',
        'question',
        'highlight',
        'warning',
    ];

    protected  $stylesConfig = [
        'info' => 'fg=white;bg=blue;options=bold',
        'error' => 'fg=white;bg=red;options=bold',
        'comment' => 'fg=white;bg=cyan;options=bold',
        'question' => 'fg=white;bg=magenta;options=bold',
        'highlight' => 'fg=white;bg=green;options=bold',
        'warning' => 'fg=white;bg=yellow;options=bold',
    ];

    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $style
     * @param  string  $string
     * @param  int  $type
     * @return void
     */
    public function render($style, $string, $type = 0)
    {
        $string = $this->mutate($string, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsurePunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $tag = strtoupper($style);
        if (in_array($style, self::STYLES)) {
            $stylesConfig = $this->stylesConfig[$style];
            $this->output->write("<{$stylesConfig}> {$tag} </> ",);
        } else {
            $this->output->write(
                "<{$style}> {$tag} </{$style}> ",
            );
        }
        $this->output->writeln($string, $type);
    }
}
