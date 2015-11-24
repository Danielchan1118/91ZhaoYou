<?php
	error_reporting(E_ALL);
	ini_set( 'display_errors', 'On' );
	echo "hello\n";
	include("ApkParser.class.php");
	$dataapk = "D:/wwwroot/s.91zhaoyou/Public/Uploads/App/6/541ab0960f7f4.apk";
	$dataapk = str_replace("/","\\",$dataapk);
	//echo file_get_contents($dataapk);
	$p = new \Think\ApkParser();// 实例化上传类    
	$res = $p->open($dataapk);
	if($res){
		$xml = $p->getXML();
		echo "xml:".$xml;
	}
?>