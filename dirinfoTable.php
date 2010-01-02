<?php
include_once('config.php');
include_once( INSTALL_PATH . '/Smarty/Smarty.class.php' );
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );
include_once( INSTALL_PATH . "/Reservation.class.php" );
include_once( INSTALL_PATH . "/Dirinfo.class.php" );
include_once( INSTALL_PATH . "/Settings.class.php" );

// 新規キーワードがポストされた
if( isset($_POST["add_dir"]) ) {
	if( $_POST["add_dir"] == 1 ) {
		$settings = Settings::factory();
		$dir_base = INSTALL_PATH.$settings->spool."/";
		$mkdir = $dir_base.$_POST['dir_name'];
		if (!is_dir($mkdir)) {
			try {
				`mkdir -p $mkdir`;
				$rec = new Dirinfo();
				$rec->dir_name = $_POST['dir_name'];
				if (isset($_POST['parent_id'])) {
					$rec->parent_id = $_POST['parent_id'];
				}
			}
			catch( Exception $e ) {
				exit( $e->getMessage() );
			}
		} else {
			$msg="すでに存在するディレクトリです";
		}
	}
}


$dirinfos = array();
try {
	$recs = Dirinfo::createRecords(DIRINFO_TBL);
	foreach( $recs as $rec ) {
		$arr = array();
		$arr['id'] = $rec->id;
		$arr['dir_name'] = $rec->dir_name;
		$arr['parent_id'] = $rec->parent_id;

		array_push( $dirinfos, $arr );
	}
}
catch( Exception $e ) {
	exit( $e->getMessage() );
}

$smarty = new Smarty();

$smarty->assign( "dirinfos", $dirinfos );
$smarty->assign( "sitetitle", "ディレクトリの管理" );
if (isset($msg)) {
	$smarty->assign( "dir_name", $_POST['dir_name'] );
	$smarty->assign( "msg", $msg );
}
$smarty->display( "dirinfoTable.html" );
?>