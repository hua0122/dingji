<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 15:04
 */

namespace app\wap\controller;

use app\common\controller\Fornt;
use think\Request;

class User extends Fornt
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $web_path = $_SERVER['SERVER_NAME'];
        $this->assign('web_path', "http://" . $web_path);
    }

    //个人中心
    public function index()
    {
        if (empty(session('openid'))) {
            header("Location:getwxinfo");
            exit();
        }

        $userwxinfo = model('WxUser')->where(array("openid" => session('openid')))->find();
        $this->assign("userwxinfo", $userwxinfo);

        return $this->fetch("template/wap/user/index.html");
    }

    //学习中心
    public function study()
    {
        $study = model('Student');
        $info = $study
            ->join('sent_grade', 'sent_grade.id=sent_student.grade_id', 'left')
            ->join('sent_area', 'sent_area.id=sent_student.area_id', 'left')
            ->field('sent_student.*,sent_grade.name as grade_name,sent_grade.price,sent_grade.content,sent_area.address,sent_area.thumb,sent_area.lat,sent_area.lng')
            ->where(array("openid" => session("openid")))->find();

            $this->assign("info", $info);

            //体检信息查询
            $code = model('Apply')
                ->join('sent_test', 'sent_test.id=sent_apply.code_id', 'left')
                ->join('sent_station', 'sent_station.id=sent_apply.station_id', 'left')
                ->field('sent_apply.*,sent_test.code,sent_test.verify,sent_station.name as station_name,sent_station.address,sent_station.lng,sent_station.lat')
                ->where(array("openid" => session("openid")))
                ->order("create_time desc")
                ->find();
            $this->assign('code', $code);


            return $this->fetch("template/wap/user/study.html");




    }

    //投诉建议
    public function feedback()
    {

        if (IS_POST) {
            $data = input('post.');
            if (empty($data['content'])) {
                $this->error('内容不能为空');
            }
            $openid = session("openid");
            $user = db("WxUser")->where(array("openid"=>$openid))->find();

            $data['name'] = $user['name'];
            $data['phone'] = $user['phone'];


            $feedback = model('Feedback');
            if ($data) {
                $res = $feedback->save($data);
                if ($res) {
                    return stripslashes(json_encode(array("code" => "200")));
                } else {
                    return json_encode(array("code" => "500"));

                }
            }

        } else {
            return $this->fetch("template/wap/user/feedback.html");
        }

    }


    //获取微信用户信息
    public function getwxinfo()
    {

        if (!empty($_REQUEST['data']) || !empty(session("openid"))) {
            $data = $_REQUEST['data'];
            //echo $data;
            $data = json_decode($data, JSON_UNESCAPED_UNICODE);
            $data = json_decode($data);

            if (session("openid")) {
                session("openid", session("openid"));
            } else {
                session("openid", $data->openid);//这一步保存openid到session
            }
            $info = model('WxUser')->where(array("openid" => $data->openid))->find();

            if (count($info) <= 0) {
                $sign = array(
                    "openid"  => $data->openid,
                    "nickname"  => $data->nickname,
                    'sex' => $data->sex,
                    "city"  => $data->city,
                    "country"  => $data->country,
                    "province"  => $data->province,
                    "headimgurl"  => $data->headimgurl,
                    "subscribe_time" => time()
                );

                model('WxUser')->save($sign);
            }

            $url = "../wap";
            header("Location:" . $url);
            exit();
        }

        $url = "/l_wx/getwxinfo.php?method=getUserInfo&state=djjx";
        header("Location:" . $url);
        exit();
    }


}