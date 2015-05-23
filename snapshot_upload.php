<?php
/**
 * 2014-02-06 snapshot uploader
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
if ($handle = opendir($snapshot_dir)) {
  /* This is the correct way to loop over the directory. */
  while (false !== ($entry = readdir($handle))) {
    if( !in_array($entry, array('.', '..')) && in_array(subtok(strtolower($entry), ".", -1), array('jpg', 'png', 'gif')) )
      $str .= " -F \"snapshot[]=@$entry\"";
  }
  closedir($handle);
}
if( empty($str) )die("No snapshot found in $snapshot_dir! Exit\n");

$cmd = "curl $str ".$avideo_api_url['uploader_snapshot'];
echo2($cmd);
chdir($snapshot_dir);
$json=shell_exec($cmd);
echo $json;
$entries = json_decode($json, true);

if($entries){
  foreach($entries as $entry):
    //己若上載登錄成功, 則刪除本地端的snapshot
    print_r($entry);
    if( $entry['status'] == 'succeed' ) unlink($entry['orig_fname']);
  endforeach;
}

//寫日誌
file_put_contents(date("Y-m-d-His").'.log', print_r($entries,true));

//休息3秒後結束, 讓user有機會看見訊息.
sleep(3);
