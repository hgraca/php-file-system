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

    abstract protected function copyFileRaw(string $sourcePath, string $destinationPath);

    abstract protected function deleteFileRaw(string $path);

    abstract protected function createLinkRaw(string $path, string $targetPath);

    abstract protected function getLinkTargetRaw(string $path): string;

    abstract protected function createDirRaw(string $path);

    abstract protected function deleteDirRaw(string $path);

    /**
     * @return string[] The array with the fine names
     */
    abstract protected function readDirRaw(string $path): array;

    public function dirExists(string $path): bool
    {
        return $this->dirExistsRaw($this->sanitizeDirPath($path));
    }

    /**
     * TODO create the tests for linkExists
     */
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

    public function copyFile(string $sourcePath, string $destinationPath): bool
    {
        $sourcePath = $this->sanitizeFilePath($sourcePath);
        $destinationPath = $this->sanitizeFilePath($destinationPath);

        if (! $this->fileExists($sourcePath)) {
            throw new FileNotFoundException("File not found: '$sourcePath'");
        }

        if ($sourcePath === $destinationPath) {
            return true;
        }

        if ($this->dirExists($destinationPath)) {
            throw new FileSystemException("The destination path '$destinationPath' already exists and is a dir.");
        }

        $dirPath = dirname($destinationPath);
        if (! $this->dirExists($dirPath)) {
            $this->createDir($dirPath);
        }

        $this->copyFileRaw($sourcePath, $destinationPath);

        return $this->fileExists($destinationPath);
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

    /**
     * TODO create the tests for copyLink
     */
    public function copyLink(string $path, string $toPath): bool
    {
        return $this->createLink($toPath, $this->getLinkTarget($path));
    }

    /**
     * TODO create the tests for createLink
     */
    public function createLink(string $path, string $targetPath): bool
    {
        $path = $this->sanitizeDirPath($path);

        $fileExists = $this->fileExists($path);
        $linkExists = $this->linkExists($path);
        if ($fileExists || $linkExists || $this->dirExists($path)) {
            throw new FileSystemException(
                "The path '$path' already exists and is a " . $fileExists ? 'file.' : $linkExists ? 'link.' : 'dir.'
            );
        }

        $this->createLinkRaw($path, $targetPath);

        return $this->linkExists($path);
    }

    /**
     * TODO create the tests for getLinkTarget
     */
    protected function getLinkTarget(string $path): string
    {
        if (! $this->linkExists($path)) {
            throw new FileNotFoundException("Link not found: '$path'");
        }

        return $this->getLinkTargetRaw($path);
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
     * @return string[] The array with the file and dir names
     * TODO create the tests for readDir
     */
    public function readDir(string $path): array
    {
        $path = $this->sanitizeDirPath($path);

        if (! $this->dirExists($path)) {
            throw new DirNotFoundException("Dir not found: '$path'");
        }

        return $this->readDirRaw($path);
    }

    /**
     * TODO create the tests for copy
     */
    public function copy(string $sourcePath, string $destinationPath): bool
    {
        if ($sourcePath === $destinationPath) {
            return true;
        }

        // Check for symlinks
        if ($this->linkExists($sourcePath)) {
            return $this->copyLink($sourcePath, $destinationPath);
        }

        // Simple copy for a file
        if ($this->fileExists($sourcePath)) {
            return $this->copyFile($sourcePath, $destinationPath);
        }

        // Make destination directory
        if ($this->dirExists($destinationPath)) {
            $this->deleteDir($destinationPath);
        }
        $this->createDir($destinationPath);

        // Loop through the folder
        foreach ($this->readDir($sourcePath) as $fileName) {
            // Skip pointers
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $this->copy("$sourcePath/$fileName", "$destinationPath/$fileName");
        }

        return true;
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
