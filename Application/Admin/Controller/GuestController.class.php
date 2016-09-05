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
class GuestController extends AdminController {

    /**
     * 客户管理
     * 黄
     */
    public function index(){
	
	 //初始化条件数组
	 $filter = $this->query_array();
	 
	 $list = $this->guest_list($filter);

	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	 
    }
	
	
	/**
	*   客户列表
	*   黄线可
	**/
	
	public function guestlist()
	{
	 //条件数组
	 $filter = $this->query_array();
	 
	 
	 //如果有部门取出部门名字
	 $bid = I('bumen_id');
	 
	 if($bid)
	 {
	 	$bname = M('bumen')->where('id = '.$bid)->getField('bname');
		$this->assign('bname',$bname);
	 }
	
	 $list = $this->guest_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	
	}

    public function add(){
		
		//黄新增输出品牌信息到模版
		
		$brand_list = M('brand')->field('id,name')->select();
		
		$this->assign('brand_list',$brand_list);
		
		$branddiscount = M('branddiscount')->where(array('guest_id'=>$id))->select();
		
		$this->assign('branddiscount',$branddiscount);
		
		$this->display(); // 输出模板
    }
	
	
	//修改客户信息
	Public function changeguest(){
		$id=I('id');
		$info=M('guest')->where(array('id'=>$id))->find();
		
		//获取客户关联部门列表
		$gblist = M('bid_gid')->alias('bg')->join(' left join `onethink_bumen` b on bg.bid = b.id')->where('bg.gid = '.$id)->select();
		$this->assign('gblist',$gblist);
		
		//黄新增输出品牌信息到模版
		$brand_list = M('brand')->field('id,name')->select();
		$this->assign('brand_list',$brand_list);
		
		$branddiscount = M('branddiscount')->where(array('guest_id'=>$id))->select();
		$this->assign('branddiscount',$branddiscount);
		
		//如果有服务人员，取出服务人员
		if($info['fid'])
		{
			$info['fname'] = M('user')->where('id = '.$info['fid'])->getField('name');
		}
		
		//黄新增输出品牌信息到模版
		$this->assign('info',$info); 
		$this->display('Ajax:edit_guest');
	}
	
	
	
	//添加客户数据整理里写入数据库
    Public  function  getinfo(){
		 
		 $gbid = I('gbid');
		 
		 $data = array( //获取数据
			'guestname' =>trim(I('guestname')),
			'province' => I('province'),
			'address'  => trim(I('address')),
			'manager' =>  trim(I('manager')),
			'phone'   =>  trim(I('phone')),
			'tphone'  =>  trim(I('tphone')),
			'remark'  =>  trim(I('remark')),
			'stus'   =>   empty($gbid)?0:1,
			'writer' =>   I('writer'),
			'intime' =>time(),
			'fid' =>I('user_id'),
			);
	
		 //黄修改插入品牌折扣
	
		 $res_id = M('guest') ->add($data);
	
		 if ($data['guestname'] && $res_id) {
		  //插入品牌折扣表
		  $brand_id = I('brand_id');
		  $discount_value_k = I('discount_value_k');//开单折扣
		  $discount_value_g = I('discount_value_g');//过账折扣
	
		  //去重复
		  $new_array = array();
	
		  if (count($brand_id) > 0) {
			$brand_id = array_unique($brand_id);
	
			foreach ($brand_id as $k => $v) {
			  if ($v && $discount_value_k[$k] && $discount_value_g[$k]) {
				$new_array[] = array(
			  'guest_id'=>$res_id,
			  'brand_id'=>$brand_id[$k],
			  'discount_k'=>$discount_value_k[$k],
			  'discount_g'=>$discount_value_g[$k]
				);
			  };
			}
	
		  }
	
		  if (count($new_array) > 0) {
			 foreach ($new_array as $k => $v) {
			  if (!M('branddiscount') ->add($v)) {
				$this->error('添加品牌折扣失败');
			  }
			} 
		  }
 
		 
		 $data_gb = array();
		 if($gbid)
		 {
			 foreach($gbid as $v)
			 {
				$data_gb[] = array(
				'bid'=>$v,
				'gid'=>$res_id,
				);
			 }
			 
			 if(!M('bid_gid')->addAll($data_gb))
			  {
				$this->error('添加合作部门失败！');
			  }
		 }

		  
		  $this -> success('添加成功'); 
		   
		 }
	
		 else{$this->error('添加失败');}
    }
	
	
	
	//获取修改资料并修改客户资料
	Public function getchangeguest(){
		$id=I('id');
		
		
		//黄新增获取商户品牌关联折扣存入数据库
		
		//获取post品牌折扣的数组长度
		
		$branddiscount_id = I('branddiscount_id');
		
		$brand_id = I('brand_id');
		
		$discount_value_k = I('discount_value_k');
		
		$discount_value_g = I('discount_value_g');
		
		$p_length = count($brand_id);
		
		//判断客户是否有用户组
		if(M('gid_group')->where('gid = '.$id)->find())
		{
			$this->error('该客户已经有所属客户组，请先删除后再进行操作！');
		}
		
		//过滤数据并且写入数据库
		
		if ($p_length > 0) {
		  for ($i=0; $i < $p_length; $i++) { 
		
			if ($brand_id[$i]) {
				  $dis_data = array(
				  'guest_id'=>$id,
				  'brand_id'=>$brand_id[$i],
				  'discount_k'=>$discount_value_k[$i],
				  'discount_g'=>$discount_value_g[$i]
					);
		
				//修改
		
				if (!empty($discount_value_k[$i]) && !empty($discount_value_g[$i])) {
					if ($branddiscount_id[$i]) {
		
					  //更新 
					 
						  M('branddiscount') ->where(array('id'=>$branddiscount_id[$i]))->save($dis_data);
						
				  }else
				  {
					//新增
					//判断是否有品牌
					if (M('branddiscount') ->where(array('guest_id'=>$id,'brand_id'=>$brand_id[$i]))->find()) {
						M('branddiscount') ->where(array('guest_id'=>$id,'brand_id'=>$brand_id[$i]))->save($dis_data);
					}else
					{
					  M('branddiscount') ->add($dis_data);
					}
		
				  }
				}else
				{
		
				  if ($branddiscount_id[$i] && empty($discount_value_k[$i])) {
					M('branddiscount') ->where(array('id'=>$branddiscount_id[$i]))->delete();
				  }
				}
				
			}
			
		  }
		}
			 $gbid = I('gbid');
			 $data = array(
				'guestname' =>trim(I('guestname')),
				'province' => I('province'),
				'address'  => trim(I('address')),
				'manager' =>  trim(I('manager')),
				'phone'   =>  trim(I('phone')),
				'tphone'  =>  trim(I('tphone')),
				'remark'  =>  trim(I('remark')),
				'uptime' =>time(),
				'stus' => empty($gbid)?0:I('stus'),
				'fid' =>I('user_id'),
			  );
			  
			 //修改bid_gid关联表
			 
			 //查询是否有关联然后删除
			 M('bid_gid')->where('gid = '.$id)->delete();
			 
			 $bd_array = array();
			 
			 if($gbid)
			 {
				foreach($gbid as $k=>$v)
				{
					$bd_array[] = array(
					 'bid'=>$v,
					 'gid'=>$id,
					);
				}
			 }
		
			 if(M('guest') ->where(array('id'=>$id))->save($data)){
			 M('bid_gid')->addAll($bd_array);
				   $this -> success('修改成功','guestlist');       
			 }else{$this->error('修改失败');}
		
	}

	
	public function ajax_query()
	{
	 
	 //条件数组
	 
	 $filter = $this->query_array();

     $list = $this->guest_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $res_str = $this->fetch('Ajax:guest_list'); // 输出模板
	 
	 $data['info'] = $res_str;
	 
	 $data['success'] = 1;
	 
	 $this->ajaxReturn($data);
	
	}
	
	
	
	//设置已合作客户为未合作客户

	 Public function changegueststyle(){
	 
	 $id = I('id');
	 $stus = I('stus');
	 
	 //先判断当前客户关联的部门是否超过用户权限
	 //客户部门关联
	 $blist = M('bid_gid')->where('gid = '.$id)->getField('bid',true);
	 $ulist = M('uid_bid')->where('uid = '.UID)->getField('bid',true);
	 
	 $arre = array_diff($blist,$ulist);
	 
	 
	 //1为合作2为休眠0为未合作，未合作把部门和军团的标识去掉
	 
	 if($stus == 0)
	 { 
		  if(count($arre) > 0)
		  {
				$this->error('该客户所合作的其他部门您暂时没有权限操作，请联系客服主管！');
		  }
		  
		  //判断客户是否有用户组
		  if(M('gid_group')->where('gid = '.$id)->find())
		  {
		  	$this->error('该客户已经有所属客户组，请先删除后再进行操作！');
		  }
		  
		  $data = array(
		  'stus' => $stus,
		  );
		  //删除bid_gid关联
		  M('bid_gid')->where('gid = '.$id)->delete();
	 }
	 
	  //如果要设为休眠客户，判断是否有所属部门，有不用管没有的话弹出选择部门框
	 
	 elseif(2 == $stus)
	 {
		   
		   if(count($arre) > 0)
		   {
				$this->error('该客户所合作的其他部门您暂时没有权限操作，请联系客服主管！');
		   }
		   
		   //判断客户是否有用户组
			if(M('gid_group')->where('gid = '.$id)->find())
			{
				$this->error('该客户已经有所属客户组，请先删除后再进行操作！');
			}
		   
		   $bid_arr = I('bid_arr');   
		   
		   if(!$blist && !$bid_arr)
		   {
			 $this->error(1);
		   }
		   else
		   {
			   //获取bid数组
			   
			   if($bid_arr)
			   {
				   foreach($bid_arr as $k=>$v)
				   {
						$data_bg[] = array(
						'bid'=>$v,
						'gid'=>$id,
						);
				   }
				   
				   M('bid_gid')->addAll($data_bg); 
			   }

			   $data = array(
				  'stus' => $stus,
				);   
		   }
	 
	 }else
	 {
		 $data = array(
		  'stus' => $stus,
		  );
	 }
	
	 
	 
		 if(M('guest') ->where(array('id'=>$id))->save($data)){
			   $this ->success('修改成功');       
		 }else{$this->error('修改失败');}
	
	 }
 
	public function authority()
	{
	  $branddiscount_list = D('Department')->getTree(0,'id,name,parent_id');
	
	
	  $this->display(); // 输出模板
	}
	
	
	//获取顾客列表封装函数
	
	public function guest_list($filter)
	{
	  if($filter['order_by'])
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }  
	  
	  $where = "g.stus =".$filter['stus'];

	  if($filter['keywords'])
	  {
	    $where .= " and g.guestname like '%".$filter['keywords']."%'";
	  }
	  
	  //print_r($where);
	  
	  if($filter['stus'])
	  {
		  if($filter['bumen_id'])
		  {
			$where .= ' and bg.bid = '.$filter['bumen_id'];
		  }
		  
		  $where .= ' and ub.uid = '.UID;
		  
		  $list = M('uid_bid')->alias('ub')->field('g.id,g.guestname,g.province,g.manager,g.phone,g.stus,g.fid,count(distinct bd.brand_id) as bnum')
					->join('left join `onethink_bid_gid` bg on ub.bid = bg.bid')
					->join('left join `onethink_guest` g on bg.gid = g.id')
					->join('left join `onethink_branddiscount` bd ON g.id = bd.guest_id')
					->where($where)->group('bg.gid')->page($filter['p'].',10')->order($limit)->select();
	
		  //print_r($list);exit;
		  
		  //取出部门列表
		  if(count($list) > 0)
		   {
			 foreach($list as $k=>$v)
			 {
			  $list[$k]['blist'] = M('bid_gid')->alias('bg')->join('left join `onethink_bumen` b on bg.bid = b.id')->where(array('bg.gid' => $v['id']))->select();
			  if($v['fid'])
			  {
			  	$list[$k]['fname'] = M('user')->where('id = '.$v['fid'])->getField('name');
			  }
			  
			 }
		   }
		   
		   //分页
		  $count      = M('uid_bid')->alias('ub')
						->join('left join `onethink_bid_gid` bg on ub.bid = bg.bid')
						->join('left join `onethink_guest` g on bg.gid = g.id')
						->join('left join `onethink_branddiscount` bd ON g.id = bd.guest_id')
						->where($where)->count('distinct bg.gid');
	  }else
	  {
	  		$list = M('guest')->alias('g')->field('g.*,count(distinct bd.brand_id) as bnum')
					->join('left join `onethink_branddiscount` bd on g.id = bd.guest_id')
					->where($where)->group('g.id')->page($filter['p'].',10')->order($limit)->select();
					
			//取出部门列表
			  if(count($list) > 0)
			   {
				 foreach($list as $k=>$v)
				 {
				  $list[$k]['blist'] = M('bid_gid')->alias('bg')->join('left join `onethink_bumen` b on bg.bid = b.id')->where(array('bg.gid' => $v['id']))->select();
				  if($v['fid'])
				  {
					$list[$k]['fname'] = M('user')->where('id = '.$v['fid'])->getField('name');
				  }
				 }
			   }
			   
			$count  = M('guest')->alias('g')->join('left join `onethink_branddiscount` bd on g.id = bd.guest_id')->where($where)->count('distinct g.id');
					
					
	  
	  }
	  
	  
	  
	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);// 赋值查询条件
	  
	  return array('list' => $list ,'show'=> $show);
	
	}
	
	private function query_array()
	{
		 
	 $filter = array(
	 'p' => I('p',1),
	 'stus' => I('stus',1),
	 'order_by'=> I('order_by'),
	 'sort_by'=> I('sort_by','ASC'),
	 'keywords' => I('keywords'),
	 'bumen_id' => I('bumen_id'),
	 );
	  
	 return $filter;
	
	}
	
	
	public function select_uidbid()
	{
		$gid = I('gid');
		
		//取出该用户的关联bid
		$ublist = M('uid_bid')->where('uid = '.UID)->getField('bid',true);
		
		//print_r($ublist);exit;
		
		$gblist = M('bid_gid')->where('gid = '.$gid)->select();
		
		//取出所有部门列表，然后针对部门判断是否有权限和是否被选中
		$blist = M('bumen')->field('id,tid,bid,bname,pid')->where('status = 1')->select();
		
		$blist = D('Common/Tree')->toFormatTree($blist,'bname','id','pid');
		
		foreach($blist as $k=>$v)
		{
			//是否有权限
			$blist[$k]['show'] = in_array($v['id'], $ublist)?1:0;
			//是否被选中
			$blist[$k]['gbid'] = $this->inarray_b($v['id'], $gblist);	
		}

		$this->ajaxreturn($blist);	
	}

	
	//判断bid是否存在函数
	private function inarray_b($a,$array)
	{
		foreach($array as $v)
		{
			if($a == $v['bid'])
			{
				return $v['id'];
			}
		}
		
		return '';
	}


   

}