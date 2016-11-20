<?php
namespace Hgraca\FileSystem;

use Hgraca\FileSystem\Exception\DirNotFoundException;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\FileSystemException;
use Hgraca\FileSystem\Exception\InvalidPathException;

abstract class FileSystemAbstract implements FileSystemInterface
{
    abstract protected function dirExistsRaw(string $path): bool;

    abstract protected function linkExistsRaw(string $path): bool;

    abstract protected function fileExistsRaw(string $path): bool;

    abstract protected function readFileRaw(string $path): string;

    abstract protected function writeFileRaw(string $path, string $content);

    abstract protected function deleteFileRaw(string $path);

    abstract protected function createDirRaw(string $path);

    abstract protected function deleteDirRaw(string $path);

    public function dirExists(string $path): bool
    {
        return $this->dirExistsRaw($this->sanitizeDirPath($path));
    }

    public function linkExists(string $path): bool
    {
        return $this->linkExistsRaw($this->sanitizeFilePath($path));
    }

    public function fileExists(string $path): bool
    {
        return $this->fileExistsRaw($this->sanitizeFilePath($path));
    }

    public function readFile(string $path): string
    {
        $path = $this->sanitizeFilePath($path);

        if (! $this->fileExists($path)) {
            throw new FileNotFoundException("File not found: '$path'");
        }

        return $this->readFileRaw($path);
    }

    public function writeFile(string $path, string $content)
    {
        $path = $this->sanitizeFilePath($path);

        if ($this->dirExists($path)) {
            throw new FileSystemException("The path '$path' already exists and is a dir.");
        }

        $dirPath = dirname($path);
        if (! $this->dirExists($dirPath)) {
            $this->createDir($dirPath);
        }

        $this->writeFileRaw($path, $content);
    }

    public function deleteFile(string $path): bool
    {
        $path = $this->sanitizeFilePath($path);

        if (! $this->fileExists($path)) {
            throw new FileNotFoundException("File not found: '$path'");
        }

        $this->deleteFileRaw($path);

        return ! $this->fileExists($path);
    }

    public function createDir(string $path): bool
    {
        $path = $this->sanitizeDirPath($path);

        $fileExists = $this->fileExists($path);
        if ($fileExists || $this->dirExists($path)) {
            throw new FileSystemException("The path '$path' already exists and is a " . $fileExists ? 'file.' : 'dir.');
        }

        $this->createDirRaw($path);

        return $this->dirExists($path);
    }

    public function deleteDir(string $path): bool
    {
        $path = $this->sanitizeDirPath($path);

        if (! $this->dirExists($path)) {
            throw new DirNotFoundException("Dir not found: '$path'");
        }

        $this->deleteDirRaw($path);

        return ! $this->dirExists($path);
    }

    /**
     * TODO Create the tests for FileSystemAbstract::getAbsolutePath
     */
    public function getAbsolutePath($path): string
    {
        $path      = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts     = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * @throws InvalidPathException
     */
    protected function sanitizeDirPath(string $path): string
    {
        return $this->sanitizePath($path) . '/';
    }

    /**
     * @throws InvalidPathException
     */
    protected function sanitizeFilePath(string $path): string
    {
        return $this->sanitizePath($path);
    }

    /**
     * @throws InvalidPathException
     */
    protected function sanitizePath(string $path): string
    {
        return '/' . $this->getAbsolutePath(trim($path, " \t\n\r\0\x0B\\/"));
    }
}
