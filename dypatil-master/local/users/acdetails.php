<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/request/lib/lib.php');
$id = required_param('id', PARAM_INT);
global $USER, $DB;

$profiletablecss = '/local/users/css/profiletable.css';
$PAGE->requires->css($profiletablecss);

$PAGE->set_url('/local/users/acdetails.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');

if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
if (!isloggedin() || isguestuser()) {
    print_error('You dont have permissions');
}
require_login();
$name = $DB->get_record('user', array('id' => $id));
$local_users = $DB->get_record('local_users', array('userid' => $id));

$PAGE->set_heading($SITE->fullname);
$strheading = get_string('acdetails', 'local_users');

$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$requests = new requests();
$userid = $id ? $id : $USER->id;       // Owner of the page
if ((!$user = $DB->get_record('user', array('id' => $userid))) || ($user->deleted)) {

    echo $OUTPUT->header();
    if (!$user) {
        echo $OUTPUT->notification(get_string('invaliduser', 'error'));
    } else {
        echo $OUTPUT->notification(get_string('userdeleted'));
    }
    echo $OUTPUT->footer();
    die;
}

$currentuser = ($user->id == $id);
$context = $usercontext = context_user::instance($id, MUST_EXIST);

$currentcss = '/local/users/css/styles.css';
$PAGE->requires->css($currentcss);

echo $OUTPUT->header();
echo $OUTPUT->heading(fullname($user));

$hierarchy = new hierarchy();
$schoollist = $hierarchy->get_assignedschools();
if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}
$schoollist = $hierarchy->get_school_parent($schoollist, '', false, false);
$schoollist = array_keys($schoollist);

list($usql, $params) = $DB->get_in_or_equal($schoollist);
$params = array_merge(array($id), $params);

$sql = "SELECT * FROM {local_userdata} WHERE userid = ? AND schoolid $usql";
$userdata = $DB->get_records_sql($sql, $params); //Assume student enrolled to only one school.

$_count = sizeof($userdata);
$myuser = users::getInstance();

foreach ($userdata as $data) {
    $row = array();
    $var = false;
    echo "<br/>";
    echo '<div id="acdetails">';
    echo '<div id="acheading" style="float:left;width:100%;"><div style="float:left;width:50%;">';
    echo "<table cellpadding='5' >";
    $list = $myuser->names($data);
    echo '<tr><td><b>' . get_string('serviceid', 'local_users') . '</b></td><td>: ' . $data->serviceid . '</td></tr>';
    echo '<tr><td><b>' . get_string('schoolid', 'local_collegestructure') . '</b></td><td>: ' . $list->school . '</td></tr>';
    echo '<tr><td><b>' . get_string('program', 'local_programs') . '</b></td><td>: ' . $list->program . '</td></tr>';
    echo '<tr><td><b>' . get_string('curriculum', 'local_curriculum') . '</b></td><td>: ' . $list->curriculum . '</td></tr>';

    echo '</table>';
    echo '</div><div style="float: right; width: 48%; margin-right: 10px;">';

    $courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $data->curriculumid));
    $course_count = sizeof($courses);

    echo '<table align="right"><tr><td colspan="2" align="right"><div style="margin: 8px 5px 12px 0;"><a target="_blank" href="' . $CFG->wwwroot . '/local/users/acdetails_pdf.php?id=' . $id . '&sid=' . $data->schoolid . '&pid=' . $data->programid . '">' . get_string('download', 'local_users') . '</a></div></td></tr>
        <tr><td>' . get_string('total_courses', 'local_users') . '</td><td>: <b>' . $course_count . '</b></td>';

    $enrolled = 0;
    $completed = 0;
    foreach ($courses as $course) {
        $cell = array();
        $list = $myuser->names($course);
        $cell[] = '<b><a target="_blank" href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->courseid . '" >' . $list->courseid . '</a>: </b>' . $list->coursename;
        $cell[] = $status = $myuser->get_coursestatus($course->courseid, $user->id);
        if ($status != 'Not Enrolled') {
            $var = true;
            $cell[] = $myuser->get_coursestatus($course->courseid, $user->id, true);
            $enrolled++;
            if ($status != 'Enrolled (Inprogress)')
                $completed++;
        } else {
            $cell[] = '';
        }
        $row[] = $cell;
    }

    echo '<tr><td>' . get_string('enrolled', 'local_users') . '</td><td>: <b>' . $enrolled . '</b></td></tr>';
    echo '<tr><td>' . get_string('completed', 'local_users') . '</td><td>: <b>' . $completed . '</b></td></tr>';
    echo '</table></div></div>';

    $table = new html_table();
    $table->align = array('left', 'left');
    $table->size = array('60%', '40%');
    $table->id = 'acdetailstable';
    $table->head = array('Courses', get_string('status'));
    if ($var) {
        $table->head[] = get_string('table_head', 'local_users');
        $table->size = array('45%', '30%', '25%');
    }
    $table->data = $row;

    echo html_writer::table($table);
    echo '</div>';
}
echo $OUTPUT->footer();
