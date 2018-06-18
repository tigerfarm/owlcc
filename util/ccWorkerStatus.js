// Documentation:
// https://www.twilio.com/docs/api/taskrouter/workers
//
console.log("+++ Start: ccWorkerStatus.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
var arrayActivities = [];
client.taskrouter.v1
        .workspaces(workspaceSid).activities.list()
        .then((activities) => {
            console.log("++ Load workspace activies.");
            activities.forEach((activity) => {
                // console.log("+ SID: " + activity.sid + " : " + activity.friendlyName);
                arrayActivities.push([activity.sid, activity.friendlyName]);
            });
            workerList();
        });
function workerList() {
    client.taskrouter.v1
            .workspaces(workspaceSid).workers.list()
            .then((workers) => {
                console.log("++ List worker activity status.");
                workers.forEach((worker) => {
                    workerInfo(worker.sid);
                });
            });
}
function workerInfo(workerSid) {
    client.taskrouter.v1
            .workspaces(workspaceSid).workers(workerSid).fetch()
            .then(worker => {
                var theActivity = "";
                for (i = 0; i < arrayActivities.length; i++) {
                    if (arrayActivities[i][0] === worker.activitySid) {
                        theActivity = arrayActivities[i][1];
                    }
                }
                if (theActivity === "") {
                    theActivity = worker.activitySid;
                }
                console.log("+ " + worker.friendlyName
                        + " : " + theActivity
                        );
            });
}
//
// eof