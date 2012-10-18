# AWS Glacier API for PHP 

This API allows you to perform operations on the AWS Glacier Service  which has so far been missing in the AWS PHP SDK. This API can be used to supplement the capabilities for the AWS SDK for PHP.

For more information about the AWS SDK for PHP, including a complete list of supported services, see
[aws.amazon.com/sdkforphp](http://aws.amazon.com/sdkforphp).


## Staying up-to-date!
Follow this repository on github.

### Getting the latest versions
You can get the latest version of the SDK via:

* [GitHub](https://github.com/thenative/native-aws-php)


## Source
The source tree for includes the following files and directories:

* `lib` -- Contains any third-party libraries that the SDK depends on. The licenses for these projects will always be Apache 2.0-compatible.
* `src` -- Contains the source files needed to talk with AWS Services.
* `README` -- The document you're reading right now.


## Minimum Requirements in a nutshell

* You are at least an intermediate-level PHP developer and have a basic understanding of object-oriented PHP.
* You have a valid AWS account, and you've already signed up for the services you want to use.
* The PHP interpreter, version 5.2 or newer. PHP 5.2.17 or 5.3.x is highly recommended for use with the AWS SDK for PHP.
* The cURL PHP extension (compiled with the [OpenSSL](http://openssl.org) libraries for HTTPS support).
* The ability to read from and write to the file system via [file_get_contents()](http://php.net/file_get_contents) and [file_put_contents()](http://php.net/file_put_contents).

## Installation

### Via GitHub

[Git](http://git-scm.com) is an extremely fast, efficient, distributed version control system ideal for the
collaborative development of software. [GitHub](http://github.com/amazonwebservices) is the best way to
collaborate with others. Fork, send pull requests and manage all your public and private git repositories.
We believe that GitHub is the ideal service for working collaboratively with the open source PHP community.

Git is primarily a command-line tool. GitHub provides instructions for installing Git on
[Mac OS X](http://help.github.com/mac-git-installation/), [Windows](http://help.github.com/win-git-installation/),
and [Linux](http://help.github.com/linux-git-installation/). If you're unfamiliar with Git, there are a variety
of resources on the net that will help you learn more:

* [Git Immersion](http://gitimmersion.com) is a guided tour that walks through the fundamentals of Git, inspired
  by the premise that to know a thing is to do it.
* The [PeepCode screencast on Git](https://peepcode.com/products/git) ($12) will teach you how to install and
  use Git. You'll learn how to create a repository, use branches, and work with remote repositories.
* [Git Reference](http://gitref.org) is meant to be a quick reference for learning and remembering the most
  important and commonly used Git commands.
* [Git Ready](http://gitready.com) provides a collection of Git tips and tricks.
* If you want to dig even further, I've [bookmarked other Git references](http://pinboard.in/u:skyzyx/t:git).

If you're comfortable working with Git and/or GitHub, you can pull down the source code as follows:

    git clone git://github.com/thenative/native-aws-php.git
    cd ./native5-aws-php

## Configuration
1. Add in your credentials to the src/Glacier.php file. This will be abstracted to a configuration file shortly

## Additional Information
* License: <http://www.apache.org/licenses/LICENSE-2.0/>
