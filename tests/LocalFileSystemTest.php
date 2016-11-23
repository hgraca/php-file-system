<?php
namespace Hgraca\FileSystem\Test;

use Hgraca\FileSystem\LocalFileSystem;

class LocalFileSystemTest extends FileSystemTestAbstract
{
    private static $TMP_FOLDER = __DIR__ . '/tmp';

    public function setUp()
    {
        $this->fileSystem = new LocalFileSystem();

        mkdir(self::$TMP_FOLDER . '/a/dir/an_empty_dir', 0777, true);
        mkdir(self::$TMP_FOLDER . '/a/dir/another_dir', 0777, true);
        mkdir(self::$TMP_FOLDER . '/a/dir/yet_another_dir', 0777, true);

        file_put_contents(self::$TMP_FOLDER . '/a/dir/fileA', self::FILE_A_CONTENTS);
        file_put_contents(self::$TMP_FOLDER . '/a/dir/another_dir/fileB', self::FILE_B_CONTENTS);
        file_put_contents(self::$TMP_FOLDER . '/a/dir/yet_another_dir/fileC.php', self::FILE_C_CONTENTS);

        if (! $this->fileSystem->dirExists(self::$TMP_FOLDER)) {
            throw new \Exception("The tmp directory used for testing could not be created!");
        }
    }

    public function tearDown()
    {
        $this->fileSystem->deleteDir(self::$TMP_FOLDER);
        if ($this->fileSystem->dirExists(self::$TMP_FOLDER)) {
            throw new \Exception("The tmp directory used for testing could not be deleted!");
        }
    }

    protected function getBasePath(): string
    {
        return self::$TMP_FOLDER;
    }
}
