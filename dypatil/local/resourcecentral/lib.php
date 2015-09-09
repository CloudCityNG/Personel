<?php
/*
 * To get all the likes comment and rating for activities
 */
function get_like_comment_rating($courseid, $activityid, $itemid) {
    global $CFG, $DB;
    $likes = $comments = $ratings = 0;
    if ($activityid != null) {
        $like = "SELECT count(id) FROM {local_like} WHERE courseid={$courseid} AND activityid={$activityid} AND itemid={$itemid} AND likestatus=1";
        $likes = $DB->count_records_sql($like);
        $comment = "SELECT count(id) FROM {local_comment} WHERE courseid={$courseid} AND activityid={$activityid} AND itemid={$itemid}";
        $comments = $DB->count_records_sql($comment);
        $rating = "SELECT count(id) FROM {local_rating} WHERE courseid={$courseid} AND activityid={$activityid} AND itemid={$itemid}";
        $ratings = $DB->count_records_sql($rating);
    }
    return '' . $likes . ' Likes ' . $comments . ' Comments ' . $ratings . ' Ratings';
}

/* List the all activities */
function get_list_all_activity($key, $id) {
    global $CFG, $DB;
    if ($key == 'L') {
        $sql = "SELECT ll.*,count(ll.id) as counts,m.name,cm.instance,cm.id as value FROM {local_like} as ll,{course_modules} as cm,{modules} m WHERE ll.activityid=cm.id AND cm.module=m.id AND ll.likestatus=1 AND ll.courseid={$id} group by ll.activityid order by counts desc";
        $likeditems = $DB->get_records_sql($sql);
    }
    if ($key == 'C') {
        $sql = "SELECT ll.*,count(ll.id) as counts,m.name,cm.instance,cm.id as value FROM {local_comment} as ll,{course_modules} as cm,{modules} m WHERE ll.activityid=cm.id AND cm.module=m.id  AND ll.courseid={$id} group by ll.activityid order by counts desc";
        $likeditems = $DB->get_records_sql($sql);
    }
    if ($key == 'V') {
        $sql = "SELECT ll.*,count(ll.id) as counts,m.name,cm.instance,cm.id as value FROM {local_rating} as ll,{course_modules} as cm,{modules} m WHERE ll.activityid=cm.id AND cm.module=m.id  AND ll.courseid={$id} group by ll.activityid order by counts desc";
        $likeditems = $DB->get_records_sql($sql);
    }
    if ($key == 'SW') {
        $sql = "SELECT s.*,m.name,cm.instance,cm.id as value FROM {storywall} s,{course_modules} cm,{modules} m WHERE s.id=cm.instance AND cm.module=m.id AND s.course={$id} AND m.name='storywall' ";
        $likeditems = $DB->get_records_sql($sql);
    }
    if ($key == 'SP') {
        $sql = "SELECT s.*,m.name,cm.instance,cm.id as value FROM {sociopedia} s,{course_modules} cm,{modules} m WHERE s.id=cm.instance AND cm.module=m.id AND s.course={$id} AND m.name='sociopedia' ";
        $likeditems = $DB->get_records_sql($sql);
    }
    if ($key == 'A') {
        $sql = "SELECT s.*,m.name,cm.instance,cm.id as value FROM {assign} s,{course_modules} cm,{modules} m WHERE s.id=cm.instance AND cm.module=m.id AND s.course={$id} AND m.name='assign' ";
        $likeditems = $DB->get_records_sql($sql);
    }
    if ($key == 'D') {
        $sql = "SELECT s.*,m.name,cm.instance,cm.id as value FROM {forum} s,{course_modules} cm,{modules} m WHERE s.id=cm.instance AND cm.module=m.id AND s.course={$id} AND m.name='forum' ";
        $likeditems = $DB->get_records_sql($sql);
    }
    return $likeditems;
}

?>