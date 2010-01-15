<?php
include_once('config.php');
include_once( INSTALL_PATH . '/Smarty/Smarty.class.php' );
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );
include_once( INSTALL_PATH . "/Reservation.class.php" );
include_once( INSTALL_PATH . "/Keyword.class.php" );
// include_once( INSTALL_PATH . "/Settings.class.php" );

// 新規キーワードがポストされた
if( isset($_POST["add_keyword"]) ) {
	if( $_POST["add_keyword"] == 1 ) {
		try {
			$rec = new Keyword();
			$rec->keyword = $_POST['k_search'];
			$rec->type = $_POST['k_type'];
			$rec->category_id = $_POST['k_category'];
			$rec->channel_id = $_POST['k_station'];
			$rec->use_regexp = $_POST['k_use_regexp'];
			$rec->mode = $_POST['record_mode'];
			$rec->dir_id = $_POST['dir_id'];
			
			// 録画予約実行
			$rec->reservation($rec->mode,$rec->dir_id);
		}
		catch( Exception $e ) {
			exit( $e->getMessage() );
		}
	}
}


$keywords = array();
try {
	$recs = Keyword::createRecords(KEYWORD_TBL);
	foreach( $recs as $rec ) {
		$arr = array();
		$arr['id'] = $rec->id;
		$arr['keyword'] = $rec->keyword;
		$arr['type'] = $rec->type == "*" ? "すべて" : $rec->type;
		
		if( $rec->channel_id ) {
			$crec = new DBRecord(CHANNEL_TBL, "id", $rec->channel_id );
			$arr['channel'] = $crec->name;
		}
		else $arr['channel'] = 'すべて';
		
		if( $rec->category_id ) {
			$crec = new DBRecord(CATEGORY_TBL, "id", $rec->category_id );
			$arr['category'] = $crec->name_jp;
		}
		else $arr['category'] = 'すべて';
		
		$arr['use_regexp'] = $rec->use_regexp;
		$arr['mode'] = $RECORD_MODE[$rec->mode]['name'];
		
		array_push( $keywords, $arr );
	}
}
catch( Exception $e ) {
	exit( $e->getMessage() );
}

$smarty = new Smarty();

$smarty->assign( "keywords", $keywords );
$smarty->assign( "sitetitle", "自動録画キーワードの管理" );
$smarty->display( "keywordTable.html" );
?>