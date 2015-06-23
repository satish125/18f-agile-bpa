# 18f-agile-bpa

[![Dependencies from david-dm.org](https://david-dm.org/CRGTMobile/18f-agile-bpa.svg)](http://david-dm.org/CRGTMobile/18f-agile-bpa)

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
