// Documentation:
// https://www.twilio.com/docs/api/taskrouter/workflows
//
console.log("+++ Start: ccWorkflowList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
console.log("+ List tasks.");
client.taskrouter.v1
        .workspaces(workspaceSid)
        .workflows
        .list()
        .then((data) => {
            data.forEach((workflows) => console.log(
            "+ " + workflows.sid + " : " + workflows.friendlyName
            ));
            console.log("+ end of list.");
        });
// eof