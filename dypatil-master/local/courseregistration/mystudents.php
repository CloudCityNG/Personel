<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
//$PAGE->requires->js('/local/courseregistration/module.js');
//$PAGE->requires->js('/local/courseregistration/module1.js');
$PAGE->requires->css('/local/courseregistration/styles.css');
$id = optional_param('id', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$baseurl = new moodle_url('/local/courseregistration/mystudents.php?id=' . $id . '');
$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/courseregistration/mystudents.php');
$PAGE->set_heading(get_string('pluginname', 'local_courseregistration'));
$PAGE->navbar->add(get_string('pluginname', 'local_courseregistration'), new moodle_url('/local/courseregistration/myclasses.php'));
$PAGE->navbar->add(get_string('viewmystudents', 'local_courseregistration'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_courseregistration'));
$currenttab = 'present_student_list';
student_progress_tabs($currenttab, $id);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('studentprogress_desc', 'local_courseregistration'));
}
$record = $DB->get_record('local_clclasses', array('id' => $id));
$sql = "SELECT luc.id,luc.semesterid,u.firstname,u.lastname,u.id as userid,u.lastlogin as lastlogin FROM {local_user_clclasses} luc,{user} u WHERE luc.classid={$id} AND luc.registrarapproval=1 AND luc.userid=u.id";
$query = $DB->get_records_sql($sql);
$totalcount = count($query);
$perpage = 5;
$list = $page * $perpage;
$enrolledusers = $DB->get_records_sql('' . $sql . ' LIMIT ' . $list . ',' . $perpage . '');
try {
    if (empty($query)) {
        $e = get_string('exception_nousers', 'local_courseregistration');
        throw new Exception($e);
    } else {
        $data = array();
        foreach ($enrolledusers as $enrolleduser) {
            $result = array();
            $userid = $DB->get_record('user', array('id' => $enrolleduser->userid));
            $result[] = $OUTPUT->user_picture($userid, array('size' => 15));
            $result[] = html_writer::tag('a', $enrolleduser->firstname . ' ' . $enrolleduser->lastname, array('href' => '' . $CFG->wwwroot . '/local/users/profile.php?id=' . $enrolleduser->userid . ''));
            if ($enrolleduser->lastlogin == 0) {
                $result[] = 'Not Login';
            } else {
                $result[] = date('d-M-Y', $enrolleduser->lastlogin);
            }
            $progressbar = stu_online_ins_progress($enrolleduser->userid, $enrolleduser->semesterid, $id);
            $result[] = $progressbar;

            $result[] = count_ins_progress($enrolleduser->userid, $enrolleduser->semesterid, $id);
            $data[] = $result;
        }
        $table = new html_table();
        $table->head = array(
            get_string('userpic'),
            get_string('name'),
            get_string('lastlogin'),
            get_string('progressbar', 'local_courseregistration'),
            get_string('clsgrds', 'local_courseregistration')
        );
        $table->size = array('10%', '15%', '15%', '20%', '15%');
        $table->align = array('left', 'left', 'left', 'left', 'left');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>

<script src="demo.js"></script>
<link href="jquery.fs.tipper.css" rel="stylesheet" type="text/css" media="all" />
<script src="jquery.fs.tipper.js"></script>
<style>
    .tipped {  /*clear: both;*/ float: none; display: block;  }
    /*.tipped:hover { background: #777; }*/
    .button {
        margin:0px !important;
    }
</style>
<script>
    $(document).ready(function () {
        $(".tipped").tipper();
    });
</script>