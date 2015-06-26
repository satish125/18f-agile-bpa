# RecallRodent - Quick Start Installation Guide
[![Dependency Status](https://www.versioneye.com/user/projects/55899654306662001d00017c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899654306662001d00017c)
[![Dependency Status](https://www.versioneye.com/user/projects/55899725306662001e000242/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899725306662001e000242)

![Squirrel alt text](/docs/images/Squirrel.jpg)


## Prerequisites

* [Node.js](https://nodejs.org)
* [Git](http://git-scm.com)
* Apache/MySQL/Php Framework:
  * [WAMP(Windows)] (http://www.wampserver.com/en/)
  * [LAMP(Linux)] (https://www.turnkeylinux.org/lampstack)
  * [MAMP(Mac OS X)] (https://www.mamp.info/en/)
* [MySQL Workbench (Optional)] (https://www.mysql.com/products/workbench/)
* [OpenFDA API Key (requires sign-up)] (http://api.fda.gov)
* [Information Machine API Key (requires sign-up)] (http://iamdata.co)

## Sample Windows Install

[View Detailed Windows Installation Instructions](detailed-windows-installation.md)

## Commands

* *npm install –g grunt-cli yo bower generator-cg-angular* - installs the following technologies to be called from the command line:
  * Grunt javascript task runner
  * Yeoman scaffolding tool
  * Bower package manager
  * AngularJS framework
* *npm install* - installs dependencies in the /node_modules directory
* *bower install* - installs the application libraries
* *grunt serve* - runs a localhost server and listens to changes in files. This does not build the application into /dist.
* *grunt build* - builds the application into /dist directory
* *grunt test* - runs karma unit tests on angular code

## Apache Changes

* Turn on rewrite engine for .htaccess to hide index.php in /api
* Modify /etc/apache2/sites-available/000-default.conf and rewrite directive for SSL redirect
  * Redirect permanent / https://ec2-54-152-245-25.compute-1.amazonaws.com/




