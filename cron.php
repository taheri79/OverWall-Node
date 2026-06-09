<?php
include_once 'functions.php';

$configs = getAppInbounds();

$stats = [];

foreach ($configs as $config) {
    if ($config->enable) {
        $uuid = str_replace('app-', '', $config->remark);
        $traffic = round(($config->down + $config->up) * 1.05);
        $stats[] = [
            'uuid' => $uuid,
            'up' => 0,
            'down' => $traffic,
        ];
    }
}
$clients = getAppInboundClients();
foreach ($clients as $client) {
    if ($client->enable) {
        $uuid = str_replace('app-', '', $client->email);
        $traffic = round(($client->down + $client->up) * 1.05);
        $stats[] = [
            'uuid' => $uuid,
            'up' => 0,
            'down' => $traffic,
        ];
    }
}
//var_dump(getServerAddress());
//addSetting('servertokenapp','nF0GfvaASs76Nt0s');
//var_dump(getSetting('servertokenapp'));
//var_dump($stats);
$ch = curl_init(getServerAddress() . 'api/updateStats');
$payload = json_encode([
    'token' => getSetting('servertokenapp'),
    'stats' => $stats,
//    'onlines'=>checkOnline()
    'onlines'=>0
]);
//    var_dump($payload);

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
checkAndDeleteOldConfigs(5);


