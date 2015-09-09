<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $DB;
$classid = optional_param('classid', 0, PARAM_INT);
$moocid = optional_param('moocid', 0, PARAM_INT);

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();

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
$PAGE->navbar->add(get_string('modcostsettings', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/modcost.php'));
$PAGE->navbar->add(get_string('addprice', 'local_onlinepayment'));
$PAGE->set_url('/local/onlinepayment/modcost.php');
$returnurl = new moodle_url('/local/onlinepayment/modcost.php');

if (!$classid && !$moocid) {
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('addprice', 'local_onlinepayment'));
if ($classid) {
    //if($DB->record_exists('local_item', array('moduleid'=>$classid))){
    //    $message = get_string('paymentsdonedontchange', 'local_onlinepayment');
    //    $style = array('style'=>'notifyproblem');
    //    $hierarchy->set_confirmation($message, $returnurl, $style);
    //}

    $record = $DB->get_record('local_clclasses', array('id' => $classid));
    $cobaltcourse = $DB->get_record('local_cobaltcourses', array('id' => $record->cobaltcourseid));
    $record->class_name = $record->fullname;
    $record->module_name = $cobaltcourse->fullname;
    $record->credithours = $cobaltcourse->credithours;
    if ($record->onlinecourseid) {
        $record->mooc_name = $DB->get_field('course', 'fullname', array('id' => $record->onlinecourseid));
    }
    $record->classid = $classid;
    if ($cost = $DB->get_record('local_classcost', array('classid' => $classid))) {
        if ($cost->classcost != 0) {
            $record->cost = $cost->classcost;
            $record->costper = 1;
        } else if ($cost->credithourcost != 0) {
            $record->cost = $cost->credithourcost;
            $record->costper = 2;
        }

        $discounts = $DB->get_records('local_costdiscounts', array('costid' => $cost->id));
        $i = 0;
        foreach ($discounts as $key => $discount) {
            $record->discount[] = $discount->discount;
            $record->discountcode[] = $discount->discountcode;
            $start = 'startdate[' . $i . ']';
            $end = 'enddate[' . $i . ']';
            $record->$start = $discount->startdate;
            $record->$end = $discount->enddate;
            $record->descid[] = $discount->id;
            $i++;
        }
    }
}
if ($moocid) {
    $record = $DB->get_record('course', array('id' => $moocid));
    $record->mooc_name = $record->fullname;
    $record->moocid = $moocid;
    if ($cost = $DB->get_record('local_classcost', array('courseid' => $moocid))) {
        $record->cost = $cost->coursecost;

        $discounts = $DB->get_records('local_costdiscounts', array('costid' => $cost->id));
        $i = 0;
        foreach ($discounts as $key => $discount) {
            $record->discount[] = $discount->discount;
            $record->discountcode[] = $discount->discountcode;
            $start = 'startdate[' . $i . ']';
            $end = 'enddate[' . $i . ']';
            $record->$start = $discount->startdate;
            $record->$end = $discount->enddate;
            $record->descid[] = $discount->id;
            $i++;
        }
    }
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

$addcost = new addcost_form(null, (array) $record);
$addcost->set_data($record);
if ($addcost->is_cancelled()) {
    redirect('modcost.php');
} else if ($data = $addcost->get_data()) {
    $data->currencycode = 'GBP';
    //print_object($data);exit;
    if ($data->classid) {
        if ($data->costper == 1) {
            $data->classcost = $data->cost;
            $data->credithourcost = 0;
        } else {
            $data->credithourcost = $data->cost;
            $data->classcost = 0;
        }
        if ($cost = $DB->get_record('local_classcost', array('classid' => $data->classid))) {
            $data->id = $cost->id;
            $DB->update_record('local_classcost', $data);
        } else {
            $data->id = $DB->insert_record('local_classcost', $data);
        }
    } else {
        $data->courseid = $data->moocid;
        $data->coursecost = $data->cost;
        if ($cost = $DB->get_record('local_classcost', array('courseid' => $data->courseid))) {
            $data->id = $cost->id;
            $DB->update_record('local_classcost', $data);
        } else {
            $data->id = $DB->insert_record('local_classcost', $data);
        }
    }
    foreach ($data->discount as $k => $v) {
        $desc = new stdClass();
        $desc->id = -1;
        $desc->costid = $data->id;
        $desc->discount = $v;
        $desc->discountcode = $data->discountcode[$k];
        $start = 'startdate[' . $k . ']';
        $end = 'enddate[' . $k . ']';
        $desc->startdate = $data->$start;
        $desc->enddate = $data->$end;
        $desc->time = time();
        $desc->user = $USER->id;
        if ($data->descid[$k]) {
            $desc->id = $data->descid[$k];
            $DB->update_record('local_costdiscounts', $desc);
        } else {
            $DB->insert_record('local_costdiscounts', $desc);
        }
    }
    redirect('modcost.php');
}
$addcost->display();
echo $OUTPUT->footer();
