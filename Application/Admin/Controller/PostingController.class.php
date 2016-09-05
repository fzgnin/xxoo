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
class PostingController extends AdminController {

    /**
     * 过账系统首页
     * 黄
     */
    public function index(){
       
		//保留主页
		$this->display(); // 输出模板
	 
    }
	
	//过账操作列表
	public function postinglist()
	{
		//列表
		$filter = $this->query_array();
		$this->send_out($filter);
	}
	
	//ajax操作
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

	//获取信息get函数
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
	  
	  //根据时间类型判断时间
	  if(1 == $filter['date_type'])
	  {
	    $where = ' o.add_time>='.$filter['begin_time'].' and o.add_time<'.$filter['end_time'];
	  }else
	  {
	    $where = ' o.post_time>='.$filter['begin_time'].' and o.post_time<'.$filter['end_time'];
	  }
	  
	  
	  $where .= ' and o.status = '.$filter['status'];
	  
	  if($filter['order_type'])
	  {
		  $where .= ' and o.order_type = '.$filter['order_type'];
	  }
		
	  
	  //根据传值调取相关的信息
	  
	  //销售出库单
	  if(1 == $filter['order_type'] || 2 == $filter['order_type'])
	  {	
		
		$list = M('order')->alias('o')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		$a_amount = M('order')->alias('o')->where($where)->sum('g_amount');
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   
		   //判断是否分单
		   $feat = M('bumen_feat')->where('order_id = '.$list[$k]['id'])->select();
		   //存在说明已分单
		   if($feat)
		   {
		     //判断分单金额是否相符0无分单数据，1分单金额不符，2无分单，3已分单
			 $featamount = 0;
			 $featnum = count($feat);
			 foreach($feat as $ko => $vo)
			 {
			   $featamount += $vo['feat'];
			 }
			 
			 //判断金额是否异常
			 if($featamount != $list[$k]['g_amount'])
			 {
			   $list[$k]['feat'] = 1;
			 }else
			 {
			   if($featnum > 1)
			   {
			     $list[$k]['feat'] = 3;
			   }else
			   {
			     $list[$k]['feat'] = 2;
			   }
			 }
			 
		   }else
		   {
		     $list[$k]['feat'] = 0;
		   }
		   
		 }
		}
		
		//分页
		$count = M('order')->alias('o')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Posting:order_list';
	  
	  }
	  
	  elseif(3 == $filter['order_type'] || 4 == $filter['order_type'])
	  {
				
		$list = M('stock_order')->alias('o')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		$a_amount = M('stock_order')->alias('o')->where($where)->sum('j_amount');
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['suppliername'] = M('supplier')->where(array('id'=>$v['supplier_id']))->getField('suppliername'); 
		   $list[$k]['username'] = M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
		 }
		}
		
		//分页
		$count = M('stock_order')->alias('o')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Posting:stockorder_list';
	  
	  
	  }
	  
	  
	  elseif(5 == $filter['order_type'] || 6 == $filter['order_type'])
	  {
				
		$list = M('income_order')->alias('o')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		$a_amount = M('income_order')->alias('o')->where($where)->sum('c_amount');
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['suppliername'] = M('supplier')->where(array('id'=>$v['supplier_id']))->getField('suppliername'); 
		   $list[$k]['username'] = M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
		 }
		}
		
		//分页
		$count = M('income_order')->alias('o')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Posting:incomeorder_list';
	  
	  
	  }
	  
	  
	  elseif(7 == $filter['order_type'] || 8 == $filter['order_type'])
	  {
				
		$list = M('inventory_order')->alias('o')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		$a_amount = M('inventory_order')->alias('o')->where($where)->sum('j_amount');
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['username'] = M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
		 }
		}
		
		//分页
		$count = M('inventory_order')->alias('o')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Posting:inventoryorder_list';
	  
	  
	  
	  
	  }
	  
	  //收款单和付款单
	  
	  elseif(10 == $filter['order_type'] || 11 == $filter['order_type'])
	  {
	    $list = M('finance_order')->alias('o')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		$a_amount = M('finance_order')->alias('o')->where($where)->sum('j_amount');
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['username'] = M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
		   
		   $list[$k]['account'] = M('finance_list')->where('order_id = '.$v['id'])->getField('cname',true);
		   
		   $list[$k]['account'] = implode(',',$list[$k]['account']);
		 }
		}
		
		//分页
		$count = M('finance_order')->alias('o')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Posting:finance_list';
	  
	  } 
	  
	  //其他收付款单操作
	  elseif(12 == $filter['order_type'] || 13 == $filter['order_type'])
	  {
	    $list = M('otherfinance_order')->alias('o')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['account'] = M('otherfinance_list')->where('order_id = '.$v['id'])->getField('cname',true);
		   
		   $list[$k]['account'] = implode(',',$list[$k]['account']);
		 }
		}
		
		
		$a_amount = M('otherfinance_order')->alias('o')->where($where)->sum('amount');
		
		//分页
		$count = M('otherfinance_order')->alias('o')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Posting:otherfinance_list';
	  
	  }   
	  
	
	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,$filter['page_num']);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);
	  
	  return array('list' => $list ,'show'=> $show ,'model_t' => $model_t,'a_amount'=>$a_amount );
	
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
	   'type' => I('type'),
	   'status' => I('status',0),
	   'order_type' => I('order_type',1),
	   'name' => I('name'),
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'date_type' => I('date_type',1),
	   'page_num'=>I('page_num',10),
	  );
	  
	 return $filter;
	
	}
	
	//页面输出统一函数
	private function send_out($filter)
	{
		$list = $this->get_list($filter);
		
		$this->assign('filter',$filter);// 赋值分页输出
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('a_amount',$list['a_amount']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display();
	
	}
	
	
	
	//黄线可过账操作这个好像要老老实实的写
	public function posting()
	{
	   //先接收参数
	   $id = I('id');
	   
	   $order_type = I('order_type');
	   
	   $post_type = I('post_type');
	   
	   $this->assign('post_type',$post_type);
	   
	   //判断订单的类型进行相关的过账操作
	   
	   if(1 == $order_type || 2 == $order_type)
	   {
	      //取出订单的信息
		 $order_info = M('order')->where('id = '.$id)->find();

		 //取出商品信息
		 $goods_list = M('order_goods')->where('order_id = '.$id)->select();
		 
		 $order_info['c_amount'] = 0;
		 
		 //根据商品取出库存商品单价和总价
		 foreach($goods_list as $k=>$v)
		 {
		    //取出商品平均价没有取默认单价
			$price = M('warehouse')->where('goods_id = '.$v['goods_id'])->getField('averages');
			
			$goods_list[$k]['cost_price'] = $price?$price:M('goods')->where('id = '.$v['goods_id'])->getField('cost_one');
			
			$goods_list[$k]['cost_total'] = $goods_list[$k]['cost_price']*$v['num'];
			
			$order_info['c_amount'] += $goods_list[$k]['cost_total'];
		 
		 }
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('goods_list',$goods_list);
	 
	     $this->display('order');
	   
	   
	   
	   }
	   
	   //库存
	   elseif(3 == $order_type || 4 == $order_type)
	   {
	     //取出订单的信息
		 $order_info = M('stock_order')->where('id = '.$id)->find();
		 
		 //取出用户名
		 $order_info['username'] = M('member')->where(array('uid'=>$order_info['uid']))->getField('nickname');
		 
		 //取出商品信息
		 $goods_list = M('stock_goods')->where('order_id = '.$id)->select();
		 
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('goods_list',$goods_list);
	 
	     $this->display('stock');
		 
	   
	   }
	   
	   //损益
	   elseif(5 == $order_type || 6 == $order_type)
	   {
	     //取出订单的信息
		 $order_info = M('income_order')->where('id = '.$id)->find();
		 
		 //取出用户名
		 $order_info['username'] = M('member')->where(array('uid'=>$order_info['uid']))->getField('nickname');
		 
		 //取出商品信息
		 $goods_list = M('income_goods')->where('order_id = '.$id)->select();
		 
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('goods_list',$goods_list);
	 
	     $this->display('income');
		 
	   
	   }
	   
	   //库存盘点单的相关过账操作 && 库存初始单相关过账操作
	   elseif(7 == $order_type || 8 == $order_type)
	   {
	     //第一步，获取当前订单的详细信息
		 $order_info = 	M('inventory_order')->where('id = '.$id)->find();
		 
		 //取出用户名
		 $order_info['username'] = M('member')->where(array('uid'=>$order_info['uid']))->getField('nickname');
		 
		 $goods_list = M('inventory_goods')->where('order_id = '.$id)->select();
		 
		 //循环商品表取出现有商品库存
		 
		 foreach($goods_list as $k =>$v)
		 {
		   $goods_list[$k]['s_num'] = M('warehouse')->where('goods_id = '.$v['goods_id'])->getField('num');
		   $goods_list[$k]['s_num'] = $goods_list[$k]['s_num']?$goods_list[$k]['s_num']:0;
		   $goods_list[$k]['price'] = M('goods')->where('id = '.$v['goods_id'])->getField('cost_one');
		 
		 }
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('goods_list',$goods_list);
	 
	     $this->display('inventory');
	   
	   }
	   
	   
	   //收款单和付款单的过账操作
	   elseif(10 == $order_type || 11 == $order_type)
	   {
		 //第一步，获取当前订单的详细信息
		 $order_info = 	M('finance_order')->where('id = '.$id)->find();
		 
		 //取出用户名
		 $order_info['username'] = M('member')->where(array('uid'=>$order_info['uid']))->getField('nickname');
		 
		 $money_list = M('finance_list')->where('order_id = '.$id)->select();
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('money_list',$money_list);
	 
	     $this->display('finance');
	   
	   }
	   //其他收款单和其他付款单
	   elseif(12 == $order_type || 13 == $order_type)
	   {
		 //第一步，获取当前订单的详细信息
		 $order_info = 	M('otherfinance_order')->where('id = '.$id)->find();
		 
		 $money_list = M('otherfinance_list')->where('order_id = '.$id)->select();
		 
		 foreach($money_list as $k => $v)
		 {
		 	$money_list[$k]['type_name'] = M('paycategory')->where(array('id'=>$v['pay_type']))->getField('name');
		 }
		 
		 //获取辅助核算
		 $assist_list = M('otherfinance_assist')->where('order_id = '.$id)->select();
		 
		 foreach($assist_list as $k=>$v)
		 {
			$assist = M('assist')->where('id = '.$v['assist_id'])->find();
			$assist_list[$k]['assist_name'] = $assist['name'];
		 }
		 
		 $this->assign('assist_list',$assist_list);
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('money_list',$money_list);
	 
	     $this->display('otherfinance');
	   
	   }
	   elseif(14 == $order_type)
	   {
		 //第一步，获取当前订单的详细信息
		 $order_info = 	M('giro_order')->where('id = '.$id)->find();
		 
		 $this->assign('order_info',$order_info);
		 
	     $this->display('diro');
	   }
	   
	   elseif(15 == $order_type || 16 == $order_type)
	   {
	   		 //取出订单的信息
		 $order_info = M('distribution_order')->where('id = '.$id)->find();

		 //取出商品信息
		 $goods_list = M('distribution_goods')->where('order_id = '.$id)->select();
		 
		 $order_info['c_amount'] = 0;
		 
		 //根据商品取出库存商品单价和总价
		 foreach($goods_list as $k=>$v)
		 {
		    //取出商品平均价没有取默认单价
			$price = M('warehouse')->where('goods_id = '.$v['goods_id'])->getField('averages');
			
			$goods_list[$k]['cost_price'] = $price?$price:M('goods')->where('id = '.$v['goods_id'])->getField('cost_one');
			
			$goods_list[$k]['cost_total'] = $goods_list[$k]['cost_price']*$v['num'];
			
			$order_info['c_amount'] += $goods_list[$k]['cost_total'];
		 
		 }
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('goods_list',$goods_list);
	 
	     $this->display('distribution');
	   
	   }

	}
	
	public function posting_insert()
	{
		$id = I('id');
		
		$order_type = I('order_type');
		
		$time = time();
		
		//判断当前时间是否已经结账
		
		//取最后的结账时间
		$end_date = M('checkout')->order('id desc')->limit(1)->getField('end_date');
		  
		//如果有结账时间且订单的开单时间小于结账时间返回错误
		if($end_date && $time < strtotime($end_date)+86400)
		{
		  $this->error('当前日期已经结账不能再过账！');
		}
	   
		if(!$id || !$order_type)
		{
		 $this->error('传值错误');
		}
		
		$info = $this->get_info($id,$order_type);
		
		//暂时加入老数据暂不支持过账反过账操作
		if(4 != $info['order_info']['mtype'] && (12 == $info['order_info']['order_type'] || 13 == $info['order_info']['order_type']))
		{
			//$this->error('暂时不支持，老数据的过账操作，请联系管理员！');
		}
		
		if(4 == $info['order_info']['mtype'])
		{
			//$this->error('暂时不能操作！');
		}
		
		//判断订单是否符合过账的条件（1.是否已经过账，2.订单是否存在,3.开单日期是否已经结账（3先不做因为存在一月份的单子三月份再过账））
		if(!$info['order_info'] || 1 == $info['order_info']['status'])
		{
			$this->error('此订单不存在或已过账！');
		}
		
		//获取各项数据
		$res_data = $this->make_data($info['order_info'],$info['order_goods'],1);
		//print_r($res_data);exit;
		//开启回滚
		M($info['o_table'])->startTrans();
		//存入数据
		$res_result = $this->save_data($info['order_info'],$res_data,1);
		
		if($res_result)
		{
			M($info['o_table'])->commit();//成功则提交
			$this->success('过账成功！');
		}else
		{
			M($info['o_table'])->rollback();//不成功，则回滚
			$this->error('过账失败！');
		}
		
	   
	   //根据订单类型做相关的过账操作
	   if(1 == $order_type || 2 == $order_type)
	   {		  
	   }   
	   //厂家出入库单据过账
	   else if(3 == $order_type || 4 == $order_type)
	   {
	   }
	   
	   else if(7 == $order_type || 8 == $order_type)
	   {
	      //获取订单的详细信息
		  $order_goods = M('inventory_goods')->where('order_id = '.$id)->select();
		  $order_info = M('inventory_order')->where('id = '.$id)->find();
		  
		  $goods_data = array();
		  $warehouse_data = array();
		  $data_log = array();
		  
		  //判断库存是否有改动
		  $error_open = 0;
		  
		  //更改商品表库库存数量
		  foreach($order_goods as $k => $v)
		  {
		    //更新商品表数据	
			$goods_data[] = array(
			'id' => $v['goods_id'],
			'num' =>$v['n_num'],
			);
			
			$price = M('goods')->where('id = '.$v['goods_id'])->getField('cost_one');
			
			
			$now_num = M('warehouse')->where('goods_id = '.$v['goods_id'])->getField('num');
			
			if($v['num'] != $now_num)
			{
			 $error_open = 1;
			}
			
			//更新仓库表数据
			$warehouse_data[] = array(
			'id'=>M('warehouse')->where('goods_id = '.$v['goods_id'])->getField('id'),
			'num'=>$v['n_num'],
			'uptime'=>$time,
			'wid' => 1,
			'goods_id'=>$v['goods_id'],
			'goodsname'=>$v['goodsname'],
			'price'=> $price,
			'totalprice'=>$price*$v['n_num'],
			);
			
			
			
			//写入仓库入库日志
			$data_log[] = array(
			'id' => (7 == $order_info['order_type'])?'':M('warehouse_log')->where('goods_id = '.$v['goods_id'])->getField('id'),
			'order_id'=>$v['order_id'],
			'order_sn'=>$order_info['order_sn'],
			'goods_id'=>$v['goods_id'],
			'goodsname'=>$v['goodsname'],
			'num'=>(7 == $order_info['order_type'])?$v['l_num']:$v['n_num'],
			'remarks'=> (7 == $order_info['order_type'])?'库存盘点---'.$order_info['remarks']:'库存初始化---'.$order_info['remarks'],
			'order_type'=>$order_info['order_type'],
			'add_time'=>$time,
			'price'=>$price,
			'totalprice'=>$price*$v['l_num'],
			);
			
		  }
		  
		  if($error_open)
		  {
		    $this->error('库存已有变动，该单据不能过账！');
		  }
		  
		  if(count($goods_data) <= 0 || count($warehouse_data) <= 0 || count($data_log) <= 0)
		  {
		    $this->error('数据传输错误！');
		  }
		  
		  //已过账的单子返回错误
		  if(0 != $order_info['status'])
		  {
		     $this->error('此订单已过帐或无效！');
		  }
		  
		  //把各项数据写入数据库
		  
		  //开启事务处理
		  M('warehouse_log')->startTrans();
		  
		  if(M('warehouse')->addALL($warehouse_data,array(),true) && M('warehouse_log')->addALL($data_log,array(),true) && M('inventory_order')->where('id = '.$id)->setField('status',1))
		  {
		    
			//循环商品表写入商品的库存数量
			foreach($goods_data as $k =>$v)
			{
			  M('goods')->where('id = '.$v['id'])->setField('num',$v['num']);
			}
			
			
			M('warehouse_log')->commit();//成功则提交
			
			$this->success('过账成功！');
		  }else
		  {
		    M('warehouse_log')->rollback();//不成功，则回滚
			
			$this->error('过账失败！');
		  }
		  
	   
	   }

	}

	public function backoff_insert()
	{
		//接收参数
		$id = I('id');

		$order_type = I('order_type');

		$time = time();

		if(!$id || !$order_type)
		{
		$this->error('传值错误');
		}

		//判断当前时间是否已经结账
	   
	    //取最后的结账时间
	    $end_date = M('checkout')->order('id desc')->limit(1)->getField('end_date');
		
		$info = $this->get_info($id,$order_type);
		
		//暂时加入老数据暂不支持过账反过账操作
		if(4 != $info['order_info']['mtype'] && (12 == $info['order_info']['order_type'] || 13 == $info['order_info']['order_type']))
		{
			//$this->error('暂时不支持，老数据的反过账操作，请联系管理员！');
		}
		
		if(4 == $info['order_info']['mtype'])
		{
			//$this->error('暂时不能操作！');
		}
		
		//判断订单是否符合过账的条件（1.是否已经过账，2.订单是否存在,3.开单日期是否已经结账（3先不做因为存在一月份的单子三月份再过账））
		if(!$info['order_info'] || 0 == $info['order_info']['status'])
		{
			$this->error('此订单不存在或未过账！');
		}
		
		//判断过账日期是否已经结账
		if($info['order_info']['post_time'] < strtotime($end_date)+86400)
		{
			$this->error('当前日期已经结账不能再反过账！');
		}
		
		//获取各项数据
		$res_data = $this->make_data($info['order_info'],$info['order_goods'],2);
		
		//开启回滚
		M($info['o_table'])->startTrans();
		//存入数据
		$res_result = $this->save_data($info['order_info'],$res_data,2);

		if($res_result)
		{
			M($o_table)->commit();//成功则提交
			$this->success('反过账成功！');
		}else
		{
			M($o_table)->rollback();//不成功，则回滚
			$this->error('反过账失败！');
		}
	}
	
	
	public function posting_all()
	{
		$str = I('str');
		
		$post_type = I('post_type');
		
		$time = time();
		
		//判断当前时间是否已经结账
		   
		//取最后的结账时间
		$end_date = M('checkout')->order('id desc')->limit(1)->getField('end_date');
		  
		//如果有结账时间且订单的开单时间小于结账时间返回错误
		if($end_date && $time < strtotime($end_date)+86400)
		{
		  $this->error('当前日期已经结账不能再过账！');
		}	   
		
		//解析字符串
		$str_arr = explode(',',rtrim($str,','));
		
		$a_num = count($str_arr);
		
		$p_num = 0;
		
		foreach($str_arr as $v)
		{
			$arr_t = explode('-',$v);
		   	$p_num += $this->post_fun($arr_t[0],$arr_t[1],$post_type);			
		}
		
		$l_num = $a_num-$p_num;
		
		$info = "操作成功".$p_num."条，失败".$l_num."条";
		
		$this->success($info);
	
	}
	
	//批量过账
	private function post_fun($id,$order_type,$post_type)
	{
		   
		$time = time();
		
		if(!$id || !$order_type)
		{
		 return 0;
		}
		
		$info = $this->get_info($id,$order_type);
		
		//判断订单是否符合过账的条件（1.是否已经过账，2.订单是否存在,3.开单日期是否已经结账（3先不做因为存在一月份的单子三月份再过账））
		if(!$info['order_info'] || (1 == $info['order_info']['status'] && 1 == $post_type) || (0 == $info['order_info']['status'] && 2 == $post_type))
		{
			return 0;
		}

		//获取各项数据
		$res_data = $this->make_data($info['order_info'],$info['order_goods'],$post_type);
		
		//开启回滚
		M($info['o_table'])->startTrans();
		//存入数据
		$res_result = $this->save_data($info['order_info'],$res_data,$post_type);
		
		if($res_result)
		{
			M($info['o_table'])->commit();//成功则提交
			return 1;
		}else
		{
			M($info['o_table'])->rollback();//不成功，则回滚
			return 0;
		}
	}
	
	//制作仓库的库存信息和日志信息
	private function make_warehousedata($arr,$order_info,$checktype = true)
	{
		$array = array();
		
		$warehouse_data = array();
		
		$warehouse_datalog = array();
		
		$time = time();

		$fuhao = $checktype?1:-1;	
		
		//合并重复商品
		foreach ($arr as $v){
			$array[$v['goods_id']]['num'] += $v['num'];
			$array[$v['goods_id']]['goods_id'] = $v['goods_id'];
			$array[$v['goods_id']]['id'] = $v['id'];
			$array[$v['goods_id']]['order_id'] = $v['order_id'];
			$array[$v['goods_id']]['goodsname'] = $v['goodsname'];
			$array[$v['goods_id']]['code'] = $v['code'];
			$array[$v['goods_id']]['format'] = $v['format'];
			$array[$v['goods_id']]['supplier_id'] = $v['supplier_id'];
			$array[$v['goods_id']]['brand_id'] = $v['brand_id'];
			$array[$v['goods_id']]['price'] = $v['price'];
			$array[$v['goods_id']]['totalprice'] += $v['totalprice'];
			$array[$v['goods_id']]['c_price'] = $v['c_price'];
			$array[$v['goods_id']]['c_totalprice'] += $v['c_totalprice'];
			
		}
		
		
		//根据类型做相关操作（现存量减，总销量加，总销本加，现成本减）（现存量加，总进量加，总成本加，总均价变，现成本加）（现存量减，总进量减，总成本减，现成本减）
		foreach($array as $ko=>$vo)
		{
			//取出商品库存信息和商品信息
			$now_goods = M('warehouse')->where('goods_id = '.$vo['goods_id'])->find();
			
			$goods_info = M('goods')->where('id = '.$vo['goods_id'])->find();
			
			//根据订单类型做判断
			switch($order_info['order_type'])
			{
				case 1:
				case 2:
				case 15:
				case 16:
				$averages = $now_goods && 0 != $now_goods['averages']?$now_goods['averages']:$goods_info['cost_one'];//加权平均价
				$n_num = $now_goods['n_num']-$vo['num']*$fuhao;//现有库存量
				$a_num = $now_goods?$now_goods['a_num']:0;//总进货量
				$s_num = $now_goods['s_num']+$vo['num']*$fuhao;//总销量
				$a_amount = $now_goods?$now_goods['a_amount']:0;//总成本
				$fuhao_t = -1;
				break;
				case 3:
				case 4:
				$n_num = $now_goods['n_num']+$vo['num']*$fuhao;//现有库存量
				$a_num = $now_goods['a_num']+$vo['num']*$fuhao;//总进货量
				$s_num = $now_goods?$now_goods['s_num']:0;//总销量
				$a_amount = $now_goods['a_amount']+$vo['c_totalprice']*$fuhao;//总成本
				$averages = round($a_amount/$a_num,2);//加权平均价
				$averages = 0 == $averages?0 == $now_goods['averages']?$goods_info['cost_one']:$now_goods['averages']:$averages;
				$fuhao_t = 1;
				break;
				case 5:
				case 6:
				$n_num = $now_goods['n_num']+$vo['num']*$fuhao;//现有库存量
				$a_num = $now_goods['a_num']+$vo['num']*$fuhao;//总进货量
				$s_num = $now_goods?$now_goods['s_num']:0;//总销量
				$a_amount = $now_goods['a_amount']+$vo['c_totalprice']*$fuhao;//总成本
				$averages = round($a_amount/$a_num,2);//加权平均价
				$averages = 0 == $averages?0 == $now_goods['averages']?$goods_info['cost_one']:$now_goods['averages']:$averages;
				$fuhao_t = 1;
				break;

			}
			
			
			$warehouse_data[] = array(
					'id'=>$now_goods['id'],
					'uptime'=>$time,
					'wid' => 1,
					'goods_id'=>$vo['goods_id'],
					'goodsname'=>$vo['goodsname'],
					'averages'=>$averages,
					'n_num'=>$n_num,
					'a_num'=>$a_num,
					's_num'=>$s_num,
					'n_amount'=>$averages*$n_num,
					'a_amount'=>$a_amount,
					's_amount'=>$s_num*$averages,
			);
					   
			$warehouse_datalog[] = array(
					'order_id'=>$order_info['id'],
					'order_sn'=>$order_info['order_sn'],
					'goods_id'=>$vo['goods_id'],
					'goodsname'=>$vo['goodsname'],
					'num'=>$vo['num']*$fuhao_t,
					'n_num'=>$n_num,
					'a_num'=>$a_num,
					's_num'=>$s_num,
					'n_amount'=>$averages*$n_num,
					'a_amount'=>$a_amount,
					's_amount'=>$s_num*$averages,
					'averages'=>$averages,
					'remarks'=> $order_info['remarks'],
					'order_type'=>$order_info['order_type'],
					'add_time'=>$order_info['add_time'],
					'post_time'=>$time,
					'price'=>$vo['c_price']?$vo['c_price']:0,
					'totalprice'=>$vo['c_totalprice']?$vo['c_totalprice']:0,
			);
		
		}
	
	
		return array('data'=>$warehouse_data,'datalog'=>$warehouse_datalog);
	
	}
	
	//制作账户数据
	private function make_companydata($finance_list = array(),$order_info,$checktype = true)
	{
		$time = time();
		$c_data = array();
		$c_datalog = array();
		
		if(!$finance_list)
		{
			$finance_list[0] = array(
				'cid'=> $order_info['out_cid'],
				'cname' => $order_info['out_cname'],
				'money' => -1*$order_info['money'],
			); 
			$finance_list[1] = array(
				'cid'=> $order_info['in_cid'],
				'cname' => $order_info['in_cname'],
				'money' => $order_info['money'],
			);
		}

		//开始
		foreach($finance_list as $k=>$v)
		  {
		    //取出当前账户余额
		    $company = M('company')->where('id = '.$v['cid'])->find();
			$balance = $checktype?$company['money']+$v['money']:$company['money']-$v['money'];
			
			$c_datalog[] = array(
				'order_id' =>$order_info['id'],
				'order_sn' =>$order_info['order_sn'],
				'order_type' =>$order_info['order_type'],
				'mname' =>$order_info['mname'],
				'cid' =>$v['cid'],
				'cname' =>$v['cname'],
				'money' =>$v['money'],
				'amount' =>$order_info['amount'],
				'balance' =>$balance,
				'add_time' =>$order_info['add_time'],
				'post_time' =>$time,
				'remarks' =>$order_info['remarks'],
			);
			
			$c_data[] = array(
				'id'=>$v['cid'],
				'cid'=>$company['cid'],
				'cname'=>$company['cname'],
				'type'=>$company['type'],
				'add_time'=>$company['add_time'],
				'b_money'=>$company['b_money'],
				'money'=>$balance,
			);
		  }
		return array('c_data'=>$c_data,'c_datalog'=>$c_datalog);
	
	}
	
	//制作写入信息
	private function make_data($order_info,$order_goods,$post_type)
	{
		$time = time();
		
		$warehouse = array();
		
		$company = array();
		
		$subject = array();
		
		//根据过账类型输出数据
		//过账
		if(1 == $post_type)
		{
			switch($order_info['order_type'])
			{
				case 1:
				case 2:
						$gs_money = M('guest')->where('id = '.$order_info['guest_id'])->getField('money');
						$gs_log = array(
						  'gid'=>$order_info['guest_id'],
						  'gname'=>$order_info['gname'],
						  'order_id'=>$order_info['id'],
						  'order_type'=>$order_info['order_type'],
						  'order_sn'=>$order_info['order_sn'],
						  'amount'=>$order_info['g_amount']*-1,
						  'balance'=>$gs_money-$order_info['g_amount'],
						  'add_time'=>$order_info['add_time'],
						  'post_time'=>$time,
						  'remarks'=>$order_info['remarks'],
						);
						$warehouse = $this->make_warehousedata($order_goods,$order_info);
						break;
				case 3:
				case 4:
						//供应商信息
						$gs_money = M('supplier')->where('id = '.$order_info['supplier_id'])->getField('money');
						$gs_log = array(
						  'sid'=>$order_info['supplier_id'],
						  'sname'=>$order_info['suppliername'],
						  'order_id'=>$order_info['id'],
						  'order_type'=>$order_info['order_type'],
						  'order_sn'=>$order_info['order_sn'],
						  'amount'=>$order_info['c_amount'],
						  'balance'=>$order_info['c_amount']+$gs_money,
						  'remarks'=>$order_info['remarks'],
						  'add_time'=>$order_info['add_time'],
						  'post_time'=>$time,
						);
						$warehouse = $this->make_warehousedata($order_goods,$order_info);
						break;
				case 5:
				case 6:
						$gs_log = array();
						$warehouse = $this->make_warehousedata($order_goods,$order_info);
						break;
				case 10:
				case 11:
						//根据mtype判断客户类型
						if(1 == $order_info['mtype'])
						{
							//取出当余额
							$balance = M('guest')->where('id = '.$order_info['mid'])->getField('money');
						 	$balance += $order_info['amount'];
						  
							//形成店家日志log
							$gs_log = array(
								'gid' =>$order_info['mid'],
								'gname' =>$order_info['mname'],
								'order_id' =>$order_info['id'],
								'order_type' =>$order_info['order_type'],
								'order_sn' =>$order_info['order_sn'],
								'amount' =>$order_info['amount'],
								'balance' =>$balance,
								'add_time' =>$order_info['add_time'],
								'post_time' =>$time,
								'remarks' =>$order_info['remarks'],
							);  
						}
						//2为供应商
						elseif(2 == $order_info['mtype'])
						{
							//取出当余额
							$balance = M('supplier')->where('id = '.$order_info['mid'])->getField('money');
							$balance += $order_info['amount'];
						
							//形成供应商日志log
							$gs_log = array(
								'sid' =>$order_info['mid'],
								'sname' =>$order_info['mname'],
								'order_id' =>$order_info['id'],
								'order_type' =>$order_info['order_type'],
								'order_sn' =>$order_info['order_sn'],
								'amount' =>$order_info['amount'],
								'balance' =>$balance,
								'add_time' =>$order_info['add_time'],
								'post_time' =>$time,
								'remarks' =>$order_info['remarks'],
							);
						}
						$company = $this->make_companydata($order_goods,$order_info);
						break;
				case 12:
				case 13:
						
						if(4 == $order_info['mtype'])
						{
							$company = $this->make_companydata($order_goods,$order_info);
							$subject = $this->make_subjectdata($order_goods,$order_info);
						break;	
						}else
						{
							//根据mtype判断客户类型
							if(1 == $order_info['mtype'])
							{
								//取出当余额
								$balance = M('guest')->where('id = '.$order_info['mid'])->getField('money');
								$balance += $order_info['amount'];
							  
								//形成店家日志log
								$gs_log = array(
									'gid' =>$order_info['mid'],
									'gname' =>$order_info['mname'],
									'order_id' =>$order_info['id'],
									'order_type' =>$order_info['order_type'],
									'order_sn' =>$order_info['order_sn'],
									'amount' =>$order_info['amount'],
									'balance' =>$balance,
									'add_time' =>$order_info['add_time'],
									'post_time' =>$time,
									'remarks' =>$order_info['remarks'],
								);  
							}
							//2为供应商
							elseif(2 == $order_info['mtype'])
							{
								//取出当余额
								$balance = M('supplier')->where('id = '.$order_info['mid'])->getField('money');
								$balance += $order_info['amount'];
							
								//形成供应商日志log
								$gs_log = array(
									'sid' =>$order_info['mid'],
									'sname' =>$order_info['mname'],
									'order_id' =>$order_info['id'],
									'order_type' =>$order_info['order_type'],
									'order_sn' =>$order_info['order_sn'],
									'amount' =>$order_info['amount'],
									'balance' =>$balance,
									'add_time' =>$order_info['add_time'],
									'post_time' =>$time,
									'remarks' =>$order_info['remarks'],
								);
							}
							$company = $this->make_companydata($order_goods,$order_info);
							break;
						}

						/*$company = $this->make_companydata($order_goods,$order_info);
						$subject = $this->make_subjectdata($order_goods,$order_info);
						//print_r($company);
						//print_r($subject);exit;
						break;	*/	
				
				case 14:
						$gs_log = array();
						$company = $this->make_companydata($order_goods,$order_info);
						break;
				case 15:
				case 16:
						$gs_log = array();
						$warehouse = $this->make_warehousedata($order_goods,$order_info);
						break;
	
						
			}	  
			$order_data = array(
			  'status'=>1,
			  'post_time'=>$time,
			);
		}else
		{
			$gs_log = array();
			switch($order_info['order_type'])
			{
				case 1:
				case 2: 
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
				case 15:
				case 16:
						$warehouse = $this->make_warehousedata($order_goods,$order_info,false);
						break;
				case 10:
				case 11:
				case 14:
						$company = $this->make_companydata($order_goods,$order_info,false);
						break;
				case 12:
				case 13:
						$company = $this->make_companydata($order_goods,$order_info,false);
						$subject = $this->make_subjectdata($order_goods,$order_info,false);
						break;
			}
			$order_data = array(
			  'status'=>0,
			  'post_time'=>0,
			);
		}
		return array('gs_log'=>$gs_log,'warehouse'=>$warehouse,'order_data'=>$order_data,'company'=>$company,'subject'=>$subject);
	}
	
	//写入各项过账数据返回写入状态
	private function save_data($order_info,$res_data,$post_type)
	{
		
		switch($order_info['order_type'])
		{
			case 1:
			case 2:
					
					//修改订单状态 
					$res_order = M('order')->where('id = '.$order_info['id'])->save($res_data['order_data']);
					
					//设业绩表订单为已过账
					$res_bfeat = M('bumen_feat')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->save($res_data['order_data']);	
					
					//修改库存数量
					$res_warehouse = M('warehouse')->addALL($res_data['warehouse']['data'],array(),true);

					if(1 == $post_type)
					{
						//改变客户账户上金额
						$res_guest = M('guest')->where('id = '.$order_info['guest_id'])->setDec('money',$order_info['g_amount']);
						
						//记录库存日志
						$res_warehouse_log = M('warehouse_log')->addALL($res_data['warehouse']['datalog'],array(),true);
						
						//记录客户账户变动日志	
						$res_guest_log = M('guest_log')->add($res_data['gs_log']);
					}else
					{
						//改变客户账户上金额
						$res_guest = M('guest')->where('id = '.$order_info['guest_id'])->setInc('money',$order_info['g_amount']);
						
						//删除库存日志
						$res_warehouse_log = M('warehouse_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
						
						//删除客户账户变动日志	
						$res_guest_log = M('guest_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
					}
					
					$res = $res_guest && $res_order && $res_warehouse && $res_warehouse_log && $res_guest_log && $res_bfeat;
					break;
			case 3:
			case 4:
					//根据出库或入库进行供应商账户操作				
					//更改订单表状态 
					$res_order = M('stock_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);
					
					//改变库存
					$res_warehouse = M('warehouse')->addALL($res_data['warehouse']['data'],array(),true);

					if(1 == $post_type)
					{
						//改变供应商账户金额
						$res_sup = M('supplier')->where('id = '.$order_info['supplier_id'])->setInc('money',$order_info['c_amount']);
						
						//写入供应商账户明细
						$res_suplog = M('supplier_log')->add($res_data['gs_log']);
						
						//改变库存日志
						$res_warehouse_log = M('warehouse_log')->addALL($res_data['warehouse']['datalog'],array(),true);
					}else
					{
						//改变供应商账户金额
						$res_sup = M('supplier')->where('id = '.$order_info['supplier_id'])->setDec('money',$order_info['c_amount']);
						
						//删除该订单供应商账户明细
						$res_suplog = M('supplier_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
						
						//删除库存明细表日志
						$res_warehouse_log = M('warehouse_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
					}

					$res = $res_warehouse && $res_warehouse_log && $res_suplog && $res_order && $res_sup;
					break;	
			case 5:
			case 6:
					//根据出库或入库进行供应商账户操作				
					//更改订单表状态 
					$res_order = M('income_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);
					
					//改变库存
					$res_warehouse = M('warehouse')->addALL($res_data['warehouse']['data'],array(),true);

					if(1 == $post_type)
					{
						//改变库存日志
						$res_warehouse_log = M('warehouse_log')->addALL($res_data['warehouse']['datalog'],array(),true);
					}else
					{	
						//删除库存明细表日志
						$res_warehouse_log = M('warehouse_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
					}

					$res = $res_warehouse && $res_warehouse_log && $res_order;
					break;
			case 10:
			case 11:
					//更改订单表状态 
					$res_order = M('finance_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);

					//改变公司账户金额
					$res_company = M('company')->addALL($res_data['company']['c_data'],array(),true);

					if(1 == $post_type)
					{
						//改变用户账户金额
						if(1 == $order_info['mtype'])
						{
							$res_gs = M('guest')->where('id = '.$order_info['mid'])->setInc('money',$order_info['amount']);

							$res_gslog = M('guest_log')->add($res_data['gs_log']);
						}
						elseif(2 == $order_info['mtype'])
						{
							$res_gs = M('supplier')->where('id = '.$order_info['mid'])->setInc('money',$order_info['amount']);

							$res_gslog = M('supplier_log')->add($res_data['gs_log']);
						}else
						{
							$res_gs = 1;
							$res_gslog = 1;
						}

						$res_companylog = M('company_log')->addALL($res_data['company']['c_datalog'],array(),true);

					}else
					{
						//改变用户账户金额
						if(1 == $order_info['mtype'])
						{
							$res_gs = M('guest')->where('id = '.$order_info['mid'])->setDec('money',$order_info['amount']);

							$res_gslog = M('guest_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
						}
						elseif(2 == $order_info['mtype'])
						{
							$res_gs = M('supplier')->where('id = '.$order_info['mid'])->setDec('money',$order_info['amount']);

							$res_gslog = M('supplier_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
						}else
						{
							$res_gs = 1;
							$res_gslog = 1;
						}

						$res_companylog = M('company_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();

					}

					$res = $res_gs && $res_gslog && $res_company && $res_companylog && $res_order;
					break;
					
			case 12:
			case 13:
			
					if(4 != $order_info['mtype'])
					{
						//更改订单表状态 
						$res_order = M('otherfinance_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);
	
						//改变公司账户金额
						$res_company = M('company')->addALL($res_data['company']['c_data'],array(),true);
	
						if(1 == $post_type)
						{
							//改变用户账户金额
							if(1 == $order_info['mtype'])
							{
								$res_gs = M('guest')->where('id = '.$order_info['mid'])->setInc('money',$order_info['amount']);
	
								$res_gslog = M('guest_log')->add($res_data['gs_log']);
							}
							elseif(2 == $order_info['mtype'])
							{
								$res_gs = M('supplier')->where('id = '.$order_info['mid'])->setInc('money',$order_info['amount']);
	
								$res_gslog = M('supplier_log')->add($res_data['gs_log']);
								
							}else
							{
								$res_gs = 1;
								$res_gslog = 1;
							}
	
							$res_companylog = M('company_log')->addALL($res_data['company']['c_datalog'],array(),true);
	
						}else
						{
							//改变用户账户金额
							if(1 == $order_info['mtype'])
							{
								$res_gs = M('guest')->where('id = '.$order_info['mid'])->setDec('money',$order_info['amount']);
	
								$res_gslog = M('guest_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
							}
							elseif(2 == $order_info['mtype'])
							{
								$res_gs = M('supplier')->where('id = '.$order_info['mid'])->setDec('money',$order_info['amount']);
	
								$res_gslog = M('supplier_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
							}else
							{
								$res_gs = 1;
								$res_gslog = 1;
							}
	
							$res_companylog = M('company_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
	
						}
						
						//var_dump($res_gs);
						//var_dump($res_gslog);
						//var_dump($res_company);
						//var_dump($res_companylog);
						//var_dump($res_order);
	
						$res = $res_gs && $res_gslog && $res_company && $res_companylog && $res_order;
						break;
					}else
					{
						//更改订单表状态 
						$res_order = M('otherfinance_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);
	
						//改变公司账户金额
						$res_company = M('company')->addALL($res_data['company']['c_data'],array(),true);
	
						if(1 == $post_type)
						{
							//账户日志
							$res_companylog = M('company_log')->addALL($res_data['company']['c_datalog']);
							
							//记录科目日志
							$res_subjectlog = M('subject_log')->add($res_data['subject']['s_datalog']);
							
							//科目积分加减
							$res_subject = M('subject')->where('id = '.$order_info['mid'])->setInc('money',$order_info['amount']);
	
						}else
						{
							//删除账户日志
							$res_companylog = M('company_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
							
							//删除科目日志
							$res_subjectlog = M('subject_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
							
							//还原科目积分
							$res_subject = M('subject')->where('id = '.$order_info['mid'])->setDec('money',$order_info['amount']);
						}
	
						$res = $res_subject && $res_subjectlog && $res_company && $res_companylog && $res_order;
						break;
					}
					/*
					
					*/
					
			case 14:
					//更改订单表状态 
					$res_order = M('giro_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);

					//改变公司账户金额
					$res_company = M('company')->addALL($res_data['company']['c_data'],array(),true);

					if(1 == $post_type)
					{
						$res_companylog = M('company_log')->addALL($res_data['company']['c_datalog'],array(),true);
					}else
					{
						$res_companylog = M('company_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
					}
					
					//var_dump($res_company);
					//var_dump($res_companylog);
					//var_dump($res_order);

					$res = $res_company && $res_companylog && $res_order;
					break;	
			case 15:
			case 16:
					
					//修改订单状态 
					$res_order = M('distribution_order')->where('id = '.$order_info['id'])->save($res_data['order_data']);
					
					//设业绩表订单为已过账
					$res_bfeat = M('bumen_feat')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->save($res_data['order_data']);	
					
					//修改库存数量
					$res_warehouse = M('warehouse')->addALL($res_data['warehouse']['data'],array(),true);

					if(1 == $post_type)
					{	
						//记录库存日志
						$res_warehouse_log = M('warehouse_log')->addALL($res_data['warehouse']['datalog'],array(),true);
					}else
					{
						//删除库存日志
						$res_warehouse_log = M('warehouse_log')->where('order_id = '.$order_info['id'].' and order_type = '.$order_info['order_type'])->delete();
					}
					
					$res = $res_order && $res_warehouse && $res_warehouse_log && $res_bfeat;
					break;
		}
		
		
		return $res;
	}
	
	//获取订单数据
	private function get_info($id,$order_type)
	{
		switch($order_type)
		{
		case 1:
		case 2:
			$o_table = 'order';
			$g_table = 'order_goods';
			break;
		case 3:
		case 4:
			$o_table = 'stock_order';
			$g_table = 'stock_goods';
			break;
		case 5:
		case 6:
			$o_table = 'income_order';
			$g_table = 'income_goods';
			break;
		case 10:
		case 11:
			$o_table = 'finance_order';
			$g_table = 'finance_list';
			break;
		case 12:
		case 13:
			$o_table = 'otherfinance_order';
			$g_table = 'otherfinance_list';
			break;
		case 14:
			$o_table = 'giro_order';
			$g_table = '';
		case 15:
		case 16:
			$o_table = 'distribution_order';
			$g_table = 'distribution_goods';
			break;
		
		}
		//取值
		if($g_table)
		{
			$order_goods = M($g_table)->where('order_id = '.$id)->select();
		}else
		{
			$order_goods = '';
		}
		
		$order_info = M($o_table)->where('id = '.$id)->find();
		
		return array('order_info'=>$order_info,'order_goods'=>$order_goods,'o_table'=>$o_table);
	
	}
	
	
	
	//制作科目的积分加减信息和日志信息
	private function make_subjectdata($order_goods,$order_info,$checktype = true)
	{
		$s_data = array();
		$s_datalog = array();
		
		//先取出当前账户资金
		$subject = M('subject')->where('id = '.$order_info['mid'])->find();
		$balance = $checktype?$subject['money']+$order_info['amount']:$subject['money']-$order_info['amount'];
		
		$s_data = array(
			'id'=>$order_info['mid'],
			'name'=>$subject['name'],
			'assist_id'=>$subject['assist_id'],
			'status'=>$subject['status'],
			'money'=>$balance,
		);
		
		$s_datalog = array(
			'sid' =>$order_info['mid'],
			'sname' =>$subject['name'],
			'order_id' =>$order_info['id'], 
			'order_type' =>$order_info['order_type'],
			'order_sn' =>$order_info['order_sn'],
			'amount' =>$order_info['amount'],
			'add_time' =>$order_info['add_time'],
			'post_time' =>time(),
			'remarks' =>$order_info['remarks'],
		);
		return array('s_data'=>$s_data,'s_datalog'=>$s_datalog);
	}
	
	
	

}