<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Smarty/Smarty.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );

if( ! isset( $_GET['reserve_id'] ) ) exit("Error: 録画IDが指定されていません" );
$reserve_id = $_GET['reserve_id'];

try {
  $prec = new DBRecord( RESERVE_TBL, "id", $reserve_id );
  
  $crecs = DBRecord::createRecords( DIRINFO_TBL );
  $dirs = array();
  foreach( $crecs as $crec ) {
	$dir = array();
	$dir['id'] = $crec->id;
	$dir['dir_name'] = $crec->dir_name;
	$dir['selected'] = $prec->dir_id == $dir['id'] ? "selected" : "";
	
	array_push( $dirs , $dir );
  }
  
  $smarty = new Smarty();
  
  $smarty->assign( "title", $prec->title );
  $smarty->assign( "description", $prec->description );
  
  $smarty->assign( "dirs" , $dirs );
  
  $smarty->assign( "reserve_id", $prec->id );
  
  $smarty->display("editdialog.html");
}
catch( exception $e ) {
	exit( "Error:". $e->getMessage()." => ".print_r($e->getTrace()) );
}
?>