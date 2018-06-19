<?php
// Load Twilio PHP Helper Library.
require __DIR__ . '/twilio-php-master/Twilio/autoload.php';

use Twilio\Rest\Client;

$account_sid = getenv("ACCOUNT_SID");
$auth_token = getenv('AUTH_TOKEN');
$client = new Client($account_sid, $auth_token);
$workspace_sid = getenv("WORKSPACE_SID");
$voice_workers = $client->taskrouter->v1->workspaces($workspace_sid)
        ->workers
        ->read(array(
    'targetWorkersExpression' => "worker.channel.voice.configured_capacity > 0"
        )
);
$activities = $client->taskrouter->v1->workspaces($workspace_sid)
        ->activities
        ->read();
$activity = [];
foreach ($activities as $record) {
    $activity[$record->friendlyName] = $record->sid;
}
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
                    foreach ($voice_workers as $voice_worker) {
                        echo "<tr><td>"
                        . "<a href=\"agent_desktop.php?WorkerSid=" . $voice_worker->sid . "\" style=\"color: #080A30;padding-right: 10px;\"" . ">"
                        . $voice_worker->friendlyName . "</a>"
                        . "</td><td>"
                        . $voice_worker->activityName
                        . "</td></tr>"
                        . "\xA";
                    }
                    ?>                    
                </table>
            </div>
            <div style="padding-top: 10px;">
                <a href="/index.html">Home</a>
            </div>
        </div>
        <script type="text/javascript" src="custom/pageBottom.js"></script>
    </body>
</html>
