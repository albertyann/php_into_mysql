<?php
/*
define("DbHost", "*.*.*.*"); //数据库主机
define("DbUser", "********"); //数据库用户
define("DbPass", "********"); //数据库口令
define("DbName", "*"); //数据库名
*/

define("DbHost", "127.0.0.1"); //数据库主机
define("DbUser", "root"); //数据库用户
define("DbPass", ""); //数据库口令
define("DbName", "heping"); //数据库名

$filename = 'chaojibang_bak.sql';


$_errors = array();
$_warns = array();
$_success = array();

$content = file_get_contents($filename); // 读取数据
$content=preg_replace("/--.*\n/iU","",$content); //去掉注释
//$content=str_replace("ct_",TABLE_PRE,$content); // 去掉前缀

function read_table_sql($content){
	preg_match_all("/CREATE TABLE .*\(.*\).*\;/iUs",$content,$m);
	$tables = array();
	if(isset($m[0])){
		foreach($m[0] as $txt){
			preg_match('/CREATE TABLE (.*)\s\(/i', $txt, $mt);
			$str = $mt[0];
			$L = strpos($str, '`') + 1;
			$R = strrpos($str, '`');
			$table_name = substr($str, $L, $R - $L);
			$txt = str_replace('CHARSET=latin1', 'CHARSET=utf8', $txt);

			$tables[$table_name] = $txt;
		}
	}
	return $tables;
}



function read_insert_sql($content=''){
	preg_match_all("/INSERT INTO .*VALUES.*\(.*\).*\;/iUs",$content,$m);
	$insert_sql = array();
	if($m){
		foreach($m[0] as $mt){
			$insert_sql[] = $mt;
		}
	}
	return $insert_sql;
}

function execute_sql($arr, $link){
	foreach($arr as $key=>$sql){ 
		mysql_query($sql) or die(mysql_error());
		$_success[] = $sql;
	}
}





$link = mysql_connect(DbHost,DbUser,DbPass) or die("not connect db.");
mysql_set_charset('utf8',$link);

$creae_database_sql = 'CREATE DATABASE '.DbName;
if (mysql_query($creae_database_sql, $link)) {
    echo "Database my_db created successfully\n";
} else {
    $_warns[] = mysql_error();
}
mysql_select_db(DbName) or die("not select database.");



$tables = read_table_sql($content);
$inserts = read_insert_sql($content);
execute_sql($tables,$link);
execute_sql($inserts,$link);

echo "errors: ".count($_errors)."  warning: ".count($_warns)."  success: ".count($_success)."\n";;
if(count($_errors) > 0){
	foreach($_errors as $e){
		echo $e."\n";
	}
}else if($_warns){
	foreach($_warns as $e){
		echo $e."\n";
	}
}else{
	echo "安装成功";
}
?>
