<?php
include_once "avideo_client.conf";

/*
 * WScript 元件, 用來在 win32 系統下模擬 linux 的 fork 工作模式.
 * 在此為必要.
 * $wsh->run($exec, $display, $fork_flag);
 * $fork_flag: false: 開啟子程序獨立執行.
 * $display 意義:
 * 0 Hide the window and activate another window. 
 * 1 Activate and display the window. (restore size and position) Specify this flag when displaying a window for the first time. 
 * 2 Activate & minimize. 
 * 3 Activate & maximize. 
 * 4 Restore. The active window remains active. 
 * 5 Activate & Restore. 
 * 6 Minimize & activate the next top-level window in the Z order. 
 * 7 Minimize. The active window remains active. 
 * 8 Display the window in its current state. The active window remains active. 
 * 9 Restore & Activate. Specify this flag when restoring a minimized window. 
 * 10 Sets the show-state based on the state of the program that started the application. 
 */
try { 
  $wsh = new COM("WScript.Shell");
}catch(Exception $e){
  print_r($e);sleep(5);
}
if(!$wsh) die("WshShell Object created failed\r\n");

function renderx($path, $extra=null){

  global $wsh, $player;
  if(!is_file($player)){echo "$player is not an executable file.\n";sleep(5);exit;}
  $cmd = sprintf("\"%s\" \"%s\" %s", $player, $path, $extra);
  echo $cmd;
  $errno = $wsh->Run($cmd,2,false);
  return true;

}

/**
 *
 */
function strip_noise_chars($text){
  //去除掉可能因檔名已存在而被 windows檔案管理系統以 () 所附加的雜名: 例如像 (1), (2), ...
  preg_match_all('/(\s*\([0-9]+\))/', $text, $matched);
  $result = str_replace($matched[1][0], '', $text);
  return $result;
}

/* 引自php官網:
 * subtok(string,chr,pos,len)
 *
 * chr = chr used to seperate tokens
 * pos = starting postion
 * len = length, if negative count back from right
 *
 *  subtok('a.b.c.d.e','.',0)     = 'a.b.c.d.e'
 *  subtok('a.b.c.d.e','.',0,2)   = 'a.b'
 *  subtok('a.b.c.d.e','.',2,1)   = 'c'
 *  subtok('a.b.c.d.e','.',2,-1)  = 'c.d'
 *  subtok('a.b.c.d.e','.',-4)    = 'b.c.d.e'
 *  subtok('a.b.c.d.e','.',-4,2)  = 'b.c'
 *  subtok('a.b.c.d.e','.',-4,-1) = 'b.c.d'
 */
function subtok($string,$chr,$pos,$len = NULL) {
  return implode($chr,array_slice(explode($chr,$string),$pos,$len));
}

function echo2($text){
  echo $text."\n";
}
?>

