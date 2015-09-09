<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage programs
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $PAGE, $DB;
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/lib.php');

$fullname = optional_param('fullname', '', PARAM_TEXT);
$orderid = optional_param('id', 0, PARAM_INT);
$hierarchy = new hierarchy();
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//If the loggedin user have the required capability allow the page
//if (!has_capability('local/payment:createtax', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->set_url('/local/onlinepayment/paymentstatus.php');
$PAGE->set_title(get_string('paymentdetails', 'local_onlinepayment'));
//Header and the navigation bar
$PAGE->set_heading(get_string('paymentdetails', 'local_onlinepayment'));

$PAGE->requires->css('/local/onlinepayment/css/style.css');
//$PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/index.php'));
$PAGE->navbar->add(get_string('paymentdetails', 'local_onlinepayment'));
echo $OUTPUT->header();
//Heading of the page
//echo $OUTPUT->heading(get_string('paymentdetails', 'local_onlinepayment'));
$trans = onlinepay_transaction::getInstance();


$tax = tax::getInstance();
$schoollist = $hierarchy->get_assignedschools();
if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}
$schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);

//$filter = new status_filter_form();
//$filter->display();
//$records = $tax->get_payment_status($schoollist, $fullname);
//print_object($records);
$data = array();
if ($orderid) {
    echo $OUTPUT->heading(get_string('paymentdetails', 'local_onlinepayment'));
    $record = $tax->get_payment_status($schoollist, $orderid);
    $semester = $DB->get_record('local_semester', array('id' => $record->semesterid));
    $school = $DB->get_record('local_school', array('id' => $record->schoolid));
    $courses = $tax->get_course_items($record->id);
    $courses = (object) $courses;
    $out = html_writer::start_tag('div', array('class' => 'pay_outermost')); //outer
    $out .= html_writer::start_tag('div', array('class' => 'pay_innerleft')); //inner left
    $out .= html_writer::start_tag('table', array('cellpadding' => '5'));
    $out .= '<tr><td><b>' . get_string('serviceid', 'local_users') . '</b></td><td><b>: </b>' . $record->serviceid . '</td></tr>';
    $out .= '<tr><td><b>' . get_string('studentname', 'local_onlinepayment') . ' </b></td><td><b>: </b>' . $record->fullname . '</td></tr>';
    $out .= '<tr><td><b>' . get_string('semester', 'local_semesters') . ' </b></td><td><b>: </b>' . $semester->fullname . '</td></tr>';
    $out .= '<tr><td><b>' . get_string('schoolid', 'local_collegestructure') . ' </b></td><td><b>: </b>' . $school->fullname . '</td></tr>';
    $out .= '<tr><td valign="baseline"><b>' . get_string('cobaltcourses', 'local_cobaltcourses') . '  </b></td><td><b>: </b>';
    $i = 1;
    foreach ($courses as $course) {
        if ($i > 1)
            $out .= '&nbsp;&nbsp;';
        $out .= '<b>' . $course->shortname . ':</b> ' . $course->fullname /* .' &nbsp;($'.$course->coursecost.')' */ . '<br/>';
        $i++;
    }
    $out .= '</td></tr>';
    $out .= html_writer::end_tag('table');
    $out .= html_writer::end_tag('div');

    //$total = $DB->get_record('local_cobalt_order_total', array('orderid'=>$record->id));
    $out .= html_writer::start_tag('div', array('class' => 'pay_innerright')); //inner right
    $out .= html_writer::start_tag('table', array('cellpadding' => '5'));
    $traId = $record->transactionid ? $record->transactionid : ' - ';
    $out .= '<tr><td><b>' . get_string('transactionid', 'local_onlinepayment') . ' </b></td><td><b>: </b>' . $traId . '</td></tr>';
    //$out .= '<tr><td><b>Total Amount </b></td><td><b>: </b>$'.$total->nettotal.'</td></tr>';
    //$out .= '<tr><td><b>Tax added </b></td><td><b>: </b>$'.$total->taxadded.'</td></tr>';
    $out .= '<tr><td><b>' . get_string('amountpaid', 'local_onlinepayment') . ' </b></td><td><b>: </b>&pound ' . $record->amount . '</td></tr>';
    $out .= '<tr><td></td><td><b> </b>' . $output_info = $trans->get_tax_information($record->userid) . '</td></tr>';
    $out .= '<tr><td><b>' . get_string('paymentmethod', 'local_onlinepayment') . ' </b></td><td><b>: </b>' . $record->payment_method . '</td></tr>';
    $out .= '<tr><td><b>' . get_string('paidon', 'local_onlinepayment') . ' </b></td><td><b>: </b>' . date('d M, Y', $record->paidon) . '</td></tr>';
    $out .= '<tr><td><b>' . get_string('paymentstatus', 'local_onlinepayment') . ' </b></td><td><b>: </b>' . $record->paymentstatus . '</td></tr>';
    $out .= html_writer::end_tag('table');
    $out .= html_writer::end_tag('div');
    $out .= html_writer::end_tag('div');
    echo $out;
} else {
    $tax->createtabview('orders');
    echo $OUTPUT->heading(get_string('paymentorders', 'local_onlinepayment'));
    $records = $tax->get_payment_status($schoollist, $orderid);
    foreach ($records as $record) {
        //$semester = $DB->get_record('local_semester', array('id'=>$record->semesterid));
        $courses = $tax->get_course_items($record->id);
        $countss = count($courses); // used to restrict the commas
        $courses = (object) $courses;

        $school = $DB->get_record('local_school', array('id' => $record->schoolid));
        $line = array();
        $line[] = $record->serviceid;
        $line[] = html_writer::tag('a', $record->fullname, array('href' => $CFG->wwwroot . '/local/users/profile.php?id=' . $record->userid));
        $traId = $record->transactionid ? $record->transactionid : ' - ';
        $line[] = $traId;
        $paidcrs = '';

        foreach ($courses as $key => $course) {
            $commas = (($countss - 1) == $key ? '' : ',');
            $paidcrs .= '<div>' . $course->fullname . $commas . '</div>';
        }
        $line[] = $paidcrs;
        $line[] = '&pound ' . $record->amount;
        $line[] = date('d M, Y', $record->paidon);
        $line[] = html_writer::tag('a', 'More Details', array('href' => $CFG->wwwroot . '/local/onlinepayment/paymentstatus.php?id=' . $record->id, 'target' => '_blank'));

        //$line[] = html_writer::tag('a', fullname($user), array('href'=>$CFG->wwwroot.'/user/profile.php?id='.$user->id));
        //$course = $DB->get_record('local_cobaltcourses', array('id'=>$record->productid));
        //$line[] = html_writer::tag('a', $course->shortname, array('href'=>$CFG->wwwroot.'/local/cobaltcourses/view.php?id='.$course->id));
        //$line[] = 'amount paid';
        //$line[] = date('d M, Y', $record->timecreated);
        //$line[] = $record->status;
        //$line[] = $DB->get_field('local_semester', 'fullname', array('id'=>$detail->semesterid));
        $data[] = $line;
    }

    if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('viewpaymentstatuspage', 'local_onlinepayment'));
    }

    $PAGE->requires->js('/local/onlinepayment/js/paymentstatus.js');
    if (!empty($data)) {
        echo "<div id='filter-box' >";
        echo '<div class="filterarea"></div></div>';
    }
    if (empty($data)) {
        echo get_string('nopaymentsdone', 'local_onlinepayment');
    }
    $table = new html_table();
    $table->id = "paymentstatustable";
    $table->head = array(get_string('serviceid', 'local_onlinepayment'),
        get_string('studentname', 'local_onlinepayment'),
        //get_string('semester', 'local_onlinepayment'),
        get_string('transactionid', 'local_onlinepayment'),
        get_string('classmodname', 'local_onlinepayment'),
        get_string('totalamount', 'local_onlinepayment'),
        get_string('paymentdate', 'local_onlinepayment'),
        get_string('view'));
    $table->size = array('10%', '15%', '15%', '20%', '10%', '15%', '15%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '100%';
    $table->data = $data;
    if (!empty($data))
        echo html_writer::table($table);
}
echo $OUTPUT->footer();
