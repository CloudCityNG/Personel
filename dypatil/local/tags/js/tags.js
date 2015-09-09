$(".more_tags").click(function () {
    $(this).text($(this).text() == 'more..' ? 'less..' : 'more..');
    $('.hidden_tags').slideToggle(0);
});