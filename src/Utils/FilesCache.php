<?php

namespace App\Utils;

use App\Utils\Interfaces\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;

class FilesCache implements CacheInterface
{
    /* @var $cache FilesystemTagAwareAdapter */
    public $cache;

    public function __construct(string $projectDir, string $env) {
        $this->cache = new FilesystemTagAwareAdapter(
            'FilesystemCache',
            $TTL = 3600,
            $projectDir . DIRECTORY_SEPARATOR . "var/cache/$env"
        );
    }
}