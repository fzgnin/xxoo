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
class BumenController extends AdminController {

    /**
     * 部门管理列表首页
     * @author 黄
     */
    public function index(){
	    
		$Department = M('bumen'); // 实例化Department对象
		
		$list = $Department->field('id,pid as pId,name')->select();
		
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

			//获取数据
			$data = array(
				'id'=>'',
				'pid'=>I('pid'),
				'bname'=>I('name'),
				'status'=>1,
				'sort'=>100,
				'name'=>I('name'),
				'charge'=>I('charge'),
			);
			
			//新增数据
			$res = M('bumen')->add($data);
			if($res)
			{
				$return['status'] = 1;
				$return['info'] = '操作成功';
				$return['id'] = $res;
				$this->ajaxReturn($return);
			}
			else
			{
				$this->error('操作失败！');
			}
		}else
		{	
			
			
			
			$department = M('bumen')->where('status = 1')->select();
			
			$department = D('Common/Tree')->toFormatTree($department,'name','id','pid');
			
			$department = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级部门')), $department);
			
			$this->assign('department', $department);
			
			
			$this->display();
		}
	    
    }
	
	public function bumenlist()
	{
		$filter = $this->query_array();

		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display();
	
	}
	
	public function edit()
	{
	  
	  $id = I('id');
	  
	  if(IS_POST)
		{

			//判断pid是否是自己或者自己的子类，如果是返回错误
			
			if($this->in_child_array(I('pid'),$id))
			{
				$this->error('不能把当前下级设定为上级！');
			}
			
		
			//获取数据
			$data = array(
				'id'=>$id,
				'pid'=>I('pid'),
				'bname'=>I('name'),
				'name'=>I('name'),
				'charge'=>I('charge'),
			);
			
			//print_r($data);exit;
			
			//新增数据
			$res = M('bumen')->save($data);
			if($res)
			{
				$this->success('操作成功！',U('Bumen/bumenlist'));
			}
			else
			{
				$this->error('操作失败！');
			}
		}else
		{
			  //获取该用户的所属部门和职位
			  $info = M('bumen')->where('id = '.$id)->find();
			  
			  $this->assign('info',$info);
			  
			  
			  $department = M('bumen')->where('status = 1')->select();
					
			  $department = D('Common/Tree')->toFormatTree($department,'name','id','pid');
			
			  $department = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级部门')), $department);
			
			  $this->assign('department', $department);
			  
			  $this->display();
			
		
		
		}	
	}
	
	public function change_status()
	{
		$id = I('id');	
		
		$status = 1 == I('status')?0:1;
		
		if(M('bumen')->where('id = '.$id)->setField('status',$status))
		{
			$this->success('操作成功！');
		}
		else
		{
			$this->error('操作失败！');
		}	
	}
	
	public function ajax_query()
	{
	
	  $filter = $this->query_array();

      $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出

	  $res_str = $this->fetch('Ajax:Bumen:bumen_list'); // 输出模板
	 
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
	   $where = 'id = '.$filter['id'];
	  }else
	  {
	   $where = '1';
	  }
	  
	  if($filter['keywords'])
	  {
	    $where .= " and name like '%".$filter['keywords']."%'";
	  }
	  
  
	  $list = M('bumen')->where($where)->order($limit)->select();
	  
	  foreach($list as $k=>$v)
	  {
	  	$list[$k]['pname'] = M('bumen')->where('id = '.$v['pid'])->getField('name');
	  }
	  
	  
	  $list = D('Common/Tree')->toFormatTree($list,'name','id','pid');
	  
	  //print_r($list);
	   
	   //分页
	  $count      = M('bumen')->where($where)->count();
	  
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
	
	private function in_child_array($pid,$id)
	{
		
		
		
		//取出id下所有子id
		$arr[] = $id;
		
		$id_array = array_merge($arr,$this->get_child_array($arr));
		
		if(in_array($pid,$id_array))
		{
			return true;
		}else
		{
			return false;
		}
	
	}
	
	private function get_child_array($array = array())
	{
		
		$where = 'pid in ('.implode(',',$array).')';
		
		$where .= ' and status = 1';

		$new_array = M('bumen')->where($where)->getField('id',true);
		
		if($new_array)
		{
			$new_array = array_merge($new_array,$this->get_child_array($new_array));
			
		}else
		{
			$new_array = array();
		}
		
		return $new_array;
	}

 
}