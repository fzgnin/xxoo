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
 * 财务过账系统黄线可
 2016.2.26
 * huang
 */
class InitialController extends AdminController {

    /**
     * 过账系统首页
     * 黄
     */
    public function index(){
       
		//初始化条件数组
		
		$this->display(); // 输出模板
		
    }
	
	public function initiallist()
	{
		//列表
		$filter = $this->query_array();
		
		$this->send_out($filter);
	}
	
	public function ajax_query()
	{
	 
		//条件数组
		
		$list = $this->get_list();
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->assign('a_amount',$list['a_amount']); 
		
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
	  
		//设置查询条件
		
		if($filter['order_by'])
		{
			$limit = $filter['order_by']." ".$filter['sort_by'];
		}else
		{
			$limit = '';
		}
		
		$where = '1';
		
		//1供应商2店家3公司账户
		if(1 == $filter['gs_type'])
		{
			
			if($filter['keywords'])
			{
				$where .= ' and suppliername like "%'.$filter['keywords'].'%"';
			}
		
			
			$list = M('supplier')->field('id,suppliername,intime,province,address,manager,phone,b_money,(b_money+money) as money')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
			
			//分页
			$count = M('supplier')->where($where)->count();
			
			//模版
			$model_t = 'Ajax:Initial:supplier_list';
		}
		
		elseif(2 == $filter['gs_type'])
		{
			if($filter['keywords'])
			{
				$where .= ' and guestname like "%'.$filter['keywords'].'%"';
			}
			
			if($filter['status'])
			{
				$status = $filter['status']-1;
				
				$where .= ' and stus = '.$status;
			}
			
			$list = M('guest')->field('id,guestname,intime,province,address,manager,phone,b_money,stus,(b_money+money) as money')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
			
			foreach($list as $k=>$v)
			{
				//获取部门id
				$list[$k]['bumen'] = M('bid_gid')->alias('bg')->join('left join `onethink_bumen` b on bg.bid = b.id ')->field('b.id,b.bname')->where('gid = '.$v['id'])->select();
				
				//print_r($list[$k]['bname']);
				
				//$list[$k]['status']
			
			}
			
			//分页
			$count = M('guest')->where($where)->count();
			
			//模版
			$model_t = 'Ajax:Initial:guest_list';
		}
		elseif(3 == $filter['gs_type'])
		{
			if($filter['keywords'])
			{
				$where .= ' and cname like "%'.$filter['keywords'].'%"';
			}

			$list = M('company')->field('id,cname,cid,add_time,b_money,(b_money+money) as money')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
			
			//分页
			$count = M('company')->where($where)->count();
			
			//模版
			$model_t = 'Ajax:Initial:company_list';

		} 
	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,$filter['page_num']);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);
	  
	  return array('list' => $list ,'show'=> $show ,'model_t' => $model_t,'a_amount'=>$a_amount );
	
	}
	
	
	public function change_bmoney()
	{
		$id = I('id');
		$gs_type = I('gs_type');
		
		if(!$id || !$gs_type)
		{
			$this->error('数据传输错误！');
		}
		
		if(1 == $gs_type)
		{
			//取出名字初始金额和当前余额
			$info = M('supplier')->field('suppliername as name,b_money,money,(b_money+money) as n_money')->where('id = '.$id)->find();	
		}
		elseif(2 == $gs_type)
		{
			$info = M('guest')->field('guestname as name,b_money,money,(b_money+money) as n_money')->where('id = '.$id)->find();
		}
		elseif(3 == $gs_type)
		{
			$info = M('company')->field('cname as name,b_money,money,(b_money+money) as n_money')->where('id = '.$id)->find();
		}
		
		
		$this->success($info);
	
	
	}
	
	public function save_bmoney()
	{
		$id = I('id');
		$gs_type = I('gs_type');
		$b_money = I('b_money');
		
		//存储初始金额
		switch($gs_type)
		{
			case 1:
				$info = M('supplier')->where('id = '.$id)->setField('b_money',$b_money);
				break;
			case 2:
				$info = M('guest')->where('id = '.$id)->setField('b_money',$b_money);
				break;
			case 3:
				$info = M('company')->where('id = '.$id)->setField('b_money',$b_money);
				break;
			default:	
		}
		
		if($info)
		{
			$this->success('更新成功！');
		}else
		{
			$this->error('更新失败！');
		}	
	}
	
	
	//定义查询变量
	private function query_array()
	{
	  
	  $begin_time = I('begin_time')?strtotime(I('begin_time')):'';
	  
	  $end_time = I('end_time')?strtotime(I('end_time'))+86399:mktime(23,59,59,date('m'),date('d'),date('Y'));
	  
	  //如果没有起始时间设置起始时间为本期
	  if(!$begin_time)
	  {
	    //获取本期的起始时间
	    $time = M('checkout')->order('id desc')->limit(1)->getField('end_date');
		//如果有本期时间设定为本期时间
		if($time)
	    {
		  $begin_time = strtotime($time)+86400;
		  //print_r($filter);exit;
		  //如果结束时间小于起始时间设置结束时间为起始时间后一天
		  if($begin_time >= $end_time)
		  {
		    $end_time = $begin_time+86399;
		  }
	
	      $this->assign('time',date('Y-m-d',$begin_time));
	    }
		//没有起始时间且没有本期开始时间设置时间为零
		else
	    {
	      $begin_time = 0;
		
	    }
		
	  }
  
	  $filter = array(
	   'p' => I('p',1),
	   'order_by'=> I('order_by'),
	   'sort_by'=> I('sort_by','ASC'),
	   'keywords' => I('keywords'),
	   'status' => I('status',0),
	   'gs_type' => I('gs_type',1),
	   'name' => I('name'),
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'date_type' => I('date_type',1),
	   'page_num'=>I('page_num',10),
	  );
	  
	 return $filter;
	
	}
	
	private function send_out($filter)
	{
	  $list = $this->get_list($filter);
	 
	  $this->assign('filter',$filter);// 赋值分页输出
	   
	  $this->assign('list',$list['list']);// 赋值数据集
	  
	  $this->assign('a_amount',$list['a_amount']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  $this->display();
	
	}

}