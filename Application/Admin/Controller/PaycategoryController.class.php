<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huang 2374266244
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;

/**
 * 后台收支类别
 * huang
 */
class PaycategoryController extends AdminController {

    /**
     * 收支类别列表首页
     * 黄
     */
	public function index(){
	
	
		$filter = $this->query_array();
		
		$list = $this->guest_list($filter);
		
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
	
	}
	
	
	/**
	*   收支类别列表
	*   黄线可
	**/
	
	public function paycategorylist()
	{
		//条件数组
		
		$filter = $this->query_array();
		
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
	
	}
	

	public function add(){
		
		//取出所有科目
		$plist = M('paycategory')->field('id,name')->select();
		
		$this->assign('plist',$plist);
	
		$this->display(); // 输出模板
	
	}
	
	
	//编辑收支类别
	Public function edit(){
		
		
		$id=I('id');
		
		$info = M('paycategory')->where('id = '.$id)->find();
		
		$this->assign('info',$info);
		
		//取出所有科目
		$plist = M('paycategory')->field('id,name')->select();
		
		$this->assign('plist',$plist);
		
		$this->display();
	}
	
	
	
	//写入数据库
    Public  function  insert(){
	
		$data = array( //获取数据
			'id' =>I('id'),
			'name' =>trim(I('name')),
			'type' => I('type'),
			'pid'  => I('pid'),
			'remarks' =>  I('remarks'),
		);
		
		//名字不能为空
		if(!$data['name'])
		{
			$this->error('名字不能为空！');
		}
	
		//写入
		
		if (M('paycategory') ->add($data,array(),true))
		{  
			$this ->success('操作成功',__APP__.'Admin/Paycategory/paycategorylist'); 
		}
		else
		{
			$this->error('操作失败');
		}
    }
	
	public function delete()
	{
	  $id = I('id');

	  //检查是否被使用
	  
	  $is_use = M('paycategory')->where('pid = '.$id)->find();
	  
	  if($is_use)
	  {
	  	$this->error('该类别有下属分类，请先删除下属分类！');
	  }
	  
	  if(M('otherfinance_list')->where('pay_type = '.$id)->find())
	  {
	  	$this->error('该类别已经被使用，暂不能被删除！');
	  }
	  

	  if(M('paycategory')->where('id = '.$id)->delete())
	  {
	    $this->success('删除成功！');
		
	  }else
	  {
	    $this->error('删除失败！');
 	  }
	
	
	}

	
	public function ajax_query()
	{
	
		//条件数组
		
		$list = $this->get_list();
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$res_str = $this->fetch($list['model_t']); // 输出模板
		
		$data['info'] = $res_str;
		
		$data['success'] = 1;
		
		$this->ajaxReturn($data);
	
	}
	
	
	//获取顾客列表封装函数
	
	public function get_list($filter = array())
	{
	  
		if(!$filter)
		{
			$filter = $this->query_array();
		}
		
		if($filter['order_by'])
		{
			$limit = $filter['order_by']." ".$filter['sort_by'];
		}else
		{
			$limit = '';
		} 
		
		$where = '1';
		
		if($filter['type'])
		{
			$where .= ' and type = '.$filter['type'];
		}
		
		$list = M('paycategory')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		foreach($list as $k=>$v)
		{
			if($v['pid'] > 0)
			{
				$list[$k]['pname'] = M('paycategory')->where('id = '.$v['pid'])->getField('name');
			}
		}
		
		//分页
		$count      = M('paycategory')->where($where)->count();
		
		$model_t = 'Ajax:Paycategory:paycategory_list';
	  
	  
	
	  
	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show,'model_t' => $model_t);
	
	}
	
	private function query_array()
	{
		 
	 $filter = array(
	 'p' => I('p',1),
	 'sid' => I('sid'),
	 'type' => I('type'),
	 'order_by'=> I('order_by'),
	 'sort_by'=> I('sort_by','ASC'),
	 'keywords' => I('keywords'),
	 );
	  
	 return $filter;
	
	}

   

}