<?php
namespace Hgraca\FileSystem;

use Hgraca\Helper\StringHelper;

class InMemoryFileSystem extends FileSystemAbstract
{
    const DIR_DISCRIMINATOR = 'dir';

    /** @var array */
    private $fileSystem = [];

    protected function dirExistsRaw(string $path): bool
    {
        return array_key_exists($path, $this->fileSystem) && $this->fileSystem[$path] === self::DIR_DISCRIMINATOR;
    }

    /**
     * TODO create the tests for InMemoryFileSystem::linkExists
     */
    protected function linkExistsRaw(string $path): bool
    {
        // TODO implement InMemoryFileSystem::linkExists
        throw new \Exception('Not implemented ' . __METHOD__);
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

    protected function deleteFileRaw(string $path)
    {
        unset($this->fileSystem[$path]);
    }

    protected function createDirRaw(string $path)
    {
        $this->fileSystem[$path] = self::DIR_DISCRIMINATOR;
    }

    public function deleteDirRaw(string $path)
    {
        foreach ($this->fileSystem as $existingPath => $content) {
            if (StringHelper::hasBeginning($path, $existingPath)) {
                unset($this->fileSystem[$existingPath]);
            }
        }
    }

    /**
     * TODO create the tests for InMemoryFileSystem::copy
     */
    public function copy(string $sourcePath, string $destinationPath): bool
    {
        // TODO implement InMemoryFileSystem::copy
        throw new \Exception('Not implemented' . __METHOD__);
    }
}
