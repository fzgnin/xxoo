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
 * 客户组
 * huang
 */
class GuestGroupController extends AdminController {

    /**
     * 收支类别列表首页
     * 黄
     */
	public function index(){
	
	
		$filter = $this->query_array();
		
		$list = $this->guest_list($filter);
		
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
	
	}
	
	
	/**
	*   客户组列表
	*   黄线可
	**/
	
	public function guestgrouplist()
	{
		//条件数组
		$filter = $this->query_array();
		
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
	
	}
	

	public function add(){

		$this->display(); // 输出模板
	
	}

	
	//写入数据库
    Public  function  insert(){

		$data = array( //获取数据
			'id' =>I('id'),
			'name' =>trim(I('groupname')),
			'bid' =>I('bumen_id'),
		);

		//名字不能为空
		if(!$data['name'] || !$data['bid'])
		{
			$this->error('请填写完整信息！');
		}
	
		//写入
		if (M('guestgroup') ->add($data,array(),true))
		{  
			$this ->success('操作成功',__APP__.'Admin/GuestGroup/guestgrouplist'); 
		}
		else
		{
			$this->error('操作失败');
		}
    }
	
	public function delete()
	{
	  $id = I('id');

	  //检查是否有客户
	  
	  if(M('gid_group')->where('group_id = '.$id)->find())
	  {
	  	$this->error('请先删除该客户组下的客户！');
	  } 

	  if(M('guestgroup')->where('id = '.$id)->delete())
	  {
	    $this->success('删除成功！');
		
	  }else
	  {
	    $this->error('删除失败！');
 	  }
	
	
	}

	public function show_guestgroup()
	{
		$id = I('id');
		
		//取出客户组所属客户
		$guest_list = M('gid_group')->alias('gg')->field('g.id,g.guestname,g.address,g.province,g.manager')->join('left join `onethink_guest` g on gg.gid = g.id')->where('group_id = '.$id)->select();
		//print_r(M('gid_group')->getLastSql());
		$this->assign('guest_list',$guest_list);
		
		$this->assign('id',$id);
		
		//取出bid
		$bid = M('guestgroup')->where('id = '.$id)->getField('bid');
		
		$this->assign('bid',$bid);
		
		$this->display();
	}
	
	public function guestgroup_insert()
	{
		$id_arr = I('id_arr');
		$id = I('id');
		
		$id_arr = explode(',',$id_arr);
		
		//取出bid
		$bid = M('guestgroup')->where('id = '.$id)->getField('bid');
		
		//取出已经有的（同一部门不允许有两个相同的客户）
		$id_now = M('gid_group')->where('bid = '.$bid)->getField('gid',true);
		
		$id_arr = $id_now?array_diff($id_arr,$id_now):$id_arr;
		
		foreach($id_arr as $k=>$v)
		{
			$data[] = array(
				'group_id'=>$id,
				'gid'=>$v,
				'bid'=>$bid
			);
		}
		
		$res = M('gid_group')->addAll($data,array(),true);
		
		if($res)
		{
			$this->success('操作成功！');
		}else
		{
			$this->error('操作失败！（此客户可能已经被其他组占用）');
		}
	}
	
	public function guestgroup_delete()
	{
		$id = I('id');
		$gid = I('gid');
		
		$res = M('gid_group')->where('group_id = '.$id.' and gid = '.$gid)->delete();
		
		if($res)
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
		
		if($filter['keywords'])
		{
			$where = "name like '%".$filter['keywords']."%'";
		}else
		{
			$where = 1;
		}
		
		$list = M('guestgroup')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		foreach($list as $k=>$v)
		{
			$list[$k]['bname'] = M('bumen')->where('id = '.$v['bid'])->getField('bname');
		}
		
		//分页
		$count      = M('guestgroup')->where($where)->count();
		
		$model_t = 'Ajax:GuestGroup:guestgroup_list';

	  
		$_GET['p'] = $filter['p'];
		
		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
		
		$show       = $Page->show_ajax($filter);// 分页显示输出
		
		$this->assign('filter',$filter);// 赋值数据集
		
		return array('list' => $list ,'show'=> $show,'model_t' => $model_t);
	
	}
	
	private function query_array()
	{
		 
	 $filter = array(
	 'p' => I('p',1),
	 'sid' => I('sid'),
	 'type' => I('type'),
	 'order_by'=> I('order_by'),
	 'sort_by'=> I('sort_by','ASC'),
	 'keywords' => I('keywords'),
	 );
	  
	 return $filter;
	
	}

   

}