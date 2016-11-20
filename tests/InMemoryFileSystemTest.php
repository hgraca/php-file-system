<?php
namespace Hgraca\FileSystem\Test;

use Hgraca\FileSystem\InMemoryFileSystem;
use Hgraca\Helper\InstanceHelper;

class InMemoryFileSystemTest extends FileSystemTestAbstract
{
    public function setUp()
    {
        $this->fileSystem = new InMemoryFileSystem();
        InstanceHelper::setProtectedProperty(
            $this->fileSystem,
            'fileSystem',
            [
                '/a/dir/'                          => InMemoryFileSystem::DIR_DISCRIMINATOR,
                '/a/dir/fileA'                     => self::FILE_A_CONTENTS,
                '/a/dir/another_dir/'              => InMemoryFileSystem::DIR_DISCRIMINATOR,
                '/a/dir/another_dir/fileB'         => self::FILE_B_CONTENTS,
                '/a/dir/yet_another_dir/'          => InMemoryFileSystem::DIR_DISCRIMINATOR,
                '/a/dir/yet_another_dir/fileC.php' => self::FILE_C_CONTENTS,
            ]
        );
    }

    protected function getBasePath(): string
    {
        return '';
    }
}
