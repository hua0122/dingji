<?php
define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT']);
include_once $_SERVER['DOCUMENT_ROOT'] . '/l_wx/weixin.php';
$wx = new Weixin_class();

$content = file_get_contents('php://input');

$postObj = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

$arr = json_decode(json_encode($postObj),true);


$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/callback.txt", "w") or die("Unable to open file!");
fwrite($file, $content);
fclose($file);

 /*//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
$out_trade_no = $postObj->out_trade_no;
//$out_trade_no = $_REQUEST['out_trade_no'];
$res = $wx->orderquery($out_trade_no);*/
 $out_trade_no = $arr['out_trade_no'];
 $res = $wx->orderquery($out_trade_no);


//if ('SUCCESS' == $res->return_code && 'SUCCESS' == $res->trade_state) {
if('SUCCESS' == $arr['return_code'] && 'SUCCESS' == $arr['result_code']){



    if ('dj' == substr($out_trade_no, 0, 2)) {
		//$url = "http://ydxctrue.yidianxueche.cn/index.php?do=update_order_status&sn=".$out_trade_no;
        $url = WEBURL."/enlist/update_order_status&sn=".$out_trade_no;

		file_get_contents($url);
		echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
		exit();
	} else if('tj' == substr($out_trade_no, 0, 2)){
		//$url = "http://ydxctrue.yidianxueche.cn/index.php?do=update_tjorder_status&sn=".$out_trade_no;
        $url = WEBURL."/enlist/update_tjorder_status&sn=".$out_trade_no;
		file_get_contents($url);
		echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
		exit();
	}else if('yc' == substr($out_trade_no,0,2)){
	    $url = WEBURL."/api/activity/update_ycorder_status&sn=".$out_trade_no;
	    file_get_contents($url);
        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
        exit();
   }
	
	//处理票
	/*include_once $_SERVER['DOCUMENT_ROOT'] . '/l_db/drive/mysql.class.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/l_db/config/database.php';
	$db = DB::getDBClass();
	//查询记录
	$db -> where(array('out_trade_no' => $out_trade_no));
	$db->limit(1, 1);
	$content = $db->select('student');
	
	//var_dump($content);

	if (!empty($content[0]) && count($content)>0 && 2 == $content['0']['ispay']) {
		
		$db->where(array('id'=>$content['0']['id']));
		if ($db->update('student', array('ispay'=>'1', 'payinfo'=>json_encode($res, JSON_UNESCAPED_UNICODE)))) {
			//$db->where(array('id'=>$content['0']['id']));
			
			echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
			exit();
		}
	}*/
}
//file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/l_wx/wxpay.txt", json_encode($postObj));
//$wx->orderquery($out_trade_no);
