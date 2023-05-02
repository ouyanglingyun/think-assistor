<?php

namespace lingyun\migration\faker;

use Faker\Provider\Base;

use lingyun\support\Gravatar as GravatarBuild;

class Gravatar extends Base
{
    public static function gravatarUrl(string $email = null, int $size = 80, string $mode = null, string $url = null): string
    {
        return $url ? GravatarBuild::url($url)->gravatarUrl($email, $size, $mode) : GravatarBuild::gravatarUrl($email, $size, $mode);
    }

    public static function gravatar(string $dir = null, string $email = null, int $size = 80, string $mode = null, bool $fullPath = false, string $url = null): ?string
    {
        return $url ? GravatarBuild::url($url)->gravatar($dir, $email, $size, $mode, $fullPath) : GravatarBuild::gravatar($dir, $email, $size, $mode, $fullPath);
    }
}
