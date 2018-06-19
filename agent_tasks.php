<?php
// Load Twilio PHP Helper Library.
require __DIR__ . '/twilio-php-master/Twilio/autoload.php';

use Twilio\Rest\Client;

$account_sid = getenv("ACCOUNT_SID");
$auth_token = getenv('AUTH_TOKEN');
$client = new Client($account_sid, $auth_token);
$workspaceSid = getenv("WORKSPACE_SID");
$tasks = $client->taskrouter->v1->workspaces($workspaceSid)
        ->tasks
        ->read();
foreach ($tasks as $task) {
    if ($task->assignmentStatus == "wrapping") {
        // echo "wrapping: " . $task->sid . "\xA";
        $client->taskrouter->v1->workspaces($workspaceSid)->tasks($task->sid)
                ->update(array(
                    'assignmentStatus' => "completed",
                    'reason' => "Was stuck in wrapping"
        ));
    }
}
// $twilio->taskrouter->v1->workspaces($workspaceSid)->tasks($task->sid)
//         ->update(array('assignmentStatus' => "completed",'reason' => "Stuck in wrapping"));
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Agent List</title>
        <link rel="icon" href="custom//favicon.ico" type="image/x-icon">    
        <link rel="shortcut icon" href="custom//favicon.ico" type="image/x-icon">
        <link href="custom/agentapp.css" rel="stylesheet"/>
    </head>
    <body>
        <script type="text/javascript" src="custom/pageTop.js"></script>
        <div class="company">
            <h2>Agent List</h2>
            <p>Click your TaskRouter Worker ID.</p>
            <div id="agentList">
                <table>
                    <?php
                    echo "\xA";
                    foreach ($tasks as $task) {
                        $reservations = $client->taskrouter->workspaces($workspaceSid)
                                ->tasks($task->sid)
                                ->reservations
                                ->read();
                        $doUpdate = "";
                        if ($task->assignmentStatus == "wrapping") {
                            $doUpdate = "*";
                        }
                        foreach ($reservations as $reservation) {
                            echo "<tr><td>"
                            . $task->sid
                            . " " . $reservation->reservationStatus
                            . " " . $reservation->workerName
                            . " " . $task->assignmentStatus . " " . $doUpdate
                            . " " . $task->reason
                            . "</td></tr>"
                            . "\xA";
                        }
                    }
                    ?>                    
                </table>
            </div>
        </div>
        <script type="text/javascript" src="custom/pageBottom.js"></script>
    </body>
</html>
