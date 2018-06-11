# Owl Contact Center Version 2

## Files

Heroku Hosting Service
- [app.json](app.json) : Heroku deployment file to describe the application.
- [composer.json](composer.json) : Heroku deployment file which sets the programming language used.

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/tigerfarm/owlcc)

## Setup Steps to Run on your Local Host.

Download this project's zip file and unzip it into a work directory.
Example:
```
/Projects/OwlCc
```

Download Twilio PHP helper library zip file.
Unzip it into a work directories view subdirectory, which creates the library directory:
```
/Projects/OwlCc/views/twilio-php-master
```

In project directory, edit setvars.sh and add your values.

```
ACCOUNT_SID=your_account_SID
AUTH_TOKEN=your_account_auth_token
WORKSPACE_SID=your_TaskRouter_workspace_SID
```

Set your terminal session's environment variables.
```
$ source ./setvars.sh
```

Run the PHP HTTP server using port 8000.
```
$ php -S localhost:8000
```

Test that it works. In your browser, goto: http://localhost:8000.
The Owl CC home pages is displayed.

In another terminal window, run Ngrok to allow notifictions of incoming agent calls.
```
$ /Users/dthurston/Applications/ngrok http 8000
...
```

Connect as an agent by going to the Ngrok URL, example:
```
http://706bf85f.ngrok.io/agent_list
```