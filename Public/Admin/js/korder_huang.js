// JavaScript Document
//回车事件绑定调取收货单位
	
	//出入库单据js
	
	function enter_up(obj,ocj)
	{
		var theEvent = window.event || arguments.callee.caller.arguments[0];    
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;    
        if (code == 13) {    
            var url = APP+"/Admin/Ajaxquery/select_information/keywords/"+obj.value+"/type/"+ocj+"/name/"+obj.name;
			
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
				content: url //iframe的url
			});
			$(obj).blur();
            return false;    
        }    
        return true;	
	}
	
	
	

//黄线可开始写表格js

//计算价格
function calculate_money()
 {
   var tr_length = $('#tbody_id tr').length;
   
   var total_num = 0;
   var total_n_num = 0;
   var total_l_num = 0;
  
    
   for(var i=0;i<tr_length;i++)
   {
    //获取信息
	var num = parseInt($('#tbody_id tr').eq(i).find('input').eq(4).val());
	var n_num = $('#tbody_id tr').eq(i).find('input').eq(5).val();
	
	var l_num = accSub(n_num,num);
	
	//计算每行的价格和折扣然后赋值
	$('#tbody_id tr').eq(i).find('input').eq(6).val(l_num);
	$('#tbody_id tr').eq(i).find('span').eq(3).html(l_num);

	//计算总价
	if(num)
	{
	 total_num = accAdd(total_num,num);
	}
	if(n_num)
	{
	 total_n_num = accAdd(total_n_num,n_num);
	}
	
	if(l_num)
	{
	 total_l_num = accAdd(total_l_num,l_num);
	}

   }
   
   //赋总值
   $('#total_num').html(total_num);
   $('#total_num_input').val(total_num);
   $('#total_n_num').html(total_n_num);
   $('#total_n_num_input').val(total_n_num);
   $('#total_l_num').html(total_l_num);
   $('#total_l_num_input').val(total_l_num);
   
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

//增加一行
$('.fa-plus').bind('click',function(){
 var res = '<tr><td class="text-center"><i class="fa fa-trash-o" style="cursor: pointer;"></i></td><td class="text-center">#<span class="paixu">1</span></td><td class="td_huang"><input class="form-control form-huang" type="text"  onkeydown="enter_up(this,&quot;kgoods&quot;)" value="" name="code[]" onblur="check_info(this)"></td><td class="td_huang" style="width: 200px !important;"><input class="form-control form-huang" type="text" name="goodsname[]" value=""  onblur="check_info(this)" onkeydown="enter_up(this,&quot;kgoods&quot;)"/><input type="hidden" name="goods_id[]" value="" /></td><td><span></span><input type="hidden" name="format[]" value="" /></td><td><span></span><input type="hidden" name="num[]" value="" /></td><td class="td_huang"><input class="form-control form-huang" type="text" name="n_num[]" value="" onBlur="calculate_money()"/></td><td><span></span><input type="hidden" name="l_num[]" value="" /></td></tr>';
 $('#tbody_id').append(res);
 
 //重拍序列号
 $('.paixu').each(function(i){
   $(this).html(i+1);
 });
 
 //动态绑定事件
 $(".fa-trash-o").on("click", function() {
		$(this).parent().parent().remove();
	
	 //重拍序列号
	 $('.paixu').each(function(i){
	   $(this).html(i+1);
	 });
	calculate_money(); 
 });
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

 function form_submit()
 {
	  
  $('#xsckd').submit();
 
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
		
            if(i-3>=0) 
			{
			inputs[i-3].focus(); 
			var inputr = inputs[i-3];
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
	
            if(i+3 <inputs.length)
			{
			inputs[i+3].focus(); 
			var inputr = inputs[i+3];
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

