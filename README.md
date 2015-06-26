# RecallRodent 

[![Dependency Status](https://www.versioneye.com/user/projects/55899654306662001d00017c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899654306662001d00017c)
[![Dependency Status](https://www.versioneye.com/user/projects/55899725306662001e000242/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55899725306662001e000242)

![Squirrel alt text](/docs/images/Squirrel.jpg)


Guident (acquired by CRGT in December 2012) formed Team 4840West to respond to this RFQ.  Team 4840West’s approach to the GSA RFQ has been to create a prototype utilizing agile methodologies, work with an experienced, knowledgeable team of people, and employ a modern and open source technology stack.  Our team brings over 45 years of combined experience in securing digital services, as well as over 60 years of combined experience creating high-traffic digital services and mobile and web applications (Play 7).

To kick off the project, our team moved into the “war room” located within our Agile Center of Excellence and started tracking our sprints first using a physical scrum board, and later switching to Asana. We had twice daily standups and utilized Slack for group messaging, while Github acted as our source code version control system (Play 4).  Our embedded Product Owner exercised full authority over adding and removing features, and prioritizing the Features Backlog based on user feedback and team progress.  We defined team roles as closely to the pure agile structure as possible (Play 6). To give focus and ensure a solid product and timely delivery CRGT created a dedicated fund and time charge code for the members of team 4840W. (Play5)

During Sprint 0, the team brainstormed ideas to use the data available through the openFDA APIs and decided to focus our product on two Epics related to improving access to recalls. We then created a user survey to gauge people’s experience with recalls (Play 1).  The results from this survey helped us to confirm that our Epics were aligned with user needs.  We then white-boarded the design for an application that shifts the process of finding out about recalls from a passive one (where the user hears about it on the news) to a proactive and personalized one (where the user can find out if they have recently purchased a recalled item) (Play 2).

We then began making wireframes for usability testing to ensure what we were building addressed user needs, and quickly realized we needed to pivot on design.  Our original design idea had us displaying recent recalls only after a user registered and logged in. After receiving feedback from a usability tester who did not necessarily want to register for the site, we moved that functionality to the Home page, though the ability to use the Connecting Stores feature of the application is still requires a secure login.

Based on this and additional usability testing, we were also able to create the application’s User Personas using the Xtensio tool.

Throughout our development process, we utilized a consistent and modern style guide that was modeled on government sites such as openFDA and whitehouse.gov.  We used the WAVE tool during development to ensure all pages are 508 compliant (Play 3).  We employed a comprehensive, modern open source technology stack that mirrors those used by successful private sector technology companies (Play 8).  Our service is hosted by Amazon AWS Cloud Services and run by Linux servers to utilize elastic resource provisioning.  We crafted the service in a scalable and flexible model that allows for future capabilities to be easily added (Play 9). For Continuous Integration and Deployment, we deployed Jenkins to automatically build code, run Karma unit and integration tests, run Sonar code analysis, and deploy the build (Play 10).

To protect user data, we incorporated secure cookies, SSL, and pass all outside API services through our API service to reduce the chance of API key exposure.  Additionally, we chose not to store any product data associated to users in our database – instead, it is held outside of the system and available only through APIs (Play 11).

A third round of usability testing helped us to refine our Home page to better define what users should expect when they register for an account.  We added stories to the backlog to be included in future iterations (Play 13) to address performance monitoring (Play 12) and the capability for users to report bugs and issues.  Finally, we have created a demo user account (click here for details and site usage instructions) that comes pre-loaded with connected store loyalty cards and has matching recalled products in the account.

In keeping with the Arctic Ground Squirrel we researched as a result of the 18F blog announcing this RFQ, we have named our application “RecallRodent”. It is an application built using agile processes and iterative workflows, and one of which we are very proud.









## Prerequisites

* [Node.js](https://nodejs.org)
* LAMP Framework (Linux/Apache/MySQL/Php)

## Install

[View Installation Instructions](/docs/installation.md)

## Commands

* *grunt serve* - runs a localhost server and listens to changes in files. This does not build the app into /dist.
* *grunt test* - runs karma unit tests on angular code
* *grunt build* - builds the app into /dist

## Apache Changes

* Turn on rewrite engine for .htaccess to hide index.php in /api
* Modify /etc/apache2/sites-available/000-default.conf and rewrite directive for SSL redirect
  * Redirect permanent / https://ec2-54-152-245-25.compute-1.amazonaws.com/




