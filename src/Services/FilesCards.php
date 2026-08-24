<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;


class FilesCards
{
    public function __construct(private Filesystem $filesystem)
    {
        throw new \Exception('Not implemented');
    }
}