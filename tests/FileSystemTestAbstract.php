<?php

namespace Hgraca\FileSystem\Test;

use Hgraca\FileSystem\Exception\DirNotFoundException;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\FileSystemException;
use Hgraca\FileSystem\Exception\PathIsDirException;
use Hgraca\FileSystem\Exception\PathIsFileException;
use Hgraca\FileSystem\Exception\PathIsLinkException;
use Hgraca\FileSystem\FileSystemInterface;
use PHPUnit_Framework_TestCase;

abstract class FileSystemTestAbstract extends PHPUnit_Framework_TestCase
{
    const FILE_A_CONTENTS = 'file A contents';
    const FILE_A_CREATION_TIME = 123456789;
    const FILE_B_CONTENTS = 'file B contents';
    const FILE_B_CREATION_TIME = 123456790;
    const FILE_C_CONTENTS = 'file C contents';
    const FILE_C_CREATION_TIME = 123456791;
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

    /**
     * @expectedException \Hgraca\FileSystem\Exception\FileNotFoundException
     */
    public function testGetFileCreationTimestamp_ThrowsFileNotFoundException()
    {
        $path = $this->getBasePath() . '/a/unexisting/dir/fileK';
        $this->fileSystem->getFileCreationTimestamp($path);
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
        $sourcePath = $this->getBasePath() . $sourcePath;
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
            ['/a/dir', PathIsDirException::class],
            ['/a/fileB.ln', PathIsLinkException::class],
            ['/a/dir/yet_another_dir/fileC.php', PathIsFileException::class],
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

    /**
     * @dataProvider dataProvider_testReadDir_successful
     */
    public function testReadDir_successful(string $path, array $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::assertEquals($expectedResult, $this->fileSystem->readDir($path));
    }

    public function dataProvider_testReadDir_successful(): array
    {
        return [
            ['/a/dir/', ['.', '..', 'an_empty_dir', 'another_dir', 'fileA', 'yet_another_dir']],
            ['/a/dir/yet_another_dir', ['.', '..', 'fileC.php']],
            ['/a/dir/an_empty_dir/', ['.', '..']],
        ];
    }

    /**
     * @dataProvider dataProvider_testReadDir_fail
     */
    public function testReadDir_fail(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::expectException($expectedResult);
        $this->fileSystem->readDir($path);
    }

    public function dataProvider_testReadDir_fail(): array
    {
        return [
            ['/a/dir/fileA', DirNotFoundException::class],
            ['/a/dir/unexisting_dir/', DirNotFoundException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_test_linkExists
     */
    public function test_linkExists(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        self::assertEquals($expectedResult, $this->fileSystem->linkExists($path));
    }

    public function dataProvider_test_linkExists(): array
    {
        return [
            ['/a/fileB.ln', true],
            ['/a/dir/fileA', false],
            ['/a/dir/unexisting_dir/', false],
        ];
    }

    /**
     * @dataProvider dataProvider_test_copyLink
     */
    public function test_copyLink(string $sourcePath, string $destinationPath, string $expectedException = null)
    {
        $sourcePath = $this->getBasePath() . $sourcePath;
        $destinationPath = $this->getBasePath() . $destinationPath;

        if ($expectedException) {
            self::expectException($expectedException);
        }

        $this->fileSystem->copyLink($sourcePath, $destinationPath);

        self::assertTrue($this->fileSystem->linkExists($destinationPath));
        self::assertEquals(
            $this->fileSystem->getLinkTarget($sourcePath),
            $this->fileSystem->getLinkTarget($destinationPath)
        );
    }

    public function dataProvider_test_copyLink(): array
    {
        return [
            ['/a/fileB.ln', '/a/fileB.copy.ln', null],
            ['/a/dir/fileA', '/a/dir/fileA.copy.ln', FileNotFoundException::class],
            ['/a/fileB.ln', '/a/fileB.ln', FileSystemException::class],
            ['/a/fileB.ln', '/a/dir/fileA', FileSystemException::class],
            ['/a/fileB.ln', '/a/dir', FileSystemException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_test_createLink
     */
    public function test_createLink(string $path, string $targetPath, string $expectedException = null)
    {
        $path = $this->getBasePath() . $path;
        $targetPath = $this->getBasePath() . $targetPath;

        if ($expectedException) {
            self::expectException($expectedException);
        }

        $this->fileSystem->createLink($path, $targetPath);

        self::assertTrue($this->fileSystem->linkExists($path));
        self::assertEquals($targetPath, $this->fileSystem->getLinkTarget($path));
    }

    public function dataProvider_test_createLink(): array
    {
        return [
            ['/a/fileC.ln', '/a/dir/yet_another_dir/fileC.php', null],
            ['/a/fileB.ln', '/a/dir/yet_another_dir/fileC.php', FileSystemException::class],
            ['/a/dir/fileA', '/a/dir/yet_another_dir/fileC.php', FileSystemException::class],
            ['/a/dir', '/a/dir/yet_another_dir/fileC.php', FileSystemException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_test_getLinkTarget
     */
    public function test_getLinkTarget(string $path, string $targetPath, string $expectedException = null)
    {
        $path = $this->getBasePath() . $path;
        $targetPath = $this->getBasePath() . $targetPath;

        if ($expectedException) {
            self::expectException($expectedException);
        }

        self::assertEquals($targetPath, $this->fileSystem->getLinkTarget($path));
    }

    public function dataProvider_test_getLinkTarget(): array
    {
        return [
            ['/a/fileB.ln', '/a/dir/another_dir/fileB', null],
            ['/a/fileC.ln', '', FileNotFoundException::class],
            ['/a/dir/fileA', '', FileNotFoundException::class],
            ['/a/dir', '', FileNotFoundException::class],
        ];
    }

    /**
     * @dataProvider dataProvider_test_getAbsolutePath
     */
    public function test_getAbsolutePath(string $path, string $expectedResult)
    {
        $path = $this->getBasePath() . $path;
        $expectedResult = $this->getBasePath() . $expectedResult;

        self::assertEquals($expectedResult, $this->fileSystem->getAbsolutePath($path));
    }

    public function dataProvider_test_getAbsolutePath(): array
    {
        return [
            ['/a/dir/../fileA', '/a/fileA'],
            ['/a/dir/../../fileA', '/fileA'],
            ['/a/dir/.././fileA', '/a/fileA'],
        ];
    }

    public function test_copy()
    {
        $path = $this->getBasePath() . '/a/';
        $targetPath = $this->getBasePath() . '/b/';

        self::assertTrue($this->fileSystem->copy($path, $targetPath));

        self::assertTrue($this->fileSystem->linkExists($this->getBasePath() . '/b/fileB.ln'));
        self::assertTrue($this->fileSystem->fileExists($this->getBasePath() . '/b/dir/fileA'));
        self::assertTrue($this->fileSystem->dirExists($this->getBasePath() . '/b/dir/'));
        self::assertTrue($this->fileSystem->dirExists($this->getBasePath() . '/b/dir/another_dir/'));
        self::assertTrue($this->fileSystem->fileExists($this->getBasePath() . '/b/dir/another_dir/fileB'));
        self::assertTrue($this->fileSystem->dirExists($this->getBasePath() . '/b/dir/yet_another_dir/'));
        self::assertTrue($this->fileSystem->fileExists($this->getBasePath() . '/b/dir/yet_another_dir/fileC.php'));
        self::assertTrue($this->fileSystem->dirExists($this->getBasePath() . '/b/dir/an_empty_dir/'));
    }

    public function test_copy_EqualOriginAndDestination()
    {
        $path = $this->getBasePath() . '/a/';
        $targetPath = $this->getBasePath() . '/a/';

        self::assertTrue($this->fileSystem->copy($path, $targetPath));
    }

    public function test_copy_DestinationFolderExists()
    {
        $path = $this->getBasePath() . '/a/dir/another_dir';
        $targetPath = $this->getBasePath() . '/b/dir/another_dir';

        $this->fileSystem->writeFile($this->getBasePath() . '/b/dir/another_dir/fileK', 'bla');

        self::assertTrue($this->fileSystem->copy($path, $targetPath));
        self::assertTrue($this->fileSystem->fileExists($this->getBasePath() . '/b/dir/another_dir/fileB'));
        self::assertFalse($this->fileSystem->fileExists($this->getBasePath() . '/b/dir/another_dir/fileK'));
    }
}
