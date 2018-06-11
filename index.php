<?php
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
switch ($request_uri[0]) {
    case '/':
        require 'views/index.html';
        break;
    case '/agents':
        require 'views/agent_desktop.php';
        break;
    case '/agent_list':
        require 'views/agent_list.php';
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        require 'views/notfound.php';
        break;
}