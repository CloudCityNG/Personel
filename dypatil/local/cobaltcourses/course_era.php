<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $data;
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
$authorintro = get_string('authorintronav', 'local_cobaltcourses');
$PAGE->navbar->add($authorintro);
echo $OUTPUT->header();
?>
<div class="heading">
    <?php
    global $DB;
    $id = optional_param('id', 74, PARAM_INT);
    $result = $DB->get_record_sql("select * from {local_cobaltcourses} where id = $id");
    ?>
    <h1><?php echo $result->fullname ?></h1>
    <div class="course_sum" >
        <h4><?php echo get_string('coursesum', 'local_cobaltcourses') ?></h4>
        <?php echo $result->summary ?>
    </div>
</div>
<div>
    <?php
    if (isloggedin()) {
        echo'<div  class="enroll"><a href="../local/courseregistration"><button>' . get_string('enrollcourse', 'local_cobaltcourses') . '</button></a></div>';
    } else {
        echo'<div   class="enroll"><a href="../login/index.php"><button>' . get_string('enrollcourse', 'local_cobaltcourses') . '</button></a></div>';
    }
    ?>
</div>
<div class="syllabus">
    <h4><?php echo get_string('authorintro', 'local_cobaltcourses') ?></h4>
    <table>
        <?php
        $sql = "select u.* from {local_scheduleclass} sc ,  {user} u, {local_school_permissions} sp  where sc.courseid = $id and sc.instructorid = sp.userid and sp.userid=u.id and sp.roleid= 10";
        $results = $DB->get_records_sql($sql);
        foreach ($results as $result) {
            ?>
            <tr class="auth"><td class="auth_desc"><?php
                    $string = $result->description;

                    if (strlen($string) > 100) {
                        /* ---truncate string--- */
                        $stringCut = substr($string, 0, 100);
                        $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . '... <a href="../../local/cobaltcourses/view_author.php?id=' . $result->id . '">Read More</a>';
                    }
                    echo $string;
                    ?></td><td class="auth_img"><a href=""><?php echo $OUTPUT->user_picture($result, array('size' => 100)) ?></a><span><?php echo '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $result->id . '">' . $result->username . '</a>'; ?></span></td></tr>
            <?php
        }
        echo '</table>';
        echo $OUTPUT->footer();
        ?>
