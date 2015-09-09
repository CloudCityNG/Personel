      $( "#pop_logout" ).click(function() {
       $( "#logout" ).toggle( "fold",  500 );
    });
          $(document).click(function (e) {
        if ($(e.target).closest('#logout').length > 0 || $(e.target).closest('#pop_logout').length > 0) return;
        $('#logout').slideUp(200);
    });
       //   $( "#login_button" ).click(function() {
       //$( "#front-login" ).toggle("blind");
       //   });
       
    $('#login_button').click(function (e) {
//  e.preventDefault();      
        $('#front-login').slideToggle(200);
    });
    $(document).click(function (e) {
        if ($(e.target).closest('#front-login').length > 0 || $(e.target).closest('#login_button').length > 0) return;
        $('#front-login').slideUp(200);
    });
$(function() {
	$('#da-slider').cslider({
		autoplay	: true,
		bgincrement	:400
	});
});
 $(document).ready(function(){
             $("#logout").hide();
            $( "#front-login" ).hide();
        $(window).scroll(function(){
            if ($(this).scrollTop() > 100) {
                $('.scrollup').fadeIn();
            } else {
                $('.scrollup').fadeOut();
            }
        }); 
        $('.scrollup').click(function(){
            $("html, body").animate({ scrollTop: 0 }, 1000);
            return false;
        });
 $('[placeholder]').focus(function() {
  var input = $(this);
  if (input.val() == input.attr('placeholder')) {
    input.val('');
    input.removeClass('placeholder');
  }
}).blur(function() {
  var input = $(this);
  if (input.val() == '' || input.val() == input.attr('placeholder')) {
    input.addClass('placeholder');
    input.val(input.attr('placeholder'));
  }
}).blur();
 $('[placeholder]').parents('form').submit(function() {
  $(this).find('[placeholder]').each(function() {
    var input = $(this);
    if (input.val() == input.attr('placeholder')) {
      input.val('');
    }
  })
});
$( "#event_tabs" ).tabs();
$( "#exam_tabs" ).tabs();
$( "#approval_tabs" ).tabs();
$( "#academic_status_tabs" ).tabs();

});