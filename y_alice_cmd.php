#!/usr/bin/php
<?php


////////////////// Y_Alice_Cmd v 1.2///////////////////////
///////////////////////////////////////////////////////////
/////////////// Created by Anton Tumilovich ///////////////
/////////////// Telegram @Anton_Tumilovich  ///////////////
///////////////////////////////////////////////////////////
/// thanks to https://github.com/AlexxIT/YandexStation ////
///////////////////////////////////////////////////////////




class Yandex_Alice_Data
{
//// USER YANDEX NAME & PASSWORD /////
  public $username = '';
  public $password = '';


//// CLOUD COOKIE ////
  public $out_cookies = '';

//// CLOUD TOKEN ////
  public $main_token = ''; 





//// CLOUD API PRESET ////
///// Name of scenario for command
  public $scenario_name = 'Голос'; // Only russians symbols
///// Name of speaker
  public $speaker_name = ''; // if not set, use first speaker;


//// CLOUD Application ID / secret ////
// Application ID / secret Yandex Mobile get from https://github.com/AlexxIT/YandexStation
  public $client_id = 'c0ebe342af7d48fbbbfcf2d2eedb8f9e';
  public $client_secret = 'ad0a908f0aa341a182a37ecd75bc319e';

//// LOCAL Application ID / secret ////
// Application 2 ID / secret # Thanks to https://github.com/MarshalX/yandex-music-api/ get from https://github.com/AlexxIT/YandexStation
  public $client_secret_2 = '53bc75238f0c4d08a118e51fe9203300';
  public $client_id_2 = '23cabbbdc6cd418abb4b39c32c41195d';



//// AFTER GET DATA, SET THIS ////
//////// for local work, but now not done ////////
  public $local_token = '';


//// CLOUD 
  public $speaker_id = '';
  public $scenario_id = '';
}






$Y_Alice = new Yandex_Alice_Data();




function Get_Main_Token($Y_Alice)
{
  echo "GET : Main token \n";
//  echo "secret " . $Y_Alice->client_secret . "\n";
  $query = array(
            'client_secret' => $Y_Alice->client_secret,
            'client_id' => $Y_Alice->client_id,
            'grant_type' => 'password',
            'username' => $Y_Alice->username,
            'password' => $Y_Alice->password
  );
  $query = http_build_query($query);

//echo "query " . $query;

  $header = "Content-type: application/x-www-form-urlencoded";

  $opts = array('http' =>
      array(
      'method'  => 'POST',
      'header'  => $header,
      'content' => $query
      )
  );
  $context = stream_context_create($opts);
  $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);

  if ($result === FALSE)
  {
    echo "ERROR GET MAIN TOKEN, check login & password \n";
    die();
  }

//echo "Token result " . $result . "\n";
    $result = json_decode($result);

    $out_main_token =  $result->access_token;
//    $main_token = str_replace("%", "", $result->access_token);
//    $main_token = preg_replace('/[^ a-zа-яё\d]/ui', '', $result->access_token);
    return $out_main_token;
}


function Get_Local_Token($Y_Alice)
{
  echo "Get local token \n";
    $query = array(
            'client_secret' => $Y_Alice->client_secret,
            'client_id' => $Y_Alice->client_id,
            'grant_type' => 'x-token',
            'access_token' => $Y_Alice->main_token
    );
    $query = http_build_query($query);

    $header = "Content-type: application/x-www-form-urlencoded";

    $opts = array('http' =>
      array(
      'method'  => 'POST',
      'header'  => $header,
      'content' => $query
      ) 
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
    $result = json_decode($result);

    $local_token = $result->access_token;
    return $local_token;
}



function Get_User_Info($Y_Alice)
{
  echo "Get user info \n";
    $query = array(
      'token' => $Y_Alice->main_token,
      'size' => 'islands-300'
    );
    $query = http_build_query($query);

    // Forming the header for the POST request
    $header = "Content-type: application/x-www-form-urlencoded";

    // Executing the POST request and outputting the result
    $opts = array('http' =>
      array(
      'method'  => 'POST',
      'header'  => $header,
      'content' => $query
      ) 
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://registrator.mobile.yandex.net/1/user_info', false, $context);
  return $result;
}

function Get_Device_List($Y_Alice)
{
    $query = array();
    $query = http_build_query($query);

    $header = "Authorization: Oauth " . $Y_Alice->local_token . "\n";
    $header .= "Content-type: application/json";

    $opts = array('http' =>
      array(
      'method'  => 'GET',
      'header'  => $header
      )
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://quasar.yandex.net/glagol/device_list', false, $context);
    $result = json_decode($result);
  return $result;
}

function Get_Device_Data($Y_Alice, $device_id, $device_platform)
{
    $query = array();
    $query = http_build_query($query);

    $header = "Authorization: Oauth " . $Y_Alice->local_token . "\n";
    $header .= "Content-type: application/json";

    $opts = array('http' =>
      array(
      'method'  => 'GET',
      'header'  => $header
      )
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://quasar.yandex.net/glagol/token?device_id=' . $device_id . '&platform=' . $device_platform, false, $context);
    $result = json_decode($result);

  return $result->token;
}


function Get_Login_Cookies($Y_Alice)
{
  echo "Login to Yandex Quasar\n";
  $query = array(
  'type' => 'x-token',
  'retpath' => 'https://www.yandex.ru/androids.txt'
  );
  $query = http_build_query($query);

  $header = "Content-type: application/x-www-form-urlencoded\n";
  $header .= "Ya-Consumer-Authorization: OAuth " . $Y_Alice->main_token;

  $opts = array('http' =>
      array(
      'method'  => 'POST',
      'header'  => $header,
      'content' => $query
      ) 
  );
  $context = stream_context_create($opts);
  $result = file_get_contents('https://registrator.mobile.yandex.net/1/bundle/auth/x_token/', false, $context);
  $data = json_decode($result, true);

//    echo "Get pasport host " . $data['passport_host'] . "\n";
//    echo "Get track id ". $data['track_id'] . "\n";

  $url =  $data['passport_host'] . '/auth/session/' . '?track_id=' . $data['track_id'];

  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
// читаем куки здесь
  preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
  $cookies = array();
  $out_cookies = '';


  foreach($matches[1] as $item)
  {
    $out_cookies .= $item . ";";
  }
//var_dump($cookies);

//  file_put_contents('a.html', $result);


  $tmp_redir_pos = strpos($result, "Redirecting to ");
  $tmp_redir_url = substr($result, $tmp_redir_pos);
  $tmp_redir_url = str_replace("Redirecting to ", "", $tmp_redir_url);


  $url = $tmp_redir_url;

  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;


  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_COOKIE, $out_cookies);
  $result = curl_exec($ch);
//  file_put_contents('b.html', $result);

  return $out_cookies;
}



function Get_Scenarios($Y_Alice)
{
  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $Y_Alice->out_cookies);
  $result = curl_exec($ch);

  $out_str_pos = strpos($result, '{"status":"ok"');
  $out_str = substr($result, $out_str_pos);
//  $out_str = str_replace("Redirecting to ", "", $tmp_redir_url);

  return $out_str;
}

function Get_Scenario_id($Y_Alice, $scenario_name, $speaker_id)
{
  global $speaker_id;
  $data = json_decode(Get_Scenarios($Y_Alice));
//  $data = json_decode($data);
//  return $data->rooms[0]->devices[0]->id;
  foreach($data->scenarios as $key => $value)
  {
    if ($value->name == $Y_Alice->scenario_name)
    {
//      echo "found spekaer id " . $value->id . "\n";
      return $value->id;
    }
  }
  echo "SCENARIO NOT FOUND, Now create scenario\n";
  Add_Scenario($Y_Alice, $scenario_name, $speaker_id);
}


function Get_Devices($Y_Alice)
{
  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/devices';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $Y_Alice->out_cookies);
  $result = curl_exec($ch);

  $out_str_pos = strpos($result, '{"status":"ok"');
  $out_str = substr($result, $out_str_pos);
//  $out_str = str_replace("Redirecting to ", "", $tmp_redir_url);

  return json_decode($out_str, false);
//  return $out_str;
}


function Get_Speaker_id($Y_Alice, $speaker_name = '')
{
  $data = Get_Devices($Y_Alice);

  foreach($data->rooms as $key => $value)
  {
    $device_in_room_data = $value->devices;
//    echo "Room name is " . $value->name . "\n";
    foreach($device_in_room_data as $key => $value)
    {
//      echo "Device name is " . $value->name . " type is " . $value->type . "\n";
      if (strpos($value->type, "devices.types.smart_speaker") > -1 || strpos($value->type, "yandex.module") > -1)
      {
//      echo "FOUNDED Device name is " . $value->name . " type is " . $value->type . "\n";
        if (strlen($speaker_name) > 0)
        {
          if ($value->name == $speaker_name)
          {
//            echo "found NAMEd spekaer id " . $value->id . "\n";
            return $value->id;
          }
        }
        else
        {
//          echo "found FIRST spekaer id " . $value->id . "\n";
          return $value->id;
        }
      }
    }
  }
  echo "ERROR : NOT FOUND SPEAKERS\n";
  die();
//  return -1;
//  return $data->rooms[0]->devices[0]->id;
}



function Get_CSRF_Token($Y_Alice)
{
//echo "\nCSRF : main token : " . $Y_Alice->main_token . "\n";
//echo "\nCSRF : out_cookies : " . $Y_Alice->out_cookies . "\n";
  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;

//  $url = 'https://iot.quasar.yandex.ru/m/user/devices/' . $device_id . '/configuration';
  $url = 'https://yandex.ru/quasar/iot';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $Y_Alice->out_cookies);
  $result = curl_exec($ch);

//file_put_contents("scrf_token_result.txt", $result);

  $out_str_pos = strpos($result, '"csrfToken2":"');
  $out_str = substr($result, $out_str_pos);
  $out_str = str_replace('"csrfToken2":"', '', $out_str);
  $out_str_pos_end = strpos($out_str, '":"');
  $out_str = substr($result, $out_str_pos, $out_str_pos_end - 1);
  $out_str = str_replace('"csrfToken2":"', '', $out_str);
  $out_str = str_replace('","', '', $out_str);

//  return json_decode($out_str, false);
//  return $result;
  return $out_str;
}







function Add_Scenario($Y_Alice, $scenario_name, $spekaer_id)
{
  echo "ADD SCENARIO\n";
  $csrf_token = Get_CSRF_Token($Y_Alice);

  $arrayToSend = array(
            'name' => $scenario_name,// encode(device_id),
            'icon' => 'home',
            'trigger_type'=> 'scenario.trigger.voice',
            'devices'=> [],
            'external_actions' => [ array(
                'type' => 'scenario.external_action.phrase',
                'parameters' => array(
                    'current_device' => false,
                    'device_id' => $speaker_id,
                    'phrase' => '-'
                )
            )]
  );
  $json = json_encode($arrayToSend, JSON_UNESCAPED_UNICODE);

  $query = $json;

//  echo "JSON OUT ." . $json . ".\n";
//  file_put_contents('json_out.txt', $json);

  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;
  $headers[] = 'x-csrf-token: ' . $csrf_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios/';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $Y_Alice->out_cookies);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $result = curl_exec($ch);


//  $out_str_pos = strpos($result, '{"status":"ok"');
//  $out_str = substr($result, $out_str_pos);
//  $out_str = str_replace("Redirecting to ", "", $tmp_redir_url);

//  return json_decode($out_str, false);
//  return $out_str;
  return $result;
}


function Run_Scenario($Y_Alice, $speaker_id, $scenario_id, $text, $is_cmd = false)
{
  if (strlen($text) < 2) {return;}
  $csrf_token = Get_CSRF_Token($Y_Alice);
//  echo "csrf token ". $csrf_token . "\n";
  if ($is_cmd)
  {
    $action = 'text';
  }
  else
  {
    $action = 'phrase';
  }
//  $action = 'text';

  $arrayToSend = array(
            'name' => 'Голос',// encode(device_id),
            'icon' => 'home',
            'trigger_type'=> 'scenario.trigger.voice',
            'devices'=> [],
            'external_actions' => [ array(
                'type' => 'scenario.external_action.' . $action,
                'parameters' => array(
                    'current_device' => false,
                    'device_id' => $speaker_id,
                    $action => $text
//                    'phrase' => '-'
                )
            )]
  );
  $json = json_encode($arrayToSend, JSON_UNESCAPED_UNICODE);

  $query = $json;

//  echo "JSON OUT ." . $json . ".\n";

  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $Y_Alice->main_token;
  $headers[] = 'x-csrf-token: ' . $csrf_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios/' . $scenario_id;

//  file_put_contents('json_out.txt', $json);
//  file_put_contents('url_out.txt', $url);

//// UPDATE SCENARIO ///
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_COOKIE, $Y_Alice->out_cookies);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $result = curl_exec($ch);

//echo "Run result 0 " . $result . "\n";


//// RUN SCENARIO ///
  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios/' . $scenario_id . "/actions";

//  file_put_contents('json_out.txt', $json);
//  file_put_contents('url_out.txt', $url);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_COOKIE, $Y_Alice->out_cookies);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $result = curl_exec($ch);

//echo "Run result 1 " . $result . "\n";
  return $result;
}






  if (strlen($Y_Alice->username) < 1 || strlen($Y_Alice->password) < 1)
  {
    echo "First setup Yandex Alice account username and password\n";
    die();
  }


//  if (strlen($local_token) < 1)     {$local_token = Get_Local_Token($client_id_2, $client_secret_2, $main_token);}

  if (strlen($Y_Alice->main_token) < 1)      {$Y_Alice->main_token  = Get_Main_Token($Y_Alice);}
  if (strlen($Y_Alice->out_cookies) < 1)
  {
    $Y_Alice->out_cookies = Get_Login_Cookies($Y_Alice);
    echo "Script : replace string\n";
    echo 'public $out_cookies=' . "'';\n";
    echo "to string\n";
    echo 'public $out_cookies=' . "'" . $Y_Alice->out_cookies . "';\n";
  }

  if (strlen($Y_Alice->speaker_id) < 1)      {$Y_Alice->speaker_id  = Get_Speaker_id($Y_Alice, $Y_Alice->speaker_name);}
  if (strlen($Y_Alice->scenario_id) < 1)     {$Y_Alice->scenario_id = Get_Scenario_id($Y_Alice, $Y_Alice->scenario_name, $Y_Alice->speaker_name);}


//  $get_user_info = Get_User_Info($main_token);

//  $get_device_list = Get_Device_List($local_token);

//  $device_name = $get_device_list->devices[0]->name;
//  $device_id = $get_device_list->devices[0]->id;
//  $device_platform = $get_device_list->devices[0]->platform;

//  $get_device_token =  Get_Device_Data($main_token, $local_token, $device_id, $device_platform);



//  $get_scenarios = Get_Scenarios($main_token, $out_cookies);

//  $get_devices = Get_Devices($main_token, $out_cookies);

//  $speaker_id = $get_devices->rooms[0]->devices[0]->id;
//  $speaker_name = $get_devices->rooms[0]->devices[0]->name;
//  $speaker_type = $get_devices->rooms[0]->devices[0]->type;

//  $get_device_config = Get_Device_Config($main_token, $out_cookies, $speaker_id);



//  $csrf_token = Get_CSRF_Token($main_token, $out_cookies);
//  $add_scenario = Add_Scenario($main_token, $out_cookies, $scenario_name, $speaker_id);

//  $text = "Проверка";
//  $text = 'Включи джаз';
//  $text = 'предыдущий трек';
//  $is_cmd = true;
//  $run_scenario = Run_Scenario($Y_Alice, $speaker_id, $scenario_id, $text, $is_cmd);


/*
///// USER DATA ////
echo "User data is ";
print_r($get_user_info);
echo "\n";

///// TOKENS ////
echo "Main token     " . $main_token . "\n";
echo "Local token is " . $local_token . "\n";


///// DEVICE DATA ////
echo "Id           " . $device_id . ".\n";
echo "Name         " . $device_name . ".\n";
echo "Platfrom     " . $device_platform . ".\n";
echo "Device token " . $get_device_token . "\n";


///// CLOUD API ////
//  file_put_contents('c.html', $get_scenarios);
  echo "Get cookies ." . $out_cookies . ".\n";
  echo "Get scenarios " . $get_scenarios . "\n";


    echo "Speaker id " . $speaker_id . "\n";
    echo "Speaker name " . $speaker_name . "\n";
    echo "Speaker type " . $speaker_type . "\n";


print_r($get_device_config);

echo "CSRF Token " . $csrf_token . "\n";

echo $add_scenario . "\n";
*/

//  echo "Get scenarios " . $get_scenarios . "\n";

//echo "Run scenario " . $run_scenario . "\n";

//Get_Speaker_id($main_token, $out_cookies)




//echo "Scenario data " . $data->scenarios[0]->name . "\n";
//echo "GET : Main token     " . $Y_Alice->main_token . "\n";
//echo "GET : out cookies   " . $Y_Alice->out_cookies . "\n";


//echo "Echo speaker id " . $speaker_id . "\n";
//echo "Echo scenario id " . $scenario_id;

//  $get_devices = Get_Devices($main_token, $out_cookies);
//echo json_encode($get_devices);
//  file_put_contents('device.txt', json_encode($get_devices));


//    $text = "Проверка";
//    $text = 'Включи джаз';
//    $text = 'предыдущий трек';
//    $is_cmd = true;
//    $run_scenario = Run_Scenario($Y_Alice, $Y_Alice->speaker_id, $Y_Alice->scenario_id, $text, $is_cmd);


  if ($argc > 1)
  {
    if ($argv[1] == 'get_token')
    {
      echo "GET : Main token     " . $Y_Alice->main_token . "\n";
    }
  }


  if ($argc > 2)
  {
    if (strlen($argv[2]) > 0 && ($argv[1] == 'cmd' || $argv[1] == 'tts'))
    {
      $is_cmd = $argv[1] == 'cmd';
      $text = $argv[2];
      $run_scenario = Run_Scenario($Y_Alice, $Y_Alice->speaker_id, $Y_Alice->scenario_id, $text, $is_cmd);
    }
  }
  else
  {
    echo 'Usage : ' . $argv[0] . ' cmd/tts "text/command"' . "\n";
  }



?>