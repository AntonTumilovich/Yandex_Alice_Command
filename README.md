# Yandex_Alice_Command
Yandex Alice Command

Позволяет отправлять любые фразы, в том числе команды, для произнесения на Яндекс.Алису. Алиса либо произнесет нужную фразу, либо выполнит команду, например, если послать команду "включи джаз", Алиса включит джаз.

Для начала необходимо ввести свой логин и пароль от Яндекса в скрипте и запустить скрипт, он выдаст строчку с cookie, этой строчкой нужно заменить вот эту строку: public $out_cookies = '';
Потом запустить с параметром get_token и прописать в скрипте main_token, все, можно использовать.

Для произнесения Алисой фразы запустить:
./alice.php tts "Добрый день"

Для выполнения Алисой команды запустить:
./alice.php tts "включи джаз"
./alice.php tts "сделай погромче"
./alice.php tts "сделай потише"
./alice.php tts "стоп"

Для работы скрипта нужны пакеты: php, php-curl, php-openssl.

В файле ya_alice_cmd_flow.txt лежит пример флоу для Node-RED, перед использованием флоу замените путь до скрипта на свой.


v1.3 : 19/11/20 :<br>
  [ADD] : REMOVE SPEC CHAR FROM STRING<br>
  [ADD] : CUT LONG STRING TO 99 CHARS<br>

v1.4 : 27/11/20 :<br>
  [FIX] : SEARCH Speakers out of rooms<br>

v1.5 : 02/11/20 :
 [ADD] : Function Send($msg), if $msg have at begin chars '!!', then msg send as comman else - msg send as tts msg
 [FIX] : Space cut on msg

Если вам понравился проект, можете отблагодарить на кофе )))
https://yasobe.ru/na/ya_alice_command
