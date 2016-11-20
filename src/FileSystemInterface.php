<?php
namespace Hgraca\FileSystem;

use Hgraca\FileSystem\Exception\DirNotFoundException;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\FileSystemException;
use Hgraca\FileSystem\Exception\InvalidPathException;

interface FileSystemInterface
{
    /**
     * @throws InvalidPathException
     */
    public function dirExists(string $path): bool;

    /**
     * @throws InvalidPathException
     */
    public function linkExists(string $path): bool;

    /**
     * @throws InvalidPathException
     */
    public function fileExists(string $path): bool;

    /**
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function readFile(string $path): string;

    /**
     * @throws FileSystemException
     * @throws InvalidPathException
     */
    public function writeFile(string $path, string $content);

    /**
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function deleteFile(string $path);

    /**
     * Creates a folder and all intermediate folders idf they don't exist
     *
     * @throws InvalidPathException
     * @throws FileSystemException
     */
    public function createDir(string $path);

    /**
     * Deletes a folder and all its contents
     *
     * @throws DirNotFoundException
     * @throws InvalidPathException
     */
    public function deleteDir(string $path);

    /**
     * Copies a symlink, file or folder and all its contents.
     * If the destination exists, it will be completely replaced.
     */
    public function copy(string $sourcePath, string $destinationPath): bool;
}
