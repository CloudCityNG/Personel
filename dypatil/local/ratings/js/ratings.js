function updatevalues(path, LikeArea, Activity, Item, Course, Action) {

    LikeDisable = '<img src="' + path + '/local/ratings/pix/like_disable.png" title="You Liked it" />';
    UnlikeDisable = '<img src="' + path + '/local/ratings/pix/unlike_disable.png" title="You Disliked it" />';
    $.ajax({url: path + "/local/ratings/index.php?likearea=" + LikeArea + "&activity=" + Activity + "&item=" + Item + "&action=" + Action + "&course=" + Course,
        beforeSend: function () {
            if (Action) {
                $('.count_unlikearea_' + Item).html('<img src="ajax-loader.gif" alt="loading.." style="margin:0 auto;" />');
            } else {
                $('.count_likearea_' + Item).html('<img src="ajax-loader.gif" alt="loading.." style="margin:0 auto;" />');
            }
        },
        success: function (result) {
            if (Action) {
                $("#label_unlike_" + Item).html(UnlikeDisable);
                $(".count_unlikearea_" + Item).html(result);
            } else {
                $("#label_like_" + Item).html(LikeDisable);
                $(".count_likearea_" + Item).html(result);
            }
        },
        cache: false, dataType: "html"});
}

function DeleteComment(CommentId, Course, Activity, Item, CommentArea, path) {
    $.ajax({url: path + "/local/ratings/delete.php?id=" + CommentId + "&courseid=" + Course + "&activityid=" + Activity + "&itemid=" + Item + "&commentarea=" + CommentArea,
        beforeSend: function () {
        },
        success: function (result) {
            $(".commentcount_" + Item).html(result);
            $(".comment_" + Item + '_' + CommentId).hide();
        },
        cache: false, dataType: "html"});
}

function fnViewevent(Course, Activity, Item, RateArea, path, Rating, Heading) {
    $.ajax({url: path + "/local/ratings/update.php?courseid=" + Course + "&activityid=" + Activity + "&itemid=" + Item + "&ratearea=" + RateArea + "&rating=" + Rating + "&heading=" + Heading,
        beforeSend: function () {
        },
        success: function (result) {
            var resultvalue = result.split('!@');
            $("#myratings").dialog('close');
            $(".overall_ratings_" + Item).html(resultvalue[0]);
            $(".totalcount_" + Item).html(resultvalue[1]);
        },
        cache: false, dataType: "html"});
}

function fnViewAllRatings(Course, Activity, Item, RateArea, path, Heading) {
    // used to change the dialog position
    if (RateArea == "storywall") {
        my_custom = "center";
        at_custom = "center";
        of_custom = "#readmore_part1";
    }
    else if (RateArea == "Resource Central") {
        my_custom = "center";
        at_custom = "center";
        of_custom = "#rate" + Item;
    }
    else {
        my_custom = "center";
        at_custom = "center";
        of_custom = window;
    }

    $("#myratings").dialog({
        width: 450,
        modal: true,
        closeText: "",
        position: {my: my_custom, at: at_custom, of: of_custom},
        title: RateArea + ' - ' + Heading
    });

    $.ajax({url: path + "/local/ratings/allrating.php?courseid=" + Course + "&activityid=" + Activity + "&itemid=" + Item + "&ratearea=" + RateArea + "&heading=" + Heading,
        beforeSend: function () {
        },
        success: function (result) {
            $("#myratings").html(result);
        },
        cache: false, dataType: "html"});
}

/*JS function for the commenting*/
function fnComment(Course, Activity, Item, CommentArea, path) {

    var Comment = document.getElementById('mycomment_' + Item).value;

    $.ajax({url: path + "/local/ratings/comment.php?courseid=" + Course + "&activityid=" + Activity + "&itemid=" + Item + "&commentarea=" + CommentArea + "&comment=" + Comment,
        beforeSend: function () {
        },
        success: function (result) {
            var resultvalue = result.split('!@');
            $(".commentclick_" + Item).after(resultvalue[0]);
            $(".commentcount_" + Item).html(resultvalue[1]);
            $("#mycomment_" + Item).val(null);
        },
        cache: false, dataType: "html"});
}

function fnViewAllComments(Item) {
    ////$(".anchorclass_"+Item).click(function(){
    //$(".coursecomment").css("display","block");
    //$('#comment_list_'+Item).css("display","block");
    $('#comment_list_' + Item).slideToggle();
    //});
    $(".viewall" + Item).click(function () {
        $(this).text($(this).text() == 'View less' ? 'View All' : 'View less');
        $('.viewallcomments' + Item).slideToggle()();
    });
    $(".closeicon" + Item).click(function () {
        $('#comment_list_' + Item).slideUp();
    });
}

function fnstorywallComments(Item) {
    $(".viewall" + Item).text($(".viewall" + Item).text() == 'View less' ? 'View All' : 'View less');
    $('.viewallcomments' + Item).slideToggle()();
}
