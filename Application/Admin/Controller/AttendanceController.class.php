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
 * 考勤系统
 * @author 黄
 */
class AttendanceController extends AdminController {

    /**
     * 考勤系统首页
     * @author 黄
     */
    public function index(){
	    
		$Department = M('bumen'); // 实例化对象
		
		$list = $Department->field('id,pid as pId,name')->where('status = 1')->select();
		
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
		
		$this->send_out($filter);
    }
	
	//新增员工
	public function add()
	{
		//取出部门信息
		$did_info = M('bumen')->where('status = 1')->select();
		
		$did_info = D('Common/Tree')->toFormatTree($did_info,'bname','id','pid');
		
		//赋值
		$this->assign('did_info',$did_info);
		
		//取出职位信息
		$pid_info = M('position')->field('id,name')->where('status = 1')->select();
		
		$this->assign('pid_info',$pid_info);
		
		$this->display();
	}
	
	//员工列表
	public function userlist()
	{
 
	   //设置查询条件
	   $filter = $this->query_array();	
	   $filter['type'] = 'userlist';
	   
	   //如果有部门传进来，获取部门名称
	   $bid = I('department_id');
	   
	   if($bid)
	   {
	   		$bname = M('bumen')->where('id = '.$bid)->getField('bname');
			$this->assign('bname',$bname);
	   }
	   
	   $this->send_out($filter);
	}
	
	
	//签到列表
	public function signlist()
	{
 
	   $filter = $this->query_array();	
	   
	   $filter['type'] = 'signlist';
	   
	   $this->send_out($filter);
	}
	
	//按人员查看签到
	public function usignlist()
	{
	   $filter = $this->query_array();
	   
	   $filter['type'] = 'usignlist';
	   
	   $this->send_out($filter);
	}
	
	//未签到列表
	public function nosignlist()
	{
	  $filter = $this->query_array();	
	   
	  $filter['type'] = 'nosignlist';
	   
	  $this->send_out($filter);

	}
	
	//签到列表信息
	public function signinfo_list()
	{
	  $filter = $this->query_array();	
	   
	  $filter['type'] = 'signinfo_list';
	   
	  $this->send_out($filter);
	
	}
	
	
	public function attendlist()
	{
	
	  $filter = $this->query_array();	
	   
	  $filter['type'] = 'attendlist';
	   
	  $this->send_out($filter);
	
	}
	
	//获取考勤信息
	public function get_kaoqin()
	{
	  $uid = I('uid');
	  
	  $data = I('data');
	  
	  $return = array();
 
	  //获取本月的考勤业绩
	  $year = substr($data,0,4);
	  $month = intval(substr($data,5));
		
	  $return['list'] = M('user_kaoqin')->field('day,morning,afternoon')->where('uid = '.$uid.' and year = '.$year.' and month = '.$month)->select();
	  
	  $return['user'] = M('user')->where('id = '.$uid)->find();
	  
	  $this->ajaxReturn($return);
	
	}
	
	//获取用户的签到和请假信息
	public function get_day_info()
	{
	  $day = I('day');
	  
	  $uid = I('uid');
	  
	  $return = array();
	  
	  //根据day调出日期
	  $sx = substr($day,strlen($day)-1,1);
	  
	  $day = substr($day,0,strlen($day)-2);
	  
	  $starttime = strtotime(date($day));
	  
	  $endtime = $starttime + 60*60*24;
	  
	  $return['list'] = M('sigin')->where('uid = '.$uid.' and s_time <'.$endtime.' and s_time >= '.$starttime)->select();
	  
	  foreach($return['list'] as  $k => $v)
	  {
	    $return['list'][$k]['s_time'] = $return['list'][$k]['s_time']?date('Y-m-d H:i:s',$return['list'][$k]['s_time']):'无';
		
		$return['list'][$k]['t_time'] = $return['list'][$k]['t_time']?date('Y-m-d H:i:s',$return['list'][$k]['t_time']):'无';
		
		$return['list'][$k]['plan'] = $return['list'][$k]['plan']?$return['list'][$k]['plan']:'无';
		
		$return['list'][$k]['work_z'] = $return['list'][$k]['work_z']?$return['list'][$k]['work_z']:'无';
		
	  }
	  
	  $return['shenpi'] = M('shenpi')->where('uid = '.$uid.' and status = 2')->select();
	  
	  $return['sx'] = $sx;
	  
	  $this->ajaxReturn($return);
	}
	
	
	public function set_user_kaoqin()
	{
	   $uid = I('uid');
	   
	   $type = I('type');
	   
	   $kq_time = I('kq_time');
	   
	   //对时间进行分解
	   
	   $year = substr($kq_time,0,4);
	   
	   $month = substr($kq_time,5,2);
	   
	   $day = substr($kq_time,8,strlen($kq_time)-10);
	   
	   $sx = substr($kq_time,-1);
	   
	   $return = array();

	   
	   
	   //开始写如数据库
	   
	   $where['uid'] = $uid;
	   
	   $where['year'] = $year;
	   
	   $where['month'] = $month;
	   
	   $where['day'] = $day;
	   
	   $info = M('user_kaoqin')->where($where)->find();
	   
	   if(0 == $sx)
	   {
		   $data = array(
		   'id'=>isset($info['id'])?$info['id']:'',
		   'uid'=>$uid,
		   'year'=>$year,
		   'month'=>$month,
		   'day'=>$day,
		   'morning'=>$type,
		   'afternoon'=>isset($info['afternoon'])?$info['afternoon']:'',
		   );
	   }else
	   {
	       $data = array(
		   'id'=>isset($info['id'])?$info['id']:'',
		   'uid'=>$uid,
		   'year'=>$year,
		   'month'=>$month,
		   'day'=>$day,
		   'morning'=>isset($info['morning'])?$info['morning']:'',
		   'afternoon'=>$type,
		   );
	   }
	   
	   if(M('user_kaoqin')->add($data,array(),true))
	   {
	      $return['success'] = 1;
	   }else
	   {
	      $return['success'] = 0;
	   }
	   
	   $this->ajaxReturn($return);
	
	
	}
	
	public function ajax_query()
	{
	
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
	  
	  
	  if('signlist' == $filter['type'])
	  {
	    //获取签到的信息
		
		$where = '1';
		
		$limit = 'tt DESC';
		
		if(1 == $filter['sign_type'])
		{
		  $s_t = 't_time';
		}else
		{
		  $s_t = 's_time';
		}
		
		$list = M('sigin')->field('count(distinct uid) as num ,FROM_UNIXTIME('.$s_t.', "%Y-%m-%d") as tt')->where($where)->group('tt')->page($filter['p'].',10')->order($limit)->select();
		
		$ucount = M('user')->where('onjob = 1')->count();
		
		foreach($list as $k=>$v)
		{
		  $list[$k]['w_num'] = $ucount-$v['num'];
		  $list[$k]['qdl'] = round($v['num']/$ucount,2);
		}
	  
	    $count      = M('sigin')->field("count(distinct FROM_UNIXTIME(".$s_t.", '%Y-%m-%d')) as num")->where($where)->find();
		
		$count = $count['num'];
		
		$model_t = 'Ajax:Attendance:sign_list';
	  
	  }
	  elseif('nosignlist' == $filter['type'])
	  {
	    if(1 == $filter['sign_type'])
		{
		  $s_t = 't_time';
		}else
		{
		  $s_t = 's_time';
		}
		
		$list = M('user')->where("onjob = 1 and id not in(select uid from `onethink_sigin` where FROM_UNIXTIME(".$s_t.",'%Y-%m-%d') = '".$filter['time']."')")->page($filter['p'].',10')->order($limit)->select();
		
		foreach($list as $k=>$v)
		{
		  $list[$k]['bname'] = M('department')->where('id = '.$v['did'])->getField('name');
		}
		
		$count = M('user')->where("onjob = 1 and id not in(select uid from `onethink_sigin` where FROM_UNIXTIME(".$s_t.",'%Y-%m-%d') = '".$filter['time']."')")->count();
		
		$model_t = 'Ajax:Attendance:nosign_list';
		
	  
	  }
	  elseif('usignlist' == $filter['type'])
	  {
	    if($filter['department_id'])
		{
		  $where = 'onjob = 1 and bid = '.$filter['department_id'];
		}else
		{
		  $where = 'onjob = 1';
		}
		
		//print_r($filter['month']);exit;
		
		$list = M('user')->where($where)->page($filter['p'].',10')->order($limit)->select();
		
		//根据list查询出每个人的当月的签到次数和签到天数
		foreach($list as $k=>$v)
		{
		  $list[$k]['sign_snum'] = M('sigin')->where("FROM_UNIXTIME(s_time,'%Y-%m') = '".$filter['month']."' and uid = ".$v['id'])->count();
		  $list[$k]['sign_tnum'] = M('sigin')->where("FROM_UNIXTIME(t_time,'%Y-%m') = '".$filter['month']."' and uid = ".$v['id'])->count();
		  $list[$k]['day_snum'] = M('sigin')->where("FROM_UNIXTIME(t_time,'%Y-%m') = '".$filter['month']."' and uid = ".$v['id'])->count();
		  
		  
		  $s_t = M('sigin')->where("uid = ".$v['id']." and FROM_UNIXTIME(s_time,'%Y-%m') = '".$filter['month']."'")
		         ->field("count(distinct FROM_UNIXTIME(s_time,'%Y-%m-%d')) as d_s, count(distinct FROM_UNIXTIME(t_time,'%Y-%m-%d')) as d_t")->find();
				 //print_r();exit;
		  $list[$k]['day_snum'] = $s_t['d_s'];
		  $list[$k]['day_tnum'] = $s_t['d_t'];
		  
		  //print_r(M('sigin')->getLastSql());exit;
		}
		
		$count = M('user')->where($where)->count();
		
		$model_t = 'Ajax:Attendance:usign_list';
	  
	  
	  }
	  elseif('attendlist' == $filter['type'])
	  {
	      //获取当前选择的日期
		  
		  //header("Content-type:text/html;charset=utf-8");
		  //print_r(date(t,strtotime($filter['month'])));exit;
		  $days = date(t,strtotime($filter['month']));
		  $weekarray = array('日','一','二','三','四','五','六');
		  $table_tr = array();
		  
		  for($i=1;$i<=$days;$i++)
		  {
		    $table_tr[$i-1]['d'] = $i;
			$table_tr[$i-1]['w'] = $weekarray[date('w',strtotime($filter['month'].'-'.$i))];
			
		  }
		  
		  $this->assign('table_tr',$table_tr);
		  
		  
		  $where = '1';
		  
		  if($filter['onjob'])
		  {
		  	$where .= ' and onjob = '.$filter['onjob'];
		  }
 
		  
		  //部门筛选
		  if($filter['department_id'])
		  {
		    $where .= ' and bid = '.$filter['department_id'];
		  }
		  
	  
		  $list = M('user')->where($where)->page($filter['p'].',10')->order($limit)->select();
		  
		  $year = substr($filter['month'],0,4);
		  $month = intval(substr($filter['month'],5));
			
			foreach($list as $k=>$v)
			{
			   //根据user取出相应的考勤信息
			   
				 
			   $kaoqin = M('user_kaoqin')->field('day,morning,afternoon')->where('uid = '.$v['id'].' and year = '.$year.' and month = '.$month)->select();
			   
			   $kaoqin_a = array();
			   
			   //对考勤数组进行重排
			   if($kaoqin)
			   {
				   foreach($kaoqin as $ko=>$vo)
				   {
					 $kaoqin_a[$vo['day']]['day'] = $vo['day'];
					 $kaoqin_a[$vo['day']]['morning'] = $vo['morning'];
					 $kaoqin_a[$vo['day']]['afternoon'] = $vo['afternoon'];
					 
					 if(1 == $vo['morning'])
					 {
					   $list[$k]['c'] += 0.5;
					 }
					 
					 if(1 == $vo['afternoon'])
					 {
					   $list[$k]['c'] += 0.5;
					 }
					 
					 
					 if(2 == $vo['morning'])
					 {
					   $list[$k]['j'] += 0.5;
					 }
					 
					 if(2 == $vo['afternoon'])
					 {
					   $list[$k]['j'] += 0.5;
					 }
					 
					 if(3 == $vo['morning'])
					 {
					   $list[$k]['x'] += 0.5;
					 }
					 
					 if(3 == $vo['afternoon'])
					 {
					   $list[$k]['x'] += 0.5;
					 }
					 
					 if(4 == $vo['morning'])
					 {
					   $list[$k]['s'] += 0.5;
					 }
					 
					 if(4 == $vo['afternoon'])
					 {
					   $list[$k]['s'] += 0.5;
					 }
					 
					 if(5 == $vo['morning'])
					 {
					   $list[$k]['b'] += 0.5;
					 }
					 
					 if(5 == $vo['afternoon'])
					 {
					   $list[$k]['b'] += 0.5;
					 }
					 
					 if(6 == $vo['morning'])
					 {
					   $list[$k]['k'] += 0.5;
					 }
					 
					 if(6 == $vo['afternoon'])
					 {
					   $list[$k]['k'] += 0.5;
					 }
					 
					 if(7 == $vo['morning'])
					 {
					   $list[$k]['d'] += 0.5;
					 }
					 
					 if(7 == $vo['afternoon'])
					 {
					   $list[$k]['d'] += 0.5;
					 }
					 
					 if(8 == $vo['morning'])
					 {
					   $list[$k]['q'] += 0.5;
					 }
					 
					 if(8 == $vo['afternoon'])
					 {
					   $list[$k]['q'] += 0.5;
					 }
				   }
			   
			   }
			   
			   for($z=0;$z<$days;$z++)
			   {
			     $list[$k]['kaoqin'][$z]['day'] = isset($kaoqin_a[$z+1]['day'])?$kaoqin_a[$z+1]['day']:0;
				 $list[$k]['kaoqin'][$z]['morning'] = isset($kaoqin_a[$z+1]['morning'])?$kaoqin_a[$z+1]['morning']:0;
				 $list[$k]['kaoqin'][$z]['afternoon'] = isset($kaoqin_a[$z+1]['afternoon'])?$kaoqin_a[$z+1]['afternoon']:0;
			   }
			   
			   //取得出勤，加班，事假，病假，休息，旷工，迟到，早退，天数
			   
			   
	
			}
			
		  $this->assign('data_time',date('Y-m',time()));
		  
		   //分页
		  $count      = M('user')->where($where)->count();
		  
		  $model_t = 'Ajax:Attendance:attend_list';
	  
	  }
	  
	  elseif('signinfo_list' == $filter['type'])
	  {
	     $list = M('sigin')->where('uid = '.$filter['uid'].' and FROM_UNIXTIME(s_time,"%Y-%m") = "'.$filter['month'].'"')->page($filter['p'].',10')->order($limit)->select();
		 
		 foreach($list as $k=>$v)
		 {
		   $list[$k]['name'] = M('user')->where('id = '.$v['uid'])->getField('name');
		 }
		 
		 $count = M('sigin')->where('uid = '.$filter['uid'].' and FROM_UNIXTIME(s_time,"%Y-%m") = "'.$filter['month'].'"')->count();
		 
		 $model_t = 'Ajax:Attendance:signinfo_list';
  
	  }
	  
	  elseif('userlist' == $filter['type'])
	  {
	  		$where = '1';
			
			if($filter['onjob'])
			{
				$where .= ' and onjob = '.$filter['onjob'];
			}
			
			if($filter['department_id'])
		  	{
		    	$where .= ' and bid = '.$filter['department_id'];
		  	}
			
			if($filter['keywords'])
			{
				$where .= ' and name like "%'.$filter['keywords'].'%"';
			}
			//print_r($where);
			
			$list = M('user')->where($where)->page($filter['p'].',10')->order($limit)->select();
			
			foreach($list as $k=>$v)
			{
				$list[$k]['bname'] = M('bumen')->where('id = '.$v['bid'])->getField('name');
				$list[$k]['zhiwei'] = M('position')->where('id = '.$v['pid'])->getField('name');
			}
			
			$count = M('user')->where($where)->count();
			
			$model_t = 'Ajax:Attendance:users_list';			
			
	  
	  }

	  
	  else
	  {
		  //部门筛选
		  if($filter['department_id'])
			{
			  $where = 'onjob = 1 and bid = '.$filter['department_id'];
			}else
			{
			  $where = 'onjob = 1';
			}
		  
		  if($filter['keywords'])
		  {
			$where .= " and nickname like '%".$filter['keywords']."%'";
		  }
		  
	  
		  $list = M('user')->where($where)->page($filter['p'].',10')->order($limit)->select();
			
			foreach($list as $k=>$v)
			{
			  $list[$k]['bname'] = M('bumen')->where('id = '.$v['bid'])->getField('name');
			  
			  switch ($v['pid'])
			  {
				  case 1:
				  $list[$k]['zhiwei'] = '董事';
				  break;
				  case 2:
				  $list[$k]['zhiwei'] = '团长';
				  break;
				  case 3:
				  $list[$k]['zhiwei'] = '部长';
				  break;
				  default:
				  $list[$k]['zhiwei'] = '暂无';
			  }
			  
	
			}
			
		  $this->assign('data_time',date('Y-m',time()));
		  
		   //分页
		  $count      = M('user')->where($where)->count();
		  
		  $model_t = 'Ajax:Attendance:user_list';
	  
	  }


	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  $this->assign('filter',$filter);// 赋值查询条件
	  
	  return array('list' => $list ,'show'=> $show,'model_t' => $model_t);
	
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
	   'type' => I('type'),
	   'sign_type' => I('sign_type'),
	   'time' => I('time'),
	   'month' => I('month',date('Y-m',time())),
	   'uid' => I('uid'),
	   'onjob' => I('onjob'),
	  );
	  
	 return $filter;
	
	}
	
	private function send_out($filter)
	{
	  $list = $this->get_list($filter);
	   
	  $this->assign('list',$list['list']);// 赋值数据集

	  $this->assign('page',$list['show']);// 赋值分页输出
	  
	  $this->display();
	
	}
	
	//离职与激活
	public function set_user_onjob()
	{
		$id = I('id');
		
		$onjob = I('onjob');
		
		if(1 == $onjob)
		{
			$status = 2;
		}else
		{
			$status = 1;
		}
		
		if(M('user')->where('id = '.$id)->setField('onjob',$status))
		{
			$this->success('操作成功！');
		}else
		{
			$this->error('操作失败！');
		}
	
	}
	
	
	//编辑员工信息
	public function edit()
	{
		$id = I('id');
		
		//根据id取出用户的信息
		$user_info = M('user')->where('id = '.$id)->find();
		
		//取出部门信息
		$did_info = M('bumen')->where('status = 1')->select();
		
		$did_info = D('Common/Tree')->toFormatTree($did_info,'name','id','pid');
		
		//取出职位信息
		$pid_info = M('position')->field('id,name')->where('status = 1')->select();
		
		$this->assign('pid_info',$pid_info);
		
		//取出app展现的部门
		
		$bidarr = M('uid_bidarr')->where('uid = '.$id)->getField('bidarr');
		
		$gblist = M('bumen')->where('id in ('.$bidarr.')')->select();
		
		$this->assign('gblist',$gblist);
		
		//赋值
		$this->assign('user_info',$user_info);
		
		$this->assign('did_info',$did_info);
		
		$this->display('Ajax:Attendance:edit');
	
	
	}
	
	//显示员工档案
	public function show_userarchives()
	{
		$id = I('id');
		
		//根据id取出该员工的档案信息
		$user_info = M('user_danan')->where('uid = '.$id)->find();
		
		$this->assign('user_info',$user_info);
		
		$this->display('Ajax:Attendance:user_archives');
	
	}
	
	
	//保存员工信息
	public function insert()
	{
		
		$error = '';
		
		$gbid = I('gbid');
		
		//如果有id说明是编辑
		if(I('id'))
		{
			$data = array(
				'name'=>I('name'),
				'username'=>I('username'),
				'bid'=>I('bid'),
				'pid'=>I('pid'),
				'id'=>I('id'),
			);
			
			M('user')->startTrans();//开启回滚
			
			$res_user = M('user')->save($data);
			
			$res_user = false === $res_user?0:1;

			$res_token = 1;

			
			$res = M('uid_bidarr')->where('uid = '.$data['id'])->getField('id');
			
			if($res)
			{
				$data_bidarr = array(
					'id'=>$res,
					'uid'=>$data['id'],
					'bidarr'=>implode(',',$gbid),
				);
				//print_r($data_bidarr);
				$res_bidarr = M('uid_bidarr')->where('id = '.$res)->save($data_bidarr);
				$res_bidarr = false === $res_bidarr?0:1;
				//print_r(M('uid_bidarr')->getLastSql());
			}else
			{
				$data_bidarr = array(
					'uid'=>$data['id'],
					'bidarr'=>implode(',',$gbid),
				);
				$res_bidarr = M('uid_bidarr')->add($data_bidarr);
			}

		}else
		{
			$data = array(
				'name'=>I('name'),
				'username'=>I('username'),
				'bid'=>I('bid'),
				'pid'=>I('pid'),
				'id'=>I('id'),
				'password'=>'51c81b5b6a975503317046b3eb4eacaa',
				'score'=>100,
			);
			
			//开启事务
			M('user')->startTrans();//开启回滚
			
			$res_user = M('user')->add($data);
			
			
			//调用api接口获取用户token
			$p = new \Common\Controller\ServerAPI('pwe86ga5e0s36','tXDzsgoz6InJ10');
			$r = $p->getToken($res_user,$data['username'],'11');
			$r = json_decode($r);
			
			if(200 == $r->code)
			{
				$res_token = M('user')->where('id = '.$res_user)->setField('ry_token',$r->token);
			}
			else
			{
				$res_token = 0;
				$error = '人数限制，添加失败，请联系管理员！';
			}
			
			//写入权限表分配默认权限
			
			$data_bidarr = array(
				'uid'=>$res_user,
				'bidarr'=>implode(',',$gbid),
			);
			$res_bidarr = M('uid_bidarr')->add($data_bidarr);
				
			
			
		}
		
		//更改user表的信息
		//var_dump($res_user);
		//var_dump($res_bidarr);
		if($res_user && $res_token && $res_bidarr)
		{
			M('user')->commit();//成功则提交
			$this->success('操作成功！');
		}
		else
		{
			M('user')->rollback();//不成功，则回滚
			$this->error('操作失败！');
		}
	}
	
	//制作员工前台app权限
	public function make_auth()
	{
		$bid = I('bid');
		
		//取出bid下的子id信息
		$bid_arr = $this->get_child_array($bid);
		array_unshift($bid_arr,$bid);
		
		$blist = M('bumen')->field('id,bname')->where('id in ('.implode(',',$bid_arr).')')->select();
		
		$this->success($blist);
		
		//echo json_encode($blist);exit;
	
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