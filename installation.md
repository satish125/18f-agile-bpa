# RecallRodent Installation

![Squirrel alt text](/docs/images/Squirrel.jpg)

## Prerequisites

* [Node.js](https://nodejs.org)
* [Git](http://git-scm.com)
* [WAMP Framework (Windows/Apache/MySQL/Php)] (http://www.wampserver.com/en/)
* [MySQL Workbench (Optional)] (https://www.mysql.com/products/workbench/)
* [OpenFDA API Key (requires sign-up)] (http://api.fda.gov)
* [Information Machine API Key (requires sign-up)] (http://iamdata.co)

## Install WAMP 

### 1.	Download WAMP executable application from http://www.wampserver.com/en/. NOTE: Be sure to download the correct version for the system (i.e., 32bit or 64bit).
### 2.	Open the folder where the WAMP executable was saved and run it as administrator.
![WAMP alt text](/docs/images/wamp_run.jpg)
 

### 3.	Click Next on the Setup dialog box.
![WAMP alt text](/docs/images/wamp_wizard_1.jpg)
 

### 4.	Select “I accept the agreement” and click Next.
![WAMP alt text](/docs/images/wamp_wizard_2.jpg)
 

### 5.	At prompt for where to install WAMP, use: c:\wamp and click Next.
![WAMP alt text](/docs/images/wamp_wizard_3.jpg)
 

### 6.	Choose an option for installing a Quick Launch icon and/or a Desktop icon for the shortcut.
![WAMP alt text](/docs/images/wamp_wizard_4.jpg)
 

### 7.	Next, use SMTP as localhost and type your  email address in the Email field.
![WAMP alt text](/docs/images/wamp_wizard_5.jpg)
 

### 8.	Select the Launch WampServer now checkbox and click Finish.
![WAMP alt text](/docs/images/wamp_wizard_6.jpg)
 

### 9.	The application launches and the icon in the system tray should display.
![WAMP alt text](/docs/images/wamp_systray_icon.jpg)

 
## Configure SSL

### 1. Obtain SSL Certificate and Key

The follow steps were copied from the website http://forum.wampserver.com/read.php?2,32986 with a couple of steps added at the end to handle some issues that were encountered after implementing them. It is recommended that this section be viewed as a guide and that the aforementioned website be referenced for any updates.

### 2. Copy the server.key and server.crt files:

#### 2a) In the conf folder of apache2.4.9 folder, create two folders named as ssl.key and ssl.crt

#### 2b) copy the server.key file to ssl.key folder and server.crt file to ssl.crt

### 3. Edit the httpd.conf and php.ini files:
#### 3a) In httpd.conf file, remove the comment '#' at the line which says: LoadModule ssl_module modules/mod_ssl.so
#### 3b) In httpd.conf, remove the comment '#' at the line which says: 
#### 	 Include conf/extra/httpd_ssl.conf
#### Then move that line after this block <IfModule ssl_module>.... </IfModule>

#### 3c) Open the php.ini file located in apache2.4.9\bin folder, remove the comment ';' at the line which says: 
#### 	 extension=php_openssl.dll

### 4. Edit the httpd_ssl.conf file in the folder name, “extra” within the conf folder:

#### 4a) Find the line which says "SSLMutex ...." and change it to "SSLMutex default" without quotes.
#### NOTE: If SSLMutex is not present in the file, disregard this step.

#### 4b) Find the line which says: <VirtualHost _default_:443>. Right after it, change the line which says "DocumentRoot ..." to DocumentRoot "C:/wamp/www/" with quotes. Change the line ErrorLog "c:/Apache24/logs/error.log" to Errorlog “logs/sslerror_log”. Change the line TransferLog "c:/Apache24/logs/access.log" to TransferLog logs/sslaccess_log

#### 4c) SSL crt file: Change the line SSLCertificateFile "c:/Apache24/conf/server.crt" to SSLCertificateFile "conf/ssl.crt/server.crt"

#### 4d) SSL key file: Change the line SSLCertificateKeyFile "c:/Apache24/conf/server.key" to SSLCertificateKeyFile "conf/ssl.key/server.key"

#### 4e) Change the line which says <Directory "C:/Program Files/Apache Software Foundation/Apache2.4/cgi-bin"> or something similar to <Directory "C:/wamp/www/"> and add the following lines inside those <Directory ... >...</Directory> tags:

#### 	 Options Indexes FollowSymLinks MultiViews
#### 	 AllowOverride All
#### 	 Order allow,deny
#### 	 Allow from all

#### 4f) Make sure the line which says:
#### 	 CustomLog "logs/ssl_request_log" \
#### 	 is uncommented (remove the #). 

### 5. Validate the Changes 
#### In the previous DOS Command windows, enter httpd -t . If it displays “Syntax is OK”, then go to Step ### 6. If not, then correct the wrong syntax and redo step ### 5.

### 6. Restart the Apache server

### 7. If restart is successful, then open the browser and enter "localhost" (without quotes).

##Additional Steps (Not included in http://forum.wampserver.com/read.php?2,32986)
### 1.	If the error depicted below occurs during Step 5 above, uncomment the line: LoadModule socache_shmcb_module modules/mod_socache_memcache.so in httpd.conf.
![WAMP alt text](/docs/images/wamp_syntax_error.jpg)
 
### 2.	It is also recommended that the webserver redirects any HTTP requests to HTTPS or that port 80 is disabled to prevent unsecured access.

## MySQL Workbench (Optional)
### The WAMP installation includes the MySQL console but MySQL Workbench is a user-friendly application that aids in development. Use the following steps to install the application if preferred over using the MySQL console.
### 1.	Download the application from https://www.mysql.com/products/workbench/.
### 2.	From the download folder, right-click on the installer application and select Install from the pop-up menu.
![MySQL alt text](/docs/images/mysql_install.jpg)
 
### 3.	Click Next when the MySQL Workbench Setup Wizard dialog box displays.
![MySQL alt text](/docs/images/mysql_wizard_1.jpg)
 
### 4.	Choose the installation destination folder
#### Click Next to install the application in the defaulted destination folder or click Change to browse to a different folder for installation and click Next when selected.
![MySQL alt text](/docs/images/mysql_wizard_2.jpg)
 
### 5.	Choose the Setup Type and click Next.
![MySQL alt text](/docs/images/mysql_wizard_3.jpg)
 
### 6.	Click Next on the “Ready to Install the Program” dialog box.
![MySQL alt text](/docs/images/mysql_wizard_4.jpg)
 
### 7.	Choose “Launch MySQL Workbench now” and click Finish on the “Wizard Completed” dialog box.
![MySQL alt text](/docs/images/mysql_wizard_5.jpg)
 
### 8.	The MySQL Workbench application interface will display.
![MySQL alt text](/docs/images/mysql_workbench.jpg)
 

## Install Git and Node.js
### 1.	Follow installation instructions on http://git-scm.com to install Git.
### 2.	Follow installation instructions on http://nodejs.org to install Node.js.

## Retrieve RecallRodent Source Code
### 1.	Sign into https://github.com/ and link to 18f-agile-bpa.
### 2.	Click the “Clone to Desktop” button to download the GitHub Windows application and follow the wizard instructions to install it. Optionally, navigate to https://windows.github.com/ and download GitHub for Windows.
### 3.	Open Windows Explorer and create a directory called “github”.
### 4.	Run the Command Prompt application as administrator and change the directory to the github directory created in step 3 above.
### 5.	Type “git clone https://github.com/CRGTMobile/18f-agile-bpa.git” (without quotes) on the command line and click the Enter key.
### 6.	Open the GitHub application downloaded in Step 2 above, click the button with the plus sign (“+”), and click the Clone option.
![MySQL alt text](/docs/images/github_clone_dialog.jpg)
 
### 7.	In the “Browse For Folder” dialog box, select the folder for the repository cloned in Step 5 above “c:\[..]\github\18f-agile-bpa” (where “[..]” is the path starting after the C drive). The repository will display.
![MySQL alt text](/docs/images/github_application_gui.jpg)
 
## Configure the Source Code on Localhost
### 1.	Run the Command Prompt application as administrator and the change directory to the 18f-agile-bpa directory (“cd 18f-agile-bpa”) where the source was downloaded.
### 2.	Type “npm install –g grunt-cli yo bower generator-cg-angular” (without quotes) and click Enter key.
![MySQL alt text](/docs/images/npm_install_1.jpg)
 

### 3.	Confirm the message “Everything looks all right!” followed by a configuration listing.
![MySQL alt text](/docs/images/npm_install_2.jpg)
 

### 4.	Type “npm install” (without quotes) and click the Enter key. NOTE: there may be warnings about deprecated objects but these are resolved by Node.js. 
![MySQL alt text](/docs/images/npm_install_3.jpg)
 
### 5.	Install Bower
Type “bower install” (without quotes) and answer the prompt “May bower anonymously report usage statistics to improve the tool over time? (y/n)” by either typing “Y” or “N” (without quotes) and clicking the Enter key.
![MySQL alt text](/docs/images/bower_install.jpg)
 
### 6.	When the Bower listing is completed, type “grunt build”, click the Enter key and confirm the message “Done, without errors.” displays.
![MySQL alt text](/docs/images/grunt_build.jpg)

### 7.	When the build messages complete, type "grunt test", click the Enter key and confirm the message "Done, without errors." 
![MySQL alt text](/docs/images/grunt_test.jpg)

## Create the Database Objects
### 1.	Left-click on the WAMP server icon in the system tray and select the MySQL > MySQL Console menu item.
![MySQL alt text](/docs/images/mysql_console_run.jpg)
 
### 2.	At the Enter password prompt, don’t type anything and just click the Enter key. NOTE: if the root user of the MySQL database was previously configured with a password, then that password should be entered at this prompt. This instruction is relevant for a new installation of MySQL that hasn’t had the root user set up with a password.
### 3.	Using a text editor, open the file at C:\[..]\18f-agile-bpa\server\sql\insert_table_iamdata_properties.sql (where “[..]” is the path to the local github directory) and modify the insert values statement to change the client_id and client_key from '#####xxx', 'xxx#x#x##xx##xxxxxx#x######x#x##' respectively to the API license information granted from signing up at http://iamdata.co.
### 4.	Using a text editor, open the file at and modify the insert values statement to change the api_key from 'xxxxX#xxX#xXXxx#xX#XXX#xxXXXXXx#XxXX###x' to the API key granted from signing up at http://api.fda.gov.
### 3.	Using a text editor, open the file at C:\[..]\18f-agile-bpa\server\sql\run_mysql_scripts.sql (where “[..]” is the path to the local github directory) and confirm the directory path is correct for all the script files listed.
### 4.	Type “source c:/[..]/18f-agile-bpa/server/sql/run_mysql_scripts.sql;”, where “[..]” is the path to the local github directory, (without quotes) at the prompt in the MySQL console and click the Enter key. The console will display the statuses of all the changes.
![MySQL alt text](/docs/images/mysql_script_run.jpg)
 
## Copy the Built Website Files to the WWW Directory
### 1.	Open two Windows Explorer windows: one to view the contents of \18f-agile-bpa\server and the other view the contents of C:\wamp\www
### 2.	Select the folder named “api” from the \18f-agile-bpa\server directory and copy it to C:\wamp\www.
### 3.	In the \18f-agile-bpa\server window, go up one directory to \18f-agile-bpa, select the folder named “dist”, and copy it to C:\wamp\www.

## Restart the WAMP Server
### 1.	Click on the system tray arrow to display the application shortcuts and left-click the WAMP server icon.
### 2.	From the pop-up menu, select “Restart All Services”.
![MySQL alt text](/docs/images/wamp_server_restart.jpg)
 
### 3.	Open the browser, refresh the page with "localhost" as the URL (without quotes), and confirm the RecallRodent application home page displays.
![MySQL alt text](/docs/images/recallrodent_home.jpg)
 
