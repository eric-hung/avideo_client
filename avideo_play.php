<?php
/**
 * 2014-02-04 全新改版
 * 以 php-cli 的形式在local端執行
 *
 * 特色:
 *   1 不再依賴local端開 WEB SERVER, 只需安裝 php-cli 即可。
 *
 * 前置準備作業:
 *   1 系統需有 php-cli 的解譯器。
 *   2 需指定本目錄下的 asxer.BAT 為 .asx 副檔名的檔案之預設開啟程式。
 */
//參數處理: 
if( count($argv) !=3 ) die("Only exactly 2 parameter required: [nid] and [start]\n"); 
$nid = $argv[1];
$start = $argv[2];
if( !is_numeric($nid) || !is_numeric($start))die("Parameter format unexpected!\n Both of avideo_nid and start must be numeric\n");

chdir(dirname(__FILE__));
include_once 'avideo_client.conf';
include_once 'avideo_client.inc';

define('SLEEPING_TIME_BEFORE_SHELL_TERMINATED', 1);

$ext = 'asx';
if(!is_array($avideo_reposity_url)){
  $asx_path = $avideo_reposity_url."/$nid.$ext";
  //echo $asx_path;sleep(10);exit;
  $avideo_url = fetch_asx_file($asx_path);
  //echo2($nid);echo2($avideo_url);sleep(5);exit;
}else{
  foreach($avideo_reposity_url as $url):
    $asx_path = $url."/$nid.$ext";
    if(!is_url_effective($asx_path)){
      echo2("$asx_path not effective. Search next one...\n");
      continue;
    }
    else{
      $avideo_url = fetch_asx_file($asx_path);
      //echo2($nid);echo2($avideo_url);sleep(5);exit;
      echo2("$asx_path effective. Rendering...\n");
      break;
    }
  endforeach;  
}

if( empty($avideo_url) ){echo "Expected avideo_url which fetched from '$asx_path' cloud not be read.\n";sleep(5);exit;}

//根據組態變數呼叫同名函式:
run_player($avideo_url, $start, $nid);
sleep(SLEEPING_TIME_BEFORE_SHELL_TERMINATED);

/**
 * 回傳 $avideo_url;
 */
function fetch_asx_file($asx_path){
  $text = @file_get_contents($asx_path);
  if( empty($text) ) return null;
  
  preg_match_all('/ref href="(.*)"/', $text, $xxmatch);
  
  //$avideo_url = $xxmatch[1][0];
  $avideo_url = dirname($asx_path).'/'.basename($xxmatch[1][0]);

  return $avideo_url;
}

/*
 * start: 起始時刻
 * nid: node nid
 * ext: 副檔名
 *
 */
function run_player($avideo_url, $start, $nid) {
  $clip_list = fetch_clip_list($nid);

  //需先修改機碼:
  if(1)
    build_reg_registry($clip_list, $start, $avideo_url);
  
  if(!renderx($avideo_url)):
    echo2("影片路徑不存在: $avideo_url");
  else:
    echo2("影片路徑播放中: $avideo_url");
  endif;
  return;
}

/**
 *
 */
function build_reg_registry($clip_list, $start, $path){
  global $registry;
  foreach($registry as $v):
    if(empty($v))continue;
    
    $reg_cmd = sprintf("reg delete %s\\RememberFiles /va /f",$v);
    echo2($reg_cmd);
    exec($reg_cmd);

    $reg_cmd = sprintf("reg add %s\\RememberFiles /v 0 /t REG_SZ /d \"%d=%s\"",$v,$start*1000,$path);
    echo2($reg_cmd);
    exec($reg_cmd);

    $reg_cmd = sprintf("reg delete %s\\UrlHistory /va /f",$v);
    echo2($reg_cmd);
    exec($reg_cmd);    

    $reg_cmd = sprintf("reg add %s\\UrlHistory /v 0 /t REG_SZ /d \"%s\"",$v,$path);
    echo2($reg_cmd);
    exec($reg_cmd);

    //bookmark:
    $reg_cmd = sprintf("reg delete %s\\BMList /va /f",$v);
    echo2($reg_cmd);
    exec($reg_cmd);    

    $reg_cmd = sprintf("reg add %s\\BMList /v 0 /t REG_SZ /d \"%s\"",$v,$path);
    echo2($reg_cmd);
    exec($reg_cmd);

    //bookmark item:
    $reg_cmd = sprintf("reg delete %s\\BMItem_0 /va /f",$v);
    echo2($reg_cmd);
    exec($reg_cmd);    

    $idx = 0;
    foreach($clip_list as $clip):
      if(!isset($clip['start']))continue;
      
      $code = sprintf("%s*%s*%s",$clip['start']*1000,'wawa','lk39d9340');
      $reg_cmd = sprintf("reg add %s\\BMItem_0 /v %d /t REG_SZ /d \"%s\"",$v, $idx++, $code);
      exec($reg_cmd);  
      echo2($reg_cmd);
    endforeach;

  endforeach;
  
}

/**
 *
 */
function fetch_clip_list($avideo_nid){
  global $avideo_api_url;
  
  $url = $avideo_api_url['get_clip_list']."/$avideo_nid";
  $json = file_get_contents($url);
  $clip_list = json_decode($json, TRUE);

  return $clip_list;
}

function is_url_effective($url) {
  $ch = curl_init(); // create cURL handle (ch)
  if (!$ch) {
      die("Couldn't initialize a cURL handle");
  }
  // set some cURL options
  $ret = curl_setopt($ch, CURLOPT_URL,            $url);
  $ret = curl_setopt($ch, CURLOPT_HEADER,         1);
  $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $ret = curl_setopt($ch, CURLOPT_TIMEOUT,        2);

  // execute
  $ret = curl_exec($ch);

  if (empty($ret)) {
    curl_close($ch); // close cURL handler
    return FALSE;
  } else {
    $info = curl_getinfo($ch);
    curl_close($ch); // close cURL handler

    if (empty($info['http_code'])) {
      return FALSE;
    } elseif( '200' != $info['http_code'] ){
      return FALSE;
    }
    return TRUE;
  }
}