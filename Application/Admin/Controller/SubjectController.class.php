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
 * 后台收支类别
 * huang
 */
class SubjectController extends AdminController {

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
	*   收支类别列表
	*   黄线可
	**/
	
	public function subjectlist()
	{
		//条件数组
		$filter = $this->query_array();
		
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
	
	}
	

	public function add(){
		
		//取出辅助项目
		$assist = M('assist')->select();
		
		$this->assign('assist',$assist);
		
		$this->display(); // 输出模板
	
	}
	
	
	//编辑收支类别
	Public function edit(){

		$id=I('id');
		
		$info = M('subject')->where('id = '.$id)->find();
		
		$this->assign('info',$info);

		//取出辅助项目
		$assist = M('assist')->select();
		
		foreach($assist as $k=>$v)
		{
			if(in_array($v['id'],explode(',',$info['assist_id'])))
			{
				$assist[$k]['checked'] = 1;
			}
		}
		
		$this->assign('assist',$assist);
		
		$this->display();
	}
	
	
	
	//写入数据库
    Public  function  insert(){
	
		$assist_id = implode(',',I('assist_id'));
		
		$data = array( //获取数据
			'id' =>I('id'),
			'name' =>trim(I('name')),
			'assist_id' =>$assist_id?$assist_id:'',
		);
		
		//名字不能为空
		if(!$data['name'])
		{
			$this->error('名字不能为空！');
		}
		
		//判断是否使用
		if($data['id'])
		{
			if(M('otherfinance_order')->where('mtype = 4 and mid = '.$data['id'])->find())
			{
				$this->error('该项目已经被使用，暂无法编辑！');
			}
		}
	
		//写入
		if (M('subject') ->add($data,array(),true))
		{  
			$this ->success('操作成功',__APP__.'Admin/Subject/subjectlist'); 
		}
		else
		{
			$this->error('操作失败');
		}
    }
	
	public function delete()
	{
	  $id = I('id');

	  //检查是否被使用
	  
	  if(M('otherfinance_order')->where('mtype =  4  and mid = '.$id)->find())
	  {
	  	$this->error('该类别已经被使用，暂不能被删除！');
	  } 

	  if(M('subject')->where('id = '.$id)->delete())
	  {
	    $this->success('删除成功！');
		
	  }else
	  {
	    $this->error('删除失败！');
 	  }
	
	
	}
	
	
	//资金往来情况
	public function show_subject()
	{
		$id = I('id');
		
		//根据项目id取出往来费用列表
		$slist = M('otherfinance_order')->where('mtype = 4 and mid = '.$id)->select();
		
		foreach($slist as $k=>$v)
		{
			$slist[$k]['order_type'] = get_ordertype($v['order_type']);
			$slist[$k]['status'] = 1 == $slist[$k]['status']?'已过帐':'未过帐';
		}
		
		$this->ajaxReturn($slist);

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
		
		$list = M('subject')->page($filter['p'].',10')->order($limit)->select();
		
		foreach($list as $k=>$v)
		{
			$list[$k]['assist'] = M('assist')->where('id in ('.$v['assist_id'].')')->select();
		}
		
		
		//分页
		$count      = M('subject')->where($where)->count();
		
		$model_t = 'Ajax:Subject:subject_list';

	  
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