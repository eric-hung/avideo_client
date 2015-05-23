<?php
/**
 * 2014-02-07
 *   1 只是單純地根據參數決定將工作配交給哪支程式處理
 */
//參數處理: 
if( count($argv) !=2 ) die("Only exactly 1 parameter required!\n");

chdir(dirname(__FILE__));
include_once 'avideo_client.conf';
include_once 'avideo_client.inc';

//參數必須是以"."做分隔欄位字元，至少要有兩欄，且最後一欄是"asx"
$argv[1] = strip_noise_chars(basename($argv[1]));

$fs = explode(".", $argv[1]);
if (count($fs)<2 || $fs[count($fs)-1] != "asx") die("Parameter format error! Format: \"[cmd].[[args]].asx\"\n");

switch($fs[0]):
  case 'avideo_play':
    $cmd = sprintf("%s -q avideo_play.php %d %d", $php_exec, $fs[1], $fs[2]);
    echo2($cmd);
    echo2(shell_exec($cmd));
    break;
  case 'snapshot_upload':
    $cmd = sprintf("%s -q snapshot_upload.php", $php_exec);
    echo2($cmd);
    echo2(shell_exec($cmd));
    break;
  default:  
endswitch;
sleep(5);
