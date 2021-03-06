<?php
// +----------------------------------------------------------------------
// | SentCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.tensent.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: molong <molong@tensent.cn> <http://www.tensent.cn>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\common\controller\Admin;
use think\Request;
class Person extends Admin {

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->schoolid = cookie("schoolid");
    }

    /**
	 * 人员管理首页
	 * @author
	 */
	public function index() { 
	    $id   = input('id', '', 'trim,intval');
	    //查询是否存在子集ID,若存在取出来，若不存在就是其本身
	    /*$departments = db('Department')->field('id')->where(array("pid"=>$id))->select();
	    $ids="";
	    if($departments){
	        foreach ($departments as $k=>$v){
	            $v=join(',', $v);
	            $temp[] = $v;
	        }
	      
	        foreach($temp as $v){
	            $ids.=$v.",";
	        }
	        $ids=substr($ids,0,-1);  //利用字符串截取函数消除最后一个逗号
	    }
	    if($id){
	        $id=$id.",".$ids;
	    }

	    

	    if(!empty($id)){
	        $map['sent_person.department_id'] = array('in',$id);
	    }*/

        $map=array();

        $keyword = input('keyword','', 'htmlspecialchars,trim');
        if(!empty($keyword)){
            $map['username|mobile|sent_person.code'] = array('like', '%' .$keyword . '%');

        }


        if(!empty($id)) {
            $map['sent_person.department_id'] = $id;
        }



        //$map['sent_person.status'] = array('egt', 0);

        if(isset($this->schoolid)){
            $map['school_id'] = $this->schoolid;
        }else{

            //根据角色ID查询当前学校ID
            $role_id = db('AuthGroupAccess')->where(array("uid"=>session("user_auth.uid")))->find();
            $where['group_id'] = $role_id['group_id'];
            $where['rules'] = array('<>','');
            $school_default = db("AuthGroupDetail")
                ->join('sent_school','sent_school.id=sent_auth_group_detail.school_id','left')
                ->field('sent_auth_group_detail.*,sent_school.name')
                ->where($where)->find();


            $map['school_id'] = $school_default['school_id'];
        }



        $order = "id desc";
		$list  = model('Person')
		->join('sent_department','sent_person.department_id=sent_department.id','left')
		->field('sent_person.id,sent_person.username,sent_person.email,sent_person.mobile,sent_person.sex,sent_person.create_time,sent_person.status,sent_person.department_id,sent_person.status,sent_department.title,sent_person.code,sent_person.number')
		->where($map)
		->order($order)->paginate(15);


		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		
		$this->assign($data);
		$this->setMeta('队员信息');
		
		
		
		return $this->fetch();
	}

	/**
	 * 添加人员
	 * @author colin <molong@tensent.cn>
	 */
	public function add() {
		$model = \think\Loader::model('Person');
		//$department = model('Person');
		if (IS_POST) {
		    $data = input('post.');
		   
			if ($data) {
				unset($data['id']);
				$result = $model->save($data);
				if ($result) {
					return $this->success("添加成功！", url('Person/index'));
				} else {
					return $this->error($model->getError());
				}
			} else {
				return $this->error($model->getError());
			}
			
			
			
			if ($result) {
				return $this->success('队员添加成功！', url('admin/person/index'));
			} else {
				return $this->error($model->getError());
			}
		} else {
		    /*
			$data = array(
				'keyList' => $model->addfield,
			);
			$this->assign($data);
			*/
			$this->assign('info', array('department_id' => input('department_id')));
			$departments = db('Department')->select();
			$tree  = new \com\Tree();
			$departments = $tree->toFormatTree($departments);
			
			if (!empty($departments)) {
			    $departments = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $departments);
			} else {
			    $departments = array(0 => array('id' => 0, 'title_show' => '顶级菜单'));
			}
			
			$this->assign('Departments', $departments);
			
			
			$this->setMeta("添加队员");
			return $this->fetch('edit');
		}
	}

	/**
	 * 修改昵称初始化
	 * @author huajie <banhuajie@163.com>
	 */
	public function edit() {
		$model = model('Person');
		$id   = input('id', '', 'trim,intval');
		if (IS_POST) {
		$data = input('post.');
			if ($data) {
				$result = $model->save($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('Person/index'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($model->getError());
			}
			
		} else {
		    /*
			$info = $this->getUserinfo();

			$data = array(
				'info'    => $info,
				'keyList' => $model->editfield,
			);
			$this->assign($data);
			
			
			*/
		    
		    $info = array();
		    /* 获取数据 */
		    $info  = db('Person')->field(true)->find($id);
		    $departments = db('Department')->field(true)->select();
		    $tree  = new \com\Tree();
		    $departments = $tree->toFormatTree($departments);
		    	
		    $departments = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $departments);
		    $this->assign('Departments', $departments);
		    if (false === $info) {
		        return $this->error('获取队员信息错误');
		    }
		  
		    $this->assign('info', $info);
		    
		    
		    
		    
			$this->setMeta("编辑人员");
			return $this->fetch('edit');
		}
	}

	/**
	 * del
	 * @author colin <colin@tensent.cn>
	 */
	public function del($id) {
		$uid = array('IN', is_array($id) ? implode(',', $id) : $id);
		//获取管理员信息
		$find = $this->getUserinfo($uid);
		model('Person')->where(array('uid' => $uid))->delete();
		return $this->success('删除人员成功！');
	}

	
}