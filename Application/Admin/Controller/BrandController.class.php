<?php


/***黄先科--品牌管理
** 2015.12.25

***/


namespace Admin\Controller;
use User\Api\UserApi;


class BrandController extends AdminController {


//品牌列表页面
    Public function index(){
         $brand_list=M('brand') -> order('id desc')->select();
           
         
         $this->assign('brand_list',$brand_list);
         $this->display();
    }
	
	
	
	
	Public function brandlist(){
	 
		$filter = array(
		'p' => I('p') ? I('p') : 1,
		'order_by'=> I('order_by'),
		'sort_by'=> I('sort_by')?I('sort_by'):'ASC'
		);
		
		$list = $this->brand_list($filter);
		
		
		$this->assign('list',$list['list']);// 赋值数据集
		
		$this->assign('page',$list['show']);// 赋值分页输出
		
        $this->display();
    }



//添加品牌页面

    Public function add(){
	
         //判断是否有id，有是编辑无则新增
		 
    	$id = I('id');
		
    	if ($id) {
    		$brand = M('brand');

    		$brand_info = $brand->where('id='.$id)->find();

    		$this->assign('brand_info',$brand_info);
    	}
       
         $this->display();
    }


//提交品牌信息到数据库

    Public function insert()
    {
    	//$User = M("brand"); // 实例化User对象

    	//判断是更新还是新增

    	$id = I('id');
		
		if(!I('brandname'))
		{
		 $this->error('品牌名字不能为空');
		}

    	$old_logo = I('old_logo');
		
		//print_r(C('UPLOAD_PATH'));exit;
		$img_logo = $this->_upload('Brand',50 ,50);
		//print_r($img_logo);exit;

    	//初始化图片上传类
		

    	if ($id) {

    		//赋值数据到$data

			$data = array(
				'id' => $id,

				'name' => I('brandname'),

				'img_logo' => isset($img_logo['pic_path'])?$img_logo['pic_path']:$old_logo,

				'img_logo_thumb' => isset($img_logo['mini_pic'])?$img_logo['mini_pic']:$old_logo,

				'desc' => I('branddesc'));

			//更新数据

    	    if(M('brand')->save($data))
			{
	           $this -> success('修改成功'); 

			}else
			{
	           $this->error('修改失败');
			}
    		
    	}else
    	{

	        //判断是否已有此品牌

	    	$name = I('brandname');
	        
	        if (M("brand")->where(array('name' => $name))->find()) {
	        	$this->error('已存在此品牌！');
	        }

	        //上传图片

			// 保存表单数据 包括附件数据
			
			$data = array(
				'name' => I('brandname'),

				'img_logo' => isset($img_logo['pic_path'])?$img_logo['pic_path']:'',

				'img_logo_thumb' => isset($img_logo['mini_pic'])?$img_logo['mini_pic']:'',

				'desc' => I('branddesc'));
				
			if(M('brand')->add($data))
			{
	           $this -> success('添加成功'); 

			}else
			{
	           $this->error('添加失败');

			}

    	}

    }


//删除品牌操作

    Public function delete_brand ()
    {
        //获取要删除的id

        $id = I('id');

        //实例化brand

        $brand = M('brand');

        //判断品牌下是否有产品，有禁止删除

        /*

        产品表待定

        */

        //现获取图片的名字，删除信息后再删除图片

        $brand_info = $brand->where('id='.$id)->find();


        //删除数据

        if ($brand->where('id='.$id)->delete()) {

        	//删除图片

        	if(!unlink(C('IMAGE_GEN').$brand_info['img_logo']) || !unlink(C('IMAGE_GEN').$brand_info['img_logo_thumb']))
        	{
        		$this->error('删除品牌成功！删除图片失败，请手动删除！');
        	}


        	$this->success('删除成功！');

        }else
        {

        	$this->error('删除失败!');
        }


    }
	
	public function ajax_query()
	{
	 
	 //条件数组
 
	 $filter = array(
	 'p' => I('p') ? I('p') : 1,
	 'order_by'=> I('order_by'),
	 'sort_by'=> I('sort_by')?I('sort_by'):'ASC'
	 );

     $list = $this->brand_list($filter);
	   
	 $this->assign('list',$list['list']);// 赋值数据集

	 $this->assign('page',$list['show']);// 赋值分页输出

	 $res_str = $this->fetch('Ajax:brand_list'); // 输出模板
	 
	 $data['info'] = $res_str;
	 
	 $data['success'] = 1;
	 
	 $this->ajaxReturn($data);
	
	}
	
	
	
	//获取顾客列表封装函数
	
	public function brand_list($filter)
	{
	  if($filter['order_by'])
	  {
	   $limit = $filter['order_by']." ".$filter['sort_by'];
	  }else
	  {
	   $limit = '';
	  }  
	  
	  $list = M('brand')->page($filter['p'].',10')->order($limit)->select();
	   
	   //分页
	  $count      = M('brand')->count();
	  
	  $_GET['p'] = $filter['p'];
	   
	  $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数 
	 
	  $show       = $Page->show_ajax($filter);// 分页显示输出
	  
	  return array('list' => $list ,'show'=> $show);
	
	}
	
	
	
	
	    /**
         * 图片上传处理
         * @param [String] $path [保存文件夹名称]
         * @param [String] $thumbWidth [缩略图宽度]
         * @param [String] $thumbHeight [缩略图高度]
         * @return [Array] [图片上传信息]
         */
            
        private function _upload($path,$thumbWidth = '' , $thumbHeight = '') {
            $obj = new \Think\Upload();// 实例化上传类
            $obj->maxSize = 3145728;// 设置附件上传大小
            $obj->savePath =$path.'/'; // 设置附件上传目录
            $obj->exts =  array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $obj->saveName = array('uniqid','');//文件名规则
            $obj->replace = true;//存在同名文件覆盖
            $obj->autoSub = true;//使用子目录保存
            $obj->subName  = array('date','Ym');//子目录创建规则，
            $info = $obj->upload();
            if(!$info) {
                return array('status' =>0, 'msg'=> $obj->getError() );
            }else{
                if($info){    //生成缩略图
        
                    $image = new \Think\Image();
        
                    foreach($info as $file) {
                        
                        $thumb_file = './Uploads/' . $file['savepath'] . $file['savename'];
                        $save_path = './Uploads/' .$file['savepath'] . 'mini_' . $file['savename'];
                        $image->open( $thumb_file )->thumb( $thumbWidth, $thumbHeight,\Think\Image::IMAGE_THUMB_FILLED )->save( $save_path );
                        return array(
                                'status' => 1,
                                'savepath' => $file['savepath'],
                                'savename' => $file['savename'],
                                'pic_path' => $file['savepath'] . $file['savename'],
                                'mini_pic' => $file['savepath'] . 'mini_' .$file['savename']
                        );
                        //@unlink($thumb_file); //上传生成缩略图以后删除源文件
                    }
                }else{
                    foreach($info as $file) {
                        return array(
                                'status' => 1,
                                'savepath' => $file['savepath'],
                                'savename' => $file['savename'],
                                'pic_path' => $file['savepath'].$file['savename']
                        );
                    }
                }
            }
        }

}

?>