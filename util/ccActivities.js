// Documentation:
// https://www.twilio.com/docs/api/taskrouter/activities
//
console.log("+++ Start: ccActivies.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
console.log("+ List workspace activies.");
client.taskrouter.v1
        .workspaces(workspaceSid)
        .activities
        .list()
        .then((activities) => {
            activities.forEach((activity) => {
                console.log("+ SID: " + activity.sid + " : " + activity.friendlyName);
            });
            console.log("+ end of list.");
        });

// eof