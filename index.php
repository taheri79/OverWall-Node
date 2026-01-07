<?php

if (empty($_POST['action'])){
    echo 'ok';
    exit(404);
}
include_once 'functions.php';
$action = $_POST['action'];

if ($action == 'CreateConfig'){

    $token = $_POST['token'];

    if (getSetting('servertoken') != $token){
        echo 'tokenInvalid';
        exit();
    }
    if ($_POST['protocol'] == 'reality'){
        echo addInboundsReality($_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit']);
    }elseif($_POST['protocol'] == 'vlessws'){
        echo addInboundsVlessWs($_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit']);
    }elseif($_POST['protocol'] == 'xhttp'){
        echo addClient(1,$_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit']);
    }
}
if ($action == 'CreateTestConfig'){
    echo CreateTestConfig();
}
elseif ($action == 'DeleteConfig'){
    if ($_POST['protocol'] == 'xhttp'){
        echo DeleteClient(1,$_POST['uid']);
    }else{
        echo DeleteConfig($_POST['uid']);
    }

}