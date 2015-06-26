# RecallRodent
[![Dependency Status](https://www.versioneye.com/user/projects/55899654306662001d00017c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899654306662001d00017c)
[![Dependency Status](https://www.versioneye.com/user/projects/55899725306662001e000242/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899725306662001e000242)

![Squirrel alt text](/docs/images/Squirrel.jpg)




## Prerequisites

* [Node.js](https://nodejs.org)
* LAMP Framework (Linux/Apache/MySQL/Php)

## Install

[View Installation Instructions](installation.md)

## Commands

* *grunt serve* - runs a localhost server and listens to changes in files. This does not build the app into /dist.
* *grunt test* - runs karma unit tests on angular code
* *grunt build* - builds the app into /dist

## Apache Changes

* Turn on rewrite engine for .htaccess to hide index.php in /api
* Modify /etc/apache2/sites-available/000-default.conf and rewrite directive for SSL redirect
  * Redirect permanent / https://ec2-54-152-245-25.compute-1.amazonaws.com/




