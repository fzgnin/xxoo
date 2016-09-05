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
 * huang
 */
class StockController extends AdminController {

    /**
     * 商品管理
     * 黄
     */
    public function index(){

	 $this->display(); // 输出模板
	 
    }


    //新增出货单表

    public function add(){
	
	//生成订单编号固定格式

	$time = date("Y-m-d");
	
	$num = $this->get_order_id($time,3);
	
	//对num进行处理不足5位自动补零
	
	$num=sprintf("%05d", $num);

	$order_num = 'CJRK'.$time.'-'.$num;
	
	$this->assign('order_num',$order_num);
	
	$time_1 = date("Y-m-d H:i");
	
	$this->assign('time',$time_1);
	
	$this->assign('order_type',3);
	
	$this->assign('username',$_SESSION['onethink_admin']['user_auth']['username']);
	
    $this->display(); // 输出模板
    }
	
	public function backorder()
	{
	$time = date("Y-m-d");
	
	$num = $this->get_order_id($time,4);
	
	//对num进行处理不足5位自动补零
	
	$num=sprintf("%05d", $num);

	$order_num = 'CJCK'.$time.'-'.$num;
	
	$this->assign('order_num',$order_num);
	
	$time_1 = date("Y-m-d H:i");
	
	$this->assign('time',$time_1);
	
	$this->assign('order_type',4);
	
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
		//获取post数据写到数组中去
		$data = array(
			'add_time'=>strtotime(I('add_time')),
			'order_sn'=>I('order_sn'),
			'supplier_id'=>I('supplier_id'),
			'suppliername'=>I('suppliername'),
			'user_id'=>I('user_id'),
			'goods_id'=>I('goods_id'),
			'goodsname'=>I('goodsname'),
			'code'=>I('code'),
			'format'=>I('format'),
			'num'=>I('num'),
			'price'=>I('price'),
			'price_total'=>I('price_total'),
			'total_num'=>I('total_num'),
			'total_s_price'=>I('total_s_price'),
			'remarks'=>I('remarks'),
			'warehouse'=>I('warehouse'),
			'order_type'=>I('order_type',3),
			'id'=>I('id'),
			'order_goods_id'=>I('order_goods_id'),
		);
		
		//判断信息是否完整
		
		
	  
		$not_null = 0;
		foreach($data['goods_id'] as $k => $v)
		{
			if($v && $data['num'][$k] > 0 && $data['price'][$k])
			{
				$not_null = 1;
			}
			elseif((!$v || $data['num'][$k] <= 0 || $data['price'][$k] <=0) && !(!$v && !$data['num'][$k] && !$data['price'][$k]))
			{
				$this->error('请填写完整信息！');
			}
		}
	  
		if(!$data['supplier_id'] || $not_null == 0 || !$data['warehouse'])
		{
			$this->error('请填写完整信息！');
		}
	  
		//判断当前单据日期是否已经结账
		//取最后的结账时间
		$end_date = M('checkout')->order('id desc')->limit(1)->getField('end_date');
		
		//如果有结账时间且订单的开单时间小于结账时间返回错误
		if($end_date && $data['add_time'] < strtotime($end_date)+86400)
		{
			$this->error('该日期范围内已经结账不能再下单！');
		}
	  
		//暂时设为过账后不允许编辑
		//判断订单号是否重复 
		if($data['id'])
		{
			if(1 == M('stock_order')->where('id = '.$data['id'])->getField('status'))
			{
			  	$this->error('此单据已经过账，暂不允许更改！');
			}
		}elseif(M('stock_order')->where("order_sn = '".$data['order_sn']."'")->find())
		{
			$this->error('订单号重复！');
		}
 
		//先写入订单表
		
		$fuhao = $this->order_fuhao($data['order_type']);
		   
		//计算该订单的进货价与进货总金额
		$amount = 0;
		$j_amount = 0;
		$goods_data = array();
		   
		foreach($data['goods_id'] as $k=>$v)
		{
			if($data['num'][$k] > 0 && $v)
			{
				$goods_info = M('goods')->where('id = '.$v)->find();
				$data['num'][$k] = $data['num'][$k]*$fuhao;
				
				$amount += $data['num'][$k]*$goods_info['price'];
				$j_amount += $data['num'][$k]*$goods_info['cost_two'];
			
				$goods_data[] = array(
					'id'=>$data['order_goods_id'][$k],
					'order_id'=>'',
					'goods_id'=>$v,
					'brand_id'=>$goods_info['brand_id'],
					'goodsname'=>$data['goodsname'][$k],
					'code'=>$data['code'][$k],
					'format'=>$data['format'][$k],
					'num'=>$data['num'][$k],
					'price'=>$goods_info['price'],
					'totalprice'=>$goods_info['price']*$data['num'][$k],
					'c_price'=>$data['price'][$k],
					'c_totalprice'=>$data['price_total'][$k]*$fuhao,
					'j_price'=>$goods_info['cost_two'],
					'j_totalprice'=>$data['num'][$k]*$goods_info['cost_two'],
					'supplier_id'=>$data['supplier_id'],
				);
			}
		}
		   
		  
		//获取数据
		$order_data = array(
			'order_sn'=>$data['order_sn'],
			'supplier_id'=>$data['supplier_id'],
			'suppliername'=>$data['suppliername'],
			'amount'=>$amount,
			'c_amount'=>$data['total_s_price']*$fuhao,
			'j_amount'=>$j_amount,
			'add_time'=>$data['add_time'],
			'insert_time'=>time(),
			'warehouse'=>$data['warehouse'],
			'remarks'=>$data['remarks'],
			'uid'=>UID,
			'order_type'=>$data['order_type'],
			'id'=>$data['id'],
		);
		  
		//使用replace into 做新增或更新操作
		//开启回滚
		M('stock_order')->startTrans();
		 
		$order_id = M('stock_order')->add($order_data,array(),true);
		  
		  
		//插入商品表循环商品id
		foreach($goods_data as $ko=>$vo)
		{
			$goods_data[$ko]['order_id'] = $order_id;
		}


		
		//如果是编辑获取被删除掉的数据先
		$res_del = 1;
		$res_order = 1;
		
		if($data['id'])
		{
			//获取删掉的商品的id数组
			$id_array = M('stock_goods')->where('order_id = '.$data['id'])->getField('id',true);
			
			$left_array = $data['order_goods_id']?array_diff($id_array,$data['order_goods_id']):$id_array;
			
			if(count($left_array) >0)
			{
				$str = implode(',',$left_array);
				
				$res_del = M('stock_goods')->delete($str); 
			}
		}else
		{
			//设置订单号为已经使用
			$res_order = M('session_all_id')->where("user_id=".UID." and order_type = ".$data['order_type'])->setField('is_use',1);
		}
		
		//统一批量插入数据
		$res_list = M('stock_goods')->addALL($goods_data,array(),true);
		
		if($res_list && $res_order && $res_del && $order_id)
		{
			M('stock_order')->commit();//成功则提交
			$this->success('操作成功！');
		}else
		{
			M('stock_order')->rollback();//不成功，则回滚
			$this->error('操作失败！');
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
	  if(1 == M('stock_order')->where('id = '.$id)->getField('status'))
	  {
	    $this->error('此订单已过账，请联系财务进行删除！');
	  }
	  
	  if(M('stock_order')->where('id = '.$id)->delete() && M('stock_goods')->where('order_id = '.$id)->delete())
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
		$where = 'add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
		break;
		case 2:
		$where = 'post_time>='.$filter['begin_time'].' and post_time<'.$filter['end_time'];
		break;
		case 3:
		$where = 'insert_time>='.$filter['begin_time'].' and insert_time<'.$filter['end_time'];
		break;
		default:
		$where = 'add_time>='.$filter['begin_time'].' and add_time<'.$filter['end_time'];
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
	  
	  if($filter['suppliername'])
	  {
	    $where .= ' and suppliername like "%'.$filter['suppliername'].'%"';
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

				
		$list = M('stock_order')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		$a_amount = M('stock_order')->where($where)->sum('j_amount');
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['suppliername'] = M('supplier')->where(array('id'=>$v['supplier_id']))->getField('suppliername'); 
		   $list[$k]['amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['amount'];
		   $list[$k]['username'] = M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
		 }
		}
		
		//分页
		$count = M('stock_order')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Stock:order_list';

	  
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
	 $order_info = M('stock_order')->where(array('id'=>$id))->find();

	 $order_info['username'] = M('member')->where(array('uid'=>$order_info['uid']))->getField('nickname');
	 
	 $fuhao = $this->order_fuhao($order_info['order_type']);
		   
	 $order_info['amount'] = $fuhao*$order_info['amount'];
	 $order_info['c_amount'] = $fuhao*$order_info['c_amount'];
	 $order_info['j_amount'] = $fuhao*$order_info['j_amount'];
	 
	 //获取订单商品信息
	 $goods_list = M('stock_goods')->where('order_id = '.$id)->select();
	 
	 foreach($goods_list as $k=>$v)
	 {
	   $goods_list[$k]['price'] = $fuhao*$goods_list[$k]['price'];
	   $goods_list[$k]['num'] = $fuhao*$goods_list[$k]['num'];
	   $goods_list[$k]['totalprice'] = $fuhao*$goods_list[$k]['totalprice'];
	   $goods_list[$k]['c_price'] = $fuhao*$goods_list[$k]['c_price'];
	   $goods_list[$k]['c_totalprice'] = $fuhao*$goods_list[$k]['c_totalprice'];
	   $goods_list[$k]['j_price'] = $fuhao*$goods_list[$k]['j_price'];
	   $goods_list[$k]['j_totalprice'] = $fuhao*$goods_list[$k]['j_totalprice'];
	 }	 
	 
	 return array('order_info'=>$order_info,'goods_list'=>$goods_list);
	 
	}
	
	private function send_out($filter)
	{
	  $list = $this->get_list($filter);
	 
	  $this->assign('filter',$filter);// 赋值分页输出
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  $this->display();
	
	}
	
	private function order_fuhao($type)
	{
	  if(3 == $type)
		   {
		     $fuhao = 1;
		   }else
		   {
		     $fuhao = -1;
		   }
		   
		  return  $fuhao;
	}
	

}