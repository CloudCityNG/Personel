$(document).ready(function ()
{
    $("#programs > li > a").not(":first").find("+ ul").slideUp(1);
    $("#programs > li > a").click(function ()
    {
        if ($(this).find("+ ul").is(':visible')) {
            $(this).find("+ ul").slideUp("slow");
            $(this).removeClass('expanded');
            $(this).addClass('collapsed');
        } else {
            $(this).find("+ ul").slideDown("slow");
            $(this).removeClass('collapsed');
            $(this).addClass('expanded');
        }
        
    });
});


$(document).ready(function ()
{
    $("#course > li > a ").not(":first").find("+ ul").slideUp(1);
    $("#course > li > a ").click(function ()
    {
        if ($(this).find("+ ul").is(':visible')) {
            $(this).find("+ ul").slideUp("slow");
            $(this).removeClass('expanded');
            $(this).addClass('collapsed');
        } else {
            $(this).find("+ ul").slideDown("slow");
            $(this).removeClass('collapsed');
            $(this).addClass('expanded');
        }
        //$(this).find("+ ul").slideToggle("slow");
    });
});