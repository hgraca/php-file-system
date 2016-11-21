# File system library

A generic library that wraps file system calls. 
It contains an in-memory file system that behaves the same as the real one, very useful for testing applications that use this little wrapper class.

## Installation

To install the library, run the command below and you will get the latest version:

```
composer require hgraca/file-system
```

## Todo

- Create the tests for `FileSystemAbstract::readDir`
- Create the tests for `FileSystemAbstract::copy`
- Create the tests for `FileSystemAbstract::linkExists`
- Create the tests for `FileSystemAbstract::copyLink`
- Create the tests for `FileSystemAbstract::createLink`
- Create the tests for `FileSystemAbstract::getLinkTarget`
- Create the tests for `FileSystemAbstract::getAbsolutePath`
- Change structure from inheritance into composition
