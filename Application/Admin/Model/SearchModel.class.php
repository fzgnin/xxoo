<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 多订单表统一查询模块
 * @author huangxianke
 */

class SearchModel extends Model {

    /* 自动验证规则 */
    protected $_validate = array(
        
    );

    /* 自动完成规则 */
    protected $_auto = array(
        
    );
	
	/* sql语句 */
	protected $sql = '';
	
	/* 所有要操作的表数组 */
	protected $table_array = array('order','finance_order','otherfinance_order','inventory_order','dym_order','giro_order','income_order','stock_order');
	
	
	/* 要取出的数据（如果数据库默认的不是该字段，用数组替换数组默认格式'表名'=>'字段名'） */
	protected  $field_array = array(
		'id'=>array(
			//查询字段转换
			//'order'=>'value',
		
		),
		'order_sn'=>array(
		
		),
		'order_type'=>array(
		
		),
		'add_time'=>array(
		
		),
		'wname'=>array(
			'order'=>'gname',
			'finance_order'=>'mname',
			'otherfinance_order'=>'mname',
			'inventory_order'=>'',
			'dym_order'=>'gname',
			'giro_order'=>'out_cname',
			'income_order'=>'',
			'stock_order'=>'suppliername',
		),
		'amount'=>array(
			'order'=>'g_amount',
			'dym_order'=>'g_amount',
			'giro_order'=>'money',
			'income_order'=>'c_amount',
			'stock_order'=>'c_amount',
		),
		'remarks'=>array(
		
		),
	);
	
	
	/*
	*制作where查询条件
	*如果array为空则表明所有表都有
	*单表为空说明忽略该查询条件不为空说明替换该查询条件
	*/
	protected $search_where = array(
		'order_sn'=>array(
		
		),
		'order_type'=>array(
		
		),
		'add_time'=>array(
		
		),
		'amount'=>array(
			'order'=>'g_amount',
			'dym_order'=>'g_amount',
			'giro_order'=>'money',
			'income_order'=>'c_amount',
			'stock_order'=>'c_amount',
		),
		'remarks'=>array(
		
		),
	);
	
	
	
	//制作查询语句
	public function make_sql($where)
	{	
		foreach($this->table_array as $v)
		{
			$this->sql .= '(select ';
			$this->sql .= $this->make_field($v);
			$this->sql .= ' from '.C('DB_PREFIX').$v;
			$this->sql .= ' where '.$this->make_where($v,$where).') union ';		
		}
		//去掉最后一个union
		$this->sql = substr($this->sql,0,-7);
		if($where['order_by'])
		{
			$this->sql .= ' order by '.$where['order_by'];
		}
		$this->sql .= ' limit '.$where['p'].','.$where['page_num'];
		return $this->sql;
	}
	
	
	//制作查询条件
	public function make_where($order_name,$filter)
	{
		$where = '';
		
		foreach($this->search_where as $k=>$v)
		{
			switch($k)
			{
				case 'add_time':
								$where .= ' and add_time >='.$filter['begin_time'].' and add_time <'.$filter['end_time'];
								break;
				default:
								if($filter[$k])
								{
									if(!empty($v))
									{
										//过滤未有内容表
										if(isset($v[$order_name]) && !empty($v[$order_name]))
										{
											$where .= ' and '.$v[$order_name].' = "'.$filter[$k].'"';
										}elseif(!isset($v[$order_name]))
										{
											$where .= ' and '.$k.' = "'.$filter[$k].'"';
										}
									}else
									{
										$where .= ' and '.$k.' = "'.$filter[$k].'"';
									}	
								
								}							
			}
		}
		//去掉第一个and
		$where = preg_replace('/and/','',$where,1);
		return $where;
	}
	
	
	
	//制作查询字段
	public function make_field($order_name)
	{
		$field = '';
		foreach($this->field_array as $k=>$v)
		{
			if(!empty($v) && $v[$order_name])
			{
				$field .= $v[$order_name].' as '.$k.',';
			}elseif(isset($v[$order_name]) && !$v[$order_name])
			{
				$field .= '"无" as '.$k.',';
			}else
			{
				$field .= $k.',';
			}
		}		
		$field = rtrim($field, ",");
		return $field;
	}

	
	//查询操作
	public function search_orders($where)
	{
		//拼接字符串
		$this->make_sql($where);
		M()->query($this->sql);
		print_r(M()->getLastSql());
		return 1;
	}

    
}
