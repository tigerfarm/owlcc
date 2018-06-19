// Documentation:
// https://www.twilio.com/docs/api/taskrouter/taskqueues
//
// Need to fix when not using the Agent app Client and when WrapUp, never goes to completed.
// WTd3b6a4ca832337da6147323342cce0db	0	10 mins 18 secs	voice 	Support Queue 	support Workflow 	David 	wrapping
// WTc9ec3ec82f1f7b369ccd4f8935bbdb23	0	2 mins 27 secs	voice 	Support Queue 	support Workflow 	Stacy 	wrapping
console.log("+++ Start: ccReservationsList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
client.taskrouter.v1
        .workspaces(workspaceSid).tasks.list()
        .then((tasks) => {
            console.log("+ Load Tasks.");
            tasks.forEach((task) => {
                doList(task.sid, task.assignment_status);
            });
            ;
        });
function doList(taskSid,taskAssignement) {
    client.taskrouter.v1
        .workspaces(workspaceSid).tasks(taskSid).reservations.list()
        .then((reservations) => {
            reservations.forEach((reservation) => {
                // Example: + taskSid:WTxxxxxxxxxxxxxxxxxxxxxx reservation.sid:WRxxxxxxxxxxxxxxxxxxxxxx accepted David
                console.log("+ taskSid:" + taskSid + " reservation.sid:" + reservation.sid + " " + reservation.reservationStatus + " " + reservation.workerName + " " + taskAssignement);
                // "participants":{"worker":"CA8d837e4b2d27bc9a245588e02effd702","customer":"CAef469be89cdd826632a0a82b9d4beefd"}
            });
        });
}
// eof