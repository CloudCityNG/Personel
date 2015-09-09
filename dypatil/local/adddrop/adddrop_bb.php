<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/adddrop/lib.php');
global $CFG, $USER, $DB;
$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/adddrop/index.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageadddrop', 'local_adddrop'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('manageadddrop', 'local_adddrop'));
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_adddrop'));
}
$today = date("Y-m-d");
$query = "SELECT u.firstname,e.semester_id,u.batchid,e.program_id FROM {user} u,{local_event_activities} e WHERE u.id={$USER->id} AND u.batchid=e.batch_id ";

$studentregistar = $DB->get_records_sql($query);
$data = array();
$i = 0;
foreach ($studentregistar as $getsem) {
    $semid = $getsem->semester_id;
    $query = "SELECT c.id,c.shortname,c.fullname,c.credithours FROM {$CFG->prefix}dp_plan_module_assign pma,{$CFG->prefix}dp_custom_module_courses cmc,{$CFG->prefix}course c WHERE pma.planid={$semid} AND pma.moduleid=cmc.moduleid AND cmc.courseid=c.id ";
    $cList = $DB->get_records_sql($query);
    $credit = 0;
    foreach ($cList as $courseList) {
        $status = get_Existadddrop($USER->id, $courseList->id, $semid);
        $existregistration = get_existregistration($USER->id, $courseList->id, $semid);
        $line = array();
        $row = ($i % 2);

        $line[] = $courseList->shortname;
        $line[] = $courseList->fullname;
        $line[] = $courseList->credithours;

        if ($existregistration[0] == 1) {
            $line[] = ' <img src="' . $CFG->pixpath . '/t/clear.gif" alt="' . get_string('Registered Course', 'local_plan') . '" /> ';
        } else
            $line[] = '&nbsp;';

        if (!$existregistration[0] && !$status[0]) {
            $line[] = ' <a href="' . $CFG->wwwroot . '/local/plan/action3.php?id=' . $courseList->id . '&amp;CourseConf=1&action=requestregistration&amp;&semN=' . $semid . '&sesskey=' . sesskey() . '" title="' . get_string('approve', 'local_plan') . '">
             <img src="' . $CFG->pixpath . '/t/go.gif" alt="' . get_string('approve', 'local_plan') . '" /> </a>';
        } else if ($status[0] == 1 && $status[1] == 0)
            $line[] = get_string('wmapproval', 'local_adddrop');
        else if ($status[0] == 1 && $status[1] == 1 && $status[2] == 0)
            $line[] = get_string('wrapproval', 'local_adddrop');
        else if ($status[0] == 1 && $status[1] == 1 && $status[2] == 1)
            $line[] = get_string('addingcourse', 'local_adddrop');
        else if ($status[0] == 1 && $status[1] == 2)
            $line[] = get_string('rejectedbym', 'local_adddrop');
        else
            $line[] = '&nbsp;';

        if ($existregistration[0] == 1 && !$status[0]) {
            $line[] = '<a href="' . $CFG->wwwroot . '/local/plan/action3.php?id=' . $courseList->id . '&amp;CourseConf=1&action=reject&amp;&semN=' . $semid . '&sesskey=' . sesskey() . '" title="' . get_string('approve', 'local_plan') . '">
              <img src="' . $CFG->pixpath . '/t/delete.gif" alt="' . get_string('approve', 'local_plan') . '" /> </a>';
        } else if ($status[0] == 2 && $status[1] == 0)
            $line[] = get_string('wmapproval', 'local_adddrop');
        else if ($status[0] == 2 && $status[1] == 1 && $status[2] == 0)
            $line[] = get_string('wrapproval', 'local_adddrop');
        else if ($status[0] == 2 && $status[1] == 1 && $status[2] == 1)
            $line[] = get_string('addingcourse', 'local_adddrop');
        else if ($status[0] == 2 && $status[1] == 2)
            $line[] = get_string('rejectedbym', 'local_adddrop');
        else
            $line[] = '&nbsp;';

        $creditSum = $credit + $courseList->credithours;
        $credit = $creditSum;
        $i++;
    }
    $data[] = $line;
    print '' . $credit . '';
}
$table = new html_table();
$table->head = array(
    get_string('code', 'local_clclasses'), get_string('coursename', 'local_cobaltcourses'), get_string('credithours', 'local_adddrop'), get_string('add', 'local_adddrop'), get_string('drop', 'local_adddrop'));
$table->size = array('15%', '20%', '10%', '15%', '10%', '10%');
$table->align = array('center', 'center', 'center', 'center', 'center', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
?>
