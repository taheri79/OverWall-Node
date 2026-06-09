<?php

$url = $argv[1];
$host = $argv[2];
$name = $argv[3];


include_once 'functions.php';
addSetting('hostnameapp',$host);
$url = urlencode($url);
$token = file_get_contents(getServerAddress()."api/addServer?url=$url&name=$name");
addSetting('servertokenapp',$token);

// php -f install.php http://oww.overwall.sbs:8000/ oww.overwall.sbs oww