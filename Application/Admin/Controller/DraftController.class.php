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
class DraftController extends AdminController {

    /**
     * 商品管理
     * 黄
     */
    public function index(){

	 $this->display(); // 输出模板
	 
    }

	
	public function orderlist()
	{
	  //获取客服填的销售单子
	  $filter = $this->query_array();
	  
	  $this->send_out($filter);
	}

	
	public function review()
	{
	  $id = I('id');
	  
	  if(!$id)
	  {
	   $this->error('该订单不存在，请确定传值正确性！');
	  }
	  
	  //获取该订单下单品牌数
	  $oblist = M('draft_goods')->alias('d')->field('b.id,b.name,sum(if(d.status<=0,"1",0)) as n_s ,count(d.id) as a_s')
	  			->join('left join `onethink_brand` b on d.brand_id = b.id')->where('order_id = '.$id)->group('brand_id')->select();
	  
	  foreach($oblist as $k=>$v)
	  { 	
			$oblist[$k]['is_f'] = (0 == $v['n_s'])?1:0;
	  }
	  //print_r($oblist);
	  $this->assign('oblist',$oblist);

	  $this->assign('id',$id);
	
	  $this->display();
	}
	
	//反审核
	public function no_review()
	{
	  $id = I('id');
	  
	  if(!$id)
	  {
	   $this->error('该订单不存在，请确定传值正确性！');
	  }
	  
	  $result = array('status'=>1,'info'=>'');
	  
	  //要做工作，删除关联订单，设置状态
	  $order_id_list = M('draft_goods')->where('order_id = '.$id)->getField('gl_id',true);
	  
	  $order_id_list = array_unique($order_id_list);
	  
	  $where['id'] = array('in',$order_id_list);
	  $whereg['order_id'] = array('in',$order_id_list);
	  $data['status'] = 0;
	  $data['gl_id'] = 0;
	  
	  M('order')->startTrans();
 
	  $res_order = M('order')->where($where)->find()?M('order')->where($where)->delete():1; 
	  $res_ordergoods = M('order_goods')->where($whereg)->select()?M('order_goods')->where($whereg)->delete():1;
	  
	  //设置状态
	  $res_dtaft = M('draft_order')->where('id = '.$id)->setField('status',0);
	  $res_draftgoods = M('draft_goods')->where('order_id = '.$id)->save($data);
	  //var_dump($res_order);
	  //var_dump($res_ordergoods);
	  //var_dump($res_dtaft);
	  //var_dump($res_draftgoods);
	  if($res_order && $res_ordergoods && $res_dtaft && $res_draftgoods)
	  {
	  	M('warehouse_log')->commit();//成功则提交
		$result['info'] = '反审核成功！';
		$this->ajaxReturn($result);
	  }
	  else{
	  	M('warehouse_log')->rollback();//不成功，则回滚
		$result['info'] = '反审核失败！';
		$result['status'] = 0;
		$this->ajaxReturn($result);
	  }
	   
	}
	
	
	
	
	public function get_orderbrand()
	{
		$brand_id = I('brand_id');
		
		$id = I('id');
		
		//判断是否通过审核
		$draft_goods_array = M('draft_goods')->where('order_id = '.$id.' and brand_id = '.$brand_id)->select();
		
		$is_nf = 0;
		$gl_idarray = array();
		
		foreach($draft_goods_array as $k=>$v)
		{
			if(0 == $v['status'])
			{
				$is_nf = 1;
			}
			
			$gl_idarray[] = $v['gl_id'];
		}
		
		$gl_idarray = array_unique($gl_idarray);
		
		if($is_nf)
		{
			//根据订单id和品牌id形成订单
			
			//订单信息
			$order_info = M('draft_order')->where('id = '.$id)->find();
			
			//根据订单类型生成订单编号固定格式
	
			$time = date("Y-m-d");
			
			$num = $this->get_order_id($time,$order_info['order_type']);
			
			//对num进行处理不足5位自动补零
			
			$num=sprintf("%05d", $num);
			
			if(1 == $order_info['order_type'])
			{
				$order_num = 'XSCK'.$time.'-'.$num;
			}else
			{
				$order_num = 'XSTH'.$time.'-'.$num;
			}

			$order_info['order_sn'] = $order_num;
			
			$time_1 = date("Y-m-d H:i");
			
			$this->assign('time',$time_1);
			
			
			$order_goods = M('draft_goods')->where('order_id = '.$id.' and brand_id = '.$brand_id.' and status = 0')->select();
			
			//取出该客户该品牌的折扣
			$zhekou = M('branddiscount')->where('guest_id = '.$order_info['gid'].' and brand_id = '.$brand_id)->find();
			
			$fuhao = $this->order_fuhao($order_info['order_type']);
			
			foreach($order_goods as $k => $v)
			{
				
				
				$goods_info = M('goods')->where('id = '.$v['goods_id'])->find();
				$order_goods[$k]['code'] = 	$goods_info['code'];
				$order_goods[$k]['goodsname'] = $goods_info['goodsname'];
				$order_goods[$k]['format'] = $goods_info['format'];
				$order_goods[$k]['k_zhekou'] = $zhekou['discount_k']?$zhekou['discount_k']:1;
				$order_goods[$k]['g_zhekou'] = $zhekou['discount_g']?$zhekou['discount_g']:1;
				
				$order_goods[$k]['num'] = 	$v['num']*$fuhao;
				$order_goods[$k]['price'] = 	$v['price']*$fuhao;
				$order_goods[$k]['totalprice'] = 	$v['totalprice']*$fuhao;
			}
			
			
		}
		else
		{
			//此处暂设为一个品牌只允许为一个订单
			$order_id = $gl_idarray[0];
			
			$order_info = M('order')->where('id = '.$order_id)->find();
			
			$order_goods = M('order_goods')->where('order_id = '.$order_id)->select();
		}
		
		$this->assign('order_info',$order_info);
			
		$this->assign('order_goods',$order_goods);
		
		$this->assign('draft_id',$id);
		
		$this->assign('brand_id',$brand_id);
		
		$this->assign('is_nf',$is_nf);
		
		$res_str = $this->fetch('Ajax:Draft:draft_order'); // 输出模板
		
		$data['info'] = $res_str;
	 
		$data['success'] = 1;
	 
		$this->ajaxReturn($data);
		
		
	
	}

	
	
	
	
	
	public function order_info()
	{
	 $id = I('id');
	 
	 //取出订单
	 $draft_info = M('draft_order')->where('id = '.$id)->find();
	 
	 $this->assign('draft_info',$draft_info);
	 
	 $draft_goods = M('draft_goods')->alias('dg')->field('g.code,g.goodsname,g.format,dg.num,dg.price,dg.totalprice,dg.status,dg.gl_id')
	 				->join('left join `onethink_goods` g on dg.goods_id = g.id')->where('order_id = '.$id)->select();
	 // print_r($draft_goods);
	 $draft_newgoods = array_unique(array_column($draft_goods,'gl_id'));
	
	 foreach($draft_newgoods as $v)
	 {
	 	if($v)
		{
			$order_info = M('order')->where('id = '.$v)->find();
			$order_goods = M('order_goods')->where('order_id = '.$v)->select();
			$order_array[] = array('order_info'=>$order_info,'order_goods'=>$order_goods);
		}
	 }
	 //print_r($order_array);
	 $this->assign('draft_goods',$draft_goods);
	 
	 $this->assign('order_array',$order_array);
	 
	 //查询该订单是否有生成单据
	 
	 
	 $this->display();
	}
	
	
	
	//插入商品到数据库
	public function insert()
	{
	  //获取post数据
	  
	  $add_time = strtotime(I('add_time'));
	  $order_sn = I('order_sn');
	  $guest_id = I('guest_id');
	  $bumen_id = I('bumen_id');
	  $goods_id = I('goods_id');
	  $goodsname = I('goodsname');
	  $code = I('code');
	  $format = I('format');
	  $num = I('num');
	  $price = I('price');
	  $price_total = I('price_total');
	  $zhekou = I('zhekou');
	  $g_zhekou = I('g_zhekou');
	  $z_price = I('z_price');
	  $z_price_total = I('z_price_total');
	  $g_z_price = I('g_z_price');
	  $g_z_price_total = I('g_z_price_total');
	  $total_num = I('total_num');
	  $total_s_price = I('total_s_price');
	  $total_s_z_price = I('total_s_z_price');
	  $total_s_g_z_price = I('total_s_g_z_price');
	  $remarks = I('remarks');
	  $warehouse = I('warehouse');
	  $order_type = I('order_type',1);
	  $id = I('id');
	  $order_goods_id = I('order_goods_id');
	  $time = time();
	  $draft_id = I('draft_id');
	  $brand_id = I('brand_id');
	  
	  $not_null = 0;
	  foreach($goods_id as $k => $v)
	  {
	   //商品存在并且数量大于零至少出现一条
	   if($v && $num[$k] > 0)
	   {
	    $not_null = 1;
	   }
	  }
	  
	  if(!$guest_id || !$bumen_id || $not_null == 0 || !$warehouse || !$add_time)
	  {
	   $this->error('请填写完整信息！');
	  }
	  
	  
	  
	  //判断商品总价是否和订单总价一致
	  $a_top = 0;
	  $k_top = 0;
	  $g_top = 0;
	  foreach($g_z_price_total as $k => $v)
	  {
	    $a_top = $a_top + $price_total[$k];
		$k_top = $k_top + $z_price_total[$k];
		$g_top = $g_top + $v;
	  }
	  
	  
	  //判断订单号是否重复 
	  
		  if(!$id && M('order')->where("order_sn = '$order_sn'")->select())
		  {
		   $this->error('订单号重复！');
		  }
		  
		  //判断是否有分单
		  if($id && M('bumen_feat')->where('order_id = '.$id)->count() > 1)
		  {
			$this->error('此订单已经分单，请删除分单后再编辑！');
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
			if(1 == M('order')->where('id = '.$id)->getField('status'))
			{
			  $this->error('此单据已经过账，暂不允许更改！');
			}
		  }
		  
		  
		   //先写入订单表
		   
		   $fuhao = $this->order_fuhao($order_type);
		   $bumen = M('bumen')->where('id = '.$bumen_id)->find();
		  
		  //获取数据
		  $order_data = array(
		   'order_sn'=>$order_sn,
		   'guest_id'=>$guest_id,
		   'gname'=>I('gname'),
		   'tid'=>$bumen['tid'],
		   'bumen_id'=>$bumen_id,
		   'bname'=>I('bname'),
		   'amount'=>$a_top*$fuhao,
		   'k_amount'=>$k_top*$fuhao,
		   'g_amount'=>$g_top*$fuhao,
		   'add_time'=>$add_time,
		   'insert_time'=>$time,
		   'warehouse'=>$warehouse,
		   'remarks'=>$remarks,
		   'uid'=>UID,
		   'order_type'=>$order_type,
		   'id'=>$id,
		   'insert_type'=>1,
		  );
		  
		  //使用replace into 做新增或更新操作
		  
		  $order_id = M('order')->add($order_data,array(),true);
		  
		  if(!$order_id)
		  {
		   $this->error('插入或更新订单失败！');
		  }
		  
		 //插入商品表循环商品id
		  
		  $goods_data = array();
		  
		  $b_money = 0;
	   
	      $c_money = 0;
		  
		  foreach($goods_id as $k=>$v)
		  {
			if($num[$k] > 0 && $v)
			{
			  $goods_info = M('goods')->where('id = '.$v)->find();
			  
			  $goods_data[] = array(
			  'id'=>$order_goods_id[$k],
			  'order_id'=>$order_id,
			  'goods_id'=>$v,
			  'brand_id'=>$goods_info['brand_id'],
			  'goodsname'=>$goodsname[$k],
			  'code'=>$code[$k],
			  'format'=>$format[$k],
			  'num'=>$num[$k]*$fuhao,
			  'k_zhekou'=>$zhekou[$k],
			  'g_zhekou'=>$g_zhekou[$k],
			  'price'=>$price[$k]*$fuhao,
			  'totalprice'=>$price_total[$k]*$fuhao,
			  'k_price'=>$z_price[$k]*$fuhao,
			  'k_totalprice'=>$z_price_total[$k]*$fuhao,
			  'g_price'=>$g_z_price[$k]*$fuhao,
			  'g_totalprice'=>$g_z_price_total[$k]*$fuhao,
			  'bid'=>$bumen_id,
			  'guest_id'=>$guest_id,
			  );
			  
			  $b_money += $num[$k]*$fuhao*$goods_info['cost_two'];
			 
			  $c_money += $num[$k]*$fuhao*$goods_info['cost_one'];		
			}
		  }

		  if(count($goods_data)>0)
		  {
		  
		    //分单数据同步
			$feat_id = M('bumen_feat')->where('order_id = '.$order_id)->getField('id');
		  
		    $feat_log = array(
					 'order_id'=>$order_id,
					 'order_sn'=>$order_sn,
					 'order_type'=>$order_type,
					 'bid'=>$bumen_id,
					 'bname'=>$bumen['bname'],
					 'gid'=>$guest_id,
					 'gname'=>M('guest')->where('id = '.$guest_id)->getField('guestname'),
					 'tid'=>$bumen['tid'],
					 'feat'=>$g_top*$fuhao,
					 'b_feat'=>$b_money,
					 'c_feat'=>$c_money,
					 'amount'=>$g_top*$fuhao,
					 'b_amount'=>$b_money,
					 'c_amount'=>$c_money,
					 'add_time'=>$add_time,
					 'post_time'=>$time,
					 'remarks'=>$remarks,
					 'id'=>$feat_id,
					 );
					 
			
			//获取删掉的商品的id数组
			$id_array = M('order_goods')->where('order_id = '.$id)->getField('id',true);
					
		    if(M('order_goods')->addALL($goods_data,array(),true))
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
				  
				  M('order_goods')->delete($str); 
				  
				}
				//写入分单业绩
				M('bumen_feat')->add($feat_log,array(),true);
				
				$this->success('编辑成功！');die();
						 
			  }else
			  {
			   //设置订单号为已使用
			   M('session_all_id')->where("user_id=".UID." and order_type = ".$order_type)->setField('is_use',1);
			   
			   //写入分单业绩
			   M('bumen_feat')->add($feat_log,array(),true);
			   
			   //更新订单表状态
			   $draft_data['status'] = 1;
			   $draft_data['gl_id'] = $order_id;
			   M('draft_goods')->where('order_id = '.$draft_id.' and brand_id = '.$brand_id)->save($draft_data);
			   
			   //判断该订单是否全部审核是设置审核完成
			   $is_f = M('draft_goods')->where('order_id = '.$draft_id.' and status = 0')->find();
			   if(!$is_f)
			   {
			   		M('draft_order')->where('id = '.$draft_id)->save(array('status'=>1,'post_time'=>time()));//已审核
			   }else
			   {
			   		M('draft_order')->where('id = '.$draft_id)->save(array('status'=>2,'post_time'=>time()));//部分审核
			   }
			   
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
		$where = 'ub.uid = '.UID.' and d.add_time>='.$filter['begin_time'].' and d.add_time<'.$filter['end_time'];
		break;
		case 2:
		$where = 'ub.uid = '.UID.' and d.post_time>='.$filter['begin_time'].' and d.post_time<'.$filter['end_time'];
		break;
		default:
		$where = 'ub.uid = '.UID.' and d.add_time>='.$filter['begin_time'].' and d.add_time<'.$filter['end_time'];
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
	  
	  
	  if($filter['uname'])
	  {
	    $where .= ' and uname like "%'.$filter['uname'].'%"';
	  }
	  
	  
	  if($filter['amount'])
	  {
	    $where .= ' and amount like "%'.$filter['amount'].'%"';
	  }
	  
	  if($filter['remarks'])
	  {
	    $where .= ' and remarks like "%'.$filter['remarks'].'%"';
	  }

				
		$list = M('draft_order')->alias('d')->field('d.id,d.gname,d.bname,d.order_type,d.amount,d.add_time,d.post_time,d.remarks,d.uname,d.status')
				->join('left join `onethink_uid_bid` ub on d.bid = ub.bid')->where($where)->page($filter['p'].',10')->order($limit)->select();
		//print_r($list);
		//查询该订单的关联订单
		foreach($list as $k => $v)
		{
			$order_snarray = M('draft_goods')->alias('d')->field('o.order_sn,d.gl_id')
							 ->join('left join `onethink_order` o on d.gl_id = o.id')->where('d.order_id = '.$v['id'])->group('d.gl_id')->select();
			$list[$k]['gl_sn'] = $order_snarray;
			
			//判断是否可以啪啪啪
		}
		//print_r($list);
		$a_amount = M('draft_order')->alias('d')->join('left join `onethink_uid_bid` ub on d.bid = ub.bid')->where($where)->sum('amount');

		
		//分页
		$count = M('draft_order')->alias('d')->join('left join `onethink_uid_bid` ub on d.bid = ub.bid')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Draft:order_list';

	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);// 赋值分页输出
	  
	  $this->assign('a_amount',$a_amount); 
	  
	  return array('list' => $list ,'show'=> $show ,'model_t' => $model_t);
	
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
	   'order_type' => I('order_type'),
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'date_type' => I('date_type',1),
	   'gname' => I('gname'),
	   'bname' => I('bname'),
	   'uname' => I('uname'),
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
	 $order_info = M('draft_order')->where(array('id'=>$id))->find();
	 
	 /****
	 //取出客户，部门，订单类型，下单时间，下单人，状态
	 
	 //取出商品信息
	 $goods_list = M('draft_goods')->alias('dg')
	 				->field('dg.brand_id,dg.status,dg.num,dg.price,dg.totalprice,dg.goods_id,g.goodsname,g.code,g.format,og.k_zhekou,og.g_zhekou,og.k_price,og.g_price,og.k_totalprice,og.g_totalprice')
	 				->join('left join `onethink_order_goods` og on dg.gl_id = og.order_id and dg.goods_id = og.goods_id')
					->join('left join `onethink_goods` g on dg.goods_id = g.id')
					->where('dg.order_id = '.$id)->select();
	// print_r($goods_list);
	 $new_array = array();
	 $data_a = array();
	 foreach($goods_list as $k => $v)
	 {
	 	$data_a['brand_id'] = $v['brand_id'];
		$data_a['status'] = $v['status'];
		
		$res_k = $this->inarray($data_a,$new_array);
		
		if($res_k < 0)
		{
			$array_c['brand_id'] = $v['brand_id'];
			
			$array_c['status'] = $v['status'];
			
			$array_c['goods_array'][0] = array(
			'brand_id'=>$v['brand_id'],
			'status'=>$v['status'],
			'code'=>$v['code']?$v['code']:'',
			'goodsname'=>$v['goodsname'],
			'format'=>$v['format'],
			'num'=>$v['num'],
			'price'=>$v['price'],
			'totalprice'=>$v['totalprice'],
			'k_zhekou'=>$v['k_zhekou'],
			'g_zhekou'=>$v['g_zhekou'],
			'k_price'=>$v['k_price'],
			'g_price'=>$v['g_price'],
			'k_totalprice'=>$v['k_totalprice'],
			'g_totalprice'=>$v['g_totalprice'],
			);
			
			$new_array[] = $array_c;
			
		}else
		{
			$new_array[$res_k]['goods_array'][] = array(
			'brand_id'=>$v['brand_id'],
			'status'=>$v['status'],
			'code'=>$v['code']?$v['code']:'',
			'goodsname'=>$v['goodsname'],
			'format'=>$v['format'],
			'num'=>$v['num'],
			'price'=>$v['price'],
			'totalprice'=>$v['totalprice'],
			'k_zhekou'=>$v['k_zhekou'],
			'g_zhekou'=>$v['g_zhekou'],
			'k_price'=>$v['k_price'],
			'g_price'=>$v['g_price'],
			'k_totalprice'=>$v['k_totalprice'],
			'g_totalprice'=>$v['g_totalprice'],
			);
		}
	 }
	 
	 **/
	 
	 //以上先不做
	 
	 $order_goods = M('draft_goods')->where('order_id = '.$id)->select();
	 
	 $draft_order = array();
	 
	 $draft_order['order_info'] = $order_info;
	 
	 $draft_order['order_goods'] = $order_goods;

	  
	 
	 return array('draft_order'=>$draft_order,'goods_list'=>$new_array);
	 
	}
	
	private function inarray($array1,$array2)
	{
		foreach($array2 as $k=>$v)
		{
			if($array1['brand_id'] == $v['brand_id'] && $array1['status'] == $v['status'])
			{
				return $k;
			}
		}
		
		return -1;
		
		
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
	  if(1 == $type)
		   {
		     $fuhao = 1;
		   }else
		   {
		     $fuhao = -1;
		   }
		   
		  return  $fuhao;
	}
	

}