<?php
/**
 * 2014-03-22 pic uploader
 * 以 php-cli 的形式在local端執行
 *
 * 特色:
 *
 * 前置準備作業:
 */
chdir(dirname(__FILE__));
include_once 'avideo_client.conf';
include_once 'avideo_client.inc';
 
$str = "";
$count = 0;
$log_file = date("Y-m-d-His").'.log';
if ($handle = opendir($pic_dir)) {
  /* This is the correct way to loop over the directory. */
  while ( false !== ($entry = readdir($handle)) ) {
    if( !in_array($entry, array('.', '..')) && in_array(subtok(strtolower($entry), ".", -1), array('jpg', 'jpeg', 'png', 'gif')) ):
      $str .= " -F \"pic[]=@$entry\"";
      $count++;
    endif;
    
    if( 0 == ($count % 10) && !empty($str) ):
      upload($str, $log_file);
      $str = "";
    endif;
  }
  closedir($handle);
  if(!empty($str)):
    upload($str, $log_file);
  endif;  
}
if( empty($str) )die("No pic found in $pic_dir! Exit\n");

// 
function upload($str, $log_file){
  global $avideo_api_url, $pic_dir;
  
  $cmd = "curl $str " . $avideo_api_url['uploader_pic'];
  echo2($cmd);
  chdir($pic_dir);
  $json=shell_exec($cmd);
  echo $json;
  $entries = json_decode($json, true);

  if($entries){
    foreach($entries as $entry):
      //己若上載登錄成功, 則刪除本地端的pic
      print_r($entry);
      if( $entry['status'] == 'succeed' && !empty($entry['fid']) ) unlink($entry['orig_fname']);
    endforeach;
  }

  //寫日誌
  file_put_contents($log_file, print_r($entries,true), FILE_APPEND);
}
