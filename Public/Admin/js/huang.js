// JavaScript Document

//更改排序方式
//table_order_page(1);
	//更改排序方式
	function table_order(obj)
	{
	  
	  
	  //获取分页的排序条件
	  var filter_list = getCookie('SEARCH_INFO');
	  
	  
	  if(filter_list)
	  {
		  filter_list =  JSON.parse(filter_list);
		  
		  //更改分页的排序方式		  
		  filter_list['order_by'] = obj;
		  
		  if(filter_list['sort_by'] == 'ASC')
		  {
		    filter_list['sort_by'] = 'DESC';
		  }else
		  {
		    filter_list['sort_by'] = 'ASC';
		  }
		  
		  //获取上方筛选信息
		  $('#select_value_id .ajax_search_btn_input').each(function()
		  {
			if($(this).val())
			{
			  filter_list[$(this).attr('name')] = $(this).val();
			}else
			{
			  filter_list[$(this).attr('name')] = '';
			}
		  }); 
		  
		  //获取下方筛选信息
		  $('#select_value_id_child .ajax_search_btn_input').each(function()
		  {
			if($(this).val())
			{
			  filter_list[$(this).attr('name')] = $(this).val();
			}
			else
			{
			  filter_list[$(this).attr('name')] = '';
			}
		  }); 
		  
		  //获取页数
		  if($('#page_num').val())
		  {
			  filter_list['page_num'] = $('#page_num').val();
		  }else
		  {
			  filter_list['page_num'] = 10;
		  }
	  }
	  
	  $.ajax({
	  type:'POST',
	  url:url,
	  data:filter_list,
	  success:function(data)
	  {
	   $('#guest_list').html(data.info);
	  }

	  });	
	}
	
	//分页ajax效果
	function table_order_page(obj,ocj,odj)
	{
	  //获取cookie存储的查询条件--后期有待改进加密
	  var filter_list = getCookie('SEARCH_INFO');
	  
	   if(filter_list)
	   {
	      filter_list =  JSON.parse(filter_list);
		  if(obj != 0)
		  {
		   filter_list['p'] = obj;
		  }
		  
		  //获取上方筛选信息
		  $('#select_value_id .ajax_search_btn_input').each(function()
		  {
			if($(this).val())
			{
			  filter_list[$(this).attr('name')] = $(this).val();
			}else
			{
			  filter_list[$(this).attr('name')] = '';
			}
		  }); 
		  
		  //获取下方筛选信息
		  $('#select_value_id_child .ajax_search_btn_input').each(function()
		  {
			if($(this).val())
			{
			  filter_list[$(this).attr('name')] = $(this).val();
			}
			else
			{
			  filter_list[$(this).attr('name')] = '';
			}
		  }); 
		  
		  //获取页数
		  if($('#page_num').val())
		  {
			  filter_list['page_num'] = $('#page_num').val();
		  }else
		  {
			  filter_list['page_num'] = 10;
		  }
		  
		  //获取点击得来的信息
		  if(ocj)
		  {
		    filter_list[ocj] = odj;
		  }
		  
		  //根据time_type变换时间颜色
		  $('#select_value_id .btn').css('background-color','#4bbd00');
		  
		  switch(filter_list['time_type'])
			{
			case 'year':
			  $('#select_value_id a').eq(0).css('background-color','#233b13');
			  break;
			case 'mounth':
			  $('#select_value_id a').eq(1).css('background-color','#233b13');
			  break;
			case 'day':
			  $('#select_value_id a').eq(2).css('background-color','#233b13');
			  break;
			case 'stage':
			  $('#select_value_id a').eq(3).css('background-color','#233b13');
			  break;
			default:
 
			}
		  
	   }
	   
	  //判断是否有高级查询
	  
	  if($('#select_model_form').serialize())
	  {
		  //赋值页数
		  if(obj != 0)
		  {
		   $('#select_p').val(obj);
		  }
		  
		  //赋值分页数量
		  if(filter_list['page_num'])
		  {
			  $('#select_page_num').val(filter_list['page_num']);
		  }
		  
		  var send_str = $('#select_model_form').serialize();
		  
	  }else
	  {
		  var send_str = filter_list; 
	  }

	  $.ajax({
	  type:'POST',
	  url:url,
	  data:send_str,
	  success:function(data)
	  {
	   $('#guest_list').html(data.info);
	  }
	  });
	
	}
	

	//获取cookie的值
	function getCookie(name)
	{
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
	if(arr=document.cookie.match(reg))
	return unescape(arr[2]);
	else
	return null;
	}
	
	function setCookie(name,value)
	{
		var Days = 30;
		var exp = new Date();
		exp.setTime(exp.getTime() + Days*24*60*60*1000);
		document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
	}
	
	
	/**********黄 下面是弹出框的js************/
		
    function enter_up(obj,ocj)
	{
		var theEvent = window.event || arguments.callee.caller.arguments[0];    
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;    
        if (code == 13) {    
            var url = APP+"/Admin/Ajaxquery/select_information/keywords/"+obj.value+"/type/"+ocj+"/bid/"+obj.getAttribute("data-bid");
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
	
	function check_info(obj)
	{
	  if(obj.value != obj.title)
	  {
	    obj.value = '';
		$(obj).parent().find('input').eq(1).val('');
	  }
	
	}
	
	function delete_this(obj)
	{
	  
	  //分两步第一步去除cookie的值，第二步去除
	  
	  //第一步
	  clean_timetype(obj);
	  
	  //第二步
	  
	  $('#'+obj).parent().find('input').eq(0).val('');
	  $('#'+obj).parent().find('input').eq(1).val('');
  
	  table_order_page(1);  
	}
	
	
	//黄线可开始写业绩排行js
	
	
	//点击今年跳转时间并统计数据
	
	function change_times(obj)
	{
	  //先给时间框赋值
	  
	  //js拼接出今年的开始时间
	  
	  var date =new Date();
	  
	  var year = date.getFullYear();
	  
	  var mounth = date.getMonth()+1;
	  
	  if(mounth <= 9)
	  {
	    mounth = '0'+mounth;
	  }
	  
	  var day = date.getDate();
	  if(day <= 9)
	  {
		  day = '0'+day;
	  }
	  var hours = date.getHours();
	  
	  var minutes = date.getMinutes();	  
	  
	  var this_year = year+'-01-01';
	  
	  var this_mounth = year+'-'+mounth+'-01';
	  
	  var this_day = year+'-'+mounth+'-'+day;
	  
	  var now_time = GetDateStr(1);
	  
	  $('#select_value_id .btn').css('background-color','#4bbd00');

	  switch(obj)
		{
		case 'year':
		  $("input[name='begin_time']").val(this_year);
	      $('#select_value_id a').eq(0).css('background-color','#233b13');
		  break;
		case 'mounth':
		  $("input[name='begin_time']").val(this_mounth);
	      $('#select_value_id a').eq(1).css('background-color','#233b13');
		  break;
		case 'day':
		  $("input[name='begin_time']").val(this_day);
	      $('#select_value_id a').eq(2).css('background-color','#233b13');
		  break;
		case 'stage':
		  $("input[name='begin_time']").val($('#benqi').val());
	      $('#select_value_id a').eq(3).css('background-color','#233b13');
		  break;
		default:
		  $("input[name='begin_time']").val(this_day);
	      
		}
		
	  $("input[name='end_time']").val(this_day);
	  
	  $('#select_time_type').val(obj);
	  
	  if('stage' == obj)
	  {
		 var benqi_unix = Date.parse(new Date($('#benqi').val()))/1000;//本期的时间格式
	     var today_unix = Date.parse(new Date(this_day))/1000;//当天的时间格式
		 
		 if(benqi_unix >= today_unix)
		  {
			$("input[name='end_time']").val($('#benqi').val());  
		  }
	  }

	  
	  //执行搜索函数
	  
	  table_order_page(1,'time_type',obj);
	}
	
	//查看订单
	function show_order(obj,oaj)
	{
		//iframe层
		if(oaj)
		{
			switch(oaj)
			{
				case 1:
				case 2:
				var url = APP+"/Admin/Order/order_info/id/"+obj;
				break;
				case 3:
				case 4:
				var url = APP+"/Admin/Stock/order_info/id/"+obj;
				break;
				case 7:
				case 8:
				var url = APP+"/Admin/Inventory/order_info/id/"+obj;
				break;
				case 10:
				case 11:
				var url = APP+"/Admin/Finance/order_info/id/"+obj;
				break;
				case 12:
				case 13:
				var url = APP+"/Admin/OtherFinance/order_info/id/"+obj;
				break;
				case 14:
				var url = APP+"/Admin/Giro/order_info/id/"+obj;
				break;
				default:
				
				var url = APP+"/Admin/"+oaj+"/order_info/id/"+obj;
				
				}
			
			
		}else
		{
			var url = URL_JS+"/order_info/id/"+obj;
		}
	  
		layer.open({
			type: 2,
			title: '查看订单详细',
			shadeClose: false,
			shade: 0.8,
			shift:5,
			area: ['1500px', '800px'],
			content: url //iframe的url
		}); 
	}
	
	
	
	//查看订单
	function edit_order(obj,oaj)
	{
		//iframe层
		if(oaj)
		{
			switch(oaj)
			{
				case 1:
				case 2:
				var url = APP+"/Admin/Order/edit/id/"+obj;
				break;
				case 3:
				case 4:
				var url = APP+"/Admin/Stock/edit/id/"+obj;
				break;
				case 7:
				case 8:
				var url = APP+"/Admin/Inventory/edit/id/"+obj;
				break;
				case 10:
				case 11:
				var url = APP+"/Admin/Finance/edit/id/"+obj;
				break;
				case 12:
				case 13:
				var url = APP+"/Admin/OtherFinance/edit/id/"+obj;
				break;
				case 14:
				var url = APP+"/Admin/Giro/edit/id/"+obj;
				break;
				default:
				
				var url = APP+"/Admin/"+oaj+"/edit/id/"+obj;
				
				}
			
			
		}else
		{
			var url = URL_JS+"/edit/id/"+obj;
		}
	  
		layer.open({
			type: 2,
			title: '查看订单详细',
			shadeClose: false,
			shade: 0.8,
			shift:5,
			area: ['1500px', '800px'],
			content: url //iframe的url
		}); 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//删除单据
	function delete_order(obj,oaj)
	{
		//iframe层
		  
		  if(oaj)
			{
				var url = APP+"/Admin/"+oaj+"/delete/id/"+obj;
			}else
			{
				var url = URL_JS+"/delete/id/"+obj;
			}
		  
			layer.confirm('确定删除吗？',function(index)
			  {
				$.ajax({
				type:'POST',
				data:{'id':obj},
				url:url,
				success:function(data)
					{
					  if(data.status)
					  {
						  layer.msg(data.info,{icon:1,time: 1000});
						  table_order_page(0);
					  }else
					  {
					      layer.msg(data.info,{icon:2,time: 3000});
					  }
					}					
				});	  
			  })
	}
	
	//过账操作
	function posting_order(oaj,obj,ocj)
	{
	   var url = APP+'/Admin/Posting/posting/id/'+oaj+'/order_type/'+obj+'/post_type/'+ocj;
	   
	   if(1 == ocj)
	   {
		   var p_title = '过账操作';
	   }else
	   {
		   var p_title = '反过账操作';
	   }
	   
	   layer.open({
			type: 2,
			title: p_title,
			shadeClose: false,
			shade: 0.8,
			shift:5,
			area: ['1500px', '800px'],
			content: url //iframe的url
		});
	
	
	
	}
	
	//过账写入
	function  posting_insert(obj,oaj,ocj)
	{
	  var url = APP+'/Admin/Posting/posting_insert';
	  
	  layer.confirm('确定要过账吗？',function(index)
				  {
					$.ajax({
					type:'POST',
					data:{'id':obj,'order_type':oaj,'forminfo':ocj},
					url:url,
					success:function(data)
						{
						  if(data.status)
						  {
							  layer.closeAll();
							  layer.msg(data.info,{icon:1,time: 1000});
							  table_order_page(0);
							  
						  }else
						  {
							  layer.msg(data.info,{icon:2,time: 3000});
						  }
						}					
					});	  
				  })
	
	}
	
	//反过账
	function  backoff_insert(obj,oaj)
	{
	  var url = APP+'/Admin/Posting/backoff_insert';
	  
	  layer.confirm('确定要反过账吗？',function(index)
				  {
					$.ajax({
					type:'POST',
					data:{'id':obj,'order_type':oaj},
					url:url,
					success:function(data)
						{
						  if(data.status)
						  {
							  layer.closeAll();
							  layer.msg(data.info,{icon:1,time: 1000});
							  table_order_page(0);
							  
						  }else
						  {
							  layer.msg(data.info,{icon:2,time: 3000});
						  }
						}					
					});	  
				  })
	
	}
	
	//回调函数
	function call_back()
	{
	 table_order_page(1);
	}
	
	//获取明天的日期格式
	function GetDateStr(AddDayCount) {
		var dd = new Date();
		dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
		var y = dd.getFullYear();
		var m = dd.getMonth()+1;//获取当前月份的日期
		if(m <= 9)
	    {
	      m = '0'+m;
	    }
		var d = dd.getDate();
		return y+"-"+m+"-"+d;
    }
	
	
	
	//清除时间格式
	function clean_timetype(obj)
	{
		var filter_list =  JSON.parse(getCookie('SEARCH_INFO'));
	  
	    filter_list[obj] = '';

	    setCookie('SEARCH_INFO',JSON.stringify(filter_list));
		
	}
	
	function clean_timetype_new(obj)
	{
		//时间赋值
		if('begin_time' == obj.name)
		{
			$('#search_begintime_id').val(obj.value);
		}
		else if('end_time' == obj.name)
		{
			$('#search_endtime_id').val(obj.value);	
		}
		//去除选中效果
		$('#select_time_type').val('');
		
	}
	
	function delete_select(obj)
	{
		
		switch(obj)
		{
			case 'order_type':
			$('#search_model select[name="'+obj+'"]').val('');
			break;
			case 'status':
			$('#search_model select[name="'+obj+'"]').val('');
			break;
			default:
			$('#search_model input[name="'+obj+'"]').val('');
		}
		
		table_order_page(1);
		
	}
	
	
	function show_select()
	{
	  var str = $('#search_model').html();
	  
	  layer.open({
		  type: 1,
		  title:'高级筛选查询条件',
		  closeBtn: 0,
		  content: $('#search_model'),
		  btn:'确认',
		  area: '330px',
		  yes: function(index, layero){
		  
			//设置为第一页
			$('#select_p').val(1);
			
			var form_value = $('#select_model_form').serialize();
	
				$.ajax({
				  type:'POST',
				  url:url,
				  data:form_value,
				  success:function(data)
				  {
				   $('#guest_list').html(data.info);
				  }
			
				});
			
			layer.close(index); //如果设定了yes回调，需进行手工关闭
		  }
		});
		
	}
	
	function select_all(obj)
	{
		$(".ids").prop("checked", obj.checked);
	}
	
	
	
	function posting_all()
	{
		//获取被选中的checkbox
		var str = '';
		$('input[name="id[]"]').each(function(){
			if($(this).prop('checked'))
			{
				str += $(this).val()+'-'+$(this).attr('data-type')+',';	
			}
		});
		
		//获取批量操作的方式
		var post_type = $('#batch_id').val();
		
		//如果有就开始过账操作
		if(str)
		{
			layer.confirm('确定要批量操作吗？',function(index)
				  {
					var o_c = layer.load(2, {shade: [0.1,'#000']});
					$.ajax({
					type:'POST',
					url:APP+'/Admin/Posting/posting_all',
					data:{'str':str,'post_type':post_type},
					success:function(data)
						{
						  layer.close(o_c);
						  if(data.status)
						  {
							  layer.msg(data.info,{icon:1,time:3000});
							  table_order_page(0);
							  
						  }else
						  {
							  layer.msg(data.info,{icon:2,time:3000});
						  }
						},
						error:function()
						{
							alert('网络传输错误，请稍后再试！');
							layer.close(o_c);
						}					
					});	  
			})
			
			
		}else
		{
			layer.msg('请选择要操作的订单！',{icon:2,time:3000});
		}
		
	}