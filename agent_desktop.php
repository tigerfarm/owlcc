<?php
// Load Twilio PHP Helper Library.
require __DIR__ . '/twilio-php-master/Twilio/autoload.php';

use Twilio\Rest\Client;
use Twilio\Jwt\TaskRouter\WorkerCapability;
use Twilio\Jwt\ClientToken;

// -------------------------------------------------------
$account_sid = getenv("ACCOUNT_SID");
$auth_token = getenv('AUTH_TOKEN');
$client = new Client($account_sid, $auth_token);
// -------------------------------------------------------
$workerSid = $_REQUEST['WorkerSid'];
$client_capability = new ClientToken($account_sid, $auth_token);
$client_capability->allowClientIncoming($workerSid);
$client_token = $client_capability->generateToken(28800); // Expire: 60 * 60 * 8
// -------------------------------------------------------
$workspace_sid = getenv("WORKSPACE_SID");
$capability = new WorkerCapability($account_sid, $auth_token, $workspace_sid, $workerSid);
$capability->allowFetchSubresources();
$capability->allowActivityUpdates();
$capability->allowReservationUpdates();
$workerToken = $capability->generateToken(28800);  // Expire: 60 * 60 * 8
// -------------------------------------------------------
$activities = $client->taskrouter->v1->workspaces($workspace_sid)->activities->read();
$activity = [];
foreach ($activities as $record) {
    $activity[$record->friendlyName] = $record->sid;
    $activityName[$record->sid] = $record->friendlyName;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Agent Desktop</title>
        <link rel="icon" href="/favicon.ico" type="image/x-icon">    
        <link rel="shortcut icon" href="custom//favicon.ico" type="image/x-icon">
        <script type="text/javascript" src="//media.twiliocdn.com/sdk/js/client/v1.4/twilio.min.js"></script>
        <script type="text/javascript" src="//media.twiliocdn.com/taskrouter/js/v1.10/taskrouter.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <link href="custom/agentapp.css" rel="stylesheet"/>
        <script type="text/javascript">
            Twilio.Device.setup("<?= $client_token ?>", {debug: true});
            Twilio.Device.ready(function (device) {
                console.log("Device: Client is ready for connections");
                logger("Device: Client is ready for connections.");
            });
            Twilio.Device.error(function (error) {
                $("#logger").text("Error: " + error.message);
                logger("Device: Error: " + error.message + ".");
            });
            Twilio.Device.connect(function (conn) {
                $("#logger").text("Device: Successfully established call");
                logger("Device: Successfully established call.");
            });
            Twilio.Device.disconnect(function (conn) {
                $("#logger").text("Device: Call ended");
                logger("Device: Client is ready for connections.");
            });
            Twilio.Device.incoming(function (conn) {
                //$("#logger").text("Incoming connection from " + conn.parameters.From);
                // accept the incoming connection and start two-way audio
                conn.accept();
                logger("Device: Incoming connection from " + conn.parameters.From + ".");
            });
            function call() {
                logger("call(), connecct to " + $("#number").val() + ".");
                params = {"PhoneNumber": $("#number").val()};
                Twilio.Device.connect(params);
            }
            function hangup() {
                logger("hangup(), set ReservationObject.task.complete().");
                ReservationObject.task.complete();
                // To totally shutdown the call:
                // $.post("/hangup", {
                //    participant: ReservationObject.task.attributes.conference.participants.customer,
                //    conference: ReservationObject.task.attributes.conference.sid
                //});
                // /hangup :
                //    participant = client.conferences(request.values.get('conference')).update(status="completed")
                //    resp = VoiceResponse
                //    return Response(str(resp), mimetype='text/xml')
                logger("Set Worker activity to: WrapUp.");
                worker.update("ActivitySid", "<?= $activity['WrapUp'] ?>", function (error, worker) {
                    logger("Worker: " + worker.friendlyName + ", has ended the call.");
                    logger("Device: disconnect.");
                    Twilio.Device.disconnectAll();
                    if (error) {
                        console.log(error.code);
                        console.log(error.message);
                    } else {
                        console.log(worker.activityName);
                    }
                    logger("---------");
                });
                logger("---------");
            }
        </script>
        <script type="text/javascript">
            //TaskRouter JS code
            let ReservationObject;
            // -----------------------------------------------------------------
            function goAvailable() {
                logger("goAvailable(): update worker's activity to: Idle.");
                worker.update("ActivitySid", "<?= $activity['Idle'] ?>", function (error, worker) {
                    if (error) {
                        console.log(error.code);
                        console.log(error.message);
                    }
                    ReservationObject.task.complete(); // If the status was wrapping, now it will be completed.
                });
                logger("---------");
            }
            function goOffline() {
                logger("goOffline(): update worker's activity to: Offline.");
                worker.update("ActivitySid", "<?= $activity['Offline'] ?>", function (error, worker) {
                    if (error) {
                        console.log(error.code);
                        console.log(error.message);
                    }
                });
            }
            // -----------------------------------------------------------------
            function rejectReservation() {
                logger("rejectReservation().");
                ReservationObject.reject();
            }
            function acceptReservation() {
                logger("acceptReservation(): start a conference call, and connect caller and agent.");
                var options = {
                    "PostWorkActivitySid": "<?= $activity['WrapUp'] ?>",
                    "Timeout": "30",
                    "Record": "false"
                };
                logger("Record the call: " + options.Record + ", Post Activity: WrapUp");
                // https://www.twilio.com/docs/taskrouter/api/reservations
                ReservationObject.conference(null, null, null, null,
                        function (error, reservation) {
                            if (error) {
                                console.log(error.code);
                                console.log(error.message);
                            }
                        },
                        options
                        );
                logger("Conference initiated.");
                refreshWorkerUI(worker, "In a Call");
            }
            // -----------------------------------------------------------------
            // Show/hide buttons corresponding to the activity
            function refreshWorkerUI(worker, activityOverride = null) {
                let activityName = activityOverride || worker.activityName;
                console.log("Worker activity: " + activityName);
                let buttons = {
                    'online': false,
                    'offline': false,
                    'accept': false,
                    'reject': false,
                    'hangup': false
                };
                switch (activityName) {
                    case "Idle":
                        buttons['offline'] = true;
                        break;
                    case "Offline":
                        buttons['online'] = true;
                        break;
                    case "Incoming Reservation":
                        buttons['accept'] = true;
                        buttons['reject'] = true;
                        break;
                    case "In a Call":
                        buttons['hangup'] = true;
                        break;
                    case "WrapUp":
                        buttons['offline'] = true;
                        buttons['online'] = true;
                        break;
                }
                Object.keys(buttons).forEach(function (button) {
                    //console.log(button, buttons[button]);
                    document.getElementById("btn_" + button).style.display = (buttons[button] ? "inline" : "none");
                });
                let available = worker.available;
                document.getElementById("worker_status").innerText = "Status: " + activityName;
                if (available === true) {
                    document.getElementById("worker_status").style.color = "#00BB00";
                } else {
                    document.getElementById("worker_status").style.color = "#BB0000";
            }
            }
            // -----------------------------------------------------------------
            function registerTaskRouterCallbacks() {
                logger("registerTaskRouterCallbacks().");
                worker.on('ready', function (worker) {
                    logger("Successfully registered as: " + worker.friendlyName + ".");
                    // document.querySelector('h2').innerHTML = "Call center agent desktop for: " + worker.friendlyName;
                    document.getElementById('worker_name').innerText = "Agent: " + worker.friendlyName + " >>> ";
                    if (worker.attributes.skills) {
                        logger("Skills: " + worker.attributes.skills.join(', '));
                    }
                    if (worker.attributes.languages) {
                        logger("Languages: " + worker.attributes.languages.join(', '));
                    }
                    logger("Current activity is: " + worker.activityName);
                    refreshWorkerUI(worker);
                });
                worker.on('reservation.created', function (reservation) {
                    logger("---------");
                    logger("reservation.created: You are reserved to handle a call from: " + reservation.task.attributes.from);
                    if (reservation.task.attributes.selected_language) {
                        logger("Caller selected language: " + reservation.task.attributes.selected_language);
                    }
                    logger("Customer request, task.attributes.selected_product: " + reservation.task.attributes.selected_product);
                    logger("Reservation SID: " + reservation.sid);
                    refreshWorkerUI(worker, "Incoming Reservation")
                    ReservationObject = reservation;  // set global ReservationObject
                });
                worker.on('activity.update', function (worker) {
                    let activityName = worker.activityName;
                    logger("Worker activity updated to: " + activityName);
                    refreshWorkerUI(worker);
                });
                worker.on('reservation.accepted', function (reservation) {
                    logger("Reservation " + reservation.sid + " accepted.");
                    logger("---------");
                    ReservationObject = reservation;
                });
                worker.on('reservation.rejected', function (reservation) {
                    logger("Reservation " + reservation.sid + " rejected.");
                });
                worker.on('reservation.timeout', function (reservation) {
                    logger("Reservation " + reservation.sid + " timed out.");
                });
                worker.on('reservation.canceled', function (reservation) {
                    logger("Reservation " + reservation.sid + " canceled.");
                });
            }
            // -----------------------------------------------------------------
            function logger(message) {
                var log = document.getElementById('log');
                log.value += "\n> " + message;
                log.scrollTop = log.scrollHeight;
            }
            function sleep(milliseconds) {
                var start = new Date().getTime();
                for (var i = 0; i < 1e7; i++) {
                    if ((new Date().getTime() - start) > milliseconds) {
                        break;
                    }
                }
            }
            function goAgentList() {
                goOffline();
                sleep(500);
                window.location.replace("/agent_list.php");
            }
            window.onload = function () {
                // Initialize TaskRouter.js on page load using window.workerToken -
                // a Twilio Capability token that was set from rendering the template with agents endpoint
                logger("Initializing...");
                window.worker = new Twilio.TaskRouter.Worker("<?= $workerToken ?>");
                registerTaskRouterCallbacks();
            };
        </script>
    </head>
    <body>
        <script type="text/javascript" src="custom/pageTop.js"></script>
        <div class="company">
            <!-- div class="content" -->
            <h2>Agent Desktop</h2>
            <table><tr>
                    <td><section id="worker_name"></section></td>
                    <td><section id="worker_status"></section></td>
                </tr></table>
            <section>
                <br/>
                <a class="btn" id="btn_online" style="display:none;"><span class="network-name" onclick="goAvailable()">Go Available</span></a>
                <a class="btn" id="btn_offline"><span class="network-name" onclick="goOffline()">Go Offline</span></a>
                <a class="btn" id="btn_accept" style="display:none;"><span class="network-name" onclick="acceptReservation()">Accept</span></a>
                <a class="btn" id="btn_reject" style="display:none;"><span class="network-name" onclick="rejectReservation()">Reject</span></a>
                <a class="btn" id="btn_hangup" style="display:none;"><span class="network-name" onclick="hangup()">Hangup</span></a>
            </section>
            <section class="log"></section>
            <br/>
            <section>
                <textarea id="log" readonly="true" style="width: 700px;height: 200px">Agent:</textarea>
            </section>
            <div style="padding-top: 10px;">
                <span id="goAgentList" onclick="goAgentList()">Return to Agent List</span>
            </div>
        </div>
        <script type="text/javascript" src="custom/pageBottom.js"></script>
    </body>
</html>
