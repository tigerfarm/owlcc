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
        <title>Title</title>
        <link rel="icon" href="/favicon.ico" type="image/x-icon">    
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <link href="agentapp.css" rel="stylesheet"/>
    </head>
    <body>
        <div id="topBar">
            <table><tr>
                    <td><img src="TwilioLogo.jpg" alt="Twilio" style="width:120px; padding-right: 30px;"/></td>
                    <td><h1>Owl Call Center</h1></td>
                </tr></table>
            <hr>
        </div>
        <div class="company">
            <h2>TaskRouter Workers</h2>
            <?php
            foreach ($voice_workers as $voice_worker) {
                echo "<a href=\"agent_desktop.php?WorkerSid=" . $voice_worker->sid . "\">" . $voice_worker->friendlyName . "</a> - " . $voice_worker->activityName . "<br />";
            }
            ?>
        </div>
        <div id="bottomBar">
            <hr>
            <a href="/">Back to Home Page</a>
        </div>
    </body>
</html>
