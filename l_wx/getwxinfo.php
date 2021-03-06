<?php
define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT']);
include_once $_SERVER['DOCUMENT_ROOT'] . '/l_wx/weixin.php';

$wx = new Weixin_class();

$method=$_REQUEST['method']?$_REQUEST['method']:'getCode';


switch ($method) {//获取code
	case "getCode":
		$state = $_REQUEST['state']?$_REQUEST['state']:"STATE";
		$scope = $_REQUEST['scope']?$_REQUEST['scope']:"snsapi_base";


		if($state=='xxc_api'||$state == "xxchd_api"){
		    $appid = APPID_XXC;
        }
        elseif ($state == "djjx_api"||$state == "djjxhd_api"){
		    $appid = APPID_DJ;
        }
        elseif ($state == "ydxc_api"||$state == "ydxchd_api"){
            $appid = APPID;
        }
        elseif ($state == "jxyjx_api"||$state == "jxyjxhd_api"){
            $appid = APPID_JXY;
        }
        elseif ($state == "cnjx_api"||$state == "cnjxhd_api"){
            $appid = APPID_CN;
        }
        elseif ($state == "xnjx_api"||$state == "xnjxhd_api"){
            $appid = APPID_XN;
        }
        else{
		    $appid = APPID;
        }


		
		$redirect_uri = urldecode($_REQUEST["redirect_uri"])?urldecode($_REQUEST["redirect_uri"]):"http://" . $_SERVER['HTTP_HOST']."/l_wx/getwxinfo.php?method=getOpenId";//urlencode
		if (!empty($redirect_uri)) {
			$codeurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' .
								$appid  . '&redirect_uri='.
								$redirect_uri .'&response_type=code&scope='.$scope.'&state=' . 
								$state . '#wechat_redirect';
		}
		//echo $codeurl;exit();
		header("Location:$codeurl");
		break;
	//根据openid获取用户信息
	case "getUserInfoForOpenid":
		$openid = $_REQUEST['openid'];
		if (!empty($openid)) {
			$access_token = $wx->get_acctoken();
			$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token[0]."&openid=$openid&lang=zh_CN";
			//echo $url;
			$data = file_get_contents($url);
			$data = json_decode($data, JSON_UNESCAPED_UNICODE);
			if (!$data['errcode']) {
				echo json_encode($data, JSON_UNESCAPED_UNICODE);
				exit();
			} else {
				echo "";
			}
		}
		break;
		
	case "getUserInfo":
		$code = @$_REQUEST["code"];
		$state = $_REQUEST['state']?$_REQUEST['state']:"STATE";
        if($state=='xxc_api'||$state == "xxchd_api"){
            $appid = APPID_XXC;
            $appsecret = APPSECRET_XXC;
        }
        elseif ($state == "djjx_api"||$state == "djjxhd_api"){
            $appid = APPID_DJ;
            $appsecret = APPSECRET_DJ;
        }
        elseif ($state == "ydxc_api"||$state == "ydxchd_api"){
            $appid = APPID;
            $appsecret = APPSECRET;
        }
        elseif ($state == "jxyjx_api"||$state == "jxyjxhd_api"){
            $appid = APPID_JXY;
            $appsecret = APPSECRET_JXY;
        }
        elseif ($state == "cnjx_api"||$state == "cnjxhd_api"){
            $appid = APPID_CN;
            $appsecret = APPSECRET_CN;
        }
        elseif ($state == "xnjx_api"||$state == "xnjxhd_api"){
            $appid = APPID_XN;
            $appsecret = APPSECRET_XN;
        }
        else{
            $appid = APPID;
            $appsecret = APPSECRET;
        }
        //var_dump($state);
        //var_dump($appid);


		$getaccessurl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.
						$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
		//echo $getaccessurl;
		//exit;
		$data = file_get_contents($getaccessurl);
		$data = json_decode($data, true);

		if ($data["errcode"] == 40029 || $data['errcode'] == 41008) {//code无效重新获取

			$url = "/l_wx/getwxinfo.php?method=getCode&state=".$state."&scope=snsapi_userinfo&redirect_uri=".urlencode("http://" . $_SERVER['HTTP_HOST']."/l_wx/getwxinfo.php?method=getUserInfo");
			header("Location:" . $url);

			exit();
		}



		$infourl = "https://api.weixin.qq.com/sns/userinfo?access_token=".$data['access_token'] . 
			"&openid=" . $data['openid'] . "&lang=zh_CN";
			
		$user_info = file_get_contents($infourl);
		$data= json_encode($user_info, JSON_UNESCAPED_UNICODE);
		$_SESSION['user_info'] = $user_info;





        switch($state) {
			case 'addStudent':
				
				$url = "/s_user/student.php?method=showbm&data=".$data;
				header("Location:" . $url);
				exit();
				break;
			case 'showxxds':
				$url = "/s_user/tp.php?method=showxxds&data=".$data;
				header("Location:" . $url);
				exit();
				break; 
				
			case 'index':
				$url = "/client/getinfo.php?data=".$data;
				header("Location:" . $url);
				exit();
				break;

			case 'djjx':
				$url = "/user/getwxinfo?data=".$data;
				header("Location:" . $url);
				exit();
				break;
				//报名系统登录
            case 'djjx_api':
                $url = "/api/user/getwxinfo?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'xxc_api':
                $url = "/api/user/getwxinfo_xxc?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'ydxc_api':
                $url = "/api/user/getwxinfo_ydxc?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'cnjx_api':
                $url = "/api/user/getwxinfo_cnjx?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'xnjx_api':
                $url = "/api/user/getwxinfo_xnjx?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'jxyjx_api':
                $url = "/api/user/getwxinfo_jxyjx?data=".$data;
                header("Location:" . $url);
                exit();
                break;


                //活动
            case 'ydxchd_api':
                $url = "/api/user/getwxinfo_ydxchd?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'xxchd_api':
                $url = "/api/user/getwxinfo_xxchd?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'cnjxhd_api':
                $url = "/api/user/getwxinfo_cnjxhd?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'xnjxhd_api':
                $url = "/api/user/getwxinfo_xnjxhd?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'jxyjxhd_api':
                $url = "/api/user/getwxinfo_jxyjxhd?data=".$data;
                header("Location:" . $url);
                exit();
                break;
            case 'djjxhd_api':
                $url = "/api/user/getwxinfo_djjxhd?data=".$data;
                header("Location:" . $url);
                exit();
                break;
        }
		
		//var_dump($data);exit();
		//break;
		
	case "getOpenId":
		$code = @$_REQUEST["code"];
		$state = $_REQUEST['state']?$_REQUEST['state']:"STATE";

        if($state=='xxc_api'||$state == "xxchd_api"){
            $appid = APPID_XXC;
            $appsecret = APPSECRET_XXC;
        }
        elseif ($state == "djjx_api"||$state == "djjxhd_api"){
            $appid = APPID_DJ;
            $appsecret = APPSECRET_DJ;
        }
        elseif ($state == "ydxc_api"||$state == "ydxchd_api"){
            $appid = APPID;
            $appsecret = APPSECRET;
        }
        elseif ($state == "jxyjx_api"||$state == "jxyjxhd_api"){
            $appid = APPID_JXY;
            $appsecret = APPSECRET_JXY;
        }
        elseif ($state == "cnjx_api"||$state == "cnjxhd_api"){
            $appid = APPID_CN;
            $appsecret = APPSECRET_CN;
        }
        elseif ($state == "xnjx_api"||$state == "xnjxhd_api"){
            $appid = APPID_XN;
            $appsecret = APPSECRET_XN;
        }
        else{
            $appid = APPID;
            $appsecret = APPSECRET;
        }
		
		$getaccessurl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.
						$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
		//echo $getaccessurl;
		$data = file_get_contents($getaccessurl);
		$data = json_decode($data, true);
		if ($data["errcode"] == 40029 || $data['errcode'] == 41008) {//code无效重新获取
			$url = "/l_wx/getwxinfo.php?method=getCode&state=".$state;
			header("Location:" . $url);
			exit();
		}
		switch($state) {
			case 'addStudent':
				
				$url = "/s_user/student.php?method=showbm&openid=".$data["openid"];
				header("Location:" . $url);
				exit();
				break;
			case 'showxxds':
				$url = "/s_user/tp.php?method=showxxds&openid=".$data["openid"];
				header("Location:" . $url);
				exit();
				break; 
				
			case 'index':
				$url = "/client/getinfo.php?openid=".$data["openid"];
				header("Location:" . $url);
				exit();
				break; 
		}
		
		//var_dump($data);exit();
		//break;
}

/*
$url = "https://api.weixin.qq.com/connect/oauth2/access_token?appid=" . 
APPID ."&secret=" . 
APPSECRET ."&code=" . $code . "&grant_type=authorization_code";*/
						




