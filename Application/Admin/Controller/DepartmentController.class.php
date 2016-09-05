<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huang <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;

/**
 * 部门列表
 * @author 黄
 */
class DepartmentController extends AdminController {

    /**
     * 部门管理列表首页
     * @author 黄
     */
    public function index(){
	    
		$Department = M('Department'); // 实例化Department对象
		
		$list = $Department->field('id,parent_id as pId,name')->select();
		
		//加click事件
		foreach($list as $k=>$v)
		{
		  $list[$k]['open'] = 'true';
		  $list[$k]['click'] = 'table_department('.$v['id'].')';
		}
					
		$d_list = json_encode($list);
		
		$this->assign('d_list',$d_list);// 赋值数据集
		
		//取出所有的会员
		
		$filter = $this->query_array();
		
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集

	    $this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
    }

 
   

    public function add(){
	    if(IS_POST)
		{
		$department = D('Department');
        
		//print_r($department);exit;
			if($department->create())
			{
			  if($department->add()!== false)
			  {
			  $this->success('插入成功');
			  }else
			  {
			  $this->error('插入失败');
			  }
			}else
			{
			 $this->error($department->getError());
			}
		 
		}else
		{
			$department = M('department')->field(true)->select();
			$department = D('Common/Tree')->toFormatTree($department);
			$department = array_merge(array(0=>array('id'=>0,'name'=>'顶级部门')), $department);
			$this->assign('department', $department);
			$this->display();
		}
	    
    }
	
	public function edit()
	{
	  $id = I('id');
	  
	  //获取该用户的所属部门和职位
	  $info = M('member')->field('department_id,pid')->where('uid = '.$id)->find();
	  
	  
	  $this->success('成功');
	
	
	}
	
	public function ajax_query()
	{
	
	  $filter = $this->query_array();

      $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出

	  $res_str = $this->fetch('Ajax:user_list'); // 输出模板
	 
	  $data['info'] = $res_str;
	 
	  $data['success'] = 1;
	 
	  $this->ajaxReturn($data);
	
	}
	
	//获取顾客列表封装函数
	
	public function get_list($filter)
	{
	  if($filter['order_by'])
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }  
	  
	  //部门筛选
	  if($filter['department_id'])
	  {
	   $where = 'department_id = '.$filter['department_id'];
	  }else
	  {
	   $where = '1';
	  }
	  
	  if($filter['keywords'])
	  {
	    $where .= " and nickname like '%".$filter['keywords']."%'";
	  }
	  
  
	  $list = M('member')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		foreach($list as $k=>$v)
		{
		  $list[$k]['d_name'] = M('department')->where('id = '.$v['department_id'])->getField('name');
		}
	   
	   //分页
	  $count      = M('member')->where($where)->count();
	  
	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show);
	
	}
	
	
	//定义查询变量
	private function query_array()
	{
	  $filter = array(
	   'p' => I('p',1),
	   'order_by'=> I('order_by'),
	   'sort_by'=> I('sort_by','ASC'),
	   'department_id' => I('department_id'),
	   'keywords' => I('keywords'),
	  );
	  
	 return $filter;
	
	}

 
}