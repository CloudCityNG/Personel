<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $DB;

$month = optional_param('month', 0, PARAM_INT);
$year = optional_param('year', 0, PARAM_INT);
$startdate = optional_param('datefrom', 0, PARAM_INT);
$enddate = optional_param('dateto', 0, PARAM_INT);

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();

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

$record = new stdClass();
$record->id = -1;
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

$PAGE->set_title(get_string('reports', 'local_onlinepayment'));
$PAGE->set_heading(get_string('report', 'local_onlinepayment'));
$PAGE->navbar->add(get_string('billingmodule', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/paymentstatus.php'));
$PAGE->navbar->add(get_string('report', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/modcost.php'));
$PAGE->set_url('/local/onlinepayment/report.php');
$returnurl = new moodle_url('/local/onlinepayment/report.php');

//display the page
echo $OUTPUT->header();
$tax->createtabview('reports');
$tax->get_report_headings('vatreport');
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewvatreportpage', 'local_onlinepayment'));
}

$filterform = new vatreportfilter_form();
$filterform->display();

if ($getdata = $filterform->get_data()) {
    $startdate = $getdata->startdate;
    $enddate = $getdata->enddate;
    $month = $getdata->month;
    $year = $getdata->year;
}
if ($DB->record_exists('local_tax_rate', array('typeid' => 1))) {
    //$mysql = "SELECT tra.*, CONCAT(user.firstname, ' ', user.lastname) AS studentname, user.country
    //            FROM {local_payment_transaction} AS tra
    //            JOIN {user} AS user ON user.id = tra.userid
    //            WHERE tra.status = 'Success' ";
    $mysql = "SELECT item.id, item.itemtype, item.moduleid, item.online_courseid, item.item_amount AS amount, tra.orderid, tra.transactionid, tra.timecreated,
                    user.id AS userid, CONCAT(user.firstname, ' ', user.lastname) AS studentname, user.country
                    FROM {local_item} AS item
                    JOIN {local_payment_transaction} AS tra ON tra.orderid = item.orderid
                    JOIN {user} AS user ON user.id = tra.userid
                    WHERE tra.status = 'Success' ";

    if ($month) {
        $mysql .= " AND FROM_UNIXTIME(tra.timecreated, '%m') = '{$month}' ";
    }
    if ($year) {
        $mysql .= " AND FROM_UNIXTIME(tra.timecreated, '%Y') = '{$year}' ";
    }
    if ($startdate) {
        $start = date('Y-m-d', $startdate);
        $mysql .= " AND FROM_UNIXTIME(tra.timecreated, '%Y-%m-%d') >= '{$start}' ";
    }
    if ($enddate) {
        $end = date('Y-m-d', $enddate);
        $mysql .= " AND FROM_UNIXTIME(tra.timecreated, '%Y-%m-%d') <= '{$end}' ";
    }
    $mysql .= " ORDER BY tra.timecreated DESC ";
    $reports = $DB->get_records_sql($mysql);

    $data = array();
    $ctrs = array();
    foreach ($reports as $report) {
        $ctrs[$report->id] = $report->country;
    }

    $euCountries = $tax->get_eu_countries();
    $grandtotal = 0;

    /* ---Group the UK, EU Countries and non EU Countries--- */
    //Group UK countries---------------
    $subtotal = 0;
    $satisfy1 = false;
    foreach ($ctrs as $k => $ctr) {
        if ($ctr == 'GB') {
            list($data[], $subtotal) = $tax->display_vat_data($reports, $k, $subtotal);
            $satisfy1 = true;
            unset($ctrs[$k]);
        }
    }
    if ($satisfy1)
        $data[] = array('', '', '', '', '', '<b>Sub-Total</b>', '<b>&pound ' . $subtotal . '</b>');
    $grandtotal += $subtotal;

    //Group all EU countries---------------
    $subtotal = 0;
    $satisfy2 = false;
    foreach ($ctrs as $k => $ctr) {
        if (in_array($ctr, array_keys($euCountries))) {
            list($data[], $subtotal) = $tax->display_vat_data($reports, $k, $subtotal);
            $satisfy2 = true;
            unset($ctrs[$k]);
        }
    }
    if ($satisfy2)
        $data[] = array('', '', '', '', '', '<b>Sub-Total</b>', '<b>&pound ' . $subtotal . '</b>');
    $grandtotal += $subtotal;

    //Group all Non-EU countries---------------
    $subtotal = 0;
    $satisfy3 = false;
    foreach ($ctrs as $k => $ctr) {
        list($data[], $subtotal) = $tax->display_vat_data($reports, $k, $subtotal);
        $satisfy3 = true;
        unset($ctrs[$k]);
    }
    if ($satisfy3)
        $data[] = array('', '', '', '', '', '<b>Sub-Total</b>', '<b>&pound ' . $subtotal . '</b>');
    $grandtotal += $subtotal;

    //Grand total of all VAT amount
    if ($satisfy1 || $satisfy2 || $satisfy3)
        $data[] = array('', '', '', '', '', '<b>Grand-Total</b>', '<b>&pound ' . $grandtotal . '</b>');

    $PAGE->requires->js('/local/onlinepayment/js/vatreport.js');
    if (empty($data)) {
        echo get_string('norecordsfound', 'local_onlinepayment');
    }
    $vat_help = $OUTPUT->help_icon('vatpercent', 'local_onlinepayment', '');
    $table = new html_table();
    $table->id = "vatreporttable";
    $table->head = array(get_string('studentname', 'local_courseregistration'),
        //get_string('transactionid', 'local_onlinepayment'),
        get_string('amountpaid', 'local_onlinepayment'),
        get_string('paymentdate', 'local_onlinepayment'),
        get_string('module', 'local_onlinepayment'),
        get_string('country'),
        get_string('vatpercent', 'local_onlinepayment') . $vat_help,
        get_string('vatamount', 'local_onlinepayment')
    );
    $table->size = array('15%', '10%', '10%', '15%', '15%', '10%', '10%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center');
    $table->width = '100%';
    $table->data = $data;
    if (!empty($data)) {
        echo html_writer::table($table);
        $link = html_writer::tag('a', 'Excel', array('href' => $CFG->wwwroot . '/local/onlinepayment/vatreport_xl.php?format=xls&?month=' . $month . '&year=' . $year . '&startdate=' . $startdate . '&enddate=' . $enddate . ''));
        echo '<p>Download: ' . $link . '</p>';
    }
} else { // if no VAT rate exists
    echo get_string('norecordsfound', 'local_onlinepayment');
}
echo $OUTPUT->footer();
