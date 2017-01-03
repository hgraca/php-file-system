<?php

namespace Hgraca\FileSystem;

use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\InvalidPathException;
use Hgraca\FileSystem\Exception\PathIsDirException;
use Hgraca\FileSystem\Exception\PathIsFileException;
use Hgraca\FileSystem\Exception\PathIsLinkException;

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

    public function getFileCreationTimestamp(string $path): int;

    /**
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function readFile(string $path): string;

    /**
     * @throws PathIsDirException
     * @throws InvalidPathException
     */
    public function writeFile(string $path, string $content);

    /**
     * @throws PathIsDirException
     */
    public function copyFile(string $sourcePath, string $destinationPath): bool;

    /**
     * @throws InvalidPathException
     */
    public function deleteFile(string $path): bool;

    public function copyLink(string $path, string $toPath): bool;

    /**
     * @throws PathIsDirException
     * @throws PathIsFileException
     */
    public function createLink(string $path, string $targetPath): bool;

    public function getLinkTarget(string $path): string;

    /**
     * Creates a folder and all intermediate folders idf they don't exist
     *
     * @throws InvalidPathException
     * @throws PathIsFileException
     * @throws PathIsLinkException
     */
    public function createDir(string $path): bool;

    /**
     * Deletes a folder and all its contents
     *
     * @throws InvalidPathException
     */
    public function deleteDir(string $path): bool;

    /**
     * @return string[] The array with the file and dir names
     */
    public function readDir(string $path): array;

    /**
     * Copies a symlink, file or folder and all its contents.
     * If the destination exists, it will be completely replaced.
     */
    public function copy(string $sourcePath, string $destinationPath): bool;

    public function getAbsolutePath(string $path): string;

    public function getExtension(string $path): string;
}
