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
 * 资金转账单
 * huang
 */
class GiroController extends AdminController {

    /**
     * 资金转账单
     * 黄
     */
	public function index(){
	
		$this->display(); // 输出模板
	 
	}


    //新增资金转账单
    public function add(){
	
		//生成订单编号固定格式
		$order_sn = make_order_sn(14,'ZJZZ');
		
		$this->assign('order_sn',$order_sn);
		
		$this->assign('time',date("Y-m-d H:i"));
		
		$this->assign('order_type',14);
		
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
		
		$order_info = M('giro_order')->where(array('id'=>$id))->find();
		
		//判断订单是否过账，已过账订单不能修改
		if(1 == $order_info['status'])
		{
			$this->error('已过账订单暂不能修改！');
		}
		
		$this->assign('order_info',$order_info);
		
		$this->display();
	}
	
	public function order_info()
	{
		$id = I('id');
		
		$order_info = M('giro_order')->where(array('id'=>$id))->find();
		
		$this->assign('order_info',$order_info);
		
		$this->display();
	}
	
	
	
	//提交资金转帐单
	public function insert()
	{
		//获取post数据
		$data = array(
			'id'=>I('id'),
			'add_time' => strtotime(I('add_time')),
			'insert_time'=>time(),
			'order_sn' => I('order_sn'),
			'order_type'=>I('order_type',14),
			'out_cname' => I('out_cname'),
			'out_cid' => I('out_cid'),
			'in_cname' => I('in_cname'),
			'in_cid' => I('in_cid'),
			'money' => I('money'),
			'purpose' => I('purpose'),
			'remarks' => I('remarks'),
		);
		
		if(!($data['out_cname'] && $data['out_cid'] && $data['in_cname'] && $data['in_cid'] && $data['money']))
		{
			$this->error('请填写完整信息！');
		}
		
		
		//数据过滤
		if($data['out_cid'] == $data['in_cid'])
		{
			$this->error('转出账户和转入账户不能相同');
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
		//判断订单是否过账
		if($data['id'])
		{
			$order_info = M('giro_order')->where('id = '.$data['id'])->find();
			if(!$order_info || 1 == $order_info['status'])
			{
		  		$this->error('此订单不存在或已过账！');
			}
		}
		elseif(M('giro_order')->where("order_sn = '".$data['order_sn']."'")->find())
		{
			$this->error('订单号重复！');
		}
		
		M('giro_order')->startTrans(); 
		   
		//先写入订单表
		//使用replace into 做新增或更新操作
		if($data['id'])
		{
			$res = M('giro_order')->save($data);
			$res_order = 1;
		}
		else
		{
			$res = M('giro_order')->add($data);
			$res_order = M('session_all_id')->where("user_id=".UID." and order_type = ".$data['order_type'])->setField('is_use',1);
		}
		  
		if($res && $res_order)
		{
			M('giro_order')->commit();//成功则提交
			$this->success('操作成功！');
		}else
		{
			M('giro_order')->rollback();//成功则提交
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
		if(1 == M('giro_order')->where('id = '.$id)->getField('status'))
		{
			$this->error('此订单已过账，请联系财务进行删除！');
		}
		
		if(M('giro_order')->where('id = '.$id)->delete())
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
	  
		if($filter['order_sn'])
		{
			$where .= ' and order_sn like "%'.$filter['order_sn'].'%"';
		}
	  
		if($filter['money'])
		{
			$where .= ' and money like "%'.$filter['money'].'%"';
		}
	  
		if($filter['remarks'])
		{
			$where .= ' and remarks like "%'.$filter['remarks'].'%"';
		}
		
		if($filter['out_cname'])
		{
			$where .= ' and out_cname like "%'.$filter['out_cname'].'%"';
		}
		
		if($filter['in_cname'])
		{
			$where .= ' and in_cname like "%'.$filter['in_cname'].'%"';
		}

				
		$list = M('giro_order')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		//分页
		$count = M('giro_order')->where($where)->count();
		
		//模版
		$model_t = 'Ajax:Giro:order_list';
	  
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
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'date_type' => I('date_type',1),
	   'out_cname' => I('out_cname'),
	   'in_cname' => I('in_cname'),
	   'order_sn' => I('order_sn'),
	   'money' => I('money'),
	   'remarks' => I('remarks'),
	   'status' => I('status'),
	  );
	  
	 return $filter;
	
	}
	
	/*获取订单号的函数
	*黄线可
	*2016.1.1
	*/
	
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