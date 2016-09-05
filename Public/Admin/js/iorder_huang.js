// JavaScript Document

//黄线可开始写表格js

//计算价格
function calculate_money()
 {
   var tr_length = $('#tbody_id tr').length;
   var s_price = 0;
   var j_price = 0;
   var total_num = 0;
   var total_s_price = 0;
   var total_j_price = 0;
    
   for(var i=0;i<tr_length;i++)
   {
    //获取信息
	var num = parseInt($('#tbody_id tr').eq(i).find('input').eq(4).val());
	var price = $('#tbody_id tr').eq(i).find('input').eq(5).val();
	var averages = $('#tbody_id tr').eq(i).find('input').eq(7).val();
	//var g_zhekou = $('#tbody_id tr').eq(i).find('input').eq(10).val();
	s_price = accMul(num,price);
	j_price = accMul(num,averages);
	//g_z_price = accMul(g_zhekou,price);
	//计算每行的价格和折扣然后赋值
	$('#tbody_id tr').eq(i).find('input').eq(6).val(s_price);
	$('#tbody_id tr').eq(i).find('span').eq(3).html(s_price);
	
	$('#tbody_id tr').eq(i).find('input').eq(8).val(j_price);
	$('#tbody_id tr').eq(i).find('span').eq(5).html(j_price);

	//计算总价
	if(num)
	{
	 total_num = accAdd(total_num,num);
	}
	if(s_price)
	{
	 total_s_price = accAdd(total_s_price,s_price);
	}
	if(j_price)
	{
	 total_j_price = accAdd(total_j_price,j_price);
	}

   }
   
   //赋总值
   $('#total_num').html(total_num);
   $('#total_num_input').val(total_num);
   $('#total_s_price').html(total_s_price);
   $('#total_s_price_input').val(total_s_price);
   $('#total_j_price').html(total_j_price);
   $('#total_j_price_input').val(total_j_price);
   
 }
 

//增加一行
$('.fa-plus').bind('click',function(){
 var res = '<tr>';
 	 res += '<td class="text-center"><i class="fa fa-trash-o" style="cursor: pointer;"></i></td>';	
	 res += '<td class="text-center">#<span class="paixu">1</span></td>';
	 res += '<td class="td_huang"><input class="form-control form-huang" type="text"  onkeydown="enter_up(this,&quot;igoods&quot;)" value="" name="code[]"  onblur="check_info(this)"></td>';
	 res += '<td class="td_huang" style="width: 200px !important;">';
	 res += '<input class="form-control form-huang" type="text" name="goodsname[]" value="" onblur="check_info(this)"  onkeydown="enter_up(this,&quot;igoods&quot;)"/>';
	 res += '<input type="hidden" name="goods_id[]" value="" /></td>';
	 res += '<td><span></span><input type="hidden" name="format[]" value="" /></td>';
	 res += '<td class="td_huang"><input class="form-control form-huang" type="text" name="num[]" value="" onBlur="calculate_money()"/></td>';
	 res += '<td><span></span><input type="hidden" name="price[]" value="" /></td>';
	 res += '<td><span></span><input type="hidden" name="price_total[]" value="" /></td>';
	 res += '<td><span></span><input type="hidden" name="c_price[]" value="" /></td>';
	 res += '<td><span></span><input type="hidden" name="c_price_total[]" value="" /></td></tr>';
       
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


function check_info(obj)
{
	obj.value = obj.title;
}

