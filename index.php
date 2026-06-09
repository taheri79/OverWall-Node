<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once 'functions.php';
if (empty($_POST['action'])){

//    echo 'ok';

    echo DeleteClient(1,'ee255b45-51c4-49c7-a4cf-a5d6399adc30');
//    var_dump(checkOnline());
//    var_dump(getServerAddress());

//    var_dump(getPanelBaseUrl());
//    var_dump(addClient(1,'0ca6e834-13ee-4189-ab6d-a0a4fc4bf72d',1,1,1,));
    //header('Location: '.getServerAddress());
}
$action = $_POST['action'];

if ($action == 'CreateConfig'){

    $token = $_POST['token'];

//    if (getSetting('servertoken') != $token){
//        echo 'tokenInvalid';
//        exit();
//    }
    if ($_POST['protocol'] == 'reality'){
        echo addInboundsReality($_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit']);
    }elseif($_POST['protocol'] == 'vlessws'){
        echo addInboundsVlessWs($_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit']);
    }elseif($_POST['protocol'] == 'vlesswsclient'){
        echo addClient(1,$_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit'],'vlessws');
    }elseif($_POST['protocol'] == 'xhttp'){
        echo addClient(1,$_POST['uid'],round($_POST['traffic']*0.95),$_POST['time'],$_POST['ip_limit'],'xhttp');
    }
}
if ($action == 'CreateTestConfig'){
    echo CreateTestConfig();
}
elseif ($action == 'DeleteConfig'){
    if (in_array($_POST['protocol'], ['xhttp','vlesswsclient'])){
        echo DeleteClient(1,$_POST['uid']);
    }else{
        echo DeleteConfig($_POST['uid']);
    }

}