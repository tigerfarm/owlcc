// Documentation:
// https://www.twilio.com/docs/api/taskrouter/workers
// 
// https://www.twilio.com/docs/taskrouter/api/tasks
// curl –XGET https://taskrouter.twilio.com/v1/Workspaces/$WORKSPACE_SID/Tasks \
// --data-urlencode AssignmentStatus='wrapping'
// -u $ACCOUNT_SID:$AUTH_TOKEN
//
console.log("+++ Start: ccWorkerList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;

var request = require('request');
request('https://' + accountSid + ':' + authToken + '@taskrouter.twilio.com/v1/Workspaces/' + workspaceSid + '/Tasks', function (error, response, theResponse) {
    console.log('error:', error);
    console.log('statusCode:', response && response.statusCode);
    // console.log('JSON response:', theResponse);
    var jsonobj = JSON.parse(theResponse);
    jsonobj.tasks.forEach(function (task) {
        doList(task);
    });
});
function doList(task) {
    client.taskrouter.v1
        .workspaces(workspaceSid).tasks(task.sid).reservations.list()
        .then((reservations) => {
            reservations.forEach((reservation) => {
                // Example: + taskSid:WTxxxxxxxxxxxxxxxxxxxxxx reservation.sid:WRxxxxxxxxxxxxxxxxxxxxxx accepted David
                console.log(
                        "+ taskSid:" + task.sid 
                        + " reservation.sid:" + reservation.sid 
                        + ', TaskQueue:', task.task_queue_friendly_name
                        + ', Workflow:', task.workflow_friendly_name
                        + ', Channel:', task.task_channel_unique_name
                        + " " + reservation.reservationStatus 
                        + " " + reservation.workerName + " " 
                        + task.assignment_status
                        );
                // "participants":{"worker":"CA8d837e4b2d27bc9a245588e02effd702","customer":"CAef469be89cdd826632a0a82b9d4beefd"}
            });
        });
}

// eof