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
class ReportController extends AdminController {

    /**
     * 商品管理
     * 黄
     */
	 
    public function index(){
	
		//获取今天的时间戳
		
		$filter = $this->query_array();
		
		$filter['type'] = 1;
		
		$list = $this->get_list($filter);
		
		$this->assign('filter',$filter);
		
		$this->assign('list',$list['list']);

	    $this->display(); // 输出模板
	 
    }
	
	
	public function baobiao(){
        
		
		//获取今天的时间戳
		
		$filter = $this->query_array();
		
		$filter['type'] = 'baobiao';
	
		$this->send_out($filter);
    }
	
	public function orderlist()
	{
	    $filter = $this->query_array();
		
		$filter['type'] = 1;
		
		$list = $this->get_list($filter);
		
		$this->assign('beginToday',$filter['begin_time']);
		
		$this->assign('nowToday',$filter['end_time']);
		
		$this->assign('list',$list['list']);

	    $this->display(); // 输出模板
	
	}
	
	public function bumenfeat()
	{
	    $filter = $this->query_array();
		
		$filter['type'] = 6;
		
		$list = $this->get_list($filter);
		
		$this->assign('filter',$filter);
		
		$this->assign('list',$list['list']);

	    $this->display(); // 输出模板
	
	}


	public function ajax_query()
	{
	 
	 //条件数组
	 
     $list = $this->get_list();
	
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出
	 
	 $this->assign('filter',$this->query_array());// 赋值查询条件

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

	  $limit = $this->limit_function($filter);

	  $where = 'o.add_time>='.$filter['begin_time'].' and o.add_time<'.$filter['end_time'];
	  
	  if($filter['order_type'])
	  {
	    $where .= ' and o.order_type = '.$filter['order_type'];
	  }
	  
	  if($filter['bumen_id'])
	  {
	    $where .= ' and o.bumen_id = '.$filter['bumen_id'];
	  }
	  
	  if($filter['guest_id'])
	  {
	    $where .= ' and o.guest_id = '.$filter['guest_id'];
	  }
	  
	  if($filter['tid'])
	  {
	    $where .= ' and o.tid = '.$filter['tid'];
	  }
	  
	  if($filter['goods_id'])
	  {
	    $where .= ' and g.goods_id = '.$filter['goods_id'];
	  }
	  
	  
		//重构报表数据，（报表查询）
		
		if('baobiao' == $filter['type'])
		{
			//获取用户勾选的对比信息（暂时设为固定）
			$bid_arr = array(1,2,3,4,5,6,43);
			
			//根据对比信息取出本部业绩和子部门业绩
			$where1['b.id'] = array('in',$bid_arr);
			
			$list = M('bumen')
					->alias('b')
					->field('b.id,b.bname,sum(o.g_amount) as g_my')
					->join('left join `onethink_order` o on b.id = o.bumen_id ')
					->where($where1)
					->group('b.id')
					->page($filter['p'].',10')
					->select();
			
			foreach($list as $k=>$v)
			{
				//获取下属子类列表
				$child_arr = $this->get_child_array($v['id']);
				
				//根据子类获取子类的业绩
				if(!empty($child_arr))
				{
					$child_arr = implode(',',$child_arr);
					$list[$k]['c_my'] = M('order')->where('bumen_id in ('.$child_arr.')')->sum('g_amount');
				}
				else
				{
					$list[$k]['c_my'] = 0;
				}		
				
				$list[$k]['g_my'] = $list[$k]['g_my']?$list[$k]['g_my']:0;
				$list[$k]['a_my'] = $list[$k]['g_my']?$list[$k]['g_my']+$list[$k]['c_my']:$list[$k]['c_my'];

			}	
			
			$count = count($bid_arr);
				  
			$a_amount = M('bumen')->alias('b')->join('left join `onethink_order` o on b.id = o.bumen_id ')->where($where1)->sum('o.g_amount');
			
			$model_t = 'Ajax:Report:report_jlist';
		}
	  
	  
	  
	 
	  
	  elseif(1 == $filter['type'])
	  {
	    if($filter['tid'])
		{
		
			if($filter['bumen_id'])
			{
			  if($filter['goods_id'])
			  {
				  //获取该部门下所有的订单列表
				  $list = M('order_goods')->alias('g')->field('o.id,o.order_sn,o.add_time,o.order_type,o.guest_id,g.goods_id,g.goodsname,g.code,g.format,g.num,g.price,g.g_price,g.g_totalprice,g.bid,o.remarks')
						  ->join('`onethink_order` as o on g.order_id = o.id')->where($where)->page($filter['p'].',10')->order($limit)->select();
				  //print_r($list);
				  if($list)
				  {
					foreach($list as $k=>$v)
					{
					  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
					  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bid']))->getField('bname');
					}
				  }
				  
				  $count = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where($where)->count();
				  
				  $a_amount = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where($where)->sum('g_totalprice');
				  
				  $model_t = 'Ajax:Report:report_odlist';
			  }else
			  {
			      //获取该部门下所有的订单列表
				  $list = M('order')->alias('o')->field('o.id,o.order_sn,o.add_time,o.order_type,o.guest_id,o.bumen_id,o.amount,o.k_amount,o.g_amount,o.remarks,o.warehouse')
						  ->where($where)->page($filter['p'].',10')->order($limit)->select();
				  //print_r($list);
				  if($list)
				  {
					foreach($list as $k=>$v)
					{
					  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
					  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bumen_id']))->getField('bname');
					  $ff = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where('g.order_id = '.$v['id'])->sum('g_totalprice');
		   
					   if($ff != $v['g_amount'])
					   {
						 $list[$k]['error'] = 1;
					   }else
					   {
						 $list[$k]['error'] = 0;
					   }
					}
				  }
				  
				  $count = M('order')->alias('o')->where($where)->count();
				  
				  $a_amount = M('order')->alias('o')->where($where)->sum('g_amount');
				  
				  $model_t = 'Ajax:Report:report_list';
			  
			  
			  }
			  
			  
			}else
			{
				
				$list = M('order_goods')->alias('g')->field('o.bumen_id,sum(g.num) as a_num,sum(totalprice) as a_my,sum(k_totalprice) as k_my,sum(g_totalprice) as g_my')
						->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('o.bumen_id')->page($filter['p'].',10')->order($limit)->select();
						
				if($list)
				{
				   foreach($list as $k => $v)
				   {
				     $list[$k]['bname'] = M('bumen')->where('bid = '.$v['bumen_id'])->getField('bname');
				   }
				}
				
				$count =  M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->count('distinct bumen_id');
				
				$a_amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g_totalprice');
		
				//模版
				$model_t = 'Ajax:Report:report_blist';
			}
			
		}else
		{
			//取展示业绩的军团
			$tid_arr = M('juntuan')->where('status = 1')->getField('id',true);
			
			$count =  count($tid_arr);
			
			$tid_arr = implode(',',$tid_arr);
			
			$where .= ' and o.tid in ('.$tid_arr.')';
			
			$a_amount = 0;
			
			$list = M('order_goods')->alias('g')->field('o.tid,sum(g.num) as a_num,sum(totalprice) as a_my,sum(k_totalprice) as k_my,sum(g_totalprice) as g_my')
					->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('o.tid')->page($filter['p'].',10')->order($limit)->select();
			
			foreach($list as $k=>$v)
			{
				$list[$k]['tname'] = M('juntuan')->where('id = '.$v['tid'])->getField('tname');
				
				$a_amount += $v['g_my'];
			}
			
			
			//模版
			$model_t = 'Ajax:Report:report_jlist';
		}
		
		
	  }
	  
	 
	 
	  //按照部门来选择
	  elseif(2 == $filter['type'])
	  {
		
		
		if($filter['bumen_id'])
		{ 
		  if($filter['goods_id'])
			  {
				  //获取该部门下所有的订单列表
				  $list = M('order_goods')->alias('g')->field('o.id,o.order_sn,o.add_time,o.order_type,o.guest_id,g.goods_id,g.goodsname,g.code,g.format,g.num,g.price,g.g_price,g.g_totalprice,g.bid,o.remarks')
						  ->join('`onethink_order` as o on g.order_id = o.id')->where($where)->page($filter['p'].',10')->order($limit)->select();
				  //print_r($list);
				  if($list)
				  {
					foreach($list as $k=>$v)
					{
					  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
					  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bid']))->getField('bname');
					}
				  }
				  
				  $count = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where($where)->count();
				  
				  $a_amount = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where($where)->sum('g_totalprice');
				  
				  $model_t = 'Ajax:Report:report_odlist';
			  }else
			  {
			      //获取该部门下所有的订单列表
				  $list = M('order')->alias('o')->field('o.id,o.order_sn,o.add_time,o.order_type,o.guest_id,o.bumen_id,o.amount,o.k_amount,o.g_amount,o.remarks,o.warehouse')
						  ->where($where)->page($filter['p'].',10')->order($limit)->select();
				  //print_r($list);
				  if($list)
				  {
					foreach($list as $k=>$v)
					{
					  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
					  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bumen_id']))->getField('bname');
					  $ff = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where('g.order_id = '.$v['id'])->sum('g_totalprice');
		   
					   if($ff != $v['g_amount'])
					   {
						 $list[$k]['error'] = 1;
					   }else
					   {
						 $list[$k]['error'] = 0;
					   }
					}
				  }
				  
				  $count = M('order')->alias('o')->where($where)->count();
				  
				  $a_amount = M('order')->alias('o')->where($where)->sum('g_amount');
				  
				  $model_t = 'Ajax:Report:report_list';
			  
			  
			  }
		  
		}else
		{
	
			//取展示业绩的部门
			$bid_arr = M('bumen')->alias('b')->field('b.id')->join('left join `onethink_juntuan` j on b.tid = j.id ')->where('j.status = 1')->select();
			
			$bid_arr = array_column($bid_arr, 'id');
			
			$bid_arr = implode(',',$bid_arr);
			
			$where .= ' and o.bumen_id in ('.$bid_arr.')';
			
			
			$list = M('order_goods')->alias('g')->field('o.bumen_id,o.bname,sum(g.num) as a_num,sum(totalprice) as a_my,sum(k_totalprice) as k_my,sum(g_totalprice) as g_my')
						->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('o.bumen_id')->page($filter['p'].',10')->order($limit)->select();
			//print_r($list);			
				
				
				$count =  M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->count('distinct bumen_id');
				
				$a_amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g_totalprice');
		
				//模版
				$model_t = 'Ajax:Report:report_blist';
		}

	  }
	  	  
	  
	  //按照店家客户来选择
	  elseif(3 == $filter['type'])
	  {
		
		//判断是否有店家的id有的话就是终端店家详细订单信息
		if($filter['guest_id'])
		{	  
		  if($filter['goods_id'])
			  {
				  //获取该部门下所有的订单列表
				  $list = M('order_goods')->alias('g')->field('o.id,o.order_sn,o.add_time,o.order_type,o.guest_id,g.goods_id,g.goodsname,g.code,g.format,g.num,g.price,g.g_price,g.g_totalprice,g.bid,o.remarks')
						  ->join('`onethink_order` as o on g.order_id = o.id')->where($where)->page($filter['p'].',10')->order($limit)->select();
				  //print_r($list);
				  if($list)
				  {
					foreach($list as $k=>$v)
					{
					  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
					  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bid']))->getField('bname');
					}
				  }
				  
				  $count = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where($where)->count();
				  
				  $a_amount = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where($where)->sum('g_totalprice');
				  
				  $model_t = 'Ajax:Report:report_odlist';
			  }else
			  {
			      //获取该部门下所有的订单列表
				  $list = M('order')->alias('o')->field('o.id,o.order_sn,o.add_time,o.order_type,o.guest_id,o.bumen_id,o.amount,o.k_amount,o.g_amount,o.remarks,o.warehouse')
						  ->where($where)->page($filter['p'].',10')->order($limit)->select();
				  //print_r($list);
				  if($list)
				  {
					foreach($list as $k=>$v)
					{
					  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
					  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bumen_id']))->getField('bname');
					  $ff = M('order_goods')->alias('g')->join('`onethink_order` as o on g.order_id = o.id')->where('g.order_id = '.$v['id'])->sum('g_totalprice');
		   
					   if($ff != $v['g_amount'])
					   {
						 $list[$k]['error'] = 1;
					   }else
					   {
						 $list[$k]['error'] = 0;
					   }
					}
				  }
				  
				  $count = M('order')->alias('o')->where($where)->count();
				  
				  $a_amount = M('order')->alias('o')->where($where)->sum('g_amount');
				  
				  $model_t = 'Ajax:Report:report_list';
			  
			  
			  }
		  
		}else
		{
		  
		  //无店家id显示店家的详细列表信息还要考虑商品信息
		  
		  $list = M('order_goods')->alias('g')->field('g.guest_id,sum(g.num) as a_num,sum(totalprice) as a_my,sum(k_totalprice) as k_my,sum(g_totalprice) as g_my')
		        ->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('g.guest_id')->page($filter['p'].',10')->order($limit)->select();
				
		  if($list)
		  {
		    foreach($list as $k => $v)
			{
			  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
			}
		  }

		  $count =  M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->count('distinct g.guest_id');
		  
		  $a_amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g_totalprice');
//print_r(M('order_goods')->getLastsql());exit;
		  //模版
		  $model_t = 'Ajax:Report:report_dlist';
		}
		
		
	  }
	  
	  
	  
	  //按照商品筛选
	  elseif(4 == $filter['type'])
	  {

		if($filter['brand_id'])
		{
		 $where .= ' and g.brand_id = '.$filter['brand_id'];
		}
		
		//判断是否有商品id有的话就是终端商品详细销售情况
		if($filter['goods_id'])
		{
		  
		  //获取该商品的订单信息
		  
		  $list = M('order_goods')->alias('g')->field('o.id,o.order_sn,o.guest_id,o.bumen_id,o.add_time,o.order_type,g.goodsname,g.num,g.price,g.totalprice,g.k_totalprice,g.g_totalprice')
		          ->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->page($filter['p'].',10')->order($limit)->select();
			//print_r($list);	  
		  if($list)
		  {
		    foreach($list as $k=>$v)
			{
			  $list[$k]['guestname'] = M('guest')->where('id = '.$v['guest_id'])->getField('guestname');
			  $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bumen_id']))->getField('bname');
			}
		  }
				  
		  $count = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->count();
		  
		  $a_amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g.g_totalprice');
		  
		  $model_t = 'Ajax:Report:report_oglist';
		  
		}else
		{
			$list = M('order_goods')->alias('g')->field('g.goods_id,g.goodsname,sum(g.num) as a_num,sum(g.totalprice) as a_my,sum(g.k_totalprice) as k_my,sum(g.g_totalprice) as g_my')
			        ->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('g.goods_id')->page($filter['p'].',10')->order($limit)->select();
			//print_r($list);
			$count =  M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->count('distinct goods_id');
			
			$a_amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g.g_totalprice');
	
			//模版
			$model_t = 'Ajax:Report:report_slist';
		
		}
		
		
		//下面是模版赋值
		//获取品牌
		$brand_list = M('brand')->field('id,name')->select();
		
		$this->assign('brand_list',$brand_list);

	  }
	  
	  
	  //普通请求
	  
	  elseif(5 == $filter['type'])
	  {
	
		$list = M('order')->alias('o')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['guestname'] = M('guest')->where(array('id'=>$v['guest_id']))->getField('guestname'); 
		   $list[$k]['bname'] = M('bumen')->where(array('bid'=>$v['bumen_id']))->getField('bname');
		   $list[$k]['amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['amount'];
		   $list[$k]['k_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['k_amount'];
		   $list[$k]['g_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['g_amount'];
		 }
		}
		
		$count = M('order')->where($where)->count();

		//模版
		$model_t = 'Ajax:order_list';
	  }
	  
	  elseif(6 == $filter['type'])
	  {
	    $where = 'add_time>='.$filter['begin_time'].' and add_time<='.$filter['end_time'];
		
		if($filter['order_type'])
		{
		  $where .= ' and order_type = '.$filter['order_type'];
		}
		
		if($filter['guest_id'])
		{
		  $where .= ' and gid = '.$filter['guest_id'];
		}
		
		if($filter['bumen_id'])
		{
		  $where .= ' and bid = '.$filter['bumen_id'];
		}
		
		if($filter['tid'])
		{
		  $where .= ' and tid = '.$filter['tid'];
		}
		
		//print_r($where);
		
		if($filter['tid'])
		{
		
			if($filter['bumen_id'])
			{
                  //获取该部门下所有的订单列表
				  
				  $list = M('bumen_feat')->where($where)->page($filter['p'].',10')->order($limit)->select();	  
				// print_r($list);
				  $count = M('bumen_feat')->where($where)->count();
				  
				  $a_amount = M('bumen_feat')->where($where)->sum('feat');
				  
				  $model_t = 'Ajax:Report:feat_list';

			  
			}else
			{
				
				$list = M('bumen_feat')->field('bid,bname,sum(feat) as g_my')->where($where)->group('bid')->page($filter['p'].',10')->order($limit)->select();
		
				$count =  M('bumen_feat')->where($where)->count('distinct bid');
				
				$a_amount = M('bumen_feat')->where($where)->sum('feat');
		
				//模版
				$model_t = 'Ajax:Report:feat_blist';
			}
			
		}else
		{
			//取展示业绩的军团
			$tid_arr = M('juntuan')->where('status = 1')->getField('id',true);
			
			$count =  count($tid_arr);
			
			$tid_arr = implode(',',$tid_arr);
			
			$where .= ' and tid in ('.$tid_arr.')';
			
			$list = M('bumen_feat')->field('tid,sum(feat) as g_my')->where($where)->group('tid')->page($filter['p'].',10')->order($limit)->select();
			
			foreach($list as $k => $v)
			{
				$list[$k]['tname'] = M('juntuan')->where('id = '.$v['tid'])->getField('tname');
			}
			
			$a_amount = M('bumen_feat')->where($where)->sum('feat');
			
			//模版
			$model_t = 'Ajax:Report:feat_jlist';
		}
	  
	  }
	  
	  //部门
	  elseif(7 == $filter['type'])
	  {
	    $where = 'add_time>='.$filter['begin_time'].' and add_time<='.$filter['end_time'];
		
		if($filter['order_type'])
		{
		  $where .= ' and order_type = '.$filter['order_type'];
		}
		
		if($filter['guest_id'])
		{
		  $where .= ' and gid = '.$filter['guest_id'];
		}
		
		if($filter['bumen_id'])
		{
		  $where .= ' and bid = '.$filter['bumen_id'];
		}
		
		if($filter['tid'])
		{
		  $where .= ' and tid = '.$filter['tid'];
		} 
 
		 if($filter['bumen_id'])
		{ 
				  
				  $list = M('bumen_feat')->where($where)->page($filter['p'].',10')->order($limit)->select();	  
				 
				  $count = M('bumen_feat')->where($where)->count();
				  
				  $a_amount = M('bumen_feat')->where($where)->sum('feat');
				  
				  $model_t = 'Ajax:Report:feat_list';

		}else
		{
				
				$list = M('bumen_feat')->field('*,sum(feat) as g_my')->where($where)->group('bid')->page($filter['p'].',10')->order($limit)->select();
//print_r($list);exit;
				$count =  M('bumen_feat')->where($where)->count('distinct bid');
				
				$a_amount = M('bumen_feat')->where($where)->sum('g_my');
		
				//模版
				$model_t = 'Ajax:Report:feat_blist';
		}
	  
	  
	  }
	  
	  //按店家
	  elseif(8 == $filter['type'])
	  {
	     
		 $where = 'add_time>='.$filter['begin_time'].' and add_time<='.$filter['end_time'];
		
		if($filter['order_type'])
		{
		  $where .= ' and order_type = '.$filter['order_type'];
		}
		
		if($filter['guest_id'])
		{
		  $where .= ' and gid = '.$filter['guest_id'];
		}
		
		if($filter['bumen_id'])
		{
		  $where .= ' and bid = '.$filter['bumen_id'];
		}
		
		if($filter['tid'])
		{
		  $where .= ' and tid = '.$filter['tid'];
		}
		 
		 
		 //判断是否有店家的id有的话就是终端店家详细订单信息
		if($filter['guest_id'])
		{	   
			      //获取该部门下所有的订单列表
				  $list = M('bumen_feat')->where($where)->page($filter['p'].',10')->order($limit)->select();

				  $count = M('bumen_feat')->where($where)->count();
				  
				  $a_amount = M('bumen_feat')->where($where)->sum('feat');
				  
				  $model_t = 'Ajax:Report:feat_list';

		  
		}else
		{
		  
		  //无店家id显示店家的详细列表信息还要考虑商品信息
		  
		  $list = M('bumen_feat')->field('*,sum(feat) as g_my')->where($where)->group('gid')->page($filter['p'].',10')->order($limit)->select();
		        

		  $count =  M('bumen_feat')->where($where)->count('distinct gid');
		  
		  $a_amount = M('bumen_feat')->where($where)->sum('feat');

		  //模版
		  $model_t = 'Ajax:Report:feat_dlist';
		}
	  
	  
	  }
	  
	  $this->assign('a_amount',$a_amount);
	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);
	  
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
	   'keywords' => I('keywords'),
	   'type' => I('type'),
	   'tid' => I('tid'),
	   'order_type' => I('order_type'),
	   'guest_id' => I('guest_id'),
	   'bumen_id' => I('bumen_id'),
	   'guestname' => I('guestname')?I('guestname'):I('guest_id')?M('guest')->where('id = '.I('guest_id'))->getField('guestname'):'',
	   'bname' => I('bname')?I('bname'):I('bumen_id')?M('bumen')->where('bid = '.I('bumen_id'))->getField('bname'):'',
	   'brand_id' => I('brand_id'),
	   'goods_id' => I('goods_id'),
	   'goodsname' => I('goodsname')?I('goodsname'):I('goods_id')?M('goods')->where('id = '.I('goods_id'))->getField('goodsname'):'',
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	  );
	  
	 return $filter;
	
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
	
	//编写limit函数
	
	private function limit_function($filter)
	{
	  $limit = '';
	  
	  //判断是否有商品id或部门id或顾客id，有的话且$filter['order_by'] = a_my,k_my,g_my其中之一赋值$limit否则为空
	  
	  $f_array = array("a_num", "a_my", "k_my", "g_my");
	  
	  switch ($filter['type'])
	  {
	    case 1:
		if(!$filter['bumen_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		break;
		
		case 2:
			if(!$filter['bumen_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		break;
		
		case 3:
		    if(!$filter['guest_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		
		break;
		
		case 4:
		if(!$filter['goods_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		break;
		
		case 5:
		$distiy = true;
		break;
		
		case 6:
		if(!$filter['bumen_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		break;
		
		case 7:
		if(!$filter['bumen_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		break;
		
		case 8:
		if(!$filter['guest_id'])
			{
			  $distiy = true;
			}else
			{
			  $distiy = false;
			}
		break;
		
		default:
		$distiy = true;
	  
	  }
	  
	  
	  
	  if($filter['order_by'] && (in_array($filter['order_by'], $f_array) && $distiy || !in_array($filter['order_by'], $f_array) && !$distiy))
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }
	  //print_r($distiy);
	  return $limit;

	}
	
	private function send_out($filter)
	{
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display();
	}
	
	
	private function get_child_array($array = array())
	{
		$where = is_array($array)?'pid in ('.implode(',',$array).')':'pid ='.$array;
		
		$where .= ' and status = 1';
		
		$new_array = M('bumen')->where($where)->getField('id',true);
		
		$new_array = $new_array?array_merge($new_array,$this->get_child_array($new_array)):array();
		
		return $new_array;
	}
	
}