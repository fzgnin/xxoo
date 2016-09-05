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
 * 后台公司账户管理
 * huang
 */
class CompanyController extends AdminController {

    /**
     * 账户管理
     * 黄
     */
    public function index(){
        
	

	 $this->display(); // 输出模板
	 
    }
	
	public function companylist()
	{
	  $filter = $this->query_array();  
	  
	  //获取本期的起始时间
	  $time = M('checkout')->order('id desc')->limit(1)->getField('end_time');
	
	  if($time)
	  {
	    $filter['begin_time'] = $time;
	
	    $this->assign('time',$time);
	  }
	  
	  $this->send_out($filter);
	}

	
	public function edit()
	{
	  $id = I('id');
	  
	  if(!$id)
	  {
	   $this->error('该订单不存在，请确定传值正确性！');
	  }
	  
	  $order = $this->get_order_info($id);
	 
	  $this->assign('order_info',$order['order_info']);
	 
	  $this->assign('money_list',$order['money_list']);
	  
	
	  $this->display();
	}
	
	

	
	//插入商品到数据库
	public function insert()
	{
	 $cname = I('cname');
	 $cid = I('cid');
	 $money = I('money');
	 $type = I('type');
	 
	 if(!$cname || !$cid)
	 {
	   $this->error('请填写相关信息！');
	 }
	 
	 //形成数据data
	 $data = array(
	 'cname'=>$cname,
	 'cid'=>$cid,
	 'b_money'=>$money,
	 'type'=>$type,
	 'add_time'=>time(),
	 );
	 
	 //写入数据库
	 if(M('company')->add($data))
	 {
	   $this->success('添加成功！');
	 }else
	 {
	   $this->error('添加失败！');
	 }
	 
	}
	
	
	//删除操作
	public function delete()
	{
	  $id = I('id');
	  
	  if(!$id)
	  {
	    $this->error('传值错误！');
	  }
	  
	  //查询账户是否被使用（暂时先查询收付款单，因为可能有未过账的数据使用该表）
	  if(M('finance_list')->where('cid = '.$id)->find())
	  {
	    $this->error('此帐号已经被使用，暂时不能删除！');
	  }
	  
	  if(M('company')->where('id = '.$id)->delete())
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

	  //获取银行账户列表
	  
		$list = M('company')->field('id,cname,cid,(b_money+money) as money')->page($filter['p'].',10')->order($limit)->select();

		//分页
		$count = M('company')->count();
		
		$a_amount = M('company')->sum('b_money+money');
		
		$this->assign('a_amount',$a_amount);
		
		//模版
		$model_t = 'Ajax:Company:company_list';

	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show ,'model_t' => $model_t);
	
	}
	
	
	//定义查询变量
	private function query_array()
	{
	  $filter = array(
	   'p' => I('p',1),
	   'order_by'=> I('order_by'),
	   'sort_by'=> I('sort_by','ASC'),
	   'keywords' => I('keywords'),
	   'type' => I('type'),
	   'order_type' => I('order_type'),
	  );
	  
	 return $filter;
	
	}
	
	
	
	private function send_out($filter)
	{
	  $list = $this->get_list($filter);
	 
	  $this->assign('filter',$filter);// 赋值分页输出
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  $this->display();
	
	}
	

}