// Documentation:
// https://www.twilio.com/docs/taskrouter/api/tasks (assignement status: retrieve and update)
// https://www.twilio.com/docs/taskrouter/api/reservations
// https://www.twilio.com/docs/taskrouter/lifecycle-task-state
// 
console.log("+++ Start: ccWorkerList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;

var request = require('request');
request('https://' + accountSid + ':' + authToken + '@taskrouter.twilio.com/v1/Workspaces/' + workspaceSid + '/Tasks', function (error, response, theResponse) {
    // console.log('error:', error);
    // console.log('statusCode:', response && response.statusCode); //error: null
    // console.log('JSON response:', theResponse);                  // statusCode: 200
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
            });
        });
}

// eof