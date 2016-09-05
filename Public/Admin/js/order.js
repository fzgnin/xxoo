// JavaScript Document
//回车事件绑定调取收货单位
	
	//出入库单据js
	
	function enter_up(obj,ocj)
	{
		var theEvent = window.event || arguments.callee.caller.arguments[0];    
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;    
        if (code == 13) {    
            
			
			if('goods' == ocj)
			{
				
				if($('#supplier_id').val() == '')
			    {
					layer.msg('请先选择供货商！',{icon:2,time: 3000});
					return false;   
			    }
				
				if($('#guest_id').val() == '')
				{	
					layer.msg('请先选择客户！',{icon:2,time: 3000});
					return false;
				}
				
			}
			
			var gid = $('#guest_id').val();
			
			var url = APP+"/Admin/Ajaxquery/select_information/keywords/"+obj.value+"/type/"+ocj+"/name/"+obj.name+"/guest_id/"+gid;
			
			layer.open({
				type: 2,
				title: '选择相关信息',
				shadeClose: false,
				shade: 0.8,
				shift:5,
				area: ['900px', '560px'],
				success:function()
				{
					$(obj).addClass('data-selected');	
				},
				end:function()
				{
					$(obj).removeClass('data-selected');
				},
				content: url //iframe的url
			});
			$(obj).blur();
            return false;    
        }    
        return true;	
	}
	


//删除一行
$('.fa-trash-o').bind('click', function() {
 $(this).parent().parent().remove();

 //重拍序列号
 $('.paixu').each(function(i){
   $(this).html(i+1);
 });
 calculate_money();
});
  
 //动态添加商品函数
 function add_goods(str)
 {
   //记录总行数
   var tr_length = $('#tbody_id tr').length;

   //删除没有商品编号或者商品全名的行
   for(i=tr_length-1;i>=0;i--)
   {
	 if(!$('#tbody_id tr').eq(i).find('input').eq(0).val() || !$('#tbody_id tr').eq(i).find('input').eq(1).val())
	 {
	   //删除此行
	   $('#tbody_id tr').eq(i).remove();
	 }
   }  
   //插入新增行
   $('#tbody_id').append(str);
   
   //绑定事件
	$('.fa-trash-o').on('click', function() {
	$(this).parent().parent().remove();
	
	//重拍序列号
	$('.paixu').each(function(i){
	$(this).html(i+1);
	});
	calculate_money();
	});
	
	//重拍序列号
	$('.paixu').each(function(i){
	$(this).html(i+1);
	});
		
   //计算现有行数
 }

 function form_submit(oaj,obj,ocj)
 {
	//防止重复点击
	var load_index = layer.load();
	
	//利用ajax来提交表格
	$.ajax({
	type:'post',
	url:oaj,
	data:$('#xsckd').serialize(),
	success:function(data)
	{
		if(data.status)
		{
			if(1 == ocj)
			{
				//再增加
				layer.close(load_index);
				layer.msg(data.info,{icon:1,time: 1000},function(){
					window.location.href = window.location.href;
				});
			}else if(2 == ocj)
			{
				parent.table_order_page(0);
				parent.layer.msg(data.info,{icon:1,time: 1000});
				parent.layer.closeAll('iframe');	
			}
			else
			{
				//跳转
				layer.close(load_index);
				layer.msg(data.info,{icon:1,time: 1000},function(){
					window.location.href = obj;
				});
			}
			
		}else
		{
			layer.close(load_index);
			layer.msg(data.info,{icon:2,time: 3000});
		}
	},
	error:function()
	{
		layer.close(load_index);
		alert('数据传输错误！');
	}
	});
 }

//乘
function accMul(arg1,arg2)   
{   
    var m=0,s1=arg1.toString(),s2=arg2.toString();   
    try{m+=s1.split(".")[1].length}catch(e){}   
    try{m+=s2.split(".")[1].length}catch(e){}   
    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)   
}   
 //除 
function accDiv(arg1,arg2){   
    var t1=0,t2=0,r1,r2;   
    try{t1=arg1.toString().split(".")[1].length}catch(e){}   
    try{t2=arg2.toString().split(".")[1].length}catch(e){}   
    with(Math){   
    r1=Number(arg1.toString().replace(".",""))   
    r2=Number(arg2.toString().replace(".",""))   
    return (r1/r2)*pow(10,t2-t1);   
    }   
}   
//加
function accAdd(arg1,arg2){      
   var r1,r2,m;      
   try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
   try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}     
        m=Math.pow(10,Math.max(r1,r2))  
        return (accMul(arg1,m)+accMul(arg2,m))/m      
}
//减       
function accSub(arg1,arg2){      
   var r1,r2,m;      
   try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
   try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}     
        m=Math.pow(10,Math.max(r1,r2))  
        return (accMul(arg1,m)-accMul(arg2,m))/m      
}   

var inputs=document.getElementById("tbody_id").getElementsByClassName("form-control"); 
var input_tr =$("#tbody_id").find("tr").length; 
var input_len = inputs.length/input_tr;

function keyDown(event) 
{ 
    var focus=document.activeElement; 
    if(!document.getElementById("tbody_id").contains(focus)) return; 
    var event=window.event||event;
    var key=event.keyCode; 
    for(var i=0; i<inputs.length; i++) 
    { 
        if(inputs[i]===focus) break; 
    } 
    switch(key) 
    { 
        case 37: 
		
            if(i>0)
			{
			inputs[i-1].focus();
			var inputr = inputs[i-1];
			setTimeout(function () {
                    inputr.select();
                });
			//inputs[i-1].select();
			} 
			 
            break; 
        case 38: 
		
            if(i-input_len>=0) 
			{
			inputs[i-input_len].focus(); 
			var inputr = inputs[i-input_len];
			setTimeout(function () {
                    inputr.select();
                });
			//inputs[i-6].select();
			}
			
            break; 
        case 39: 
		
            if(i<inputs.length-1)
			{
			inputs[i+1].focus(); 
			var inputr = inputs[i+1];
			setTimeout(function () {
                    inputr.select();
                });
			//inputs[i+1].select();
			} 
			
            break; 
        case 40: 
	
            if(i+input_len <inputs.length)
			{
			inputs[i+input_len].focus(); 
			var inputr = inputs[i+input_len];
			setTimeout(function () {
                    inputr.select();
                });
			//inputs[i+6].select();
			} 
			
            break; 
    } 
}  




function check_info(obj)
{
	obj.value = obj.title;
}



function show_selectinfo(obj)
{
	//形成单位类型的html页面
	var _str = '';
	var theEvent = window.event || arguments.callee.caller.arguments[0];    
	var code = theEvent.keyCode || theEvent.which || theEvent.charCode;    
	if (code == 13) {   

		var s_arr = "{name:'"+obj.name+"',value:'"+obj.value+"'}";
		_str += '<div style="width: 100%; text-align: center; padding: 10px;">';
		_str += '<a href="javascript:;" class="btn_list_1" style="font-size:25px;margin-right:25px;line-height:55px;padding:4px 20px;" onclick="check_gstype('+s_arr+',&quot;guest&quot;)">顾客</a>';
		_str += '<a href="javascript:;" class="btn_list_1" style="font-size:25px;margin-right:25px;line-height:55px;padding:4px 20px;" onclick="check_gstype('+s_arr+',&quot;supplier&quot;)">供应商</a>';
		_str += '<a href="javascript:;" class="btn_list_1" style="font-size:25px;margin-right:25px;line-height:55px;padding:4px 20px;" onclick="check_gstype('+s_arr+',&quot;bumen&quot;)">部门</a>';
		_str += '<a href="javascript:;" class="btn_list_1" style="font-size:25px;margin-right:25px;line-height:55px;padding:4px 20px;" onclick="check_gstype('+s_arr+',&quot;subject&quot;)">项目</a>';
		_str += '</div>';
		
		layer.open({
			type: 1,
			title: '选择单位类型',
			shadeClose: false,
			shade: 0.8,
			shift:5,
			area: ['auto', '130px'],
			content: _str //iframe的url
		});
		
		$(obj).blur();
		return false;    
	}    
	return true;	
}

function check_gstype(oaj,ocj)
{
	
	var url = APP+"/Admin/Ajaxquery/select_information/keywords/"+oaj['value']+"/type/"+ocj+"/name/"+oaj['name'];
	
	if('goods' == ocj)
	{
		if($('#supplier_id').val() == '')
		{
			layer.msg('请先选择供货商！');
			//$('#hhhhyu').focus();	
			return false;   
		}
	}
	layer.open({
		type: 2,
		title: '选择相关信息',
		shadeClose: false,
		shade: 0.8,
		shift:5,
		area: ['900px', '530px'],
		content:url //iframe的url
	});
}

