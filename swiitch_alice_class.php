<?php

///////////////// Yandex_Alice_Cmd PHP Lib v 1.5//////////////////
//////////////////////////////////////////////////////////////////
/////////////////// Created by Anton Tumilovich //////////////////
/////////////////// Telegram @Anton_Tumilovich  //////////////////
//////////////////////////////////////////////////////////////////
/////// thanks to https://github.com/AlexxIT/YandexStation ///////
/// thanks to https://github.com/sergejey/majordomo-yadevices ////
//////////////////////////////////////////////////////////////////


/// v1.3 : 19/11/20 :
// [ADD] : REMOVE SPEC CHAR FROM STRING
// [ADD] : CUT LONG STRING TO 99 CHARS

/// v1.4 : 27/11/20 :
// [FIX] : SEARCH Speakers out of rooms

/// v1.5 : 02/11/20 :
// [ADD] : Function Send($msg), if $msg have at begin chars '!!', then msg send as comman else - msg send as tts msg
// [FIX] : Space cut on msg

class Yandex_Alice
{
//// USER YANDEX NAME & PASSWORD /////
  public $username = '';
  public $password = '';


//// CLOUD COOKIE ////
  public $out_cookies = '';

//// CLOUD 
  public $speaker_id = '';
  public $speaker_id_all = array();
  public $scenario_id = '';


//// CLOUD API PRESET ////
///// Name of scenario for command
  public $scenario_name = 'Голос'; // Only russians symbols
///// Name of speaker
  public $speaker_name = ''; // if not set, use first speaker;
  public $speaker_name_all = array(); // if not set, use first speaker;


  public $Config_File = '/tmp/alice_data.json';
  public $Use_Config = true;
  public $debug = false;
  public $verbose = true;
  public $csrf_token = "";


//// CLOUD TOKEN ////
//// NOT NEEDED YET //////
//  public $main_token = '';






//// CLOUD Application ID / secret ////
// Application ID / secret Yandex Mobile get from https://github.com/AlexxIT/YandexStation
//  public $client_id = 'c0ebe342af7d48fbbbfcf2d2eedb8f9e';
//  public $client_secret = 'ad0a908f0aa341a182a37ecd75bc319e';

//// LOCAL Application ID / secret ////
// Application 2 ID / secret # Thanks to https://github.com/MarshalX/yandex-music-api/ get from https://github.com/AlexxIT/YandexStation
//  public $client_secret_2 = '53bc75238f0c4d08a118e51fe9203300';
//  public $client_id_2 = '23cabbbdc6cd418abb4b39c32c41195d';






//// AFTER GET DATA, SET THIS ////
//////// for local work, but now not done ////////
  public $local_token = '';

  public $yandex_login_error_link = '';

  function __construct($username = '', $password = '', $speaker_name = '', $use_config = true, $config_file = '')
  {
    if (strlen($username) > 0) {$this->username = $username;};
    if (strlen($password) > 0) {$this->password = $password;};
    if (strlen($speaker_name) > 0) {$this->speaker_name = $speaker_name;}
    if ($use_config)
    {
      $this->Use_Config = true;
      if (strlen($config_file) > 0)
      {
        $this->Config_File = $config_file;
      }
      $this->Load_Data();
    }
    if (strlen($this->speaker_name) > 0)
    {
      $this->speaker_name_all = explode(';', $this->speaker_name);
    }
  }

  function Clear_Data()
  {
    $this->username = '';
    $this->password = '';

    $this->out_cookies = '';

    $this->speaker_id = '';
    $this->speaker_id_all = array();
    $this->scenario_id = '';

    $this->scenario_name = 'Голос'; // Only russians symbols

    $this->speaker_name = ''; // if not set, use first speaker;
    $this->speaker_name_all = array(); // if not set, use first speaker;
    $this->csrf_token = "";
  }

  function Save_Data($file = '')
  {
    if (!$this->Use_Config) {return;}
    if (strlen($file) > 0) {$this->Config_File = $file;}
    if ($this->debug) {echo "ALICE: Save config " . $this->Config_File . "\n";}

    $this->speaker_id = implode(';', $this->speaker_id_all);


    $data = new \stdClass();
    $data->json_data = 'yes';
    $data->speaker_name = $this->speaker_name;
    $data->scenario_name = $this->scenario_name;
    $data->speaker_id = $this->speaker_id;
    $data->scenario_id = $this->scenario_id;
//    $data->debug = $this->debug;
    $data->out_cookies = $this->out_cookies;
    $json_out = json_encode($data);
//    echo "Echo config is " . $json_out . "\n";

    if (strlen($this->Config_File) > 0) {file_put_contents($this->Config_File, $json_out);}

  }



  function Load_Data($file = '')
  {
    if (!$this->Use_Config) {return;}
    if (strlen($file) > 0) {$this->Config_File = $file;}
    if ($this->debug) {echo "ALICE: Load config " . $this->Config_File . "\n";}
    if (!file_exists($this->Config_File)) {return;}

    $json = file_get_contents($this->Config_File, FILE_USE_INCLUDE_PATH);


    $row = json_decode($json, true);

    if (!isset($row['json_data'])) {return;}
    if ($row['json_data'] != "yes") {return;}

//    $this->debug = $row['debug'];
    $this->out_cookies = $row['out_cookies'];
    $this->speaker_name = $row['speaker_name'];
    $this->scenario_name = $row['scenario_name'];
    $this->speaker_id = $row['speaker_id'];
    $this->scenario_id = $row['scenario_id'];
    if (strlen($this->speaker_name) > 0)
    {
      $this->speaker_name_all = explode(';', $this->speaker_name);
    }
    if (strlen($this->speaker_id) > 0)
    {
      $this->speaker_id_all = explode(';', $this->speaker_id);
    }

//print_r($this->speaker_id_all);
  }





  function Login()
  {
    if ($this->debug) {echo "ALICE: Login to Yandex Quasar\n";}
    if (strlen($this->username) < 1 && strlen($this->password) < 1)
    {
      if ($this->debug) {echo "ALICE: Error: Username or password not set\n";}
      return;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://passport.yandex.ru/passport?mode=auth&retpath=https://yandex.ru');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('login' => $this->username, 'passwd' => $this->password)));
//echo "\ncookies1 " . $cookies . "\n";
    $result = curl_exec($ch);

    if (strpos($result, "Redirecting to https://passport.yandex.ru/auth/finish") === FALSE)
    {
      $this->yandex_login_error_link = Get_String_Value_Full($result, "Redirecting to ", "");
    }
//      $this->yandex_login_error_link = Get_String_Value_Full($result, "Redirecting to ", "");

    file_put_contents('/tmp/alice_tts_login_result.txt', $result);


// читаем куки здесь
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
    $cookies = array();
    $out_cookies = '';

    foreach($matches[1] as $item)
    {
      $out_cookies .= $item . ";";
    }
//  var_dump($out_cookies);


    $tmp_redir_pos = strpos($result, "Redirecting to ");
    $tmp_redir_url = substr($result, $tmp_redir_pos);
    $tmp_redir_url = str_replace("Redirecting to ", "", $tmp_redir_url);


    $url = $tmp_redir_url;

//echo "redir url is " . $tmp_redir_url . "\n";

    $headers = array();
    $headers[] = 'Content-type: application/x-www-form-urlencoded';
    $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_COOKIE, $out_cookies);
    $result = curl_exec($ch);

    $this->Save_Data();

    $this->out_cookies = $out_cookies;
  }


function Get_Scenarios()
{
  if (strlen($this->out_cookies) < 1) {return -1;}
  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';
//  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $this->main_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $this->out_cookies);
  $result = curl_exec($ch);

  $out_str_pos = strpos($result, '{"status":"ok"');
  $out_str = substr($result, $out_str_pos);
//  $out_str = str_replace("Redirecting to ", "", $tmp_redir_url);

  return $out_str;
}


function Get_Devices()
{
  if (strlen($this->out_cookies) < 1) {return -1;}
  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';
//  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $this->main_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/devices';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $this->out_cookies);
  $result = curl_exec($ch);

  $out_str_pos = strpos($result, '{"status":"ok"');
  $out_str = substr($result, $out_str_pos);
//  $out_str = str_replace("Redirecting to ", "", $tmp_redir_url);

  return json_decode($out_str, false);
//  return $out_str;
}




function Get_Scenario_id()
{
  if (strlen($this->out_cookies) < 1) {return -1;}
  $data = json_decode($this->Get_Scenarios());
//  $data = json_decode($data);
//  return $data->rooms[0]->devices[0]->id;
  foreach($data->scenarios as $key => $value)
  {
    if ($value->name == $this->scenario_name)
    {
//      echo "found spekaer id " . $value->id . "\n";
      $this->scenario_id = $value->id;
      $this->Save_Data();
      return $value->id;
    }
  }
  echo "SCENARIO NOT FOUND, Now create scenario\n";
  $this->Add_Scenario();
//  $this->Add_Scenario($this->scenario_name, $this->speaker_id);
}




function Get_Speaker_id()
{
  if (strlen($this->out_cookies) < 1) {return -1;}
  $data = $this->Get_Devices();

  $is_found = false;

  foreach($data->rooms as $key => $value)
  {
    $device_in_room_data = $value->devices;
//    echo "Room name is " . $value->name . "\n";
    foreach($device_in_room_data as $key => $value)
    {
//      echo "Device name is " . $value->name . " type is " . $value->type . "\n";
      if (strpos($value->type, "devices.types.smart_speaker") > -1 || strpos($value->type, "yandex.module") > -1)
      {
        if ($this->verbose)
        {
          echo "FOUNDED Device name is " . $value->name . " type is " . $value->type . "\n";
        }
        if (count($this->speaker_name_all) > 0)
        {
          foreach ($this->speaker_name_all as $key_name => $value_name)
          {
//            $value = $value * 2;
//            if ($value->name == $this->speaker_name)
            if ($value->name == $value_name)
            {
              if ($this->debug) {echo "found NAMEd spekaer " . $value->name . " id " . $value->id . "\n";}
              array_push($this->speaker_id_all, $value->id);
              $is_found = true;
//            return $value->id;
            }
          }
        }
        else
        {
          if ($this->debug) {echo "found spekaer " . $value->name . " id " . $value->id . "\n";}
//          $this->speaker_id_all = $value->id;
          array_push($this->speaker_id_all, $value->id);
          $is_found = true;
//          $this->Save_Data();
//          return $value->id;
        }
      }
    }
  }

  foreach($data->speakers as $key => $value)
  {
//      echo "Device name is " . $value->name . " type is " . $value->type . "\n";
      if (strpos($value->type, "devices.types.smart_speaker") > -1 || strpos($value->type, "yandex.module") > -1)
    {
      if ($this->verbose)
      {
        echo "FOUNDED Device name is " . $value->name . " type is " . $value->type . "\n";
      }
      if (count($this->speaker_name_all) > 0)
      {
        foreach ($this->speaker_name_all as $key_name => $value_name)
        {
//            $value = $value * 2;
//            if ($value->name == $this->speaker_name)
          if ($value->name == $value_name)
          {
            if ($this->debug) {echo "found NAMEd spekaer " . $value->name . " id " . $value->id . "\n";}
            array_push($this->speaker_id_all, $value->id);
            $is_found = true;
//            return $value->id;
          }
        }
      }
      else
      {
        if ($this->debug) {echo "found spekaer " . $value->name . " id " . $value->id . "\n";}
//          $this->speaker_id_all = $value->id;
        array_push($this->speaker_id_all, $value->id);
        $is_found = true;
//          $this->Save_Data();
//          return $value->id;
      }
    }
  }


  if ($is_found)
  {
    $this->Save_Data();
  }
  else
  {
    if ($this->debug) {echo "ERROR : NOT FOUND SPEAKERS\n";}
  }
//  return ;
  return -1;
//  return $data->rooms[0]->devices[0]->id;
}




function Get_CSRF_Token()
{
  if (strlen($this->out_cookies) < 1) {return -1;}
//echo "\nCSRF : main token : " . $this->main_token . "\n";
//echo "\nCSRF : out_cookies : " . $this->out_cookies . "\n";
  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';
//  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $this->main_token;

//  $url = 'https://iot.quasar.yandex.ru/m/user/devices/' . $device_id . '/configuration';
  $url = 'https://yandex.ru/quasar/iot';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $this->out_cookies);
  $result = curl_exec($ch);

//file_put_contents("csrf_token_result.txt", $result);

  $out_str_pos = strpos($result, '"csrfToken2":"');
  $out_str = substr($result, $out_str_pos);
  $out_str = str_replace('"csrfToken2":"', '', $out_str);
  $out_str_pos_end = strpos($out_str, '":"');
  $out_str = substr($result, $out_str_pos, $out_str_pos_end - 1);
  $out_str = str_replace('"csrfToken2":"', '', $out_str);
  $out_str = str_replace('","', '', $out_str);
//  echo "csrf 1 " . $out_str . "\n";


//  return json_decode($out_str, false);
//  return $result;
//  return $out_str;
  $this->csrf_token = $out_str;
//echo "CSRF Token is " . $this->csrf_token;
}







function Add_Scenario()
{
  echo "ADD SCENARIO\n";
//  $this->csrf_token = 
  $this->Get_CSRF_Token();

  $arrayToSend = array(
            'name' => $this->scenario_name,// encode(device_id),
            'icon' => 'home',
            'trigger_type'=> 'scenario.trigger.voice',
            'devices'=> [],
            'external_actions' => [ array(
                'type' => 'scenario.external_action.phrase',
                'parameters' => array(
                    'current_device' => false,
                    'device_id' => $this->speaker_id,
                    'phrase' => '-'
                )
            )]
  );
  $json = json_encode($arrayToSend, JSON_UNESCAPED_UNICODE);

  $query = $json;

//  echo "JSON OUT ." . $json . ".\n";
  file_put_contents('/tmp/add_scenario_json_out.txt', $json);

  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';
//  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $this->main_token;
  $headers[] = 'x-csrf-token: ' . $this->csrf_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios/';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
/// set cookie
  curl_setopt($ch, CURLOPT_COOKIE, $this->out_cookies);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $result = curl_exec($ch);


  file_put_contents('/tmp/add_scenario_result.txt', $result);

//  $out_str_pos = strpos($result, '{"status":"ok"');
//  $out_str = substr($result, $out_str_pos);
//  $out_str = str_replace("Redirecting to ", "", $tmp_redir_url);

//  return json_decode($out_str, false);
//  return $out_str;
  return $result;
}



function Run_Scenario($textz, $is_cmd = false)
{
  $text = preg_replace('/[^А-Яа-яёA-Za-z0-9,\s]/iu', '', $textz);
  $text = substr($text, 0, 99);

  if (strlen($text) < 2)
  {
    if ($this->debug)
    {
      echo "ALICE: ERROR: CMD: Text is short";
      return "error_string_short";
    }
  }

// echo "cmd is ." . $is_cmd . "., msg is " . $text;

  if (strlen($this->out_cookies) < 1)    {$this->Login();}
  if (strlen($this->speaker_id) < 1)     {$this->Get_Speaker_id();}
  if (strlen($this->scenario_id) < 1)    {$this->Get_Scenario_id();}

  if ($this->debug) {echo "ALICE: Run scenario\n";}

//  $csrf_token = 
  $this->Get_CSRF_Token();
//  echo "csrf token ". $csrf_token . "\n";
  if ($is_cmd)
  {
    $action = 'text';
    $action_new = 'text_action';
  }
  else
  {
    $action = 'phrase';
    $action_new = 'phrase_action';
  }
//  $action = 'text';


//  $ex_actions = array();
  $dev_actions = array();


  foreach ($this->speaker_id_all as $key => $value)
  {
    // $arr[3] will be updated with each value from $arr...
//    echo "{$key} => {$value} ";
    $one_dev_action = new \stdClass();
    $one_dev_action->id = $value;
    $one_dev_action->capabilities = array();
    $one_state = array(
          'instance' => $action_new,
          'value' => $text
            );
    $all_state = array();
    array_push($all_state, $one_state);

    $one_cap = array(
        'type' => 'devices.capabilities.quasar.server_action',
        'state' => $one_state
      );

    array_push($one_dev_action->capabilities, $one_cap);

/*
    $one_ex_action = new \stdClass();
    $one_ex_action->type = 'scenario.external_action.' . $action;
    $one_ex_action->parameters = array(
                    'current_device' => false,
                    'device_id' => $value,
                    $action => $text
                );

    array_push($ex_actions, $one_ex_action);
*/
    array_push($dev_actions, $one_dev_action);
  }


  $arrayToSend = array(
            'name' => 'Голос',// encode(device_id),
            'icon' => 'home',
            'trigger_type'=> 'scenario.trigger.voice',
            'requested_speaker_capabilities'=> array(),
            'devices'=> $dev_actions
//            'devices'=> [],
//            'external_actions' => $ex_actions
  );
  $json = json_encode($arrayToSend, JSON_UNESCAPED_UNICODE);

  $query = $json;

//  echo "JSON OUT ." . $json . ".\n";
//  file_put_contents('update_scenario_json_out.txt', $json);

  $headers = array();
  $headers[] = 'Content-type: application/x-www-form-urlencoded';
  $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';
//  $headers[] = 'Ya-Consumer-Authorization: OAuth ' . $this->main_token;
  $headers[] = 'x-csrf-token: ' . $this->csrf_token;

  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios/' . $this->scenario_id;

//  file_put_contents('json_out.txt', $json);
//  file_put_contents('url_out.txt', $url);

//// UPDATE SCENARIO ///
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_COOKIE, $this->out_cookies);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $result = curl_exec($ch);

//echo "Run result 0 " . $result . "\n";
//  file_put_contents('update_scenario_result.txt', $result);


//// RUN SCENARIO ///
  $url = 'https://iot.quasar.yandex.ru/m/user/scenarios/' . $this->scenario_id . "/actions";

//  file_put_contents('json_out.txt', $json);
//  file_put_contents('url_out.txt', $url);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_COOKIE, $this->out_cookies);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $result = curl_exec($ch);

//echo "Run result 1 " . $result . "\n";
  return $result;
}


function Send($text)
{
  if (substr($text, 0, 2) == "!!")
  {
    return $this->Run_Scenario($text, true);
  }
  else
  {
    return $this->Run_Scenario($text, false);
  }
}

function Say($text)
{
//  $text = $textz;
  return $this->Run_Scenario($text, false);
}

function Cmd($text)
{
  return $this->Run_Scenario($text, true);
}





}
///////////////////// END OF ALICE TTS LIB ////////////////////




/////////////// OLD SUXXS Begin ///////////
//$Y_Alice = new Yandex_Alice_Data();



/*
///////// GET TOKEN NOT WORK YET //////
function Get_Main_Token($Y_Alice)
{
//  echo "GET : Main token \n";
//  echo "secret " . $Y_Alice->client_secret . "\n";
  $query = array(
            'client_secret' => $Y_Alice->client_secret,
            'client_id' => $Y_Alice->client_id,
            'grant_type' => 'password',
            'username' => $Y_Alice->username,
            'password' => $Y_Alice->password
  );
  $query = http_build_query($query);

echo "query " . $query;

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

echo "Token result " . $result . "\n";
  if ($result === FALSE)
  {
    echo "ERROR GET MAIN TOKEN, check login & password \n";
    die();
  }

echo "Token result " . $result . "\n";
    $result = json_decode($result);

    $out_main_token =  $result->access_token;
//    $main_token = str_replace("%", "", $result->access_token);
//    $main_token = preg_replace('/[^ a-zа-яё\d]/ui', '', $result->access_token);
    return $out_main_token;
}
*/

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
//    $headers[] = 'User-Agent: com.yandex.mobile.auth.sdk/7.15.0.715001762';
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















/*
//// OLD LOGIN AND GET COOKIES VIA MAIN TOKEN ////
function Get_Login_Cookies_Old($Y_Alice) /// VIA TOKEN
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

echo 'cookies ' + $out_cookies;
  return $out_cookies;
}
*/












//  if (strlen($local_token) < 1)     {$local_token = Get_Local_Token($client_id_2, $client_secret_2, $main_token);}

//  if (strlen($Y_Alice->main_token) < 1)      {$Y_Alice->main_token  = Get_Main_Token($Y_Alice);}
//  if (strlen($Y_Alice->out_cookies) < 1)
//  {
//    $Y_Alice->out_cookies = Get_Login_Cookies($Y_Alice);
//    $Y_Alice->Login();// = Get_Login_Cookies($Y_Alice);
//    echo "Script : replace string\n";
//    echo 'public $out_cookies=' . "'';\n";
//    echo "to string\n";
//    echo 'public $out_cookies=' . "'" . $Y_Alice->out_cookies . "';\n";
//  }

//  if (strlen($Y_Alice->speaker_id) < 1)      {$Y_Alice->speaker_id  = Get_Speaker_id($Y_Alice, $Y_Alice->speaker_name);}
//  if (strlen($Y_Alice->scenario_id) < 1)     {$Y_Alice->scenario_id = Get_Scenario_id($Y_Alice, $Y_Alice->scenario_name, $Y_Alice->speaker_name);}




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

//$text = "Проверка";
//  $text = 'Первая часть разработки закончена, теперь можно посылать любой текст на любую колонку с Яндекс Алиса';
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


//sleep(10);
//echo "argc " . $argc . "\n";
/*
  if ($argc > 1)
  {
    if ($argv[1] == 'get_token')
    {
      echo "GET : Main token     " . $Y_Alice->main_token . "\n";
    }
  }
*/
/*
  if ($argc > 2)
  {
    if (strlen($argv[2]) > 0 && ($argv[1] == 'cmd' || $argv[1] == 'tts'))
    {
      $is_cmd = $argv[1] == 'cmd';
      $text = $argv[2];
      if ($is_cmd)
      {
        $Y_Alice->Cmd($text);
      }
      else
      {
        $Y_Alice->Say($text);
      }
//      $run_scenario = $Y_Alice->Run_Scenario($text, $is_cmd);
    }
  }
  else
  {
    echo 'Usage : ' . $argv[0] . ' cmd/tts "text/command"' . "\n";
  }
*/
//    $text = "Проверка 2";
//    $text = "а может и три";
//    $text = 'Первая часть разработки закончена, теперь можно посылать любой текст на любую колонку с Яндекс Алиса';
//    $text = 'Включи джаз';
//    $text = 'предыдущий трек';
//    $is_cmd = true;
//    $run_scenario = Run_Scenario($Y_Alice, $Y_Alice->speaker_id, $Y_Alice->scenario_id, $text, $is_cmd);


?>