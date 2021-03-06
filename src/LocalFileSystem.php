<?php

namespace Hgraca\FileSystem;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LocalFileSystem extends FileSystemAbstract
{
    protected function dirExistsRaw(string $path): bool
    {
        return file_exists($path) && is_dir($path);
    }

    protected function linkExistsRaw(string $path): bool
    {
        return file_exists($path) && is_link($path);
    }

    protected function fileExistsRaw(string $path): bool
    {
        return file_exists($path) && is_file($path);
    }

    protected function getFileCreationTimestampRaw(string $path): int
    {
        return filectime($path);
    }

    protected function readFileRaw(string $path): string
    {
        return file_get_contents($path);
    }

    protected function writeFileRaw(string $path, string $content)
    {
        file_put_contents($path, $content, LOCK_EX);
    }

    protected function copyFileRaw(string $sourcePath, string $destinationPath)
    {
        copy($sourcePath, $destinationPath);
    }

    protected function deleteFileRaw(string $path)
    {
        unlink($path);
    }

    protected function createLinkRaw(string $path, string $targetPath)
    {
        symlink($targetPath, $path);
    }

    protected function getLinkTargetRaw(string $path): string
    {
        return readlink($path);
    }

    public function createDirRaw(string $path)
    {
        $oldUmask = umask(0);
        mkdir($path, 0777, true);
        umask($oldUmask);
    }

    public function deleteDirRaw(string $path)
    {
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($path);
    }

    /**
     * @return string[] The array with the file and dir names
     */
    public function readDirRaw(string $path): array
    {
        $iterator = new DirectoryIterator($path);

        $contents = [];
        foreach ($iterator as $fileInfo) {
            $contents[] = $fileInfo->getFilename();
        }

        return $contents;
    }
}
