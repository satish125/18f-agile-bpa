# RecallRodent - Installation Guide
[![Dependency Status](https://www.versioneye.com/user/projects/55899654306662001d00017c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899654306662001d00017c)

![Squirrel alt text](/docs/3 - what we use/install/images/arctic-ground-squirrel.jpg)


## Prerequisites

* [Node.js](https://nodejs.org)
* [Git](http://git-scm.com)
* Apache/MySQL/Php Framework:
  * [WAMP(Windows)] (http://www.wampserver.com/en/)
  * [LAMP(Linux)] (https://www.turnkeylinux.org/lampstack)
  * [MAMP(Mac OS X)] (https://www.mamp.info/en/)
* [OpenFDA API Key (requires sign-up for at least one API key)] (http://api.fda.gov)
* [Information Machine API Key (requires sign-up)] (http://iamdata.co)

## Sample Windows Install

[View Detailed Windows Installation Instructions](/docs/3 - what we use/install/detailed-windows-installation.md)

## Commands

* ``git clone https://github.com/CRGTMobile/18f-agile-bpa.git`` - retrieve the RecallRodent source code
* ``npm install -g grunt-cli yo bower generator-cg-angular`` - installs the following technologies to be called from the command line:
  * Grunt javascript task runner
  * Yeoman scaffolding tool
  * Bower package manager
  * [CG-Angular generator](https://github.com/cgross/generator-cg-angular) for Angular projects
* ``npm install`` - installs development dependencies in the /node_modules directory
* ``bower install`` - installs frontend dependencies in the /bower_components directory
* ``grunt serve`` - runs a localhost server and listens to changes in files. This does not build RecallRodent into /dist.
* ``grunt build`` - builds RecallRodent into /dist directory
* ``grunt test`` - runs karma unit tests on angular code

## Apache Changes

* Enable mod_deflate module
* Enable mod_rewrite module
* Configure rewrite directive to redirect all non-HTTPS traffic to the HTTPS page
* Configure SSL Certificate
* Disable SSLv3, only retain TLS protocols
* Configure mod deflate for html, php, js, css and png files in the SSL virtual host



