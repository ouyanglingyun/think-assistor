<?php

namespace think\assistor\support;

use think\helper\Str;

class Gravatar
{
    private static $MODES = [
        'identicon', 'monsterid', 'mp', 'retro', 'robohash', 'wavatar',
    ];

    private static $url = 'http://sdn.geekzu.org/avatar/';

    public function __construct(string $url)
    {
        static::$url = $url;
    }

    public static function url(string $url)
    {
        return new static($url);
    }

    public static function gravatarUrl(string $email = null, int $size = 80, string $mode = null): string
    {
        if (!$mode || !in_array($mode, static::$MODES, true)) {
            $mode = static::$MODES[array_rand(static::$MODES)];
        }
        $hash = $email ? md5(Str::lower(trim($email))) : Str::random(12);

        return rtrim(static::$url, '/') . "/{$hash}?default={$mode}&size={$size}";
    }

    public static function gravatar(string $email = null, int $size = 80, string $mode = null, string $dir = null, bool $fullPath = true): ?string
    {
        if ($dir && !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $url = static::gravatarUrl($email, $size, $mode);

        if (!is_dir($dir) || !is_writable($dir)) {
            return $url;
        }

        $name     = md5(uniqid($_SERVER['SERVER_ADDR'] ?? '', true));
        $fileName = $name . '.jpg';
        $filePath = $dir . $fileName;

        // save file
        try {
            // use cURL
            $fp = fopen($filePath, 'w');
            $client = new \GuzzleHttp\Client();

            $response =  $client->get($url, [\GuzzleHttp\RequestOptions::SINK => $fp]);

            fclose($fp);

            if ($response->getStatusCode() !== 200) {
                unlink($filePath);
                // could not contact the distant URL or HTTP error - fail silently.
                return null;
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return $fullPath ? $filePath : $fileName;
    }
}
