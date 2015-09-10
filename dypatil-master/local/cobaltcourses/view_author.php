<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $data, $DB;
$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);
/* ---get the required layout--- */
$PAGE->set_pagelayout('admin');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$authorintro = get_string('author_introduction', 'local_cobaltcourses');
$PAGE->navbar->add($authorintro);
$PAGE->requires->css('/local/cobaltcourses/courseera.css');
echo $OUTPUT->header();
$id = optional_param('id', -1, PARAM_INT);
$result = $DB->get_record_sql("select * from {user} where id = $id");
?>
<div class="syllabus">
    <h1 class="viewh1"><?php echo $result->firstname . ' ' . $result->lastname ?> </h1>
    <div class="view_auth_img"><a href=""><?php echo $OUTPUT->user_picture($result, array('size' => 100)) ?></a>
        <div class="view_auth_detail">
            <table >
                <tr><td class="align"><?php echo get_string('emailid', 'local_admission'); ?>  </td><td><?php echo ' : ' . $result->email ?></td></tr>
                <tr><td class="align"><?php echo get_string('phone'); ?>  </td><td><?php echo ' : ' . $result->phone1 ?></td></tr>
            </table>
        </div>
    </div>
    <div class="view_auth_desc"><?php echo $result->description; ?></div>
</div>
<?php
echo $OUTPUT->footer();
?>
