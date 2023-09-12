<?php

namespace think\assistor\faker\provider;

use Faker\Provider\Base;

use think\assistor\support\Gravatar as GravatarBuild;

class Gravatar extends Base
{
    public static function gravatarUrl(string $email = null, int $size = 80, string $mode = null, string $url = null): string
    {
        return $url ? GravatarBuild::url($url)->gravatarUrl($email, $size, $mode) : GravatarBuild::gravatarUrl($email, $size, $mode);
    }

    public static function gravatar(string $email = null, int $size = 80, string $mode = null, string $dir = null, bool $fullPath = false, string $url = null): ?string
    {
        return $url ? GravatarBuild::url($url)->gravatar($email, $size, $mode, $dir, $fullPath) : GravatarBuild::gravatar($email, $size, $mode, $dir,  $fullPath);
    }
}
