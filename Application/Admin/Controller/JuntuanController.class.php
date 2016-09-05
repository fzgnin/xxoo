<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huang <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;

/**
 * 部门列表
 * @author 黄
 */
class JuntuanController extends AdminController {

    /**
     * 部门管理列表首页
     * @author 黄
     */
    public function index(){
	    
		$Department = M('bumen'); // 实例化Department对象
		
		$list = $Department->field('id,pid as pId,name')->select();
		
		//加click事件
		foreach($list as $k=>$v)
		{
		  $list[$k]['open'] = 'true';
		  $list[$k]['click'] = 'table_department('.$v['id'].')';
		}
					
		$d_list = json_encode($list);
		
		$this->assign('d_list',$d_list);// 赋值数据集
		
		//取出所有的会员
		
		$filter = $this->query_array();
		
		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集

	    $this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display(); // 输出模板
    }

 
   

    public function add(){
	    if(IS_POST)
		{

			//获取数据
			$data = array(
				'id'=>'',
				'pid'=>I('pid'),
				'tname'=>I('tname'),
				'status'=>1,
				'sort'=>100,
			);
			
			//新增数据
			$res = M('juntuan')->add($data);
			
			if($res)
			{
				//写入部门表	
				$gbid = I('gbid');

				if(!empty($gbid))
				{
					$where['id'] = array('in',$gbid);
					M('bumen')->where($where)->setField('tid',$res);
				}
				
				$this->success('操作成功！',U('Juntuan/juntuanlist'));
			}
			else
			{
				$this->error('操作失败！');
			}
		}
		else
		{	
			$department = M('juntuan')->where('status = 1')->select();
			
			$department = D('Common/Tree')->toFormatTree($department,'tname','id','pid');
			
			$department = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级分类')), $department);
			
			$this->assign('department', $department);

			$this->display();
		}
	    
    }
	
	public function juntuanlist()
	{
		$filter = $this->query_array();

		$list = $this->get_list($filter);
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
		$this->display();
	
	}
	
	public function edit()
	{
	  
	  $id = I('id');
 
	  if(IS_POST)
		{

			//判断pid是否是自己或者自己的子类，如果是返回错误
			if($this->in_child_array(I('pid'),$id))
			{
				$this->error('不能把当前下级设定为上级！');
			}
			
		
			//获取数据
			$data = array(
				'pid'=>I('pid'),
				'tname'=>I('tname'),
			);
			
			//获取关联的bid
			$gbid = I('gbid');
			
			//获取删掉的id数组
			$id_array = M('bumen')->where('tid = '.$id)->getField('id',true);
			$left_array = $gbid?array_diff($id_array,$gbid):$id_array;
			
			//获取增加的id数组
			$right_array = $id_array?array_diff($gbid,$id_array):$gbid;
			
			$res_del = 1;
			$res_add = 1;
			
			//删除
			if(!empty($left_array))
			{
				$str_d = implode(',',$left_array);
				$res_del = M('bumen')->where('id in ('.$str_d.')')->setField('tid',0); 
			}
			
			//增
			if(!empty($right_array))
			{
				$str_a = implode(',',$right_array);
				$res_add = M('bumen')->where('id in ('.$str_a.')')->setField('tid',$id);
			}
			
			//新增数据
			$res = M('juntuan')->where('id = '.$id)->save($data);
			
			if(false !== $res)
			{
				$this->success('操作成功！',U('Juntuan/juntuanlist'));
			}
			else
			{
				$this->error('操作失败！');
			}
		}else
		{
			  //获取业绩划分信息
			  $info = M('juntuan')->where('id = '.$id)->find();
			  
			  $this->assign('info',$info);
			  
			  //获取树状结构
			  $department = M('juntuan')->where('status = 1')->select();
					
			  $department = D('Common/Tree')->toFormatTree($department,'tname','id','pid');
			
			  $department = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级分类')), $department);
			
			  $this->assign('department', $department);
			  
			  //获取管辖的部门信息
			  
			  $gblist = M('bumen')->field('id,bname')->where('tid = '.$id)->select();
			  
			  $this->assign('gblist',$gblist);
			  
			  $this->display();
		}	
	}
	
	public function change_status()
	{
		$id = I('id');	
		
		$status = 1 == I('status')?0:1;
		
		if(M('juntuan')->where('id = '.$id)->setField('status',$status))
		{
			$this->success('操作成功！');
		}
		else
		{
			$this->error('操作失败！');
		}	
	}
	
	public function ajax_query()
	{
	
	  $filter = $this->query_array();

      $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出

	  $res_str = $this->fetch('Ajax:Juntuan:juntuan_list'); // 输出模板
	 
	  $data['info'] = $res_str;
	 
	  $data['success'] = 1;
	 
	  $this->ajaxReturn($data);
	
	}
	
	//获取顾客列表封装函数
	
	public function get_list($filter)
	{
	  if($filter['order_by'])
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }  
	  
	  //部门筛选
	  if($filter['department_id'])
	  {
	   $where = 'id = '.$filter['id'];
	  }else
	  {
	   $where = '1';
	  }
	  
	  if($filter['keywords'])
	  {
	    $where .= " and tname like '%".$filter['keywords']."%'";
	  }
	  
  
	  $list = M('juntuan')->where($where)->order($limit)->select();
	  
	  foreach($list as $k=>$v)
	  {
	  	$list[$k]['pname'] = M('juntuan')->where('id = '.$v['pid'])->getField('tname');
	  }
	  
	  
	  $list = D('Common/Tree')->toFormatTree($list,'tname','id','pid');
	  
	  //print_r($list);
	   
	   //分页
	  $count      = M('juntuan')->where($where)->count();
	  
	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show);
	
	}
	
	
	//定义查询变量
	private function query_array()
	{
	  $filter = array(
	   'p' => I('p',1),
	   'order_by'=> I('order_by'),
	   'sort_by'=> I('sort_by','ASC'),
	   'department_id' => I('department_id'),
	   'keywords' => I('keywords'),
	  );
	  
	 return $filter;
	
	}
	
	private function in_child_array($pid,$id)
	{
		//取出id下所有子id
		$arr[] = $id;
		
		$id_array = array_merge($arr,$this->get_child_array($arr));
		
		if(in_array($pid,$id_array))
		{
			return true;
		}else
		{
			return false;
		}
	
	}
	
	private function get_child_array($array = array())
	{
		
		$where = 'pid in ('.implode(',',$array).')';
		
		$where .= ' and status = 1';

		$new_array = M('juntuan')->where($where)->getField('id',true);
		
		if($new_array)
		{
			$new_array = array_merge($new_array,$this->get_child_array($new_array));
			
		}else
		{
			$new_array = array();
		}
		
		return $new_array;
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