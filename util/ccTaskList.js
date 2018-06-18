// Documentation:
// https://www.twilio.com/docs/api/taskrouter/tasks
//
console.log("+++ Start: ccTaskList.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;
console.log("+ List tasks.");
client.taskrouter.v1
        .workspaces(workspaceSid).tasks.list()
        .then((tasks) => {
            tasks.forEach((task) => {
                var jsonobj = JSON.parse(task.attributes);
                // Example: + Task SID: WTxxxxxxxxxxxxxxxxxxxxxx Call from: +12223331234 to: +17778887890 Product: support
                console.log("+ Task SID: " + task.sid + " Call from: " + jsonobj.from + " to: " + jsonobj.called + " Product: " + jsonobj.selected_product);
            });
            console.log("+ end of list.");
        });
// task.attributes or task.sid
// {"from_country":"US","called":"+17778887890","selected_language":"en","to_country":"US","to_city":"SAN BRUNO","selected_product":"support","to_state":"CA","caller_country":"US","call_sid":"CAxxxxxxxxxxxxxxxxxxxx","account_sid":"ACxxxxxxxxxxxxxxxxxxxxxxx","from_zip":"94030","from":"+12223331234","direction":"inbound","called_zip":"94030","caller_state":"CA","to_zip":"94030","called_country":"US","from_city":"SAN BRUNO","called_city":"SAN BRUNO","caller_zip":"94030","api_version":"2010-04-01","called_state":"CA","from_state":"CA","caller":"+12223331234","caller_city":"SAN BRUNO","to":"+17778887890"}
// 
// eof