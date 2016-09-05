<?php 

namespace Admin\Controller;
use User\Api\UserApi;

Class ApiController extends \Think\Controller{
 //加载模板
       //加载验证码

 
//app_login登录功能api
  Public function login(){
    //获取数据库信息验证      
            $name = I('name');
            $password = I('pwd','','md5');  
            $user = M('user')->where(array('username'=>$name))->find();
    //判断数据登录不成功
              if (!$user || $user['password'] != $password){
     	       $data = array( 'status' => '0',);
     	       echo json_encode($data); 
               }else{
				   //成功获取登录信息         
               $user['status'] = 1;
               echo json_encode($user); 
				   }
    
    
  }
//app_frm4个人中心 检查版本更新
  Public function appcheckup(){
   $info = M('checkup')->where(array('id'=>1))->find();
   if($info){
    echo json_encode($info); 
   }


  }

//app写入用户现在版本
  Public function up_version(){
   $uid = I('uid');
   $version = I('version');
  $data= array(
    'version'=> $version,
    );

   $sql = M('user')->where(array('id'=>$uid))->save($data);
   if($sql){
 $info['status']=1;
  echo json_encode($info);

   }else{
 $info['status']=0;
  echo json_encode($info);
    
   }
  }



//更新当前用户缓存信息
  Public function get_new_user_local(){
  $uid = I('uid');
  $sql = M('user')->where(array('id'=>$uid))->find();
  if($sql){
  echo json_encode($sql); 
  }else{
  $info['status']=0;
  echo json_encode($info);
  }

  }


 //app  js 获取聊天头像和名片
  Public function chatget_info(){
  $id = I('id');
  $sql=M('user')->where(array('id'=>$id))->field('name,headurl')->find();
  if($sql){
  echo json_encode($sql); 
  }else{
  $info['status']=0;
  echo json_encode($info); 
  }

  }


 //app_index更新用户头像地址
  Public function saveheadurl(){
  $uid = I('uid');
  $headurl = I('headurl');
  $data = array(
    'headurl'=>$headurl
    );
  $sql=M('user')->where(array('id'=>$uid))->save($data);
  if($sql){
    $info['status']=1;
    echo json_encode($info); 
  }else{
    $info['status']=0;
    echo json_encode($info); 
  }


  }


   //app_index店家用户户头像地址
  Public function save_guest_headurl(){
  $workerid = I('workerid');
  $headurl = I('headurl');
  $data = array(
    'headurl'=>$headurl
    );
  $sql=M('guestworker')->where(array('id'=>$workerid))->save($data);
  if($sql){
    $info['status']=1;
    echo json_encode($info); 
  }else{
    $info['status']=0;
    echo json_encode($info); 
  }


  }

//app_bumens获取进入部门参数并传递数据
   Public function getbguest(){
      $bid=I('bid');
      $info=M('guest')->field('id,guestname')->where(array('bid'=>$bid))->select();
      echo json_encode($info); 
   
   }

 //app-guest-bumen_content页面获取部门名称和店家个数
   Public function getbmname(){
    $bid=I('bid');
    $bname=M('bumen')->field('bname')->where(array('bid'=>$bid))->find();
    $bmgc=M('guest')->where(array('bid'=>$bid))->count();
    $bamount=M('order')->where(array('bumen_id'=>$bid))->sum('g_amount');
    $data=array(
      'bname'=>$bname['bname'],
      'bmgc'=>$bmgc,
      'bamount'=>$bamount
      );
    echo json_encode($data);  

       }


 //app获取进入店家参数并传递数据 
  Public function getguest(){
     $gid=I('gid');
     $info=M('guest')->where(array('id'=>$gid))->find();  
     $bname=M('bumen')->where(array('bid'=>$info['bid']))->getField('bname');
     $info['bname']=$bname;
     echo json_encode($info); 

  }


//app=guest-guestinfo_content判断跟进状态 
  Public function iffollow(){
   $uid=I('uid');
   $gid=I('gid');

   $sql=M('follow')->where(array('uid'=>$uid,'gid'=>$gid))->find();
   if($sql){
    $info['stus']=1;
    echo json_encode($info);
   }else{
    $info['stus']=0;
    echo json_encode($info);
   }
  } 

//app-guest-guestinfo_content获取跟进状态
  Public function getfollow(){
  $uid=I('uid');//获取用户id
  $gid=I('gid');//获取客户id
  //如果接受数据为空
  if($uid==0 || $gid==0){
  $info['stus']=0;
  echo $request.'('.json_encode($info).')'; 
  }else{//正确接收数据后返回
  $data=array(
    'uid'=>$uid,
    'gid'=>$gid,
    'time'=>time(),
    );
  
  $sql=M('follow')->add($data);
  if($sql){
  $info['stus']=1;
  echo json_encode($info); 
  }else{
  $info['stus']=0;
  echo json_encode($info); 
  }
}

  }

//app-guest-guestinfo_frm删除跟进状态
  Public function delfollow(){
  $uid = I('uid');
  $gid = I('gid');
  $sql=M('follow')->where(array('uid'=>$uid,'gid'=>$gid))->delete();
  if($sql){
  $info['stus']=1;
  echo json_encode($info); 
  }else{
  $info['stus']=0;
  echo json_encode($info); 
  }
  }


//app-guest-guestinfo_frm获取店家合作品牌信息
  Public function getbranddiscount(){
   $gid = I('gid');
   $open=M('branddiscount');
  $sql = $open->join('onethink_brand ON onethink_brand.id = onethink_branddiscount.brand_id' )->field('onethink_brand.name')->where(array('onethink_branddiscount.guest_id'=>$gid))->select();
  if($sql){
  echo json_encode($sql); 
  }else{
  $info['stus']=0;
  echo json_encode($info); 
  }
  }



//app-signin-choice签到是选取跟进客户api
  Public function getfllowguestall(){
  $uid=I('uid');

   $open=M('follow');
   $info = $open->join('onethink_guest ON onethink_guest.id = onethink_follow.gid' )->field('onethink_guest.id,onethink_guest.guestname')->where(array('onethink_follow.uid'=>$uid))->select();
   if(!$info){
   $info['stus']=0;
   echo json_encode($info); 
   }else{
    echo json_encode($info); 
   }
  }


//app-signin-choice获取返回到签到页面的guest那么
  Public function getsignin_guestname(){
   $gid = I('gid');
   $info = M('guest')->where(array('id'=>$gid))->field('id,guestname')->find();
   if(!$info){
   $info['stus']=0;
   echo json_encode($info); 
   }else{
    echo json_encode($info); 
   }

  }

//app-all全局请求服务器获取当前服务器时间
  Public function gettime(){
     $time['now'] = time();
     echo json_encode($time);
  }


//app-signin接收签到数据并保存数据库
  Public function getsignin_info(){
      $uid=I('uid');
      $plan =I('plan');
      $guest_id=I('guest_id');
      $address=I('address');
      $signintime=I('signintime');
      $guestname=I('guestname');
     $data=array(
       'uid'=>$uid,
       'plan'=>$plan,
       's_address'=>$address,
       's_time'=>$signintime,
       'g_id'=>$guest_id,
       'g_name'=>$guestname,
       'type'=>1,
      );

    $upsignin=array('lastsignin'=>$signintime);//保存最后一次签到数据到用户表
    $upsuser=M('user')->where(array('id'=>$uid))->save($upsignin);
    $sql=M('sigin')->add($data);
    if($sql){
   $info['stus']=1;
   echo json_encode($info); 
    }else{
   $info['stus']=0;
   echo json_encode($info); 

    }

  }

//app-获取签到页面的初始化签到数据
  Public function get_today_sigin(){
   $uid = I('uid');
   $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
   $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
   $map1['s_time'] = array(array('gt',$beginToday),array('lt',$endToday));
   $map1['uid'] = $uid;
   $sql = M('sigin')->where($map1)->select();
   if($sql){
   echo json_encode($sql); 
   }else{
    $info['stus']=0;
    echo json_encode($info); 
   }
   
  }

//app -接收签退数据并保存数据库
  Public function get_today_sigint(){
  $sigint_id = I('sigint_id');
  $address = I('address');
  $signintime = I('signintime');
  $data = array(
      't_address'=>$address,
      't_time'=>$signintime,
      'type'=>2,
    );
  $sql = M('sigin')->where(array('id'=>$sigint_id))->save($data);
if($sql){
   $info['stus']=1;
   echo json_encode($info); 
   }else{
    $info['stus']=0;
    echo json_encode($info); 
   }
  }

//app-frame4个人中心检查签到后时候今天提交了日报表
  Public function checkdaysheet(){
    $uid = I('uid');
    $signintime =I('lastsignin');
    $sql=M('daysheet')->where(array('uid'=>$uid,'signintime'=>$signintime))->field('status')->find();
    if($sql){
      echo json_encode($sql);
    }else{
      $info['stus']=0;
     echo json_encode($info); 
    }

  }

//app-guest-changeinfo获取修改信息并修改数据
   Public function changeinfo(){
   $gid=I('gid');
   $manager=I('manager');
   $phone=I('phone');
   $tphone=I('tphone');
   $address=I('address');
   $rename=I('rename');
   $rephone=I('rephone');
   $data=array(
       'manager'=>$manager,
       'phone'=>$phone,
       'tphone'=>$tphone,
       'address'=>$address,
       'rename'=>$rename,
       'rephone'=>$rephone
    );
  
  $sql=M('guest')->where(array('id'=>$gid))->save($data);
     if($sql){
      $info['stus']=1;
      echo json_encode($info); 
     }else{
      $info['stus']=0;
      echo json_encode($info); 
     }
   }

//app-guest-guestarchives_content信息档案页面数据加载
   Public function guestarchives(){
   $gid=I('gid');
   $guestname=M('guest')->field('guestname')->where(array('id'=>$gid))->find();
   $sql=M('archives')->where(array('gid'=>$gid))->find();//查询档案数据库
   if(!$sql){//如果不存在该客户档案，创建档案
    $data=array('gid'=>$gid, 'uptime'=>time());
    $add=M('archives')->where(array('gid'=>$gid))->add($data);
    $info=M('archives')->where(array('gid'=>$gid))->find();
    $info['guestname']=$guestname['guestname']; 
     echo json_encode($info); 
   }else{
    $sql['guestname']=$guestname['guestname'];
    echo json_encode($sql); 
   }
   }
//app-guest-changearchives_content修改档案信息页面数据加载
   Public function changearchivesinfo(){
   $gid=I('gid');
   $info=M('archives')->where(array('gid'=>$gid))->find();
   $guestname=M('guest')->field('guestname')->where(array('id'=>$gid))->find();
   $info['guestname']=$guestname['guestname'];
   echo json_encode($info); 
   }
//app-guest-changearchives_content修改客户档案信息获取并修改数据库
   Public function getchangearchivesinfo(){
    $gid=I('gid');
    $areas=I('areas');
    $type=I('type');
    $years=I('years');
    $win=I('win');
    $iteam=I('iteam');
    $lv=I('lv');
    $pro=I('pro');
    $need=I('need');
    $zhuce=I('zhuce');
    $youxiao=I('youxiao');
   $data=array(
       'areas'=>$areas,
       'type'=>$type,
       'years'=>$years,
       'win'=>$win,
       'iteam'=>$iteam,
       'lv'=>$lv,
       'pro'=>$pro,
       'need'=>$need,
       'zhuce'=>$zhuce,
       'youxiao'=>$youxiao
    );
  
  $sql=M('archives')->where(array('gid'=>$gid))->save($data);
     if($sql){
      $info['stus']=1;
      echo json_encode($info); 
     }else{
      $info['stus']=0;
      echo json_encode($info); 
     }

   }

 //app-guest-addworker_content接收店内联系方式并保存
  Public function getaddworker(){
  $gid = I('gid');
  $name=I('name');
  $job=I('job');
  $sex=I('sex');
  $year=I('year');
  $month=I('month');
  $day=I('day');
  $years=I('years');
  $phone=I('phone');
  $data=array(
       'gid'=>$gid,
       'name'=>$name,
       'job'=>$job,
       'sex'=>$sex,
       'year'=>$year,
       'month'=>$month,
       'day'=>$day,
       'years'=>$years,
       'phone'=>$phone,
       'uptime'=>time(),
    );
   $add=M('guestworker')->add($data);
   if($add){
    $info['stus']=1;
    echo json_encode($info); 
   }else{
    $info['stus']=0;
    echo json_encode($info); 
   }


  }

 //app-guest-addworker_content接收店内联系方式并保存
  Public function getchangeworker(){
  $workerid = I('workerid');
  $name=I('name');
  $job=I('job');
  $sex=I('sex');
  $year=I('year');
  $month=I('month');
  $day=I('day');
  $years=I('years');
  $phone=I('phone');
  $data=array(
       'name'=>$name,
       'job'=>$job,
       'sex'=>$sex,
       'year'=>$year,
       'month'=>$month,
       'day'=>$day,
       'years'=>$years,
       'phone'=>$phone,
       'uptime'=>time(),
    );
   $add=M('guestworker')->where(array('id'=>$workerid))->save($data);
   if($add){
    $info['stus']=1;
    echo json_encode($info); 
   }else{
    $info['stus']=0;
    echo json_encode($info); 
   }


  }


//app-guest-addworker_content接收店内联系方式
  Public function showguestworker(){
  $gid=I('gid');//接收店家id
  $sql=M('guestworker')->where(array('gid'=>$gid))->select();
  if($sql){
  echo json_encode($sql); 
  }else{
    $info['stus']=0;
    echo json_encode($info); 
  }
  

  }
//app-guestplan进入页面获取初始化数据
 Public function getguestplan(){
  $gid=I('gid');
  $sql=M('guest')->where(array('id'=>$gid))->find();
  $getsql=M('guestplan')->where(array('guest_id'=>$gid))->find();
  if($getsql){
    $getsql['stus']=1;
    echo json_encode($getsql);
  }else{
    $info['guestname']=$sql['guestname'];
    echo json_encode($info);
  }



 }

//app-guest_addplan页面进入时获取初始数据
 Public function getaddplaninfo(){
   $gid=I('gid');
   $sql=M('guest')->where(array('id'=>$gid))->find();
   $getsql=M('guestplan')->where(array('guest_id'=>$gid))->find();
  if($getsql){
    $getsql['stus']=1;
    $getsql['tid']=$sql['tid'];
    $getsql['bid']=$sql['bid'];
    echo json_encode($getsql);
  }else{
    $info['guestname']=$sql['guestname'];
    $info['tid']=$sql['tid'];
    $info['bid']=$sql['bid'];
    echo json_encode($info);
  }

 }

//app-addguestplan 接收页面数据并保存数据库
 Public function saveguestplan(){
  $gid=I('gid');
  $y1=I('y1');
  $y2=I('y2');
  $y3=I('y3');
  $y4=I('y4');
  $y5=I('y5');
  $y6=I('y6');
  $y7=I('y7');
  $y8=I('y8');
  $y9=I('y9');
  $y10=I('y10');
  $y11=I('y11');
  $y12=I('y12');
  $feat = $y1+$y2+$y3+$y4+$y5+$y6+$y7+$y8+$y9+$y10+$y11+$y12;
  $data=array(
     'year'=>2016,
     'guest_id'=>I('gid'),
     'guestname'=>I('guestname'),
     'feat'=>$feat,
     'service'=>I('service'),
     'active'=>I('active'),
     'tid'=>I('tid'),
     'bid'=>I('bid'),
     'fplan'=>I('fplan'),
     'wplan'=>I('wplan'),
     'y1'=>$y1,
     'y2'=>$y2,
     'y3'=>$y3,
     'y4'=>$y4,
     'y5'=>$y5,
     'y6'=>$y6,
     'y7'=>$y7,
     'y8'=>$y8,
     'y9'=>$y9,
     'y10'=>$y10,
     'y11'=>$y11,
     'y12'=>$y12,
     'uptime'=>time()
    );

  $getsql=M('guestplan')->where(array('year'=>2016,'guest_id'=>$gid))->find();
    if(!$getsql){
    $sql=M('guestplan')->add($data);
      if($sql){$info['stus']=1;echo json_encode($info);} else{$info['stus']=0;echo json_encode($info);}
    }else{
     $sql=M('guestplan')->where(array('guest_id'=>$gid,'year'=>2016))->save($data); 
      if($sql){$info['stus']=1;echo json_encode($info);} else{$info['stus']=0;echo json_encode($info);}
    }

 }

//app-travel获取行程
 Public function gettravel(){
  $uid=I('uid');
  $sql=M('travel')->where(array('uid'=>$uid))->select();
  if($sql){
    echo json_encode($sql);
  }else{
   $info['stus']=0;
   echo json_encode($info);
  }

 }


//app-travel行程管理添加数据
Public function addtravel(){
 $uid = I('uid');
 $data = array(
    'uid'=>$uid,
    'year'=>2016,
    'month'=>1,
    'content'=>I('content'),
    'time'=>time(),
  );
 $sql=M('travel')->where(array('uid'=>$uid))->order('day desc')->find();
 if($sql){
   $data['day']=$sql['day']+1;
   $sql1=M('travel')->add($data);
   if($sql){
    $info['stus']=1;
    echo json_encode($info); 
   }else{
    $info['stus']=0;
    echo json_encode($info); 
   }
 }

if(!$sql){
   $data['day']=1;
   $sql1=M('travel')->add($data);
   if($sql){
    $info['stus']=1;
    echo json_encode($info); 
   }else{
    $info['stus']=0;
    echo json_encode($info); 
   }
}

}


//app-guest-noguestlist_content获取未合作客户数据
  Public function getnoguest(){
    $info=M('guest')->field('id,guestname,province')->where(array('stus'=>0))->limit(1500)->select();
    echo json_encode($info); 
    
  }
//app-guest-noguestlist-content获取区域店家数据
  Public function getnoguestone(){
    
    $province=I('province');
    $sql=M('guest')->field('id,guestname,province')->where(array('stus'=>0,'province'=>$province))->limit(1500)->select();
    if($sql){
      echo json_encode($sql); 
    }else{
    $info['stus']=0;
    echo json_encode($info);  
    }
    
  }

//app-daysheet日报表页面获取初始化签到信息 
  Public function get_sigininfo(){
   $uid = I('uid');
   $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
   $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
   $map1['s_time'] = array(array('gt',$beginToday),array('lt',$endToday));
   $map1['uid'] = $uid;
   $map2['s_time'] = array(array('gt',$beginToday),array('lt',$endToday));
   $map2['uid'] = $uid;
   $map2['type'] = $type;

   $sql = M('sigin')->where($map1)->select();

   if($sql){
   echo json_encode($sql);
   }else{
   $info['stus']=0;
   echo json_encode($info);

   }

  } 
  

//app-daysheet 接收数据并保存日报表数据到数据库 //（老版本待删除）
  Public function post_daysheet(){
    $uid=I('uid');
    $data=array(
          'uid'=>$uid,
          'plan'=>I('plan'),
          'work_z'=>I('work_z'),
          'img1'=>I('img1'),
          'img2'=>I('img2'),
          'img3'=>I('img3'),
          'img4'=>I('img4'),
          'time'=>time(),
      );
   $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
   $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
   $map1['time'] = array(array('gt',$beginToday),array('lt',$endToday));
   $map1['uid'] = $uid;
   $sql = M('day_sheet')->where($map1)->find();
   if($sql){
   $sql1=M('day_sheet')->where(array('id'=>$sql['id']))->save($data);
   $info['stus']=1;
   echo json_encode($info);
   }else{
   $sql2=M('day_sheet')->add($data);
    $info['stus']=2;
   echo json_encode($info);
   }

  }

 //app-daysheet 接收数据并保存日报表数据到数据库 //0.3版本建立
  Public function post_daysheet_s(){
    $id=I('id');
    $uid=I('uid');
    $ran_type = I('ran_type');
    $ginfo_type = I('ginfo_type');
    $data=array(
          'uid'=>$uid,
          'plan'=>I('plan'),
          'work_z'=>I('work_z'),
          'img1'=>I('img1'),
          'img2'=>I('img2'),
          'img3'=>I('img3'),
          'img4'=>I('img4'),
          'time'=>time(),
          'ran_type'=>$ran_type,
          'ginfo_type'=>$ginfo_type,
      );
   $sql1=M('sigin')->where(array('id'=>$id))->find();
   if($sql1){$sql = M('sigin')->where(array('id'=>$id))->save($data);}
   if($sql){
   if($ran_type == 1 && $sql1['ran_type'] != 1){
   $data1=array(
   'uid'=>$uid,
   'type'=>2,
   'sigintime'=>$sql1['s_time'],
   'time'=>time(),
   'sid'=>$id,
    );
   $sql2=M('work_ran')->add($data1);
   }

   $info['stus']=1;
   echo json_encode($info);

   }else{
   $info['stus']=0;
   echo json_encode($info);
   }

  } 
 
//分享别人的工作报表到工作圈
  Public function fxbb_ran(){
   $id = I('gomysheetid');
   $uid= I('uid');
   $sql = M('sigin')->where(array('id'=>$id))->find();
   if($sql){
if($sql['ran_type'] != 1){
  if($uid == $sql['uid']){
    $uid = 0 ;
  }
   $data1=array(
   'uid'=>$sql['uid'],
   'type'=>2,
   'sigintime'=>$sql['s_time'],
   'time'=>time(),
   'sid'=>$id,
   'rename' => $uid,
    );
   $sql2=M('work_ran')->add($data1);
   
   $data = array(
    'ran_type'=>1,
    );
   $sql1 = M('sigin')->where(array('id'=>$id))->save($data);
   $info['stus']=1;
   echo json_encode($info);
   }else{
   $info['stus']=0;
   echo json_encode($info);

   }
   }else{
 $info['stus']=0;
   echo json_encode($info);

   }

   

  }


//app获取日报表的草稿信息 //(老版本待删除)
  Public function get_daysheet_m(){
   $uid=I('uid');
   $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
   $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
   $map1['time'] = array(array('gt',$beginToday),array('lt',$endToday));
   $map1['uid'] = $uid;
   $sql = M('day_sheet')->where($map1)->find();
   if($sql){
    echo json_encode($sql);
  }else{
     $info['stus']=0;
   echo json_encode($info);
  }

  } 

 //app 获取填写报表页面初始化信息数据
  Public function get_daysheet_s_l(){
  $id = I('id');
  $sql = M('sigin')->where(array('id'=>$id))->find();
  if($sql){
    echo json_encode($sql);
  }else{
     $info['stus']=0;
   echo json_encode($info);
  }


 }

 //app-guest-followguestlist_content获取我跟进已合作店家的列表
  Public function followguestlist(){

   $uid=I('uid');

   $open=M('follow');
   $info = $open->join('onethink_guest ON onethink_guest.id = onethink_follow.gid' )->field('onethink_guest.id,onethink_guest.guestname,onethink_follow.id as fid')->where(array('onethink_follow.uid'=>$uid,'onethink_guest.stus'=>1))->select();

   if(!$info){
   $info['stus']=0;
   echo json_encode($info); 
   }else{
    echo json_encode($info); 
   }
   }
 

  //app-guest-followguestlist_content设置为vip跟进客户
  Public function followviplist(){
 
   $uid=I('uid');

  $open=M('follow');
  $info = $open->join('onethink_guest ON onethink_guest.id = onethink_follow.gid' )->field('onethink_guest.id,onethink_guest.guestname,onethink_follow.id as fid')->where(array('onethink_follow.uid'=>$uid,'onethink_guest.stus'=>1,'onethink_follow.vip'=>1))->select();
   if(!$info){
   $info['stus']=0;
   echo json_encode($info); 
   }else{
    echo json_encode($info); 
   }


   }


  //app-guest-followguestlist_content设置为vip跟进客户
  Public function followguestlist_vip(){
  $id = I('id');
  $sql = M('follow')->where(array('id'=>$id))->field('vip')->find();
  if($sql['vip'] == 1){
  $info['stus']=0;
   echo json_encode($info); 
  }else{
  
    $data= array(
  'vip'=>1,
  	);

  $sql1=M('follow')->where(array('id'=>$id))->save($data);
  if($sql1){
$info['stus']=1;
   echo json_encode($info); 

  }else{
  $info['stus']=2;
   echo json_encode($sql['vip']); 	
  }

  }


   }

   //取消跟进客户的vip
  Public function followguestlist_delvip(){
    $id = I('id');
    $data = array(
    	'vip'=>0,
    	);
    $sql = M('follow')->where(array('id'=>$id))->save($data);
    if($sql){
    $info['stus']=1;
   echo json_encode($info); 	
}else{
	$info['stus']=0;
   echo json_encode($info); 
}
  }





//app-guest-followguestlist_content获取我跟进未合作店家的列表
  Public function follownoguestlist(){

   $uid=I('uid');

   $open=M('follow');
   $info = $open->join('onethink_guest ON onethink_guest.id = onethink_follow.gid' )->field('onethink_guest.id,onethink_guest.guestname')->where(array('onethink_follow.uid'=>$uid,'onethink_guest.stus'=>0))->select();

   if(!$info){
   $info['stus']=0;
   echo json_encode($info); 
   }else{
    echo json_encode($info); 
   }
   }

//app-frame4对比签到时间是不是同一天
 Public function checksignintime(){
    $lastsignin = I('lastsignin');
    $date1 = date("Y-m-d",$lastsignin);
    $date2 = date("Y-m-d",time());
   if($date1 === $date2){
   $info['stus']=1;
   echo json_encode($info); 
   }else{
    $info['stus']=0;
   echo json_encode($info); 
   }

 }


//app-frame1获取工作的信息
  Public function get_workinfo(){
  $open=M('daysheet');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_daysheet.uid' )->where(array('onethink_daysheet.status'=>1))->order('daysheettime desc')->field('onethink_daysheet.id,onethink_daysheet.daysheettime,onethink_daysheet.plan,onethink_daysheet.img1,onethink_daysheet.img2,onethink_daysheet.img3,onethink_user.name,onethink_user.headurl')->select();
  if($info){
    echo json_encode($info);  
  }else{
   $info['stus']=0;
   echo json_encode($info);  
  }

  }

//app-frame1获取工作的信息的详情  //老版本已经过期
  Public function get_workshowinfo(){
  $id = I('gotoworkinfoid');

  }

//app-myindex-savepassword_content修改密码保存数据库
    Public function getsavepwd(){
    $uid=I('uid');
    $nowpwd=I('nowpwd','','md5');
    $newpwd=I('newpwd','','md5');
    $repwd=I('repwd','','md5');

    $now=M('user')->where(array('id'=>$uid))->find();
    if($now['password'] != $nowpwd){
      $info['stus'] = 0;
      echo json_encode($info);
    }else{
      $data=array('password'=>$newpwd,);
      $sql=M('user')->where(array('id'=>$uid))->save($data);
      if($sql){ 
       $info['stus'] = 1;
      echo json_encode($info);}
    }
    }

//app-user-glise 员工管理列表页面显示
    Public function user_glistinfo(){
       $sql = M('user')->select();
       if($sql){
        echo json_encode($sql);}
        else{
        $info['stus'] = 1;
        echo json_encode($info);}
     

    }

//app-user-info  获取当前员工的详细信息
    Public function get_userinfo(){
    $uid = I('gotouid');
    $sql = M('user')->where(array('id'=>$uid))->find();
    if($sql){
     echo json_encode($sql); 
   }else{
     $info['stus'] = 0;
        echo json_encode($info);
   }
    }
//app-myindex-guest_content获取合作、未合作、跟进店家数量
    Public function guestindex(){
    $uid=I('uid');
    $num1=M('guest')->where(array('stus'=>1))->count();
    $num2=M('guest')->where(array('stus'=>0))->count();
    $num3=M('follow')->where(array('uid'=>$uid))->count();
    //组合数组
    $data=array(
          'num1'=>$num1,
          'num2'=>$num2,
          'num3'=>$num3
      ); 
    echo json_encode($data); 
    }

//app-frame2 的charts数据输出
Public function guest_sixcharts(){

$list = M('bumen')->alias('b')->join('left join `onethink_bid_gid` bg on b.id = bg.bid')->field('count(distinct gid)')->where('tid in (1,2,3,4,5,6) and b.status = 1')->group('tid')->select();

$data=array(//数据封装数组输出
         'onegc'=>$list[0],
         'twogc'=>$list[1],
         'threegc'=>$list[2],
         'fourgc'=>$list[3],
         'fivegc'=>$list[4],
         'sixgc'=>$list[5]
      );

 echo json_encode($data);   
}


//app-guestworkeringo 获取客户联系人的详情信息
Public function get_guestworkerinfo(){
$workerid = I('workerid');
$sql = M('guestworker')->where(array('id'=>$workerid))->find();
if($sql){
    $gid = $sql['gid'];
    $guestname = M('guest')->where(array('id'=>$gid))->field('guestname')->find();
    $sql['guestname']=$guestname['guestname'];
   echo json_encode($sql);  
}else{
  $info['stus']=0;
   echo json_encode($info); 
}
}

//app-guestworkeringo 添加到我的联系人
Public function add_mydirectory(){
   $uid = I('uid');
   $workerid = I('workerid');
   $data=array(
     'uid'=>$uid,
     'workerid'=>$workerid
    );
   $sql = M('directory')->where(array('uid'=>$uid,'workerid'=>$workerid))->find();
   if($sql){
    $info['stus']=0;
    echo json_encode($info); 
   }else{
     $sql1=M('directory')->add($data);
     if($sql1){
       $info['stus']=1;
       echo json_encode($info); 
     }
   }


}

//获取每个店家的规划业绩和实际业绩--图表
Public function get_guestallfeat(){
$gid = I('gid');
$map1['add_time'] = array(array('gt',1451577600),array('lt',1454256000));
$map1['guest_id'] = $gid;
$plan=M('guestplan')->where(array('guest_id'=>$gid))->field('y1,y2,y3,y4,y5,y6,y7,y8,y9,y10,y11,y12')->find();
$tfeat=M('order')->field('sum(g_amount) as ty1')->where($map1)->find();
if($plan != null){
 $info = array_merge_recursive($plan, $tfeat);
 echo json_encode($info);
}else{
  if($tfeat !== null){echo json_encode($tfeat);}else{$info['stus']=0;echo json_encode($info);}
  
}

}

//app 对戒每个部门的业绩--图表
Public function get_bumenallfeat(){
$bid = I('bid');
$map1['bumen_id'] = $bid;
$y = "2016";
for($m=1;$m<=12;$m++){
$d = strtotime("$y-$m-1"); // 该月开始
$c = $m+1;
$e = strtotime("$y-$c-1"); //该月结束
$map1['add_time'] = array(array('gt',$d),array('lt',$e));
 $tfeat=M('order')->where($map1)->sum('g_amount');
 if(!$tfeat){
  $tfeat = 0;
 }
 $data['m'.$m]=$tfeat;
}

 echo json_encode($data);

 

 
 $map2['add_time'] = array(array('gt',1454256000),array('lt',1456761600));
 $map2['bumen_id'] = $bid;


}


//app-user 获取我的报表列表
Public function get_mydaysheet(){
  $uid = I('uid');
  $sql = M('sigin')->where(array('uid'=>$uid))->order('s_time desc')->select();
  if($sql){
    echo json_encode($sql);
  }

}


//app-user 获取我的工作日报表详情
Public function get_mydaysheetinfo(){
  $id = I('gomysheetid');
  $open=M('sigin');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_sigin.uid' )->where(array('onethink_sigin.id'=>$id))->find();
   echo json_encode($info);
}


//app-显示我的通讯录
Public function show_mydirectory(){
  $uid = I('uid');
  $open = M('directory');
  $info = $open->join('onethink_guestworker ON onethink_guestworker.id = onethink_directory.workerid' )->where(array('onethink_directory.uid'=>$uid))->select();
  if($info){
    echo json_encode($info); 
  }else{
   $info['stus']=0;
    echo json_encode($info); 
  }
  
}

//app-客户管理年度规划数据
Public function get_guestguanliplan(){
  $all_n=M('guestplan')->count();
  $all_f=M('guestplan')->sum('feat');
  $one_n=M('guestplan')->where(array('tid'=>1))->count();
  $one_f=M('guestplan')->where(array('tid'=>1))->sum('feat');
  $two_n=M('guestplan')->where(array('tid'=>2))->count();
  $two_f=M('guestplan')->where(array('tid'=>2))->sum('feat');
  $three_n=M('guestplan')->where(array('tid'=>3))->count();
  $three_f=M('guestplan')->where(array('tid'=>3))->sum('feat');
  $four_n=M('guestplan')->where(array('tid'=>4))->count();
  $four_f=M('guestplan')->where(array('tid'=>4))->sum('feat');
  $five_n=M('guestplan')->where(array('tid'=>5))->count();
  $five_f=M('guestplan')->where(array('tid'=>5))->sum('feat');
  $six_n=M('guestplan')->where(array('tid'=>6))->count();
  $six_f=M('guestplan')->where(array('tid'=>6))->sum('feat');
  $data=array(
   'all_n'=>$all_n,
   'all_f'=>$all_f,
   'one_n'=>$one_n,
   'one_f'=>$one_f,
   'two_n'=>$two_n,
   'two_f'=>$two_f,
   'three_n'=>$three_n,
   'three_f'=>$three_f,
   'four_n'=>$four_n,
   'four_f'=>$four_f,
   'five_n'=>$five_n,
   'five_f'=>$five_f,
   'six_n'=>$six_n,
   'six_f'=>$six_f,
    );

  echo json_encode($data); 
}


//app-frame0 获取聊天对象资料重组数组
Public function chat_userinfo(){
$info = M('user')->field('id,name,headurl')->select();
$userinfo = array();
foreach ( $info as $i=>$val ) {
 $key = $val['id'];
 $userinfo[$key] = $val; 
}
echo json_encode($userinfo);

}

//app-获取每个军团的规划业绩和年度业绩
Public function get_juntuanall(){
$planfeat_one = M('guestplan')->where(array('tid'=>1))->sum('feat');
 $open = M('order');
 $yearfeat_one= $open->join('onethink_bumen ON onethink_bumen.bid = onethink_order.bumen_id' )->where(array('onethink_bumen.tid'=>1))->sum('onethink_order.g_amount');
$planfeat_two = M('guestplan')->where(array('tid'=>2))->sum('feat');
$yearfeat_two= $open->join('onethink_bumen ON onethink_bumen.bid = onethink_order.bumen_id' )->where(array('onethink_bumen.tid'=>2))->sum('onethink_order.g_amount');
$planfeat_three = M('guestplan')->where(array('tid'=>3))->sum('feat');
 $yearfeat_three= $open->join('onethink_bumen ON onethink_bumen.bid = onethink_order.bumen_id' )->where(array('onethink_bumen.tid'=>3))->sum('onethink_order.g_amount');
$planfeat_four = M('guestplan')->where(array('tid'=>4))->sum('feat');
 $yearfeat_four= $open->join('onethink_bumen ON onethink_bumen.bid = onethink_order.bumen_id' )->where(array('onethink_bumen.tid'=>4))->sum('onethink_order.g_amount');
$planfeat_five = M('guestplan')->where(array('tid'=>5))->sum('feat');
 $yearfeat_five= $open->join('onethink_bumen ON onethink_bumen.bid = onethink_order.bumen_id' )->where(array('onethink_bumen.tid'=>5))->sum('onethink_order.g_amount');
$planfeat_six = M('guestplan')->where(array('tid'=>6))->sum('feat');
 $yearfeat_six= $open->join('onethink_bumen ON onethink_bumen.bid = onethink_order.bumen_id' )->where(array('onethink_bumen.tid'=>6))->sum('onethink_order.g_amount');
$data = array(
'planfeat_one' => $planfeat_one,
'yearfeat_one' => $yearfeat_one,
'planfeat_two' => $planfeat_two,
'yearfeat_two' => $yearfeat_two,
'planfeat_three' => $planfeat_three,
'yearfeat_three' => $yearfeat_three,
'planfeat_four' => $planfeat_four,
'yearfeat_four' => $yearfeat_four,
'planfeat_five' => $planfeat_five,
'yearfeat_five' => $yearfeat_five,
'planfeat_six' => $planfeat_six,
'yearfeat_six' => $yearfeat_six,
  );

echo json_encode($data);

}

//app-guestlist 部门点击业绩分布（版本号：1.0）2版本后删除
public function bumenguestlist_m()
  {
     $bid = I('bid');
     
     //获取部门下所有的客户列表
     
     $beginThismonth = mktime(0,0,0,date('m'),1,date('Y')); 
     $guest_list = M('guest')->query("select g.guestname,g.id,ifnull(sum(o.g_amount),0) as year,ifnull(sum(if(o.add_time>=".$beginThismonth.",o.g_amount ,0)),0) as month,gp.feat from `onethink_guest` as g left join `onethink_order` as o on g.id = o.guest_id left join `onethink_guestplan` as gp on g.id = gp.guest_id where (o.bumen_id = ".$bid." or g.bid =".$bid.") group by g.id order by month DESC"); 
      echo json_encode($guest_list);   

  }

//app-guestlist 部门点击业绩分布（版本号：1.0）2版本后删除
public function bumenguestlist_y()
  {
     $bid = I('bid');
     
     //获取部门下所有的客户列表
     
     $beginThismonth = mktime(0,0,0,date('m'),1,date('Y')); 
     $guest_list = M('guest')->query("select g.guestname,g.id,ifnull(sum(o.g_amount),0) as year,ifnull(sum(if(o.add_time>=".$beginThismonth.",o.g_amount ,0)),0) as month,gp.feat from `onethink_guest` as g left join `onethink_order` as o on g.id = o.guest_id left join `onethink_guestplan` as gp on g.id = gp.guest_id where (o.bumen_id = ".$bid." or g.bid =".$bid.") group by g.id order by year DESC"); 
      echo json_encode($guest_list);   

  }

//app-guestlist 部门点击业绩分布（版本号：1.0）2版本后删除
public function bumenguestlist_p()
  {
     $bid = I('bid');
     
     //获取部门下所有的客户列表
     
     $beginThismonth = mktime(0,0,0,date('m'),1,date('Y')); 
     $guest_list = M('guest')->query("select g.guestname,g.id,ifnull(sum(o.g_amount),0) as year,ifnull(sum(if(o.add_time>=".$beginThismonth.",o.g_amount ,0)),0) as month,gp.feat from `onethink_guest` as g left join `onethink_order` as o on g.id = o.guest_id left join `onethink_guestplan` as gp on g.id = gp.guest_id where (o.bumen_id = ".$bid." or g.bid =".$bid.") group by g.id order by feat DESC"); 
      echo json_encode($guest_list);   

  }

//新版本 部门月度业绩排行显示
public function bumenguest_list_m(){
   $bid = I('bid');
     
     //获取部门下所有的客户列表
     
     $beginThismonth = mktime(0,0,0,date('m'),1,date('Y')); 
     $guest_list = M('bid_gid')->query("select g.guestname,g.id,ifnull(sum(o.g_amount),0) as year,ifnull(sum(if(o.add_time>=".$beginThismonth.",o.g_amount ,0)),0) as month,ifnull(gp.feat,0) as feat from `onethink_guest` as g left join `onethink_order` as o on g.id = o.guest_id left join `onethink_guestplan` as gp on g.id = gp.guest_id left join `onethink_bid_gid` as bg on g.id = bg.gid  where (bg.bid =".$bid.") group by g.id order by month DESC"); 
      echo json_encode($guest_list);   

}

//新版本 部门年度度业绩排行显示
public function bumenguest_list_y()
  {
     $bid = I('bid');
     
     //获取部门下所有的客户列表
     
    $beginThismonth = mktime(0,0,0,date('m'),1,date('Y')); 
     $guest_list = M('bid_gid')->query("select g.guestname,g.id,ifnull(sum(o.g_amount),0) as year,ifnull(sum(if(o.add_time>=".$beginThismonth.",o.g_amount ,0)),0) as month,ifnull(gp.feat,0) as feat from `onethink_guest` as g left join `onethink_order` as o on g.id = o.guest_id left join `onethink_guestplan` as gp on g.id = gp.guest_id left join `onethink_bid_gid` as bg on g.id = bg.gid  where (bg.bid =".$bid.") group by g.id order by year DESC"); 
      echo json_encode($guest_list);   

  }

//新版本 部门规划业绩排行显示
public function bumenguest_list_p()
  {
     $bid = I('bid');
     
     //获取部门下所有的客户列表
     
    $beginThismonth = mktime(0,0,0,date('m'),1,date('Y')); 
     $guest_list = M('bid_gid')->query("select g.guestname,g.id,ifnull(sum(o.g_amount),0) as year,ifnull(sum(if(o.add_time>=".$beginThismonth.",o.g_amount ,0)),0) as month,ifnull(gp.feat,0) as feat from `onethink_guest` as g left join `onethink_order` as o on g.id = o.guest_id left join `onethink_guestplan` as gp on g.id = gp.guest_id left join `onethink_bid_gid` as bg on g.id = bg.gid  where (bg.bid =".$bid.") group by g.id order by feat DESC"); 
      echo json_encode($guest_list);   

  }




 public function get_huang()
  {
    $id = I('id');
  
  $t_arr = array();
  
  //取得该id的名字和所属成员
  $t_arr[0]['i'] = M('bumen')->where('id = '.$id)->getField('bname');
  
  $t_arr[0]['p'] = M('user')->field('id,name,headurl,job,bid as did')->where('bid = '.$id.' and onjob = 1')->select();
  
  $t_arr[0]['n'] = 0;
  
  //分类下id总数再加上此id是总id
  
  $ss = $this->dfddf($id);
  
  if($ss)
  {
  $ss = $ss.','.$id;
  }else
  {
  $ss = $id;
  }
  
  $where['bid']  = array('in',$ss);
  $where['onjob']  = 1;
  
  //获取该分类下的人数
  $t_arr[0]['num'] = M('user')->where($where)->count();
  
  $t_arr[0]['c'] = $this->ggg_ff($id,$t_arr[0]['n']);
  
  //header("content-type:text/html;charset=utf-8");
  
  //echo "<pre>";
  //print_r($t_arr);exit;
  
  echo json_encode($t_arr);   
  
  }
 
 
  public function ggg_ff($id,$n)
  {
    //取得该id下所有的子id
  $c_id = M('bumen')->field('id,bname')->where('pid = '.$id)->select();
  
  $ff_arr = array();
  
  foreach($c_id as $k=>$v)
  {
    $ff_arr[$k]['i'] =  $v['bname'];
    
    $ff_arr[$k]['p'] = M('user')->field('id,name,username,headurl,job,bid as did')->where('bid = '.$v['id'].' and onjob = 1')->select();
	
	$ff_arr[$k]['n'] = 1 + $n;
	
	$ss = $this->dfddf($v['id']);
	
	if($ss)
	  {
	  $ss = $ss.','.$v['id'];
	  }else
	  {
	  $ss = $v['id'];
	  }
  
    $where['bid']  = array('in',$ss);
    $where['onjob']  = 1;
  
    //获取该分类下的人数
    $ff_arr[$k]['num'] = M('user')->where($where)->count();
  
    $ff_arr[$k]['c'] = $this->ggg_ff($v['id'],$ff_arr[$k]['n']);
		
  }
  
  return $ff_arr;
  
  }



//app工作圈获取数据
Public function get_workran_info(){
  $open=M('work_ran');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_work_ran.uid' )->order('time desc')->field('onethink_work_ran.id,onethink_work_ran.rename,onethink_work_ran.sid,onethink_work_ran.type,onethink_work_ran.content,onethink_work_ran.img1,onethink_work_ran.img2,onethink_work_ran.img3,onethink_work_ran.sigintime,onethink_work_ran.time,onethink_work_ran.p_count,onethink_work_ran.d_count,onethink_user.name,onethink_user.headurl')->limit(100)->select();
  echo json_encode($info);   
}

//app 获取个人工作圈数据
Public function get_chatuser_ran_info(){
  $uid = I('goto_sendid');
  $open=M('work_ran');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_work_ran.uid' )->order('time desc')->field('onethink_work_ran.id,onethink_work_ran.rename,onethink_work_ran.sid,onethink_work_ran.type,onethink_work_ran.content,onethink_work_ran.img1,onethink_work_ran.img2,onethink_work_ran.img3,onethink_work_ran.sigintime,onethink_work_ran.time,onethink_work_ran.p_count,onethink_work_ran.d_count,onethink_user.name,onethink_user.headurl')->where(array('uid'=>$uid))->limit(100)->select();
  echo json_encode($info);   
}


//app提交工作页面的发表日志
Public function post_workran_writework(){   
    $data=array(
          'uid'=>I('uid'),
          'content'=>I('content'),
          'img1'=>I('img1'),
          'img2'=>I('img2'),
          'img3'=>I('img3'),
          'type'=>1,
          'time'=>time(),
      );
 $sql=M('work_ran')->add($data);
   if($sql){
    $info['stus']=1;
    echo json_encode($info);
   }else{
    $info['stus']=0;
    echo json_encode($info);
   }
}

//app  获取发表的work心情详情
Public function get_work_data(){
$id = I('gotoworkinfoid');
  $open=M('work_ran');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_work_ran.uid' )->field('onethink_user.name,onethink_user.headurl,onethink_work_ran.type,onethink_work_ran.content,onethink_work_ran.img1,onethink_work_ran.img2,onethink_work_ran.img3,onethink_work_ran.sigintime,onethink_work_ran.time,onethink_work_ran.sid,onethink_work_ran.p_count,onethink_work_ran.d_count,onethink_work_ran.uid')->where(array('onethink_work_ran.id'=>$id))->find();
   echo json_encode($info);

}

//app 获取发表work详情的评论信息
Public function get_work_ran_p(){
  $id = I('gotoworkinfoid');
 $open=M('work_p');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_work_p.uid' )->field('onethink_user.name,onethink_user.headurl,onethink_work_p.content')->where(array('onethink_work_p.wid'=> $id))->order('time desc')->select();
  if($info){
    echo json_encode($info);
  }else{
    $data['stus']=0;
    echo json_encode($data);
   }

  }


//app 获取报表详情的评论信息
Public function get_sigin_p(){
  $id = I('gomysheetid');
 $open=M('sigin_p');
  $info = $open->join('onethink_user ON onethink_user.id = onethink_sigin_p.uid' )->field('onethink_user.name,onethink_user.headurl,onethink_sigin_p.content')->where(array('onethink_sigin_p.sid'=> $id))->order('time desc')->select();
  if($info){
    echo json_encode($info);
  }else{
    $data['stus']=0;
    echo json_encode($data);
   }

  }

//app work_ran 保存接收到的工作评论内容
Public function save_workran_p(){
 $wid = I('gotoworkinfoid');
 $uid = I('uid');
 $content =I('content');
 $data=array(
  'wid'=>$wid,
  'uid'=>$uid,
  'content'=>$content,
  'time'=>time()
  );
  $sql = M('work_p')->add($data);
  if($sql){
   $updata_p['p_count'] = array('exp','p_count+1');// 评论加1
   $p_count= M('work_ran')->where(array('id'=>$wid))->save($updata_p); // 根据条件保存修改的数据
   $info['stus']=1;
   echo json_encode($info);
  }else{
   $info['stus']=0;
   echo json_encode($info);

  }
}

//app work_ran 保存接收到的报表评论内容
Public function save_sigin_p(){
 $sid = I('gomysheetid');
 $uid = I('uid');
 $content =I('content');
 $data=array(
  'sid'=>$sid,
  'uid'=>$uid,
  'content'=>$content,
  'time'=>time()
  );
  $sql = M('sigin_p')->add($data);
  if($sql){
   $updata_p['p_count'] = array('exp','p_count+1');// 评论加1
   $p_count= M('sigin')->where(array('id'=>$sid))->save($updata_p); // 根据条件保存修改的数据
   $info['stus']=1;
   echo json_encode($info);
  }else{
   $info['stus']=0;
   echo json_encode($info);

  }
}

//app点攒的积分判断和积分加减
Public function work_d_score(){
$uid = I('uid');
$wid = I('gotoworkinfoid');
             //判断用户积分是否足够
$count =M('user')->field('score')->where(array('id'=>$uid))->find();
if($count['score'] <= 0){
  $info['stus']=0;
  echo json_encode($info);
}else{
             //查找目标id->pid
  $getpid=M('work_ran')->field('uid')->where(array('id'=>$wid))->find();
  $pid = $getpid['uid'];
  if($uid == $pid){
 $info['stus']=2;
  echo json_encode($info);
  }else{
  //处理积分变化
  $updata_add['score'] = array('exp','score+1');// 积分加1
  $updata_del['score'] = array('exp','score-1');// 积分加1
  $updata_d['d_count'] = array('exp','d_count+1');// 点赞加1
  $sql1 = M("user")->where(array('id'=>$uid))->save($updata_del); 
  $sql2 = M("user")->where(array('id'=>$pid))->save($updata_add); 
  $sql3 = M("work_ran")->where(array('id'=>$wid))->save($updata_d);
  $info['stus']=1;
  echo json_encode($info);
    
  }
           
}

}



//app点攒的积分判断和积分加减（报表）
Public function sigin_d_score(){
$uid = I('uid');
$sid = I('gomysheetid');
             //判断用户积分是否足够
$count =M('user')->field('score')->where(array('id'=>$uid))->find();
if($count['score'] <= 0){
  $info['stus']=0;
  echo json_encode($info);
}else{
             //查找目标id->pid
  $getpid=M('sigin')->field('uid')->where(array('id'=>$sid))->find();
  $pid = $getpid['uid'];
  if($uid == $pid){
 $info['stus']=2;
  echo json_encode($info);
  }else{
  //处理积分变化
  $updata_add['score'] = array('exp','score+1');// 积分加1
  $updata_del['score'] = array('exp','score-1');// 积分加1
  $updata_d['d_count'] = array('exp','d_count+1');// 点赞加1
  $sql1 = M("user")->where(array('id'=>$uid))->save($updata_del); 
  $sql2 = M("user")->where(array('id'=>$pid))->save($updata_add); 
  $sql3 = M("sigin")->where(array('id'=>$sid))->save($updata_d);
  $info['stus']=1;
  echo json_encode($info);
    
  }
           
}

}


//app 提交请假审批
Public function shenpi_bx_info(){
$uid = I('uid');
$touid = I('touid');
$money = I('money');
$qj_reseason = I('qj_reseason');

$data=array(
   'uid'=>$uid,
   'touid'=>$touid,
   'money'=>$money,
   'qj_reseason'=>$qj_reseason,
   'typeid'=>2,
   'typename'=>"报销审批",
   'status'=>1,
   'time'=>time(),
  );
 $sql = M('shenpi')->add($data);

 if($sql){
  $info['stus']=1;
  echo json_encode($info);
}else{
$info['stus']=0;
  echo json_encode($info);

}


}

//app 获取我需要处理的未审批
Public function get_shenpi_myno(){
$touid = I('uid');
$sql = M('shenpi')->where(array('touid'=>$touid,'status'=>1))->select();
if($sql){
 echo json_encode($sql); 
}else{
$info['stus']=0;
  echo json_encode($info);

}
}

//app 获取已经审批处理
Public function get_shenpi_myyes(){
$touid = I('uid');
$map['status'] = array('in','2,3');
$map['touid'] =$touid;
$sql = M('shenpi')->where($map)->select();
if($sql){
 echo json_encode($sql); 
}else{
$info['stus']=0;
  echo json_encode($info);
}
}


//app 获取我发起的审批
Public function get_shenpi_myshenpi(){
$uid = I('uid');
$sql = M('shenpi')->where(array('uid'=>$uid))->select();
if($sql){
 echo json_encode($sql); 
}else{
$info['stus']=0;
  echo json_encode($info);
}


}



//app 获取一条审批的详细内容
Public function get_shenpi_info(){
$shenpi_id = I('shenpi_id');
$sql=M('shenpi')->where(array('id'=>$shenpi_id))->find();
if($sql){
 echo json_encode($sql); 
}else{
 $info['stus']=0;
  echo json_encode($info);

}
}

//app 审批通过
Public function set_shenpi_ok(){
$shenpi_id = I('shenpi_id');
$get_reson = I('get_reson');
$data=array(
    'get_reson'=>$get_reson,
    'status'=>2,
  );
$sql=M('shenpi')->where(array('id'=>$shenpi_id))->save($data);
if($sql){
  $info['stus']=1;
  echo json_encode($info);
}else{
 $info['stus']=0;
  echo json_encode($info);

}


}

//app 审批拒绝
Public function set_shenpi_no(){
$shenpi_id = I('shenpi_id');
$get_reson = I('get_reson');
$data=array(
    'get_reson'=>$get_reson,
    'status'=>3,
  );
$sql=M('shenpi')->where(array('id'=>$shenpi_id))->save($data);
if($sql){
  $info['stus']=1;
  echo json_encode($info);
}else{
 $info['stus']=0;
  echo json_encode($info);


}
}


//app获取提交的年度规划信息并报存数据库
Public function get_sheet_year(){
$uid = I('uid');
$sheet_id = I('sheet_id');
$type = I('type');
$title = I('title');
$sheetcontent=I('sheetcontent');
$data= array(
  'uid'=>$uid,
  'title'=>$title,
  'sheetcontent'=>$sheetcontent,
  'time'=>time(),
  'type' => $type,
  );
 $sql=M('sheet')->where(array('id'=>$sheet_id))->find();
 if($sql){
  $sql_1=M('sheet')->where(array('id'=>$sheet_id))->save($data);
  $info['stus']=1;
  echo json_encode($info);
 }else{
  $sql_2=M('sheet')->add($data);
  $info['stus']=2;
  echo json_encode($info);
 }


}

//app获取年度规划初始信息
Public function get_sheet_year_info(){
 $uid = I('uid');
 $sheet_id = I('sheet_id');
 $sql = M('sheet')->where(array('id'=>$sheet_id))->find();
 if($sql){
   echo json_encode($sql);
 }else{
   $info['stus']=0;
  echo json_encode($info);
 }
}


//app 获取创建群聊是的用户信息
Public function get_cgroup_user(){
$sql=M('user')->select();
if($sql){
   echo json_encode($sql);
 }else{
   $info['stus']=0;
  echo json_encode($info);
 }
}

//app报销审批的修改新版本
Public function post_qjshenpi(){
$uid = I('uid');
$uid = I('uid');
$touid = I('touid');
$qj_time = I('qj_time');
$qj_time_end=I('qj_time_end');
$qj_reseason = I('qj_reseason');

$data=array(
   'uid'=>$uid,
   'touid'=>$touid,
   'qj_time'=>$qj_time,
   'qj_time_end'=>$qj_time_end,
   'qj_reseason'=>$qj_reseason,
   'typeid'=>1,
   'typename'=>"请假审批",
   'status'=>1,
   'time'=>time(),
  );
 $sql = M('shenpi')->add($data);

 if($sql){
  $info['stus']=1;
  echo json_encode($info);
}else{
$info['stus']=0;
  echo json_encode($info);

}


}


//app 获取sheet数据
Public function get_sheetlist_info(){
$uid = I('uid');
$sql =M('sheet')->where(array('uid'=>$uid,'type'=>0))->select();
if($sql){
  echo json_encode($sql);
}else{
$info['stus']=0;
  echo json_encode($info);

}

}


//app 获取sheet 拥有类型的数据
Public function get_sheetlist_type_info(){
$uid = I('uid');
$type = I('type');
$sql =M('sheet')->where(array('uid'=>$uid,'type'=>$type))->select();
if($sql){
  echo json_encode($sql);
}else{
$info['stus']=0;
  echo json_encode($info);

}

}

//app店家进货明细
Public function get_guestproinfo(){

  
}

//app 获取用户积分
Public function get_userscore(){
 $uid = I('uid');
 $sql = M('user')->where(array('id'=>$uid))->field('score,job,signature')->find();
 if($sql){
   echo json_encode($sql);
 }else{
  $info['stus']=0;
  echo json_encode($info);
 }
}


//app 获取积分排行
Public function user_score(){
 $sql = M('user')->order('score desc')->select();
  if($sql){
   echo json_encode($sql);
 }else{
  $info['stus']=0;
  echo json_encode($info);

 }
}

//app 删除签到信息
Public function del_s_l(){
$id = I('id');
$sql = M('sigin')->where(array('id'=>$id))->delete();
if($sql){
 $info['stus']=1;
 echo json_encode($info);
}else{
 $info['stus']=0;
 echo json_encode($info);
}


}

//app 获取信息发布者的个人信息
Public function get_chat_gotuuser_info(){
$id = I('goto_sendid');
$sql = M('user')->where(array('id'=>$id))->find();
$sql_1 = M('bumen')->where(array('bid'=>$sql['bid']))->field('bname')->find();
$sql['bname']=$sql_1['bname'];
if($sql){
echo json_encode($sql);
}else{
 $info['stus']=0;
 echo json_encode($info); 
}
}



//app 获取客户联系人的往来记录
Public function get_guestworker_text(){
$workerid=I('workerid');
$uid=I('uid');
$content=I('content');
$data=array(
  'worker_id'=>$workerid,
  'uid'=>$uid,
  'content'=>$content,
  'time'=>time()
  );
$sql= M('guestworker_text')->add($data);
 if($sql){
 $info['stus']=1;
 echo json_encode($info); 
 }else{
 $info['stus']=0;
 echo json_encode($info); 
 }
}


//app获取客户联系人往来记录
Public function get_guestworker_textinfo(){
$workerid=I('workerid');
$sql = M('guestworker_text')->where(array('worker_id'=>$workerid))->select();
if($sql){
echo json_encode($sql); 

}else{
 $info['stus']=0;
 echo json_encode($info); 

}


}


//app获取店家的定位和地址
Public function get_guest_map(){
$gid=I('gid');
$lon=I('lon');
$lat=I('lat');
$map_address = I('map_address');
$data=array(
  'lon'=>$lon,
  'lat'=>$lat,
  'map_address'=>$map_address,
  );
$sql=M('guest')->where(array('id'=>$gid))->save($data);
if($sql){
 $info['stus']=1;
 echo json_encode($info); 
}else{
 $info['stus']=0;
 echo json_encode($info); 
 
}


}

//app 个人中心 资料获取初步信息
Public function get_my_user_info_ajax(){
$uid = I('uid');
$sql=M('user')->where(array('id'=>$uid))->field('sex,job,phone,signature')->find();
if($sql){
 echo json_encode($sql); 
}else{
 $info['stus']=0;
 echo json_encode($info); 
 
}
}
//app 个人中心 接受性别信息修改
Public function post_user_sex_change(){
$uid = I('uid');
$data=array(
  'sex'=>I('sex'),
  );
$sql=M('user')->where(array('id'=>$uid))->save($data);
if($sql){
 $info['stus']=1;
 echo json_encode($info); 
}else{
 $info['stus']=0;
 echo json_encode($info); 
 
}
}


//app 个人中心 接受职位信息修改
Public function post_user_job_change(){
$uid = I('uid');
$data=array(
  'job'=>I('job'),
  );
$sql=M('user')->where(array('id'=>$uid))->save($data);
if($sql){
 $info['stus']=1;
 echo json_encode($info); 
}else{
 $info['stus']=0;
 echo json_encode($info); 
 
}
}

//app 个人中心 接受个性签名信息修改
Public function post_user_signature_change(){
$uid = I('uid');
$data=array(
  'signature'=>I('signature'),
  );
$sql=M('user')->where(array('id'=>$uid))->save($data);
if($sql){
 $info['stus']=1;
 echo json_encode($info); 
}else{
 $info['stus']=0;
 echo json_encode($info); 
 
}
}





//app 个人中心 接受手机信息修改
Public function post_user_phone_change(){
$uid = I('uid');
$data=array(
  'phone'=>I('phone'),
  );
$sql=M('user')->where(array('id'=>$uid))->save($data);
if($sql){
 $info['stus']=1;
 echo json_encode($info); 
}else{
 $info['stus']=0;
 echo json_encode($info); 
 
}
}



//app店家所属部门获取现合作部门
Public function get_setting_bumen_now(){
$gid = I('gotogid');
$sql = M('bid_gid')->field('onethink_bumen.tid,onethink_bumen.bid,onethink_bumen.bname')->join('onethink_bumen ON onethink_bumen.bid = onethink_bid_gid.bid' )->where(array('onethink_bid_gid.gid'=>$gid))->select();
if($sql){
echo json_encode($sql);  
}else{
  $info['stus']=0;
 echo json_encode($info); 
}

}



//app店家所属部门获取现合作部门
Public function get_setting_bumen_all(){
 $sql=M('bumen')->order('tid asc')->select();
 if($sql){
 echo json_encode($sql); 
 }else{
 $info['stus']=0;
 echo json_encode($info); 
 }

}

//app获取所属部门提交的部门id
Public function post_setting_bumen_id(){
$gid=I('gotogid');
$post_id = I('id_arry');
$post_id = json_decode($post_id);
$sql=M('bid_gid')->where(array('gid'=>$gid))->delete();


if($post_id == null){

  $data1 = array(
   'stus'=>2,
    );

 $sql2=M('guest')->where(array('id'=>$gid))->save($data1);
if($sql2){
   $info['stus']=3;
 echo json_encode($info); 
}


}else{

for($i=0; $i<count($post_id);$i++){
  $data = array(
     'bid'=>$post_id[$i],
     'gid'=>$gid
    );
  $sql1 = M('bid_gid')->add($data);
}
 $info['stus']=1;
 echo json_encode($info); 
}

}

//app获取搜索内容 ——guset-部门页面
Public function get_search_info(){
$bid = I('bid');
$content = I('content');
$map['bid']=$bid;
$map['guestname'] = array('like',"%$content%");
$sql = M('guest')->where($map)->select();
if($sql){
 echo json_encode($sql); 
}else{
 $info['stus']=0;
 echo json_encode($info);
}
}

//app 获取部门名称
Public function get_bumen_name(){
$bid = I('bid');
$sql = M('bumen')->where(array('bid'=>$bid))->field('tid,bname')->find();
if($sql){
 echo json_encode($sql); 
}else{
$info['stus']=0;
 echo json_encode($info); 
}

}

//app 获取新增店家数据 并添加店家
Public function post_add_guest(){
$bid=I('bid');
$guestname=I('guestname');
$areas = I('areas');
$manager=I('manager');
$phone=I('phone');
$address=I('address');
$tid=I('tid');

$data=array(
 'guestname'=>$guestname,
 'areas'=>$areas,
 'address'=>$address,
 'manager'=>$manager,
 'phone'=>$phone,
 'tid'=>$tid,
 'bid'=>$bid,
 'stus'=>1,
 'time'=>time(),
  );


 
$sql=M('guest')->add($data);
if($sql){
  $data1 = array(
   'gid'=>$sql,
   'bid'=>$bid,
    );
 $sql1=M('bid_gid')->add($data1);
 if($sql1){
$info['stus']=1;
 echo json_encode($info); 

 }else{
  $info['stus']=0;
 echo json_encode($info); 
 }

}else{
 $info['stus']=0;
 echo json_encode($info); 

}



}

//获取并添加未合作客户
Public function post_add_noguest(){
$guestname=I('guestname');
$areas = I('areas');
$manager=I('manager');
$phone=I('phone');
$address=I('address');
$uid = I('uid');
$data=array(
 'guestname'=>$guestname,
 'areas'=>$areas,
 'address'=>$address,
 'manager'=>$manager,
 'phone'=>$phone,
 'stus'=>0,
 'time'=>time(),
  );
$sql=M('guest')->add($data);
if($sql){
$data1 = array(
    'uid'=>$uid,
    'gid'=>$sql,
    'time'=>time(),
	);
$sql2 = M('follow')->add($data1);
if($sql2){
	 $info['stus']=1;
 echo json_encode($info); 
}else{
 $info['stus']=0;
 echo json_encode($info); 

}

}




}





//app 获取店家定位信息
Public function get_guest_address_lat(){
$gid=I('gid');
$sql=M('guest')->where(array('id'=>$gid))->field('lon,lat')->find();
if($sql){
   echo json_encode($sql);
 }else{
 $info['stus']=0;
 echo json_encode($info); 
 }


}



//app 获取点加的at1信息
Public function guest_at1(){
 $gid=I('gotogid');
 $sql=M('sigin')->where(array('g_id'=>$gid ,'ginfo_type'=>1))->select();
 if($sql){
 echo json_encode($sql); 	
}else{
 $info['stus']=0;
 echo json_encode($info); 
}
}

//app 获取点加的at2信息
Public function guest_at2(){
 $gid=I('gotogid');
 $sql=M('sigin')->where(array('g_id'=>$gid ,'ginfo_type'=>2))->select();
 if($sql){
 echo json_encode($sql); 	
}else{
 $info['stus']=0;
 echo json_encode($info); 
}
}

//app 获取点加的at3信息
Public function guest_at3(){
 $gid=I('gotogid');
 $sql=M('sigin')->where(array('g_id'=>$gid ,'ginfo_type'=>3))->select();
 if($sql){
 echo json_encode($sql); 	
}else{
 $info['stus']=0;
 echo json_encode($info); 
}
}


//app 获取点加的at4信息
Public function guest_at4(){
 $gid=I('gotogid');
 $sql=M('sigin')->where(array('g_id'=>$gid ,'ginfo_type'=>4))->select();
 if($sql){
 echo json_encode($sql); 	
}else{
 $info['stus']=0;
 echo json_encode($info); 
}
}


//app 获取提交的员工梦想
Public function user_post_dream_info(){
$uid = I('uid');
$dream = I('dream');
$touid = I('touid');
$data=array(
  'uid'=>$uid,
  'dream'=>$dream,
  'type'=>1,
  'touid'=>$touid,
  'time'=>time(),
	);

$sql=M('dream')->add($data);
if($sql){
  $info['stus']=1;
 echo json_encode($info);  

}else{

	 $info['stus']=0;
 echo json_encode($info); 
}

}


 //app 获取员工梦想
Public function get_user_dream(){
$uid = I('uid');
$sql=M('dream')->where(array('uid'=>$uid))->select();
if($sql){
 echo json_encode($sql); 	
}else{

	 $info['stus']=0;
 echo json_encode($info); 

}


}

//获取所有的梦想
Public function get_user_dream_all(){
$sql=M('dream')->select();
if($sql){
 echo json_encode($sql); 	
}else{

	 $info['stus']=0;
 echo json_encode($info); 

}


}


//供应商信息获取并写入
Public function get_gongying_info(){
$name = I('name');
$areas = I('areas');
$type =I('type');
$manager = I('manager');
$phone = I('phone');
$address = I('address');
$info = I('info');

$data = array(
 'name'=>$name,
 'areas'=>$areas,
 'type'=>$type,
 'manager'=>$manager,
 'phone'=>$phone,
 'address'=>$address,
 'info'=>$info,
 'time'=>time(),
  );

$sql=M('gongying')->add($data);
if($sql){
   $info['stus']=1;
 echo json_encode($info); 
}else{
   $info['stus']=0;
 echo json_encode($info); 

}

}

//获取供应商信息
Public function get_gongying_data(){
  $type = I('type');
$sql = M('gongying')->where(array('type'=>$type))->select();
 if($sql){
  echo json_encode($sql); 
 }else{
    $info['stus']=0;
 echo json_encode($info); 
 }


}


//根据id 获取具体供应商的详细信息
Public function get_gongying_oneinfo(){
  $id = I('id');
  $sql=M('gongying')->where(array('id'=>$id))->find();
  if($sql){
echo json_encode($sql); 
  }else{
    $info['stus']=0;
 echo json_encode($info); 

  }

}

//app获取店家的服务信息
Public function get_guestserver(){
$gid = I('gid');
$sql = M('sigin')->where(array('g_id'=>$gid))->select();
if($sql){
 echo json_encode($sql);  
}else{
    $info['stus']=0;
 echo json_encode($info); 
  
}



}

//app工作全获取图片诉诸格式接口
Public function workran_info_pic_array(){
  $id = I('id');

  $sql = M('work_ran')->where(array('id'=>$id))->field('img1,img2,img3')->find();
 
  foreach($sql as $k){
    if($k != ''){
       $data[] = $k; 
    }
  
  }
 echo json_encode($data); 
}

//app工作全获取图片诉诸格式接口
Public function sigin_info_pic_array(){
  $id = I('id');

  $sql = M('sigin')->where(array('id'=>$id))->field('img1,img2,img3')->find();
 
  foreach($sql as $k){
    if($k != ''){
       $data[] = $k; 
    }
  
  }
 echo json_encode($data); 
}


//app 接受任务信息写入数据库
Public function get_renwu_add(){
 $uid = I('uid');
 $touid = I('touid');
 $arr_touid = explode(",", $touid); 
 $content = I('content');
 $data = array(
   'uid'=>$uid,
   'touid' =>$touid,
   'content'=>$content,
   'time'=>time(),
  );

 $sql = M('renwu')->add($data);
 if($sql){
     for($i=0; $i<count($arr_touid);$i++){
     $data1= array(
       'touid'=>$arr_touid[$i],
       'renwu_id'=>$sql,
       'time'=>time(),
       'read'=>0
     	);
     $sql1=M('renwu_to')->add($data1);	
}
     $info['stus']=1;
 echo json_encode($info); 
 }else{
      $info['stus']=0;
 echo json_encode($info); 
 }



}

//app读取我的任务  未完成



//app 读取我发布的任务
Public function get_renwu_mysend(){
$uid = I('uid');
$sql = M('renwu')->where(array('uid'=>$uid))->select();
if($sql){
 echo json_encode($sql); 	
}else{
      $info['stus']=0;
 echo json_encode($info); 

} 

}


//app获取未完成的任务
Public function get_my_renwu_no(){
  $uid = I('uid');
  $open=M('renwu_to');
  $sql = $open->alias('t')->join('left join `onethink_renwu` r ON t.renwu_id = r.id' )->field('r.content,r.time,r.uid,r.touid,r.id')->where(array('t.touid'=>$uid,'r.over'=>0))->select();

  if($sql){
 echo json_encode($sql); 	
  }else{
      $info['stus']=0;
 echo json_encode($info); 

  }

}

//app 获取任务设置为已完成状态
Public function post_my_renwu_over(){
$renwu_id = I('renwu_id');
$data=array(
   'over'=>1,
	);
$sql = M('renwu')->where(array('id'=>$renwu_id))->save($data);
if($sql){
$info['stus']=1;
 echo json_encode($info); 	
}else{
$info['stus']=0;
 echo json_encode($info); 	

}


}


//app获取未完成的任务
Public function get_my_renwu_yes(){
  $uid = I('uid');
  $open=M('renwu_to');
  $sql = $open->alias('t')->join('left join `onethink_renwu` r ON t.renwu_id = r.id' )->field('r.content,r.time,r.uid,r.touid,r.id')->where(array('t.touid'=>$uid,'r.over'=>1))->select();

  if($sql){
 echo json_encode($sql); 	
  }else{
      $info['stus']=0;
 echo json_encode($info); 

  }

}


 //app  接收往来供应的信息并写入数据库
Public function get_renwu_write(){
  $uid = I('uid');
  $name =I('name');
  $price = I('price');
  $spec = I('spec');
  $gy_id = I('gy_id');
  $img1 = I('img1');
  $data = array(
   'name' =>$name,
   'price' =>$price,
   'spec' =>$spec,
   'uid' =>$uid,
   'time' =>time(),
   'gy_id'=>$gy_id,
   'img1'=>$img1

  	);
  $sql = M('gys_pro')->add($data);
  if($sql){
  	  $info['stus']=1;
 echo json_encode($info); 
  }else{
  	  $info['stus']=0;
 echo json_encode($info); 
  }


}


//app获取供应商write的信息
Public function get_gys_write_info(){
   $gy_id =I('gy_id');
   $sql = M('gys_pro')->where(array('gy_id'=>$gy_id))->select();
   if($sql){
   	 echo json_encode($sql); 
   	}else{
  	  $info['stus']=0;
 echo json_encode($info); 

   	}

}



//app 提交店内客户资料信息
Public function post_cus_info(){
   $g_id = I('g_id');
   $name = I('name');
   $phone = I('phone');
   $sex = I('sex');
   $job = I('job');
   $lv = I('lv');
   $sell = I('sell');
   $old = I('old');
   $birthday = I('birthday');
   $fw_id = I('fw_id');
   $fw_name = I('fw_name');

   $data = array(
  'g_id'=>$g_id,
  'name'=>$name,
  'phone'=>$phone,
  'sex'=>$sex,
  'job'=>$job,
  'lv'=>$lv,
  'sell'=>$sell,
  'old'=>$old,
  'birthday'=>$birthday,
  'fw_id'=>$fw_id,
  'fw_name'=>$fw_name,
  'time'=>time(),
   	);

   $sql = M('guest_cus')->add($data);
   if($sql){
	  $info['stus']=1;
 echo json_encode($info); 

   }else{
   	 $info['stus']=0;
 echo json_encode($info); 

   }

}

//查询cus
Public function get_cus_gusetinfo(){
  $g_id = I('g_id');
  $sql = M('guest_cus')->where(array('g_id'=>$g_id))->select();
    if($sql){
 echo json_encode($sql); 

  }else{
 $info['stus']=0;
 echo json_encode($info);

  }


}


//cus显示客户数量
Public function get_cus_gusetinfo_count(){
  $g_id = I('g_id');

  $sql = M('guest_cus')->where(array('g_id'=>$g_id))->count();
   $data=array(
  'count'=>$sql,
    );  
   if($sql){
    echo json_encode($data); 
   } else{
 $info['stus']=0;
 echo json_encode($info);

   }


}

//添加cus的成交
Public function add_cus_info(){
  $g_id = I('g_id');
  $c_id = I('cid');
  $y_time = I('y_time');
  $y_type = I('y_type');
  $name = I('name');
  $y_money = I('y_money');
  $is_ok = I('is_ok');
  $s_time = I('s_time');
  $s_type = I('s_type');
  $s_money = I('s_money');
 
  $data = array(
    'g_id'=>$g_id,
    'c_id'=>$c_id,
    'name'=>$name,
    'y_time'=>$y_time,
    'type'=>$type,
    'y_type'=>$y_type,
    'y_money'=>$y_money,
    'is_ok'=>$is_ok,
    's_time'=>$s_time,
    's_type'=>$s_type,
    's_money'=>$s_money,
    );
 $sql = M('guest_cusinfo')->add($data);
 if($sql){
 	 $info['stus']=1;
 echo json_encode($info);
}else{
	 $info['stus']=0;
 echo json_encode($info);

}

}

// app cus 修改订单的成交状态
Public function change_cus_ok(){
$id = I('id');
$is_ok = I('is_ok');
$s_time = I('s_time');
$s_type = I('s_type');
$s_money = I('s_money');

$data = array(
  'is_ok'=>$is_ok,
  's_time'=>$s_time,
  's_type'=>$s_type,
  's_money'=>$s_money,

  );
$sql = M('guest_cusinfo')->where(array('id'=>$id))->save($data);
if($sql){
   $info['stus']=1;
 echo json_encode($info); 
}else{
   $info['stus']=0;
 echo json_encode($info);  
}





}

//app cus 成交记录
Public function cus_info_atinfo(){
   $c_id = I('cid');
   $sql = M('guest_cusinfo')->where(array('c_id'=>$c_id))->select();
   if($sql){
   	 echo json_encode($sql);
   	}else{
   	 $info['stus']=0;
 echo json_encode($info);	
   	}

}

//app_cus 获取设为已成交的初始化信息
Public function cus_isok_info(){
 $c_id = I('c_id');
 $sql = M('guest_cusinfo')->where(array('id'=>$c_id))->find();
 if($sql){
  echo json_encode($sql);
}else{
   $info['stus']=0;
 echo json_encode($info);

}



}



  //app_cus 获取总店成交记录
  Public function cus_guest_cj_info(){
    $g_id = I('g_id');
    $sql = M('guest_cusinfo')->where(array('g_id'=>$g_id))->select();
    if($sql){
     echo json_encode($sql); 
   }else{
$info['stus']=0;
 echo json_encode($info); 

   }
  }


  //app_cus 获取个人成交记录
  Public function cus_cus_cj_info(){
    $c_id = I('c_id');
    $sql = M('guest_cusinfo')->where(array('c_id'=>$c_id))->select();
    if($sql){
     echo json_encode($sql); 
   }else{
$info['stus']=0;
 echo json_encode($info); 

   }
  }


  //app-cus 获取个人详情
  Public function get_cus_guest_info(){
  $cus_id = I('cus_id');
  $sql = M('guest_cus')->where(array('id'=>$cus_id))->find();
  if($sql){
      echo json_encode($sql); 
    }else{
     $info['stus']=0;
 echo json_encode($info);  
    }

  }



//app -cus 添加往来记录
  Public function add_cus_text(){
    $uid = I('uid');
    $cid = I('cid');
    $content = I('content');
    $data=array(
    'uid'=>$uid,
    'cid'=>$cid,
    'content'=>$content,
    'time'=>time(),
      );
    
  $sql=M('guest_custext')->add($data);

  if($sql){
 $info['stus']=1;
 echo json_encode($info); 

  }else{
       $info['stus']=0;
 echo json_encode($info); 
  }

  }


//app-cus 读取往来信息
  Public function get_cus_text(){
  $cid=I('cid');
  $sql = M('guest_custext')->where(array('cid'=>$cid))->select();
  if($sql){
 echo json_encode($sql); 

  }else{
       $info['stus']=0;
 echo json_encode($info); 
  }


  }

 //app cus 删除cus信息
  Public function del_cus_info(){
   $c_id = I('cus_id');
   $sql1=M('guest_cus')->where(array('id'=>$c_id))->delete();
    $sql2=M('guest_cusinfo')->where(array('c_id'=>$c_id))->delete();
     $sql3=M('guest_custext')->where(array('cid'=>$c_id))->delete();
       $info['stus']=1;
        echo json_encode($info); 
  
    }

// app cus 添加特殊日期
   Public function get_cus_tesu_date(){
    $c_id = I('c_id');
    $month = I('month');
    $day = I('day');
    $content = I('content');
    $data = array(
    'c_id'=>$c_id,
    'month'=>$month,
    'day'=>$day,
    'content'=>$content,
    'time'=>time(),
      );
    $sql = M('cus_tesu_date')->add($data);
    if($sql){
      $info['stus']=1;
      echo json_encode($info);  
    }else{
      $info['stus']=0;
      echo json_encode($info);  

    }

   }

 
 //app cus 获取特殊日期的信息
   Public function get_cus_tesudate_info(){
     $c_id = I('c_id');
     $sql = M('cus_tesu_date')->where(array('c_id'=>$c_id))->select();
     if($sql){
       echo json_encode($sql); 
     }else{
      $info['stus']=0;
      echo json_encode($info); 
     }

   }

// app cus 获取用户的护理日志
   Public function  get_cus_huli_log(){
    $c_id = I('c_id');
    $content = I('content');
    $data = array(
     'c_id'=>$c_id,
     'type'=>1,
     'content'=>$content,
     'time'=>time(),

      );
     $sql = M('guest_custext')->add($data);
     if($sql){
      $info['stus']=1;
      echo json_encode($info); 
     }else{
      $info['stus']=0;
      echo json_encode($info);
     }
  

   }


// app cus  查询护理日志信息
   Public function get_cus_huli_loginfo(){
    $c_id = I('c_id');
    $sql = M('guest_custext')->where(array('type'=>1))->select();
    if($sql){
      echo json_encode($sql);
    }else{
      $info['stus']=0;
      echo json_encode($info); 
    }

   }



// app cus 获取用户的私密生活
   Public function  get_cus_shenghuo_log(){
    $c_id = I('c_id');
    $content = I('content');
    $data = array(
     'c_id'=>$c_id,
     'type'=>2,
     'content'=>$content,
     'time'=>time(),

      );
     $sql = M('guest_custext')->add($data);
     if($sql){
      $info['stus']=1;
      echo json_encode($info); 
     }else{
      $info['stus']=0;
      echo json_encode($info);
     }
  

   }


// app cus  查询私密生活信息
   Public function get_cus_shenghuo_loginfo(){
    $c_id = I('c_id');
    $sql = M('guest_custext')->where(array('type'=>2))->select();
    if($sql){
      echo json_encode($sql);
    }else{
      $info['stus']=0;
      echo json_encode($info); 
    }

   }



// app cus 获取用户的私密话题
   Public function  get_cus_huati_log(){
    $c_id = I('c_id');
    $content = I('content');
    $data = array(
     'c_id'=>$c_id,
     'type'=>3,
     'content'=>$content,
     'time'=>time(),

      );
     $sql = M('guest_custext')->add($data);
     if($sql){
      $info['stus']=1;
      echo json_encode($info); 
     }else{
      $info['stus']=0;
      echo json_encode($info);
     }
  

   }


// app cus  查询私密话题信息
   Public function get_cus_huati_loginfo(){
    $c_id = I('c_id');
    $sql = M('guest_custext')->where(array('type'=>3))->select();
    if($sql){
      echo json_encode($sql);
    }else{
      $info['stus']=0;
      echo json_encode($info); 
    }

   }


// app guest 新增跟进信息
    Public function get_guest_text(){
     $gid = I('gid');
     $uid = I('uid');
     $content = I('content');
     $data = array(
      'uid'=>$uid,
      'gid'=>$gid,
      'content'=>$content,
      'time'=>time(),
      );
     $sql = M('guest_text')->add($data);
     if($sql){
        $info['stus']=1;
        echo json_encode($info); 
      }else{
          $info['stus']=0;
        echo json_encode($info); 
      }

    }

//app-guest 读取往来信息
  Public function get_guest_textlist(){
  $gid=I('gid');
  $sql = M('guest_text')->where(array('gid'=>$gid))->select();
  if($sql){
 echo json_encode($sql); 

  }else{
       $info['stus']=0;
 echo json_encode($info); 
  }


  }


  //获取我的行程

 Public function get_xingcheng_info(){
   $uid = I('uid');
   $year = I('year');
   $month = I('month');
   $day = I('day');
   
   $sql = M('xingcheng')->where(array('uid'=>$uid,'year'=>$year,'month'=>$month,'day'=>$day))->find();

   if($sql){
echo json_encode($sql);

   }else{
	 $info['stus']=0;
 echo json_encode($info);	

   }


 }

// 提交编辑行程
 Public function post_xingcheng(){
   $uid = I('uid');
   $year = I('year');
   $month = I('month');
   $day = I('day');
   $content = I('content');
 $data=array(
   'uid'=>$uid,
   'year'=>$year,
   'month'=>$month,
   'day'=>$day,
   'content'=>$content,
 	);

 $sql = M('xingcheng')->where(array('uid'=>$uid,'year'=>$year,'month'=>$month,'day'=>$day))->find();
  if($sql){
    $sql1=M('xingcheng')->where(array('id'=>$sql['id']))->save($data);
    if($sql1){
     $info['stus']=1;
 echo json_encode($info);	
    }else{
    	 $info['stus']=0;
 echo json_encode($info);
    }
  }else{
  	$sql2=M('xingcheng')->add($data);
  	if($sql2){
  		 $info['stus']=1;
 echo json_encode($info);
  	}else{
  		 $info['stus']=0;
 echo json_encode($info);
  	}
  }

 }



//员工自己填写档案信息
public function add_user_danan(){
  $uid = I('uid');
    $data = array();
  $data['uid'] = I('uid');
  $data['name'] = I('name');
  $data['sex'] = I('sex');
  $data['brith'] = I('brith');
  $data['jiguan'] = I('jiguan');
  $data['shenfenzheng'] = I('shenfenzheng');
  $data['address'] = I('address');
  $data['home_phone'] = I('home_phone');
  $data['phone'] = I('phone');
  $data['guanxi'] = I('guanxi');
  $data['j_name'] = I('j_name');
  $data['j_job'] = I('j_job');
  $data['j_address'] = I('j_address');
  $data['j_phone'] = I('j_phone');
  $data['huming'] = I('huming');
  $data['bank'] = I('bank');
  $data['bank_card'] = I('bank_card');
  $data['r_time'] = I('r_time');
  $data['r_bumen'] = I('r_bumen');
  $data['n_zhiwei'] = I('n_zhiwei');
  $data['jl_job'] = I('jl_job');
  $data['add_time'] = time();
  
  $open_f = 0;
  
  $info = array();
  
  
  if(in_array('',$data))
  {
    $open_f = 1;
  }
  
  
  if($open_f)
  {
   $info['status'] = 0;
   $info['infoma'] = '请填写完整信息！';
   
   echo json_encode($info);
   exit;
  }
  
   $sql = M('user_danan')->where(array('uid'=>$uid))->find();

   if($sql){
  $sql2 = M('user_danan')->where(array('id'=>$sql['id']))->save($data);
   if($sql2){
   $info['status'] = 1;
   $info['infoma'] = '更新成功！';
   }else{
   $info['status'] = 0;
   $info['infoma'] = '更新失败！';
   }
echo json_encode($info);

   }else{
$sql3 = M('user_danan')->add($data);
   if($sql3){
  $info['status'] = 1;
   $info['infoma'] = '提交成功！';

   }else{
     $info['status'] = 0;
   $info['infoma'] = '提交失败！';
   }
echo json_encode($info);
   }
  
  }
  
  


  //获取档案信息
  Public function get_user_danan(){
  $uid = I('uid');
  $sql = M('user_danan')->where(array('uid'=>$uid))->find();

  if($sql){
   echo json_encode($sql); 
  }else{
    $info['stus'] = 0;
    $info['infoma'] = '未填写档案信息！';
    echo json_encode($info);
  }


  }


  //app guest_cus 获取select 服务人
  Public function get_cus_fw(){
   $gid = I('gid');
   $sql = M('guestworker')->where(array('gid'=>$gid))->select();
   if($sql){
    echo json_encode($sql); 
  }else{
   $info['stus'] = 0; 
   echo json_encode($info); 
  }



  }




//---------------------------------------黄----------------------------------------------------------

//今日业绩排行
  public function get_yeji()
  {
    //获取今日全国业绩
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$yeji = M('order')->field('sum(g_amount) as all_money,sum(if(add_time>'.$beginThismonth.',g_amount,0)) as m_money,sum(if(add_time>'.$beginToday.',g_amount,0)) as t_money')->find();
	
	
	
	$res = json_encode($yeji);
	
    print_r($res);
  }
  
  public function get_dym_new()
  {
    $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$yeji = M('dym_order')->field('sum(amount) as all_money,sum(if(add_time>'.$beginThismonth.',amount,0)) as m_money,sum(if(add_time>'.$beginToday.',amount,0)) as t_money')->find();
	
	$res = json_encode($yeji);
	
    print_r($res);
  }
  
  public function get_dym()
  {
    //获取今日进货店家排行
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$type = I('type');
	
	$where = '';
	
	if('t' == $type)
	{
	  $where = 'add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'add_time>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'add_time >= '.$start_time.' and add_time <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'add_time >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'add_time <'.$end_time;
	  }else
	  {
	    $where =  '1';
	  }
	  
	}
	
	$res = M('dym_order')->alias('d')->field('b.bname,b.bid,sum(amount) as a_my')->join('left join `onethink_bumen` as b on d.bid = b.bid')->where($where)->group('b.bid')->order('a_my desc')->select();
	
	
	$a_mount = M('dym_order')->where($where)->sum('amount');
	
	$ert = array();
	
	$ert['res'] = $res;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  }
  
  
  public function get_jinhuoguest()
  {
    //获取今日进货店家排行
	
	$type = I('type');
	
	$bid = I('bid');
	
	$between = I('between');
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$where = '';
	
	if('t' == $type)
	{
	  $where = 'onethink_order.add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'onethink_order.add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'onethink_order.add_time>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'onethink_order.add_time >= '.$start_time.' and onethink_order.add_time <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'onethink_order.add_time >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'onethink_order.add_time <'.$end_time;
	  }else
	  {
	    $where =  '1';
	  }
	  
	}
	
	if($bid)
	{
	  $where .= ' and onethink_order.bumen_id = '.$bid;
	}
	
	switch($between)
	{
		case 1:
			$have = 'a_my > 0 and a_my <= 100000';
			break;
		case 2:
			$have = 'a_my > 100000 and a_my <= 200000';
			break;
		case 3:
			$have = 'a_my > 200000 and a_my <= 300000';
			break;
		case 4:
			$have = 'a_my > 300000 and a_my <= 500000';
			break;
		case 5:
			$have = 'a_my > 500000';
			break;
			default:
			$have = '';
		
	}
	
	if($have)
	{
	$res = M('order')->field('g.guestname,g.id,sum(g_amount) as a_my')->join('left join `onethink_guest` as g on onethink_order.guest_id = g.id')->where($where)->group('guest_id')->having($have)->order('a_my desc')->select();
	}else
	{
	$res = M('order')->field('g.guestname,g.id,sum(g_amount) as a_my')->join('left join `onethink_guest` as g on onethink_order.guest_id = g.id')->where($where)->group('guest_id')->order('a_my desc')->select();
	}
	
	
	$a_mount = M('order')->where($where)->sum('g_amount');
	
	$ert = array();
	
	$ert['res'] = $res;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  
  }
  
  public function get_juntuan()
  {
    //获取今日进货店家排行
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$type = I('type');
	
	$where = '';
	
	if('t' == $type)
	{
	  $where = 'onethink_order.add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'onethink_order.add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'onethink_order.add_time>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'onethink_order.add_time >= '.$start_time.' and onethink_order.add_time <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'onethink_order.add_time >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'onethink_order.add_time <'.$end_time;
	  }else
	  {
	    $where =  '';
	  }
	  
	}
	
	$res = M('order')->field('onethink_order.tid,sum(g_amount) as a_my,count(distinct guest_id) as g_num')->where($where)->group('onethink_order.tid')->order('a_my desc')->select();
	
	foreach($res as $k => $v)
	{
	  $res[$k]['headimg'] = M('user')->where('tid = '.$v['tid'].' and pid = 2')->getField('headurl');
	}
	
	$a_mount = M('order')->where($where)->sum('g_amount');
	
	$ert = array();
	
	$ert['res'] = $res;
	
	$ert['a_mount'] = $a_mount?$a_mount:0;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  
  }
  
  //获取店家
  public function get_bumen()
  {
    //获取今日进货店家排行
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$type = I('type');
	
	$tid = I('tid');
	
	$where = '';
	
	if('t' == $type)
	{
	  $where = 'onethink_order.add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'onethink_order.add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'onethink_order.add_time>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'onethink_order.add_time >= '.$start_time.' and onethink_order.add_time <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'onethink_order.add_time >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'onethink_order.add_time <'.$end_time;
	  }else
	  {
	    $where =  '1';
	  }
	  
	}
	
	if($tid)
	{
	  $where .=  ' and onethink_order.tid = '.$tid;
	}
	
	//获取部门的业绩排行
	$res = M('order')->field('b.bname,b.bid,sum(g_amount) as a_my,count(distinct guest_id) as g_num')->join('left join `onethink_bumen` as b on onethink_order.bumen_id = b.bid')->where($where)->group('b.bid')->order('a_my desc')->select();
	
	foreach($res as $k => $v)
	{
	  $res[$k]['headimg'] = M('user')->where('bid = '.$v['bid'].' and pid = 3')->getField('headurl');
	}
	
	$a_mount = M('order')->where($where)->sum('g_amount');
	
	$ert = array();
	
	$ert['res'] = $res;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  
  }
  
  public function get_danpin()
  {
    //获取今日进货店家排行
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$type = I('type');
	
	$stype = I('stype','y');
	
	$where = '';
	
	if('t' == $type)
	{
	  $where = 'o.add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'o.add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'o.add_time>= 0 ';
	}
	if('y' == $stype)
	{
	  $res = M('order_goods')->alias('g')->field('g.goodsname,sum(g.g_totalprice) as a_my')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('g.goods_id')->order('a_my desc')->select();
	  
	  $amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g.g_totalprice');
	}elseif('n' == $stype)
	{
	  $res = M('order_goods')->alias('g')->field('g.goodsname,sum(g.num) as a_my')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->group('g.goods_id')->order('a_my desc')->select();
	  $amount = M('order_goods')->alias('g')->join('left join `onethink_order` as o on g.order_id = o.id')->where($where)->sum('g.num');
	}
	
	
	$result['res'] = $res;
	
	$result['amount'] = $amount;
	
	$result = json_encode($result);
	
	print_r($result);
  
  }
  
  
  public function get_dingdan()
  {
    //获取今日进货店家排行
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$type = I('type');
	
	$gid = I('gid');
	
	$bid = I('bid');
	
	$where = '';
	
	if('t' == $type)
	{
	  $where = 'add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'add_time>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'add_time >= '.$start_time.' and add_time <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'add_time >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'add_time <'.$end_time;
	  }else
	  {
	    $where =  '1';
	  }
	  
	}
	
	if($gid)
	{
	  $where .=  ' and guest_id = '.$gid;
	}
	
	if($bid)
	{
	$where .=  ' and bid = '.$bid;
	$res = M('dym_order')->field('id,order_type,order_sn,amount,add_time')->where($where)->select();
	foreach($res as $k=>$v)
	{
 	  $res[$k]['add_time'] = date('Y-m-d H:i:s',$res[$k]['add_time']);
	}
	$a_mount = M('dym_order')->where($where)->sum('amount');
	
	}else
	{
	$res = M('order')->field('id,order_type,order_sn,g_amount,add_time')->where($where)->select();
	foreach($res as $k=>$v)
	{
 	  $res[$k]['add_time'] = date('Y-m-d H:i:s',$res[$k]['add_time']);
	}
	$a_mount = M('order')->where($where)->sum('g_amount');
	
	}
	
	
	$ert = array();
	
	$ert['res'] = $res;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  
  
  }
  
  public function get_order_info()
  {
     $order_id = I('order_id');
	 
	 $order_type = I('order_type');
	 
	 $res = array();
	 
	 if(9 == $order_type)
	 {
	 $res['order_info'] = M('dym_order')->where('id = '.$order_id)->find();
	 
	 $res['order_info']['guestname'] = M('guest')->where('id = '.$res['order_info']['guest_id'])->getField('guestname');
	 
	 $res['order_info']['bname'] = M('bumen')->where('bid = '.$res['order_info']['bid'])->getField('bname');
	 
	 $res['order_info']['add_time'] = date('Y-m-d H:i:s',$res['order_info']['add_time']);
	 
	 $res['order_goods'] = M('dym_goods')->where('order_id = '.$order_id)->select();
	 
	 }else
	 {
	 $res['order_info'] = M('order')->where('id = '.$order_id)->find();
	 
	 $res['order_info']['guestname'] = M('guest')->where('id = '.$res['order_info']['guest_id'])->getField('guestname');
	 
	 $res['order_info']['bname'] = M('bumen')->where('bid = '.$res['order_info']['bumen_id'])->getField('bname');
	 
	 $res['order_info']['add_time'] = date('Y-m-d H:i:s',$res['order_info']['add_time']);
	 
	 $res['order_goods'] = M('order_goods')->where('order_id = '.$order_id)->select();
	 }
	 
	 
	 
	 $res = json_encode($res);
	
	 print_r($res);
  
  }
  
  
  //获取分类下的所有子分类
  
  public function dfddf($id)
  {
   //获取下级的分类id数组
   $c_id = M('bumen')->where('pid = '.$id)->getField('id',true);
   
   $dds = '';
   
   
   //根据下级分类循环获取下下级的分类
   if(count($c_id) > 0)
   {
   
   $dds = implode(',',$c_id);
   
     foreach($c_id as $v)
	 {
	   //判断最后一个字符是否是逗号，是逗号就干他娘的
	   
	   $dds = rtrim($dds,',');
	   
	   $dds .= ','.$this->dfddf($v);
	 }
   }
   
   $dds = rtrim($dds,',');
   
   return $dds;
   
  
  }
  
  
  public function get_huang_new()
  {
    $id = I('id');
  
  $t_arr = array();
  
  $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
  
  $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
  
  $datatime = I('datatime');
  
  if($datatime)
  {
	  $datatimestart = strtotime($datatime);
		  
	  $datatimeend = $datatimestart + 86400;
	  
	  $beginsmonth=mktime(0,0,0,date('m',strtotime($datatime)),1,date('Y'));
  
      $endsmonth=mktime(23,59,59,date('m',strtotime($datatime)),date('t'),date('Y'));
	  
	  $where1 =  's.s_time>'.$datatimestart.' and s.s_time < '.$datatimeend;
	  $where2 =  's.time>'.$datatimestart.' and s.time < '.$datatimeend;
	  $where3 =  's.s_time>'.$beginsmonth.' and s.s_time < '.$endsmonth;
	  $where4 =  's.time>'.$beginsmonth.' and s.time < '.$endsmonth;
  }
  else
  {
      $where1 = 's.s_time>'.$beginToday;
	  $where2 = 's.time>'.$beginToday;
	  $where3 = 's.s_time>'.$beginThismonth;
	  $where4 = 's.time>'.$beginThismonth;
  }
  
  //取得该id的名字和所属成员
  $t_arr[0]['i'] = M('bumen')->where('id = '.$id)->getField('bname');
  
  $t_arr[0]['p'] = M('user')->alias('u')->field('u.id,u.name,u.headurl,sum(if('.$where1.',"1",0)) as nt,sum(if('.$where3.',"1",0)) as nm,sum(if('.$where2.',"1",0)) as mt,sum(if('.$where4.',"1",0)) as mm')
                   ->join('left join `onethink_sigin` as s on u.id = s.uid')->where('bid = '.$id.' and onjob = 1')->group('u.id')->select();
  
  $t_arr[0]['n'] = 0;
  
  //分类下id总数再加上此id是总id
  
  $ss = $this->dfddf($id);
  
  if($ss)
  {
  $ss = $ss.','.$id;
  }else
  {
  $ss = $id;
  }
  
  $where['bid']  = array('in',$ss);
  
  $where['onjob']  = 1;
  
  //获取该分类下的人数
  $t_arr[0]['num'] = M('user')->where($where)->count();
  
  
  if($datatime)
  {
  $t_arr[0]['c'] = $this->ggg_ff_new($id,$t_arr[0]['n'],$datatime);
  }else
  {
  $t_arr[0]['c'] = $this->ggg_ff_new($id,$t_arr[0]['n']);
  }
  
  
  
  echo json_encode($t_arr);   
  
  }
 
 
  public function ggg_ff_new($id,$n,$datatime='')
  {
    //取得该id下所有的子id
  $c_id = M('bumen')->field('id,bname')->where('pid = '.$id)->select();
  
  $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
  
  $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
  
  
  $ff_arr = array();
  
  if($datatime)
  {
      $datatimestart = strtotime($datatime);
		  
	  $datatimeend = $datatimestart + 86400;
	  
	  $beginsmonth=mktime(0,0,0,date('m',strtotime($datatime)),1,date('Y'));
  
      $endsmonth=mktime(23,59,59,date('m',strtotime($datatime)),date('t'),date('Y'));
	  
	  $where1 =  's_time>'.$datatimestart.' and s_time < '.$datatimeend;
	  $where2 =  'time>'.$datatimestart.' and time < '.$datatimeend;
	  $where3 =  's_time>'.$beginsmonth.' and s_time < '.$endsmonth;
	  $where4 =  'time>'.$beginsmonth.' and time < '.$endsmonth;
  }else
  {
      $where1 = 's_time>'.$beginToday;
	  $where2 = 'time>'.$beginToday;
	  $where3 = 's_time>'.$beginThismonth;
	  $where4 = 'time>'.$beginThismonth;
  }
  
  foreach($c_id as $k=>$v)
  {
    $ff_arr[$k]['i'] =  $v['bname'];
	
	$sse = 1;
	
	$Model = M();

    $ff_arr[$k]['p'] = M('user')->field('id,name,username,headurl')->where('bid = '.$v['id'].' and onjob = 1')->select();

	foreach($ff_arr[$k]['p'] as $ko =>$vo)
	{
	  $ff_arr[$k]['p'][$ko]['nt'] = M('sigin')->where($where1.' and uid = '.$vo['id'])->count();
	  
	  $ff_arr[$k]['p'][$ko]['nm'] = M('sigin')->where($where3.' and uid = '.$vo['id'])->count();
	  $ff_arr[$k]['p'][$ko]['mt'] = M('sigin')->where($where2.' and uid = '.$vo['id'])->count();
	  $ff_arr[$k]['p'][$ko]['mm'] = M('sigin')->where($where4.' and uid = '.$vo['id'])->count();
	}
    
    
	$ff_arr[$k]['n'] = 1 + $n;
	
	$ss = $this->dfddf($v['id']);
	
	if($ss)
	  {
	  $ss = $ss.','.$v['id'];
	  }else
	  {
	  $ss = $v['id'];
	  }
  
    $where['bid']  = array('in',$ss);
	
	$where['onjob']  = 1;
  
    //获取该分类下的人数
    $ff_arr[$k]['num'] = M('user')->where($where)->count();
  
    $ff_arr[$k]['c'] = $this->ggg_ff_new($v['id'],$ff_arr[$k]['n']);
		
  }
  
  return $ff_arr;
  
  }
  
  
  public function get_huang_info()
  {
  
    $datatime = I('datatime');
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	$user['num'] = M('user')->where('onjob = 1')->count();
	
	if($datatime)
	{
	  $datatimestart = strtotime($datatime);
	  
	  $datatimeend = $datatimestart + 86400;
	  
	  $user['t_c'] = M('sigin')->where('s_time >= '.$datatimestart.' and s_time < '.$datatimeend)->count('distinct uid');
	  
	  $user['t_b'] = M('sigin')->where('time >= '.$datatimestart.' and time < '.$datatimeend)->count('distinct uid');
	  
	}else
	{
	  $user['t_c'] = M('sigin')->where('s_time >= '.$beginToday)->count('distinct uid');
	
	  $user['t_b'] = M('sigin')->where('time >= '.$beginToday)->count('distinct uid');
	}

	$user['qdl'] =  round($user['t_c']/$user['num'] ,2); 
	
	$user['bbl'] =  round($user['t_b']/$user['num'] ,2);
	
	$res = json_encode($user);
	
    print_r($res);
  }
  
  
  public function save_ruzhi()
  {
    $data = array();
	
	$data['name'] = I('name');
	$data['sex'] = I('sex');
	$data['brith'] = I('brith');
	$data['jiguan'] = I('jiguan');
	$data['shenfenzheng'] = I('shenfenzheng');
	$data['address'] = I('address');
	$data['home_phone'] = I('home_phone');
	$data['phone'] = I('phone');
	$data['guanxi'] = I('guanxi');
	$data['j_name'] = I('j_name');
	$data['j_address'] = I('j_address');
	$data['j_phone'] = I('j_phone');
	$data['huming'] = I('huming');
	$data['bank'] = I('bank');
	$data['bank_card'] = I('bank_card');
	$data['r_time'] = I('r_time');
	$data['r_bumen'] = I('r_bumen');
	$data['n_zhiwei'] = I('n_zhiwei');
	$data['o_zhiwei'] = I('o_zhiwei');
	$data['add_time'] = time();
	
	$open_f = 0;
	
	$info = array();
	
	
	if(in_array('',$data))
	{
	  $open_f = 1;
	}
	
	
	if($open_f)
	{
	 $info['status'] = 0;
	 $info['infoma'] = '请填写完整信息！';
	 
	 echo json_encode($info);
	 exit;
	}
	
	if(M('ruzhi')->add($data))
	{
	 $info['status'] = 1;
	 $info['infoma'] = '提交成功！';
	}else
	{
	 $info['status'] = 0;
	 $info['infoma'] = '提交失败！';
	}
	
	echo json_encode($info);
	exit;
  
  
  }
  
  
  
  
  
  public function save_lizhi()
  {
    $data = array();
	
	$data['name'] = I('name');
	$data['uid'] = I('uid');
	$data['did'] = I('bid');
	$data['pid'] = I('pid');
	$data['in_time'] = I('in_time');
	$data['out_time'] = I('out_time');
	$data['out_reasion'] = I('out_reasion');
	$data['s_name'] = I('s_name');
	$data['sp_name'] = I('sp_name');
	$data['add_time'] = time();
	
	$open_f = 0;
	
	$info = array();
	
	
	if(in_array('',$data))
	{
	  $open_f = 1;
	}
	
	if($open_f)
	{
	 $info['status'] = 0;
	 $info['infoma'] = '请填写完整信息！';
	 
	 echo json_encode($info);
	 exit;
	}
	
	if(M('lizhi')->add($data))
	{
	 $info['status'] = 1;
	 $info['infoma'] = '提交成功！';
	}else
	{
	 $info['status'] = 0;
	 $info['infoma'] = '提交失败！';
	}
	
	echo json_encode($info);
	exit;
  
  
  }
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  //2016.4.6新增军团和部门业绩的分析接口
  
  //所有店家信息
  
  public function get_allguest()
  {
    //获取今日进货店家排行
	
	$type = I('type');
	
	$tid = I('tid');
	
	$bid = I('bid');
	
	if(!$tid)
	{
	  $tid = M('bumen')->where('bid = '.$bid)->getField('tid');
	}
	
	$where = $this->time_where($type);
	
	//根据bid来判断当前要查询的是部门还是军团信息，是部门就列出所有部门信息，是军团就列出军团下部门信息
	
	//军团
	if('' === $bid)
	{
		//取出军团列表
		$list = M('bumen')->field('tid,tname')->group('tid')->select();
		
		
		//根据bid取出相关店家数量和业绩
		foreach($list as $k => $v)
		{
		  $list[$k]['g_num'] = M('bumen')->alias('b')->join('left join `onethink_bid_gid` g on b.bid = g.bid ')->where('b.tid = '.$v['tid'])->count('distinct gid');
		  $list[$k]['a_my'] = M('order')->alias('o')->where($where.' and tid = '.$v['tid'])->sum('g_amount');
		}
		
		$a_mount = M('order')->alias('o')->where($where)->sum('g_amount');
	}
	else if(0 == $bid)
	{

		//取出部门列表
		$list = M('bumen')->field('bid,bname')->where('tid = '.$tid)->select();
		
		
		//根据bid取出相关店家数量和业绩
		foreach($list as $k => $v)
		{
		  $list[$k]['g_num'] = M('bid_gid')->where('bid = '.$v['bid'])->count();
		  $list[$k]['a_my'] = M('order')->alias('o')->where($where.' and bumen_id = '.$v['bid'])->sum('g_amount');
		}
		
		$a_mount = M('order')->alias('o')->where($where.' and tid = '.$tid)->sum('g_amount');
	
	}else{
	
		//获取部门下的所有店家
		
		$list = M('bid_gid')->field('gid')->where('bid = '.$bid)->select();
		
		foreach($list as $k=>$v)
		{
		  $list[$k]['guestname'] = M('guest')->where('id = '.$v['gid'])->getField('guestname');
		  $a_my = M('order')->alias('o')->where($where.' and guest_id = '.$v['gid'].' and bumen_id = '.$bid)->sum('g_amount');
		  $list[$k]['a_my'] = $a_my?$a_my:0;
		}
		
		$a_mount = M('order')->alias('o')->where($where.' and bumen_id = '.$bid)->sum('g_amount');

	}
	
	//对数组按照a_my进行从打到校排序
	
	$ages = array();
    foreach ($list as $v) {
    $ages[] = $v['a_my'];
    }
 
    array_multisort($ages, SORT_DESC, $list);
	
	
	
	$ert = array();
	
	$ert['res'] = $list;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  
  }
  
  public function get_nojuntuan()
  {
     $type = I('type');
	 
	 
	 $where = $this->time_where($type);

	 
	 //根据部门获取未进货的店家
	 
	 $list = M('bumen')->alias('b')->field('b.tid,b.tname,count(*) as a_my')->join('left join `onethink_bid_gid` g on b.bid =g.bid')
	         ->join('left join `onethink_order` o on g.gid = o.guest_id and b.tid = o.tid and o.order_type = 1 and '.$where)
	         ->where('o.id is null and g.id is not null')->group('b.tid')->select();
			 
	 $a_mount = 0;
	 
	 foreach($list as $v)
	 {
	   $a_mount +=$v['a_my'];
	 }
	 
	 $ert = array();
	
	 $ert['res'] = $list;
	
	 $ert['a_mount'] = $a_mount;
	
	 $ert = json_encode($ert);
	
	 print_r($ert);exit;
  
  
  }
  
  
  public function get_nobumen()
  {
     $type = I('type');
	 $tid = I('tid');
	 $bid = I('bid');
	 
	 $where = $this->time_where($type);
	 
	 if(0 == $bid)
	 {
	   $where1 = 'b.tid = '.$tid.' and o.id is null and g.id is not null';
	 }else
	 {
	   $where1 = 'b.tid = '.$tid.' and b.bid = '.$bid.' and o.id is null and g.id is not null';
	 }
	 
	 //根据部门获取未进货的店家
	 
	 $list = M('bumen')->alias('b')->field('b.bid,b.bname,count(*) as a_my')->join('left join `onethink_bid_gid` g on b.bid =g.bid')
	         ->join('left join `onethink_order` o on g.gid = o.guest_id and o.bumen_id = g.bid and o.order_type = 1 and '.$where)
	         ->where($where1)->group('b.bid')->select();
			 
	 $a_mount = 0;
	 
	 foreach($list as $v)
	 {
	   $a_mount +=$v['a_my'];
	 }
	 
	 $ert = array();
	
	 $ert['res'] = $list;
	
	 $ert['a_mount'] = $a_mount;
	
	 $ert = json_encode($ert);
	
	 print_r($ert);exit;
  
  
  }
  
  public function get_noguest()
  {
     //获取指定部门下相应时间段没有进货的客户
	 
	 $type = I('type');
	 $tid = I('tid');
	 $bid = I('bid');
	 
	 $where = $this->time_where($type);
	 
	 $list = M('bid_gid')->alias('b')->field('b.gid,g.guestname')->join('left join `onethink_guest` g on b.gid = g.id ')
	         ->join('left join `onethink_order` o on b.gid = o.guest_id and b.bid = o.bumen_id and o.order_type = 1 and '.$where)
			 ->where('b.bid = '.$bid.' and o.id is null')->select();
  
  
     $ert = array();
	
	 $ert['res'] = $list;
	
	 $ert['a_mount'] = count($list);
	
	 $ert = json_encode($ert);
	
	 print_r($ert);exit;
  
  
  }
  
  public function get_fuwu()
  {
     //获取规定时间规定部门的服务店家列表
	 
	 //有bid取店家列表，无bid取部门服务店家列表
	 
	 //获取数据
	 
	 $type = I('type');
	 
	 $tid = I('tid');
	 
	 $bid = I('bid');
	 
	 $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	 $beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	 
	 $ginfo_type = I('ginfo_type');
	 
	 //设置时间的查询条件
	 $where = '';
	
	 if('t' == $type)
	 {
	   $where = 's.time>='.$beginToday;
	  
	 }elseif('m' == $type)
	 {
	   $where = 's.time>='.$beginThismonth;
	  
	 }elseif('y' == $type)
	 {
	   $where = 's.time>= 0 ';
	 }else
	 {
	   $start_time = strtotime(I('start_time'));
	   $end_time = strtotime(I('end_time'));
	  
	   if($start_time && $end_time)
	   {
	     $where =  's.time >= '.$start_time.' and s.time <'.$end_time;
	   }elseif($start_time)
	   {
	     $where =  's.time >= '.$start_time;
	   }elseif($end_time)
	   {
	     $where =  's.time <'.$end_time;
	   }else
	   {
	     $where =  '1';
	   }
	  
	 }
	 
	 
	 
	 $a_mount = 0;
	 
	 //说明是军团，取部门服务店家列表
	 if('' === $bid)
	 {
	 	$where .= ' and ginfo_type = '.$ginfo_type;
		
		$list = M('bumen')->alias('b')->field('*,count(*) as a_my')->join('left join `onethink_bid_gid` g on b.bid = g.bid ')->join('left join `onethink_sigin` s on g.gid = s.g_id ')
		        ->where($where)->group('b.tid')->order('a_my DESC')->select();
				
		foreach($list as $v)
		{
		  $a_mount += $v['a_my'];
		}
	 
	 }
	 elseif(0 == $bid)
	 {
	    $where .= ' and ginfo_type = '.$ginfo_type.' and b.tid = '.$tid;
		
		$list = M('bumen')->alias('b')->field('*,count(*) as a_my')->join('left join `onethink_bid_gid` g on b.bid = g.bid ')->join('left join `onethink_sigin` s on g.gid = s.g_id ')
		        ->where($where)->group('b.bid')->order('a_my DESC')->select();
				
		foreach($list as $v)
		{
		  $a_mount += $v['a_my'];
		}

	 
	 }
	 //如果有bid取店家列表
	 else
	 {
	   $where .= ' and ginfo_type = '.$ginfo_type.' and g.bid = '.$bid;
	   
	   $list = M('bid_gid')->alias('g')->join('left join `onethink_sigin` s on g.gid = s.g_id ')->where($where)->select();
	   
	   $a_mount = count($list);
	 
	 }
	 
	 $ert = array();
	
	 $ert['res'] = $list;
	
	 $ert['a_mount'] = $a_mount;
	
	 $ert = json_encode($ert);
	
	 print_r($ert);exit;
  
  
  
  }
  
  //获取品牌列表
  public function get_brandlist()
  {
     $list = M('brand')->select();
	 
	 $list = json_encode($list);
	
	 print_r($list);exit;
  }
  
  
  
  //获取单品销量可以按照品牌筛选
  
  public function get_danpin_b()
  {
    //获取今日进货店家排行
	
	$type = I('type');
	
	$tid = I('tid');
	
	$bid = I('bid');
	
	$gid = I('gid');
	
	$brand_id = I('brand_id');
	
	$ginfo_type = I('ginfo_type');
	
	$where = $this->time_where($type);
	
	if($brand_id)
	{
	  $where .= ' and brand_id = '.$brand_id;
	}
	
	if($tid)
	{
	  $where .= ' and tid = '.$tid;
	}
	
	if($bid)
	{
	  $where .= ' and g.bid = '.$bid;
	}
	
	if($gid)
	{
	  $where .= ' and g.guest_id = '.$gid;
	}
	
	if(1 == $ginfo_type)
	{
		$list = M('order_goods')->alias('g')->field('goodsname,format,sum(g_totalprice) as a_my')->join('left join `onethink_order` o on g.order_id = o.id')->where($where)->group('goods_id')->order('a_my DESC')->select();
		//print_r($list);exit;
		$a_mount = M('order_goods')->alias('g')->join('left join `onethink_order` o on g.order_id = o.id')->where($where)->sum('g_totalprice');
	}else
	{
	    $list = M('order_goods')->alias('g')->field('goodsname,format,sum(num) as a_my')->join('left join `onethink_order` o on g.order_id = o.id')->where($where)->group('goods_id')->order('a_my DESC')->select();
		
		$a_mount = M('order_goods')->alias('g')->join('left join `onethink_order` o on g.order_id = o.id')->where($where)->sum('num');
	}
	
	
	$ert = array();
	
	$ert['res'] = $list;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);exit;
  
  }
  
  
  
  //time函数
  private function time_where($type)
  {
    $where = '';
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	if('t' == $type)
	{
	  $where = 'o.add_time>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'o.add_time>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'o.add_time>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'o.add_time >= '.$start_time.' and o.add_time <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'o.add_time >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'o.add_time <'.$end_time;
	  }else
	  {
	    $where =  '1';
	  }
	  
	}
	
	return $where;
  
  
  }
  
  
  
  
  private function time_where1($type)
  {
    $where = '';
	
	$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	$beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
	
	if('t' == $type)
	{
	  $where = 'g.intime>='.$beginToday;
	  
	}elseif('m' == $type)
	{
	  $where = 'g.intime>='.$beginThismonth;
	  
	}elseif('y' == $type)
	{
	  $where = 'g.intime>= 0 ';
	}else
	{
	  $start_time = strtotime(I('start_time'));
	  $end_time = strtotime(I('end_time'));
	  
	  if($start_time && $end_time)
	  {
	    $where =  'g.intime >= '.$start_time.' and g.intime <'.$end_time;
	  }elseif($start_time)
	  {
	    $where =  'g.intime >= '.$start_time;
	  }elseif($end_time)
	  {
	    $where =  'g.intime <'.$end_time;
	  }else
	  {
	    $where =  '1';
	  }
	  
	}
	
	return $where;
  
  
  }
  
  
  public function get_bumenlist()
  {
     $tid = I('tid');
	 
	 $list = M('bumen')->where('tid = '.$tid)->select();
	 
	 $list = json_encode($list);
	
	 print_r($list);exit;

  }
  
  
  public function get_juntuanlist()
  {

	 $list = M('bumen')->group('tid')->select();
	 
	 $list = json_encode($list);
	
	 print_r($list);exit;

  }
  
  public function get_pinpai()
  {
     $tid = I('tid');
	 
	 $bid = I('bid');
	 
	 $type = I('type');
	 
	 $gid = I('gid');
	 
	 $where = $this->time_where($type);
	 
	 if($tid)
	 {
	   $where .= ' and o.tid = '.$tid;
	 }
	 
	 if($bid)
	 {
	   $where .= ' and o.bumen_id = '.$bid;
	 }
	 
	 if($gid)
	 {
	   $where .= ' and g.guest_id = '.$gid;
	 }
	 
	 $brand_list = M('order')->alias('o')->field('g.brand_id,sum(g_totalprice) as a_my')->join('left join `onethink_order_goods` g on o.id = g.order_id ')->where($where)->group('g.brand_id')->order('a_my DESC')->select();
	 
	 //根据brand_list计算占比
	 if($brand_list)
	 {
	   $amount_m = 0;
	   
	   foreach($brand_list as $vo)
	   {
	     $amount_m += $vo['a_my'];
	   }
	   
	   foreach($brand_list as $k=>$v)
	   {
	     $brand_list[$k]['bname'] = M('brand')->where('id = '.$v['brand_id'])->getField('name');
		 $brand_list[$k]['per'] = round($v['a_my']/$amount_m,4)*100;
	   }
	 }
	 
	 $ert = array();
	
	 $ert['res'] = $brand_list;
	
	 $ert['a_mount'] = $amount_m;
	
	 $ert = json_encode($ert);
	
	 print_r($ert);exit;
  
  }
  
  public function get_xifenpinpai()
  {
  	$brand_id = I('brand_id');
	
	$type = I('type');
	
	$tid = I('tid');
	
	$bid = I('bid');
	
	$where = $this->time_where($type);
	
	$where .= ' and g.brand_id = '.$brand_id;
	
	$amount_a = M('order')->alias('o')->join('left join `onethink_order_goods` g on o.id = g.order_id ')->where($where)->sum('g_totalprice');
	
	if($bid)
	{
		
		if(!$tid)
		{
			$tid = M('bumen')->where('bid = '.$bid)->getField('tid');
		}
		
		$amount_t = M('order')->alias('o')->join('left join `onethink_order_goods` g on o.id = g.order_id ')->where($where.' and o.tid = '.$tid)->sum('g_totalprice');
		
		$where .= ' and o.bumen_id = '.$bid;
		
		//获取品牌下军团的占比
		$t_list = M('order')
				->alias('o')
				->field('o.guest_id,o.gname,sum(g_totalprice) as a_my')
				->join('left join `onethink_order_goods` g on o.id = g.order_id ')
				->where($where)
				->group('o.guest_id')
				->order('a_my DESC')
				->select();
		
		$amount = 0;
		if($t_list)
		{
			foreach($t_list as $k=>$v)
			{
				$amount += $v['a_my'];
			}
			
			foreach($t_list as $ko=>$vo)
			{
				$t_list[$ko]['per'] = round($vo['a_my']/$amount,4)*100;//占部门比
				
				$t_list[$ko]['t_per'] = round($vo['a_my']/$amount_t,4)*100;//占部门比
				
				$t_list[$ko]['a_per'] = round($vo['a_my']/$amount_a,4)*100;//占总比
				
			}
		}
	}
	elseif($tid)
	{
		$where .= ' and o.tid = '.$tid;
		
		//获取品牌下军团的占比
		$t_list = M('order')
				->alias('o')
				->field('o.bumen_id,o.bname,sum(g_totalprice) as a_my')
				->join('left join `onethink_order_goods` g on o.id = g.order_id ')
				->where($where)
				->group('o.bumen_id')
				->order('a_my DESC')
				->select();
		
		$amount = 0;
		if($t_list)
		{
			foreach($t_list as $k=>$v)
			{
				$amount += $v['a_my'];
			}
			
			foreach($t_list as $ko=>$vo)
			{
				$t_list[$ko]['per'] = round($vo['a_my']/$amount,4)*100;//占军团比
				
				$t_list[$ko]['t_per'] = round($vo['a_my']/$amount_a,4)*100;//占总比
				
			}
		}	
		
	}else
	{
		//获取品牌下军团的占比
		$t_list = M('order')
				->alias('o')
				->field('o.tid,sum(g_totalprice) as a_my')
				->join('left join `onethink_order_goods` g on o.id = g.order_id ')
				->where($where)
				->group('o.tid')
				->order('a_my DESC')
				->select();
		
		$amount = 0;
		if($t_list)
		{
			foreach($t_list as $k=>$v)
			{
				$amount += $v['a_my'];
				$t_list[$k]['tname'] = M('bumen')->where('tid = '.$v['tid'])->getField('tname');
			}
			
			foreach($t_list as $ko=>$vo)
			{
				$t_list[$ko]['per'] = round($vo['a_my']/$amount,4)*100;
			}
		}
	}
	
		
	
	$ert = array();
	
	$ert['res'] = $t_list;
	
	$ert['a_mount'] = $amount;
	
	$ert = json_encode($ert);
	
	print_r($ert);exit;
  
  }
  
  public function get_quyu()
  {
     $tid = I('tid');
	 
	 $bid = I('bid');
	 
	 $type = I('type');
	 
	 $where = $this->time_where($type);
	 
	 if($tid)
	 {
	   $where .= ' and o.tid = '.$tid;
	 }
	 
	 if($bid)
	 {
	   $where .= ' and o.bumen_id = '.$bid;
	 }
	 
	 $brand_list = M('order')->alias('o')->field('g.province,sum(g_amount) as a_my')->join('left join `onethink_guest` g on o.guest_id = g.id ')->where($where)->group('g.province')->order('a_my DESC')->select();
	 //print_r($brand_list);
	 //根据brand_list计算占比
	 if($brand_list)
	 {
	   $amount_m = 0;
	   
	   foreach($brand_list as $vo)
	   {
	     $amount_m += $vo['a_my'];
	   }
	   
	   foreach($brand_list as $k=>$v)
	   {
		 $brand_list[$k]['per'] = round($v['a_my']/$amount_m,4)*100;
	   }
	 }
	 
	 $ert = array();
	
	 $ert['res'] = $brand_list;
	
	 $ert['a_mount'] = $amount_m;
	
	 $ert = json_encode($ert);
	
	 print_r($ert);exit;
  
  }
  
  
  
  public function get_bumen_list()
  {
	 
	 $tid_list = array();
	 
	 $tid = I('tid');
	 
	 $tid_arr = array(1,2,3,4,5,6,9);
	 
	 if($tid)
	 {
	   $ko =  array_search($tid,$tid_arr);
	   $tid_arr = array($tid);
	 }
	 
	 
	 
	 
	 $tid_name = array('一军团','二军团','三军团','四军团','五军团','六军团','大医美部门');
	 
	 foreach($tid_arr as $k=>$v)
	 {
	
	    $tid_list[$k]['tid'] = $v;
		
		if($tid)
		{
		  $tid_list[$k]['tname'] = $tid_name[$ko];
		}else
		{
		  $tid_list[$k]['tname'] = $tid_name[$k];
		}
		 
		$tid_list[$k]['guihua'] = M('guestplan')->where(array('tid'=>$v))->sum('feat');
		
		$tid_list[$k]['yeji'] = M('order')->where(array('tid'=>$v))->sum('g_amount');
		
		$tid_list[$k]['bumen'] = M('bumen')->field('bid,bname,charge as name')->where('tid = '.$v.' and status=1')->order('sort ASC')->select();
							 	
	 }
	 
	 echo json_encode($tid_list);
  
  
  }
  
  
  public function get_userguest()
  {
     $uid = I('uid');
	 
	 //取出uid对应的客户列表
	 $glist = M('follow')->alias('f')->field('g.guestname,g.id')->join('left join `onethink_guest` g on f.gid = g.id')->where('f.uid = '.$uid.' and g.stus = 1')->select();
	 
	 //根据首字母形成新的数组
	 $g_list = array();
	 
	 $str_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	 
	 if(count($glist) > 0)
	 {
	   foreach($glist as $k =>$v)
	   {
	      //获取名字的第一个大写字母
		  $first_name = $this->getfirstchar($v['guestname']);
		  
		  $ko = array_search($first_name,$str_array);
		  
		  if($ko)
		  {
			$g_list[$ko]['val'][] = $v;
			$g_list[$ko]['keys'] = $str_array[$ko];
			
		  }else
		  {
		    $g_list[26]['val'][] = $v;
			$g_list[26]['keys'] = 'HASH';
		  }

	   }
	 }
	 
	 echo json_encode($g_list);exit;
  
  }
  
  public function get_guestgoods()
  {
     $gid = I('gid');
	 
	 //根据gid取出合作品牌
	 $brand_list = M('branddiscount')->alias('d')->field('b.name,b.id')->join('left join `onethink_brand` b on d.brand_id = b.id')->where('guest_id = '.$gid)->select();
	 
	 $result = array(
	 'info'=>'',
	 'status'=>0,
	 );
	 
	 if($brand_list)
	 {
	  	$result['info'] = $brand_list;
		$result['status'] = 1;
		echo json_encode($result);exit;
	 }else
	 {
	   	$result['info'] = '该客户暂无合作品牌，请联系客服添加合作品牌！';
	   	echo json_encode($result);exit;
	 } 
	 
  }
  
  public function get_brandgoods()
  { 
    $bid = I('bid');
	
	//根据品牌获得商品列表
	$goods_list = M('goods')->where('brand_id = '.$bid)->select();
	
	echo json_encode($goods_list);exit;

  }
  
  public function insertorder()
  {
  	$gid = I('gid');
	$time = time();
	$goods_id_array = I('goods_id');
	$num_array = I('num');
	$brand_id_array = I('brand_id');
	$amount = I('amount');
	$remarks = I('remarks');
	$uid = I('uid');
	$price = I('price');
	$bid = I('bid');
	
	//编辑时要用到
	$id = I('id');
	$order_goods_id = I('order_goods_id');
	
	
	$order_type = 'on' == I('order_type')?1:2;
	
	$result = array(
	'status'=>0,
	'info'=>'',
	);
	
	//把数据写入订单草稿表

	$not_null = 0;
	foreach($goods_id_array as $k => $v)
	{
		//商品存在并且数量大于零至少出现一条
		if($v && $num_array[$k] > 0)
		{
			$not_null = 1;
		}
	}
	  
	if(!$gid || $not_null == 0 || !$uid || !$bid)
	{
		$result['info'] = '请填写完整信息！';
		$this->ajaxReturn($result);
	}

		  
	//暂时设为过账后不允许编辑
	if($id)
	{
		if(1 == M('draft_order')->where('id = '.$id)->getField('status'))
		{
		  	$result['info'] = '此单据已经审核，暂不允许更改！';
			$this->ajaxReturn($result);
		}
	}
		  
		  
	//先写入订单表
	$fuhao = $this->order_fuhao($order_type);
		  
	//获取数据
	$order_data = array(
	'gid'=>$gid,
	'gname'=>M('guest')->where('id = '.$gid)->getField('guestname'),
	'bid'=>$bid,
	'bname'=>M('bumen')->where('bid = '.$bid)->getField('bname'),
	'order_type'=>$order_type,
	'amount'=>$amount*$fuhao,
	'add_time'=>$time,
	'remarks'=>$remarks,
	'uid'=>$uid,
	'uname'=>M('user')->where('id = '.$uid)->getField('name'),
	'status'=>0,
	'id'=>$id,
	);
		  
	//使用replace into 做新增或更新操作
	
	$order_id = M('draft_order')->add($order_data,array(),true);
		  
	if(!$order_id)
	{
		$result['info'] = '插入或更新订单失败！';
		$this->ajaxReturn($result);
	}
		  
	//插入商品表循环商品id
	
	$goods_data = array();
	
	$b_money = 0;
	
	$c_money = 0;
		  
	foreach($goods_id_array as $k=>$v)
	{
		if($num_array[$k] > 0 && $v)
		{	  
		  $goods_data[] = array(
		  'id'=>$order_goods_id[$k],
		  'order_id'=>$order_id,
		  'order_type'=>$order_type,
		  'goods_id'=>$v,
		  'brand_id'=>$brand_id_array[$k],
		  'num'=>$num_array[$k]*$fuhao,
		  'price'=>$price[$k]*$fuhao,
		  'totalprice'=>$price[$k]*$num_array[$k]*$fuhao,
		  );	
		}
	}

	if(count($goods_data)>0)
	{
	
			
		if(M('draft_goods')->addALL($goods_data,array(),true))
		{
			  //判断是新增还是编辑
			  if($id)
			  {
					//获取删掉的商品的id数组
					$id_array = M('draft_goods')->where('order_id = '.$id)->getField('id',true);
					
					if($order_goods_id)
					{
					  $left_array = array_diff($id_array,$order_goods_id);
					}else
					{
					  $left_array = $id_array;
					}
				
				
					//批量删除
					if(count($left_array) >0)
					{
					  $str = implode(',',$left_array);
					  
					  M('draft_goods')->delete($str); 
					  
					}
					$result['status'] = 1;
					$result['info'] = '编辑成功！';
					$this->ajaxReturn($result);die();
						 
			  }else
			  {
				$result['status'] = 1;
				$result['info'] = '下单成功！';
				$this->ajaxReturn($result);die();
			  }
		
		}else
		{
		  $result['info'] = '下单失败！';
		  $this->ajaxReturn($result);
		}
	
	}
	else
	{
	$result['info'] = '无商品下单！';
	$this->ajaxReturn($result);
	
	}
    
  }
  
  
  public function get_user_order()
  {
  	$uid = I('uid');
	
	$status = I('status');
	
	$where = 'uid = '.$uid;
	
	if($status)
	{
	  $status -= 1;
	  $where .= ' and status = '.$status;
	}
	
	//取出用户的订单
	$order_list = M('draft_order')->where($where)->order('add_time DESC')->select();
	//print_r($order_list);exit;
	
	$result = array(
	'info'=>'',
	'status'=>0,
	);
	
	if($order_list)
	{
		foreach($order_list as $k=>$v)
		{
			$order_list[$k]['add_time'] = date('Y-m-d H:i:s',$order_list[$k]['add_time']);
		}
		
		$result['info'] = $order_list;
		$result['status'] = 1;
		echo json_encode($result);exit;
	}else
	{
		$result['info'] = '暂无信息!';
		echo json_encode($result);exit;
	}
	
	
	
	
  }
  
  
  
  public function get_userbumenlist()
  {
    $uid = I('uid');
	
	$tidbid = M('user')->field('tid,bid')->where('id = '.$uid)->find();
	
	if(0 == $tidbid['tid'])
	{
	  $bidlist = M('bumen')->select();
	}
	elseif(0 == $tidbid['bid'])
	{
	  $bidlist = M('bumen')->where('tid = '.$tidbid['tid'])->select();
	}else
	{
	  $bidlist = M('bumen')->where('tid = '.$tidbid['tid'].' and bid = '.$tidbid['bid'])->select();
	}
	//print_r($tidbid);exit;
	$bidlist = json_encode($bidlist);
  
    print_r($bidlist);exit;
  
  }
  
  public function search_guest()
  {
    $tid = I('tid');
	
	$bid = I('bid');
	
	$area = I('area');
	
	$brand = I('brand');
	
	$where = 'bg.id is not null ';
	
	$result = array(
	'info'=>'',
	'status'=>0,
	);
	
	if($tid)
	{
	  $where .= ' and b.tid = '.$tid;
	}
	
	if($bid)
	{
	  $where .= ' and bg.bid = '.$bid;
	}
	
	if($area)
	{
	  $where .= ' and g.province like "'.$area.'"';
	}
	
	if($brand)
	{
	  $where .= ' and bd.brand_id = '.$brand;
	}
	
	$guest_list = M('bumen')->alias('b')->field('g.id,g.guestname')
					->join('left join `onethink_bid_gid` bg on b.bid = bg.bid ')
					->join(' left join `onethink_guest` g on bg.gid = g.id ')
					->join(' left join `onethink_branddiscount` bd on g.id = bd.guest_id ')
					->where($where)->group('bg.gid')->select();
					//print_r($guest_list);exit;
	if($guest_list)
	{
		$result['info'] = $guest_list;
		$result['status'] = 1;
		$this->ajaxReturn($result);
	}
	else
	{
	   	$result['info'] = '暂无信息！';
		$this->ajaxReturn($result);
	}
  
  
  
  
  }
  
  public function get_newguest()
  {
  	$type = I('type');
	
	$tid = I('tid');
	
	$bid = I('bid');
	
	if(!$tid && $bid)
	{
	  $tid = M('bumen')->where('bid = '.$bid)->getField('tid');
	}
	
	$where = $this->time_where($type);
	$where1 = $this->time_where1($type);
	
	
	//根据bid来判断当前要查询的是部门还是军团信息，是部门就列出所有部门信息，是军团就列出军团下部门信息
	
	//军团
	if('' === $bid)
	{
		//取出军团列表
		$list = M('bumen')->field('tid,tname')->group('tid')->select();
		
		$a_mount = M('order')->alias('o')->join('left join `onethink_guest` g on o.guest_id = g.id')->where($where1)->sum('g_amount');
		
		
		
		//根据tid取出相关店家数量和业绩和占比
		foreach($list as $k => $v)
		{
		  $list[$k]['g_num'] = M('bumen')->alias('b')
		  						->join('left join `onethink_bid_gid` bg on b.bid = bg.bid')
								->join('left join `onethink_guest` g on bg.gid = g.id')->where($where1.' and b.tid = '.$v['tid'])->count('distinct bg.gid');
		  $list[$k]['a_my'] = M('order')->alias('o')->join('left join `onethink_guest` g on o.guest_id = g.id')->where($where1.' and o.tid = '.$v['tid'])->sum('g_amount');
		  //部门总业绩
		  $t_amount = M('order')->alias('o')->where($where.' and o.tid = '.$v['tid'])->sum('o.g_amount');
		  $list[$k]['persent'] = round($list[$k]['a_my']/$t_amount,3)*100;
		}
	}
	elseif(0 == $bid)
	{

		//取出部门列表
		$list = M('bumen')->field('bid,bname')->where('tid = '.$tid)->select();
		
		$a_mount = M('order')->alias('o')->join('left join `onethink_guest` g on o.guest_id = g.id')->where($where1.' and o.tid = '.$tid)->sum('g_amount');
		
		
		
		//根据bid取出相关店家数量和业绩和占比
		foreach($list as $k => $v)
		{
		  $list[$k]['g_num'] = M('bid_gid')->alias('bg')->join('left join `onethink_guest` g on bg.gid = g.id')->where($where1.' and bg.bid = '.$v['bid'])->count();
		  $list[$k]['a_my'] = M('order')->alias('o')->join('left join `onethink_guest` g on o.guest_id = g.id')->where($where1.' and bumen_id = '.$v['bid'])->sum('g_amount');
		  //部门总业绩
		  $b_amount = M('order')->alias('o')->where($where.' and o.bumen_id = '.$v['bid'])->sum('o.g_amount');
		  $list[$k]['persent'] = round($list[$k]['a_my']/$b_amount,3)*100;
		}
		
		
		
		
	
	}else{
	
	//获取部门下的所有店家
	
	$list = M('bid_gid')
			->alias('b')
			->field('g.id as gid,g.guestname,sum(o.g_amount) as a_my')
			->join('left join `onethink_guest` g on b.gid = g.id')
			->join('left join `onethink_order` o on b.gid = o.guest_id and o.bumen_id = '.$bid)
			->where($where1.' and b.bid = '.$bid)
			->group('g.id')
			->select();
	//print_r($list);exit;
	
	$a_mount = M('order')->alias('o')->join('left join `onethink_guest` g on o.guest_id = g.id')->where($where1.' and o.bumen_id = '.$bid)->sum('g_amount');
	$b_amount = M('order')->alias('o')->where($where.' and o.bumen_id = '.$bid)->sum('o.g_amount');
	foreach($list as $k=>$v)
	{
		$list[$k]['persent'] = round($v['a_my']/$b_amount,3)*100;
	}

	}
	
	//对数组按照a_my进行从打到校排序
	
	$ages = array();
    foreach ($list as $v) {
    $ages[] = $v['a_my'];
    }
 
    array_multisort($ages, SORT_DESC, $list);
	
	
	
	$ert = array();
	
	$ert['res'] = $list;
	
	$ert['a_mount'] = $a_mount;
	
	$ert = json_encode($ert);
	
	print_r($ert);
  
  
  }
  
  
  public function get_draft_order()
  {
  		$id = I('id');
		
		//获取订单信息
		$order_info = M('draft_order')->where('id = '.$id)->find();
		
		$order_goods = M('draft_goods')->where('order_id = '.$id)->select();
		
		//按品牌分开
		$goods_array = array();
		$order_array = array();
		foreach($order_goods as $k => $v)
		{
			$goods_info = M('goods')->alias('g')->join('left join `onethink_brand` b on g.brand_id = b.id')->where('g.id = '.$v['goods_id'])->find();
			
			$goods_array[$v['brand_id']]['brand_id'] = $v['brand_id'];
			$goods_array[$v['brand_id']]['brand_name'] = $goods_info['name'];
			$goods_array[$v['brand_id']]['goods'][] = array(
			'goods_name'=>$goods_info['goodsname'],
			'price'=>$v['price'],
			'format'=>$goods_info['format'],
			'num'=>$v['num'],
			'goods_id'=>$v['goods_id']
			);
			
			$sn_array[] = $v['gl_id'];
			
		}
		
		$sn_array = array_unique($sn_array);
		
		foreach($sn_array as $v)
		{
			if($sn_array > 0)
			{
				$order_array[] = M('order')->field('id,order_sn,gname,bname,amount,g_amount,order_type,status,FROM_UNIXTIME(add_time,"%Y-%m-%d %h:%i:%s") as addtime')->where('id = '.$v)->find();
			}
		}
		
		$result = array('order_info'=>array(),'order_goods'=>array(),'order_array'=>$order_array);
		
		$result['order_info'] = $order_info;
		
		$result['order_goods'] = array_merge($goods_array);
		
		$this->ajaxReturn($result);
  
  
  
  }
  
  
  
  private function getfirstchar($s0){
		$firstchar_ord=ord(strtoupper($s0{0}));
		if (($firstchar_ord>=65 and $firstchar_ord<=91)or($firstchar_ord>=48 and $firstchar_ord<=57)) return $s0{0};
		$s=iconv("UTF-8","gb2312", $s0);
		$asc=ord($s{0})*256+ord($s{1})-65536;
		if($asc>=-20319 and $asc<=-20284)return "A";
		if($asc>=-20283 and $asc<=-19776)return "B";
		if($asc>=-19775 and $asc<=-19219)return "C";
		if($asc>=-19218 and $asc<=-18711)return "D";
		if($asc>=-18710 and $asc<=-18527)return "E";
		if($asc>=-18526 and $asc<=-18240)return "F";
		if($asc>=-18239 and $asc<=-17923)return "G";
		if($asc>=-17922 and $asc<=-17418)return "H";
		if($asc>=-17417 and $asc<=-16475)return "J";
		if($asc>=-16474 and $asc<=-16213)return "K";
		if($asc>=-16212 and $asc<=-15641)return "L";
		if($asc>=-15640 and $asc<=-15166)return "M";
		if($asc>=-15165 and $asc<=-14923)return "N";
		if($asc>=-14922 and $asc<=-14915)return "O";
		if($asc>=-14914 and $asc<=-14631)return "P";
		if($asc>=-14630 and $asc<=-14150)return "Q";
		if($asc>=-14149 and $asc<=-14091)return "R";
		if($asc>=-14090 and $asc<=-13319)return "S";
		if($asc>=-13318 and $asc<=-12839)return "T";
		if($asc>=-12838 and $asc<=-12557)return "W";
		if($asc>=-12556 and $asc<=-11848)return "X";
		if($asc>=-11847 and $asc<=-11056)return "Y";
		if($asc>=-11055 and $asc<=-10247)return "Z";
		return null;
   } 
   
   
   private function order_fuhao($type)
	{
	  if(1 == $type)
		   {
		     $fuhao = 1;
		   }else
		   {
		     $fuhao = -1;
		   }
		   
		  return  $fuhao;
	}
	
	
	
	
	public function get_ceshi()
	{
		$id = 1;
		
		//取bid列表
		$bid_arr = M('uid_bidarr')->where('uid = '.$id)->getField('bidarr');
		$bid_arr = explode(',',$bid_arr);
		//对列表格式化（取顶级树id）
		$bid_first = array();
		foreach($bid_arr as $k=>$v)
		{
			//判断当前bid是否顶技树
			$pid = M('bumen')->where('id = '.$v)->getField('pid');
			if(!in_array($pid,$bid_arr))
			{
				$bid_first[] = $v;
			}
		}
		
		
		
		$t_arr = array();
		
		foreach($bid_first as $ko=>$vo)
		{
			
			
			$t_arr[$ko]['info'] = M('bumen')->where('id = '.$vo)->find();
			
			$child_arr = $this->get_child_array($vo);
		    array_unshift($child_arr,$vo);
			
			$t_arr[$ko]['feat']['guihua'] = M('guestplan')->where('bid in ('.implode(',',$child_arr).')')->sum('feat');
			
			$t_arr[$ko]['feat']['now'] = M('order')->where('bumen_id in ('.implode(',',$child_arr).')')->sum('g_amount');
			
			$t_arr[$ko]['child'] = M('bumen')->field('id,bname,charge')->where('pid = '.$vo)->select();
		
		}
		
		echo json_encode($t_arr);   
	
	}
	
	
	
	private function get_child_array($array = array())
	{
		$where = is_array($array)?'pid in ('.implode(',',$array).')':'pid ='.$array;
		
		$where .= ' and status = 1';
		
		$new_array = M('bumen')->where($where)->getField('id',true);
		
		$new_array = $new_array?array_merge($new_array,$this->get_child_array($new_array)):array();
		
		return $new_array;
	}
	
	
	public function get_infoaa()
	{
		$start = I('start');
		$end = I('end');
		
		$data[] = array( 
        'id' => 1,//事件id 
        'title' => '标题',//事件标题 
        'start' => date('Y-m-d H:i'),//事件开始时间 
        'end' => date('Y-m-d H:i'),//结束时间 
        'allDay' => 1, //是否为全天事件 
		'info1'=>11,
		'info2'=>33,
        
    	);
		
		echo json_encode($data);
	
	}

  

  
  
  //---------------------------------------黄----------------------------------------------------------
 

  //buttom
}
?>