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
                '/a/'                              => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/.'                             => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/..'                            => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/fileB.ln'                      => [
                    InMemoryFileSystem::KEY_TYPE    => InMemoryFileSystem::LINK_DISCRIMINATOR,
                    InMemoryFileSystem::KEY_CONTENT => '/a/dir/another_dir/fileB',
                ],
                '/a/dir/'                          => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/.'                         => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/..'                        => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/fileA'                     => [
                    InMemoryFileSystem::KEY_TYPE          => InMemoryFileSystem::FILE_DISCRIMINATOR,
                    InMemoryFileSystem::KEY_CONTENT       => self::FILE_A_CONTENTS,
                    InMemoryFileSystem::KEY_CREATION_TIME => self::FILE_A_CREATION_TIME,
                ],
                '/a/dir/another_dir/'              => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/another_dir/.'             => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/another_dir/..'            => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/another_dir/fileB'         => [
                    InMemoryFileSystem::KEY_TYPE          => InMemoryFileSystem::FILE_DISCRIMINATOR,
                    InMemoryFileSystem::KEY_CONTENT       => self::FILE_B_CONTENTS,
                    InMemoryFileSystem::KEY_CREATION_TIME => self::FILE_B_CREATION_TIME,
                ],
                '/a/dir/yet_another_dir/'          => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/yet_another_dir/.'         => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/yet_another_dir/..'        => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/yet_another_dir/fileC.php' => [
                    InMemoryFileSystem::KEY_TYPE          => InMemoryFileSystem::FILE_DISCRIMINATOR,
                    InMemoryFileSystem::KEY_CONTENT       => self::FILE_C_CONTENTS,
                    InMemoryFileSystem::KEY_CREATION_TIME => self::FILE_C_CREATION_TIME,
                ],
                '/a/dir/an_empty_dir/'             => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/an_empty_dir/.'            => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
                '/a/dir/an_empty_dir/..'           => [InMemoryFileSystem::KEY_TYPE => InMemoryFileSystem::DIR_DISCRIMINATOR],
            ]
        );
    }

    protected function getBasePath(): string
    {
        return '';
    }

    public function testGetFileCreationTimestamp()
    {
        $path = $this->getBasePath() . '/a/dir/fileA';
        self::assertEquals(self::FILE_A_CREATION_TIME, $this->fileSystem->getFileCreationTimestamp($path));
    }
}
