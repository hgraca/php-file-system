<?php
namespace Hgraca\FileSystem;

use Hgraca\Helper\StringHelper;

class InMemoryFileSystem extends FileSystemAbstract
{
    const DIR_DISCRIMINATOR = 'dir';
    const LINK_DISCRIMINATOR = '@->';

    /** @var array */
    private $fileSystem = [];

    protected function dirExistsRaw(string $path): bool
    {
        return array_key_exists($path, $this->fileSystem) && $this->fileSystem[$path] === self::DIR_DISCRIMINATOR;
    }

    protected function linkExistsRaw(string $path): bool
    {
        return array_key_exists($path, $this->fileSystem)
        && StringHelper::hasBeginning(self::LINK_DISCRIMINATOR, $this->fileSystem[$path]);
    }

    protected function fileExistsRaw(string $path): bool
    {
        return array_key_exists($path, $this->fileSystem) && $this->fileSystem[$path] !== self::DIR_DISCRIMINATOR;
    }

    protected function readFileRaw(string $path): string
    {
        return $this->fileSystem[$path];
    }

    protected function writeFileRaw(string $path, string $content)
    {
        $this->fileSystem[$path] = $content;
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
        $this->fileSystem[$path] = self::LINK_DISCRIMINATOR . $targetPath;
    }

    protected function getLinkTargetRaw(string $path): string
    {
        return StringHelper::removeFromBeginning(self::LINK_DISCRIMINATOR, $this->fileSystem[$path]);
    }

    protected function createDirRaw(string $path)
    {
        $this->fileSystem[$path] = self::DIR_DISCRIMINATOR;
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
        $content = [];
        foreach ($this->fileSystem as $existingPath => $content) {
            if (StringHelper::hasBeginning($path, $existingPath)) {
                $content[] = current(
                    explode(DIRECTORY_SEPARATOR, StringHelper::removeFromBeginning($path, $existingPath), 2)
                );
            }
        }

        return $content;
    }
}
