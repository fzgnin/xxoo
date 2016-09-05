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
 * 后台客户控制器
 * 报损单报溢单
 * huang
 */
class IncomeController extends AdminController {

    /**
     * 报损报溢
     * 黄
     */
	public function index(){
	
		$this->display(); // 输出模板
	 
	}


    //新增出货单表

    public function add(){
	
		//生成订单编号固定格式
	
		$time = date("Y-m-d");
		
		$num = $this->get_order_id($time,5);
		
		//对num进行处理不足5位自动补零
		
		$num=sprintf("%05d", $num);
	
		$order_num = 'QTRK'.$time.'-'.$num;
		
		$this->assign('order_num',$order_num);
		
		$time_1 = date("Y-m-d H:i");
		
		$this->assign('time',$time_1);
		
		$this->assign('order_type',5);
		
		$this->assign('username',$_SESSION['onethink_admin']['user_auth']['username']);
		
		$this->display(); // 输出模板
    }
	
	public function backorder()
	{
	$time = date("Y-m-d");
	
	$num = $this->get_order_id($time,6);
	
	//对num进行处理不足5位自动补零
	
	$num=sprintf("%05d", $num);

	$order_num = 'QTCK'.$time.'-'.$num;
	
	$this->assign('order_num',$order_num);
	
	$time_1 = date("Y-m-d H:i");
	
	$this->assign('time',$time_1);
	
	$this->assign('order_type',6);
	
	$this->assign('username',$_SESSION['onethink_admin']['user_auth']['username']);
	
    $this->display(); // 输出模板
	
	
	}
	
	
	
	public function orderlist()
	{
	  //获取客服填的销售单子
	  $filter = $this->query_array();
	  
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
	  
	  //判断订单是否过账，已过账订单不能修改
	  if(1 == $order['order_info']['status'])
	  {
	    $this->error('已过账订单暂不能修改！');
	  }
	 
	  $this->assign('order_info',$order['order_info']);
	 
	  $this->assign('goods_list',$order['goods_list']);
	  
	
	  $this->display();
	}
	
	public function order_info()
	{
	 $id = I('id');
	 
	 $order = $this->get_order_info($id);
	 
	 $this->assign('order_info',$order['order_info']);
	 
	 $this->assign('goods_list',$order['goods_list']);
	 
	 $this->display();
	}
	
	
	
	//插入商品到数据库
	public function insert()
	{
	  //获取post数据
	  
	  $add_time = strtotime(I('add_time'));
	  $order_sn = I('order_sn');
	  $user_id = I('user_id');
	  $goods_id = I('goods_id');
	  $goodsname = I('goodsname');
	  $code = I('code');
	  $format = I('format');
	  $num = I('num');
	  $price = I('price');
	  $price_total = I('price_total');
	  $c_price = I('c_price');
	  $c_price_total = I('c_price_total');

	  $total_num = I('total_num');
	  $total_s_price = I('total_s_price');
	  $total_j_price = I('total_j_price');
	  
	  $remarks = I('remarks');
	  $warehouse = I('warehouse');
	  $order_type = I('order_type',5);
	  $id = I('id');
	  $order_goods_id = I('order_goods_id');
	  
	  $not_null = 0;
	  foreach($goods_id as $v)
	  {
	   if($v)
	   {
	    $not_null = 1;
	   }
	  }
	  
	  if($not_null == 0 || !$warehouse)
	  {
	   $this->error('请填写完整信息！');
	  }
	  
	  //判断当前单据日期是否已经结账
	  //取最后的结账时间
	  $end_date = M('checkout')->order('id desc')->limit(1)->getField('end_date');
	  
	  //如果有结账时间且订单的开单时间小于结账时间返回错误
	  if($end_date && $add_time < strtotime($end_date)+86400)
	  {
	    $this->error('该日期范围内已经结账不能再下单！');
	  }
	  
	  //暂时设为过账后不允许编辑
	  if($id)
	  {
	    if(1 == M('stock_order')->where('id = '.$id)->getField('status'))
		{
		  $this->error('此单据已经过账，暂不允许更改！');
		}
	  }
	  
	  //判断订单号是否重复 
	  
		  if(!$id && M('income_order')->where("order_sn = '$order_sn'")->find())
		  {
		   $this->error('订单号重复！');
		  }
		  
		   //先写入订单表
		   
		   $fuhao = $this->order_fuhao($order_type);
		   
		   $goods_data = array();
		   
		   foreach($goods_id as $k=>$v)
		   {
		      if($num[$k] > 0 && $v)
			  {
			    $goods_info = M('goods')->where('id = '.$v)->find();
				$num[$k] = $num[$k]*$fuhao;
				
				$goods_data[] = array(
				  'id'=>$order_goods_id[$k],
				  'order_id'=>'',
				  'goods_id'=>$v,
				  'brand_id'=>$goods_info['brand_id'],
				  'goodsname'=>$goodsname[$k],
				  'code'=>$code[$k],
				  'format'=>$format[$k],
				  'num'=>$num[$k],
				  'price'=>$price[$k],
				  'totalprice'=>$price_total[$k]*$fuhao,
				  'c_price'=>$c_price[$k],
				  'c_totalprice'=>$c_price_total[$k]*$fuhao,
				);
			  }
		   }	   
		  
		  //获取数据
		  $order_data = array(
		   'order_sn'=>$order_sn,
		   'amount'=>$total_s_price*$fuhao,
		   'c_amount'=>$total_j_price*$fuhao,
		   'add_time'=>$add_time,
		   'insert_time'=>time(),
		   'warehouse'=>$warehouse,
		   'remarks'=>$remarks,
		   'uid'=>UID,
		   'order_type'=>$order_type,
		   'id'=>$id,
		  );
		  
		  //使用replace into 做新增或更新操作
		  
		  $order_id = M('income_order')->add($order_data,array(),true);
		  
		  if(!$order_id)
		  {
		   $this->error('插入或更新订单失败！');
		  }
		  
		 //插入商品表循环商品id
		  
		  foreach($goods_data as $ko=>$vo)
		  {
			$goods_data[$ko]['order_id'] = $order_id;
		  }

		  if(count($goods_data)>0)
		  {
		  
		    //获取删掉的商品的id数组
			$id_array = M('income_goods')->where('order_id = '.$id)->getField('id',true);
			
		    if(M('income_goods')->addALL($goods_data,array(),true))
			{
			  //判断是新增还是编辑
			  if($id)
			  {
				if($order_goods_id)
				{
				  $left_array = array_diff($id_array,$order_goods_id);
				}else
				{
				  $left_array = $id_array;
				}
				
				//批量删除
				if(count($left_array) >0)
				{
				  $str = implode(',',$left_array);
				  
				  M('income_goods')->delete($str); 
				  
				}
				
				$this->success('编辑成功！');die();
						 
			  }else
			  {
			   //设置订单号为已使用
			   M('session_all_id')->where("user_id=".UID." and order_type = ".$order_type)->setField('is_use',1);
			   $this->success('下单成功！');die();
			  }
			
			}else
			{
			  $this->error('下单失败！');
			}
		  
		  }
		  else
		  {
		  
		    $this->error('无商品下单！');
		  
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
	  
	  //判断是否过账
	  if(1 == M('income_order')->where('id = '.$id)->getField('status'))
	  {
	    $this->error('此订单已过账，请联系财务进行删除！');
	  }
	  
	  if(M('income_order')->where('id = '.$id)->delete() && M('income_goods')->where('order_id = '.$id)->delete())
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

	  if($filter['order_by'])
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }  
	  
	  //获取相关供应商列表
	  
	  //根据日期类型判断
	  switch($filter['date_type'])
	  {
	    case 1:
		$where = 'uid = '.UID.' and add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
		break;
		case 2:
		$where = 'uid = '.UID.' and post_time>='.$filter['begin_time'].' and post_time<'.$filter['end_time'];
		break;
		case 3:
		$where = 'uid = '.UID.' and insert_time>='.$filter['begin_time'].' and insert_time<'.$filter['end_time'];
		break;
		default:
		$where = 'uid = '.UID.' and add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
	  }
	  
	  switch($filter['status'])
	  {
	    case 1:
		$where .= ' and status = 0';
		break;
		case 2:
		$where .= ' and status = 1';
		break;
		case 3:
		$where .= ' and status = 2';
		break;
		default:;
	  }
	  
	  if($filter['order_type'])
	  {
	    $where .= ' and order_type = '.$filter['order_type'];
	  }
	  
	  if($filter['order_sn'])
	  {
	    $where .= ' and order_sn like "%'.$filter['order_sn'].'%"';
	  }
	  
	  if($filter['amount'])
	  {
	    $where .= ' and c_amount like "%'.$filter['amount'].'%"';
	  }
	  
	  if($filter['remarks'])
	  {
	    $where .= ' and remarks like "%'.$filter['remarks'].'%"';
	  }

				
		$list = M('income_order')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['c_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['c_amount'];
		   $list[$k]['username'] = M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
		 }
		}
		
		//分页
		$count = M('income_order')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Income:order_list';

	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);// 赋值分页输出
	  
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
	   'order_type' => I('order_type'),
	   'supplier_id' => I('supplier_id'),
	   'name' => I('name'),
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'date_type' => I('date_type',1),
	   'suppliername' => I('suppliername'),
	   'order_sn' => I('order_sn'),
	   'amount' => I('amount'),
	   'remarks' => I('remarks'),
	   'status' => I('status'),
	  );
	  
	 return $filter;
	
	}
	
	/*获取订单号的函数
	*黄线可
	*2016.1.1
	*/

	
	private function get_order_id($time,$order_type)
	{
	 //查询当前订单编号表里面有没有对应的id
	
	//先查询今天有没有可用的订单号如果有就用这个如果没有新增一个
	
	$num = M('session_all_id')->where("user_id = ".UID." and add_time = '".$time."' and order_type = ".$order_type." and is_use = 0")->getField('num');
	
	if(!$num)
	{
	 //查询今天该类型的最大单数
	 $big_num = M('session_all_id')->where("order_type = ".$order_type." and add_time = '".$time."'")->max('num');
	 
	 //给这个操作员赋予新值
	 $num = $big_num + 1;

	 //初始化data数据
	 $data = array(
	  'user_id'=>UID,
	  'add_time'=>$time,
	  'order_type'=>$order_type,
	  'num'=>$num,
	  'is_use'=>0,
	 );

	 //查询有没有属于该用户该类型的订单号
	 $res_id = M('session_all_id')->where("user_id = ".UID." and order_type = ".$order_type)->getField('id');
		 if($res_id)
		 {
		  M('session_all_id')->where('id = '.$res_id)->save($data);
		 }else
		 {
		  M('session_all_id')->add($data);
		 }
	 
	}
	
	return $num;
	
	}
	
	private function get_order_info($id)
	{
	 
	 //获取订单的详细信息
	 $order_info = M('income_order')->where(array('id'=>$id))->find();

	 $order_info['username'] = M('member')->where(array('uid'=>$order_info['uid']))->getField('nickname');
	 
	 $fuhao = $this->order_fuhao($order_info['order_type']);
		   
	 $order_info['amount'] = $fuhao*$order_info['amount'];
	 $order_info['c_amount'] = $fuhao*$order_info['c_amount'];
	 
	 //获取订单商品信息
	 $goods_list = M('income_goods')->where('order_id = '.$id)->select();
	 
	 foreach($goods_list as $k=>$v)
	 {
	   $goods_list[$k]['price'] = $goods_list[$k]['price'];
	   $goods_list[$k]['num'] = $fuhao*$goods_list[$k]['num'];
	   $goods_list[$k]['totalprice'] = $fuhao*$goods_list[$k]['totalprice'];
	   $goods_list[$k]['c_price'] = $goods_list[$k]['c_price'];
	   $goods_list[$k]['c_totalprice'] = $fuhao*$goods_list[$k]['c_totalprice'];
	   
	 }	 
	 
	 return array('order_info'=>$order_info,'goods_list'=>$goods_list);
	 
	}
	
	private function send_out($filter)
	{
	  $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  $this->display();
	
	}
	
	private function order_fuhao($type)
	{
	  if(5 == $type)
		   {
		     $fuhao = 1;
		   }else
		   {
		     $fuhao = -1;
		   }
		   
		  return  $fuhao;
	}
	

}