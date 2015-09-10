<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
$PAGE->requires->css('/local/courseregistration/styles.css');

$page = optional_param('page', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$cid = optional_param('cid', 0, PARAM_INT);
$baseurl = new moodle_url('/local/courseregistration/previousstudents.php?semid=' . $semid . '&cid=' . $cid . '');

$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/courseregistration/previousstudents.php');
$PAGE->set_heading(get_string('pluginname', 'local_courseregistration'));
$PAGE->navbar->add(get_string('pluginname', 'local_courseregistration'), new moodle_url('/local/courseregistration/myclasses.php'));
$PAGE->navbar->add(get_string('viewmystudents', 'local_courseregistration'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_courseregistration'));
$currenttab = 'previous_student_list';
student_progress_tabs($currenttab, $cid);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('studentprogress_desc', 'local_courseregistration'));
}

$semester = previous_semsof_instructor();
try {
    if ($semester == 0) {
        $e = get_string('exception_nosem', 'local_courseregistration');
        throw new Exception($e);
    } else {
        echo '<div class="selfilterposition">';
        $select = new single_select(new moodle_url('/local/courseregistration/previousstudents.php'), 'semid', $semester, $semid, null);
        $select->set_label(get_string('sem', 'local_courseregistration'));
        echo $OUTPUT->render($select);
        echo '</div>';
        $class = previous_class_instructor($semid);
        echo '<div class="selfilterposition">';
        $select = new single_select(new moodle_url('/local/courseregistration/previousstudents.php'), 'cid', $class, $cid, null);
        $select->set_label(get_string('class', 'local_courseregistration'));
        echo $OUTPUT->render($select);
        echo '</div>';
        $sql = "SELECT luc.id,u.firstname,u.lastname,u.id as userid,FROM_UNIXTIME('%d-%M-%Y',lastlogin) as lastlogin FROM {local_user_clclasses} luc,{user} u WHERE luc.semesterid={$semid} AND luc.classid={$cid} AND luc.registrarapproval=1 AND luc.userid=u.id";
        $query = $DB->get_records_sql($sql);
        $totalcount = count($query);
        $perpage = 5;
        $list = $page * $perpage;
        $enrolledusers = $DB->get_records_sql('' . $sql . ' LIMIT ' . $list . ',' . $perpage . '');
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
                $result[] = $enrolleduser->lastlogin;
                $progressbar = student_progress_bar_units($enrolleduser->userid, $semid, $cid);
                $result[] = $progressbar;
                $result[] = get_string('progress', 'local_courseregistration');
                $data[] = $result;
            }
            $table = new html_table();
            $table->head = array(
                get_string('userpic'),
                get_string('name'),
                get_string('lastlogin'),
                get_string('progressbar', 'local_courseregistration'),
                get_string('progress', 'local_courseregistration')
            );
            $table->size = array('10%', '15%', '15%', '20%', '15%');
            $table->align = array('left', 'left', 'left', 'left', 'left');
            $table->width = '99%';
            $table->data = $data;
            echo html_writer::table($table);
            echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

