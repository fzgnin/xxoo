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
 * 黄基本ajax分页操作类
 * huang
 */
class AjaxqueryController extends AdminController {

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
	   'guest_id' => I('guest_id'),
	   'name' => I('name'),
	   'bid'=>I('bid'),
	  );
	  
	 return $filter;
	
	}
	
	
	public function select_information()
	{
	  
	  //获取相关供应商列表
	  
	  $list = $this->get_list();
	  
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  if('guest' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_guest');
	  }
	  elseif('bumen' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_bumen');
	  }
	  elseif('goods' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_goods');
	  }
	  else if('supplier' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_supplier');  
	  }
	  else if('kgoods' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_kgoods');  
	  }
	  else if('doublebumen' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_doublebumen');  
	  }
	  else if('money' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_money');  
	  }
	  else if('department' == I('type'))
	  {
	   $this->display('Ajax:Popup:ajax_select_department');  
	  }
	  else if('igoods' == I('type'))
	  {
	  	$this->display('Ajax:Popup:ajax_select_igoods');
	  }
	  else if('sgoods' == I('type'))
	  {
	  	$this->display('Ajax:Popup:ajax_select_sgoods');
	  }
	  else if('omoney' == I('type'))
	  {
	  	$this->display('Ajax:Popup:ajax_select_omoney');
	  }
	  else if('subject' == I('type'))
	  {
	  	$this->display('Ajax:Popup:ajax_select_subject');
	  }
	  else if('user' == I('type'))
	  {
	  	$this->display('Ajax:Popup:ajax_select_user');
	  }
	  else if('guests' == I('type'))
	  {
	  	$this->display('Ajax:Popup:ajax_select_guests');
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
	
	private function get_list($filter = array())
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
	  
	  if($filter['type'] == 'guest')
	  {
		  
		  $where['stus'] = 1;
		  
		  if($filter['keywords'])
		  {
			$where['guestname'] = array('like','%'.$filter['keywords'].'%');
		  }  
		  
		  $list = M('guest')->field('id,guestname,province,bid,(b_money+money) as money')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  if(count($list) > 0)
		   {
			 foreach($list as $k=>$v)
			 {
				//取出所属部门
				$list[$k]['bumen'] = M('bid_gid')->alias('g')->field('b.id as bid,b.bname')->join('left join `onethink_bumen` b on g.bid = b.id')->where(array('gid' => $v['id']))->select();
			 }
		   }
	
		   //分页
		  $count = M('guest')->where($where)->count();
		  
		  //模版
		  $model_t = 'Ajax:Popup:ajax_guest_list';
		  
	  }
	  
	  elseif($filter['type'] == 'bumen')
	  {
	  
	     $where['status'] = 1;
		 
		 if($filter['keywords'])
		  {
			$where['bname'] = array('like','%'.$filter['keywords'].'%');
		  } 
		 
		 $list = M('bumen')->field('id,bid,bname')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('bumen')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_bumen_list';
		  
	  }
	  
	  elseif($filter['type'] == 'department')
	  {
	    if($filter['keywords'])
		  {
			$where['name'] = array('like','%'.$filter['keywords'].'%');
		  }else
		  {
			$where = '';
		  }	 
		 
		 $list = M('department')->field('id,name')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('department')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_department_list';
	  
	  
	  
	  }
	  
	  
	  elseif($filter['type'] == 'doublebumen')
	  {
	  
	     $where['status'] = 1;
		 
		 if($filter['keywords'])
		  {
			$where['bname'] = array('like','%'.$filter['keywords'].'%');
		  } 
		 
		 $list = M('bumen')->field('id,bid,bname')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('bumen')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_doublebumen_list';
		  
	  }
	  
	  
	  
	  
	  elseif($filter['type'] == 'goods')
	  {
	    if($filter['keywords'])
		  {
			if($filter['name'] == 'code[]')
			{
			 $where['code'] = array('like','%'.$filter['keywords'].'%');
			}elseif($filter['name'] == 'goodsname[]')
			{
			 $where['goodsname'] = array('like','%'.$filter['keywords'].'%');
			}
			
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('goods')->field('id,code,goodsname,calculation,price,format,brand_id')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  //循环商品列表取出该商品针对该客户的折扣（如果有客户id传入）
		  
		  if($filter['guest_id'] && $list)
		  {
		    foreach($list as $k=>$v)
			{
			  $res = M('branddiscount')->where('guest_id = '.$filter['guest_id'].' and brand_id = '.$v['brand_id'])->field('discount_k,discount_g')->find();
			  
			  $list[$k]['zhekou'] = $res['discount_k'];
			  $list[$k]['g_zhekou'] = $res['discount_g'];
			}
		  }
	
		   //分页
		  $count = M('goods')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_goods_list';
		  
	  
	  }
	  
	  
	  
	  elseif($filter['type'] == 'igoods')
	  {
	    if($filter['keywords'])
		  {
			if($filter['name'] == 'code[]')
			{
			 $where['code'] = array('like','%'.$filter['keywords'].'%');
			}elseif($filter['name'] == 'goodsname[]')
			{
			 $where['goodsname'] = array('like','%'.$filter['keywords'].'%');
			}
			
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('goods')->field('id,code,goodsname,calculation,price,format,cost_one')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  //取出商品库存均价
		  
		  if($list)
		  {
		    foreach($list as $k=>$v)
			{
			  $list[$k]['averages'] = M('warehouse')->where('goods_id = '.$v['id'])->getField('averages');
			}
		  }
	
		   //分页
		  $count = M('goods')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_igoods_list';
		  
	  
	  }
	  
	  
	  
	  elseif('supplier' == $filter['type'])
	  {
	     if($filter['keywords'])
		  {
			 $where['suppliername'] = array('like','%'.$filter['keywords'].'%');	
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('supplier')->field('id,suppliername,province')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('supplier')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_supplier_list'; 
	  }
	  
	  elseif('kgoods' == $filter['type'])
	  {
	    if($filter['keywords'])
		  {
			if($filter['name'] == 'code[]')
			{
			 $where['g.code'] = array('like','%'.$filter['keywords'].'%');
			}elseif($filter['name'] == 'goodsname[]')
			{
			 $where['g.goodsname'] = array('like','%'.$filter['keywords'].'%');
			}
			
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('goods')->alias('g')->field('g.id,g.code,g.goodsname,g.calculation,g.price,g.format,g.brand_id,ifnull(w.n_num,0) as num')->join('left join `onethink_warehouse` as w on g.id = w.goods_id')
		          ->where($where)->page($filter['p'].',10')->order($limit)->select();
	//print_r($list);
		   //分页
		  $count = M('goods')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_kgoods_list';
	  
	  }
	  
	  
	  elseif('sgoods' == $filter['type'])
	  {
	    if($filter['keywords'])
		  {
			if($filter['name'] == 'code[]')
			{
			 $where['g.code'] = array('like','%'.$filter['keywords'].'%');
			}elseif($filter['name'] == 'goodsname[]')
			{
			 $where['g.goodsname'] = array('like','%'.$filter['keywords'].'%');
			}
			
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('goods')->alias('g')->field('g.id,g.code,g.goodsname,g.calculation,g.price,g.format,g.cost_one,w.averages')->join('left join `onethink_warehouse` as w on g.id = w.goods_id')
		          ->where($where)->page($filter['p'].',10')->order($limit)->select();
	//print_r($list);
		   //分页
		  $count = M('goods')->alias('g')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_sgoods_list';
	  
	  }
	  
	  
	  
	  elseif('money' == $filter['type'])
	  {
	    if($filter['keywords'])
		  {
			 $where['cname'] = array('like','%'.$filter['keywords'].'%');	
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('company')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('company')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_money_list'; 
	  
	  }
	  
	  
	  elseif('omoney' == $filter['type'])
	  {
	    if($filter['keywords'])
		  {
			 $where['cname'] = array('like','%'.$filter['keywords'].'%');	
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('company')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('company')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_omoney_list'; 
	  
	  }
	  
	  
	  elseif('subject' == $filter['type'])
	  {
	    if($filter['keywords'])
		  {
			 $where['name'] = array('like','%'.$filter['keywords'].'%');	
		  }else
		  {
			$where = '';
		  }
		  
		  $list = M('subject')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  //取出项目的辅助核算
		  foreach($list as $k=>$v)
		  {
			$list[$k]['assist'] = M('assist')->where('id in ('.$v['assist_id'].')')->select();
		  }
	
		   //分页
		  $count = M('subject')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_subject_list'; 
	  
	  }
	  
	  
	  elseif('user' == $filter['type'])
	  {
	  	$where['onjob'] = 1;
		
		if($filter['keywords'])
		  {
			 $where['name'] = array('like','%'.$filter['keywords'].'%');	
		  }
		  
		  $list = M('user')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('user')->where($where)->count();
		  //模版
		  $model_t = 'Ajax:Popup:ajax_user_list'; 
	  
	  }
	  
	  elseif($filter['type'] == 'guests')
	  {
		  
		  $where['g.stus'] = 1;
		  
		  if($filter['keywords'])
		  {
			$where['g.guestname'] = array('like','%'.$filter['keywords'].'%');
		  }
		  
		  if($filter['bid'])
		  {
		  	$where['bg.bid'] = $filter['bid'];
		  }  

		  $list = M('bid_gid')->alias('bg')->join('left join `onethink_guest` g on bg.gid = g.id')
		  			->field('g.id,g.guestname,g.province,g.address,g.manager')->where($where)->page($filter['p'].',10')->order($limit)->select();
	
		   //分页
		  $count = M('bid_gid')->alias('bg')->join('left join `onethink_guest` g on bg.gid = g.id')->where($where)->count();		
		  
		  //模版
		  $model_t = 'Ajax:Popup:ajax_guests_list';
		  
	  }
	  
	  else
	  {
	    $list = M('order')->where('uid = '.UID.' and order_type = '.$filter['order_type'])->page($filter['p'].',10')->order($limit)->select();
		
		if(count($list)>0)
		{
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['guestname'] = M('guest')->where(array('id'=>$v['guest_id']))->getField('guestname'); 
		   $list[$k]['bname'] = M('bumen')->where(array('id'=>$v['bumen_id']))->getField('bname');
		   $list[$k]['amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['amount'];
		   $list[$k]['k_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['k_amount'];
		   $list[$k]['g_amount'] = $this->order_fuhao($v['order_type'])*$list[$k]['g_amount'];
		 }
		}
		
		//分页
		$count = M('order')->where('uid = '.UID.' and order_type = '.$filter['order_type'])->count();
		
		//模版
		$model_t = 'Ajax:order_list';
	  
	  }

	  
	  $_GET['p'] = $filter['p'];
	  
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show ,'model_t' => $model_t );
	
	}

	
}