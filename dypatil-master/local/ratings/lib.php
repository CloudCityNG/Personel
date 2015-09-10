<?php

/**
 * @method display_like_unlike
 * @param int $course course id (used to indicates ,enabling like activity for a specified course)
 * @param string $activity activity id
 * @param int $item activity node
 * @param string $likearea plugin name
 * @returns providing option to like or unlike.
 */
function display_like_unlike($course, $activity, $item, $likearea) {
    global $DB, $CFG, $USER, $PAGE, $OUTPUT;

    $output = html_writer::start_tag('div', array('class' => 'like_unlike'));

    $output .= html_writer::start_tag('div', array('id' => 'contents_' . $item, 'style' => 'float: left;'));
    //Like button----------
    $likeEnable = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/like.png', 'title' => 'Like', 'style' => 'cursor: pointer;', 'onClick' => 'updatevalues("' . $CFG->wwwroot . '","' . $likearea . '", ' . $activity . ', ' . $item . ', ' . $course . ', 0)'));
    $likeDisable = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/like_disable.png', 'title' => 'You Liked it'));
    $likeparams = array('id' => 'label_like_' . $item, 'style' => 'float: left; padding: 0 8px 0 0;');
    if ($DB->record_exists('local_like', array('likearea' => $likearea, 'activityid' => $activity, 'itemid' => $item, 'courseid' => $course, 'likestatus' => 1, 'userid' => $USER->id))) {
        $output .= html_writer::tag('div', $likeDisable, $likeparams);
    } else {
        $output .= html_writer::tag('div', $likeEnable, $likeparams);
    }
    //Like count-------------------
    $likecount = $DB->count_records('local_like', array('likearea' => $likearea, 'activityid' => $activity, 'itemid' => $item, 'courseid' => $course, 'likestatus' => 1));
    $output .= '<span style="float: left; margin-top:5px;" class="count_likearea_' . $item . '">' . $likecount . '</span>';

    //Unlike button----------
    $unlikeEnable = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/unlike.png', 'title' => 'Dislike', 'style' => 'cursor: pointer;', 'onClick' => 'updatevalues("' . $CFG->wwwroot . '","' . $likearea . '", ' . $activity . ', ' . $item . ', ' . $course . ', 1)'));
    $unlikeDisable = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/unlike_disable.png', 'title' => 'You Disliked it'));
    $unlike = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/unlike.png'));
    $unlikeparams = array('id' => 'label_unlike_' . $item, 'style' => 'float: left; padding: 0 8px 0 10px; margin-top:5px;');
    if ($DB->record_exists('local_like', array('likearea' => $likearea, 'activityid' => $activity, 'itemid' => $item, 'courseid' => $course, 'likestatus' => 2, 'userid' => $USER->id))) {
        $output .= html_writer::tag('div', $unlikeDisable, $unlikeparams);
    } else {
        $output .= html_writer::tag('div', $unlikeEnable, $unlikeparams);
    }

    //Dislike count---------------------
    $unlikecount = $DB->count_records('local_like', array('likearea' => $likearea, 'activityid' => $activity, 'itemid' => $item, 'courseid' => $course, 'likestatus' => 2));
    $output .= '<span style="float: left; margin-top:5px;" class="count_unlikearea_' . $item . '">' . $unlikecount . '</span>';

    $output .= html_writer::end_tag('div'); //End of #contents_$item

    $output .= html_writer::end_tag('div'); //End of .like_unlike
    return $output;
}

/**
 * @method  display_rating
 * @todo function calculates over all rating for course
 * @param int $courseid course id (used to indicates ,enable rating option for a specified course)
 * @param string $activity activity id
 * @param int $item activity node
 * @param string $ratearea activity name, string $heading (used to provide dynamic heading)
 * @returns rating image for course
 */
function display_rating($courseid, $activityid, $itemid, $ratearea, $heading) {
    global $CFG, $DB, $USER;
    $avgratings = get_rating($courseid, $activityid, $itemid, $ratearea);
    $res = '<div class="radiostars">';
    $res .= '<div class="overall_ratings_' . $itemid . '" style="float: left; cursor: pointer;">';
    $res .= "<img title='" . ($avgratings->avg / 2) . " out of 5' src='" . $CFG->wwwroot . "/local/ratings/pix/star" . ($avgratings->avg) . ".png'  onclick='fnViewAllRatings($courseid, $activityid, $itemid, \"$ratearea\", \"$CFG->wwwroot\", \"$heading\")'/>";
    $res .= '</div>'; //End of .overall_ratings
    $res .= '<div class="overall_users">';
    $res .= "(<font class='totalcount_$itemid'>$avgratings->count</font> users)";
    $res .= '</div>'; //End of .overall_users
    $res .= '</div>'; //End of .radiostars
    return $res;
}

/**
 * @method ask_for_rating
 * @todo to displays the empty stars to ask for ratings
 * @param int $courseid course id 
 * @param string $activity activity id
 * @param int $item activity node
 * @param string $ratearea activity name, string $heading (used to provide dynamic heading)
 * @returns rating star images 
 */
function ask_for_rating($courseid, $activityid, $itemid, $ratearea, $heading) {
    global $DB, $USER, $CFG, $OUTPUT;
    $result = html_writer::start_tag('div', array('class' => 'comment_' . $itemid, 'style' => 'padding: 5px;'));
    $result .= html_writer::start_tag('div', array('class' => 'comment_picture'));
    $user = $DB->get_record('user', array('id' => $USER->id));
    $result .= $OUTPUT->user_picture($user, array('courseid' => $courseid, 'size' => 32));
    $result .= html_writer::end_tag('div'); //End of .comment_picture
    $result .= html_writer::start_tag('div', array('class' => 'comment_time'));
    $result .= ' <a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '&courseid=' . $courseid . '">' . fullname($user) . '</a><font class="rate_time">(You aren\'t given the rating)</font>';
    $result .= html_writer::end_tag('div'); //End of .comment_time
    $result .= html_writer::start_tag('div', array('class' => 'comment_commentarea'));
    $result .= '<div class="example_' . $itemid . '">';
    if ((isloggedin() && !isguestuser())) {
        $disable = '';
        $title = "title='Click on the star to rate this $ratearea'";
        $enroll = true;
        if ($courseid !== SITEID) {
            $context =  context_course::instance($courseid);

            $systemcontext = context_system::instance();
            if (!is_enrolled($context, $USER->id) && !is_siteadmin() && !has_capability('local/collegestructure:manage', $systemcontext)) {
                $enroll = false;
                $disable = 'disabled="disabled"';
                $title = 'title="You need to enroll to the ' . $ratearea . ' to give a rating"';
            }
        }
        if (!$enroll) {
            $result .= '<div>You need to enroll to the ' . $ratearea . ' to give a rating</div>';
        } else {
            $result .= '<form ' . $title . '>';
            for ($i = 1; $i <= 5; $i++) {
                $result .= "<input id='radio$itemid$i' type='radio' name='grade' onclick='fnViewevent($courseid, $activityid, $itemid, \"$ratearea\", \"$CFG->wwwroot\", $i, \"$heading\")'";
                $result .= ' value="' . $i . '" alt="Rating" ' . $disable . '  />';
                $result .= '<label for="radio' . $itemid . $i . '"><img ' . $disable . ' src="' . $CFG->wwwroot . '/local/ratings/pix/stars.png" ' . $disable . '  /></label>';
            }
            $result .= '</form>';
        }
    }
    $result .= '</div>';
    $result .= html_writer::end_tag('div'); //End of .comment_commentarea
    return $result;
}

/**
 * @method  get_rating
 * @todo to caluclates rating
 * @return average rating as numeric value
 */
function get_rating($courseid, $activityid, $itemid, $ratearea) {
    global $CFG, $DB, $USER;
    $sql = "SELECT AVG(rating) AS avg, count(userid) AS count
        FROM {local_rating}
        WHERE courseid = $courseid AND activityid = $activityid AND itemid = $itemid AND ratearea = '$ratearea'";

    $avgrec = $DB->get_record_sql($sql);
    $avgrec->avg = $avgrec->avg * 2;  // Double it for half star scores.
    //// Now round it up or down.
    $avgrec->avg = round($avgrec->avg);
    return $avgrec;
}

/**
 * @method  get_existing_rates
 * @todo to get  exists rated records
 * @return list of ratings given by users
 */
function get_existing_rates($record) { //For now we are not using this function
    global $DB, $USER, $CFG, $OUTPUT;
    $result = '';
    $result .= html_writer::start_tag('div', array('class' => 'comment_' . $record->itemid . '_' . $record->id, 'style' => 'padding: 5px;'));
    $result .= html_writer::start_tag('div', array('class' => 'comment_picture'));
    $user = $DB->get_record('user', array('id' => $record->userid));
    $result .= $OUTPUT->user_picture($user, array('courseid' => $record->courseid, 'size' => 32));
    $result .= html_writer::end_tag('div'); //End of .comment_picture
    $result .= html_writer::start_tag('div', array('class' => 'comment_time'));
    $curuser = ($record->userid == $USER->id) ? '(you)' : '';
    $result .= ' <a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '&courseid=' . $record->courseid . '">' . fullname($user) . '</a>' . $curuser . ' - ' . userdate($record->time);
    $result .= html_writer::end_tag('div'); //End of .comment_time
    $result .= html_writer::start_tag('div', array('class' => 'comment_commentarea'));
    $result .= '<img src="' . $CFG->wwwroot . '/local/ratings/pix/star' . ($record->rating * 2) . '.png" />';
    $result .= html_writer::end_tag('div'); //End of .comment_commentarea
    $result .= html_writer::end_tag('div'); //End of .comment_$itemid_$existing_comment->id
    return $result;
}

/**
 * @method  display_comment_area
 * @todo provides the option to give the comment
 * @param int $courseid course id 
 * @param string $activity activity id
 * @param int $itemid activity node
 * @param string $commentarea activity name,
 * @return provide the inteface to do comment
 */
function display_comment_area($courseid, $activityid, $itemid, $commentarea) {
    global $CFG, $USER, $DB;
    $result = html_writer::start_tag('div', array('class' => 'mycomment'));
    $params = array('courseid' => $courseid, 'activityid' => $activityid, 'itemid' => $itemid, 'commentarea' => $commentarea);
    $existing_comments = $DB->get_records('local_comment', $params, 'time DESC');
    $count_comments = $DB->count_records('local_comment', $params);
    if ($commentarea == "storywall")
        $result .= html_writer::tag('a', 'Share your Comment', array('id' => 'anchorclass_' . $itemid, "href" => "javascript:void(0)"));
    else
        $result .= html_writer::tag('a', 'Share your Comment', array('id' => 'anchorclass_' . $itemid, "href" => "javascript:void(0)", "onClick" => "fnViewAllComments($itemid)"));
    if ($commentarea !== 'forum') {
        $result .= '&nbsp;(<font class="commentcount_' . $itemid . '">' . $count_comments . '</font>)';
    }
    // used to make comment area visible by default   
    if ($commentarea == "storywall")
        $result .= html_writer::start_tag('div', array('class' => 'coursecomment', 'id' => 'comment_list_' . $itemid, 'style' => 'width:95% !important; position:relative'));
    else
        $result .= html_writer::start_tag('div', array('class' => 'coursecomment', 'id' => 'comment_list_' . $itemid, 'style' => 'display: none;'));

    $closeIcon = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/icon_close_popup.gif', 'title' => 'Close'));
    $result .= html_writer::tag('div', $closeIcon, array('style' => 'float: right; cursor: pointer;', 'class' => 'closeicon' . $itemid));
    if (isloggedin() && !isguestuser()) {
        $enroll = true;
        if ($courseid !== SITEID) {
            $context = context_course::instance($courseid);
            if (!is_enrolled($context, $USER->id) && !is_siteadmin()) {
                //&& !has_capability('local/feedback:view', $context)
                $enroll = false;
            }
        }
        if (!$enroll) {
            $result .= '<div>You need to enroll to the ' . $commentarea . ' to comment.</div>';
        } else {
            $result .= html_writer::start_tag('textarea', array('name' => 'commentarea', 'id' => 'mycomment_' . $itemid, 'rows' => '2', 'cols' => '50'));
            $result .= html_writer::end_tag('textarea');
            $button = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/ratings/pix/add-comment.gif'));
            //$button = html_writer::tag('button', 'Comment', array());
            $result .= html_writer::tag('a', $button, array('class' => 'commentclick_' . $itemid, "href" => "javascript:void(0)", "onClick" => "fnComment($courseid, $activityid, $itemid, \"$commentarea\", \"$CFG->wwwroot\")", 'style' => 'font-size: 12px;'));
        }
    }
    $result .= html_writer::start_tag('div', array('class' => 'comment_' . $itemid));
    $result .= html_writer::end_tag('div'); // End of .comment_$itemid
    $i = 1;
    foreach ($existing_comments as $existing_comment) {
        if ($i > 3) {
            $result .= html_writer::start_tag('div', array('class' => 'viewallcomments' . $itemid, 'style' => 'display: none;'));
        }
        $result .= get_existing_comments($courseid, $itemid, $existing_comment);
        if ($i > 3) {
            $result .= html_writer::end_tag('div'); // End of .comment_$itemid
        }
        $i++;
    }
    if ($count_comments > 3) {
        //  only applied to storywall plugin
        if ($commentarea == "storywall")
            $result .= html_writer::tag('a', 'View All', array("href" => "javascript:void(0)", 'class' => 'viewall' . $itemid, 'style' => 'font-size: 12px;', "onClick" => "fnstorywallComments($itemid)"));
        else
            $result .= html_writer::tag('a', 'View All', array("href" => "javascript:void(0)", 'style' => 'font-size: 12px;', 'class' => 'viewall' . $itemid));
    }
    $result .= html_writer::end_tag('div'); // End of .comment_list
    $result .= html_writer::end_tag('div'); // End of .mycomment
    return $result;
}

/**
 * @method  get_existing_comments
 * @todo to get  exists comment given by user
 * @param int $courseid course id
 * @param int $itemid activity node
 * @param object $existing_comment it holds comment info
 * @return print existing comment
 */
function get_existing_comments($courseid, $itemid, $existing_comment) {
    global $DB, $USER, $CFG, $OUTPUT;
    $result = '';
    $result .= html_writer::start_tag('div', array('class' => 'comment_' . $itemid . '_' . $existing_comment->id, 'style' => 'padding: 5px; margin-top: 10px;'));
    $result .= html_writer::start_tag('div', array('class' => 'comment_picture', 'style' => 'padding: 5px;'));
    $user = $DB->get_record('user', array('id' => $existing_comment->userid));
    $result .= $OUTPUT->user_picture($user, array('courseid' => $courseid, 'size' => 32));
    $result .= html_writer::end_tag('div'); //End of .comment_picture

    $result .= html_writer::start_tag('div', array('class' => 'comment_time'));
    $result .= ' <a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '&courseid=' . $courseid . '">' . fullname($user) . '</a> - ' . userdate($existing_comment->time);
    if ($USER->id == $existing_comment->userid) {
        $deleteIcon = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/pix/t/delete.png', 'style' => 'margin-left: 20px;'));
        $result .= html_writer::tag('a', $deleteIcon, array("href" => "javascript:void(0)", "onclick" => "DeleteComment($existing_comment->id, $courseid, $existing_comment->activityid, $itemid, \"$existing_comment->commentarea\", \"$CFG->wwwroot\")"));
    }
    $result .= html_writer::end_tag('div'); //End of .comment_time
    $result .= html_writer::start_tag('div', array('class' => 'comment_commentarea', 'style' => 'width: 98%;'));
    $result .= $existing_comment->comment;
    $result .= html_writer::end_tag('div'); //End of .comment_commentarea
    $result .= html_writer::end_tag('div'); //End of .comment_$itemid_$existing_comment->id
    return $result;
}
