<?php

namespace Sevming\Helper\Traits;

trait FilesystemTrait
{
    /**
     * 递归创建文件夹
     *
     * @param string $dir
     * @param int $mode

     * @return bool
     */
    public static function recursiveMkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }

        if (!static::recursiveMkdirs(dirname($dir), $mode)) {
            return false;
        }

        return @mkdir($dir, $mode);
    }

    /**
     * 获取指定目录下的所有文件
     *
     * @param string $dir
     *
     * @return array
     */
    public static function getAllFiles($dir)
    {
        $files = [];
        if ($handle = @opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($path)) {
                        $files[$file] = static::getAllFiles($path);
                    } else {
                        $files[] = $path;
                    }
                }
            }
        }

        return $files;
    }
}