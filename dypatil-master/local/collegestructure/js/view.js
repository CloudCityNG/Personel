//slides the element with class "menu_body" when paragraph with class "menu_head" is clicked
$(document).ready(function () {
    //$( ".menu_body:first" ).css( "display", "block" );
    //$( ".summary:first" ).css( "display", "block" );
    $("#firstpane p.menu_head").click(function ()
    {
        $(this).css({backgroundImage: "url(pix/t/expanded.png)"}).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
        $(this).siblings().css({backgroundImage: "url(pix/t/collapsed.png)"});
    });
    $(".menu_head").mouseover(function () {
        $(this).addClass("color_white");
    })
            .mouseout(function () {
                $(this).removeClass("color_white");
            });
});