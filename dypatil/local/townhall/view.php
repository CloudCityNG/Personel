<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/townhall/lib.php');
require_once($CFG->dirroot . '/local/ratings/lib.php');
global $CFG, $DB;
$id = optional_param('id', -1, PARAM_INT);
$page1 = optional_param('mypage1', 0, PARAM_INT);
$per_page1 = 5;
$page2 = optional_param('mypage2', 0, PARAM_INT);
$per_page2 = 5;

// get the admin layout
// $PAGE->set_pagelayout('admin');
// check the context level of the user and check whether the user is login to the system or not
// $PAGE->set_context($systemcontext);
// $systemcontext = context_system::instance();;
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_course::instance($id));
$cc = $DB->get_record('course', array('id' => $id));
$PAGE->set_course($cc);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/townhall/index.php');
$PAGE->requires->css('/local/townhall/styles.css');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui', 'core');
$PAGE->requires->css('/local/ratings/css/jquery-ui.css'); //For like/unlike, rating &commenting
$PAGE->requires->css('/local/ratings/css/style.css'); //For like/unlike, rating &commenting
$PAGE->requires->js('/local/ratings/js/ratings.js'); //For like/unlike, rating &commenting
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_townhall') . ' : ' . get_string('view', 'local_townhall');
// $PAGE->set_title($string);
if (is_siteadmin())
    $PAGE->navbar->add(get_string('managetownhall', 'local_townhall'), new moodle_url('/local/townhall/index.php'));
$PAGE->navbar->add(get_string('view', 'local_townhall'));
$strheading = get_string('view', 'local_townhall');
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

if (has_capability('local/townhall:manage',context_system::instance())) {
    $currenttab = "lists";
    print_towntabs($currenttab, $id);
    $results = $DB->get_records_sql('Select * from {local_townhall} ');
} else {
    echo '<hr>';
    $results = $DB->get_records_sql('Select * from {local_townhall} where courseid=' . $id . '');
}
$count1 = count($results);
$start1 = $page1 * $per_page1;
$results = $DB->get_records_sql("select  * from {local_townhall} where courseid=$id limit $start1, $per_page1");
echo '<h3>' . get_string('activities', 'local_townhall') . '</h3><hr>';
if (empty($results)) {
    echo get_string('noactivities', 'local_townhall');
}
foreach ($results as $result) {
    $cmid = $result->cmid;


    $id = $result->courseid;
    $name = $result->modname;
    $likes = $DB->get_records_sql('select id from {local_like} where activityid=' . $cmid . ' and courseid=' . $id . '');
    $likes = count($likes);
    $comments = $DB->get_records_sql('select id from {local_comment} where activityid=' . $cmid . ' and courseid=' . $id . '');
    $comments = count($comments);
    $rating = $DB->get_record_sql('select AVG(rating) AS rating from {local_rating} where activityid=' . $cmid . ' and courseid=' . $id . '');
    if (empty($rating))
        $rating->rating = 0;
    else
        $rating->rating = round($rating->rating, 1) . '/5';
    $mod_name = get_coursemodule_from_id($name, $cmid, 0, false, MUST_EXIST);
    $mod_name = $mod_name->name;
    echo '<div class="town_list_bar">
    <div class="town_task_list">
    <a href="' . $CFG->wwwroot . '/mod/' . $name . '/view.php?id=' . $cmid . '" target="_blank">' . $OUTPUT->pix_icon('icon', '', $name, array('class' => 'icon')) . addslashes($mod_name) . '</a>
    </div>
    <div class="town_ratings"><span>' . $OUTPUT->pix_icon('like', '', 'local_ratings') . $likes . '</span><span>' . $OUTPUT->pix_icon('star', '', 'local_ratings') . $rating->rating .
    '</span><span>' . $OUTPUT->pix_icon('comment', '', 'local_ratings') . $comments . '</span></div>
    </div>';
}
$baseurl = new moodle_url('/local/townhall/view.php?id=' . $id . '');
$pagevar1 = 'mypage1';
echo '<div class="paging_bar">';
echo $OUTPUT->paging_bar($count1, $page1, $per_page1, $baseurl, $pagevar1);
echo '</div>';
// Topics Disoplay
$topics = $DB->get_records_sql('SELECT * from {local_townhall_topic} where courseid=' . $id . ' and publish=1');
$count2 = count($topics);
$start2 = $page2 * $per_page2;
$topics = $DB->get_records_sql("select  * from {local_townhall_topic} where courseid=$id and publish=1 limit $start2, $per_page2");
$course_name = $DB->get_field('course', 'fullname', array(id => $id));
echo '<h3>' . get_string('topics', 'local_townhall') . '</h3><hr>';
if (empty($topics)) {
    echo get_string('notopics', 'local_townhall');
}
foreach ($topics as $topic) {
    $content = preg_replace("/<img[^>]+\>/i", "", $topic->description);
    preg_match('/(<img[^>]+>)/i', $topic->description, $matches);
    echo '<div class="town_list_bar">';
    if ((count($matches)) == 0) {
        echo '<div class="town_topic_task_list1"><span>' . addslashes($topic->topic) . '</span><span>' . $content . '<span></div>';
    } else {
        echo ' <div class="town_topic_task_list2"><span><font>' . addslashes($topic->topic) . '</font>' . $content . '</span><span>' . $matches[0] . '</span> </div>';
    }
    echo '<div class="town_topic_ratings">
    <div class="town_likes">' . display_like_unlike($id, 0, $topic->id, townhall) . '</div>
    <div  class="town_rates">' . display_rating($id, 0, $topic->id, townhall, $course_name) . '</div>
    <div  class="town_commnets">' . display_comment_area($id, 0, $topic->id, townhall) . '</div>
    </div>
    </div>';
}
echo '<div id="myratings"></div>';
//$baseurl2 = new moodle_url('/local/townhall/view.php?id='.$id.'');
$pagevar2 = 'mypage2';
echo '<div class="paging_bar">';
echo $OUTPUT->paging_bar($count2, $page2, $per_page2, $baseurl, $pagevar2);
echo '</div>';
echo $OUTPUT->footer();
?>
