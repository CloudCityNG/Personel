<?php

function mycommentpopupform($adminqueryid = '') {
        $script = html_writer::script('$(document).ready(function() {
                                    $("#showDialog'.$adminqueryid.'").click(function(){
                                      $("#basicModal'.$adminqueryid.'").dialog({
                                        modal: true,
                                        height: 320,
                                        width: 400
                                      });
                                    });
                                  });
                     form = $("#basicModal'.$adminqueryid.'").find( "form" ).on( "submit", function( event ) {                                     
                                        event.preventDefault();
                                        myformvalidation();
                                       });
                    ');
        return $script;
      }