// Documentation:
// https://www.twilio.com/docs/api/taskrouter/taskqueues
//
console.log("+++ Start: ccTaskQueueList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
console.log("+ List task queues.");
client.taskrouter.v1
        .workspaces(workspaceSid)
        .taskQueues
        .list()
        .then((taskQueues) => {
            taskQueues.forEach((taskQueue) => {
                console.log("+ " + taskQueue.sid + " " + taskQueue.friendlyName);
            });
            console.log("+ end of list.");
        });

// eof