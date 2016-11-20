# File system library

A generic library that wraps file system calls. 
It contains an in-memory file system that behaves the same as the real one, very useful for testing applications that use this little wrapper class.

## Installation

To install the library, run the command below and you will get the latest version:

```
composer require hgraca/file-system
```

## Todo

- Implement `FileSystemAbstract::dirname` to match `dirname`
- Create the tests for `LocalFileSystem::copy`
- Implement `InMemoryFileSystem::copy`
- Create the tests for `InMemoryFileSystem::copy`
- Implement `InMemoryFileSystem::linkExists`
- Create the tests for `InMemoryFileSystem::linkExists`
- Create the tests for `FileSystemAbstract::getAbsolutePath`
