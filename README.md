<img src="/docs/2 - how we work/process/CRGT-Logo.png" alt="CRGT Logo" width="150px">

# RecallRodent
###### :shipit: https://recallrodent.crgt.com
<i><a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/install/installation.md">Installation Instructions</a></i>


###Who We Are

Guident (acquired by CRGT in December 2012) formed <a target="_blank" href="http://4840west.tumblr.com/post/123463049278/weve-had-a-great-time-building-recallrodent-here">Team 4840West</a> to respond to this RFQ. Team 4840West’s approach to the GSA RFQ has been to create a prototype utilizing <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/1%20-%20who%20we%20are/Agile%20Methodologies.JPG">agile methodologies</a>, work with an experienced, knowledgeable, and co-located team of people, while employing a modern and open source <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/tech-stack.md">technical stack</a>. Our team brings over 45 years of combined experience in securing digital services, as well as over 60 years of combined experience creating high-traffic digital services and mobile and web applications (full story on our blog <a target="_blank" href="http://4840west.tumblr.com/">here</a>)(<a target="_blank" href="https://playbook.cio.gov/#play7">Play 7</a>).

###How We Work

#####Agile Overview

To kick off the project, our team moved into the <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/install/images/War%20Room.JPG">"war room"</a> located within our <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/agile/Agile%20Center%20of%20Excellence.jpg">Agile Center of Excellence</a> and started tracking our sprints first using a physical <a target="_blank" href="http://4840west.tumblr.com/post/121892195853/clare-mark-discussing-testing-tasks">scrum board</a>, and later switching to <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/agile/Asana%20Export.md">Asana</a>. We had twice daily <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/agile/Stand%20Up.JPG">standups</a> and utilized <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/agile/Slack_Screenshot_1.png">Slack</a> for group messaging, while Github acted as our source code version control system from day one (<a target="_blank" href="https://playbook.cio.gov/#play4">Play 4</a>). Our embedded <a target="_blank" href="http://4840west.tumblr.com/post/122366423858/just-so-everyone-knows-d">Product Owner</a> exercised full authority over adding and removing features, and prioritizing the <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/agile/Asana_Screenshot_1.png">Features Backlog</a> based on user feedback and <a target="_blank" href="https://docs.google.com/spreadsheets/d/1fR9LFT3emdt1OUXc6JyiRsLr6M93WME1UdL1R16Gr4s/edit?usp=sharing">team progress</a>. We defined team roles as closely to the pure agile structure as possible (<a target="_blank" href="https://playbook.cio.gov/#play6">Play 6</a>). To give focus and ensure a solid product and timely delivery, Guident/CRGT created a dedicated fund and time charge code for the members of team 4840West. (<a target="_blank" href="https://playbook.cio.gov/#play5">Play 5</a>)

#####Our Process

During Sprint 0, before we started reviewing the data in the openFDA API, we identified a set of goals we wanted to accomplish in order to determine a useful and relevant product idea.  Those goals were:
<ul>
<li>The application should do more than just query and display the openFDA data</li>
<li>The application should remix the data by either <br>
 - Collecting additional related data AND/OR <br>
 - Combining with an API that has related consumer data </li>
<li>The application should be meaningful and useful to a broad consumer audience </li>
<li>The application should fill an unmet user need.</li>
</ul>

With these goals in mind, our <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/Brainstorming%20Product%20Ideas.JPG">brainstorming session</a> began with the team discussing the openFDA data available and two main product types began to emerge:
<ol>
<li>A product that collects a user’s purchases, then alerts them to recalls or adverse events </li>
<li>A mobile application that notifies a user of adverse events or recalls based on their current location, and allows them to file a report (for instance, if they got food poisoning) tied to their current location.  This would crowdsource adverse events and visualize things like salmonella outbreaks in real time. (<a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/Product%20Types.JPG">Product Types</a>)</li>
</ol>

When looking at the first idea in more depth, we knew right away that we didn’t want to force users to manually enter their purchases.  This would create a tedious barrier to entry for them.  We started searching for APIs to import purchases so that they would not have to be manually entered.  We considered popular applications like MyFitnessPal, BigOven, in addition to store-based APIs like those at Walgreens, before we discovered the <a target="_blank" href="http://iamdata.co/">Information Machine API</a>.  According to their website, they “allow your users to passively collect their item level purchase data from the largest merchants in the world.”  

We felt like the second product idea was significant, but a problem emerged through further discussion: if users would report when they got food poisoning, they wouldn’t necessarily know the exact ingredient that caused the sickness.  Considering the impact of this problem, the Product Owner decided that this solution would not offer as much business value as the first.  Matching the Information Machine APIs dataset with that of the openFDA’s API became our major concept.

<br>
<img src="/docs/2 - how we work/process/matching_logic_algorithm.png" alt="Our Matching Algorithm Concept" width="600px"><br>
<i><b>Figure 1. Our Matching Algorithm Concept</b></i>
<br>


Still needing proof of our concept, our UX designer set out immediately to create a <a target="_blank" href="https://shesjulie.typeform.com/to/GIFLnG">User Survey</a> that would find out more about people’s experiences with recalls (<a target="_blank" href="https://playbook.cio.gov/#play1">Play 1</a>).  This survey was sent out to over 100 individuals.  The <a target="_blank" href="https://docs.google.com/spreadsheets/d/1zPlxlUNLPEb91SXJnMjxPRLbYVaD931_kkfAVF8UJz8/edit#gid=0">results</a> confirmed our hypothesis that: (1) users struggle to find a single source for trustworthy information about recalls, and (2) that users only find out about recalls in reactive ways, such as hearing about it on the news or from a friend.  We white-boarded possible implementations of the concept that would move the process of finding out about recalls from a reactive one to a proactive and personalized one (where the user can automatically find out if they have purchased a recalled item) (<a target="_blank" href="https://playbook.cio.gov/#play2">Play 2</a>).  We then created two <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/Epics.JPG">Epics</a> to encapsulate the features that would deliver this functionality. 

<br>
<img src="/docs/3 - what we use/install/images/connectstoresbrainstormingdiagram.jpg" alt="Our Connect Stores Concept" width="600px"><br>
<i><b>Figure 2. Our Process of Connecting Stores</b></i>
<br>

In decomposing the Epics, we wanted to make sure a login feature was included so that user profiles would stay protected.  We created <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/wireframes/Wireframes.pdf">wireframes</a> for our login page, and the first round of Usability Testing demonstrated that not all users necessarily wanted to register for the application without seeing something of value first.  This led to our first pivot in design: we originally intended to display a list of recent recalls on the landing page once the user logged in, but based on user feedback, we decided to display the list of recent recalls directly on the Home page.  This provides something of value for the non-registered user right away, and provides encouragement to register for the site.  Based on the survey results and this usability testing, we were able to utilize the Xtensio tool to finalize detailed and relevant <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/User%20Personas.pdf">User Personas</a>.

<br>
<img src="/docs/3 - what we use/install/images/recentrecallsbrainstormingdiagram.jpg" alt="Our Home Page Concept" width="600px"><br>
<i><b>Figure 3. Our Process of Displaying Recalls</b></i>
<br>

After we created the login and home pages, we were ready to do our next round of <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/usability%20testing/Usability%20Testing%20Round%202.md">Usability Testing</a> with the application itself.  We collected feedback on the home page and the login page functionality.  The major takeaways were that users liked the display of recalls on the home page, but were confused about what the registration process would do. They also pointed out that the login page needed to feature validation error messages for incorrect credentials.  We took the second takeaway as an immediate pivot point, and the first we put into the backlog.

The <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/usability%20testing/Usability%20Testing%20Round%203.md">third round</a> of Usability Testing focused on a user’s ability to register for a new account, and the login page was updated to include error messages for invalid credentials.  The main feedback we received from users was that the login page was user-friendly but the link needed to be more prominent, and that it was still not clear what the application did once the user registered for an account.  As a result, we prioritized both of those stories in the backlog and worked on a total UX redesign of the home page.

The <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/usability%20testing/Usability%20Testing%20Round%204.md">final round</a> of Usability Testing gave users the full range of activities available in the final iteration of the application.  The users easily identified the purpose of the application, indicating the home page redesign was a big success.  The <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/2%20-%20how%20we%20work/process/usability%20testing/Usability%20Testing%20Results.pdf">critiques</a> they had centered on the ease of use on the Connect Stores page, so we prioritized a redesign of that page for the final days of development. 

There were other enhancement ideas both from our team and the usability testers that were included in our Features Backlog (<a target="_blank" href="https://playbook.cio.gov/#play13">Play 13</a>).  Those features included addressing performance monitoring (<a target="_blank" href="https://playbook.cio.gov/#play12">Play 12</a>) and the capability for users to report bugs and issues. 

We shipped our final application before the original RFQ deadline of <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/tree/1.0.0">June 26th</a>, and spent the remaining time of the extension making small tweaks and addressing the defect backlog.


###What We Use

#####Design Environment

Our design was inspired by the modern platforms of government sites such as openFDA and whitehouse.gov.  In order to model those sites, we utilized <a target="_blank" href="https://www.google.com/design/spec/material-design/introduction.html">Google’s Material Design</a> as a style guide throughout our development. We used <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/WAVE%20Tool%20Usage.jpg">WAVE</a> during development to ensure all pages are 508 compliant (<a target="_blank" href="https://playbook.cio.gov/#play3">Play 3</a>). We employed a comprehensive, modern open source <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/tech-stack.md">technical stack</a>  mirroring ones at successful private sector technology companies (<a target="_blank" href="https://playbook.cio.gov/#play8">Play 8</a>). Our service is hosted by Amazon AWS Cloud Services and run by Linux servers to utilize elastic resource provisioning. We crafted the service in a scalable and flexible <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/system_diagram.pdf">model</a> that allows for future capabilities to be easily added (<a target="_blank" href="https://playbook.cio.gov/#play9">Play 9</a>). For Continuous Integration and Deployment, we deployed <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/Jenkins%20build%20and%20deploy%20screenshot.png">Jenkins</a> to automatically build code, run <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/Karma_unit_test_for_Login.png">Karma</a> unit and integration tests, run <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/Sonar%20output%20screenshot.png">Sonar</a> code analysis, and deploy the build (<a target="_blank" href="https://playbook.cio.gov/#play10">Play 10</a>).  

To ensure our product can be thoroughly tested and reviewed, we have created a demo user account <b>(usage instructions <a target="_blank" href="https://github.com/CRGTMobile/18f-agile-bpa/blob/master/docs/3%20-%20what%20we%20use/Site%20Usage%20Instructions%20and%20Demo%20Account%20Information.md">here</a>) </b> that comes pre-loaded with connected store loyalty cards and has matching recalled products in the account.

#####Security

To protect user data, we incorporated secure cookies, TLS encryption, and tunnel all outside API calls through our local services.  We utilize a strong password hashing algorithm with a unique salt to safeguard each user’s password. Purchase and loyalty card data is not held or cached in the application or database server (<a target="_blank" href="https://playbook.cio.gov/#play11">Play 11</a>).

###Conclusion

Inspired by the <a target="_blank" href="http://www.nps.gov/dena/learn/nature/images/as-2_5.jpg">Arctic Ground Squirrel</a> found on the 18F <a target="_blank" href="https://18f.gsa.gov/2015/06/15/agile-bpa-is-here/">blogpost</a> announcing this RFQ, we have named our application “RecallRodent”. It is an application built using agile processes, attends to users and makes us proud.
<br>
<img src="/docs/3 - what we use/install/images/arctic ground squirrel.png" alt="Just Ship It" width="450px">
