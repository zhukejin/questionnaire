<?php
error_reporting(0);
//定义数据库配置
$dbConfig = array(
	"host" => 'localhost',
	'username' => 'root',
	'password' => 'wanqing',
	'dbName' => 'test',
	'tbName' => 'CMCC_DEMO'
);

//接收数据
$data = $_POST;
$dataArray = array();

//创建过滤器
foreach (@$data as $k => $v) {
	if (is_string($v)) {
		if (@eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $v)) {
			show_res($k . '包含违法字符', false);
		}
	} else {
		$v = join('|', $v);
	}

	$dataArray[$k] = $v;
}

//将生存下来的数据写入数据库, step 数据库连接
$con = mysql_connect($dbConfig['host'] , $dbConfig['username'] , $dbConfig['password']);
if (!$con) {
	show_res('数据库连接失败', false);
} 
mysql_select_db($dbConfig['dbName'], $con);
mysql_query("SET NAMES UTF8");

//查询是否提交过
$select = "SELECT id FROM " . $dbConfig['tbName'] . " WHERE phone = '" . $dataArray['phone'] . "'";
$num = num_rows($select, $con);
if ($num > 0) {
	show_res('您已提交过问卷，请勿重复提交', false);
}

//插入
$dataArray['add_time'] = date('Y-m-d H:m:s');
if (insert($dbConfig['tbName'], $dataArray, $con)) {
	show_res('提交成功');
} else {
	echo mysql_error();
	show_res('提交失败', false);
}

function num_rows($select, $con) {
	$results = mysql_query($select, $con);
	if(!is_bool($results)) {
	    $num = mysql_num_rows($results);
	    return $num;
	} else {
	    return 0;
	}
}

function insert($table, $dataArray, $con) {
	$field = $value = "";
    if( !is_array($dataArray) || count($dataArray)<=0) {
        halt('没有要插入的数据');
        return false;
    }
    while(list($key,$val)=each($dataArray)) {
        $field .= "$key,";
        $value .= "'$val',";
    }
    $field = substr( $field,0,-1);
    $value = substr( $value,0,-1);
    $sql = "INSERT INTO $table($field) VALUES($value)";
    if(!mysql_query($sql, $con)) return false;
        return true;
}


function halt($msg='') {
    // $msg .= "\r\n".mysql_error();
    die($msg);
}

function show_res($msg, $flag = true) {
	$arr = array(
		'msg' => $msg,
		'flag' => $flag
	);

	echo json_encode($arr);exit;
}

?>