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
 * 后台供应商控制器
 * huang
 */
class SupplierController extends AdminController {

    /**
     * 供应商管理
     * 黄
     */
    public function index(){
        //获取当前用户所属部门	 
	 
	 //初始化条件数组
	 
	 $filter = $this->query_array();
	 
	 $list = $this->guest_list($filter);

	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	 
    }
	
	
	/**
	*   供应商列表
	*   黄线可
	**/
	
	public function supplierlist()
	{
	 //条件数组
	 
	 $filter = $this->query_array();
	
	 $list = $this->guest_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	
	}
	
	//供应商账目明细
	public function suppliermoneylist()
	{
	 //条件数组
	 
	 $filter = $this->query_array();
	
	 $list = $this->guest_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	
	}
	
	public function suppliermoneyinfo()
	{
	   
	   
	 $filter = $this->query_array();
	
	 $list = $this->guest_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	   
	   
	
	}
	

    public function add(){
	 
	 $this->display(); // 输出模板
	 
    }
	
	
	//修改客户信息
	Public function edit(){
		$id=I('id');
		$info=M('supplier')->where(array('id'=>$id))->find();

		$this->assign('info',$info); 
		$this->display('Ajax:supplier:edit');
	}
	
	
	
	//添加客户数据整理里写入数据库
    Public  function  insert(){
	
		 $data = array( //获取数据
			'suppliername' =>trim(I('suppliername')),
			'province' => I('province'),
			'address'  => trim(I('address')),
			'manager' =>  trim(I('manager')),
			'phone'   =>  trim(I('phone')),
			'remark'  =>  trim(I('remark')),
			'writer' =>   I('writer'),
			'money' =>   I('money'),
			'intime' =>time(),
			);
			
		  //判断供应商名字是否重复
		  
		  if(M('supplier')->where('suppliername = "'.$data['suppliername'].'"')->find())
		  {
		    $this->error('此供应商已经存在！');
		  }
	
		 //黄修改插入品牌折扣
	
		 if (M('supplier') ->add($data)) {
		 	  
		  $this -> success('添加成功'); 
		   
		 }
	
		 else{$this->error('添加失败');}
    }
	
	
	
	//获取修改资料并修改客户资料
    Public function update(){
    
	 $id=I('id');

     $data = array(
        'suppliername' =>trim(I('suppliername')),
        'province' => I('province'),
        'address'  => trim(I('address')),
        'manager' =>  trim(I('manager')),
        'phone'   =>  trim(I('phone')),
        'remark'  =>  trim(I('remark')),
        'uptime' =>time(),
      );


	  
	  //判断供应商名字是否重复
		  
	  if(M('supplier')->where('suppliername = "'.$data['suppliername'].'" and id != '.$id)->find())
	  {
		$this->error('此供应商已经存在！');
	  }


     if(M('supplier')->where(array('id'=>$id))->save($data)){
           $this -> success('修改成功','supplierlist');       
     }else{$this->error('修改失败');}

    }
	
	public function delete()
	{
	  $id = I('id');

	  //判断是否有资金往来上的信息，有不允许删除
	  if (M('supplier_log')->where('sid = '.$id)->find()) {
	  	$this->error('该商户已有资金往来，暂时不允许删除！');
	  }

	  if(M('supplier')->where('id = '.$id)->delete())
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
	 
	 $list = $this->guest_list();
	
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出
	 
	 $res_str = $this->fetch($list['model_t']); // 输出模板
	 
	 $data['info'] = $res_str;
	 
	 $data['success'] = 1;
	 
	 $this->ajaxReturn($data);
	
	}
	
	
	//获取顾客列表封装函数
	
	public function guest_list($filter)
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
	  
	  if('money' == $filter['type'])
	  {
	   
		   if(!$filter['sid'])
		   {
			  $this->error('传值错误！');
		   }
		   
		   $list = M('supplier_log')->where('sid = '.$sid)->page($filter['p'].',10')->order($limit)->select();
		   
		   $count      = M('supplier_log')->where('sid = '.$sid)->count();
		   
		   $model_t = 'Ajax:supplier:money_list';
	  
	  }else
	  {
		  if($filter['keywords'])
		  {
			$where .= " and onethink_supplier.suppliername like '%".$filter['keywords']."%'";
		  }
		  
		  //print_r($where);
		 
		  $list = M('supplier')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  //分页
		  $count      = M('supplier')->where($where)->count();
		  
		  $model_t = 'Ajax:supplier:supplier_list';
	  
	  
	  }
	  
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