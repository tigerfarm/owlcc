// Documentation:
// https://www.twilio.com/docs/api/taskrouter/workers
//
console.log("+++ Start: ccWorkerList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;

console.log("+ List workers.");
client.taskrouter.v1
        .workspaces(workspaceSid)
        .workers
        .list()
        .then((workers) => {
            workers.forEach((worker) => {
                var jsonobj = JSON.parse(worker.attributes);
                console.log(
                        "+ " + worker.friendlyName
                        + " SID: " + worker.sid
                        + " Skills: " + jsonobj.skills
                        // + " Languages: " + jsonobj.languages
                        );
            });
            // {"skills":["support","billing","sales"],"languages":["en","fr","es"],"contact_uri":"client:WKxxxxxxxxxxxxxxxxxxxx"}
            // -----------------------------------------------------------------
            console.log("+ List worker details.");
            client.taskrouter.v1
                    .workspaces(workspaceSid)
                    .workers
                    .list()
                    .then((workers) => {
                        workers.forEach((worker) => {
                            // console.log("+ " + worker.friendlyName + " SID: " + worker.sid);
                            client.taskrouter.v1
                                    .workspaces(workspaceSid)
                                    .workers(worker.sid)
                                    .fetch()
                                    .then(worker => {
                                        console.log("+ " + worker.friendlyName
                                                + ", Available: " + worker.available
                                                + ", ActivitySid: " + worker.activitySid
                                                + ", Attributes: " + worker.attributes
                                                );
                                    });
                        });
                    });
            // -----------------------------------------------------------------
        });

// eof