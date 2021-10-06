<?php

function debug($datas = null, $type = 1, $com = false){//$type (1-запис у файл, 2-ехо без прериву, 3-ехо з преривом, 4-вордамп без прериву, 5-вордамп з преривом)
    if(is_null($datas)) return null;
    if($type == 1){
        //Це тестовий вивід у файл
        $ret = print_r($datas, true);
        $fd = fopen("1/LOG.txt", 'a');
        if($com == true)
		fwrite($fd, "######### START (".date("Y-m-d H:i:s").") #########".PHP_EOL);
        fwrite($fd, $ret.PHP_EOL);
        if($com == true)
 	       fwrite($fd, "######### STOP  (".date("Y-m-d H:i:s").") #########".PHP_EOL);
        fclose($fd);
    }else{
    echo ("<pre>");
        if($type == 2) echo($datas);
        elseif($type == 3){ echo($datas);     exit("stop"); }
        elseif($type == 4){ var_dump($datas); }
        elseif($type == 5){ var_dump($datas); exit("stop"); }
    }
}

/*
function debug($datas = null){
    if(is_null($datas)) return null;
    $ret = print_r($datas, true);
    $fd = fopen(APP_PATH."/config/LOG.txt", 'a');
    fwrite($fd, $ret.PHP_EOL);
    fclose($fd);
}

Виклик такий:
debug($myVariable); 
*/