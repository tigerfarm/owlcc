# Owl Contact Center Version 2

<img src="custom/CallCenterFlow.jpg"/>

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/tigerfarm/owlcc)

When you deploy to Heroku, you will be prompted for an app name. The name needs to be unique,
example, enter your name+cc (example: davidcc). Click Deploy app. Once the application is deployed, click Manage app.
Set Heroku project environment variables by clicking Settings.
Click Reveal Config Vars. Add the following key value pairs:
```
ACCOUNT_SID=your_account_SID
AUTH_TOKEN=your_account_auth_token
WORKSPACE_SID=your_TaskRouter_workspace_SID
```

## Files

README.md : this file

Agent desktop website:
- [index.html](index.html) : Home page
- [agent_list.php](agent_list.php) : List TaskRouter worker agents and their status.
- [task_list.php](task_list.php) : List active worker tasks and their status. This page also resets a task to completed
if it gets stuck in status: wrapping. If a task gets stuck in wrapping, the agent can not be assigned a new task. 
- [agent_desktop.php](agent_desktop.php) : Agent desktop to manage their status and receive calls.

The [custom](custom) directory is for website branding files:
- [favicon.ico](favicon.ico) : Page icon
- [pageTop.js](pageTop.js) : Top of the page for logo and application title
- [companyLogo.jpg](companyLogo.jpg) : Top of the page logo
- [pageBottom.js](pageBottom.js) : Bottom of the page
- [Logo.jpg](Logo.jpg) : Bottom of the page logo
- [agentapp.css](agentapp.css) : HTML page styles
- [CallCenterFlow.jpg](CallCenterFlow.jpg) : Optional home page graphic

[Twilio PHP Helper Library](https://www.twilio.com/docs/libraries/php) directory:
- twilio-php-master : Downloaded and unzipped into this directory. This is the version without Composer.

For a localhost setup, creating environment variables and testing the setup:
- [setvars.sh](setvars.sh) : Set the environment variables.
- [echoVars.php](echoVars.php) : Echo the environment variables. Test the Twilio Helper Library installation and the TaskRouter configurations.

Heroku Hosting Service configurations
- [app.json](app.json) : Heroku deployment file to describe the application when it is being deployed.
- [composer.json](composer.json) : Heroku deployment file which sets the programming language used.

The [util](util) directory has sample Node.js programs, examples:
- [ccActivities.js](ccActivities.js) : List worker activities
- [ccTaskList.js](ccTaskList.js) : List tasks

## Setup Steps to Run on your Local Host.

Download this repository's zip file and unzip it into a work directory.
Example work directory:
```
/Projects/OwlCc
```

Note, the Twilio PHP helper library is included in the subdirectory:
```
/Projects/OwlCc/twilio-php-master
```

In the project directory, edit setvars.sh and add your values.
```
ACCOUNT_SID=your_account_SID
AUTH_TOKEN=your_account_auth_token
WORKSPACE_SID=your_TaskRouter_workspace_SID
```

Set your terminal session's environment variables.
```
$ source ./setvars.sh
+++ Set variables.
+ Variables set.
---------------------------------------
+++ Echo environment variables and test the environment.
+ ACCOUNT_SID   : your_account_SID
+ AUTH_TOKEN    : your_account_auth_token
+ Test the loading and using of the Twilio PHP helper library.
+ Twilio PHP Helper Library Client is working.
---------------------------------------
+ Worker SID for testing the generation of tokens: your_TaskRouter_worker_SID
+ Worker Client token created.
+ WORKSPACE_SID : your_TaskRouter_workspace_SID
+ Worker token created.
--------------------------------------- 
```

Also, in echoVars.php, workerSid to one of your TaskRouter workers.
```
$workerSid = "WK10ec1823ae8a54d715ba424599ea473f";
```

Run the PHP HTTP server using port 8000.
```
$ php -S localhost:8000
```

Test that it works. In your browser, goto: http://localhost:8000.
The Owl CC home pages is displayed.

In another terminal window, run Ngrok to allow notifications of incoming agent calls.
```
$ /Users/dthurston/Applications/ngrok http 8000
...
```

Connect as an agent by going to the Ngrok URL, example:
```
http://706bf85f.ngrok.io/agent_list
```

## Updates to make

- Desktop: Set TTL for the token using an environment variable.
- Agent List: enter a password to pass to the Desktop to authorize token generation.
-- Access password is an environment variable.
- Desktop: When putting an agent status to offline when clicking "Return", should do checks. 
- When Agent clicks Go Available, need to insure that there are no Tasks with the agent's name that are Assignment Status: wrapping.
