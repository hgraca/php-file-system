# Hgraca\FileSystem
[![Author](http://img.shields.io/badge/author-@hgraca-blue.svg?style=flat-square)](https://www.herbertograca.com)
[![Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/github/release/hgraca/php-file-system.svg?style=flat-square)](https://github.com/hgraca/php-file-system/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/hgraca/file-system.svg?style=flat-square)](https://packagist.org/packages/hgraca/file-system)

[![Build Status](https://img.shields.io/scrutinizer/build/g/hgraca/php-file-system.svg?style=flat-square)](https://scrutinizer-ci.com/g/hgraca/php-file-system/build)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/hgraca/php-file-system.svg?style=flat-square)](https://scrutinizer-ci.com/g/hgraca/php-file-system/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/hgraca/php-file-system.svg?style=flat-square)](https://scrutinizer-ci.com/g/hgraca/php-file-system)

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
