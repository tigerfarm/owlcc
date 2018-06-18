// Documentation:
// https://www.twilio.com/docs/api/taskrouter/taskqueues
//
console.log("+++ Start: ccReservationsList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
const taskSid = 'WT3a83cbc1250be97ec724922cf1a5e064';
const reservationSid = 'WRe605155e7a5c2a7032e69d963960ea41';
console.log("+ List reservations.");
client.taskrouter.v1
        .workspaces(workspaceSid)
        .tasks(taskSid)
        .reservations.list()
        .then((reservations) => {
            reservations.forEach((reservation) => {
                // Example: + taskSid:WTxxxxxxxxxxxxxxxxxxxxxx reservation.sid:WRxxxxxxxxxxxxxxxxxxxxxx accepted David
                console.log("+ taskSid:" + taskSid + " reservation.sid:" + reservation.sid + " " + reservation.reservationStatus + " " + reservation.workerName);
            });
            console.log("+ end of list.");
        });

// eof