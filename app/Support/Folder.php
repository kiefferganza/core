<?php

namespace App\Support;

class Folder
{
    public static function deleteRecursively(string $dir): void
    {
        $dir = rtrim($dir, '/');
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            chmod("$dir/$file", 0777);
            (is_dir("$dir/$file")) ? static::deleteRecursively("$dir/$file") : unlink("$dir/$file");
        }

        rmdir($dir);
    }
}
