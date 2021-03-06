<?php
//网站根目录
include_once $_SERVER['DOCUMENT_ROOT'] . '/l_wx/config.php';
//微信信息
class Weixin_class {
	//查询订单
	function orderquery($out_trade_no,$school_id) {
		$url ="https://api.mch.weixin.qq.com/pay/orderquery";
		$appid = APPID;
		$mchid = MACID;
		$nonce_str = $this->getRandChar(15);

		$data = array(
		    'appid'=>$appid,
		    'mch_id'=>$mchid,
		    'nonce_str'=>$nonce_str,
		    'out_trade_no'=>$out_trade_no,
	    );
		$sign = $this->get_signature($data,$school_id);

		//var_dump($data);

		$post = "<xml version='1.0' encoding='utf-8'>
			<appid>$appid</appid>
			<mch_id>$mchid</mch_id>
			<nonce_str>$nonce_str</nonce_str>
			<out_trade_no>$out_trade_no</out_trade_no>
			<sign>$sign</sign>
		</xml>";
		//var_dump($post);
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		// grab URL, and printe
		$data = curl_exec($ch);
		curl_close($ch);

        //var_dump($data);

		$xml = simplexml_load_string($data);//转换post数据为simplexml对象
		$res = "";
		foreach($xml->children() as $child) {    //遍历所有节点数据
			$res .= ',"'.$child->getName() . '":"' . $child . '"';
		}

		$res = substr($res,1);
		$res = "{" . $res . "}";
		$res = json_decode($res);
		return $res;
	}
	//统一下单
	function unifiedorder($total_fee, $openid, $body, $out_trade_no,$school_id) {
		$url ="https://api.mch.weixin.qq.com/pay/unifiedorder";
        if($school_id==1){//鼎吉驾校
            $appid = APPID_DJ;
            $mchid = MACID_DJ;

        }elseif($school_id==2){//金西亚驾校
            $appid = APPID_JXY;
            $mchid = MACID_JXY;
        }elseif($school_id==3){//城南驾校
            $appid = APPID_CN;
            $mchid = MACID_CN;
        }elseif($school_id==4){//西南驾校
            $appid = APPID_XN;
            $mchid = MACID_XN;
        }
        elseif($school_id==5){ //秀学车
            $appid = APPID_XXC;
            $mchid = MACID_XXC;
        }
        elseif($school_id==6){ //易点学车
            $appid = APPID;
            $mchid = MACID;
        }

        else{
            $appid = APPID;
            $mchid = MACID;
        }

		$nonce_str = $this->getRandChar(15);
		$spbill_create_ip = SPBILL_CREATE_IP;
		$notify_url =  "http://" . $_SERVER['HTTP_HOST']."/l_wx/paycallback.php";
		$trade_type = "JSAPI";
		//var_dump($notify_url);

		$data = array(
		    'appid'=>$appid,
		    'mch_id'=>$mchid,
		    'nonce_str'=>$nonce_str,
		    'body'=>$body,
		    'out_trade_no'=>$out_trade_no,
		    'total_fee'=>$total_fee,
		    'notify_url'=>$notify_url,
		    'trade_type'=>$trade_type,
		    'openid'=>$openid,
		    'spbill_create_ip'=>SPBILL_CREATE_IP,
	    );

		$sign = $this->get_signature($data,$school_id);


		$post = "<xml version='1.0' encoding='utf-8'>
			<appid>$appid</appid>
			<mch_id>$mchid</mch_id>
			<nonce_str>$nonce_str</nonce_str>
			<body>$body</body>
			<openid>$openid</openid>
			<out_trade_no>$out_trade_no</out_trade_no>
			<total_fee>$total_fee</total_fee>
			<notify_url>$notify_url</notify_url>
			<trade_type>$trade_type</trade_type>
			<spbill_create_ip>$spbill_create_ip</spbill_create_ip>
			<sign>$sign</sign>
		</xml>";

        //var_dump($post);
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/callback.txt", "w") or die("Unable to open file!");
        fwrite($file, $post);
        fclose($file);


		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(!empty($post)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		// grab URL, and printe
		$data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Errno' . curl_error($ch);
        }
		curl_close($ch);

		//var_dump($url);
        //var_dump($data);

		$xml = simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA);//转换post数据为simplexml对象
		$res = "";

        //var_dump($xml);

        if(!$xml){
            return $res;
        }

		foreach($xml->children() as $child) {    //遍历所有节点数据
			$res .= ',"'.$child->getName() . '":"' . $child . '"';
		}

		$res = substr($res,1);
		$res = "{" . $res . "}";
		$res = json_decode($res);
		//var_dump($res);
		//exit;
		return $res;
	}


    //统一下单
    function unifiedorder_h5($total_fee, $body, $out_trade_no,$school_id) {
        $url ="https://api.mch.weixin.qq.com/pay/unifiedorder";
        if($school_id==1){//鼎吉驾校
            $appid = APPID_DJ;
            $mchid = MACID_DJ;

        }elseif($school_id==2){//金西亚驾校
            $appid = APPID_JXY;
            $mchid = MACID_JXY;
        }elseif($school_id==3){//城南驾校
            $appid = APPID_CN;
            $mchid = MACID_CN;
        }elseif($school_id==4){//西南驾校
            $appid = APPID_XN;
            $mchid = MACID_XN;
        }
        elseif($school_id==5){ //秀学车
            $appid = APPID_XXC;
            $mchid = MACID_XXC;
        }
        elseif($school_id==6){ //易点学车
            $appid = APPID;
            $mchid = MACID;
        }

        else{
            $appid = APPID;
            $mchid = MACID;
        }

        $nonce_str = $this->getRandChar(15);
        $spbill_create_ip = get_client_ip();
        $notify_url =  "http://" . $_SERVER['HTTP_HOST']."/l_wx/paycallback.php";
        $trade_type = "MWEB";
        $scene_info='{"h5_info": {"type":"Wap","wap_url": "yidianxueche.cn","wap_name": "易点学车"}}';

        $data = array(
            'appid'=>$appid,
            'mch_id'=>$mchid,
            'nonce_str'=>$nonce_str,
            'body'=>$body,
            'out_trade_no'=>$out_trade_no,
            'total_fee'=>$total_fee,
            'notify_url'=>$notify_url,
            'trade_type'=>$trade_type,
            'spbill_create_ip'=>get_client_ip(),
            'scene_info'=>$scene_info,
        );

        $sign = $this->get_signature($data,$school_id);


        $post = "<xml>
			<appid>$appid</appid>
			<mch_id>$mchid</mch_id>
			<nonce_str>$nonce_str</nonce_str>
			<body>$body</body>
			<out_trade_no>$out_trade_no</out_trade_no>
			<total_fee>$total_fee</total_fee>
			<notify_url>$notify_url</notify_url>
			<trade_type>$trade_type</trade_type>
			<spbill_create_ip>$spbill_create_ip</spbill_create_ip>
            <scene_info>$scene_info</scene_info>
			<sign>$sign</sign>
		</xml>";

        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        // grab URL, and printe
        $data = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($data);//转换post数据为simplexml对象
        $res = "";
        //var_dump($post);
        //var_dump($xml);

        foreach($xml->children() as $child) {    //遍历所有节点数据
            $res .= ',"'.$child->getName() . '":"' . $child . '"';
        }

        $res = substr($res,1);
        $res = "{" . $res . "}";
        $res = json_decode($res);
        //var_dump($res);
        //exit;
        return $res;
    }

	function curl_post_ssl($url, $vars, $second=30,$aHeader=array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, '1');
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);// 只信任CA颁布的证书
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);// 检查证书中是否设置域名，并且是否与提供的主机名匹配

		//以下两种方式需选择一种
		//echo ROOT_PATH . "<br/>";
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		//curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, $_SERVER['DOCUMENT_ROOT'].'/l_wx/cert/cert.pem');
		//默认格式为PEM，可以注释
		//curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, $_SERVER['DOCUMENT_ROOT'].'/l_wx/cert/key.pem');
		//curl_setopt($ch, CURLOPT_CAINFO, ROOT_PATH.'cert/rootca.pem'); // CA根证书（用来验证的网站证书是否是CA颁布）

		//第二种方式，两个文件合成一个.pem文件
		//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

		if( count($aHeader) >= 1 ) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}

		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		if($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}
	}

	//获取订单详情
	function get_order_info($order_id,$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/merchant/order/getbyid?access_token=$access_token";
		$post = array();
		$post["order_id"] = $order_id;
		$data = $this->file_get_contents_post($url, $post);

		$card_info = json_decode($data);

		return $card_info;
	}
	//生产永久二维码
	function create_ewm($post,$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
		$data = $this->file_get_contents_post($url, $post);

		$res = json_decode($data);
		return $res->url;
		/*
		if (isset($res->ticket)) {
			//通过ticket换取二维码
			$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $res->ticket;
			define('BASE_PATH',$_SERVER['DOCUMENT_ROOT']);

			$name = '/upload_file/erweima/ewm_'.time().".jpg";

			file_put_contents(BASE_PATH . $name,$url);
			return $name;
		}*/
	}
	//卡券code解码
	function card_decrypt($post) {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/code/decrypt?access_token=$access_token";
		$res = $this->file_get_contents_post($url, $post);
		$res = json_decode($res);
		return $res;
	}

	//卡卷使用
	function card_consume($post) {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/code/consume?access_token=$access_token";
		$res = $this->file_get_contents_post($url, $post);

		$res = json_decode($res);

		return $res;
	}

	//向用户发送消息
	function send_msg($post,$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$data = $this->file_get_contents_post($url, $post);

		$card_list = json_decode($data);

		return $card_list;
	}
	//向用户发送图文消息
	function send_msg_img($post,$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$data = $this->file_get_contents_post($url, $post);

		$card_list = json_decode($data);

		return $card_list;
	}

	//批量查询卡卷列表
	function get_card_list() {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/batchget?access_token=$access_token";
		$post = array();
		$post["offset"] = 0;
		$post["count"] = 0;
		$data = $this->file_get_contents_post($url, $post);

		$card_list = json_decode($data);

		return $card_list;
	}

	//卡卷详情
	function get_card_info($card_id) {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/get?access_token=$access_token";
		$post = array();
		$post["card_id"] = $card_id;
		$data = $this->file_get_contents_post($url, $post);

		$card_info = json_decode($data);

		return $card_info;
	}

	//导入门店
	function add_store() {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/location/batchadd?access_token=$access_token";
		$post = array();
		$post["offset"] = 0;
		$post["count"] = 0;
		$data = $this->file_get_contents_post($url, $post);

		$store_list = json_decode($data);

		return $store_list;
	}

	//拉取门店列表
	function get_store() {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/location/batchget?access_token=$access_token";
		$post = array();
		$post["offset"] = 0;
		$post["count"] = 0;
		$data = $this->file_get_contents_post($url, $post);

		$store_list = json_decode($data);

		return $store_list;
	}

	//获取用户openid
	function get_user_openid($school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$codeurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' .
						APPID  . '&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect';
		$data = file_get_contents($codeurl);
		$user_info = json_decode($data);
		return $user_info;
	}

	//获取用户信息
	function get_user_info($openid,$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
		$data = file_get_contents($url);
		$user_info = json_decode($data);

		return $user_info;
	}

	//获取关注用户列表
	function get_user_list($next_openid="",$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&next_openid=$next_openid";

		$data = file_get_contents($url);
		$user_list = json_decode($data);

		return $user_list;
	}

	//生成卡卷二维码
	function create_card_qrcode($card_id,$school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/qrcode/create?access_token=$access_token";
		$post = null;
		$post["action_name"] = "QR_CARD";
		$card = array();
		$card["card_id"] = $card_id;
		$action_info = array();
		$action_info["card"] = $card;
		$post["action_info"] = $action_info;
		$data = $this->file_get_contents_post($url, $post);
		$card_info = json_decode($data);

		return $card_info;
	}

	//生成带参数的二维码
	function create_qrcode_for_ticket($ticket,$school_id) {
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
        $data = file_get_contents($url);
		$card_info = json_decode($data);

		return $card_info;
	}

	//自定义菜单
	public function createMenu($school_id) {
		$access_token=$this->get_acctoken($school_id);
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
		$post = array();

		$button = array();
		$button["type"] = "click";
		$button["name"] = "小学霸网络评比";
		$button["key"] = "key_xbpb";

		$post["button"][] = $button;

		//echo $url;

		var_dump($this->file_get_contents_post($url, $post));
	}

	//获取最新颜色列表
	function get_colors() {
		$access_token=$this->get_acctoken();
		$access_token = $access_token[0];
		$url = "https://api.weixin.qq.com/card/getcolors?access_token=$access_token";

		$data = file_get_contents($url);
		$color_list = json_decode($data);
		if ("ok" == $color_list.errmsg) {
			$color_list = $color_list.colors;
		} else {
			$color_list = array();
		}
		return $color_list;
	}

	//获取acctoken
	function get_acctoken($school_id) {
        if($school_id==1){//鼎吉驾校
            $appid = APPID_DJ;
            $appsecret = APPSECRET_DJ;
            $access_token_name = "access_token_dj";

        }elseif($school_id==2){//金西亚驾校
            $appid = APPID_JXY;
            $appsecret = APPSECRET_JXY;
            $access_token_name = "access_token_jxy";
        }elseif($school_id==3){//城南驾校
            $appid = APPID_CN;
            $appsecret = APPSECRET_CN;
            $access_token_name = "access_token_cn";
        }elseif($school_id==4){//西南驾校
            $appid = APPID_XN;
            $appsecret = APPSECRET_XN;
            $access_token_name = "access_token_xn";
        }
        elseif($school_id==5){ //秀学车
            $appid = APPID_XXC;
            $appsecret = APPSECRET_XXC;
            $access_token_name = "access_token_xxc";
        }
        elseif($school_id==6){ //易点学车
            $appid = APPID;
            $appsecret = APPSECRET;
            $access_token_name = "access_token";
        }

        else{
            $appid = APPID;
            $appsecret = APPSECRET;
            $access_token_name = "access_token";
        }

		$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/".$access_token_name.".txt", "r+") or die("Unable to open file!");

		$access_token_info = fread($file,"500");
		$access_token_info = explode(",", $access_token_info);
		fclose($file);

		if (time() - $access_token_info[1] > 7000 ) {
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.
						$appid.'&secret='.$appsecret;
			//echo $url;
			$data = file_get_contents($url);
			$access_token = json_decode($data);
			$access_token_info[0] = $access_token->access_token;
			$access_token_info[1] = time();
			$txt = $access_token_info[0] . "," . $access_token_info[1];
			$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/".$access_token_name.".txt", "w") or die("Unable to open file!");
			fwrite($file, $txt);
			fclose($file);
		}

		return $access_token_info;
	}

	//获取api_ticket
	function get_api_ticket($school_id) {
		$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/api_ticket.txt", "r+") or die("Unable to open file!");
		$api_ticket_info = fread($file,"500");
		$api_ticket_info = explode(",", $api_ticket_info);
		fclose($file);

		if (time() - $api_ticket_info[1] > 7000 ) {

			$access_token=$this->get_acctoken($school_id);
			$access_token = $access_token[0];
			$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=wx_card';
			$data = file_get_contents($url);
			$api_ticket = json_decode($data);
			$api_ticket_info[0] = $api_ticket->ticket;
			$api_ticket_info[1] = time();
			$txt = $api_ticket_info[0] . "," . $api_ticket_info[1];
			$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/api_ticket.txt", "w") or die("Unable to open file!");
			fwrite($file, $txt);
			fclose($file);
		}

		return $api_ticket_info;
	}
	function getJsApiTicket($school_id) {
        if($school_id==1){//鼎吉驾校
            $api_ticket_js = "api_ticket_js_dj";

        }elseif($school_id==2){//金西亚驾校
            $api_ticket_js = "api_ticket_js_jxy";
        }elseif($school_id==3){//城南驾校
            $api_ticket_js = "api_ticket_js_cn";
        }elseif($school_id==4){//西南驾校
            $api_ticket_js = "api_ticket_js_xn";
        }
        elseif($school_id==5){ //秀学车
            $api_ticket_js = "api_ticket_js_xxc";
        }
        elseif($school_id==6){ //易点学车
            $api_ticket_js = "api_ticket_js";
        }

        else{
            $api_ticket_js = "api_ticket_js";
        }

        //var_dump($school_id);
        //var_dump($api_ticket_js);


	  	$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/".$api_ticket_js.".txt", "r+") or die("Unable to open file!");
		$api_ticket_info = fread($file,"500");
		$api_ticket_info = explode(",", $api_ticket_info);
		fclose($file);

            if (time() - $api_ticket_info[1] > 7000 ) {
                $access_token=$this->get_acctoken($school_id);
                $access_token = $access_token[0];

                $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token='.$access_token.'';
                $data = file_get_contents($url);
                $api_ticket = json_decode($data);
                //var_dump($api_ticket);
                $api_ticket_info[0] = $api_ticket->ticket;
                $api_ticket_info[1] = time();
                $txt = $api_ticket_info[0] . "," . $api_ticket_info[1];
                $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/".$api_ticket_js.".txt", "w") or die("Unable to open file!");
                fwrite($file, $txt);
                fclose($file);
            }


		return $api_ticket_info[0];
	}

	//卡券JsAPiTicket
	function getWxCardApiTicket() {
	  	$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/api_ticket_wxcard.txt", "r+") or die("Unable to open file!");

		$api_ticket_info = fread($file,"500");
		$api_ticket_info = explode(",", $api_ticket_info);
		fclose($file);
		if (time() - $api_ticket_info[1] > 7000 ) {
			$access_token=$this->get_acctoken();
			$access_token = $access_token[0];

			$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=wx_card';

			$data = file_get_contents($url);
			$api_ticket = json_decode($data);
			$api_ticket_info[0] = $api_ticket->ticket;
			$api_ticket_info[1] = time();
			$txt = $api_ticket_info[0] . "," . $api_ticket_info[1];
			$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/api_ticket_wxcard.txt", "w") or die("Unable to open file!");
			fwrite($file, $txt);
			fclose($file);
		}
		return $api_ticket_info[0];
	}
	//模拟post提交
	function file_get_contents_post($url, $post=array()) {
		$post = json_encode($post, JSON_UNESCAPED_UNICODE);
		//$post = str_replace("\/", "/",  $post);
		//echo $post;
	    $options = array(
	        'http' => array(
	            'method' => 'POST',
	            // 'content' => 'name=caiknife&email=caiknife@gmail.com',
	            'header' => "Content-type: application/x-www-form-urlencoded ",
	            'content' => $post,
	        ),
	    );
		//echo $url;
	    $result = file_get_contents($url, false, stream_context_create($options));

	    return $result;
	}


	/**
	 * 复制操作
	 *
	 */
	function _copy($src, $dst) {
	    if ( ! is_dir($src)) {
	        if ( ! copy($src, $dst)) {
	            return _log('Unable to copy files', $src);
	        }
	    } else {
	        mkdir($dst);
	        $ls = scandir($src);

	        for ($i = 0; $i < count($ls); $i++) {
	            if ($ls[$i] == '.' OR $ls[$i] == '..') continue;

	            $_src = $src.'/'.$ls[$i];
	            $_dst = $dst.'/'.$ls[$i];

	            if ( is_dir($_src)) {
	                if ( ! _copy($_src, $_dst)) {
	                    return _log('Unable to copy files', $_src);
	                }
	            } else {
	                if ( ! copy($_src, $_dst)) {
	                    return _log('Unable to copy files', $_src);
	                }
	            }
	        }
	    }
	    return TRUE;
	}

	/**
	 * 生成随机数
	 */
	 function getRandChar($length){
		   $str = null;
		   $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		   $max = strlen($strPol)-1;

		   for($i=0;$i<$length;$i++){
		    $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		   }

		   return $str;
	  }

	 //jsapi签名
	 public function get_js_signature($nonceStr, $timestamp, $url,$school_id) {
	    $jsapiTicket = trim($this->getJsApiTicket($school_id));
         //var_dump($url);
	    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
	    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		//echo $string;
	    $signature = sha1($string);
		

	    return $signature;
  }

	 //签名
	 function get_signature($data,$school_id){

		//签名步骤一：按字典序排序参数
		@ksort($data);
		$string = $this->ToUrlParams($data);
		//签名步骤二：在string后加入KEY
         if($school_id==1){//鼎吉驾校
             $key= KEY_DJ;

         }elseif($school_id==2){//金西亚驾校
             $key = KEY_JXY;
         }elseif($school_id==3){//城南驾校
             $key = KEY_CN;
         }elseif($school_id==4){//西南驾校
             $key = KEY_XN;
         }
         elseif($school_id==5){ //秀学车
             $key = KEY_XXC;
         }
         elseif($school_id==6){ //易点学车
             $key = KEY;
         }

         else{
             $key = KEY;
         }
         //var_dump($school_id);
         //var_dump($key);


		$string = $string . "&key=".$key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	 //卡券签名
	 function get_signature_for_card($data){
		$string = "";
		foreach ($data as $k => $v) {
			$string .= $v;
		}

		//$string = $string . "&key=3Zwc3qWorJor4tZQu0xKUpy8RVHnQcBp";
		//签名步骤三：加密
		//$string = $this->getWxCardApiTicket() . $string;
		$string = $data[0] . APPSECRET . $data[1];

		$string = sha1($string);

		return $string;
	}
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams($data)
	{
		$buff = "";
		foreach ($data as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = substr($buff, 0, strlen($buff)-1);
		return $buff;
	}

    function get_client_ip($type = 0, $adv = false) {
        $type      = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }

                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


    /***自定义模板消息****/

    //获取code  并根据回调地址获取openid 及access_token
   function get_code($url){
        $openid = $_SESSION['openid'];
        $code = input('code');
        if($code){
            $openid = $this->get_openid($code);
        }

        if(empty($openid)){
            $redirect_uri="http://" . $_SERVER['HTTP_HOST'].$url;
            $scope ="snsapi_base";
            $state = "STATE";
            $codeurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' .
                APPID  . '&redirect_uri='.
                $redirect_uri .'&response_type=code&scope='.$scope.'&state=' .
                $state . '#wechat_redirect';
            header("Location:" . $codeurl);
        }else{
            $_SESSION['openid']= $openid;
        }

        return $openid;


    }

    //根据code取openid
  function get_openid($code){
        $getaccessurl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.
            APPID.'&secret='.APPSECRET.'&code='.$code.'&grant_type=authorization_code';

        $data = file_get_contents($getaccessurl);
        $data = json_decode($data, true);

        return $data;
    }


    //根据openid获取用户信息
 function get_user($url){

        $data = $this->get_code($url);

        $infourl = "https://api.weixin.qq.com/sns/userinfo?access_token=".$data['access_token'] .
            "&openid=" . $data['openid'] . "&lang=zh_CN";

        $user_info = file_get_contents($infourl);
        $data= json_encode($user_info, JSON_UNESCAPED_UNICODE);

        return $data;

   }




    //设置所属行业
    function set_industry($school_id){
        $access_token=$this->get_acctoken($school_id);
        $access_token = $access_token[0];
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=".$access_token;
        $post['industry_id1'] = "16";
        $post['industry_id2'] = "17";
        $data = $this->file_get_contents_post($url,$post);
        return json_decode($data);
    }


    //获得模板ID

    function get_template_id($school_id){
        $access_token=$this->get_acctoken($school_id);
        $access_token = $access_token[0];
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=".$access_token;
        $data = $this->file_get_contents_post($url);
        return json_decode($data);
    }

    //获取模板列表
    function template_list($school_id){
        $access_token=$this->get_acctoken($school_id);
        $access_token = $access_token[0];
        $url="https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=".$access_token;
        $data = $this->file_get_contents_post($url);
        return json_decode($data);
    }

    //发送模板消息
    function send_template_msg($school_id,$openid,$account,$payable){
        $access_token=$this->get_acctoken($school_id);
        $access_token = $access_token[0];
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;

        $post = [
            "touser"=>$openid, //对方的openid，前一步获取
            "template_id"=>"N1GYA-V5pXNIcFsPlNxMQOEn8xAf7jyrCX-ZCqa4N4s", //模板id
            "miniprogram"=>["appid"=>"", //跳转小程序appid
                "pagepath"=>"pages/index/index"],//跳转小程序页面
            "data"=>[
                "first"=>[
                    "value"=> "恭喜你购买成功", //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
                "keyword1"=>[
                    "value"=> $account, //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
                "keyword2"=>[
                    "value"=> $payable, //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
                "keyword3"=>[
                    "value"=> $payable, //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
                "keyword4"=>[
                    "value"=> $payable, //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
                "keyword5"=>[
                    "value"=> 0, //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
                "remark"=>[
                    "value"=> "如有疑问，请联系当地网点", //自定义参数
                    "color"=> '#173177'//自定义颜色
                ],
            ]
        ];



        $data = $this->file_get_contents_post($url,$post);
        return json_decode($data);
    }
}
