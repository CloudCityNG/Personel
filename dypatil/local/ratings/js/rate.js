
function updatevalues(path, LikeArea, Activity, Item, Course, Action) {
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
                $("#label_unlike_" + Item).attr('title', 'You disliked it');
                $("#label_unlike_" + Item).attr('disabled', 'disabled');
                $(".count_unlikearea_" + Item).html(result)
            } else {
                $("#label_like_" + Item).attr('title', 'You liked it');
                $("#label_like_" + Item).attr('disabled', 'disabled');
                $(".count_likearea_" + Item).html(result)
            }
        },
        error: function () {
            $('#contents_' + Item).html('error');
        }, cache: false, dataType: "html"});


}

function DeleteComment(CommentId, Course, Activity, Item, CommentArea, path) {
    $.ajax({url: path + "/local/ratings/delete.php?id=" + CommentId + "&courseid=" + Course + "&activityid=" + Activity + "&itemid=" + Item + "&commentarea=" + CommentArea,
        beforeSend: function () {
        },
        success: function (result) {
            $(".commentcount_" + Item).html(result);
            $(".comment_" + Item + '_' + CommentId).hide();
        },
        error: function () {
            $(".comment_" + Item + '_' + CommentId).html('error');
        }, cache: false, dataType: "html"});
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
        error: function () {
            $(".example_" + Item).html('error');
        }, cache: false, dataType: "html"});
}

function fnViewAllRatings(Course, Activity, Item, RateArea, path, Heading) {
    $("#myratings").dialog({
        width: 450,
        modal: true,
        closeText: "",
        title: RateArea + ' - ' + Heading
    });
    $.ajax({url: path + "/local/ratings/allrating.php?courseid=" + Course + "&activityid=" + Activity + "&itemid=" + Item + "&ratearea=" + RateArea + "&heading=" + Heading,
        beforeSend: function () {
        },
        success: function (result) {
            $("#myratings").html(result);
        },
        error: function () {
            $("#myratings").html(result);
        }, cache: false, dataType: "html"});
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
        error: function () {
            $(".comment_" + Item).html('error');
        }, cache: false, dataType: "html"});
}

function fnViewAllComments(Item) {

    //$(".anchorclass_"+Item).click(function(){
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

