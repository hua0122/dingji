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

class Enlist extends Fornt
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $web_path = $_SERVER['SERVER_NAME'];
        $this->assign('web_path', "http://" . $web_path);
    }

    //报名班级列表页
    public function index()
    {
        if (empty(session('openid'))) {
            header("Location:../user/getwxinfo");
            exit();
        }

        $area = model('Area');
        $area = $area->where(array("status" => 0))->select();

        $area_name = model('Area')->find(1);
        $this->assign('area_name', $area_name);

        $grade = model('Grade');
        $grade = $grade->where(array('area_id' => '1', "status" => 0))->select();

        //banner图
        $where = array("category_id" => 8);
        $banner = db("Document")->where($where)->limit('0,5')->select();
        $this->assign("banner", $banner);

        $this->assign('area', $area);
        $this->assign('grade', $grade);
        return $this->fetch("template/wap/enlist/index.html");
    }

    //详情页
    public function detail()
    {

        $id = input('id', '', 'trim,intval');
        $grade = model('Grade');
        $info = $grade->join('sent_area ', 'sent_area.id = sent_grade.area_id', 'left')
            ->field('sent_grade.*,sent_area.name as area_name,sent_area.thumb,sent_area.address,sent_area.lng,sent_area.lat')
            ->where(array('sent_grade.id' => $id))->find();

        $this->assign('info', $info);
        return $this->fetch("template/wap/enlist/detail.html");
    }


    //班级ajax列表
    public function ajax_list()
    {
        $area_id = input('city', '', 'trim,intval');
        $where = array("area_id" => $area_id, "status" => 0);
        $grade = model('Grade');
        $res = $grade->where($where)->select();
        echo json_encode($res);
    }

    //优惠券ajax
    public function yhq_code()
    {
        //查询优惠券是否有效
        $code = input('code', '', 'htmlspecialchars,trim');
        if (!empty($code)) {
            $res = model("Code")->where(array("code" => $code))->find();
            if ($res) {
                echo json_encode(array("code" => "200", "msg" => "有效", "res" => $res));
            } else {
                echo json_encode(array("code" => "400", "msg" => "无效"));
            }
        }


    }

    //推荐码ajax
    public function referral()
    {
        //查询推荐码是否有效
        $code = input('code', '', 'htmlspecialchars,trim');
        if (!empty($code)) {
            $res = db('Department')->where(array("code" => $code))->find();
            if ($res) {
                echo json_encode(array("code" => "200", "msg" => "有效", "res" => $res));
            } else {
                $res = db('Person')->where(array("code" => $code))->find();
                if ($res) {
                    echo json_encode(array("code" => "200", "msg" => "有效", "res" => $res));
                } else {
                    echo json_encode(array("code" => "400", "msg" => "推荐码无效,请重新输入"));
                }
            }
        }
    }

    //活动 ajax
    public function activity()
    {
        //查询活动优惠金额
        $activity_id = input('activity_id', '', 'trim,intval');
        if (!empty($activity_id)) {
            $res = db("Activity")->find($activity_id);
            if ($res) {
                echo json_encode(array("code" => "200", "msg" => "有效", "res" => $res));
            } else {
                echo json_encode(array("code" => "400", "msg" => "活动不存在或已下线"));
            }
        }
    }


    //报名页面
    public function sign()
    {
        $id = input('id', '', 'trim,intval');
        if (IS_POST) {
            $data = input('post.');
            if (!$data['name']) {
                return json_encode(array("code" => "400", "msg" => "姓名不能为空"));
            }
            if (!$data['phone']) {
                return json_encode(array("code" => "400", "msg" => "电话不能为空"));
            }
            if (!$data['card']) {
                return json_encode(array("code" => "400", "msg" => "身份证号码不能为空"));
            }

            //缴费类型
            if ($data['pay_type'] == 1) {
                //线上全额支付
                $data['unpaid'] = 0;//未付款
                $data['tuition_state'] = 1;//学费状态 1全款
            } else if ($data['pay_type'] == 2||$data['pay_type'] == 3||$data['pay_type'] == 4) {
                //活动减免 优惠券减免
                if(isset($data['activity_id'])&&isset($data['coupon'])){
                    //线上定金支付 //未付款
                    $data['unpaid'] = intval($data['payable']) - intval($data['payment']) - intval($data['activity_id']) - intval($data['coupon']);
                }elseif (isset($data['activity_id'])){
                    //线上定金支付 //未付款
                    $data['unpaid'] = intval($data['payable']) - intval($data['payment']) - intval($data['activity_id']);

                }elseif (isset($data['coupon'])){
                    //线上定金支付 //未付款
                    $data['unpaid'] = intval($data['payable']) - intval($data['payment']) - intval($data['coupon']);

                }
                else{
                    //线上定金支付 //未付款
                    $data['unpaid'] = intval($data['payable']) - intval($data['payment']);
                }

                $data['tuition_state'] = 2; //学费状态 2欠费
            } else {
                //缴费类型不对
                return json_encode(array("code" => "400", "msg" => "缴费类型不对"));
            }

            //查询是否报名  不能重复报名
            $is_have = model("Student")->where(array("openId"=>session('openid')))->find();
            if($is_have){
                return json_encode(array("code" => "500", "msg" => "不能重复报名"), JSON_UNESCAPED_SLASHES);
            }


            $data['sign_date'] = time();//报名时间
            $data['openId'] = session('openid');
            $data['sn'] = "dj_" . rand_string(20);//订单编号
            $res = model("Student")->save($data);
            if ($res) {
                //保存姓名、电话到用户表
                $data_user['name'] = $data['name'];
                $data_user['phone'] = $data['phone'];
                $where['openid']  = session("openid");
                model("WxUser")->save($data_user,$where);


                $openid = session("openid");
                $total_fee = $data['payment'] * 100;


                if (!empty($total_fee) && $total_fee > 0 && !empty($openid)) {
                    $total_fee = 0.01 * 100;
                    include_once $_SERVER['DOCUMENT_ROOT'] . '/l_wx/weixin.php';
                    $wx = new \Weixin_class();
                    $msg = "我们会在两个工作日内联系您，请保持手机畅通，耐心等待，谢谢！";
                    $unifiedOrderResult = $wx->unifiedorder($total_fee, $openid, '驾校学车', $data['sn']);
                    //var_dump($unifiedOrderResult);
                    $timeStamp = intval(time() / 10);
                    $url = $_SERVER["HTTP_REFERER"];
                    //echo $url;
                    $nonceStr = $wx->getRandChar(15);
                    //echo $url;
                    $signature = $wx->get_js_signature($nonceStr, $timeStamp, $url);
                    //var_dump($unifiedOrderResult);exit();
                    $package = "prepay_id=" . $unifiedOrderResult->prepay_id;
                    $data = array("timeStamp" => $timeStamp, "nonceStr" => $nonceStr,
                        "package" => $package, "signType" => "MD5", "appId" => 'wx09e39aed7d3c3912');

                    $paySign = $wx->get_signature($data);
                    $content = array('package' => $package, 'paySign' => $paySign, 'appId' => 'wx09e39aed7d3c3912', 'timestamp' => $timeStamp, 'nonceStr' => $nonceStr, 'signature' => $signature);
                    return json_encode(array("code" => "200", "msg" => "支付成功",'content' => $content), JSON_UNESCAPED_SLASHES);
                }else{
                    return json_encode(array("code" => "2000", "msg" => "报名成功"), JSON_UNESCAPED_SLASHES);
                }


            } else {
                return json_encode(array("code" => "400", "msg" => "报名失败"), JSON_UNESCAPED_SLASHES);
            }


        } else {
            //班级信息展示
            $grade = model('Grade')
                ->join('sent_area', 'sent_area.id=sent_grade.area_id', 'left')
                ->field('sent_grade.*,sent_area.name as area_name,sent_area.id as area_id')
                ->where(array("sent_grade.id" => $id))
                ->find();
            $this->assign('grade', $grade);
            //活动信息展示
            $activity = model('Activity')->limit(0, 3)->select();
            $this->assign('activity', $activity);


            return $this->fetch("template/wap/enlist/sign.html");
        }

    }

    //支付成功页面
    public function cg()
    {

        //体检站展示
        $station = model("Station")->select();
        $this->assign("station", $station);


        return $this->fetch("template/wap/enlist/success.html");
    }
    //支付失败
    public function fail(){
        return $this->fetch("template/wap/enlist/fail.html");
    }

    //报名成功页面
    public function sign_success()
    {

        //体检站展示
        $station = model("Station")->select();
        $this->assign("station", $station);


        return $this->fetch("template/wap/enlist/sign_success.html");
    }

    //报名失败
    public function sign_fail(){

        return $this->fetch("template/wap/enlist/sign_fail.html");
    }


    //学车协议
    public function agreement()
    {
        if (IS_POST) {
            $data = input('post.');
            session('agreement_name', $data);
            return json_encode($data);
        } else {
            $this->assign('agreement', session('agreement_name'));
            return $this->fetch("template/wap/enlist/agreement.html");
        }

    }


    //申请体检页面
    public function test()
    {
        if (IS_POST) {
            include_once $_SERVER['DOCUMENT_ROOT'] . '/l_wx/weixin.php';

            $wx = new \Weixin_class();

            $data = input('post.');

            $price = input('money');
            $data['openid'] = session("openid");
            $data['create_time'] = time();



            //先查询是否已经申请过 如果已经申请过 多次申请需缴费  首次申请免费
            $is_have = model("Apply")->where(array("openid"=>session("openid")))->find();
            if($is_have){
                $res = model("Apply")->save($data);
                $insert_id = model("Apply")->getLastInsID();
                if ($res) {
                    $code = 0;
                    $openid = session("openid");
                    $sn = "tj_" . rand_string(20);//订单编号
                    $total_fee = $price * 100;
                    if (!empty($total_fee) && $total_fee > 0 && !empty($openid)) {
                        $code = 1;
                        $msg = "我们会在两个工作日内联系您，请保持手机畅通，耐心等待，谢谢！";
                        $unifiedOrderResult = $wx->unifiedorder($total_fee, $openid, '驾校学车', $sn);
                        //var_dump($unifiedOrderResult);
                        $timeStamp = intval(time() / 10);
                        $url = $_SERVER["HTTP_REFERER"];
                        //echo $url;
                        $nonceStr = $wx->getRandChar(15);
                        //echo $url;
                        $signature = $wx->get_js_signature($nonceStr, $timeStamp, $url);
                        //var_dump($unifiedOrderResult);exit();
                        $package = "prepay_id=" . $unifiedOrderResult->prepay_id;
                        $data = array("timeStamp" => $timeStamp, "nonceStr" => $nonceStr,
                            "package" => $package, "signType" => "MD5", "appId" => 'wx09e39aed7d3c3912');

                        $paySign = $wx->get_signature($data);
                        $content = array('package' => $package, 'paySign' => $paySign, 'appId' => 'wx09e39aed7d3c3912', 'timestamp' => $timeStamp, 'nonceStr' => $nonceStr, 'signature' => $signature);

                        $sn = array("sn" => $sn);
                        $where = array("id" => $insert_id);

                        model("Apply")->save($sn,$where);

                    }
                    if (empty(session("openid"))) {
                        $code = -1;
                        $msg = "错误";
                        $content ="";
                    }
                    echo json_encode(array('code' => $code, 'msg' => $msg, 'content' => $content), JSON_UNESCAPED_UNICODE);

                }

            }else{
                $data['is_pay'] = "1";
                $res = model("Apply")->save($data);
                $insert_id = model("Apply")->getLastInsID();
                if($res){
                    //查询订单的体检站和电话号码
                    $apply =model("Apply")->field('sent_apply.*,sent_station.outfit_id')
                        ->join('sent_station','sent_station.id=sent_apply.station_id','left')
                        ->where(array("sent_apply.id"=>$insert_id))->find();
                    //查询未分配的体检码
                    $code_info = model("Test")->where(array("status"=>0,"outfit_id"=>$apply['outfit_id']))->limit(0,1)->select();
                    $code_id = $code_info[0]['id'];
                    $is_use = array('status'=>1,"username"=>$apply['name'],"phone"=>$apply['phone'],"apply_time"=>time());
                    $where = array('id'=>$code_id);
                    //print_r($code_info); exit;
                    //修改体检码表的使用状态 修改为已分配
                    model('Test')->save($is_use,$where);
                    //赋值体检申请表里面的体检码
                    model("Apply")->where(array("id"=>$insert_id))->setField('code_id',$code_id);


                    echo json_encode(array('code'=>'200','msg'=>'申请成功'),JSON_UNESCAPED_UNICODE);
                }else{
                    echo json_encode(array('code'=>'500','msg'=>'申请失败'),JSON_UNESCAPED_UNICODE);
                }

            }
        } else {
            //体检站展示
            $station = model("Station")->select();
            $this->assign("station", $station);

            //查询对应的openid是否已填写报名时的姓名和电话  如果没有则需填写
            $is_have = model("WxUser")->where(array("openid"=>session("openid")))->find();

            $this->assign("is_have",$is_have);


            return $this->fetch("template/wap/enlist/test.html");
        }


    }

    //支付成功回调函数
    public function pay_success(){
        return $this->fetch("template/wap/enlist/pay_success.html");
    }

    //支付失败回调函数
    public function pay_fail(){
        return $this->fetch("template/wap/enlist/pay_fail.html");
    }

    //支付成功之后 修改体检申请表信息
    public function update_tjorder_status(){

        $sn =$_GET['sn'];
        if (empty($sn)) {
            exit();
        }
        $updt = array("is_pay"=>1,"pay_time"=>time());

        $where = array("sn"=>$sn);
        model("Apply")->save($updt,$where);
        //查询订单的体检站和电话号码
        $apply =model("Apply")->field('sent_apply.*,sent_station.outfit_id')
            ->join('sent_station','sent_station.id=sent_apply.station_id','left')
            ->where($where)->find();
        //查询未分配的体检码
        $code_info = model("Test")->where(array("status"=>0,"outfit_id"=>$apply['outfit_id']))->limit(0,1)->select();
        $code_id = $code_info[0]['id'];
        $is_use = array('status'=>1,"username"=>$apply['name'],"phone"=>$apply['phone'],"apply_time"=>time());
        $where = array('id'=>$code_id);
        //print_r($code_info); exit;
        //修改体检码表的使用状态 修改为已分配
        model('Test')->save($is_use,$where);
        $where = array("sn"=>$sn);
        //赋值体检申请表里面的体检码
        model("Apply")->where($where)->setField('code_id',$code_id);


    }

    //支付成功后 修改报名信息表信息
    public function update_order_status(){


        $sn =$_GET['sn'];
        if (empty($sn)) {
            exit();
        }

        $sign = model("Student")->where(array("sn"=>$sn))->find();
        if ($sign['is_pay'] == 1) {
            exit();
        }

        $updt = array("is_pay"=>1,"pay_date"=>time());

        $where = array("sn"=>$sn);

        //修改学员报名表的支付状态
        model("Student")->save($updt,$where);


        //增加活动报名人数
        if(isset($sign['activity_id'])){
            model("Activity")->where(array("id"=>$sign['activity_id']))->setInc('number',1);
        }

        //增加团队招生数
        if(isset($sign['inviter'])){
            $res = db("Department")->find($sign['inviter']);
            if($res){
                model("Department")->where(array("id"=>$sign['inviter']))->setInc('number',1);
                model("Department")->where(array("id"=>$sign['inviter']))->setInc('total',1);
            }else{
                $person = db("Person")->find($sign['inviter']);
                if($person){
                    model("Person")->where(array("id"=>$sign['inviter']))->setInc('number',1);
                    model("Department")->where(array("id"=>$person['department_id']))->setInc('total',1);
                }

            }
        }

        //修改优惠券使用状态
        if(isset($sign['coupon'])){
            model("Code")->where(array("id"=>$sign['coupon']))->setField('status','1');
        }


        //查询保护系统学员 如果该学员存在  修改其状态
        $p = db("Protect")->where(array("tel"=>$sign['phone']))->find();

        if($p){

            if(isset($sign['inviter'])){
                $d = db("Department")->find($sign['inviter']);
                if($d){
                    if($d['phone']==$p['person']){//如果报名时填写的推荐人号码和添加保护人的号码相同就是直接成交
                        db("Protect")->where(array("tel"=>$sign['phone']))->setField('status','4');//已成交
                    }else{
                        db("Protect")->where(array("tel"=>$sign['phone']))->setField('status','3'); //助攻
                    }
                }else{
                    $person = db("Person")->find($sign['inviter']);
                    if($person){
                        if($person['mobile']==$p['person']){
                            db("Protect")->where(array("tel"=>$sign['phone']))->setField('status','4');//已成交
                        }else{
                            db("Protect")->where(array("tel"=>$sign['phone']))->setField('status','3'); //助攻
                        }
                    }

                }
            }

        }




        //发送模板消息
        include_once $_SERVER['DOCUMENT_ROOT'] . '/l_wx/weixin.php';
        $wx = new \Weixin_class();
        $content = $wx->send_template_msg($sign['school_id'],$sign['openid'],$sign['name'],$sign['payable']);

        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/l_wx/send_template_msg.txt", "w") or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);






    }

    //活动预存 支付成功回调函数
    public function update_ycorder_status(){
        $sn =$_GET['sn'];
        if (empty($sn)) {
            exit();
        }

        $activity = model("ActivityUser")->where(array("sn"=>$sn))->find();
        if ($activity['is_pay'] == 1) {
            exit();
        }


        $updt = array("is_pay"=>1,"pay_date"=>time());

        $where = array("sn"=>$sn);

        //修改总的优惠金额
        $r = model("ActivityUser")->where($where)->setInc('total_amount',300);
        //if(!$r) return failMsg();

        //修改活动表的支付状态
        model("ActivityUser")->save($updt,$where);

        //修改邀请人数
        if($activity['pid']>0){
            model("ActivityUser")->where(array("id"=>$activity['pid']))->setInc('num',1);
        }


    }


}