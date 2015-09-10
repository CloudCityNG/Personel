<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $DB;
$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = new moodle_url('/local/onlinepayment/accperiod.php');
$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();
// if (!has_capability('local/onlinepayment:view', $systemcontext)){
//    print_error('You dont have permissions');
//}
if ($id > 0) {


    //get the records from the table to edit
    if (!$record = $DB->get_record('local_accounting_period', array('id' => $id))) {
        print_error('invalidid', 'local_onlinepayment');
    }
    $record->school_name = $DB->get_field('local_school', 'fullname', array('id' => $record->schoolid));

    if ($check_for_order = $tax->check_academic_period_change($record)) {
        $message = get_string('paymentdonedontchange', 'local_onlinepayment', $record);
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
} else {
    // To create a new Tax Type
    $record = new stdClass();
    $record->id = -1;
}
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
$PAGE->set_title(get_string('accountingperiod', 'local_onlinepayment'));
$PAGE->set_heading(get_string('accountingperiod', 'local_onlinepayment'));
$PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/index.php', array('id' => $id)));
$PAGE->set_url('/local/onlinepayment/accperiod.php', array('id' => $id));


if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $data = $DB->get_record('local_school', array('id' => $record->schoolid));
        $message = get_string('deleteperiodsuccess', 'local_onlinepayment', $data);
        $options = array('style' => 'notifysuccess');
        $DB->delete_records('local_accounting_period', array('id' => $id));
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deleteperiod', 'local_onlinepayment');
    $PAGE->navbar->add($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_onlinepayment'));
    $yesurl = new moodle_url('/local/onlinepayment/accperiod.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delperiodconfirm', 'local_onlinepayment');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}


$PAGE->navbar->add(get_string('accountingperiod', 'local_onlinepayment'));
$params = array('id' => $id);
$settingform = new accountingperiod_form(null, $params);
$settingform->set_data($record);
if ($settingform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $settingform->get_data()) {
    if ($data->id > 0) {
        $DB->update_record('local_accounting_period', $data);
        $data = $DB->get_record('local_school', array('id' => $data->schoolid));
        $message = get_string('updateperiodsuccess', 'local_onlinepayment', $data);
    } else {
        $data->timecreated = time();
        $DB->insert_record('local_accounting_period', $data);
        $data = $DB->get_record('local_school', array('id' => $data->schoolid));
        $message = get_string('createperiodsuccess', 'local_onlinepayment', $data);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

//display the page
echo $OUTPUT->header();
$tax->createtabview('settings');

$tax->get_inner_headings('accperiod');


//echo $OUTPUT->single_button(new moodle_url('/local/onlinepayment/paytax.php'), get_string('createtaxtype', 'local_onlinepayment'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('createaccountingperiodpage', 'local_onlinepayment'));
}
//echo html_writer::tag('a', get_string('createtaxtype', 'local_onlinepayment'), array('href'=>$CFG->wwwroot.'/local/onlinepayment/paytax.php', 'style'=>'float:right;'));
// Display the form
//if (has_capability('local/onlinepayment:manage', $systemcontext))
$settingform->display();

echo $OUTPUT->box(get_string('noteforaccperiodpage', 'local_onlinepayment'));
$accperiods = $DB->get_records('local_accounting_period');
$data = array();
foreach ($accperiods as $accperiod) {
    $line = array();
    $line[] = $DB->get_field('local_school', 'fullname', array('id' => $accperiod->schoolid));
    $line[] = date('d M, Y', $accperiod->datefrom);
    $line[] = date('d M, Y', $accperiod->dateto);
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/onlinepayment/accperiod.php', array('id' => $accperiod->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/onlinepayment/accperiod.php', array('id' => $accperiod->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    //if (has_capability('local/onlinepayment:manage', $systemcontext))
    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
$PAGE->requires->js('/local/onlinepayment/js/accperiod.js');
if (!empty($data)) {
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
if (empty($data)) {
    echo get_string('noperiodcreatedyet', 'local_onlinepayment');
}
$table = new html_table();
$table->id = "accperiodtable";
$table->head = array(get_string('schoolname', 'local_collegestructure'), get_string('startdate', 'local_academiccalendar'), get_string('enddate', 'local_academiccalendar'), get_string('action'));
//if (has_capability('local/onlinepayment:manage', $systemcontext))
//$table->head[]=get_string('action');
$table->size = array('30%', '30%', '30%', '10%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '100%';
$table->data = $data;
if (!empty($data))
    echo html_writer::table($table);
echo $OUTPUT->footer();
