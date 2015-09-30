
	function mycommentpopupform(queryid){
		$('#basicModal'+queryid).dialog({
		   title: 'Post Comment',
		   modal: true
		});
		$('.ui-dialog').addClass('block_queries_popup');
	}

    //code for the display all comments
   function viewresponses(id){
		$('.student'+id).slideToggle('fast');
		if($('.student'+id).css('display') != 'none') {
			$(this).find('.dataTables_wrapper').css('display', 'block');
		} else {
			$(this).find('.dataTables_wrapper').css('display', 'none');
		}
    }