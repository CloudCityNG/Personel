<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $DB;
//
//$id = optional_param('id', -1, PARAM_INT);
$schoolid = optional_param('schoolid', 0, PARAM_INT);
$programid = optional_param('programid', 0, PARAM_INT);
$cobaltcourseid = optional_param('cobaltcourseid', 0, PARAM_INT);
//$moodlecourseid   = optional_param('moodlecourseid', 0, PARAM_INT);
//$PAGE->requires->css('/local/ratings/css/jquery-ui.css');
//$PAGE->requires->js('/local/onlinepayment/js/addmodcost.js');

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();

$record = new stdClass();
$record->id = -1;
//}
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//If the loggedin user have the required capability allow the page
if (!has_capability('local/payment:createtax', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

$PAGE->set_title(get_string('reports', 'local_onlinepayment'));
$PAGE->set_heading(get_string('report', 'local_onlinepayment'));
$PAGE->navbar->add(get_string('billingmodule', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/paymentstatus.php'));
$PAGE->navbar->add(get_string('report', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/modcost.php'));
$PAGE->set_url('/local/onlinepayment/report.php');
$returnurl = new moodle_url('/local/onlinepayment/report.php');
//
//
//$PAGE->navbar->add(get_string('modcostsettings', 'local_onlinepayment'));
$filterform = new reportfilter_form();

//display the page
echo $OUTPUT->header();
$tax->createtabview('reports');
$tax->get_report_headings('report');
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewbillingreportpage', 'local_onlinepayment'));
}
// Display the form
$filterform->display();
$startdate = 0;
$enddate = 0;
if ($getdata = $filterform->get_data()) {
    $startdate = $getdata->startdate;
    $enddate = $getdata->enddate;
}


$sql = "SELECT item.*, ord.semesterid, class.id AS classid, class.fullname AS classname, user.id AS userid, CONCAT(user.firstname, ' ', user.lastname) AS username,
              user.email, tra.status AS paidstatus, tra.timecreated AS paymentdate
              FROM {local_item} item
              JOIN {local_clclasses} class ON class.id = item.moduleid
              JOIN {local_order} ord ON ord.id = item.orderid
              JOIN {local_payment_transaction} tra ON tra.orderid = ord.id AND tra.userid = ord.userid
              JOIN {user} user ON user.id = ord.userid
              ";
if ($programid) {
    $sql .= " JOIN {local_curriculum_plancourses} AS plan ON plan.courseid = class.cobaltcourseid
                    JOIN {local_curriculum} AS cur ON cur.id = plan.curriculumid ";
}
if ($getdata) {
    $sql .= " WHERE class.visible = 1 ";
}
if ($schoolid) {
    $sql .= " AND class.schoolid = {$schoolid} ";
}
if ($cobaltcourseid) {
    $sql .= " AND class.cobaltcourseid = {$cobaltcourseid}";
}
if ($programid) {
    $sql .= " AND cur.programid = {$programid} ";
}
if ($startdate) {
    $datefrom = date('Y-m-d', $startdate);
    $sql .= " AND FROM_UNIXTIME(item.timecreated, '%Y-%m-%d') >= '{$datefrom}' ";
}
if ($enddate) {
    $dateto = date('Y-m-d', $enddate);
    $sql .= " AND FROM_UNIXTIME(item.timecreated, '%Y-%m-%d') <= '{$dateto}' ";
}
$sql .= " GROUP BY item.id ";

$reports = $DB->get_records_sql($sql);

$data = array();
foreach ($reports as $report) {
    $line = array();
    $line[] = $report->classname; //coursename (modulename)
    $line[] = $report->username; //studentname
    $userClass = $DB->get_record('local_user_clclasses', array('classid' => $report->classid, 'userid' => $report->userid, 'semesterid' => $report->semesterid, 'registrarapproval' => 1), '*', MUST_EXIST);
    $line[] = date('d M, Y', $userClass->timecreated);
    $line[] = '&pound ' . $report->item_amount;
    $line[] = $report->paidstatus;
    $line[] = date('d M, Y', $report->paymentdate);
    $line[] = $report->email;
    $line[] = '<a href="mailto:' . $report->email . '?Subject=Payment%20Transaction" target="_top">Contact</a>';
    $data[] = $line;
}

if (!$getdata || $startdate || $enddate) {
    $sql = "SELECT item.*, course.fullname AS coursename, CONCAT(user.firstname, ' ', user.lastname) AS username,
            user.id AS userid, user.email, tra.status AS paidstatus, tra.timecreated AS paymentdate
            FROM {local_item} AS item
            JOIN {course} AS course ON course.id = item.online_courseid
            JOIN {local_order} ord ON ord.id = item.orderid
            JOIN {local_payment_transaction} tra ON tra.orderid = ord.id AND tra.userid = ord.userid
            JOIN {user} user ON user.id = ord.userid
            ";
    if ($getdata) {
        $sql .= " WHERE course.visible = 1 ";
    }
    if ($startdate) {
        $datefrom = date('Y-m-d', $startdate);
        $sql .= " AND FROM_UNIXTIME(item.timecreated, '%Y-%m-%d') >= '{$datefrom}' ";
    }
    if ($enddate) {
        $dateto = date('Y-m-d', $enddate);
        $sql .= " AND FROM_UNIXTIME(item.timecreated, '%Y-%m-%d') <= '{$dateto}' ";
    }
    $sql .= " GROUP BY item.id ";

    $reports = $DB->get_records_sql($sql);
    foreach ($reports as $report) {
        $line = array();
        $line[] = $report->coursename; //coursename (modulename)
        $line[] = $report->username; //studentname
        $mysql = "SELECT * FROM {enrol} enrol JOIN {user_enrolments} userenrol ON userenrol.enrolid = enrol.id WHERE enrol.courseid = {$report->online_courseid} AND userenrol.userid = {$report->userid}";
        if ($enrolled = $DB->get_record_sql($mysql)) {
            $line[] = date('d M, Y', $enrolled->timecreated);
        } else {
            $line[] = '-';
        }
        $line[] = '&pound ' . $report->item_amount;
        $line[] = $report->paidstatus;
        $line[] = date('d M, Y', $report->paymentdate);
        $line[] = $report->email;
        $line[] = '<a href="mailto:' . $report->email . '?Subject=Payment%20Transaction" target="_top">Contact</a>';
        $data[] = $line;
    }
}

$PAGE->requires->js('/local/onlinepayment/js/modcost.js');
if (empty($data)) {
    echo get_string('norecordsfound', 'local_onlinepayment');
}
$table = new html_table();
$table->id = "modcosttable";
$table->head = array(get_string('classmodname', 'local_onlinepayment'),
    get_string('studentname', 'local_courseregistration'),
    get_string('enrolleddate', 'local_onlinepayment'),
    get_string('price', 'local_onlinepayment'),
    get_string('paymentstatus', 'local_onlinepayment'),
    get_string('paymentdate', 'local_onlinepayment'),
    get_string('email'),
    get_string('contact', 'local_admission')
);
$table->size = array('20%', '13%', '10%', '10%', '10%', '12%', '15%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center');
$table->width = '100%';
$table->data = $data;
if (!empty($data))
    echo html_writer::table($table);

echo $OUTPUT->footer();
