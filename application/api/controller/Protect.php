<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/5 0005
 * Time: 14:29
 */

namespace app\api\controller;


use app\common\controller\Api;

class Protect extends Api
{

    public function index(){


    }
    //登录
    public function login(){
        $tel = input('tel');
        $code = input('code');
        if(empty($tel)){
            return failMsg('电话号码不能为空');
        }
        if(empty($code)){
            return failMsg('验证码不能为空');
        }

        $department = model("Department")->where(array("phone"=>$tel))->find();
        if(!$department){
            $person = model("Person")->where(array("mobile"=>$tel))->find();
            if(!$person){
                return failMsg("电话号码不存在");
            }
            $data = model("Person")->field('username,code,mobile as phone')->where(array("mobile"=>$tel))->find();
            $data['type'] = "children";

        }else{
            $data = model("Department")->field('title as username,code,phone')->where(array("phone"=>$tel))->find();
            $data['type'] = "parent";
        }

        $code = model("Msg")->where(array("code"=>$code,"tel"=>$tel))->find();
        if(!$code){
            return failMsg("验证码不正确");
        }



        return success($data);




    }

    //发送验证码
    public function sent_msg(){
        $tel = input('tel');
        if(empty($tel)){
            return failMsg('手机号码不能为空');
        }
        $rand = rand_string(4,1);//生成随机数

        $data['tel'] = $tel;
        $data['code'] = $rand;

        $is_have = model("Msg")->where(array("tel"=>$tel))->find();
        if($is_have){
            $res = model("Msg")->save($data,array("id"=>$is_have['id']));
        }else{
            $res = model("Msg")->save($data);
        }


        if($res){
            return sent_code($tel,"欢迎登录保护系统，您的验证码是:".$rand);
        }else{
            return failMsg('发送失败');
        }



    }

    //设置推荐码
    public function set_code(){
        $tel = input('tel');
        $username = input('username');
        $code = input('code');
        if(empty($tel)){
            return failMsg('手机号码不能为空');
        }
        if(empty($username)){
            return failMsg('姓名不能为空');
        }
        if(empty($code)){
            return failMsg('推荐码不能为空');
        }

        $data['title'] = $username;
        $data['code'] = $code;

        $d = model("Department")->where(array("code"=>$code))->find();
        $p = model("Person")->where(array("code"=>$code))->find();
        if($d||$p){
            return failMsg("您下手慢了,试一试其他的吧",'301');
        }


        $data1['username'] = $username;
        $data1['code'] = $code;
        $res = model("Department")->save($data,array("phone"=>$tel));
        if(!$res){
            $re = model("Person")->save($data1,array("mobile"=>$tel));
            if(!$re){
                return failMsg('设置失败');
            }
        }

        return success();


    }

    //查询推荐码
    public function get_code(){
        $tel = input('tel');
        if(empty($tel)){
            return failMsg('手机号码不能为空');
        }

        $department = model("Department")->where(array("phone"=>$tel))->find();
        if(!$department){
            $person = model("Person")->where(array("mobile"=>$tel))->find();
            if(!$person){
                return failMsg("电话号码不存在");
            }
            $data = model("Person")->field('username,code,mobile as phone')->where(array("mobile"=>$tel))->find();

        }else{
            $data = model("Department")->field('title as username,code,phone')->where(array("phone"=>$tel))->find();
        }

        return success($data);


    }
    //查询学员是否已经被保护
    public function select_student(){
        $tel = input('tel');
        if(empty($tel)){
            return failMsg('学员手机号码不能为空');
        }
        //查询学员是否已经被保护
        $is_have = model("Protect")->where(array("tel"=>$tel))->find();
        if($is_have){
            return fail($is_have,"学员已经被保护");
        }else{
            return success();
        }
    }

    //意向资源保护
    public function resource_add(){
        $name = input('name');
        $tel = input('tel');
        $person = input('person');
        if(empty($person)){
            return failMsg('推荐人号码不能为空');
        }
        if(empty($tel)){
            return failMsg('学员手机号码不能为空');
        }
        //查询学员是否已经被保护
        $is_have = model("Protect")->where(array("tel"=>$tel))->find();
        if($is_have){
            return fail($is_have,"学员已经被保护");
        }


        $data['name'] = $name;
        $data['person'] = $person;
        $data['tel'] = $tel;
        $data['protect_time'] = time();
        //脱保时间规定  2,3,9,10月(3*24)  4,5,6,7,8,11,12,1(5*24)
        $m = date("m",time());
        if($m==2||$m==3||$m==9||$m==10){
            $deactivation_time = 3*24*3600;
        }else{
            $deactivation_time = 5*24*3600;
        }

        $data['deactivation_time'] = time()+$deactivation_time;
        $res = model("Protect")->save($data);
        if($res){
            return success($data);
        }else{
            return  failMsg('失败');
        }
    }



    //资源保护列表
    public function resource_list(){
        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        //根据时间分组排序 今天  明天  后天  最长五天就脱保了
        $where['status'] = '0';
        $where['person'] = $person;
        $list = model("Protect")->where($where)->select();
        if($list){
            $str_today = date('Y-m-d'); //获取今天的日期 字符串 
            $ux_today = strtotime($str_today); //将今天的日期字符串转换为 时间戳

            $ux_tomorrow = $ux_today+3600*24;// 获取明天的时间戳
            $str_tomorrow = date('Y-m-d',$ux_tomorrow);//获取明天的日期 字符串

            $ux_afftertomorrow = $ux_today+3600*24*2;// 获取后天的时间戳
            $str_afftertomorrow = date('Y-m-d',$ux_afftertomorrow);//获取后天的日期 字符串

            $weekarray=array("日","一","二","三","四","五","六");


            foreach ($list as $k=>$v){
                $str_in_format = date('Y-m-d',$v['deactivation_time']);//格式化为y-m-d的 日期字符串
                unset($list[$k]);
                if($str_in_format==$str_today)
                {
                    //今天
                    $list['today'][] = $v;
                }else if($str_in_format==$str_tomorrow)
                {
                    //明天
                    $list['tomorrow'][] = $v;
                }else if($str_in_format==$str_afftertomorrow)
                {
                    //后天
                    $list['afftertomorrow'][] = $v;
                }else{
                    //echo "星期".$weekarray[date("w",strtotime($v['deactivation_time']))];

                    $list["星期".$weekarray[date("w",strtotime($v['deactivation_time']))]][] = $v;

                }




            }
        }

        //已脱保名单
        $w ['status'] = array("neq","0");
        $deactivation = model("Protect")->where($w)->select();
        if($deactivation){
            foreach ($deactivation as $k=>$v){
                if($v['status'] == 1){
                    $deactivation[$k]['status'] = "主动脱保";
                }elseif($v['status']==2){
                    $deactivation[$k]['status'] = "超时脱保";
                }elseif($v['status']==3){
                    $deactivation[$k]['status'] = "助攻脱保";
                }else{
                    $deactivation[$k]['status'] = "已脱保并成交";
                }

            }
        }
        $data = array(
            "list"=>$list,
            "deactivation"=>$deactivation
        );

        return success($data);

    }

    //解除保护
    public function resource_remove(){
        $id = input('id','','trim,intval');
        if(!$id){
            return failIncomplete();
        }

        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        $is_have = model("Protect")->where(array("person"=>$person))->find($id);
        if(!$is_have){
            return failMsg("你没有权限操作");
        }


        $data['deactivation_time'] = time();
        $data['status'] = 1;
        $res = model("Protect")->save($data,array("id"=>$id));
        if($res){
            return success();
        }else{
            return failMsg();
        }
    }


    //开发记录添加
    public function develop_add(){
        $name = input('name');//学员姓名
        $tel = input('tel');//学员电话
        $channel = input('channel');//资源获取途径
        $deal_time = input('deal_time');//预计成交时间
        $remark = input('remark');
        $person = input('person');
        if(empty($person)){
            return failMsg('推荐人号码不能为空');
        }

        if(empty($tel)){
            return failMsg('学员手机号码不能为空');
        }
        if(empty($channel)){
            return failMsg('资源获取途径不能为空');
        }

        $is_have = model("Develop")->where(array("tel"=>$tel))->find();
        if($is_have){
            return failMsg('该学员已被添加','301');
        }


        $data['person'] = $person;
        $data['name'] = $name;
        $data['tel'] = $tel;
        $data['channel'] = $channel;
        $data['deal_time'] = $deal_time;
        $data['remark'] = $remark;
        $data['one'] = input('one');
        $data['two'] = input('two');
        $data['three'] = input('three');
        $data['develop_time'] = time();
        $res = model('Develop')->save($data);

        if($res){
            return success($data);
        }else{
            return  failMsg('失败');
        }


    }


    //开发记录列表
    public function develop_list(){
        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        $map['person']= $person;
        $list = model("Develop")->where($map)->select();
        if($list){
            $t = time();
            $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));//当天的开始时间
            $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));//当天的结束时间
            $monthstart = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));//当月开始时间
            $monthend = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));//当月结束时间
            //昨天起至时间
            $beginYesterday = mktime(0,0,0,date('m'),date('d')-1,date('y'));
            $endYesterday = mktime(0,0,0,date('m'),date('d'),date('y'))-1;

            foreach ($list as $k=>$v){
                unset($list[$k]);
                //今天时间
                if($v['develop_time'] >= $start && $v['develop_time'] <= $end){
                    $list['today'][] = $v;
                    //return '今天'.date('H:i',$v['develop_time']);
                }
                //昨天
                else if($v['develop_time'] >= $beginYesterday && $v['develop_time'] <= $endYesterday){
                    //return '昨天'.date('H:i',$v['develop_time']);
                    $list['yesterday'][] = $v;
                }
                //周几
                else if($v['develop_time'] >= $monthstart && $v['develop_time'] <= $monthend){
                    //return "周" . mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" ).date('H:i',$v['develop_time']);
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="一"){
                        $list['monday'][]=$v;
                    }
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="二"){
                        $list['tuesday'][] = $v;
                    }
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="三"){
                        $list['wednesday'][] = $v;
                    }
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="四"){
                        $list['thursday'][] = $v;
                    }
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="五"){
                        $list['friday'][] = $v;
                    }
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="六"){
                        $list['saturday'][] = $v;
                    }
                    if(mb_substr( "日一二三四五六",date("w",$v['develop_time']),1,"utf-8" )=="日"){
                        $list['Sunday'][] = $v;
                    }



                }else{
                    //return date('m-d',$v['develop_time']);
                    $list[date('m月d日',$v['develop_time'])][] = $v;
                }


            }

        }

        return success($list);

    }
    //开发记录查询
    public function develop_show(){
        $id = input('id','','trim,intval');
        if(!$id){
            return failIncomplete();
        }

        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        $is_have = model("Develop")->where(array("person"=>$person))->find($id);
        if(!$is_have){
            return failMsg("你没有权限操作");
        }


        $res = model('Develop')->find($id);

        return success($res);

    }


    //开发记录编辑
    public function develop_edit(){
        $id = input('id','','trim,intval');
        if(!$id){
            return failIncomplete();
        }

        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        $is_have = model("Develop")->where(array("person"=>$person))->find($id);
        if(!$is_have){
            return failMsg("你没有权限操作");
        }


        $name = input('name');//学员姓名
        $tel = input('tel');//学员电话
        $channel = input('channel');//资源获取途径
        $one = input('one');//跟进进度
        $two = input('two');//跟进进度
        $three = input('three');//跟进进度
        $deal_time = input('deal_time');//预计成交时间
        $remark = input('remark');



        if(empty($tel)){
            return failMsg('学员手机号码不能为空');
        }
        if(empty($channel)){
            return failMsg('资源获取途径不能为空');
        }

        $data['person'] = $person;
        $data['name'] = $name;
        $data['tel'] = $tel;
        $data['channel'] = $channel;
        $data['deal_time'] = $deal_time;
        $data['remark'] = $remark;
        $data['one'] = $one;
        $data['two'] = $two;
        $data['three'] = $three;
        $res = model('Develop')->save($data,array("id"=>$id));

        if($res){
            return success($data);
        }else{
            return  failMsg('失败');
        }
    }

    //成交学员列表(我的)
    public function deal_list(){
        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        $list = model("Protect")->where(array("status"=>4,"person"=>$person))->select();
        if($list){
            $list = timeTo($list,'deal_time');

        }
        return success($list);


    }
    //团队成交数据
    public function deal_team(){
        $person = input('person');
        if(empty($person)){
            return failLogin();
        }

        $d = db("Department")->where(array("phone"=>$person))->find();
        $p = db("Person")->where(array("department_id"=>$d['id']))->select();
        $str = implode(',',array_column($p,'mobile'));
        $w['person'] = array("in","(".$str.")");
        $list = db("Protect")->where($w)->select();
        if($list){
            $list = timeTo($list,'deal_time');
        }
        return success($list);

    }

}