<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once($CFG->dirroot . '/course/lib.php');
global $CFG, $data, $USER;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/course/course_era1.php');
/* ---get the required layout--- */
$PAGE->set_pagelayout('admin');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$PAGE->requires->css('/local/cobaltcourses/courseera.css');
$authorintro = "Overview of Course";
$PAGE->navbar->add($authorintro);
echo $OUTPUT->header();
?>
<div class="heading">
    <?php
    global $DB;
    $id = optional_param('id', 4, PARAM_INT);

    $result = $DB->get_record_sql("select * from {course} where id = $id");
    $context = context_course::instance($id);
    $content = '';
    if (!empty($CFG->coursecontact)) {
        $coursecontactroles = explode(',', $CFG->coursecontact);
        foreach ($coursecontactroles as $roleid) {
            if ($users = get_role_users($roleid, $context, true)) {
                foreach ($users as $teacher) {
                    $userid = $teacher->id;
                    $role = new stdClass();
                    $user = $DB->get_record('user', array('id' => $userid));
                    $role->id = $teacher->roleid;
                    $role->name = $teacher->rolename;
                    $role->shortname = $teacher->roleshortname;
                    $role->coursealias = $teacher->rolecoursealias;
                    $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));
                    if (empty($user->description)) {
                        $user->description = '<p><span>There is no description added for this Author.Please add description in user profile.</span></p>';
                    }


                    $namesarray[] = $user->description . $OUTPUT->user_picture($user, array('size' => 100)) . '</br> ' . '<span class = "teach_name" >' . $fullname . '</span>';
                }
            }
        }
        if (!empty($namesarray)) {
            $content .= "<ul id=\"teach\">\n<li style='list-style-type:none;'>";
            $content .= implode('</li><li style="list-style-type:none;padding-top: 35px;">', $namesarray);
            $content .= "</li></ul>";
        }
    }
    ?>
    <h1><?php echo $result->fullname ?></h1>
    <div class="course_sum" >
        <h3>Course Summary </h3>
        <?php
        $fs = get_file_storage();
        $context = context_course::instance($result->id);
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        foreach ($files as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
        }
        $summary = $result->summary;
        $summary = preg_replace("/<img[^>]+\>/i", "", $summary);
        if (strlen($summary) > 400) {
            $stringCut = substr($summary, 0, 400); // truncate string
            $summary = substr($stringCut, 0, strrpos($stringCut, ' ')) . '... <a href="../../course/view.php?id=' . $result->id . '">Read More</a>';
        }
        echo '<table><tr><td class="first"><div>' . $summary . '</div></td><td><img src="' . $url . '" height="180" width="220"></td></tr></table>';
        ?>
    </div>
</div>
<div>
    <?php
    $ssql = "SELECT u.id, u.username
FROM mdl_user u, mdl_role_assignments r
WHERE u.id=r.userid AND r.contextid = {$context->id} and u.id={$USER->id}";
    $enrolled_user = $DB->get_record_sql($ssql);

    if (isloggedin()) {
        if (empty($enrolled_user) AND ! is_siteadmin()) {
            echo'<div  class="enroll"><a href="../../enrol/index.php?id=' . $id . '"><button>Enrol into Course</button></a></div>';
        } else {
            echo'<div  class="enroll"><a href="../../course/view.php?id=' . $id . '"><button>Go to Course</button></a></div>';
        }
    } else {
        echo'<div   class="enroll"><a href="../../login/index.php?id=' . $id . '"><button>Enrol into Course</button></a></div>';
    }
    ?>
</div>
<div class="syllabus">
    <h3>Author  Introduction </h3>

    <?php
    echo $content;
    echo $OUTPUT->footer();
    ?>