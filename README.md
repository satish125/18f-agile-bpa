# 18f-agile-bpa
[![Dependency Status](https://www.versioneye.com/user/projects/55899654306662001d00017c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899654306662001d00017c)
[![Dependency Status](https://www.versioneye.com/user/projects/55899725306662001e000242/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899725306662001e000242)

## Prerequisites

* [Node.js](https://nodejs.org)
* LAMP Framework (Linux/Apache/MySQL/Php)

## Install

cd into the /web folder and run the following commands:

	npm install -g grunt-cli yo bower generator-cg-angular
	npm install

If you get any errors during the npm install, run the command again and it should be fine.

	bower install

To test that everything is working, run

	grunt build
	grunt test

## Commands

* *grunt serve* - runs a localhost server and listens to changes in files. This does not build the app into /dist.
* *grunt test* - runs karma unit tests on angular code
* *grunt build* - builds the app into /dist

## Apache Changes

* Turn on rewrite engine for .htaccess to hide index.php in /api
* Modify /etc/apache2/sites-available/000-default.conf and rewrite directive for SSL redirect
  * Redirect permanent / https://ec2-54-152-245-25.compute-1.amazonaws.com/
