<?php
include_once('config.php');
include_once(INSTALL_PATH."/DBRecord.class.php");
include_once(INSTALL_PATH."/reclib.php");
include_once(INSTALL_PATH."/Settings.class.php");
include_once(INSTALL_PATH."/Dirinfo.class.php");

$settings = Settings::factory();

if( !isset( $_POST['reserve_id'] ) ) {
	exit("Error: IDが指定されていません" );
}
$reserve_id = $_POST['reserve_id'];

$dbh = false;
if( $settings->mediatomb_update == 1 ) {
	$dbh = @mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
	if( $dbh !== false ) {
		$sqlstr = "use ".$settings->db_name;
		@mysql_query( $sqlstr );
		$sqlstr = "set NAME utf8";
		@mysql_query( $sqlstr );
	}
}

try {
	$rec = new DBRecord(RESERVE_TBL, "id", $reserve_id );
	
	if( isset( $_POST['title'] ) ) {
		$rec->title = trim( $_POST['title'] );
		if( ($dbh !== false) && ($rec->complete == 1) ) {
			$title = trim( mysql_real_escape_string($_POST['title']));
			$title .= "(".date("Y/m/d", toTimestamp($rec->starttime)).")";
			$sqlstr = "update mt_cds_object set dc_title='".$title."' where metadata regexp 'epgrec:id=".$reserve_id."$'";
			@mysql_query( $sqlstr );
		}
	}
	
	if( isset( $_POST['description'] ) ) {
		$rec->description = trim( $_POST['description'] );
		if( ($dbh !== false) && ($rec->complete == 1) ) {
			$desc = "dc:description=".trim( mysql_real_escape_string($_POST['description']));
			$desc .= "&epgrec:id=".$reserve_id;
			$sqlstr = "update mt_cds_object set metadata='".$desc."' where metadata regexp 'epgrec:id=".$reserve_id."$'";
			@mysql_query( $sqlstr );
		}
	}

	if( isset( $_POST['dir_id'] ) && $rec->dir_id != $_POST['dir_id']) {
		$dir_base = INSTALL_PATH.$settings->spool;
		if ($rec->dir_id != 0) {
			$fromdirinfo = new Dirinfo("id", $rec->dir_id );
			$fromdir = $dir_base."/".$fromdirinfo->dir_name;
		} else {
			$fromdir = $dir_base;
		}

		if ($_POST['dir_id'] != 0) {
			$todirinfo = new Dirinfo("id", $_POST['dir_id'] );
			$todir = $dir_base."/".$todirinfo->dir_name;
		} else {
			$todir = $dir_base;
		}
		$rec->dir_id = trim( $_POST['dir_id'] );

		$cmd = sprintf("mv %s/%s %s/%s",$fromdir,$rec->path,$todir,$rec->path);
printf("%s\n",$cmd);
		`$cmd`;
	}
}
catch( Exception $e ) {
	exit("Error: ". $e->getMessage());
}

exit("complete");

?>
