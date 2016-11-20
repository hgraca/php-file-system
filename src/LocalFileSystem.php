<?php
namespace Hgraca\FileSystem;

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

    protected function readFileRaw(string $path): string
    {
        return file_get_contents($path);
    }

    protected function writeFileRaw(string $path, string $content)
    {
        file_put_contents($path, $content);
    }

    protected function deleteFileRaw(string $path)
    {
        unlink($path);
    }

    public function createDirRaw(string $path)
    {
        mkdir($path, 0777, true);
    }

    public function deleteDirRaw(string $path)
    {
        $it    = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($path);
    }

    /**
     * TODO create the tests for LocalFileSystem::copy
     */
    public function copy(string $sourcePath, string $destinationPath): bool
    {
        // Check for symlinks
        if ($this->linkExists($sourcePath)) {
            return symlink(readlink($sourcePath), $destinationPath);
        }

        // Simple copy for a file
        if ($this->fileExists($sourcePath)) {
            return copy($sourcePath, $destinationPath);
        }

        // Make destination directory
        if ($this->dirExists($destinationPath)) {
            $this->deleteDir($destinationPath);
        }
        $this->createDir($destinationPath);

        // Loop through the folder
        $dir = dir($sourcePath);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->copy("$sourcePath/$entry", "$destinationPath/$entry");
        }

        // Clean up
        $dir->close();

        return true;
    }
}
