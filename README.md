<img src="/docs/2 - how we work/process/CRGT-Logo.png" alt="CRGT Logo" width="150px">

# RecallRodent
######https://recallrodent.crgt.com

###Who We Are

Guident (acquired by CRGT in December 2012) formed [Team 4840West] (/docs/1 - who we are/Team Story Document.pdf) to respond to this RFQ. Team 4840West’s approach to the GSA RFQ has been to create a prototype utilizing [agile methodologies] (/docs/1 - who we are/Agile Methodologies.JPG), work with an experienced, knowledgeable, and co-located [team of people] (http://4840west.tumblr.com/post/121944264253/team-4840west), while employing a modern and open source [technical stack] (/docs/3 - what we use/tech-stack.md). Our team brings over 45 years of combined experience in securing digital services, as well as over 60 years of combined experience creating high-traffic digital services and mobile and web applications (full story on our blog [here] (http://4840west.tumblr.com/))([Play 7] (https://playbook.cio.gov/#play7)).

###How We Work

#####Agile Overview

To kick off the project, our team moved into the [“war room”] (http://4840west.tumblr.com/post/121892117793/the-team-digging-in-on-our-first-full-day-on-the) located within our [Agile Center of Excellence] (/docs/2 - how we work/agile/Agile Center of Excellence.jpg) and started tracking our sprints first using a physical [scrum board] (http://4840west.tumblr.com/post/121892195853/clare-mark-discussing-testing-tasks), and later switching to [Asana] (/docs/2 - how we work/agile/Asana Agile Tool Export_2015-07-02.json). We had twice daily [standups] (/docs/2 - how we work/agile/Stand Up.JPG) and utilized [Slack] (/docs/2 - how we work/agile/Slack_Screenshot_1.png) for group messaging, while [Github] (https://github.com/CRGTMobile/18f-agile-bpa) acted as our source code version control system ([Play 4] (https://playbook.cio.gov/#play4)) from day one. Our embedded [Product Owner] (http://4840west.tumblr.com/post/122366423858/just-so-everyone-knows-d) exercised full authority over adding and removing features, and prioritizing the [Features Backlog] (/docs/2 - how we work/agile/Asana_Screenshot_1.png) based on user feedback and [team progress] (/docs/2 - how we work/agile/GSA_Agile_BPA_Acceptance_Test_Results.pdf). We defined team roles as closely to the pure agile structure as possible ([Play 6] (https://playbook.cio.gov/#play6)). To give focus and ensure a solid product and timely delivery, Guident/CRGT created a dedicated fund and time charge code for the members of team 4840West. ([Play 5] (https://playbook.cio.gov/#play5))

#####Our Process

During Sprint 0, before we started reviewing the data in the openFDA API, we identified a set of goals we wanted to accomplish in order to determine a useful and relevant product idea.  Those goals were:
•	The application should do more than just query and display the openFDA data
•	The application should remix the data by either
o	Collecting additional related data AND/OR
o	Combining with an API that has related consumer data
•	The application should be meaningful and useful to a broad consumer audience
•	The application should fill an unmet user need.

With these goals in mind, our [brainstorming session] (/docs/2 - how we work/process/Brainstorming Product Ideas.JPG) began with the team discussing the openFDA data available and two main [product types] (/docs/2 - how we work/process/Product Types.JPG) began to emerge:
1.	A product that collects a user’s purchases, then alerts them to recalls or adverse events
2.	A mobile application that notifies a user of adverse events or recalls based on their current location, and allows them to file a report (for instance, if they got food poisoning) tied to their current location.  This would crowdsource adverse events and visualize things like salmonella outbreaks in real time.

When looking at the first idea in more depth, we knew right away that we didn’t want to force users to manually enter their purchases.  This would create a tedious barrier to entry for them.  We started searching for APIs to import purchases so that they would not have to be manually entered.  We considered popular applications like MyFitnessPal, BigOven, in addition to store-based APIs like those at Walgreens, before we discovered the [Information Machine API] (http://iamdata.co/).  According to their website, they “allow your users to passively collect their item level purchase data from the largest merchants in the world.”  

We felt like the second product idea was very current, but a problem emerged through further discussion: if users would report when they got food poisoning, they wouldn’t necessarily know the exact ingredient that caused the sickness.  Considering the impact of this problem, the Product Owner decided that this solution would not offer as much business value as the first.  [Matching] (/docs/2 - how we work/process/matching_algorithm_concept.pdf) the Information Machine APIs dataset with that of the openFDA’s API became our major concept.

Still needing proof of our concept, our UX designer set out immediately to create a [User Survey] (https://shesjulie.typeform.com/to/GIFLnG) that would find out more about people’s experiences with recalls ([Play 1] (https://playbook.cio.gov/#play1)).  This survey was sent out to over 100 individuals.  The [results] (/docs/2 - how we work/process/usability testing/Usability Testing Results.pdf) confirmed our hypothesis that: (1) users struggle to find a single source for trustworthy information about recalls, and (2) that users only find out about recalls in reactive ways, such as hearing about it on the news or from a friend.  We white-boarded possible implementations of the concept that would move the process of finding out about recalls from a reactive one to a proactive and personalized one (where the user can automatically find out if they have purchased a recalled item) ([Play 2] (https://playbook.cio.gov/#play2)).  We then created two Epics to encapsulate the features that would deliver this functionality. 

In decomposing the [Epics] (/docs/2 - how we work/process/Epics.JPG), we wanted to make sure a login feature was included so that user profiles would stay protected.  We created [wireframes] (/docs/2 - how we work/process/wireframes/Wireframes.pdf) for our login page, and the first round of [Usability Testing] (/docs/2 - how we work/process/usability testing) demonstrated that not all users necessarily wanted to register for the application without seeing something of value first.  This led to our first pivot in design: we originally intended to display a list of recent recalls on the landing page once the user logged in, but based on user feedback, we decided to display the list of recent recalls directly on the Home page.  This provides something of value for the non-registered user right away, and provides encouragement to register for the site.  Based on the survey results and this usability testing, we were able to utilize the Xtensio tool to finalize detailed and relevant [User Personas] (/docs/2 - how we work/process/User Personas.pdf).

After we created the login and home pages, we were ready to do our next round of Usability Testing with the application itself.  We had people give feedback on the home page and the login page functionality.  The major takeaways were that users liked the display of recalls on the home page, but were confused about what the registration process would do. They also pointed out that the login page needed to feature validation error messages for incorrect credentials.  We took the second takeaway as an immediate pivot point, and the first we put into the backlog.

The [third round] () of Usability Testing focused on a user’s ability to register for a new account, and the login page was updated to include error messages for invalid credentials.  The main feedback we received from users was that the login page was user-friendly but the link needed to be more prominent, and that it was still not clear what the application did once the user registered for an account.  As a result, we prioritized both of those stories in the backlog and worked on a total UX redesign of the home page.

The [final round] (/docs/2 - how we work/process/usability testing/Usability Testing Round 4.md) of Usability Testing gave users the full range of activities available in the final iteration of the application.  The users easily identified the purpose of the application, indicating the home page redesign was a big success.  The critiques they had centered on the ease of use on the Connect Stores page, so we prioritized a redesign of that page for the final days of development. 

There were many other enhancement ideas that both our team and the usability testers proposed that were unable to make it to the final iteration of our application, but we included those ideas in our Features Backlog ([Play 13] (https://playbook.cio.gov/#play13)).  Some of those features included addressing performance monitoring ([Play 12] (https://playbook.cio.gov/#play12)) and the capability for users to report bugs and issues.  

###What We Use

#####Design Environment

Our design was inspired by the modern platforms of government sites such as openFDA and whitehouse.gov.  In order to model those sites, we utilized [Google’s Material Design] (https://www.google.com/design/spec/material-design/introduction.html) as a style guide throughout our development. We used the [WAVE tool] (/docs/3 - what we use/WAVE Tool Usage.jpg) during development to ensure all pages are 508 compliant ([Play 3] (https://playbook.cio.gov/#play3)). We employed a comprehensive, modern open source [technical stack] (/docs/3 - what we use/tech-stack.md) that mirrors those used by successful private sector technology companies ([Play 8] (https://playbook.cio.gov/#play8)). Our service is hosted by Amazon AWS Cloud Services and run by Linux servers to utilize elastic resource provisioning. We crafted the service in a scalable and flexible [model] (/docs/3 - what we use/system_diagram.pdf) that allows for future capabilities to be easily added ([Play 9] (https://playbook.cio.gov/#play9)). For Continuous Integration and Deployment, we deployed [Jenkins] (/docs/3 - what we use/Jenkins build and deploy screenshot.png) to automatically build code, run [Karma] (/docs/3 - what we use/Karma_unit_test_for_Login.png) unit and integration tests, run [Sonar] () code analysis, and deploy the build ([Play 10] (https://playbook.cio.gov/#play10)).  To ensure our product can be thoroughly tested and reviewed, we have created a demo user account (usage instructions [here] (/docs/3 - what we use/Usage Instructions.pdf)) that comes pre-loaded with connected store loyalty cards and has matching recalled products in the account.

#####Security

To protect user data, we incorporated secure cookies, TLS encryption, and tunnel all outside API calls through our local services.  This ensures the protection of user identities and API product keys.  We utilize a strong password hashing algorithm with a unique salt to safeguard each user’s password. Purchase and loyalty card data is not held or cached in the application or database server ([Play 11] (https://playbook.cio.gov/#play11)).

###Conclusion

Inspired by the [Arctic Ground Squirrel] (http://www.nps.gov/dena/learn/nature/images/as-2_5.jpg) found on the 18F [blogpost] (https://18f.gsa.gov/2015/06/15/agile-bpa-is-here/) announcing this RFQ, we have named our application “RecallRodent”. It is an application built using agile processes and iterative workflows - one of which we are very proud.

<img src="/docs/3 - what we use/install/images/arctic ground squirrel.png" alt="Just Ship It" width="450px">
