<?php
namespace Hgraca\FileSystem\Test;

use Hgraca\FileSystem\LocalFileSystem;

class LocalFileSystemTest extends FileSystemTestAbstract
{
    private static $TMPL_FOLDER = __DIR__ . '/tmpl';
    private static $TMP_FOLDER = __DIR__ . '/tmp';

    public function setUp()
    {
        $this->fileSystem = new LocalFileSystem();
        $this->fileSystem->copy(self::$TMPL_FOLDER, self::$TMP_FOLDER);
        if (!$this->fileSystem->dirExists(self::$TMP_FOLDER)) {
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
