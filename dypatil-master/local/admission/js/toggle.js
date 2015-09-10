$(document).ready(function()
{  
  $("#programs > li > a").not(":first").find("+ ul").slideUp(1);
  $("#programs > li > a").click(function()
  {            
    $(this).find("+ ul").toggle("slow");
  });
});