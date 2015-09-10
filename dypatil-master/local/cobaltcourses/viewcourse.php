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
$authorintro = "Overview Cobalt Course";
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
        <h4>Course Summary </h4>
        <?php echo $result->summary ?>
    </div>
    <table width ="100%">
        <tr>
            <td><h4>Department</h4></td><td><h4>Course Type</h4></td><td><h4>Credit Hours</h4></td><td><h4>Course Cost</h4></td>
        </tr>
        <tr>
            <td><?php echo $DB->get_field('local_department', 'fullname', array('id' => $result->departmentid)); ?></td>
            <td><?php
                if ($result->coursetype == 0) {
                    echo 'General';
                } else {
                    echo 'Elective';
                }
                ?></td>
            <td><?php echo $result->credithours; ?></td>
            <td><?php echo $result->coursecost; ?></td>
        </tr>
    </table>
</div>
<div>
</div>
<hr>
<div class="syllabus">
    <h4>Author  Introduction </h4>
    <table>
        <?php
        $sql = "select u.* from {local_scheduleclass} sc ,  {user} u, {local_school_permissions} sp  where sc.courseid = $id and sc.instructorid = sp.userid and sp.userid=u.id and sp.roleid= 10";
        $results = $DB->get_records_sql($sql);
        foreach ($results as $result) {
            ?>
            <tr class="auth"><td class="auth_desc" style='width:75%'><?php
                    $string = $result->description;

                    if (strlen($string) > 50) {
                        /* ---truncate string--- */
                        $stringCut = substr($string, 0, 400);
                        $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . '... <a href="../../local/cobaltcourses/view_author.php?id=' . $result->id . '">Read More</a>';
                    }
                    echo $string;
                    ?></td><td class="auth_img" style='width:25%;text-align: center'><a href=""><?php echo $OUTPUT->user_picture($result, array('size' => 100)) ?></a>
                    <br><?php echo '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $result->id . '" style="font-size:15px">' . $result->username . '</a>'; ?><br>
                    <?php echo "<a href = 'mailto:$result->email' style='font-size:13px'>$result->email</a>"; ?></td></tr>
            <?php
        }
        echo '</table>';
        echo $OUTPUT->footer();
        ?>
