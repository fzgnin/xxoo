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
class OrderController extends AdminController {

    /**
     * 商品管理
     * 黄
     */
    public function index(){

		//获取所有部门的列表
		$bumen_list = M('bumen')->select();
		$this->assign('bumen_list',$bumen_list);
		
		$this->display(); // 输出模板 
    }

	

    //新增出货单表

    public function add(){
	
		//生成订单编号固定格式
		$order_num = $this->produce_order_sn('XSCK',1);
		
		$this->assign('order_num',$order_num);
		
		$this->assign('time',date("Y-m-d H:i"));
		
	    $this->display(); // 输出模板
    }
	
	public function backorder()
	{
		//生成订单编号固定格式
		$order_num = $this->produce_order_sn('XSTH',2);
		
		$this->assign('order_num',$order_num);
		
		$this->assign('time',date("Y-m-d H:i"));
		
	    $this->display(); // 输出模板
	}
	
	
	public function dymadd()
	{
		//生成订单编号固定格式
		$order_num = $this->produce_order_sn('DYM',9);
		
		$this->assign('order_num',$order_num);

		$this->assign('time',date("Y-m-d H:i"));
		
	    $this->display(); // 输出模板
	}
	
	public function dymlist()
	{
		//获取客服填的销售单子
		$filter = $this->query_array();
		
		$filter['type'] = 'dym';
		
		$list = $this->guest_list($filter);
		
		$this->assign('filter',$filter);// 赋值分页输出
		
		$this->assign('a_amount',$list['a_amount']); 
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出 
		
		$this->display();
	}
	
	
	
	public function orderlist()
	{
		//获取客服填的销售单子
		$filter = $this->query_array();
		
		$list = $this->guest_list($filter);
		
		$this->assign('filter',$filter);// 赋值分页输出
		
		$this->assign('a_amount',$list['a_amount']); 
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出 
		
		$this->display();
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
		
		$this->assign('goods_list',$order['goods_list']);
		
		
		$this->display();
	}
	
	
	public function dymedit()
	{
		$id = I('id');
		
		if(!$id)
		{
		$this->error('该订单不存在，请确定传值正确性！');
		}
		
		$order = $this->get_order_dym($id);
		
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
	
	
	
	public function dym_order_info()
	{
		$id = I('id');
		
		$order = $this->get_order_dym($id);
		
		$this->assign('order_info',$order['order_info']);
		
		$this->assign('goods_list',$order['goods_list']);
		
		$this->display();
	}

	
	//插入商品到数据库
	public function insert()
	{
		//获取post数据
		
		$data = array(
			'add_time'=>strtotime(I('add_time')),
			'order_sn'=>I('order_sn'),
			'guest_id'=>I('guest_id'),
			'gname'=>I('gname'),
			'bumen_id'=>I('bumen_id'),
			'bname'=>I('bname'),
			'goods_id'=>I('goods_id'),
			'goodsname'=>I('goodsname'),
			'code'=>I('code'),
			'format'=>I('format'),
			'num'=>I('num'),
			'price'=>I('price'),
			'price_total'=>I('price_total'),
			'zhekou'=>I('zhekou'),
			'g_zhekou'=>I('g_zhekou'),
			'z_price'=>I('z_price'),
			'z_price_total'=>I('z_price_total'),
			'g_z_price'=>I('g_z_price'),
			'g_z_price_total'=>I('g_z_price_total'),
			'total_num'=>I('total_num'),
			'total_s_price'=>I('total_s_price'),
			'total_s_z_price'=>I('total_s_z_price'),
			'total_s_g_z_price'=>I('total_s_g_z_price'),
			'remarks'=>I('remarks'),
			'warehouse'=>I('warehouse'),
			'order_type'=>I('order_type',1),
			'id'=>I('id'),
			'order_goods_id'=>I('order_goods_id'),
			'insert_time'=>time(),
		);
		
	  	$not_null = 0;
		
		foreach($data['goods_id'] as $k => $v)
		{
			$show_f = $data['num'][$k] > 0 && $data['zhekou'][$k] >= 0 && $data['g_zhekou'][$k] >= 0 && $data['z_price'][$k] >= 0 && $data['g_z_price'][$k] >= 0;
			
			$show_s1 = !$data['num'][$k] && $data['zhekou'][$k] < 0 && $data['g_zhekou'][$k] < 0 && $data['z_price'][$k] < 0 && $data['g_z_price'][$k] < 0;
			
			$show_s2 = $data['num'][$k] <= 0 || $data['zhekou'][$k] < 0 || $data['g_zhekou'][$k] < 0 || $data['z_price'][$k] < 0 || $data['g_z_price'][$k] < 0;
			
			if($v && $show_f)
			{
				$not_null = 1;
			}
			elseif((!$v || $show_s2) && !(!$v && $show_s1))
			{
				$this->error('商品信息填写不完整！');
			}
		}
	  
		if(!$data['guest_id'] || !$data['bumen_id'] || $not_null == 0 || !$data['add_time'])
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
	  
	  
		//判断订单号是否重复
		
		if($data['id'])
		{
			//判断是否有分单
			if(M('bumen_feat')->where('order_id = '.$data['id'])->count() > 1)
			{
				$this->error('此订单已经分单，请删除分单后再编辑！');
			}
			
			if(1 == M('order')->where('id = '.$data['id'])->getField('status'))
			{
			  	$this->error('此单据已经过账，暂不允许更改！');
			}
			
		}else
		{
			if(M('order')->where("order_sn = '".$data['order_sn']."'")->find())
			{
				$this->error('订单号重复！');
			}
		}	  
		  
		//先写入订单表
		   
		$fuhao = $this->order_fuhao($data['order_type']);
		  
		//获取数据
		$order_data = array(
			'order_sn'=>$data['order_sn'],
			'guest_id'=>$data['guest_id'],
			'gname'=>$data['gname'],
			'bumen_id'=>$data['bumen_id'],
			'tid'=>M('bumen')->where('id = '.$data['bumen_id'])->getField('tid'),
			'bname'=>$data['bname'],
			'amount'=>$data['total_s_price']*$fuhao,
			'k_amount'=>$data['total_s_z_price']*$fuhao,
			'g_amount'=>$data['total_s_g_z_price']*$fuhao,
			'add_time'=>$data['add_time'],
			'insert_time'=>$data['insert_time'],
			'warehouse'=>$data['warehouse'],
			'remarks'=>$data['remarks'],
			'uid'=>UID,
			'order_type'=>$data['order_type'],
			'id'=>$data['id'],
		);
		
		M('order')->startTrans();//开启回滚
		  
		//使用replace into 做新增或更新操作
		$order_id = M('order')->add($order_data,array(),true);
		
		  
		//插入商品表循环商品id
		
		$goods_data = array();
		
		$b_money = 0;
		
		$c_money = 0;
		  
		foreach($data['goods_id'] as $k=>$v)
		{
			if($data['num'][$k] > 0 && $v)
			{
				$goods_info = M('goods')->where('id = '.$v)->find();
				
				$goods_data[] = array(
					'id'=>$data['order_goods_id'][$k],
					'order_id'=>$order_id,
					'goods_id'=>$v,
					'brand_id'=>$goods_info['brand_id'],
					'goodsname'=>$data['goodsname'][$k],
					'code'=>$data['code'][$k],
					'format'=>$data['format'][$k],
					'num'=>$data['num'][$k]*$fuhao,
					'k_zhekou'=>$data['zhekou'][$k],
					'g_zhekou'=>$data['g_zhekou'][$k],
					'price'=>$data['price'][$k],
					'totalprice'=>$data['price_total'][$k]*$fuhao,
					'k_price'=>$data['z_price'][$k],
					'k_totalprice'=>$data['z_price_total'][$k]*$fuhao,
					'g_price'=>$data['g_z_price'][$k],
					'g_totalprice'=>$data['g_z_price_total'][$k]*$fuhao,
					'bid'=>$data['bumen_id'],
					'guest_id'=>$data['guest_id'],
				);
				
				$b_money += $data['num'][$k]*$fuhao*$goods_info['cost_two'];
				
				$c_money += $data['num'][$k]*$fuhao*$goods_info['cost_one'];		
			}
		}

		  
		  
		//分单数据同步
		$feat_id = M('bumen_feat')->where('order_id = '.$order_id)->getField('id');
		
		$feat_log = array(
				 'order_id'=>$order_id,
				 'order_sn'=>$data['order_sn'],
				 'order_type'=>$data['order_type'],
				 'bid'=>$data['bumen_id'],
				 'bname'=>$data['bname'],
				 'gid'=>$data['guest_id'],
				 'gname'=>$data['gname'],
				 'feat'=>$data['total_s_g_z_price']*$fuhao,
				 'b_feat'=>$b_money,
				 'c_feat'=>$c_money,
				 'amount'=>$data['total_s_g_z_price']*$fuhao,
				 'b_amount'=>$b_money,
				 'c_amount'=>$c_money,
				 'add_time'=>$data['add_time'],
				 'post_time'=>$data['insert_time'],
				 'remarks'=>$data['remarks'],
				 'id'=>$feat_id,
				 );
				 
		//如果是编辑获取被删除掉的数据先
		$res_del = 1;
		$res_order = 1;
				
		if($data['id'])
		{
			//获取删掉的商品的id数组
			$id_array = M('order_goods')->where('order_id = '.$data['id'])->getField('id',true);
			
			$left_array = $data['order_goods_id']?array_diff($id_array,$data['order_goods_id']):$id_array;
			
			if(count($left_array) >0)
			{
				$str = implode(',',$left_array);
				
				$res_del = M('order_goods')->delete($str); 
			}
		}else
		{
			//设置订单号为已经使用
			$res_order = M('session_all_id')->where("user_id=".UID." and order_type = ".$data['order_type'])->setField('is_use',1);
		}
		
		$res_feat = M('bumen_feat')->add($feat_log,array(),true);
		
		$res_goods = M('order_goods')->addALL($goods_data,array(),true);
				
		if($res_goods && $res_order && $res_del && $order_id && $res_feat)
		{
			M('order')->commit();//成功则提交
			$this->success('操作成功！');
		}else
		{
			M('order')->rollback();//不成功，则回滚
			$this->error('操作失败！');
		}		

	}
	
	public function dyminsert()
	{
	  
		//获取接收数据
		
		$data = array(
			'add_time'=>strtotime(I('add_time')),
			'order_sn'=>I('order_sn'),
			'guest_id'=>I('guest_id'),
			'gname'=>I('gname'),
			'bumen_id'=>I('bumen_id'),
			'bname'=>I('bname'),
			'design'=>I('design'),
			'num'=>I('num'),
			'feat'=>I('feat'),
			'money'=>I('money'),
			'total_feat'=>I('total_feat'),
			'total_money'=>I('total_money'),
			'remarks'=>I('remarks'),
			'order_type'=>I('order_type'),
			'id'=>I('id'),
			'order_goods_id'=>I('order_goods_id'),
			'referee'=>I('referee'),
		);

		$not_null = 0;
		foreach($data['design'] as $v)
		{
			if($v)
			{
				$not_null = 1;
			}
		}
	  
		if(!$data['guest_id'] || !$data['bumen_id'] || $not_null == 0)
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
		
		if($data['id'])
		{
			//暂时设为过账后不允许编辑
			if(1 == M('dym_order')->where('id = '.$data['id'])->getField('status'))
			{
				$this->error('此单据已经过账，暂不允许更改！');
			}
		}
		else
		{
			if(M('dym_order')->where("order_sn = '".$data['order_sn']."'")->find())
			{
				$this->error('订单号重复！');
			}
		}
		  
		//先写入订单表
		
		//获取数据
		$order_data = array(
			'order_sn'=>$data['order_sn'],
			'guest_id'=>$data['guest_id'],
			'gname'=>$data['gname'],
			'bid'=>$data['bumen_id'],
			'bname'=>$data['bname'],
			'tid'=>M('bumen')->where('id = '.$data['bumen_id'])->getField('tid'),
			'amount'=>$data['total_feat'],
			'g_amount'=>$data['total_money'],
			'add_time'=>$data['add_time'],
			'remarks'=>$data['remarks'],
			'uid'=>UID,
			'order_type'=>$data['order_type'],
			'id'=>$data['id'],
			'status'=>0,
			'referee'=>$data['referee'],
		);
		
		
		M('dym_order')->startTrans();//开启回滚
		  
		//使用replace into 做新增或更新操作
		$order_id = M('dym_order')->add($order_data,array(),true); 
		  
		//插入商品表循环商品id
		$goods_data = array();
		
		foreach($data['design'] as $k=>$v)
		{
			if($data['num'][$k] > 0 && $v)
			{
				$goods_data[] = array(
				'id'=>$data['order_goods_id'][$k],
				'order_id'=>$order_id,
				'design'=>$v,
				'num'=>$data['num'][$k],
				'feat'=>$data['feat'][$k],
				'money'=>$data['money'][$k],
				'bid'=>$data['bumen_id'],
				'gid'=>$data['guest_id'],
				);		
			}
		}
		
		//如果是编辑获取被删除掉的数据先
		$res_del = 1;
		$res_order = 1;
				
		if($data['id'])
		{
			//获取删掉的商品的id数组
			$id_array = M('dym_goods')->where('order_id = '.$data['id'])->getField('id',true);
			
			$left_array = $data['order_goods_id']?array_diff($id_array,$data['order_goods_id']):$id_array;
			
			if(count($left_array) >0)
			{
				$str = implode(',',$left_array);
				
				$res_del = M('dym_goods')->delete($str); 
			}
		}else
		{
			//设置订单号为已经使用
			$res_order = M('session_all_id')->where("user_id=".UID." and order_type = ".$data['order_type'])->setField('is_use',1);
		}
		

		$res_goods = M('dym_goods')->addALL($goods_data,array(),true);
				
		if($res_goods && $res_order && $res_del && $order_id)
		{
			M('dym_order')->commit();//成功则提交
			$this->success('操作成功！');
		}else
		{
			M('dym_order')->rollback();//不成功，则回滚
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
	  
	  //判断订单状态
	  $order_info = M('order')->where('id = '.$id)->find();
	  if(!$order_info || 1 == $order_info['status'])
	  {
	    $this->error('此订单不存在或已经过账！');
	  }
	  
	  if(M('order')->where('id = '.$id)->delete() && M('order_goods')->where('order_id = '.$id)->delete() && M('bumen_feat')->where('order_id = '.$id)->delete())
	  {
	   $this->success('删除成功！');
	  }else
	  {
	   $this->error('删除失败！');
	  }
	}
	
	//删除操作
	public function dym_delete()
	{
	  $id = I('id');
	  
	  if(!$id)
	  {
	    $this->error('传值错误！');
	  }
	  
	  if(M('dym_order')->where('id = '.$id)->delete() && M('dym_goods')->where('order_id = '.$id)->delete())
	  {
	   $this->success('删除成功！');
	  }else
	  {
	   $this->error('删除失败！');
	  }
	}
	
	
	public function splitorder()
	{
	    $id = I('id');
		
		//取出订单的信息
		 $order_info = M('order')->where('id = '.$id)->find();
		 
		 //取出商品信息
		 $goods_list = M('order_goods')->where('order_id = '.$id)->select();
		 
		 //从分单表取出该订单的分单数据
		 $feat_list = M('bumen_feat')->where('order_id = '.$id)->select();
		 
		 $this->assign('order_info',$order_info);
		 
		 $this->assign('goods_list',$goods_list);
		 
		 $this->assign('feat_list',$feat_list);
	 
	     $this->display('order');
	
	}
	
	public function splitinsert()
	{
	   $bid = I('bid');
	   
	   $money = I('money');
	   
	   $id = I('id');
	   
	   $feat_id = I('feat_id');
	   
	   $time = time();
	   
	   //取出订单信息
	   $order_info = M('order')->where('id = '.$id)->find();
	   
	   $order_info['gname'] = M('guest')->where('id = '.$order_info['guest_id'])->getField('guestname');
	   
	   $order_goods = M('order_goods')->where('order_id = '.$id)->select();
	   
	   $b_money = 0;
	   
	   $c_money = 0;
	   
	   //根据订单的商品计算出订单的部门成本价和公司成本价
	   foreach($order_goods as $k => $v)
	   {
	         //首先取得现有仓库的商品信息
			 
			 $goods_info = M('goods')->where('id = '.$v['goods_id'])->find();
			 
			 //计算商品的部门成本和原始成本
			 
			 $b_money += $v['num']*$goods_info['cost_two'];
			 
			 $c_money += $v['num']*$goods_info['cost_one'];
	    
	   }
	   
	   //判断传入值是否正确
	   
	   
	   //分单操作
		  //获取传入的分单数组形成分单数据数组
		  foreach($bid as $ko => $vo)
		  {
		     if($vo)
			 {
				 $bumen_info = M('bumen')->where('id = '.$vo)->find();
				 
				 if($money[$ko])
				 {
				 
					 $feat_log[] = array(
					 'order_id'=>$order_info['id'],
					 'order_sn'=>$order_info['order_sn'],
					 'order_type'=>$order_info['order_type'],
					 'bid'=>$vo,
					 'bname'=>$bumen_info['bname'],
					 'gid'=>$order_info['guest_id'],
					 'gname'=>$order_info['gname'],
					 'feat'=>$money[$ko],
					 'b_feat'=>round($money[$ko]*$b_money/$order_info['g_amount'],2),
					 'c_feat'=>round($money[$ko]*$c_money/$order_info['g_amount'],2),
					 'amount'=>$order_info['g_amount'],
					 'b_amount'=>$b_money,
					 'c_amount'=>$c_money,
					 'add_time'=>$order_info['add_time'],
					 'post_time'=>$time,
					 'remarks'=>$order_info['remarks'],
					 'id'=>$feat_id[$ko],
					 );
				 
				 }
			 
			 }else
			 {
			   $this->error('部门不能为空！');
			 }  
		  
		  }
		  //print_r($feat_log);exit;
		  //去重复
		  $id_array = M('bumen_feat')->where('order_id = '.$id)->getField('id',true);
		  
		  $left_array = array();
		  
		  if($feat_id)
			{
			  $left_array = array_diff($id_array,$feat_id);
			}else
			{
			  $left_array = $id_array;
			}
		  //print_r($feat_log);exit;
		  
		  if(M('bumen_feat')->addALL($feat_log,array(),true))
		  {
		    //批量删除
				if(count($left_array) >0)
				{
				  $str = implode(',',$left_array);
				  
				  M('bumen_feat')->delete($str); 
				  
				}
			
			$this->success('分单成功！');
		  }else
		  {
		    $this->error('分单失败！');
		  }
	}

	public function ajax_query()
	{
	 
	 //条件数组
	 
     $list = $this->guest_list();
	
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出
	 
	 $this->assign('a_amount',$list['a_amount']); 

	 $res_str = $this->fetch($list['model_t']); // 输出模板
	 
	 $data['info'] = $res_str;
	 
	 $data['success'] = 1;
	 
	 $this->ajaxReturn($data);
	
	}

	
	//获取顾客列表封装函数
	
	public function guest_list($filter = array())
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
	  if('dym' == $filter['type'])
	  {
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
		    $where .= ' and g_amount like "%'.$filter['amount'].'%"';
		}
		  
		if($filter['remarks'])
		{
			$where .= ' and remarks like "%'.$filter['remarks'].'%"';
		}
		
		
		$list = M('dym_order')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		$a_amount = M('dym_order')->where($where)->sum('g_amount');
		
		
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		  
		   $ff = M('dym_goods')->alias('g')->join('`onethink_dym_order` as o on g.order_id = o.id')->where('g.order_id = '.$v['id'])->sum('money');
		   
		   if($ff != $v['g_amount'])
		   {
		     $list[$k]['error'] = 1;
		   }else
		   {
		     $list[$k]['error'] = 0;
		   }
		   
		   
		 }
		}
		
		//分页
		$count = M('dym_order')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Order:dym_list';
	  
	  
	  
	  }
	  
	  else
	  {
		
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
		  
		//取出用户的管辖部门
		$bid_array = M('uid_bid')->where('uid = '.UID)->getField('bid',true);
		$bid_array = implode(',',$bid_array);
		$where .= ' and bumen_id in ('.$bid_array.')';
		  
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
		    $where .= ' and g_amount like "%'.$filter['amount'].'%"';
		}
		  
		if($filter['remarks'])
		{
			$where .= ' and remarks like "%'.$filter['remarks'].'%"';
		}
		
		
		$list = M('order')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		$a_amount = M('order')->where($where)->sum('g_amount');
		
		
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   
		   //判断订单是否异常
		   $ff = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where('g.order_id = '.$v['id'])->sum('g_totalprice');
		   
		   if($ff != $v['g_amount'])
		   {
		     $list[$k]['error'] = 1;
		   }else
		   {
		     $list[$k]['error'] = 0;
		   }
		   
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
		   
		   $list[$k]['amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['amount'];
		   $list[$k]['k_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['k_amount'];
		   $list[$k]['g_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['g_amount'];
		   
		   
		 }
		}
		
		//分页
		$count = M('order')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:order_list';
	  
	  }

	  
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
	   'guest_id' => I('guest_id'),
	   'bumen_id' => I('bumen_id'),
	   'name' => I('name'),
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
	 $order_info = M('order')->where(array('id'=>$id))->find();
	 
	 $fuhao = $this->order_fuhao($order_info['order_type']);
		   
	 $order_info['amount'] = $fuhao*$order_info['amount'];
	 $order_info['k_amount'] = $fuhao*$order_info['k_amount'];
	 $order_info['g_amount'] = $fuhao*$order_info['g_amount'];
	 
	 //获取订单商品信息
	 $goods_list = M('order_goods')->where('order_id = '.$id)->select();
	 
	 foreach($goods_list as $k=>$v)
	 {
	   $goods_list[$k]['price'] = $fuhao*$goods_list[$k]['price'];
	   $goods_list[$k]['num'] = $fuhao*$goods_list[$k]['num'];
	   $goods_list[$k]['totalprice'] = $fuhao*$goods_list[$k]['totalprice'];
	   $goods_list[$k]['k_price'] = $fuhao*$goods_list[$k]['k_price'];
	   $goods_list[$k]['k_totalprice'] = $fuhao*$goods_list[$k]['k_totalprice'];
	   $goods_list[$k]['g_price'] = $fuhao*$goods_list[$k]['g_price'];
	   $goods_list[$k]['g_totalprice'] = $fuhao*$goods_list[$k]['g_totalprice'];
	 }	 
	 
	 return array('order_info'=>$order_info,'goods_list'=>$goods_list);
	 
	}
	
	
	
	private function get_order_dym($id)
	{
	 //获取订单的详细信息
	 $order_info = M('dym_order')->where(array('id'=>$id))->find();

	 $order_info['guestname'] = M('guest')->where(array('id'=>$order_info['guest_id']))->getField('guestname'); 
	 $order_info['bname'] = M('bumen')->where(array('bid'=>$order_info['bid']))->getField('bname');
	 
	 //获取订单商品信息
	 $goods_list = M('dym_goods')->where('order_id = '.$id)->select();
	 
	 return array('order_info'=>$order_info,'goods_list'=>$goods_list);
	 
	}
	
	
	
	
	private function order_fuhao($type)
	{
	  if(1 == $type)
		   {
		     $fuhao = 1;
		   }else
		   {
		     $fuhao = -1;
		   }
		   
		  return  $fuhao;
	}
	
	private function produce_order_sn($name,$id)
	{
		$time = date("Y-m-d");
		
		//对num进行处理不足5位自动补零
		$num=sprintf("%05d", $this->get_order_id($time,$id));

		$order_num = $name.$time.'-'.$num;
		
		return $order_num;
	}
	

}