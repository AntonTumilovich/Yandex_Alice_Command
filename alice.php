#!/usr/bin/php
<?php

  include_once 'swiitch_alice_class.php';

  $Y_LOGIN = "";
  $Y_PASSWORD = "";

  $Alice = new Yandex_Alice($Y_LOGIN, $Y_PASSWORD);
  $Alice->debug = true;

  if ($argc > 2)
  {
    if (strlen($argv[2]) > 0 && ($argv[1] == 'cmd' || $argv[1] == 'tts'))
    {
      $is_cmd = $argv[1] == 'cmd';
      $text = $argv[2];
      if ($is_cmd)
      {
        $Alice->Cmd($text);
      }
      else
      {
        $Alice->Say($text);
      }
//      $run_scenario = $Y_Alice->Run_Scenario($text, $is_cmd);
    }
  }
  else
  {
    echo 'Usage : ' . $argv[0] . ' cmd/tts "text/command"' . "\n";
  }


?>