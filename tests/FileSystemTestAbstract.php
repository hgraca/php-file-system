<?php
namespace Hgraca\FileSystem\Test;

use Hgraca\FileSystem\Exception\DirNotFoundException;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\FileSystemException;
use Hgraca\FileSystem\FileSystemInterface;
use PHPUnit_Framework_TestCase;

abstract class FileSystemTestAbstract extends PHPUnit_Framework_TestCase
{
    const FILE_A_CONTENTS = 'file A contents';
    const FILE_B_CONTENTS = 'file B contents';
    const FILE_C_CONTENTS = 'file C contents';
    const FILE_Z_CONTENTS = 'file Z contents';

    /** @var FileSystemInterface */
    protected $fileSystem;

    abstract protected function getBasePath(): string;

    /**
     * @dataProvider dataProvider_testDirExists
     */
    public function testDirExists(string $path, bool $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::assertEquals($expectedResult, $this->fileSystem->dirExists($path));
    }

    public function dataProvider_testDirExists(): array
    {
        return [
            ['/a/dir/', true],
            ['/a/dir/fileA', false],
            ['/a/dir/another_dir', true],
            ['/a/dir/yet_another_dir/', true],
            ['/a/unexisting/dir/', false],
        ];
    }

    /**
     * @dataProvider dataProvider_testFileExists
     */
    public function testFileExists(string $path, bool $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::assertEquals($expectedResult, $this->fileSystem->fileExists($path));
    }

    public function dataProvider_testFileExists(): array
    {
        return [
            ['/a/dir/', false],
            ['/a/dir/fileA', true],
            ['/a/dir/another_dir', false],
            ['/a/dir/yet_another_dir/', false],
            ['/a/unexisting/file', false],
        ];
    }

    /**
     * @dataProvider dataProvider_testReadFile_successful
     */
    public function testReadFile_successful(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::assertEquals($expectedResult, $this->fileSystem->readFile($path));
    }

    public function dataProvider_testReadFile_successful(): array
    {
        return [
            ['/a/dir/fileA', self::FILE_A_CONTENTS],
            ['/a/dir/yet_another_dir/fileC.php', self::FILE_C_CONTENTS],
        ];
    }

    /**
     * @dataProvider dataProvider_testReadFile_fail
     */
    public function testReadFile_fail(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::expectException($expectedResult);
        $this->fileSystem->readFile($path);
    }

    public function dataProvider_testReadFile_fail(): array
    {
        return [
            ['/a/dir/', FileNotFoundException::class],
            ['/a/dir/yet_another_dir', FileNotFoundException::class],
            ['/a/unexisting/file', FileNotFoundException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_testWriteFile_successful
     */
    public function testWriteFile_successful(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        $this->fileSystem->writeFile($path, $expectedResult);
        self::assertEquals($expectedResult, $this->fileSystem->readFile($path));
        self::assertTrue($this->fileSystem->fileExists($path));
    }

    public function dataProvider_testWriteFile_successful(): array
    {
        return [
            ['/a/dir/fileZ', self::FILE_Z_CONTENTS],
            ['/a/dir/yet_another_dir/fileC.php', self::FILE_A_CONTENTS],
            ['/a/dir/unexisting/fileZ.php', self::FILE_Z_CONTENTS],
        ];
    }

    /**
     * @dataProvider dataProvider_testWriteFile_fail
     */
    public function testWriteFile_fail(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::expectException($expectedResult);
        $this->fileSystem->writeFile($path, 'AAA');
        self::assertFalse($this->fileSystem->fileExists($path));
    }

    public function dataProvider_testWriteFile_fail(): array
    {
        return [
            ['/a/dir', FileSystemException::class],
            ['/a/dir/yet_another_dir', FileSystemException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_testCopyFile_successful
     */
    public function testCopyFile_successful(string $sourcePath, string $destinationPath)
    {
        $sourcePath = $this->getBasePath() . $sourcePath;
        $destinationPath = $this->getBasePath() . $destinationPath;

        self::assertTrue($this->fileSystem->copyFile($sourcePath, $destinationPath));
        self::assertTrue($this->fileSystem->fileExists($destinationPath));
        self::assertEquals($this->fileSystem->readFile($sourcePath), $this->fileSystem->readFile($destinationPath));
    }

    public function dataProvider_testCopyFile_successful(): array
    {
        return [
            ['/a/dir/fileA', '/a/dir/fileA'],
            ['/a/dir/fileA', '/a/dir/fileZ'],
            ['/a/dir/fileA', '/a/dir/unexistent_dir/fileZ'],
        ];
    }

    /**
     * @dataProvider dataProvider_testCopyFile_fail
     */
    public function testCopyFile_fail(string $sourcePath, string $destinationPath, string $expectedException)
    {
        $sourcePath      = $this->getBasePath() . $sourcePath;
        $destinationPath = $this->getBasePath() . $destinationPath;

        self::expectException($expectedException);
        $this->fileSystem->copyFile($sourcePath, $destinationPath);
    }

    public function dataProvider_testCopyFile_fail(): array
    {
        return [
            ['/a/dir/fileA', '/a/dir', FileSystemException::class],
            ['/a/dir/fileZ', '/a/dir/fileQ', FileNotFoundException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_testDeleteFile_successful
     */
    public function testDeleteFile_successful(string $path)
    {
        $path = $this->getBasePath() . $path;
        $this->fileSystem->deleteFile($path);
        self::assertFalse($this->fileSystem->fileExists($path));
    }

    public function dataProvider_testDeleteFile_successful(): array
    {
        return [
            ['/a/dir/fileA'],
            ['/a/dir/yet_another_dir/fileC.php'],
        ];
    }

    /**
     * @dataProvider dataProvider_testDeleteFile_fail
     */
    public function testDeleteFile_fail(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::expectException($expectedResult);
        $this->fileSystem->deleteFile($path);
    }

    public function dataProvider_testDeleteFile_fail(): array
    {
        return [
            ['/a/dir', FileNotFoundException::class],
            ['/a/dir/yet_another_dir', FileNotFoundException::class],
            ['/a/dir/unexisting/fileZ.php', FileNotFoundException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_testCreateDir_successful
     */
    public function testCreateDir_successful(string $path)
    {
        $path = $this->getBasePath() . $path;
        $this->fileSystem->createDir($path);
        self::assertTrue($this->fileSystem->dirExists($path));
    }

    public function dataProvider_testCreateDir_successful(): array
    {
        return [
            ['/a/new/dir/'],
        ];
    }

    /**
     * @dataProvider dataProvider_testCreateDir_fail
     */
    public function testCreateDir_fail(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::expectException($expectedResult);
        $this->fileSystem->createDir($path);
        self::assertFalse($this->fileSystem->dirExists($path));
    }

    public function dataProvider_testCreateDir_fail(): array
    {
        return [
            ['/a/dir', FileSystemException::class],
            ['/a/dir/yet_another_dir', FileSystemException::class],
            ['/a/dir/yet_another_dir/fileC.php', FileSystemException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_testDeleteDir_successful
     */
    public function testDeleteDir_successful(string $path)
    {
        $path = $this->getBasePath() . $path;
        $this->fileSystem->deleteDir($path);
        self::assertFalse($this->fileSystem->dirExists($path));
    }

    public function dataProvider_testDeleteDir_successful(): array
    {
        return [
            ['/a/dir/'],
            ['/a/dir/yet_another_dir'],
        ];
    }

    /**
     * @dataProvider dataProvider_testDeleteDir_fail
     */
    public function testDeleteDir_fail(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::expectException($expectedResult);
        $this->fileSystem->deleteDir($path);
    }

    public function dataProvider_testDeleteDir_fail(): array
    {
        return [
            ['/a/dir/fileA', DirNotFoundException::class],
            ['/a/dir/unexisting_dir/', DirNotFoundException::class],
        ];
    }
}
