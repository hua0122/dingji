<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/1 0001
 * Time: 14:18
 */

namespace app\admin\controller;

use app\common\controller\Admin;
use think\db;

class School extends Admin
{
    public function index()
    {
        $this->setMeta("驾校管理");
        $list = db("School")->select();

        $this->assign("list", $list);
        return $this->fetch();

    }

    public function edit()
    {
        $link = model('School');
        $id = input('id', '', 'trim,intval');
        if (IS_POST) {
            $data = input('post.');
            if ($data) {
                $result = $link->save($data, array('id' => $data['id']));
                if ($result) {
                    return $this->success("修改成功！", url('School/index'));
                } else {
                    return $this->error("修改失败！");
                }
            } else {
                return $this->error($link->getError());
            }
        } else {

            $info = db('School')->field(true)->find($id);

            if (false === $info) {
                return $this->error('获取信息错误');
            }
            $this->assign('info', $info);


            $this->setMeta("编辑驾校信息");
            return $this->fetch();

        }
    }


    //添加
    public function add()
    {
        $school = model('School');

        if (IS_POST) {

            $data = input('post.');
            if (empty($data['name'])) {
                return $this->error("驾校名称不能为空");
            }

            if ($data) {

                    $result = $school->save($data);
                    $school_id = $school->getLastInsID();
                    if ($result) {

                        //添加学校的时候 要初始化数据 分别向document和page表新增数据以保证运营管理正常
                        $map['category_id'] = array("in", "(1,3,10,11,12)");
                        $map['school_id'] = "3";
                        $document = db("Document")->where($map)->select();
                        foreach ($document as $k => $v) {
                            unset($document[$k]['id']);
                            unset($document[$k]['deadline']);
                            unset($document[$k]['create_time']);
                            unset($document[$k]['update_time']);
                            $document[$k]['school_id'] = $school_id;
                        }

                        $page = db("Page")->where(array("school_id" => 1))->select();

                        foreach ($page as $k => $v) {
                            unset($page[$k]['id']);
                            unset($page[$k]['create_time']);
                            unset($page[$k]['update_time']);
                            $page[$k]['school_id'] = $school_id;
                        }

                        model("Document")->saveAll($document);

                        model("Page")->saveAll($page);


                        //添加学校的时候 要初始化数据 保证权限管理正常
                        $group = db("auth_group")->field('id')->select();
                        $detail = [];
                        foreach ($group as $k => $v) {
                            $detail[$k]['group_id'] = $v['id'];
                            $detail[$k]['school_id'] = $school_id;
                        }
                        model("AuthGroupDetail")->saveAll($detail);

                        return $this->success("添加成功！", url('School/index'));


                    }


            } else {
                return $this->error($school->getError());
            }
        } else {


            $this->setMeta("添加信息");
            return $this->fetch();
        }
    }

}