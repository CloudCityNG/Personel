<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $DB;
$id = optional_param('id', -1, PARAM_INT);
$schoolid = optional_param('schoolid', 0, PARAM_INT);
$programid = optional_param('programid', 0, PARAM_INT);
$cobaltcourseid = optional_param('cobaltcourseid', 0, PARAM_INT);
$moodlecourseid = optional_param('moodlecourseid', 0, PARAM_INT);
$PAGE->requires->css('/local/ratings/css/jquery-ui.css');
//$PAGE->requires->js('/local/onlinepayment/js/addmodcost.js');

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();
//if ($id > 0) {
//    //get the records from the table to edit
//    if (!$record = $DB->get_record('local_accounting_period', array('id'=>$id))) {
//        print_error('invalidid', 'local_onlinepayment');
//    }
//    $record->school_name = $DB->get_field('local_school', 'fullname', array('id'=>$record->schoolid));
//} else {
// To create a new Tax Type
$record = new stdClass();
$record->id = -1;
//}
//If the loggedin user have the required capability allow the page
if (!has_capability('local/payment:createtax', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_title(get_string('modcostsettings', 'local_onlinepayment'));
$PAGE->set_heading(get_string('modcostsettings', 'local_onlinepayment'));
$PAGE->navbar->add(get_string('modcostsettings', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/modcost.php', array('id' => $id)));
$PAGE->set_url('/local/onlinepayment/modcost.php', array('id' => $id));
$returnurl = new moodle_url('/local/onlinepayment/modcost.php');


$PAGE->navbar->add(get_string('modcostsettings', 'local_onlinepayment'));
$filterform = new modcostfilter_form();

//display the page
echo $OUTPUT->header();
$tax->createtabview('settings');

$tax->get_inner_headings('modcost');

if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('modcostsettingspage', 'local_onlinepayment'));
}
// Display the form
$filterform->display();
$getdata = $filterform->get_data();

$sql = "SELECT class.*, cost.classcost, cost.credithourcost, cost.currencycode, cost.time, cost.user FROM {local_clclasses} AS class LEFT JOIN {local_classcost} AS cost ON cost.classid = class.id";
if ($programid) {
    $sql .= " JOIN {local_curriculum_plancourses} AS plan ON plan.courseid = class.cobaltcourseid
                    JOIN {local_curriculum} AS cur ON cur.id = plan.curriculumid ";
}
$sql .= " WHERE class.visible = 1 ";
if ($schoolid) {
    $sql .= " AND class.schoolid = {$schoolid} ";
}
if ($cobaltcourseid) {
    $sql .= " AND class.cobaltcourseid = {$cobaltcourseid}";
}
if ($moodlecourseid) {
    $sql .= " AND class.onlinecourseid = {$moodlecourseid}";
}
if ($programid) {
    $sql .= " AND cur.programid = {$programid} ";
}
$sql .= " GROUP BY class.id ";

$clclasses = $DB->get_records_sql($sql);
//$clclasses = $DB->get_records('local_clclasses');
$data = array();
foreach ($clclasses as $class) {
    $line = array();
    $course = $DB->get_record('local_cobaltcourses', array('id' => $class->cobaltcourseid));
    $line[] = $class->fullname; //coursename (modulename)
    //$line[] = date('d M, Y', $class->startdate); 
    //$line[] = date('d M, Y', $class->enddate);
    if ($class->classcost != 0)
        $cost = '&pound; ' . $class->classcost;
    else if ($class->credithourcost != 0)
        $cost = '&pound; ' . ($course->credithours * $class->credithourcost);
    else
        $cost = '-';
    $line[] = $cost; //class price
    $line[] = html_writer::tag('a', get_string('viewdetails', 'local_onlinepayment'), array('href' => $CFG->wwwroot . '/local/onlinepayment/viewcost.php?id=' . $class->id . '&type=class', 'target' => '_blank')); //View details

    $class->updateddate = $class->time ? $class->time : $class->timecreated;
    $line[] = date('d M, Y', $class->updateddate);

    $class->modifiedby = $class->user ? $class->user : $class->usermodified;
    $user = $DB->get_record('user', array('id' => $class->modifiedby));
    $line[] = fullname($user);
    $actionstring = ($class->classcost != 0 || $class->credithourcost != 0) ? 'Update' : 'Add';
    $button = html_writer::tag('button', $actionstring, array());
    $line[] = html_writer::tag('a', $button, array('href' => $CFG->wwwroot . '/local/onlinepayment/addcost.php?classid=' . $class->id));
    $data[] = $line;
}

if (!$getdata || $moodlecourseid) {
    $sql = "SELECT course.*, cost.coursecost, cost.currencycode, cost.time, cost.user FROM {course} AS course LEFT JOIN {local_classcost} AS cost ON cost.courseid = course.id WHERE ";
    if ($moodlecourseid) {
        $sql .= " course.id = {$moodlecourseid}";
    } else {
        $sql .= " course.id != 1 ";
    }
    $mooc_courses = $DB->get_records_sql($sql);
    foreach ($clclasses as $class) {
        if ($class->onlinecourseid) {
            unset($mooc_courses[$class->onlinecourseid]);
        }
    }
    foreach ($mooc_courses as $course) {
        $line = array();
        $line[] = $course->fullname; //coursename (modulename)
        //$line[] = ($course->startdate) ? date('d M, Y', $course->startdate) : '-'; 
        //$line[] = '-';
        $line[] = ($course->coursecost != 0) ? '&pound; ' . $course->coursecost : '-'; //class price
        $line[] = html_writer::tag('a', get_string('viewdetails', 'local_onlinepayment'), array('href' => $CFG->wwwroot . '/local/onlinepayment/viewcost.php?id=' . $course->id . '&type=mooc', 'target' => '_blank')); //View details

        $course->updateddate = $course->time ? $course->time : $course->timemodified;
        $line[] = date('d M, Y', $course->updateddate);

        $class->modifiedby = $course->user ? $course->user : 2;
        $user = $DB->get_record('user', array('id' => $course->modifiedby));
        $line[] = fullname($user);

        $actionstring = ($course->coursecost != 0) ? 'Update' : 'Add';
        //if (has_capability('local/onlinepayment:manage', $systemcontext)){
        $button = html_writer::tag('button', $actionstring, array());
        $line[] = html_writer::tag('a', $button, array('href' => $CFG->wwwroot . '/local/onlinepayment/addcost.php?moocid=' . $course->id));
        //}
        $data[] = $line;
    }
}

$PAGE->requires->js('/local/onlinepayment/js/modcost.js');
//if(!empty($data)){
//    echo "<div id='filter-box' >";
//    echo '<div class="filterarea"></div></div>';
//}
if (empty($data)) {
    echo get_string('noperiodcreatedyet', 'local_onlinepayment');
}
$table = new html_table();
$table->id = "modcosttable";
$table->head = array(get_string('classmodname', 'local_onlinepayment'),
    //get_string('startdate', 'local_academiccalendar'),
    //get_string('enddate', 'local_academiccalendar'),
    get_string('price', 'local_onlinepayment'),
    get_string('viewdetails', 'local_onlinepayment'),
    get_string('updateddate', 'local_onlinepayment'),
    get_string('modifiedby', 'local_onlinepayment'),
    get_string('action'));
//if (has_capability('local/onlinepayment:manage', $systemcontext))
//$table->head[]=get_string('action');
$table->size = array('23%', '11%', '11%', '8%', '10%', '12%', '15%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center');
$table->width = '100%';
$table->data = $data;
if (!empty($data))
    echo html_writer::table($table);

echo '<div id="myaddcost"></div>';
echo $OUTPUT->footer();
