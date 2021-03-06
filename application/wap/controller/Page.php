<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31 0031
 * Time: 15:08
 */

namespace app\wap\controller;


use app\common\controller\Fornt;

class Page extends Fornt
{
    public function index(){
        $id   = $this->request->param('id');
        $model = model("Page");
        $res = $model->find($id);
        $this->assign('res',$res);
        $web_path = $_SERVER['SERVER_NAME'];
        $this->assign('web_path',$web_path);
        return $this->fetch("template/wap/page/index.html");

    }

    //用来显示单页的 返回首页 发现最美鼎吉
    public function detail(){
        $id   = $this->request->param('id');
        $model = model("Page");
        $res = $model->where(array('id'=>$id))->find();
        $this->assign('res',$res);
        $web_path = $_SERVER['SERVER_NAME'];
        $this->assign('web_path',$web_path);
        return $this->fetch("template/wap/page/detail.html");

    }
}