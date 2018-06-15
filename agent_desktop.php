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
$client_token = $client_capability->generateToken();
// -------------------------------------------------------
$workspace_sid = getenv("WORKSPACE_SID");
$capability = new WorkerCapability($account_sid, $auth_token, $workspace_sid, $workerSid);
$capability->allowFetchSubresources();
$capability->allowActivityUpdates();
$capability->allowReservationUpdates();
$workerToken = $capability->generateToken(28800);  // 60 * 60 * 8
// -------------------------------------------------------
$activities = $client->taskrouter->v1->workspaces($workspace_sid)->activities->read();
$activity = [];
foreach ($activities as $record) {
    $activity[$record->friendlyName] = $record->sid;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Owl Agents</title>
        <link rel="icon" href="/favicon.ico" type="image/x-icon">    
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <script type="text/javascript" src="//media.twiliocdn.com/sdk/js/client/v1.4/twilio.min.js"></script>
        <script type="text/javascript" src="//media.twiliocdn.com/taskrouter/js/v1.10/taskrouter.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <link href="agentapp.css" rel="stylesheet"/>
        <script type="text/javascript">
            Twilio.Device.setup("<?= $client_token ?>", {debug: true});
            Twilio.Device.ready(function (device) {
                console.log("Client is ready for connections");
            });
            Twilio.Device.error(function (error) {
                $("#logger").text("Error: " + error.message);
            });
            Twilio.Device.connect(function (conn) {
                $("#logger").text("Successfully established call");
            });
            Twilio.Device.disconnect(function (conn) {
                $("#logger").text("Call ended");
            });
            Twilio.Device.incoming(function (conn) {
                //$("#logger").text("Incoming connection from " + conn.parameters.From);
                // accept the incoming connection and start two-way audio
                conn.accept();
            });
            function call() {
                // get the phone number or client to connect the call to
                params = {"PhoneNumber": $("#number").val()};
                Twilio.Device.connect(params);
            }
            function hangup() {
                Twilio.Device.disconnectAll();
                ReservationObject.task.complete();

                worker.update("ActivitySid", "<?= $activity['WrapUp'] ?>", function (error, worker) {
                    logger(worker.friendlyName + " has ended the call");

                    if (error) {
                        console.log(error.code);
                        console.log(error.message);
                    } else {
                        console.log(worker.activityName);
                    }
                });
            }
        </script>
        <script type="text/javascript">
            //TaskRouter JS code
            let ReservationObject;
            // -----------------------------------------------------------------
            function goAvailable() {
                // update worker's activity to Idle
                worker.update("ActivitySid", "<?= $activity['Idle'] ?>", function (error, worker) {
                    if (error) {
                        console.log(error.code);
                        console.log(error.message);
                    }
                });
            }
            function goOffline() {
                // update worker's activity to Offline
                worker.update("ActivitySid", "<?= $activity['Offline'] ?>", function (error, worker) {
                    if (error) {
                        console.log(error.code);
                        console.log(error.message);
                    }
                });
            }
            // -----------------------------------------------------------------
            function rejectReservation() {
                ReservationObject.reject();
            }
            function acceptReservation() {
                // Create a new conference and join customer and worker into it
                var options = {
                    "From": "<?= $caller_ID ?>", // CC's phone number
                    "PostWorkActivitySid": "<?= $activity['WrapUp'] ?>",
                    "Timeout": "30",
                    "Record": "true",
                    "RecordingStatusCallback": window.location.protocol + "//" + window.location.host + "/recording_callback",
                    "ConferenceStatusCallback": window.location.protocol + "//" + window.location.host + "/conference_callback",
                    "ConferenceStatusCallbackEvent": "start,end,join,leave"
                };
                console.log("Starting conference...");
                console.log(options);
                ReservationObject.conference(null, null, null, null,
                        function (error, reservation) {
                            if (error) {
                                console.log(error.code);
                                console.log(error.message);
                            }
                        },
                        options
                        )
                logger("Conference initiated!");
                refreshWorkerUI(worker, "In a Call");
            }
            // -----------------------------------------------------------------
            function muteCaller() {
                $.post("/callmute", {
                    participant: ReservationObject.task.attributes.conference.participants.customer,
                    conference: ReservationObject.task.attributes.conference.sid,
                    muted: "True"
                });
            }
            function unmuteCaller(customer) {
                //post to /callmute end point with the customer callsid and conferenceSID
                if (customer) {
                    $.post("/callmute", {
                        participant: customer,
                        conference: ReservationObject.task.attributes.conference,
                        muted: "False"
                    });

                } else {
                    $.post("/callmute", {
                        participant: ReservationObject.task.attributes.conference.participants.customer,
                        conference: ReservationObject.task.attributes.conference.sid,
                        muted: "False"
                    });
                }
            }
            // -----------------------------------------------------------------
            // Show/hide buttons corresponding to the activity
            function refreshWorkerUI(worker, activityOverride = null) {
                let activityName = activityOverride || worker.activityName;
                console.log("Worker activity: " + activityName);
                let buttons = {
                    'online': false,
                    'offline': false,
                    'mute': false,
                    'unmute': false,
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
                        buttons['mute'] = true;
                        buttons['unmute'] = true;
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
                worker.on('ready', function (worker) {
                    logger("Successfully registered as: " + worker.friendlyName);
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
                worker.on('activity.update', function (worker) {
                    let activityName = worker.activityName;
                    logger("Worker activity changed to: " + activityName);
                    refreshWorkerUI(worker)
                });
                worker.on('reservation.created', function (reservation) {
                    logger("-----");
                    logger("You have been reserved to handle a call!");
                    logger("Call from: " + reservation.task.attributes.from);
                    logger("Selected language: " + reservation.task.attributes.selected_language);
                    logger("Customer request: " + reservation.task.attributes.selected_product);
                    logger("-----");
                    logger(reservation.sid);
                    refreshWorkerUI(worker, "Incoming Reservation")
                    ReservationObject = reservation;  // set global ReservationObject
                });
                worker.on('reservation.accepted', function (reservation) {
                    logger("Reservation " + reservation.sid + " accepted.");
                    // update reservationObject to contain the updated reservation information/task attributes e.g. conference
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
        <script type="text/javascript" src="../pageTop.js"></script>
        <div class="company">
        <!-- div class="content" -->
            <h2>Owl Contact Center Agent Desktop</h2>
            <table><tr>
                    <td><section id="worker_name"></section></td>
                    <td><section id="worker_status"></section></td>
            </tr></table>
            <section>
                <br/>
                <a class="btn" id="btn_online" style="display:none;"><span class="network-name" onclick="goAvailable()">Go Available</span></a>
                <a class="btn" id="btn_offline" style="display:none;"><span class="network-name" onclick="goOffline()">Go Offline</span></a>
                <a class="btn" id="btn_accept" style="display:none;"><span class="network-name" onclick="acceptReservation()">Accept</span></a>
                <a class="btn" id="btn_reject" style="display:none;"><span class="network-name" onclick="rejectReservation()">Reject</span></a>
                <a class="btn" id="btn_mute" style="display:none;"><span class="network-name" onclick="muteCaller()">Mute</span></a>
                <a class="btn" id="btn_unmute" style="display:none;"><span class="network-name" onclick="unmuteCaller()">Unmute</span></a>
                <a class="btn" id="btn_hangup" style="display:none;"><span class="network-name" onclick="hangup()">Hangup</span></a>
            </section>
            <section class="log"></section>
            <br/>
            <section>
                <textarea id="log" readonly="true" style="width: 600px;height: 200px"></textarea>
            </section>
        </div>
        <div id="bottomBar">
            <hr>
            <a href="/agent_list.php">Back to Agent List</a>
        </div>
    </body>
</html>