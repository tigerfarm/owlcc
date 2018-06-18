// Documentation:
// https://www.twilio.com/docs/api/taskrouter/twiml-integration
//
console.log("+++ Start: ccWorkflowTaskStart.js");
const accountSid = process.env.ACCOUNT_SID;
const authToken = process.env.AUTH_TOKEN;
const client = require('twilio')(accountSid, authToken);
const workspaceSid = process.env.WORKSPACE_SID;

const workflowSupportSID = "WW999952bd6c48b9159592ea2c291b7e42";
// theJson = '{"selected_language": "en", "selected_product": "support"}';
theJson = '{"selected_product": "support"}';

const VoiceResponse = require('twilio').twiml.VoiceResponse;
const response = new VoiceResponse();
response.enqueue({
    workflowSid: workflowSupportSID
}).task({}, theJson);
console.log("response:", response.toString());

const twiml = new VoiceResponse();
let theDepartment = "";
let workflowSid = "";
theDepartment = "support";
workflowSid = workflowSupportSID;
theJson = '{"selected_product": "' + theDepartment + '"}';
twiml.enqueue({
    workflowSid: workflowSid
}).task({}, theJson);
console.log("TwiML:   ", twiml.toString());
// <?xml version="1.0" encoding="UTF-8"?>
// <Response>
// <Enqueue workflowSid="WW0123456789abcdef0123456789abcdef">
// <Task>{"selected_language" : "en", "selected_product" : "support"}</Task>
// </Enqueue>
// </Response>
// eof