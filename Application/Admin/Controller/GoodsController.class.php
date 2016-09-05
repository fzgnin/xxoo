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
class GoodsController extends AdminController {

    /**
     * 商品管理
     * 黄
     */
    public function index(){
        //获取商品列表
		
	 //获取品牌列表供筛选使用
	 $brand_list = M('brand')->field('id,name')->select();
	 
	 //设置搜索条件
	 
	 $filter = $this->query_array();
 
	 $list = $this->goods_list($filter);
	 
	 $this->assign('brand_list',$brand_list);// 赋值品牌集合
	 
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
 
    }
	
	
	/**
	*   商品列表
	*   黄线可
	**/
	
	public function goodslist()
	{
	 //获取品牌列表供筛选使用
	 $brand_list = M('brand')->field('id,name')->select();
	 
	 //设置搜索条件
	 
	 $filter = $this->query_array();
 
	 $list = $this->goods_list($filter);
	 
	 $this->assign('brand_list',$brand_list);// 赋值品牌集合
	 
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $this->display(); // 输出模板
	
	}
	
	

    //新增商品

    public function add(){
	
	//获取品牌信息
	$brand_list = M('brand')->field('id,name')->select();
	$this->assign('brand_list',$brand_list);
	
	//获取规格信息
	$guige = M('goods_guige')->find();
	$guige = explode(',',$guige['guige']);
	$this->assign('guige',$guige);
	
    $this->display(); // 输出模板
    }
	
	
	public function edit()
	{
	  //获取id
	  
	  $id = I('id');
	  
	  if(!$id)
	  {
	   $this->error('id传输错误');
	  }
	  
	  //获取品牌列表
	  
	  $brand_list = M('brand')->field('id,name')->select();
	
	  $this->assign('brand_list',$brand_list);
	  
	  //获取规格信息
		$guige = M('goods_guige')->find();
		$guige = explode(',',$guige['guige']);
		$this->assign('guige',$guige);
	  
	  
	  //获取商品信息
	  
	  $goods_info = M('goods')->where(array('id'=>$id))->find();
	  
	  
	  $this->assign('info',$goods_info);
	   
	  $this->display('Ajax:edit_goods');
	  
	}
	
	
	
	//插入商品到数据库
	public function insert()
	{
	  //判断编号是否重复
	  
	  $code = I('code');
	  
	  if(M('goods')->where(array('code'=>$code))->find())
	  {
	  $this->error('商品编号已经存在！');
	  }
	  
	  //获取商品信息数组
	  
	  $data = array(
	    'goodsname'=>I('goodsname'),
		'brand_id'=>I('brand_id'),
		'code'=>I('code'),
		'calculation'=>I('calculation'),
		'price'=>I('price'),
		'cost_one'=>I('cost_one'),
		'cost_two'=>I('cost_two'),
		'format'=>I('format'),
		'remarks'=>I('remarks'), 
	  );
	  
	  if(M('goods')->add($data))
	  {
	    $this->success('插入商品成功！');
	  }else
	  {
	    $this->error('插入商品失败！');
	  }
	 
	}
	
	public function update()
	{
	  //获取商品的信息
	  $data = array(
	    'id'=>I('id'),
	    'goodsname'=>I('goodsname'),
		'brand_id'=>I('brand_id'),
		'code'=>I('code'),
		'calculation'=>I('calculation'),
		'price'=>I('price'),
		'cost_one'=>I('cost_one'),
		'cost_two'=>I('cost_two'),
		'format'=>I('format'),
		'remarks'=>I('remarks'), 
	  );
	  
	  if(!$data['id'])
	  {
	    $this->error('商品id传输错误！');
	  }

	  if(M('goods')->where(" code = '".$data['code']."' and id != '".$data['id']."'")->select())
	  {
	  $this->error('商品编号已经存在！');
	  }
	  
	  
	  if(M('goods')->where(array('id'=>$data['id']))->save($data) !== false)
	  {
	    $this->success('修改成功！');
	  }else
	  {
	    $this->error('修改失败！');
	  }
	  
	  print_r($data);exit;
	  
	  
	  
	}
	
	//商品删除
	public function delete_goods()
	{
	  //获取id参数
	  $id = I('id');
	  
	  //其他操作暂时未想到  
	  
	  //直接删除商品
	  if(M('goods')->where('id = '.$id)->delete())
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
 
	 $filter = $this->query_array();

     $list = $this->goods_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $res_str = $this->fetch('Ajax:goods_list'); // 输出模板
	 
	 $data['info'] = $res_str;
	 
	 $data['success'] = 1;
	 
	 $this->ajaxReturn($data);
	
	}
	
	//获取顾客列表封装函数
	
	public function goods_list($filter)
	{
	  if($filter['order_by'])
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }  
	  
	  //品牌筛选
	  if($filter['brand'])
	  {
	   $where = 'brand_id = '.$filter['brand'];
	  }else
	  {
	   $where = '';
	  }
	  
	  $list = M('goods')->where($where)->page($filter['p'].',10')->order($limit)->select();
	   
	   //分页
	  $count      = M('goods')->where($where)->count();
	  
	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show);
	
	}
	
	
	public function addguige()
	{
		$guige = M('goods_guige')->find();
		
		$this->assign('guige',$guige);
		
		$this->display();
	
	}

	
	//商品规格
	public function insertguige()
	{
		$guige = I('guige');
		
		//先查询有没有，有保存无新增
		$goods_guige = M('goods_guige')->find();
		
		if($goods_guige['id'])
		{
			$data = array(
				'id'=>$goods_guige['id'],
				'guige'=>$guige,
			);
			$res = M('goods_guige')->save($data);
		}else
		{
			$data = array(
				'guige'=>$guige,
			);
			$res = M('goods_guige')->add($data);
		}
		
		if(false !== $res)
		{
			$this->success('操作成功！');
		}else
		{
			$this->error('操作失败！');
		}
		
		
	
	}
	
	//定义查询变量
	private function query_array()
	{
	  $filter = array(
	   'p' => I('p',1),
	   'order_by'=> I('order_by'),
	   'sort_by'=> I('sort_by','ASC'),
	   'brand' => I('brand'),
	  );
	  
	 return $filter;
	
	}


}