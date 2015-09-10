$(document).ready(function ()
{
    //console.log('hihello');
    $(".semestercourse > a").click(function ()
    {
        //console.log('hellohi');
        //$(this).next('div.coursedescription').slideToggle();
        if ($(this).next('div.coursedescription').is(':visible')) {
            $(this).next('div.coursedescription').slideUp("slow");
            $(this).removeClass('expanded');
            $(this).addClass('collapsed');
        } else {
            $(this).next('div.coursedescription').slideDown("slow");
            $(this).removeClass('collapsed');
            $(this).addClass('expanded');
        }
    });
});
