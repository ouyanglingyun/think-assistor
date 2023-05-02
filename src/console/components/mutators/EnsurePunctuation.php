<?php

namespace lingyun\console\components\mutators;

use think\helper\Str;

class EnsurePunctuation
{
    /**
     * Ensures the given string ends with punctuation.
     *
     * @param  string  $string
     * @return string
     */
    public function __invoke($string)
    {
        if (!Str::endsWith($string, ['.', '?', '!', ':'])) {
            return "$string.";
        }

        return $string;
    }
}
