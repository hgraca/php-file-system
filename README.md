# Hgraca\FileSystem
[![Build Status](https://travis-ci.org/hgraca/php-file-system.svg?branch=master)](https://travis-ci.org/hgraca/php-file-system)
[![codecov](https://codecov.io/gh/hgraca/php-file-system/branch/master/graph/badge.svg)](https://codecov.io/gh/hgraca/php-file-system)


A generic library that wraps file system calls. 
It contains an in-memory file system that behaves the same as the real one, very useful for testing applications that use this little wrapper class.

Please note that this is only useful for small files in a local file system.
 - If you need to handle big files, you should use streams. 
 - If you need to use files from multiple file systems (local, S3, RedShift, ...) I advise using [FlySystem](https://flysystem.thephpleague.com/).

## Installation

To install the library, run the command below and you will get the latest version:

```
composer require hgraca/file-system
```

## Usage

Simply instantiate one of the concrete classes and use.

There are 2 modes:
  - Strict: ie, If we try to delete a folder that does not exist, it throws an exception
  - Idempotent: ie, If we try to delete a folder that does not exist, it DOES NOT throw an exception

## Todo

- Add `shields.io` badges
- Add a CS fixer
- Change structure from inheritance into composition
