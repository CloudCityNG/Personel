//$( document ).ready(function() {
//    console.log( "ready!" );
//    
//    alert('sdfsdf');
//});

//$('.open_popup_link').magnificPopup({
//  
//
//  items: {
//      src: '<div class="white-popup">Dynamically created popup</div>',
//      type: 'inline'
//  },
//  closeBtnInside: true
//});
//
////setTimeout(popup, 3000); // Setting time 3s to popup login form
////function popup() {
////$("#logindiv").css("display", "block");
////}
//
////$("#commenticon").click(function() {
////$("#formdiv").css("display", "block");
////});


//code for popup

 //$(function() {
 //   var dialog, form;
 //   
 //   function addUser() {
 //     var valid = true;
 //     allFields.removeClass( "ui-state-error" );
 //     
 //     return valid;
 //   }
 //
 //   dialog = $( "#dialog_form" ).dialog({
 //     autoOpen: false,
 //     height: 300,
 //     width: 350,
 //     modal: true,
 //     buttons: {
 //       "Create an account": addUser,
 //       Cancel: function() {
 //         dialog.dialog( "close" );
 //       }
 //     },
 //     close: function() {
 //       form[ 0 ].reset();
 //       allFields.removeClass( "ui-state-error" );
 //     }
 //   });
 //
 //   form = dialog.find( "form" ).on( "submit", function( event ) {
 //     event.preventDefault();
 //     addUser();
 //   });
 //
 //   $( "#create_user" ).on( "click", function() {
 //     alert('Hi this is the alert box');
 //     dialog.dialog( "open" );
 //   });
 // });

//my code

$('.open_popup_link').click(function(){
  alert('Hi this is the alert box');
		$( "#basicModal" ).dialog({
        modal: true,
        height: 300,
        width: 400
    });
	});