<?php

namespace Hgraca\FileSystem;

use Hgraca\FileSystem\Exception\DirNotFoundException;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\InvalidPathException;
use Hgraca\FileSystem\Exception\PathIsDirException;
use Hgraca\FileSystem\Exception\PathIsFileException;
use Hgraca\FileSystem\Exception\PathIsLinkException;
use SplFileInfo;

abstract class FileSystemAbstract implements FileSystemInterface
{
    const STRICT = 0;
    const IDEMPOTENT = 1;

    private $mode;

    abstract protected function dirExistsRaw(string $path): bool;

    abstract protected function linkExistsRaw(string $path): bool;

    abstract protected function fileExistsRaw(string $path): bool;

    abstract protected function getFileCreationTimestampRaw(string $path): int;

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

    public function __construct(int $mode = self::STRICT)
    {
        $this->mode = $mode;
    }

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
        $path = $this->sanitizeFilePath($path);

        return $this->fileExistsRaw($path) && !$this->linkExists($path);
    }

    public function getFileCreationTimestamp(string $path): int
    {
        $path = $this->sanitizeFilePath($path);

        if (!$this->fileExists($path)) {
            throw new FileNotFoundException("File not found: '$path'");
        }

        return $this->getFileCreationTimestampRaw($path);
    }

    public function readFile(string $path): string
    {
        $path = $this->sanitizeFilePath($path);

        if (!$this->fileExists($path)) {
            throw new FileNotFoundException("File not found: '$path'");
        }

        return $this->readFileRaw($path);
    }

    public function writeFile(string $path, string $content)
    {
        $path = $this->sanitizeFilePath($path);

        if ($this->dirExists($path)) {
            throw new PathIsDirException("The path '$path' already exists and is a dir.");
        }

        $dirPath = dirname($path);
        if (!$this->dirExists($dirPath)) {
            $this->createDir($dirPath);
        }

        $this->writeFileRaw($path, $content);
    }

    public function copyFile(string $sourcePath, string $destinationPath): bool
    {
        $sourcePath = $this->sanitizeFilePath($sourcePath);
        $destinationPath = $this->sanitizeFilePath($destinationPath);

        if (!$this->fileExists($sourcePath)) {
            throw new FileNotFoundException("File not found: '$sourcePath'");
        }

        if ($sourcePath === $destinationPath) {
            return true;
        }

        if ($this->dirExists($destinationPath)) {
            throw new PathIsDirException("The destination path '$destinationPath' already exists and is a dir.");
        }

        $dirPath = dirname($destinationPath);
        if (!$this->dirExists($dirPath)) {
            $this->createDir($dirPath);
        }

        $this->copyFileRaw($sourcePath, $destinationPath);

        return $this->fileExists($destinationPath);
    }

    public function deleteFile(string $path): bool
    {
        $path = $this->sanitizeFilePath($path);

        $fileExists = $this->fileExists($path);

        if ($this->isStrictMode() && !$fileExists) {
            throw new FileNotFoundException("File not found: '$path'");
        }

        if ($fileExists) {
            $this->deleteFileRaw($path);
        }

        return !$this->fileExists($path);
    }

    public function copyLink(string $path, string $toPath): bool
    {
        if (!$this->linkExists($path)) {
            throw new FileNotFoundException("Link not found: '$path'");
        }

        return $this->createLink($toPath, $this->getLinkTarget($path));
    }

    public function createLink(string $path, string $targetPath): bool
    {
        $path = $this->sanitizeFilePath($path);

        if ($this->linkExists($path)) {
            $this->deleteFile($path);
        }

        if ($this->fileExists($path)) {
            throw new PathIsFileException("The path '$path' already exists and is a file");
        }
        if ($this->dirExists($path)) {
            throw new PathIsDirException("The path '$path' already exists and is a dir");
        }

        $this->createLinkRaw($path, $targetPath);

        return $this->linkExists($path);
    }

    public function getLinkTarget(string $path): string
    {
        if (!$this->linkExists($path)) {
            throw new FileNotFoundException("Link not found: '$path'");
        }

        return $this->getLinkTargetRaw($path);
    }

    public function createDir(string $path): bool
    {
        $path = $this->sanitizeDirPath($path);

        if ($this->linkExists($path)) {
            throw new PathIsLinkException("The path '$path' already exists and is a file");
        }

        if ($this->fileExists($path)) {
            throw new PathIsFileException("The path '$path' already exists and is a file");
        }

        $dirExists = $this->dirExists($path);
        if ($this->isStrictMode() && $dirExists) {
            throw new PathIsDirException("The path '$path' already exists and is a dir");
        }

        if (!$dirExists) {
            $this->createDirRaw($path);
        }

        return $this->dirExists($path);
    }

    public function deleteDir(string $path): bool
    {
        $path = $this->sanitizeDirPath($path);

        $dirExists = $this->dirExists($path);
        if ($this->isStrictMode() && !$dirExists) {
            throw new DirNotFoundException();
        }

        if ($dirExists) {
            $this->deleteDirRaw($path);
        }

        return !$this->dirExists($path);
    }

    /**
     * @throws DirNotFoundException
     *
     * @return string[] The array with the file and dir names
     */
    public function readDir(string $path): array
    {
        $path = $this->sanitizeDirPath($path);

        if (!$this->dirExists($path)) {
            throw new DirNotFoundException("Dir not found: '$path'");
        }

        $result = $this->readDirRaw($path);

        sort($result);

        return $result;
    }

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

            $this->copy(
                $this->sanitizeDirPath($sourcePath) . $fileName,
                $this->sanitizeDirPath($destinationPath) . $fileName
            );
        }

        return true;
    }

    public function getAbsolutePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
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

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public function getExtension(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        return (new SplFileInfo($path))->getExtension();
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
        return $this->getAbsolutePath(trim($path, " \t\n\r\0\x0B\\/"));
    }

    private function isStrictMode(): bool
    {
        return $this->mode === self::STRICT;
    }
}
