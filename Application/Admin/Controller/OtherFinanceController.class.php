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
 * 其他收付款单据
 * huang
 */
class OtherFinanceController extends AdminController {

    /**
     * 首页
     * 黄
    */
    public function index(){
        
		
		//获取所有部门的列表
		
		$bumen_list = M('bumen')->select();
		$this->assign('bumen_list',$bumen_list);
		
		

	 $this->display(); // 输出模板
	 
    }

    //其他收款单
    public function add_in(){
	
		//生成订单编号固定格式
		$order_sn = make_order_sn(12,'QTSK');
		$this->assign('order_num',$order_sn);
		
		$time_1 = date("Y-m-d H:i");
		$this->assign('time',$time_1);
		
		//获取收入科目
		$plist = M('paycategory')->where('type = 1')->select();
		$this->assign('plist',$plist);
		
		$this->display(); // 输出模板
    }
	
	//其他付款单
	public function add_out(){
	
		//生成订单编号固定格式
		$order_sn = make_order_sn(13,'QTFK');
		$this->assign('order_num',$order_sn);

		$time_1 = date("Y-m-d H:i");
		$this->assign('time',$time_1);
		
		//获取支出科目
		$plist = M('paycategory')->where('type = 2')->select();
		$this->assign('plist',$plist);
		
		$this->display(); // 输出模板   
    }
	
	//订单列表
	public function orderlist()
	{
		$filter = $this->query_array();  
		
		$this->send_out($filter);
	}

	//编辑
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
		
		$this->assign('money_list',$order['money_list']);
		
		$this->assign('assist_list',$order['assist_list']);
		
		//获取支出科目
		$type = $order['order_info']['order_type'] == 12?1:2;
		
		$plist = M('paycategory')->where('type = '.$type)->select();
		
		$this->assign('plist',$plist);
		
		
		$this->display();
	}
	
	//订单信息
	public function order_info()
	{
		$id = I('id');
		
		$order = $this->get_order_info($id);
		
		$this->assign('order_info',$order['order_info']);
		
		$this->assign('money_list',$order['money_list']);
		
		$this->assign('assist_list',$order['assist_list']);
		
		$this->display();
	}	

	//写入数据库
	public function insert()
	{
		//获取post数据
		$data = array(
			'add_time'=>strtotime(I('add_time')),
			'order_sn'=>I('order_sn'),
			'mtype'=>I('mtype',1),
			'mname'=>I('mname'),
			'mid'=>I('mid'),
			'cid'=>I('cid'),
			'cname'=>I('cname'),
			'pay_type'=>I('pay_type'),
			'card_id'=>I('card_id'),
			'money'=>I('money'),
			'purpose'=>I('purpose'),
			'total_money'=>I('total_money'),
			'remarks'=>I('remarks'),
			'order_type'=>I('order_type'),
			'id'=>I('id'),
			'order_goods_id'=>I('order_goods_id'),
			'time'=>time(),
		);
		
		//辅助信息
		$assist_array = array(
			'assist_childname'=>I('assist_childname'),
			'assist_childid'=>I('assist_childid'),
			'assist_id'=>I('assist_id'),
		);
		
		if($assist_array['assist_id'] && is_exist_null($assist_array))
		{
			$this->error('辅助项目不完整！');
		}
		//var_dump(is_exist_null($assist_array));exit;
		
		//判断数据是否正确
		$not_null = 0;
		
		foreach($data['cid'] as $k => $v)
		{
			//如果账户存在并且金额大于零至少一条成立
			if($v && $data['money'][$k] > 0 && $data['pay_type'][$k])
			{
				$not_null = 1;
				
			}elseif((!$v || !$data['pay_type'][$k] || $data['money'][$k] <=0) && !(!$v && !$data['money'][$k] && !$data['pay_type'][$k]))
			{
				$this->error('请填写完整信息！');
			}
		}
		
		if(0 == $not_null)
		{
			$this->error('数据不能为空！');
		}
		//print_r(111);exit;
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
			$order_info = M('otherfinance_order')->where('id = '.$data['id'])->find();
			if(!$order_info || 1 == $order_info['status'])
			{
		  		$this->error('此订单不存在或已过账！');
			}
		}
		elseif(M('otherfinance_order')->where("order_sn = '".$data['order_sn']."'")->find())
		{
			$this->error('订单号重复！');
		} 
		
		  
		//先写入订单表
		
		$order_fuhao = $this->order_fuhao($data['order_type']);
		  
		//获取数据
		$order_data = array(
			'id'=>$data['id'],
			'order_sn'=>$data['order_sn'],
			'mid'=>$data['mid'],
			'mtype'=>$data['mtype'],
			'mname'=>$data['mname'],
			'amount'=>$data['total_money']*$order_fuhao,
			'remarks'=>$data['remarks'],
			'add_time'=>$data['add_time'],
			'insert_time'=>$data['time'],
			'uid'=>UID,
			'order_type'=>$data['order_type'],
			'status'=>0,
		);
		
		//开启回滚机制
		M('otherfinance_order')->startTrans();

		//使用replace into 做新增或更新操作
		$order_id = M('otherfinance_order')->add($order_data,array(),true);
		 
		//插入商品表循环商品id	
		$goods_data = array();
		
		foreach($data['cid'] as $k=>$v)
		{
			if($data['money'][$k] != 0 && $v)
			{
				$goods_data[] = array(
					'id'=>$data['order_goods_id'][$k],
					'order_id'=>$order_id,
					'cid'=>$v,
					'cname'=>$data['cname'][$k],
					'card_id'=>$data['card_id'][$k],
					'pay_type'=>$data['pay_type'][$k],
					'money'=>$data['money'][$k]*$order_fuhao,
					'purpose'=>$data['purpose'][$k],
				);		
			}
		}
		
		//如果是编辑获取被删除掉的数据先
		$res_del_list = 1;
		$res_order = 1;
		$res_del_assist = 1;
		
		//辅助id
		$oa_id = I('oa_id');
		
		if($data['id'])
		{
			//获取删掉的商品的id数组
			$id_array = M('otherfinance_list')->where('order_id = '.$data['id'])->getField('id',true);
			$left_array = $data['order_goods_id']?array_diff($id_array,$data['order_goods_id']):$id_array;
			if(count($left_array) >0)
			{
				$str = implode(',',$left_array);
				
				$res_del_list = M('otherfinance_list')->delete($str); 
			}
			
			//获取删除掉的辅助项目数组
			$id_array_assist = M('otherfinance_assist')->where('order_id = '.$data['id'])->getField('id',true);
			$left_array_assist = $oa_id?array_diff($id_array_assist,$oa_id):$id_array_assist;
			if(count($left_array_assist) >0)
			{
				$str_assist = implode(',',$left_array_assist);
				
				$res_del_assist = M('otherfinance_assist')->delete($str_assist); 
			}
			
		}else
		{
			//设置订单号为已经使用
			$res_order = M('session_all_id')->where("user_id=".UID." and order_type = ".$data['order_type'])->setField('is_use',1);
		}
		
		//统一批量插入数据
		$res_list = M('otherfinance_list')->addALL($goods_data,array(),true);
		
		
		//制作辅助项目（有加无不加）
		if($assist_array['assist_id'])
		{
			foreach($assist_array['assist_id'] as $k=>$v)
			{
				$assist_data[] = array(
				'id'=>$oa_id[$k],
				'order_id'=>$order_id,
				'assist_id'=>$v,
				'assist_childid'=>$assist_array['assist_childid'][$k],
				'assist_childname'=>$assist_array['assist_childname'][$k],
				'order_type'=>$data['order_type'],
				);
			}	
			$res_assist = M('otherfinance_assist')->addALL($assist_data,array(),true);
		}
		else
		{
			$res_assist = 1;
		}
		
		if($res_list && $res_order && $res_del_list && $order_id && $res_assist && $res_del_assist)
		{
			M('otherfinance_order')->commit();//成功则提交
			$this->success('操作成功！');
		}else
		{
			M('otherfinance_order')->rollback();//不成功，则回滚
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
		$order_info = M('otherfinance_order')->where('id = '.$id)->find();
		if(!$order_info || 1 == $order_info['status'])
		{
			$this->error('此订单不存在或已经过账！');
		}
		
		//开启回滚
		M('otherfinance_order')->startTrans();
		
		$res_order = M('otherfinance_order')->where('id = '.$id)->delete();
		$res_list = M('otherfinance_list')->where('order_id = '.$id)->delete();
		$res_assist = M('otherfinance_assist')->where('order_id = '.$id)->delete();
		//var_dump($res_order);var_dump($res_list);var_dump($res_assist);
		if($res_order && $res_list && (false !== $res_assist))
		{
			M('otherfinance_order')->commit();//成功则提交
			$this->success('删除成功！');
		}else
		{
			M('otherfinance_order')->rollback();//不成功，则回滚
			$this->error('删除失败！');
		}
	
	
	}
	
	//分页
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

	//分页数据
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
		
		if($filter['mname'])
		{
			$where .= ' and mname like "%'.$filter['mname'].'%"';
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
	
		
		$list = M('otherfinance_order')->where($where)->page($filter['p'].','.$filter['page_num'])->order($limit)->select();
		
		//print_r($list);exit;
		//金额正负分化
		foreach($list as $k => $v)
		{
		  $list[$k]['amount'] = $list[$k]['amount']*$this->order_fuhao($list[$k]['order_type']);
		}
		
		
		//分页
		$count = M('otherfinance_order')->where($where)->count();
		
		$a_amount = M('otherfinance_order')->where($where)->sum('amount');
		
		$this->assign('a_amount',$a_amount);
		
		//模版
		$model_t = 'Ajax:OtherFinance:order_list';

	  
		$_GET['p'] = $filter['p'];
		
		$Page       = new \Think\Page($count,$filter['page_num']);// 实例化分页类 传入总记录数和每页显示的记录数 
		
		$show       = $Page->show_ajax($filter);// 分页显示输出
		
		$this->assign('filter',$filter);// 赋值分页输出
		
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
	   'order_type' => I('order_type'),
	   'guest_id' => I('guest_id'),
	   'bumen_id' => I('bumen_id'),
	   'name' => I('name'),
	   'begin_time' => $begin_time,
	   'end_time' => $end_time,
	   'time_type' => I('time_type','stage'),
	   'page_num'=>I('page_num',10),
	   'date_type' => I('date_type',1),
	   'mname' => I('mname'),
	   'order_sn' => I('order_sn'),
	   'amount' => I('amount'),
	   'remarks' => I('remarks'),
	   'status' => I('status'),
	  );
	  
	 return $filter;
	
	}
	
	//订单信息
	private function get_order_info($id)
	{
	 
		//获取订单的详细信息
		$order_info = M('otherfinance_order')->where(array('id'=>$id))->find();
		
		$fuhao = $this->order_fuhao($order_info['order_type']);
		
		$order_info['amount'] = $order_info['amount']*$fuhao;
		
		//获取订单商品信息
		$money_list = M('otherfinance_list')->where('order_id = '.$id)->select();
		
		foreach($money_list as $k => $v)
		{
			$cart_info = M('company')->where('id = '.$v['cid'])->find();
			$money_list[$k]['card_id'] = $cart_info['cid'];
			$money_list[$k]['money'] = $money_list[$k]['money']*$fuhao;
			//根据pay_type取出收支类型
			$money_list[$k]['type_name'] = M('paycategory')->where('id = '.$v['pay_type'])->getField('name');
		}
		
		//获取订单的辅助项目
		$assist_list = M('otherfinance_assist')->where('order_id = '.$id)->select();
		
		if($assist_list)
		{
			foreach($assist_list as $k=>$v)
			{
				$assist = M('assist')->where('id = '.$v['assist_id'])->find();
				$assist_list[$k]['assist_name'] = $assist['name'];
				$assist_list[$k]['assist_tags'] = $assist['tags'];
			}
		}
		
		return array('order_info'=>$order_info,'money_list'=>$money_list,'assist_list'=>$assist_list);
	 
	}
	
	//模板输出
	private function send_out($filter)
	{
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display();
	
	}
	
	//订单符号
	private function order_fuhao($type)
	{
		if(12 == $type)
		{
			$fuhao = 1;
		}else
		{
			$fuhao = -1;
		}
		   
		return  $fuhao;
	}
}