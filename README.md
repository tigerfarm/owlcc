# Owl Contact Center Version 2

<img src="CallCenterFlow.jpg"/>

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

Agent desktop website:
- [index.html](index.html) : Home page
- [agent_list.php](agent_list.php) : List TaskRouter worker agents and their status.
- [agent_desktop.css](agent_desktop.css) : Agent desktop to manage their status and receive calls.

For website branding:
- [companyLogo.jpg](companyLogo.jpg)
- [pageTop.js](pageTop.js) : Top of the page for logo and application title
- [pageBottom.js](pageBottom.js) : Bottom of the page
- [agentapp.css](agentapp.css) : HTML page styles
- [favicon.ico](favicon.ico)

[Twilio PHP Helper Library](https://www.twilio.com/docs/libraries/php) directory:
- twilio-php-master : Downloaded and unzipped into this directory. This is the version without Composer.

For a localhost setup, creating environment variables and testing the setup:
- [setvars.sh](setvars.sh) : Set the environment variables.
- [echoVars.php](echoVars.php) : Echo the environment variables. Test the Twilio Helper Library installation and the TaskRouter configurations.

README.md : this file

Heroku Hosting Service configurations
- [app.json](app.json) : Heroku deployment file to describe the application when it is being deployed.
- [composer.json](composer.json) : Heroku deployment file which sets the programming language used.

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

- Desktop: Put agent status to offline when clicking "Return."
- Desktop: Set TTL for the token using an environment variable.
- Agent List: enter a password to pass to the Desktop to authorize token generation.
-- Access password is an environment variable.

- When Agent clicks Go Available, need to insure that there are no Tasks with the agent's name that are Assignment Status: wrapping.
https://www.twilio.com/docs/taskrouter/api/tasks (assignement status)
https://www.twilio.com/docs/taskrouter/lifecycle-task-state

curl –X GET https://taskrouter.twilio.com/v1/Workspaces/$WORKSPACE_SID/Tasks \
--data-urlencode AssignmentStatus='wrapping' \
-u $ACCOUNT_SID:$AUTH_TOKEN

curl -X GET 'https://taskrouter.twilio.com/v1/Workspaces/WSc1241db79a87910a35f3ded642a7fcdc/Tasks' \
-u $ACCOUNT_SID:$AUTH_TOKEN

body:  [ { workspace_sid: 'WSc1241db79a87910a35f3ded642a7fcdc',
    assignment_status: 'wrapping',
    date_updated: '2018-06-18T22:16:17Z',
    age: 7183,
    sid: 'WTa9d450154c108974243a91c4a593cb4a',
    account_sid: 'ACe2ad81d6a0c41fc0e9eeeb5d19f10f63',
    priority: 0,
    url: 'https://taskrouter.twilio.com/v1/Workspaces/WSc1241db79a87910a35f3ded642a7fcdc/Tasks/WTa9d450154c108974243a91c4a593cb4a',
    reason: 'Conference ended',
    task_queue_sid: 'WQ0d6fbd79a768f18f4e68c2d3d1f6268c',
    workflow_friendly_name: 'support Workflow',
    timeout: 86400,
    attributes: '{"from_country":"US","conference":{"sid":"CFd47882288be44d0bf32d9af28fc9c68f","participants":{"worker":"CA4e5589ee8ac63cd62a3527d48a96273f","customer":"CA0e527ad2c844f70ee2b72813a1a23ec5"}},"called":"+16503790007","to_country":"US","to_city":"","selected_product":"support","to_state":"CA","caller_country":"US","call_sid":"CA0e527ad2c844f70ee2b72813a1a23ec5","account_sid":"ACe2ad81d6a0c41fc0e9eeeb5d19f10f63","from_zip":"94030","from":"+16508668882","direction":"inbound","called_zip":"","caller_state":"CA","to_zip":"","called_country":"US","from_city":"SAN BRUNO","called_city":"","caller_zip":"94030","api_version":"2010-04-01","called_state":"CA","from_state":"CA","caller":"+16508668882","caller_city":"SAN BRUNO","to":"+16503790007"}',
    date_created: '2018-06-18T22:16:06Z',
    task_channel_sid: 'TC3d8d950f518f3cdc19ff9266ebaec9b5',
    addons: '{}',
    task_channel_unique_name: 'voice',
    workflow_sid: 'WW999952bd6c48b9159592ea2c291b7e42',
    task_queue_friendly_name: 'Support Queue',
    links: 
     { reservations: 'https://taskrouter.twilio.com/v1/Workspaces/WSc1241db79a87910a35f3ded642a7fcdc/Tasks/WTa9d450154c108974243a91c4a593cb4a/Reservations',
       task_queue: 'https://taskrouter.twilio.com/v1/Workspaces/WSc1241db79a87910a35f3ded642a7fcdc/TaskQueues/WQ0d6fbd79a768f18f4e68c2d3d1f6268c',
       workspace: 'https://taskrouter.twilio.com/v1/Workspaces/WSc1241db79a87910a35f3ded642a7fcdc',
       workflow: 'https://taskrouter.twilio.com/v1/Workspaces/WSc1241db79a87910a35f3ded642a7fcdc/Workflows/WW999952bd6c48b9159592ea2c291b7e42' } } ]
