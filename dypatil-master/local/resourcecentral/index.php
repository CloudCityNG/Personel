<style>
    .singleselect form, .singleselect select {
        margin: 0;
        margin-top: -11px !important;
        margin-left:147px !important;
    }
</style>
<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;

require_once($CFG->dirroot . '/local/resourcecentral/resource_central_forms.php');
require_once($CFG->dirroot . '/local/resourcecentral/lib.php');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui', 'core');
require_once($CFG->dirroot . '/local/ratings/lib.php'); //For like/unlike, rating &commenting
$PAGE->requires->css('/local/ratings/css/jquery-ui.css'); //For like/unlike, rating &commenting
$PAGE->requires->css('/local/ratings/css/style.css'); //For like/unlike, rating &commenting
$PAGE->requires->js('/local/ratings/js/ratings.js'); //For like/unlike, rating &commenting
$id = optional_param('id', 0, PARAM_INT);
$key = optional_param('key', '0', PARAM_RAW);
$systemcontext = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context(context_course::instance($id));
$courserecord = $DB->get_record('course', array('id' => $id));
$PAGE->set_course($courserecord);
//$PAGE->set_title(get_string('resourcecentral_title','local_resourcecentral'));
$PAGE->requires->css('/local/resourcecentral/css/style.css');
require_login();
$PAGE->set_url('/local/resourcecentral/index.php');
$PAGE->set_heading(get_string('pluginname', 'local_resourcecentral'));
$PAGE->navbar->add(get_string('pluginname', 'local_resourcecentral'), new moodle_url('/local/resourcecentral/index.php?id=' . $id . ''));
//$PAGE->navbar->add(get_string('view_rs','local_resourcecentral'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_resourcecentral'));
$hierarchy = new hierarchy();
$admision = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('resourcecentral_view_desc', 'local_resourcecentral'));
}
$select = array('SELECT' => '---Select---', 'SW' => 'Storywall', 'D' => 'Discussions', 'A' => 'Assignments', 'SP' => 'Sociopedia');

echo '<a  style="color: #fff;
background-color: #04c;float:right;border-radius: 6px;
padding: 2px 4px 2px 4px;margin-right: 5%;" href="create.php?courseid=' . $id . '">Add Your Content</a>';
echo '<form method="POST" action="#" style="text-align:center;">';
echo '<input type="text" name="searchkey">';
echo '<input type="submit" value="Search">';
echo '</form>';
echo '<ul id="navlist">';
echo '<li><a href="index.php?key=' . T . '&id=' . $id . '">Tags</a></li>';
//echo '<li><a href="index.php?key='.A.'&id='.$id.'">Activities</a></li>';

$select = new single_select(new moodle_url('/local/resourcecentral/index.php?id=' . $id . ''), 'key', $select, $key, null);
$select->set_label(get_string('activity', 'local_resourcecentral'));


echo '<li><a href="index.php?key=' . L . '&id=' . $id . '">Likes</a></li>';
echo '<li><a href="index.php?key=' . V . '&id=' . $id . '">Voted</a></li>';
echo '<li><a href="index.php?key=' . C . '&id=' . $id . '">Comments</a></li>';
echo '<li>Activities' . $OUTPUT->render($select) . '</li>';
echo '</ul>';

if (isset($_POST['searchkey']) && $_POST['searchkey'] != null) {
    $search = $_POST['searchkey'];
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'assign'));
    $assignquery = "SELECT * FROM {assign} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $assignlists = $DB->get_records_sql($assignquery);
    if (!empty($assignlists)) {
        foreach ($assignlists as $assignlist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $assignlist->id));

            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Assignments</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $activityid . '">' . $assignlist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $assignlist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $assignlist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'assignment'));
    $assignmentquery = "SELECT * FROM {assignment} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $assignmentlists = $DB->get_records_sql($assignmentquery);
    if (!empty($assignmentlists)) {
        foreach ($assignmentlists as $assignmentlist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $assignmentlist->id));

            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Assignments</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/assignment/view.php?id=' . $activityid . '">' . $assignmentlist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $assignmentlist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $assignmentlist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'folder'));
    $folderquery = "SELECT * FROM {folder} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $folderlists = $DB->get_records_sql($folderquery);
    if (!empty($folderlists)) {
        foreach ($folderlists as $folderlist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $folderlist->id));

            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Folder</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/folder/view.php?id=' . $activityid . '">' . $folderlist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $folderlist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $folderlist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'forum'));
    $forumquery = "SELECT * FROM {forum} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $forumlists = $DB->get_records_sql($forumquery);
    if (!empty($forumlists)) {
        foreach ($forumlists as $forumlist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $forumlist->id));

            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Forum</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/forum/view.php?id=' . $activityid . '">' . $forumlist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $forumlist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $forumlist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'quiz'));
    $quizquery = "SELECT * FROM {quiz} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $quizlists = $DB->get_records_sql($quizquery);
    if (!empty($quizlists)) {
        foreach ($quizlists as $quizlist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $quizlist->id));

            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Quiz</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/quiz/view.php?id=' . $activityid . '">' . $quizlist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $quizlist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $quizlist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'url'));
    $urlquery = "SELECT * FROM {url} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $urllists = $DB->get_records_sql($urlquery);
    if (!empty($urllists)) {
        foreach ($urllists as $urllist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $urllist->id));
            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Url</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/url/view.php?id=' . $activityid . '">' . $urllist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $urllist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $urllist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'storywall'));
    $storywallquery = "SELECT * FROM {storywall} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    $storywalllists = $DB->get_records_sql($storywallquery);
    if (!empty($storywalllists)) {
        foreach ($storywalllists as $storywalllist) {
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $storywalllist->id));
            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Storywall</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/storywall/view.php?id=' . $activityid . '">' . $storywalllist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $storywalllist->intro . '</td></tr>';
            $result = get_like_comment_rating($id, $activityid, $storywalllist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'sociopedia'));
    $sociopediaquery = "SELECT * FROM {sociopedia} WHERE course={$id} AND name like '%$search%' or '%$search' or '$search%' ";
    ;
    $sociopedialists = $DB->get_records_sql($sociopediaquery);
    if (!empty($sociopedialists)) {
        foreach ($sociopedialists as $sociopedialist) {
            $value = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $sociopedialist->id));
            echo '<table class="maintable">';
            echo '<tr><td class="rowtitle">Sociopedia</td></tr>';
            echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/sociopedia/view.php?id=' . $value . '">' . $sociopedialist->name . '</a></td></tr>';
            echo '<tr><td class="rowintro">' . $sociopedialist->intro . '</td></tr>';
            $activityid = $DB->get_field('course_modules', 'id', array('course' => $id, 'module' => $moduleid, 'instance' => $sociopedialist->id));
            $result = get_like_comment_rating($id, $activityid, $sociopedialist->id);
            echo '<tr><td class="rowrating">' . $result . '</td></tr>';
            echo '</table>';
        }
    }
    $sql = "SELECT * FROM {local_resourcecentral} WHERE courseid={$id} AND title like '%$search%' or '%$search' or '$search%' ";
    $query = $DB->get_records_sql($sql);

    if (!empty($query)) {

        foreach ($query as $file) {

            $context =  context_user::instance($file->usermodified);
            $systemcontext =context_system::instance($file->usermodified);
            echo '<table class="maintable">';
            if (has_capability('local/collegestructure:manage', $systemcontext, $file->usermodified) && is_siteadmin($file->usermodified))
                echo '<tr><td class="rowtitle" colspan="3">Admin Generated Content</td></tr>';
            else if (has_capability('local/collegestructure:manage', $systemcontext, $file->usermodified) && !is_siteadmin($file->usermodified))
                echo '<tr><td class="rowtitle" colspan="3">Registrar Generated Content</td></tr>';
            else if (has_capability('local/clclasses:enrollclass', $context, $file->usermodified) && !is_siteadmin($file->usermodified))
                echo '<tr><td class="rowtitle" colspan="3" >Student Generated Content</td></tr>';
            else {
                if (has_capability('local/clclasses:submitgrades', $systemcontext, $file->usermodified) && !is_siteadmin($file->usermodified))
                    echo'<tr><td class="rowtitle" colspan="3">Faculty Uploaded Content</td></tr>';
            }

            echo '<tr><td class="rowname"><a href="download.php?id=' . $file->id . '&course=' . $id . '">' . $file->title . '</a></td></td></tr>';
            echo '<tr><td class="rowusername">' . by . ' ' . $DB->get_field('user', 'firstname', array('id' => $file->usermodified)) . '
' . $DB->get_field('user', 'lastname', array('id' => $file->usermodified)) . '
 ' . date('l, d F Y, g:i A', $file->timecreated) . '</td></td></tr>';
            echo '<tr><td class="rowintro">' . $file->description . '</td></tr>';
            echo '</table>';
        }
    }
}

elseif ($key != '0') {
    if ($key == 'L') {
        $msg = 'Likes';
    }
    if ($key == 'V') {
        $msg = 'Votes';
    }
    if ($key == 'C') {
        $msg = 'Comments';
    }
    $likeditems = get_list_all_activity($key, $id);
    if (!empty($likeditems)) {
        foreach ($likeditems as $likeditem) {
            switch ($likeditem->name) {
                case 'assign':
                    $module = $DB->get_record('assign', array('id' => $likeditem->instance));
                    echo '<table class="maintable">';
                    echo '<tr><td class="rowtitle">Assignment</td></tr>';
                    echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $likeditem->value . '">' . $module->name . '</a></td></tr>';
                    echo '<tr><td class="rowintro">' . $module->intro . '</td></tr>';
                    echo '<tr><td class="rowrating">' . ($likeditem->counts) . ' ' . $msg . '</td></tr>';
                    echo '</table>';
                    break;
                case 'assignment':
                    $module = $DB->get_record('assignment', array('id' => $likeditem->instance));
                    echo '<table class="maintable">';
                    echo '<tr><td class="rowtitle">Assignment</td></tr>';
                    echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/assignment/view.php?id=' . $likeditem->value . '">' . $module->name . '</a></td></tr>';
                    echo '<tr><td class="rowintro">' . $module->intro . '</td></tr>';
                    echo '<tr><td class="rowrating">' . ($likeditem->counts) . '  ' . $msg . '</td></tr>';
                    echo '</table>';
                    break;
                case 'storywall':
                    $module = $DB->get_record('storywall', array('id' => $likeditem->instance));
                    echo '<table class="maintable">';
                    echo '<tr><td class="rowtitle">Storywall</td></tr>';
                    echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/storywall/view.php?id=' . $likeditem->value . '">' . $module->name . '</a></td></tr>';
                    echo '<tr><td class="rowintro">' . $module->intro . '</td></tr>';
                    echo '<tr><td class="rowrating">' . ($likeditem->counts) . ' ' . $msg . '</td></tr>';
                    echo '</table>';
                    break;
                case 'forum':
                    $module = $DB->get_record('forum', array('id' => $likeditem->instance));
                    echo '<table class="maintable">';
                    echo '<tr><td class="rowtitle">Forum</td></tr>';
                    echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/forum/view.php?id=' . $likeditem->value . '">' . $module->name . '</a></td></tr>';
                    echo '<tr><td class="rowintro">' . $module->intro . '</td></tr>';
                    echo '<tr><td class="rowrating">' . ($likeditem->counts) . ' ' . $msg . '</td></tr>';
                    echo '</table>';
                    break;
                case 'sociopedia':
                    $module = $DB->get_record('sociopedia', array('id' => $likeditem->instance));
                    echo '<table class="maintable">';
                    echo '<tr><td class="rowtitle">Sociopedia</td></tr>';
                    echo '<tr><td class="rowname"><a href="' . $CFG->wwwroot . '/mod/sociopedia/view.php?id=' . $likeditem->value . '">' . $module->name . '</a></td></tr>';
                    echo '<tr><td class="rowintro">' . $module->intro . '</td></tr>';
                    echo '<tr><td class="rowrating">' . ($likeditem->counts) . ' ' . $msg . '</td></tr>';
                    echo '</table>';
                    break;
            }// end of switch..
        }// end of for
    }
} else {

    $sql = "SELECT * FROM {local_resourcecentral} WHERE courseid={$id} ";
    $query = $DB->get_records_sql($sql);
    if (!empty($query)) {
        foreach ($query as $file) {
            $context =context_user::instance($file->usermodified);
            $systemcontext =context_system::instance($file->usermodified);

            echo '<table class="maintable">';
            if (has_capability('local/collegestructure:manage', $systemcontext, $file->usermodified) && is_siteadmin($file->usermodified))
                echo '<tr><td class="rowtitle" colspan="3">Admin Generated Content</td></tr>';
            else if (has_capability('local/collegestructure:manage', $systemcontext, $file->usermodified) && !is_siteadmin($file->usermodified))
                echo '<tr><td class="rowtitle" colspan="3">Registrar Generated Content</td></tr>';
            else if (has_capability('local/clclasses:enrollclass', $context, $file->usermodified) && !is_siteadmin($file->usermodified))
                echo '<tr><td class="rowtitle" colspan="3">Student Generated Content</td></tr>';
            else {
                if (has_capability('local/clclasses:submitgrades', $systemcontext, $file->usermodified) && !is_siteadmin($file->usermodified))
                    echo'<tr><td class="rowtitle" colspan="3">Faculty Uploaded Content</td></tr>';
            }
            echo '<tr><td class="rowname" colspan="3"><a href="download.php?id=' . $file->id . '&course=' . $id . '">' . $file->title . '</a></td></td></tr>';
            echo '<tr><td class="rowusername" colspan="3">by ' . $DB->get_field('user', 'firstname', array('id' => $file->usermodified)) . '
    ' . $DB->get_field('user', 'lastname', array('id' => $file->usermodified)) . '
    ' . date('l, d F Y, g:i A', $file->timecreated) . '</td></td></tr>';
            echo '<tr><td class="rowintro" colspan="3">' . $file->description . '</td></tr>';
            echo '<tr><td class="rowrating" id="rate' . $file->id . '". colspan="3">' . get_like_comment_rating($id, $file->id, $file->id) . '</td></tr>';
            $a = display_like_unlike($id, $file->id, $file->id, 'Resource Central');
            $b = display_rating($id, $file->id, $file->id, 'Resource Central', 'Rating');
            $c = display_comment_area($id, $file->id, $file->id, 'Resource Central');
            echo '<tr><td>' . $a . '</td><td>' . $b . '</td><td>' . $c . '</td></tr>';
            echo '</table>';
        }
    }
}
echo '<div id="myratings"></div>';
echo $OUTPUT->footer();
