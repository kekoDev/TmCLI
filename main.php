<?php
if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
function keko_readline($text){
    if (PHP_OS == 'WINNT') {
        echo $text;
        $line = stream_get_line(STDIN, 1024, PHP_EOL);
     } else {
         $line = readline($text);
     }
}

include 'madeline.php';
$settings = [];
$settings['logger']['logger_level'] = 0;
$settings['app_info']['api_id'] = 579315;
$settings['app_info']['api_hash'] = '4ace69ed2f78cec268dc7az483fd3d3424';
if (!file_exists("accounts")){
    mkdir("accounts");
}
$files = glob("accounts/*.madeline");
echo "\n";
if (!empty($files)){
    echo "You have accounts : \n";
}else{
    echo "You donot have any account !\n";
}
foreach ($files as $file) {
  echo "- ".str_replace(".madeline","",str_replace("accounts/","",$file));
  echo "\n";
}
$phone = keko_readline("\nEnter your phone number: ");

if (empty($phone)){
    exit(0);
}
$key = keko_readline("\nEnter your key : ");
if (empty($key)){
    exit(0);
}
$data = file_get_contents("https://bot.keko.dev/api.php?key=" . urlencode($key));
if (!empty($data) and json_decode($data)->ok == true) {
    $data = json_decode($data);
} else {
    echo "- key dont work;";
    exit(0);
}
$sleep = keko_readline("\nEnter sleep time in minutes (10) : ");
if (empty($sleep) or is_numeric($sleep) == false){
    $sleep = 10;
}
$MadelineProto = new \danog\MadelineProto\API("accounts/".$phone.'.madeline', $settings);
$MadelineProto->async(false);
$MadelineProto->start();
$me = $MadelineProto->getSelf();
if (!$me['bot']) {
    if ($me["id"] != $data->chat_id){
        echo "- key not for this account;";
        exit(0);
    }
    $next = [];
    while (true) {
        if (empty($next)){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://bot.keko.dev/api.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $headers = array();
            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:104.0) Gecko/20100101 Firefox/104.0';
            $headers[] = 'Accept: */*';
            $headers[] = 'Accept-Language: en-US,en;q=0.5';
            $headers[] = 'Origin: https://bot.keko.dev';
            $headers[] = 'Alt-Used: bot.keko.dev';
            $headers[] = 'Cookie: key='.$key;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = json_decode(curl_exec($ch));
            curl_close($ch);
            if ($result->ok == false){
                print_r($result);
                exit(0);
            }
        }else{
            $result = $next;
        }
        echo "joining : {$result->title} | Member Count : {$result->MemberCount} | coin : {$result->coin}\n";
        try {
            $MadelineProto->channels->joinChannel(channel: $result->url);
        } catch (\danog\MadelineProto\RPCErrorException $e) {
            $MadelineProto->logger($e);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://bot.keko.dev/api.php?c='.$result->id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:104.0) Gecko/20100101 Firefox/104.0';
        $headers[] = 'Accept: */*';
        $headers[] = 'Accept-Language: en-US,en;q=0.5';
        $headers[] = 'Origin: https://bot.keko.dev';
        $headers[] = 'Alt-Used: bot.keko.dev';
        $headers[] = 'Cookie: key='.$key;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result2 = json_decode(curl_exec($ch));
        curl_close($ch);
        if ($result2->ok == false){
            print_r($result2);
            exit(0);
        }
        if (!empty($result2->f) and end($result2->f)->alert == "alert-success"){
            $next = $result2;
            echo "joined : {$result->title} | Member Count : {$result->MemberCount} | coin : {$result2->coin}\n";
        }else{
            $next = [];
            echo "error skip : {$result->title} | Member Count : {$result->MemberCount} | coin : {$result2->coin}\n";
        }
        sleep($sleep*60);
    }
}else{
    echo "You canot use bot !";
}
