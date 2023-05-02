<?php

namespace lingyun\console\components;

class Line extends Component
{
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

        switch ($style) {
            case 'info':
                $this->output->write(
                    "<bg=blue;> {$tag} </> ",
                );
                break;
            case 'comment':
                $this->output->write(
                    "<bg=magenta> {$tag} </> ",
                );
                break;
            case 'highlight':
                $this->output->write(
                    "<bg=green> {$tag} </> ",
                );
                break;
            default:
                $this->output->write(
                    "<{$style}> {$tag} </{$style}> ",
                );
                break;
        }
        $this->output->writeln($string);
    }
}
