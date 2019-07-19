<?php

namespace App\Library;

class Helper
{
    /**
     * Get Root Directory
     *
     * @param string|null $path
     * @return string
     */
    public static function getRootDir(string $path = null): string
    {
        return dirname(__DIR__, 2) . ($path ? '/' . $path : '');
    }
}