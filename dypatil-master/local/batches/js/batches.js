function activate_submit(cid,id) {
    $("."+id).on("select2:select",function(){
	$('form#'+cid+'  input[type="submit"]').removeAttr('disabled');
    });
        $("."+id).on("select2:unselect",function(e){
	if($('.'+id).val() === null){
	$('form#'+cid+'  input[type="submit"]').attr("disabled",true);
	}
    });
	 $("."+id).on("select2:change",function(e){
	if($('.'+id).val() === null){
	$('form#'+cid+'  input[type="submit"]').attr("disabled",true);
	}
    });
}

function display_users(batchid, path) {
    costcenterid = $('#select_costcenteruser'+batchid).val();
    $.ajax({url:path+"/local/batches/users.php?batchid="+batchid+"&costcenterid="+costcenterid,
     beforeSend: function(){
     },
     success:function(result){
	var output = '';
	$.each($.parseJSON(result), function(idx, obj) {
	    output += '<option value='+obj.id+'>'+obj.name+'</option>';
	});
	$(".assign-user-select"+batchid).html(output);
     },
     cache: false,dataType: "html"});
}

function display_courses(batchid, path) {
    costcenterid = $('#select_costcentercourse'+batchid).val();
    $.ajax({url:path+"/local/batches/courses.php?batchid="+batchid+"&costcenterid="+costcenterid,
     beforeSend: function(){
     },
     success:function(result){
	var output = '';
	$.each($.parseJSON(result), function(idx, obj) {
	    output += '<option value='+obj.id+'>'+obj.name+'</option>';
	});
	$(".assign-course-select"+batchid).html(output);
     },
     cache: false,dataType: "html"});
}

function batches_view(batchid,containerid) {
    $(".assign-course-select"+batchid).select2();
    $(".assign-cost-select"+batchid).select2();
    $(".assign-user-select"+batchid).select2();

    $('#batch_assign_courses'+batchid).on('click',function(){
	$('#batch_assign_courses'+batchid).removeClass("active");
	$('#batch_assigned_courses_view'+batchid).hide();
	$('#batch_assigned_users_view'+batchid).hide();
	$('#batch_assign_users_form'+batchid).hide();
       
	if($('#batch_assign_courses_form'+batchid).css('display')!='none'){
	    $('#batch_assign_courses'+batchid).addClass("active");
	    $('#batch_assign_users'+batchid).removeClass("active");
	    $('#batch_assigned_courses'+batchid).removeClass("active");
	    $('#batch_assigned_users'+batchid).removeClass("active");
	}
    });
    $('#batch_assigned_courses'+batchid).on('click',function(){
	$('#batch_assigned_courses'+batchid).removeClass("active");
	$('#batch_assign_users_form'+batchid).hide();
	$('#batch_assigned_users_view'+batchid).hide();
	$('#batch_assign_courses_form'+batchid).hide();
	if($('#batch_assigned_courses_view'+batchid).css('display')!='none'){
	    $('#batch_assigned_courses'+batchid).addClass("active");
	    $('#batch_assign_users'+batchid).removeClass("active");
	    $('#batch_assigned_users'+batchid).removeClass("active");
	    $('#batch_assign_courses'+batchid).removeClass("active");
	}
    });
    $('#batch_assigned_users'+batchid).on('click',function(){
	$('#batch_assigned_users'+batchid).removeClass("active");
	$('#batch_assign_courses_form'+batchid).hide();
	$('#batch_assigned_courses_view'+batchid).hide();
	$('#batch_assign_users_form'+batchid).hide();
	if($('#batch_assigned_users_view'+batchid).css('display')!='none'){
	    $('#batch_assigned_users'+batchid).addClass("active");
	    $('#batch_assign_courses'+batchid).removeClass("active");
	    $('#batch_assigned_courses'+batchid).removeClass("active");
	    $('#batch_assign_users'+batchid).removeClass("active");
	}
    });
    $('#batch_assign_users'+batchid).on('click',function(){
	$('#batch_assign_users'+batchid).removeClass("active");
	$('#batch_assign_courses_form'+batchid).hide();
	$('#batch_assigned_courses_view'+batchid).hide();
	$('#batch_assigned_users_view'+batchid).hide();
	if($('#batch_assign_users_form'+batchid).css('display')!='none'){
	    $('#batch_assign_users'+batchid).addClass("active");
	    $('#batch_assigned_users'+batchid).removeClass("active");
	    $('#batch_assigned_courses'+batchid).removeClass("active");
	    $('#batch_assign_courses'+batchid).removeClass("active");
	}
    });
//    $('#clpemp'+batchid).on('click',function(){
//	$('#assigncourses'+batchid).removeClass("active");
//	$('#dialogcourse'+batchid).hide();
//	$('#dialogcourse_view'+batchid).hide();
//	$('#assignusers_assign'+batchid).hide();
//	$('#assignedusers'+batchid).hide();
//	if($('#assignedusers'+batchid).css('display')!='none'){
//	    $('#clpemp'+batchid).addClass("active");
//	}
//    });
    YUI().use('node','transition', function(Y) {
	node = Y.one("#"+containerid+batchid+"");
            node.toggleView();
    });
}