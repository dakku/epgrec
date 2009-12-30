#!/usr/local/bin/php
<?php
  include_once('config.php');
  include_once( INSTALL_PATH . '/DBRecord.class.php' );
  include_once( INSTALL_PATH . '/Reservation.class.php' );
  include_once( INSTALL_PATH . '/Keyword.class.php' );
  include_once( INSTALL_PATH . '/Settings.class.php' );
  
  $settings = Settings::factory();

$cmd_base = sprintf("%s/bg_getepg.php",INSTALL_PATH);
$chkfile = "/tmp/bg_chk";
$end_status = false;

$bs_run = false;
$gr_run = false;
$bs_end = false;
$gr_end = false;


  
  if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
  if( file_exists( $settings->temp_xml ) ) @unlink( $settings->temp_xml );

  if( file_exists( $chkfile."BS" ) ) @unlink( $chkfile."BS" );
  if( file_exists( $chkfile."GR" ) ) @unlink( $chkfile."GR" );


while(1){
	// チューナがない場合処理しない
	if($settings->bs_tuners == 0 && $settings->gr_tuners == 0) {
		break;
	}

	if($end_status) {
		break;
	}

	// BSを処理する
	if( $settings->bs_tuners != 0 && !$bs_end) {
		$type="BS";

		if ($bs_run && !file_exists($chkfile.$type)) {	// ファイルがなくて実行状態なら終了
			$bs_end = true;
		}

		if(!file_exists($chkfile.$type) && !$bs_end) {	// ファイルがなくてendがfalseの場合は実行
			$cmdline = sprintf('%s %s %s %s %s > /dev/null &',$cmd_base,$type,$settings->temp_data.$type,$settings->temp_xml.$type,$chkfile.$type);
			exec( $cmdline );	// バックグラウンドで情報取得を行う
			$bs_run = true;
		}
	} else {	// チューナが無い場合は終了状態
		$bs_run = true;
		$bs_end = true;
	}
	  
	// 地上波を処理する
	if( $settings->gr_tuners != 0 && !$gr_end) {
		$type="GR";

		if ($gr_run && !file_exists($chkfile.$type)) {	// ファイルがなくて実行状態なら終了
			$gr_end = true;
		}
		if(!file_exists($chkfile.$type) && !$gr_end) {
			$cmdline = sprintf('%s %s %s %s %s > /dev/null &',$cmd_base,$type,$settings->temp_data.$type,$settings->temp_xml.$type,$chkfile.$type);
			exec( $cmdline );	// バックグラウンドで情報取得を行う
			$gr_run = true;
		}
	} else {
		$gr_run = true;
		$gr_end = true;
	}

	if (($gr_run && $gr_end) && ($bs_run && $bs_end)) {
		$end_status = true;
	}

	clearstatcache();	// 念のためファイルのステータスのキャッシュをクリアする
	sleep(5);
}

  
  // 不要なプログラムの削除
  // 8日以上前のプログラムを消す
  $arr = array();
  $arr = DBRecord::createRecords(  PROGRAM_TBL, "WHERE endtime < subdate( now(), 8 )" );
  foreach( $arr as $val ) $val->delete();
	
  // 8日以上先のデータがあれば消す
  $arr = array();
  $arr = DBRecord::createRecords(  PROGRAM_TBL, "WHERE starttime  > adddate( now(), 8 )" );
  foreach( $arr as $val ) $val->delete();
  
  // キーワード自動録画予約
  $arr = array();
  $arr = Keyword::createKeywords();
  foreach( $arr as $val ) {
	try {
		$val->reservation($val->mode);
	}
	catch( Exception $e ) {
		// 無視
	}
  }
  
  exit();
?>
