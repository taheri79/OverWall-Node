<?php
set_time_limit(0);
if (DIRECTORY_SEPARATOR == '/'){
    $dir = 'sqlite:/etc/x-ui/x-ui.db';
}else{
    $dir = 'sqlite:./x-ui.db';
}


$db = new PDO($dir) or die("cannot open the database");
date_default_timezone_set('Asia/Tehran');

function addInboundsVlessWs($uid, $traffic = 0, $day = 0, $iplimit = 0)
{
    global $db;
    $host = getSetting('hostnameapp');
    $email = 'app' . uniqid();

    $time = 0;
    if ($day) {
        $time = $day * 86400;
        $time = $time + time();
        $time = $time * 1000;
    }
    $port = rand(1024, 65535);
    while (checkAvailiblePort($port) == false) {
        $port = rand(1024, 65535);
    }
    $setting = '{
  "clients": [
    {
      "email": "' . $email . '",
      "user_name": "' . $uid . '",
      "enable": true,
      "expiryTime": ' . $time . ',
      "flow": "",
      "id": "' . $uid . '",
      "limitIp": ' . $iplimit . ',
      "reset": 0,
      "subId": "",
      "tgId": "",
      "totalGB": ' . ($traffic * 1024 * 1024 * 1024) . '
    }
  ],
  "decryption": "none",
  "fallbacks": []
}';
    $stream_setting = '{
  "network": "ws",
  "security": "none",
  "wsSettings": {
    "acceptProxyProtocol": false,
    "path": "/",
    "headers": {}
  }
}';

    $snif = '{
  "enabled": true,
  "destOverride": [
    "http",
    "tls",
    "quic"
  ]
}';
    $query = $db->prepare("
    INSERT INTO inbounds
    (`user_id`,`up`,`down`,`total`,`remark`,`enable`,`expiry_time`,`listen`,`port`,`protocol`,`settings`,`stream_settings`,`tag`,`sniffing`)
    VALUES
        (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $query->execute([
        1, 0, 0, $traffic * 1024 * 1024 * 1024, 'app-' . $uid, 1, $time, null, $port, 'vless', $setting, $stream_setting, "inbound-$port", $snif
    ]);
    addInboundViaApi($uid, $port, $traffic, $time, $email);
    $config = "vless://$uid@$host:$port?type=ws&path=%2F&security=none";
    return $config;
}

function addInboundsReality($uid, $traffic = 0, $day = 0, $iplimit = 0)
{
    global $db;
    $host = getSetting('hostnameapp');
    $email = 'app' . uniqid();

    $time = 0;
    if ($day) {
        $time = $day * 86400;
        $time = $time + time();
        $time = $time * 1000;
    }
    $port = rand(1024, 65535);
    while (checkAvailiblePort($port) == false) {
        $port = rand(1024, 65535);
    }
    $setting = '{
  "clients": [
    {
      "email": "' . $email . '",
      "user_name": "' . $uid . '",
      "enable": true,
      "expiryTime": ' . $time . ',
      "flow": "",
      "id": "' . $uid . '",
      "limitIp": ' . $iplimit . ',
      "reset": 0,
      "subId": "",
      "tgId": "",
      "totalGB": ' . ($traffic * 1024 * 1024 * 1024) . '
    }
  ],
  "decryption": "none",
  "fallbacks": []
}';
    $keys = getXrayKeysx25519();
    $stream_setting = '{
  "network": "tcp",
  "security": "reality",
  "externalProxy": [],
  "realitySettings": {
    "show": false,
    "xver": 0,
    "dest": "refersion.com:443",
    "serverNames": [
      "refersion.com"
    ],
    "privateKey": "' . $keys["private"] . '",
    "minClient": "",
    "maxClient": "",
    "maxTimediff": 0,
    "shortIds": [
      "e2",
      "e303",
      "f0ba61",
      "aa0712a8",
      "07769be95b",
      "dab34f9c2d7c",
      "b1a8fc29804aaf91",
      "fe5c5df27361a9"
    ],
    "settings": {
      "publicKey": "' . $keys["public"] . '",
      "fingerprint": "firefox",
      "serverName": "",
      "spiderX": "/"
    }
  },
  "tcpSettings": {
    "acceptProxyProtocol": false,
    "header": {
      "type": "none"
    }
  },
  "sockopt": {
    "acceptProxyProtocol": false,
    "tcpFastOpen": true,
    "mark": 0,
    "tproxy": "off",
    "tcpMptcp": false,
    "penetrate": false,
    "domainStrategy": "UseIP",
    "tcpMaxSeg": 1440,
    "dialerProxy": "",
    "tcpKeepAliveInterval": 0,
    "tcpKeepAliveIdle": 300,
    "tcpUserTimeout": 10000,
    "tcpcongestion": "bbr",
    "V6Only": false,
    "tcpWindowClamp": 600,
    "interface": ""
  }
}';

    $snif = '{
  "enabled": false,
  "destOverride": [
    "http",
    "tls",
    "quic",
    "fakedns"
  ],
  "metadataOnly": false,
  "routeOnly": false
}';
    $query = $db->prepare("
    INSERT INTO inbounds
    (`user_id`,`up`,`down`,`total`,`remark`,`enable`,`expiry_time`,`listen`,`port`,`protocol`,`settings`,`stream_settings`,`tag`,`sniffing`)
    VALUES
        (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $query->execute([
        1, 0, 0, $traffic * 1024 * 1024 * 1024, 'app-' . $uid, 1, $time, null, $port, 'vless', $setting, $stream_setting, "inbound-$port", $snif
    ]);
    addInboundViaApiReality($uid, $port, $traffic, $time, $email, $keys);
//    $config = "vless://$uid@$host:$port?type=ws&path=%2F&security=none";
    $config = "vless://$uid@$host:$port?type=tcp&security=reality&pbk=" . $keys["public"] . "&fp=firefox&sni=refersion.com&sid=e2&spx=%2F";
    return $config;
}

function getPanelBaseUrl()
{
    $host = getSetting('hostnameapp');
    $webPort = getSetting('webPort');
    $webBasePath = getSetting('webBasePath');

    return 'http://' . $host . ':' . $webPort . $webBasePath;
}

function curl_post_with_cookies(
    string       $url,
    array|string $postData = [],
    array        $headers = [],
    string       $cookieFile = __DIR__ . DIRECTORY_SEPARATOR . 'cookies.txt',
    int          $timeout = 30
)
{
    // Create cookie file if not exists
    if (!file_exists($cookieFile)) {
        file_put_contents($cookieFile, '');
    }

    $ch = curl_init($url);

    // Convert array data to query string if needed
    if (is_array($postData)) {
        $postData = http_build_query($postData);
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $timeout,

        // Cookie handling
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,

        // Headers
        CURLOPT_HTTPHEADER => $headers,

        // SSL (recommended safe defaults)
//        CURLOPT_SSL_VERIFYHOST  => 2,
//        CURLOPT_SSL_VERIFYPEER  => true,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Error: " . $error);
    }

    $info = curl_getinfo($ch);
    curl_close($ch);

    return [
        'body' => $response,
        'info' => $info
    ];
}

function LoginInPanel()
{
    $baseUrl = getPanelBaseUrl();
    curl_post_with_cookies($baseUrl.'login', json_encode([
        'username' => '',
        'password' => ''
    ]),['Content-Type: application/json']);
}


function addClient($inbound_id, $uid, $traffic = 0, $day = 0, $iplimit = 0)
{
    LoginInPanel();
    $baseUrl = getPanelBaseUrl();
    $time = 0;
    if ($day) {
        $time = $day * 86400;
        $time = $time + time();
        $time = $time * 1000;
    }
    $r = curl_post_with_cookies($baseUrl.'panel/api/inbounds/addClient', json_encode([
        'id' => $inbound_id,
        'settings' => '{"clients": [{
  "id": "'.$uid.'",
  "flow": "",
  "email": "app-'.$uid.'",
  "limitIp": '.$iplimit.',
  "totalGB": '.($traffic * 1024 * 1024 * 1024).',
  "expiryTime": '.$time.',
  "enable": true,
  "tgId": "",
  "subId": "",
  "comment": "",
  "reset": 0
}]}'
    ]),['Content-Type: application/json']);

    return "vless://$uid@net-meli.plus-agency.sbs:80?type=xhttp&encryption=none&path=%2F&host=groverwalll.global.ssl.fastly.net&mode=auto&security=none";
//    vless://695bd94add758@151.101.3.8:80?type=xhttp&encryption=none&path=%2F&host=groverwalll.global.ssl.fastly.net&mode=auto&security=none#germany-app-695bd94add758
}
//echo addClient(1,"1e42021b-3581-4174-9ee0-98fd31896a92",1,1);
function DeleteClient($inbound_id,$uid){
    global $db;

    $query = $db->prepare("SELECT * FROM client_traffics WHERE email = 'app-$uid'");
    $query->execute();
    $config = $query->fetchObject();

    $baseUrl = getPanelBaseUrl();
    $r = curl_post_with_cookies($baseUrl."panel/api/inbounds/$inbound_id/delClient/$uid", json_encode([]),['Content-Type: application/json']);

    return json_encode([
        'up' => round($config->up * 1.025),
        'down' => round($config->down * 1.025)
    ]);
}


function CreateTestConfig()
{
    global $db;
    $host = getSetting('hostnameapp');
    $email = 'app' . uniqid();
    $iplimit = 1;
    $traffic = 0.1;
    $traffic = round($traffic * 1024 * 1024 * 1024);
    $uid = format_uuidv4(random_bytes(16));
    $time = 3600;
    $time = $time + time();
    $time = $time * 1000;

    $port = rand(1024, 65535);
    while (checkAvailiblePort($port) == false) {
        $port = rand(1024, 65535);
    }
    $setting = '{
  "clients": [
    {
      "email": "' . $email . '",
      "user_name": "' . $uid . '",
      "enable": true,
      "expiryTime": ' . $time . ',
      "flow": "",
      "id": "' . $uid . '",
      "limitIp": ' . $iplimit . ',
      "reset": 0,
      "subId": "",
      "tgId": "",
      "totalGB": ' . $traffic . '
    }
  ],
  "decryption": "none",
  "fallbacks": []
}';
    $stream_setting = '{
  "network": "ws",
  "security": "none",
  "wsSettings": {
    "acceptProxyProtocol": false,
    "path": "/",
    "headers": {}
  }
}';

    $snif = '{
  "enabled": true,
  "destOverride": [
    "http",
    "tls",
    "quic"
  ]
}';
    $query = $db->prepare("
    INSERT INTO inbounds
    (`user_id`,`up`,`down`,`total`,`remark`,`enable`,`expiry_time`,`listen`,`port`,`protocol`,`settings`,`stream_settings`,`tag`,`sniffing`)
    VALUES
        (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $query->execute([
        1, 0, 0, $traffic, 'app-free-' . $uid, 1, $time, null, $port, 'vless', $setting, $stream_setting, "inbound-$port", $snif
    ]);
    addInboundViaApi($uid, $port, $traffic, $time, $email);
    $config = "vless://$uid@$host:$port?type=ws&path=%2F&security=none";
    return $config;
}

function format_uuidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function updateInboundProperty($id, $key, $value)
{
    global $db;
    $query = $db->prepare("UPDATE inbounds SET $key = '$value' WHERE id = '$id'");
    $query->execute();
}

function getAppInbounds()
{
    global $db;
    $query = $db->prepare("SELECT up,down,remark,enable FROM inbounds where remark like 'app-%'");
    $query->execute();
    $configs = [];
    while ($row = $query->fetchObject()) {
        $configs[] = $row;
    }
    return $configs;
}
function getAppInboundClients()
{
    global $db;
    $query = $db->prepare("SELECT up,down,email,enable FROM client_traffics where email like 'app-%'");
    $query->execute();
    $configs = [];
    while ($row = $query->fetchObject()) {
        $configs[] = $row;
    }
    return $configs;
}

function killXray()
{
    $pid = shell_exec('pgrep -u root xray-linux-amd6');
    shell_exec("kill $pid");
    sleep(1);
    $new = shell_exec('pgrep -u root xray-linux-amd6');
    if ($new == $pid) {
        shell_exec("kill -HUP $pid");
        sleep(1);
        $new = shell_exec('pgrep -u root xray-linux-amd6');
        if ($new == $pid) {
            shell_exec("kill -9 $pid");
        }
    }
}

function getXrayKeysx25519()
{
    // Run the command
    $output = [];
    $result = null;
    exec('/usr/local/x-ui/bin/xray-linux-amd64 x25519 2>&1', $output, $result);

    // If the command failed
    if ($result !== 0) {
        return null;
    }

    $keys = [
        'private' => null,
        'public' => null,
    ];

    // Parse the output
    foreach ($output as $line) {
        if (stripos($line, 'Private key:') !== false) {
            $keys['private'] = trim(str_replace('Private key:', '', $line));
        } elseif (stripos($line, 'Public key:') !== false) {
            $keys['public'] = trim(str_replace('Public key:', '', $line));
        }
    }

    // Return null if parsing failed
    if (!$keys['private'] || !$keys['public']) {
        return null;
    }

    return $keys;
}

function checkAndDeleteOldConfigs($days = 30)
{
    global $db;

    $time = $days * 86400;
    $time = time() - $time;
    $time = $time * 1000;

    $query = $db->prepare("DELETE FROM inbounds where expiry_time < $time and enable = 0");
    $query->execute();
}


function checkAvailiblePort($port)
{
    global $db;
    $query = $db->prepare("SELECT * FROM inbounds where port = $port limit 1");
    $query->execute();
    $row = $query->fetchObject();
    if (empty($row)) {
        return true;
    } elseif ($row == false) {
        return true;
    } else {
        return false;
    }
}

function getSetting($key)
{
    global $db;
    $query = $db->prepare("SELECT * FROM settings where key = '$key' limit 1");
    $query->execute();
    $row = $query->fetchObject();
    return $row->value;
}

function addSetting($key, $value)
{
    global $db;

    $query = $db->prepare("
    INSERT INTO settings
    (`key`,`value`)
    VALUES
        (?,?)
    ");
    $query->execute([
        $key, $value
    ]);
}

function getServerAddress()
{
    $data = file_get_contents('https://pingpe.storage.c2.liara.space/data.txt');
    $url = base64_decode($data);
    return $url;
}

function DeleteConfig($uuid)
{
    global $db;

    $query = $db->prepare("SELECT * FROM inbounds WHERE remark = 'app-$uuid'");
    $query->execute();
    $config = $query->fetchObject();

    removeInboundApi($config->id);
    $query = $db->prepare("DELETE FROM inbounds where remark = 'app-$uuid'");
    $query->execute();

    return json_encode([
        'up' => round($config->up * 1.025),
        'down' => round($config->down * 1.025)
    ]);

}

function addInboundViaApi($uid, $port, $traffic, $expiryTimestampMs, $email)
{
    $json = json_encode([
        'inbounds' => [
            [
                "tag" => "inbound-$port",
                "protocol" => "vless",
                "port" => $port,
                "listen" => "0.0.0.0",
                "settings" => [
                    "clients" => [[
                        "id" => $uid,
                        "email" => $email,
                        "enable" => true,
                        "expiryTime" => $expiryTimestampMs,
                        "totalGB" => $traffic * 1024 * 1024 * 1024
                    ]],
                    "decryption" => "none",
                    "fallbacks" => []
                ],
                "streamSettings" => [
                    "network" => "ws",
                    "security" => "none",
                    "wsSettings" => [
                        "path" => "/"
                    ]
                ],
                "sniffing" => [
                    "enabled" => true,
                    "destOverride" => ["http", "tls", "quic"]
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES);

    file_put_contents("/etc/overwall-node/inbound-$port.json", $json);
    $cmd = "/usr/local/x-ui/bin/xray-linux-amd64 api adi --server=127.0.0.1:62789 /etc/overwall-node/inbound-$port.json";
    $re = shell_exec($cmd);
    @unlink("/etc/overwall-node/inbound-$port.json");
    return $re;
}

function addInboundViaApiReality($uid, $port, $traffic, $expiryTimestampMs, $email, $keys)
{
    $json = json_encode([
        'inbounds' => [
            [
                "tag" => "inbound-$port",
                "protocol" => "vless",
                "port" => $port,
                "listen" => "0.0.0.0",
                "settings" => [
                    "clients" => [[
                        "id" => $uid,
                        "email" => $email,
                        "enable" => true,
                        "expiryTime" => $expiryTimestampMs,
                        "totalGB" => $traffic * 1024 * 1024 * 1024
                    ]],
                    "decryption" => "none",
                    "fallbacks" => []
                ],
                "streamSettings" => [
                    "network" => "tcp",
                    "security" => "reality",
                    "externalProxy" => [],
                    "realitySettings" => [
                        "show" => false,
                        "xver" => 0,
                        "dest" => "refersion.com:443",
                        "serverNames" => [
                            "refersion.com"
                        ],
                        "privateKey" => $keys["private"],
                        "minClient" => "",
                        "maxClient" => "",
                        "maxTimediff" => 0,
                        "shortIds" => [
                            "e2",
                            "e303",
                            "f0ba61",
                            "aa0712a8",
                            "07769be95b",
                            "dab34f9c2d7c",
                            "b1a8fc29804aaf91",
                            "fe5c5df27361a9"
                        ],
                        "settings" => [
                            "publicKey" => $keys["public"],
                            "fingerprint" => "firefox",
                            "serverName" => "firefox",
                            "spiderX" => "/",
                        ],
                    ],
                    "tcpSettings" => [
                        "acceptProxyProtocol" => false,
                        "header" => [
                            "type" => "none"
                        ],
                    ],
                    "sockopt" => [
                        "acceptProxyProtocol" => false,
                        "tcpFastOpen" => true,
                        "mark" => 0,
                        "tproxy" => "off",
                        "tcpMptcp" => false,
                        "penetrate" => false,
                        "domainStrategy" => "UseIP",
                        "tcpMaxSeg" => 1440,
                        "dialerProxy" => "",
                        "tcpKeepAliveInterval" => 0,
                        "tcpKeepAliveIdle" => 300,
                        "tcpUserTimeout" => 10000,
                        "tcpcongestion" => "bbr",
                        "V6Only" => false,
                        "tcpWindowClamp" => 600,
                        "interface" => "",
                    ],
                ],
                "sniffing" => [
                    "enabled" => true,
                    "destOverride" => ["http", "tls", "quic"]
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES);

    file_put_contents("/etc/overwall-node/inbound-$port.json", $json);
    $cmd = "/usr/local/x-ui/bin/xray-linux-amd64 api adi --server=127.0.0.1:62789 /etc/overwall-node/inbound-$port.json";
    $re = shell_exec($cmd);
    @unlink("/etc/overwall-node/inbound-$port.json");
    return $re;
}

function checkOnline()
{

    LoginInPanel();
    $baseUrl = getPanelBaseUrl();
    $r = curl_post_with_cookies($baseUrl.'panel/api/inbounds/onlines', json_encode([]),['Content-Type: application/json']);

    var_dump($r);
    return $r;

    $command = "/usr/local/x-ui/bin/xray-linux-amd64 api statsquery --server=127.0.0.1:62789 --pattern 'user>>>'";
    $output = shell_exec($command);

// Decode JSON
    $data = json_decode($output, true);
    if (!$data || !isset($data['stat'])) {
        echo "No valid stats received.\n";
        exit(1);
    }

    $onlineUsers = [];

    foreach ($data['stat'] as $stat) {
        if (isset($stat['value']) && $stat['value'] > 0) {
            // Extract username from the name string
            if (preg_match('/user>>>(([^>]+))>>>/', $stat['name'], $matches)) {
                $username = $matches[1];
                $onlineUsers[$username] = true; // use associative array to ensure uniqueness
            }
        }
    }

    return count($onlineUsers);
}

function removeInboundApi($id)
{
    global $db;
    $query = $db->prepare("SELECT * FROM inbounds where id = '$id' limit 1");
    $query->execute();
    $inbound = $query->fetchObject();

    $port = $inbound->port;

    $json = json_encode([
        'inbounds' => [
            [
                "tag" => "inbound-$port",
            ]
        ]
    ], JSON_UNESCAPED_SLASHES);

    file_put_contents("/etc/overwall-node/inbound-$port.json", $json);
    $cmd = "/usr/local/x-ui/bin/xray-linux-amd64 api rmi --server=127.0.0.1:62789 /etc/overwall-node/inbound-$port.json";
    $r = shell_exec($cmd);
    @unlink("/etc/x-ui-bot/inbound-$port.json");
    return $r;
}

function updateInboundApi($id)
{

    global $db;
    $query = $db->prepare("SELECT * FROM inbounds where id = '$id' limit 1");
    $query->execute();
    $inbound = $query->fetchObject();

    $port = $inbound->port;
    $r = removeInboundApi($id);
    $setting = json_decode($inbound->settings);
    $uuid = $setting->clients[0]->id;
    $email = $setting->clients[0]->email;
    $r .= addInboundViaApi($uuid, $port, $inbound->total, $inbound->expiry_time, $email);
    return $r;
}