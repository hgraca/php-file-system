<?php
namespace Hgraca\FileSystem;

use Hgraca\Helper\StringHelper;

class InMemoryFileSystem extends FileSystemAbstract
{
    const DIR_DISCRIMINATOR  = 'dir';
    const LINK_DISCRIMINATOR = 'link';
    const FILE_DISCRIMINATOR = 'file';

    const KEY_TYPE          = 'type';
    const KEY_CONTENT       = 'content';
    const KEY_CREATION_TIME = 'creation_time';

    /** @var array */
    private $fileSystem = [];

    protected function dirExistsRaw(string $path): bool
    {
        return array_key_exists(
                $path,
                $this->fileSystem
            ) && $this->fileSystem[$path][self::KEY_TYPE] === self::DIR_DISCRIMINATOR;
    }

    protected function linkExistsRaw(string $path): bool
    {
        return array_key_exists(
                $path,
                $this->fileSystem
            ) && $this->fileSystem[$path][self::KEY_TYPE] === self::LINK_DISCRIMINATOR;
    }

    protected function fileExistsRaw(string $path): bool
    {
        return array_key_exists(
                $path,
                $this->fileSystem
            ) && $this->fileSystem[$path][self::KEY_TYPE] === self::FILE_DISCRIMINATOR;
    }

    protected function getFileCreationTimestampRaw(string $path): int
    {
        return $this->fileSystem[$path][self::KEY_CREATION_TIME];
    }

    protected function readFileRaw(string $path): string
    {
        return $this->fileSystem[$path][self::KEY_CONTENT];
    }

    protected function writeFileRaw(string $path, string $content)
    {
        $this->fileSystem[$path] = [
            self::KEY_TYPE          => self::FILE_DISCRIMINATOR,
            self::KEY_CONTENT       => $content,
            self::KEY_CREATION_TIME => time(),
        ];
    }

    protected function copyFileRaw(string $sourcePath, string $destinationPath)
    {
        $this->writeFileRaw($destinationPath, $this->readFileRaw($sourcePath));
    }

    protected function deleteFileRaw(string $path)
    {
        unset($this->fileSystem[$path]);
    }

    protected function createLinkRaw(string $path, string $targetPath)
    {
        $this->fileSystem[$path] = [
            self::KEY_TYPE    => self::LINK_DISCRIMINATOR,
            self::KEY_CONTENT => $targetPath,
        ];
    }

    protected function getLinkTargetRaw(string $path): string
    {
        return $this->fileSystem[$path][self::KEY_CONTENT];
    }

    protected function createDirRaw(string $path)
    {
        $this->fileSystem[$path] = [
            self::KEY_TYPE => self::DIR_DISCRIMINATOR,
        ];
    }

    protected function deleteDirRaw(string $path)
    {
        foreach ($this->fileSystem as $existingPath => $content) {
            if (StringHelper::hasBeginning($path, $existingPath)) {
                unset($this->fileSystem[$existingPath]);
            }
        }
    }

    /**
     * @return string[] The array with the file and dir names
     */
    protected function readDirRaw(string $path): array
    {
        $dirContent = [];
        foreach ($this->fileSystem as $existingPath => $content) {
            if (StringHelper::hasBeginning($path, $existingPath)) {
                $dirContent[] = current(
                    explode(DIRECTORY_SEPARATOR, StringHelper::removeFromBeginning($path, $existingPath), 2)
                );
            }
        }

        return array_values(
            array_filter(
                array_unique($dirContent),
                function ($v) {
                    return $v !== '';
                },
                ARRAY_FILTER_USE_BOTH
            )
        );
    }
}
