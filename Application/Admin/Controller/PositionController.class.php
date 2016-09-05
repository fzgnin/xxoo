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
class PositionController extends AdminController {

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
			$name = I('name');
					
			if(!$name)
			{
			 $this->error('名称不能为空');
			}
			
			//判断是否有该职位
			if(M('position')->where('name = '.$name)->find())
			{
			 $this->error('该职位已经存在！');
			}
			
			$data = array(
			'name'=>$name,
			);
			
			if(M('position')->add($data))
			{
			 $this->success('新增成功！',U('add'));
			}else
			{
			 $this->error('新增失败！');
			}
		}else
		{
			
			$this->display();
		}
	    
    }
	
	public function positionlist()
	{
	  $filter = $this->query_array();
	  
	  $list = $this->get_list($filter);
	
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
		
	  $this->display(); // 输出模板
	
	}
	
	public function edit()
	{
	  //获取id
	  
	  $id = I('id');
	  
	  if(!$id)
	  {
	   $this->error('id传输错误');
	  }
	  
	  $res_str = $this->fetch('Ajax:position_list');
	  
	  $data['info'] = $res_str;
	 
	  $data['success'] = 1;
	 
	  $this->ajaxReturn($data);
	  
	}
	
	public function delete()
	{
	 $id = I('id');
	 
	 if(!$id)
	 {
	  $this->error('无效id');
	 }
	 
	 if(M('position')->where('id = '.$id)->delete())
	 {
	   $this->success('删除成功！');
	 }else
	 {
	   $this->error('删除失败！');
	 }	
	}
	
	public function ajax_query()
	{
	
	  $filter = $this->query_array();

      $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出

	  $res_str = $this->fetch('Ajax:position_list'); // 输出模板
	 
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
	  
	  
	  if($filter['keywords'])
	  {
	    $where .= " name like '%".$filter['keywords']."%'";
	  }
	  
  
	  $list = M('position')->where($where)->page($filter['p'].',10')->order($limit)->select();
			   
	   //分页
	  $count      = M('position')->where($where)->count();
	  
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
	   'keywords' => I('keywords'),
	  );
	  
	 return $filter;
	
	}

 
}