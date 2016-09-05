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
 * 财务总账控制页
 * huang
 */
class LedgersController extends AdminController {

    /**
     * 首页
     * 黄
     */
    public function index(){

	 $amount = array();
	 
	 //本月时间戳
	 
	 $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
	 
	 //获取上次的结账信息
	 $checkout_info = M('checkout')->order('id desc')->limit(1)->find();
	 
	 $checkout_info['end_time'] = $checkout_info['end_time']?$checkout_info['end_time']:0;
	 
	 //获取总金额
	 
	 $amount['company_money'] = M('company')->sum('money+b_money');
	 
	 //获取供应商金额
	 $amount['supplier_money'] = M('supplier')->sum('money+b_money');
	 
	 //终端客户总账
	 $amount['guest_money'] = M('guest')->sum('money+b_money'); 
	 
	 //库存账目金额
	 $amount['warehouse_money'] = M('warehouse')->sum('n_amount');
	 
	 //公司现有总资产
	 
	 $amount['all'] = $amount['company_money'] - $amount['supplier_money'] - $amount['guest_money'] + $amount['warehouse_money'];
	 
	 
	 //黄重新写供应商应收应付
	 $supplier['sup_s'] = M('supplier')->where('money+b_money <= 0')->sum('money+b_money');
	 
	 $supplier['sup_s'] = abs($supplier['sup_s']);
	 
	 $supplier['sup_f'] = M('supplier')->where('money+b_money >= 0')->sum('money+b_money');
	 
	 $this->assign('supplier',$supplier);
	 
	 //库存余额
	 $warehouse = M('warehouse')->sum('n_amount');
	 
	 $this->assign('warehouse',$warehouse);
	 
	 //店家应收应付
	 $guest['g_s'] = M('guest')->where('money+b_money <= 0')->sum('money+b_money');
	 
	 $guest['g_s'] = abs($guest['g_s']);
	 
	 $guest['g_f'] = M('guest')->where('money+b_money >= 0')->sum('money+b_money');
	 
	 $this->assign('guest',$guest);
	 
	 //获取本期销售业绩和总销售业绩
	 $amount['a_amount'] = M('order')->where('status = 1')->sum('g_amount');
	 
	 $amount['n_amount'] = M('order')->where('add_time > '.$checkout_info['end_time'].' and status = 1')->sum('g_amount');
	 
	 $this->assign('amount',$amount);
	 
	 //计算部门和公司的毛利（已过账）
	 $feat['b_feat'] = M('bumen_feat')->where('status = 1')->sum('b_feat'); 
	 
	 //取总销售成本
	 $cost = M('warehouse')->sum('s_amount');
	 
	 //取已过账销售业绩
	 $sale = M('order')->where('status = 1')->sum('g_amount');
	 
	 $feat['c_feat'] = $sale-$cost;
	 
	 $this->assign('feat',$feat);
	 
	 //取进货信息
	 $in_goods['n_goods'] = M('stock_order')->where('add_time >'.$checkout_info['end_time'].' and status = 1')->sum('c_amount');
	 
	 $in_goods['a_goods'] = M('warehouse')->sum('a_amount');
	 
	 $this->assign('in_goods',$in_goods);
	 
	 //部门费用
	 $bumen_money['a_amount'] = M('otherfinance_order')->where('mtype = 3 and status = 1')->sum('amount');
	 
	 $bumen_money['a_amount'] = -1*$bumen_money['a_amount'];
	 
	 $bumen_money['n_amount'] = M('otherfinance_order')->where('add_time >'.$checkout_info['end_time'].' and status = 1 and mtype = 3')->sum('amount');
	 
	 $bumen_money['n_amount'] = -1*$bumen_money['n_amount'];
	 
	 $this->assign('bumen_money',$bumen_money);
	 
	
	
	 $this->assign('checkout_info',$checkout_info);
	 
	 $this->assign('sales_mounth',$sales_mounth?$sales_mounth:0);
	 
	 $this->assign('amount',$amount);
	 
	 $this->assign('l_m',$l_m);
	 
	 

	 $this->display(); // 输出模板
	 
    }
	
	public function checkout()
	{
	 $amount = array();
	 
	 $time = time();
	 
	 $amount['end_time'] = $time;
	 
	 $amount['add_time'] = $time;
	 
	 $amount['end_date'] = date('Y-m-d');
	 
	 //获取上次结账的时间
	 $last_info = M('checkout')->order('id desc')->limit(1)->find();
	 
	 //判断此日期前是否有未过账的单据（给与提示）
	 
	 
	 //判断结账日期是否小于等于上次结账日期
	 if(isset($last_info['end_date']) && strtotime($amount['end_date']) <= strtotime($last_info['end_date']))
	 {
	   $this->error('结账日期不能小于等于上次结账日期！');
	 }
	 
	 $amount['begin_time'] = isset($last_info['end_time'])?$last_info['end_time']:0;
	 $amount['begin_date'] = isset($last_info['end_date'])?date('Y-m-d',strtotime($last_info['end_date'])+86400):'0000-00-00';
	 
	 
	 //获取总金额	 
	 $amount_c = M('company')->sum('money+b_money');
	 $amount['company_money'] = $amount_c?$amount_c:'';
 
	 //获取供应商金额
	 $amount_s = M('supplier')->sum('money+b_money');
	 $amount['supplier_money'] = $amount_s?$amount_s:'';

	 //终端客户总账
	 $amount_g = M('guest')->sum('money+b_money');
	 $amount['guest_money'] = $amount_g?$amount_g:''; 
 
	 //库存账目金额
	 $amount_w = M('warehouse')->sum('n_amount');
	 $amount['warehouse_money'] = $amount_w?$amount_w:''; 

	 //公司现有总资产
	 $amount['all_money'] = $amount['company_money'] - $amount['supplier_money'] - $amount['guest_money'] + $amount['warehouse_money'];
	 
	 //print_r($amount);exit;
	 if(M('checkout')->add($amount))
	 {
	   
	   $this->success('结账成功！');
	 
	 }else
	 {
	   $this->error('结账失败！');
	 }
	
	
	
	}
	
	
	//反结账系统
	public function checkout_off()
	{
	  //先暂时把结账记录删除掉试试
	  //删除最近的一条记录
	   if(M('checkout')->where(1)->order('id desc')->limit('1')->delete())
	   {
	     $this->success('反结账成功！');
	   }else
	   {
	     $this->error('反结账失败！');
	   }
	
	}
	
	//利润明细表
	
	public function profitlist()
	{	 
		 $filter = $this->query_array();
		 
		 $filter['type'] = 'profit';
		
		 $this->send_out($filter);
		 	
	}
	
	
	/**
	*   库存金额变化明细
	*   黄线可
	**/
	
	public function warehouselist()
	{
		//条件数组
		
		$filter = $this->query_array();
		
		$filter['type'] = 'warehouse';
		
		//赋值品牌
		$brand_list = M('brand')->field('id,name')->select();
		
		$this->assign('brand_list',$brand_list);
		
		$this->send_out($filter);
	}
	
	
	public function warehouse_loglist()
	{
	 //条件数组
	 
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'warehouse_log';
	
	 $this->send_out($filter);
	
	}
	
	public function companylist()
	{
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'company';
	 
	 //获取本期的起始时间
	 $time = M('checkout')->order('id desc')->limit(1)->getField('end_time');
	 
	 if($time)
	 {
	   $filter['begin_time'] = $time;
	 }
	
	 $this->send_out($filter);
	 
	}
	
	public function company_loglist()
	{
	  $filter = $this->query_array();
	 
	  $filter['type'] = 'company_log';
	
	  $this->send_out($filter);
	}
	
	//供应商账目明细
	public function supplierlist()
	{
	 //条件数组
	 
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'supplier';
	
	 $this->send_out($filter);
	
	}
	
	public function supplier_loglist()
	{
	   
	   
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'supplier_log';
	 
	 $this->send_out($filter);
	 
	}
	
	
	
	//终端客户账目明细
	public function guestlist()
	{
	 //条件数组
	 
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'guest';
	
	 $this->send_out($filter);
	
	}
	
	public function guest_loglist()
	{
	   
	   
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'guest_log';
	
	 $this->send_out($filter);
	 
	}
	
	
	//终端客户账目明细
	public function bumenlist()
	{
	 //条件数组
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'bumen';
	
	 $this->send_out($filter);
	
	}
	
	public function bumen_loglist()
	{
	   
	   
	 $filter = $this->query_array();
	 
	 $filter['type'] = 'bumen_log';
	
	 $this->send_out($filter);
	 
	}
	
	
	
	public function delete()
	{
	  $id = I('id');

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
	  
	  
	  if('profit' == $filter['type'])
	  {
			  //获取相关供应商列表
			  
			  switch($filter['date_type'])
			  {
					case 1:
					$where = 'add_time >= '.$filter['begin_time'].' and add_time < '.$filter['end_time'];
					break;
					case 2:
					$where = 'post_time >= '.$filter['begin_time'].' and post_time < '.$filter['end_time'];
					break;
					default:
					$where = 'post_time >= '.$filter['begin_time'].' and post_time < '.$filter['end_time'];
			  }
			  
			  switch($filter['status'])
			  {
					case 1:
					$where .= ' and status = 0';
					break;
					case 2:
					$where .= ' and status = 1';
					break;
					default:;
			  }
			  
			  if($filter['order_type'])
				{
					$where .= ' and order_type = '.$filter['order_type'];
				}
				
				if($filter['gname'])
				{
					$where .= ' and gname like "%'.$filter['gname'].'%"';
				}
				
				if($filter['bname'])
				{
					$where .= ' and bname like "%'.$filter['bname'].'%"';
				}
				
				if($filter['order_sn'])
				{
					$where .= ' and order_sn like "%'.$filter['order_sn'].'%"';
				}
				
				if($filter['amount'])
				{
					$where .= ' and amount like "%'.$filter['amount'].'%"';
				}
				
				if($filter['remarks'])
				{
					$where .= ' and remarks like "%'.$filter['remarks'].'%"';
				}

			  $list = M('bumen_feat')->field('*,feat-b_feat as b_m,feat-c_feat as c_m')->where($where)->page($filter['p'].',10')->order($limit)->select();
			  //print_r($list);exit;
			  //分页
			  $count = M('bumen_feat')->where($where)->count();
			  
			  //总金额
			  $a_mount = M('bumen_feat')->field('sum(feat-b_feat) as b_l , sum(feat-c_feat) as c_l')->where($where)->find();
			  
			  $this->assign('a_mount',$a_mount);
					
			  //模版
			  $model_t = 'Ajax:Ledgers:profit_list';
	  }
	  
	  
	  elseif('warehouse' == $filter['type'])
	  {
			$where = '1';
			
			if($filter['brand_id'])
			{
				$where .= ' and g.brand_id = '.$filter['brand_id'];
			}
			
			if($filter['keywords'])
			{
				$where .= ' and w.goodsname like "%'.$filter['keywords'].'%"';
			}
			
			
			$list = M('warehouse')->alias('w')->join('left join `onethink_goods` g on w.goods_id = g.id ')->where($where)->page($filter['p'].',10')->order($limit)->select();
			
			$count = M('warehouse')->alias('w')->join('left join `onethink_goods` g on w.goods_id = g.id ')->where($where)->count();
			
			$now_amount = M('warehouse')->alias('w')->join('left join `onethink_goods` g on w.goods_id = g.id ')->where($where)->sum('n_amount');
			
			//仓库现有总金额	
			$this->assign('now_amount',$now_amount);				
			//模版
			$model_t = 'Ajax:Ledgers:warehouse_list';	  
	  }
	  
	  
	  elseif('warehouse_log' == $filter['type'])
	  {
			$where = 'add_time >= '.$filter['begin_time'].' and add_time < '.$filter['end_time'];
			  
			 //print_r($filter); 
			if($filter['goods_id'])
			{
				$where .= ' and goods_id = '.$filter['goods_id'];
				
				//取初始金额
				$warehouse = M('warehouse')->where('goods_id = '.$filter['goods_id'])->find();
				$last_log = M('warehouse_log')->where('add_time < '.$filter['begin_time'].' and goods_id = '.$filter['goods_id'])->sum('num');
				
				//加期初金额
				$b_array = array(
				'goodsname'=>$warehouse['goodsname'],
				'n_num'=>$last_log,
				'n_amount'=>$last_log*$warehouse['averages'],
				);
				
				$a_mount = $warehouse['n_amount'];
			  
		     	$this->assign('a_mount',$a_mount);
				
				$this->assign('b_array',$b_array);
			}	 
  
			$list = M('warehouse_log')->where($where)->page($filter['p'].',10')->order('add_time asc')->select();
			
			foreach($list as $k => $v)
			{
				$n_num = M('warehouse_log')->where('add_time <= '.$v['add_time'].' and goods_id = '.$v['goods_id'])->sum('num');
				
				$n_num = $n_num?$n_num:0;
				
				$list[$k]['n_num'] = $n_num;
				$list[$k]['n_amount'] = $n_num*$warehouse['averages'];
			}
			// print_r($list);
			$count = M('warehouse_log')->where($where)->count();
				
			//模版
			$model_t = 'Ajax:Ledgers:warehouse_log_list';
	  }
	  
	  elseif('company' == $filter['type'])
	  {
	    
			  
			  $list = M('company')->field('id,cname,cid,(b_money+money) as money')->page($filter['p'].',10')->order($limit)->select();
			  
			  $count = M('company')->count();
			  
			  $a_mount = M('company')->sum('b_money+money');
			  
			  $this->assign('a_mount',$a_mount);
					
			  //模版
			  $model_t = 'Ajax:Ledgers:company_list';
	  
	  }
	  
	  elseif('company_log' == $filter['type'])
	  {
	  
	     $where =  'add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
		 
		 if($filter['id'])
		 {
		   $where .= ' and cid = '.$filter['id'];
		   
		   //取初始金额
			 $company = M('company')->where('id = '.$filter['id'])->find();
			 $last_log = M('company_log')->where('add_time < '.$filter['begin_time'].' and cid = '.$filter['id'])->sum('money');
	
			 //加期初金额
			 $b_array = array(
			 'cname'=>$company['cname'],
			 'balance'=>$last_log+$company['b_money'],
			 );
			 
			 $this->assign('b_array',$b_array);
		 }	 
		 
	   
	     $list = M('company_log')->field('cid,cname,order_id,order_sn,order_type,add_time,post_time,mname,money')->where($where)->page($filter['p'].',10')->order('add_time asc')->select();
		 
			 foreach($list as $k=>$v)
			 {
				$s_money = M('company_log')->where('add_time <= '.$v['add_time'].' and cid = '.$v['cid'])->sum('money');
				
				$s_money = $s_money?$s_money:0;
				
				$list[$k]['balance'] = $s_money+$company['b_money'];
		 	 }
		 //print_r($list);
			  
			  $count = M('company_log')->where($where)->count();
					
			  //模版
			  $model_t = 'Ajax:Ledgers:company_log_list';
	  
	  }
	  
	  elseif('supplier' == $filter['type'])
	  {
	  
		  if(1 == $filter['pay_type'])
		  {
		   	$where = "money+b_money < 0";					
		  }
		  elseif(2 == $filter['pay_type'])
		  {
		  		$where = 'money+b_money > 0';
		  }else
		  {
		  	$where = 'money+b_money <> 0';
		  }

		  if ($filter['keywords']) {
		  	$where .= ' and suppliername like "%'.$filter['keywords'].'%"';
		  }
		  
		  
		  $list = M('supplier')->field('id,suppliername,province,address,manager,phone,(b_money+money) as money')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  foreach($list as $k => $v)
		  {
		  	$list[$k]['sf'] = $v['money'] > 0?1:0;
		  }
		  
		  //分页
		  $count      = M('supplier')->where($where)->count();
		  
		  $model_t = 'Ajax:Ledgers:supplier_list';

	  }
	  
	  elseif('supplier_log' == $filter['type'])
	  {
	    
		$where =  'add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
		
		if($filter['id'])
		  {
		    $where .= ' and sid = '.$filter['id'];
		   
		   //取初始金额
			 $supplier = M('supplier')->where('id = '.$filter['id'])->find();
			 $last_log = M('supplier_log')->where('add_time < '.$filter['begin_time'].' and sid = '.$filter['id'])->sum('amount');
	
			 //加期初金额
			 $b_array = array(
			 'gname'=>$supplier['suppliername'],
			 'balance'=>$last_log+$supplier['b_money'],
			 ); 
			 
			 $a_mount = $supplier['b_money'] + $supplier['money'];
			  
		     $this->assign('a_mount',$a_mount);
			 
			 $this->assign('b_array',$b_array);
		  }
		  
		  $list = M('supplier_log')->where($where)->page($filter['p'].',10')->order('add_time asc')->select();
		  
		  foreach($list as $k => $v)
			{
				$s_money = M('supplier_log')->where('add_time <= '.$v['add_time'].' and sid = '.$v['sid'])->sum('amount');
				
				$s_money = $s_money?$s_money:0;
				
				$list[$k]['balance'] = $s_money+$supplier['b_money'];
			}
		  
		  //分页
		  $count      = M('supplier_log')->where($where)->count();
		  
		  $model_t = 'Ajax:Ledgers:supplier_log_list';
	  
	  
	  }
	  
	  elseif('guest' == $filter['type'])
	  { 
		  
		  if(1 == $filter['pay_type'])
		  {
		   	$where = "money+b_money < 0";					
		  }
		  elseif(2 == $filter['pay_type'])
		  {
		  		$where = 'money+b_money > 0';
		  }else
		  {
		  	$where = 'money+b_money <> 0';
		  }

		  if ($filter['keywords']) {
		  	$where .= ' and guestname like "%'.$filter['keywords'].'%"';
		  }
		  
		  $list = M('guest')->field('id,guestname,province,address,manager,(money+b_money) as money,b_money')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  foreach($list as $k => $v)
		  {
		  	$list[$k]['sf'] = $v['money'] > 0?1:0;
		  }
		  
		  //分页
		  $count      = M('guest')->where($where)->count();
		  
		  $a_mount = M('guest')->where($where)->sum('money+b_money');
			  
		  $this->assign('a_mount',$a_mount);
		  
		  $model_t = 'Ajax:Ledgers:guest_list';
	  }
	  
	  elseif('guest_log' == $filter['type'])
	  {
	      $where =  'add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
		  
		  if($filter['id'])
		  {
		    $where .= ' and gid = '.$filter['id'];
		   
		   //取初始金额
			 $guest = M('guest')->where('id = '.$filter['id'])->find();
			 $last_log = M('guest_log')->where('add_time < '.$filter['begin_time'].' and gid = '.$filter['id'])->sum('amount');
	
			 //加期初金额
			 $b_array = array(
			 'gname'=>$guest['guestname'],
			 'balance'=>round($last_log+$guest['b_money'],2),
			 ); 
			 
			 $a_mount = round($guest['b_money'] + $guest['money'],2);
			  
		     $this->assign('a_mount',$a_mount);
			 
			 $this->assign('b_array',$b_array);
		  }
		  
		  $list = M('guest_log')->field('gid,gname,order_id,order_sn,add_time,order_type,post_time,amount,remarks')->where($where)->page($filter['p'].',10')->order('add_time asc')->select();
		  
			foreach($list as $k => $v)
			{
				$s_money = M('guest_log')->where('add_time <= '.$v['add_time'].' and gid = '.$v['gid'])->sum('amount');
				
				$s_money = $s_money?$s_money:0;
				
				$list[$k]['balance'] = round($s_money+$guest['b_money'],2);
			}
		  
		  //分页
		  $count      = M('guest_log')->where($where)->count();
		  
		  $model_t = 'Ajax:Ledgers:guest_log_list';
	  
	  }
	  
	  
	  
	  elseif('bumen' == $filter['type'])
	  { 
		  $where = 'mtype = 3 and status = 1';

		  if ($filter['keywords']) {
		  	$where .= ' and mname like "%'.$filter['keywords'].'%"';
		  }
		  
		  $list = M('otherfinance_order')->field('mid,mname,sum(amount) as amount')->where($where)->group('mid')->select();
					
		  
		  //分页
		  $count      = M('otherfinance_order')->where($where)->group('mid')->count();
		  
		  //$a_mount = M('finance_order')->where($where)->sum('money+b_money');
			  
		  //$this->assign('a_mount',$a_mount);
		  
		  $model_t = 'Ajax:Ledgers:bumen_list';
	  }
	  
	  elseif('bumen_log' == $filter['type'])
	  {
	      $where = 'mtype = 3 and status = 1';
		  
		  $where .=  ' and add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
		 
		  if($filter['id'])
		  {
		    $where .= ' and mid = '.$filter['id'];
		   
		   //取初始金额
			 $bname = M('bumen')->where('id = '.$filter['id'])->getField('bname');
			 $last_log = M('otherfinance_order')->where('add_time < '.$filter['begin_time'].' and status = 1 and mtype = 3 and mid = '.$filter['id'])->sum('amount');
	
			 //加期初金额
			 $b_array = array(
			 'bname'=>$bname,
			 'balance'=>$last_log,
			 ); 
			 
			 $this->assign('b_array',$b_array);
		  }
		  
		  $list = M('otherfinance_order')->field('id,mid,mname,order_type,order_sn,add_time,post_time,amount,remarks')->where($where)->page($filter['p'].',10')->order('add_time asc')->select();
		  //print_r(M('finance_order')->getLastSql()); 
			foreach($list as $k => $v)
			{
				$s_money = M('otherfinance_order')->where('add_time <= '.$v['add_time'].' and mtype = 3 and status = 1 and mid = '.$v['mid'])->sum('amount');
				
				$s_money = $s_money?$s_money:0;
				
				$list[$k]['balance'] = $s_money;
			}
		  
		  //分页
		  $count      = M('otherfinance_order')->where($where)->count();
		  
		  $model_t = 'Ajax:Ledgers:bumen_log_list';
	  
	  }
	  
	
	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);// 赋值分页输出
	  
	  return array('list' => $list ,'show'=> $show ,'model_t' => $model_t );
	
	}
	
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
	   'type' => I('type'),
	   'id' => I('id'),
	   'order_type' => I('order_type'),
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'date_type' => I('date_type',1),
	   'gname' => I('gname'),
	   'bname' => I('bname'),
	   'order_sn' => I('order_sn'),
	   'amount' => I('amount'),
	   'remarks' => I('remarks'),
	   'status' => I('status'),
	   'pay_type' => I('pay_type'),
	   'brand_id' => I('brand_id'),
	   'goods_id' => I('goods_id'),
	  );
	  
	  return $filter;
	
	}
	
	private function send_out($filter)
	{
	  $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  $this->display();
	
	}
	
	public function get_excel_data()
	{
		$exc_type = I('exc_type');
		
		switch($exc_type)
		{
			case 'guest_log':
					$data = $this->exc_guest_log();
					break;
			case 'company':
					$data = $this->exc_company();
					break;
			case 'company_log':
					$data = $this->exc_company_log();
					break;
			case 'guest':
					$data = $this->exc_guest();
					break;
		}
	
		return $data;
	}
	
	
	
	private function exc_guest_log()
	{
		
		$id = I('id');
		
		$time = $this->get_time();
		
		$begin_time = $time['begin_time'];
		
		$end_time = $time['end_time'];
		   
		//取初始金额
		$guest = M('guest')->where('id = '.$id)->find();
		$last_log = M('guest_log')->where('add_time < '.$begin_time.' and gid = '.$id)->sum('amount');
		
		//加期初金额
		$b_array = array(
		'gid'=>$id,
		'gname'=>$guest['guestname'],
		'order_type'=>'',
		'order_sn'=>'',
		'add_time'=>'',
		'post_time'=>'',
		'amount'=>'',
		'balance'=>round($last_log+$guest['b_money'],2),
		'remarks'=>'期初余额（负为应收正为应付）',
		); 
		
		$where =  'gid = '.$id.' and add_time>='.$begin_time.' and add_time<'.$end_time;
		$list = M('guest_log')->field('gid,gname,order_type,order_sn,add_time,post_time,amount,0 as balance,remarks')->where($where)->order('add_time asc')->select();		

		  
		foreach($list as $k => $v)
		{
			$s_money = M('guest_log')->where('add_time <= '.$v['add_time'].' and gid = '.$v['gid'])->sum('amount');
			$s_money = $s_money?$s_money:0;
			$list[$k]['balance'] = round($s_money+$guest['b_money'],2);
			
			//时间格式化
			$list[$k]['add_time'] = date("Y-m-d H:i:s",$list[$k]['add_time']);
			$list[$k]['post_time'] = date("Y-m-d H:i:s",$list[$k]['post_time']);
			$list[$k]['order_type'] = get_ordertype($list[$k]['order_type']);
		}
		
		array_unshift($list,$b_array);

		
		$data = array();
		
		$data['title'] = $guest['guestname'].'-'.I('begin_time').'--'.I('end_time').'号应收应付明细';
		$data['head'] = array(
			'样式名称'=>'应收应付明细',
			'公司全名'=>'上海雅兰化妆品有限公司',
			'联系人'=>'吴时肖',
			'地址'=>'上海市罗城路530号住大商务楼A座2楼',
			'电话'=>'021-33070128',
			'邮编'=>'200021',
			'单位名称'=>$guest['guestname'],
			'此前账户余额'=>round($last_log+$guest['b_money'],2),
			'日期'=>I('begin_time').'至'.I('end_time'),
		);
		$data['list_head'] = array('id','客户名称','单据类型','单据编号','下单时间','过账时间','单据金额','单后账户余额','备注');
		$data['list'] = $list;
		$data['tag'] = '应收应付明细';
		
		
		return $data;	
	}
	
	
	private function exc_company()
	{
		$data['list'] = M('company')->field('id,cname,cid,(b_money+money) as money')->select();
		$data['title'] = '公司固有资金明细';
		$data['head'] = array(
			'样式名称'=>'公司固有资金明细',
			'公司全名'=>'上海雅兰化妆品有限公司',
			'联系人'=>'吴时肖',
			'地址'=>'上海市罗城路530号住大商务楼A座2楼',
			'电话'=>'021-33070128',
			'邮编'=>'200021',
			'日期'=>date("Y-m-d H:i:s"),
		);
		$data['list_head'] = array('id','账户名称','账户账号','当前余额');
		$data['tag'] = '固有资金明细';
		return $data;
	}
	
	private function exc_company_log()
	{
		$id = I('id');
		
		$time = $this->get_time();
		
		$begin_time = $time['begin_time'];
		
		$end_time = $time['end_time'];
		
		$where =  'add_time>='.$begin_time.' and add_time<'.$end_time;
		 
		if($id)
		{
			$where .= ' and cid = '.$id;
			
			//取初始金额
			$company = M('company')->where('id = '.$id)->find();
			$last_log = M('company_log')->where('add_time < '.$begin_time.' and cid = '.$id)->sum('money');
			
			//加期初金额
			$b_array = array(
			'order_id'=>'',
			'cid'=>$company['id'],
			'cname'=>$company['cname'],
			'order_sn'=>'',
			'order_type'=>'',
			'add_time'=>'',
			'post_time'=>'',
			'remarks'=>'',
			'mname'=>'',
			'money'=>'',
			'balance'=>$last_log+$company['b_money'],
			'purpose'=>'期初余额',
			);
		}	 
		 
	   
		$list = M('company_log')->field('order_id,cid,cname,order_sn,order_type,add_time,post_time,remarks,mname,money')->where($where)->order('add_time asc')->select();
		
		foreach($list as $k=>$v)
		{
			$s_money = M('company_log')->where('add_time <= '.$v['add_time'].' and cid = '.$v['cid'])->sum('money');
			
			$s_money = $s_money?$s_money:0;
			
			$list[$k]['balance'] = $s_money+$company['b_money'];
			
			$order = $this->get_ordername($v['order_type']);
			
			$list[$k]['purpose'] = M($order['order_list'])->where('order_id = '.$v['order_id'].' and cid = '.$v['cid'].' and money = '.$v['money'])->getField('purpose');
			
			//时间格式化
			$list[$k]['add_time'] = date("Y-m-d H:i:s",$list[$k]['add_time']);
			$list[$k]['post_time'] = date("Y-m-d H:i:s",$list[$k]['post_time']);
			$list[$k]['order_type'] = get_ordertype($list[$k]['order_type']);
		}
		
		array_unshift($list,$b_array);
		
		$data['list']  = $list;
		$data['title'] = $company['cname'].'账户资金明细表';
		$data['head'] = array(
			'样式名称'=>'账户资金明细表',
			'公司全名'=>'上海雅兰化妆品有限公司',
			'联系人'=>'吴时肖',
			'地址'=>'上海市罗城路530号住大商务楼A座2楼',
			'电话'=>'021-33070128',
			'邮编'=>'200021',
			'日期'=>I('begin_time').'至'.I('end_time'),
		);
		$data['list_head'] = array('订单id','账户id','账户名称','订单号','订单类型','下单时间','过账时间','备注','往来单位','单据金额','单后余额','描述');
		$data['tag'] = '账户资金明细表';
		return $data;	 
	
	}
	
	private function exc_guest()
	{
		$pay_type = I('pay_type');
		
		$keywords = I('keywords');
		
		if(1 == $pay_type)
		{
			$where = "money+b_money < 0";					
		}
		elseif(2 == $pay_type)
		{
			$where = 'money+b_money > 0';
		}else
		{
			$where = 'money+b_money <> 0';
		}
		
		if ($keywords) {
			$where .= ' and guestname like "%'.$keywords.'%"';
		}
		
		$list = M('guest')->field('guestname,province,address,manager,(money+b_money) as money')->where($where)->select();
		
		foreach($list as $k => $v)
		{
			$list[$k]['sf'] = $v['money'] > 0?'应付':'应收';
		}
		
		$data['list']  = $list;
		$data['title'] = '往来店家应收应付';
		$data['head'] = array(
			'样式名称'=>'往来店家应收应付',
			'公司全名'=>'上海雅兰化妆品有限公司',
			'联系人'=>'吴时肖',
			'地址'=>'上海市罗城路530号住大商务楼A座2楼',
			'电话'=>'021-33070128',
			'邮编'=>'200021',
			'导出时间'=>date('Y-m-d H:i:s'),
		);
		$data['list_head'] = array('客户名称','地区','详细地址','负责人','账户金额（正为应付负为应收）','应收应付');
		$data['tag'] = '往来店家应收应付';
		return $data;	
	}
	
	
	public function get_time()
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
			}
			//没有起始时间且没有本期开始时间设置时间为零
			else
			{
			  $begin_time = 0;
			}
		}	
		return array('begin_time'=>$begin_time,'end_time'=>$end_time);
	}
	
	
	public function get_ordername($order_type)
	{
		switch($order_type)
		{
			case 1:
			case 2:
					$order_name = 'order';
					$order_list = 'order_goods';
					break;
			case 3:
			case 4:
					$order_name = 'stock_order';
					$order_list = 'stock_goods';
					break;
			case 5:
			case 6:
					$order_name = 'income_order';
					$order_list = 'income_goods';
					break;
			case 7:
			case 8:
			case 9:
					$order_name = 'order';
					break;
			case 10:
			case 11:
					$order_name = 'finance_order';
					$order_list = 'finance_list';
					break;
			case 12:
			case 13:
					$order_name = 'otherfinance_order';
					$order_list = 'otherfinance_list';
					break;
			case 14:
					$order_name = 'gro_order';
					$order_list = '';
					break;
		}
		
		return array('order_name'=>$order_name,'order_list'=>$order_list);;
	
	}
}