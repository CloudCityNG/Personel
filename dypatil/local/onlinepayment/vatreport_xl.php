<?php

/**
 * script for downloading of user lists
 */
require_once('../../config.php'); //this assumes your page is in a sub dir.
require_once($CFG->libdir . '/adminlib.php');
require_once('../../report/outline/locallib.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

$format = optional_param('format', '', PARAM_ALPHA);
$month = optional_param('month', 0, PARAM_INT);
$year = optional_param('year', 0, PARAM_INT);
$startdate = optional_param('startdate', 0, PARAM_INT);
$enddate = optional_param('enddate', 0, PARAM_INT);

global $DB, $CFG;
$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_heading($SITE->fullname);

if ($format) {
    $fields = array(get_string('studentname', 'local_courseregistration'),
        get_string('amountinpounds', 'local_onlinepayment'),
        get_string('paymentdate', 'local_onlinepayment'),
        get_string('module', 'local_onlinepayment'),
        get_string('country'),
        get_string('vatpercent', 'local_onlinepayment'),
        get_string('vatamountinpounds', 'local_onlinepayment')
    );

    switch ($format) {
        case 'xls' : vatreport_download_xls($fields, $month, $year, $startdate, $enddate);
    }
    die;
}


echo $OUTPUT->header();
echo $OUTPUT->footer();

function vatreport_download_xls($fields, $month, $year, $startdate, $enddate) {

    global $CFG, $DB, $USER;

    require_once("$CFG->libdir/excellib.class.php");
    //require_once($CFG->dirroot.'/user/profile/lib.php');
    $hierarchy = new hierarchy();
    $tax = tax::getInstance();
    $filename = clean_filename(get_string('vatreport', 'local_onlinepayment'));

    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);

    $worksheet = array();

    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }

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

    //$data = array();
    $ctrs = array();
    foreach ($reports as $report) {
        $ctrs[$report->id] = $report->country;
    }

    $countries = get_string_manager()->get_list_of_countries(false);
    $euCountries = $tax->get_eu_countries();
    $grandtotal = 0;

    /* ---Group the UK, EU Countries and non EU Countries--- */
    //Group UK countries---------------
    $i = 1;
    $sheetrow = 1;
    $subtotal = 0;
    $satisfy1 = false;
    foreach ($ctrs as $key => $ctr) {
        if ($ctr == 'GB') {
            $satisfy1 = true;
            $line = array();
            $line[] = $reports[$key]->studentname; //Student Name
            $line[] = $reports[$key]->amount;
            $line[] = date('d M, Y', $reports[$key]->timecreated);
            if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
                $line[] = $DB->get_field('local_clclasses', 'fullname', array('id' => $reports[$key]->moduleid));
            } else if ($reports[$key]->itemtype == 'mooctype' && $reports[$key]->online_courseid) {
                $line[] = $DB->get_field('course', 'fullname', array('id' => $reports[$key]->online_courseid));
            }
            //    $items = $DB->get_records('local_item', array('orderid'=>$reports[$key]->orderid));
            //    $modules = array();
            //    foreach($items as $item){
            //	if($item->itemtype=='classtype' && $item->moduleid){
            //	    $onlinecourseid = $DB->get_field('local_clclasses', 'onlinecourseid', array('id'=>$item->moduleid));;
            //	}
            //	if($item->itemtype=='mooctype' && $item->online_courseid){
            //	    $onlinecourseid = $item->online_courseid;
            //	}
            //	$course = $DB->get_record('course', array('id'=>$onlinecourseid));
            //	$modules[] = $course->fullname;
            //    }
            //    $line[] = implode(', ', $modules);
            $line[] = $countries[$reports[$key]->country]; //Student country
            $no_vat = false;
            if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
                $local_user = $DB->get_record('local_users', array('userid' => $reports[$key]->userid));
                $user_class = $DB->get_record('local_user_clclasses', array('userid' => $reports[$key]->userid, 'classid' => $reports[$key]->moduleid));
                $exist = $local_user && $user_class;
                if ($exist && $local_user->fundsbygovt && !$user_class->fundbyuk) {
                    $no_vat = true;
                }
            }
            if ($no_vat) {
                $line[] = 'No VAT applied';
                $line[] = '0';
                $subtotal += '0';
            } else
            if ($tax = $DB->get_record_select('local_tax_rate', "country = '" . $reports[$key]->country . "' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
                $line[] = $tax->rate . ' %';
                $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
                $line[] = $vatamount;
                $subtotal += $vatamount;
            } else if ($tax = $DB->get_record_select('local_tax_rate', "country = 'all' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
                $line[] = $tax->rate . ' %';
                $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
                $line[] = $vatamount;
                $subtotal += $vatamount;
            } else {
                $line[] = '0 %';
                $line[] = '0';
                $subtotal += '0';
            }
            $col = 0;
            foreach ($fields as $k => $v) {
                $worksheet[0]->write($sheetrow, $col, $line[$k]);
                $col++;
            }
            $sheetrow++;
            $i++;
            unset($ctrs[$key]);
        }
    }
    if ($satisfy1) {
        $line = array('', '', '', '', '', 'Sub-Total', $subtotal);
        $col = 0;
        foreach ($fields as $k => $v) {
            $worksheet[0]->write($sheetrow, $col, $line[$k]);
            $col++;
        }
        $sheetrow++;
        $i++;
    }
    $grandtotal += $subtotal;





    //Group all EU countries---------------
    $subtotal = 0;
    $satisfy2 = false;
    foreach ($ctrs as $key => $ctr) {
        if (in_array($ctr, array_keys($euCountries))) {
            $satisfy2 = true;
            $line = array();
            $line[] = $reports[$key]->studentname; //Student Name
            $line[] = $reports[$key]->amount;
            $line[] = date('d M, Y', $reports[$key]->timecreated);
            if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
                $line[] = $DB->get_field('local_clclasses', 'fullname', array('id' => $reports[$key]->moduleid));
            } else if ($reports[$key]->itemtype == 'mooctype' && $reports[$key]->online_courseid) {
                $line[] = $DB->get_field('course', 'fullname', array('id' => $reports[$key]->online_courseid));
            }
            //    $items = $DB->get_records('local_item', array('orderid'=>$reports[$key]->orderid));
            //    $modules = array();
            //    foreach($items as $item){
            //	if($item->itemtype=='classtype' && $item->moduleid){
            //	    $onlinecourseid = $DB->get_field('local_clclasses', 'onlinecourseid', array('id'=>$item->moduleid));;
            //	}
            //	if($item->itemtype=='mooctype' && $item->online_courseid){
            //	    $onlinecourseid = $item->online_courseid;
            //	}
            //	$course = $DB->get_record('course', array('id'=>$onlinecourseid));
            //	$modules[] = $course->fullname;
            //    }
            //    $line[] = implode(', ', $modules);
            $line[] = $countries[$reports[$key]->country]; //Student country
            $no_vat = false;
            if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
                $local_user = $DB->get_record('local_users', array('userid' => $reports[$key]->userid));
                $user_class = $DB->get_record('local_user_clclasses', array('userid' => $reports[$key]->userid, 'classid' => $reports[$key]->moduleid));
                $exist = $local_user && $user_class;
                if ($exist && $local_user->fundsbygovt && !$user_class->fundbyuk) {
                    $no_vat = true;
                }
            }
            if ($no_vat) {
                $line[] = 'No VAT applied';
                $line[] = '0';
                $subtotal += '0';
            } else
            if ($tax = $DB->get_record_select('local_tax_rate', "country = '" . $reports[$key]->country . "' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
                $line[] = $tax->rate . ' %';
                $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
                $line[] = $vatamount;
                $subtotal += $vatamount;
            } else if ($tax = $DB->get_record_select('local_tax_rate', "country = 'all' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
                $line[] = $tax->rate . ' %';
                $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
                $line[] = $vatamount;
                $subtotal += $vatamount;
            } else {
                $line[] = '0 %';
                $line[] = '0';
                $subtotal += '0';
            }
            $col = 0;
            foreach ($fields as $k => $v) {
                $worksheet[0]->write($sheetrow, $col, $line[$k]);
                $col++;
            }
            $sheetrow++;
            $i++;
            unset($ctrs[$key]);
        }
    }
    if ($satisfy2) {
        $line = array('', '', '', '', '', 'Sub-Total', $subtotal);
        $col = 0;
        foreach ($fields as $k => $v) {
            $worksheet[0]->write($sheetrow, $col, $line[$k]);
            $col++;
        }
        $sheetrow++;
        $i++;
    }
    $grandtotal += $subtotal;





    //Group all Non-EU countries---------------
    $subtotal = 0;
    $satisfy3 = false;
    foreach ($ctrs as $key => $ctr) {
        $satisfy3 = true;
        $line = array();
        $line[] = $reports[$key]->studentname; //Student Name
        $line[] = $reports[$key]->amount;
        $line[] = date('d M, Y', $reports[$key]->timecreated);
        if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
            $line[] = $DB->get_field('local_clclasses', 'fullname', array('id' => $reports[$key]->moduleid));
        } else if ($reports[$key]->itemtype == 'mooctype' && $reports[$key]->online_courseid) {
            $line[] = $DB->get_field('course', 'fullname', array('id' => $reports[$key]->online_courseid));
        }
        //$items = $DB->get_records('local_item', array('orderid'=>$reports[$key]->orderid));
        //$modules = array();
        //foreach($items as $item){
        //    if($item->itemtype=='classtype' && $item->moduleid){
        //	$onlinecourseid = $DB->get_field('local_clclasses', 'onlinecourseid', array('id'=>$item->moduleid));;
        //    }
        //    if($item->itemtype=='mooctype' && $item->online_courseid){
        //	$onlinecourseid = $item->online_courseid;
        //    }
        //    $course = $DB->get_record('course', array('id'=>$onlinecourseid));
        //    $modules[] = $course->fullname;
        //}
        //$line[] = implode(', ', $modules);
        $line[] = $countries[$reports[$key]->country]; //Student country
        $no_vat = false;
        if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
            $local_user = $DB->get_record('local_users', array('userid' => $reports[$key]->userid));
            $user_class = $DB->get_record('local_user_clclasses', array('userid' => $reports[$key]->userid, 'classid' => $reports[$key]->moduleid));
            $exist = $local_user && $user_class;
            if ($exist && $local_user->fundsbygovt && !$user_class->fundbyuk) {
                $no_vat = true;
            }
        }
        if ($no_vat) {
            $line[] = 'No VAT applied';
            $line[] = '0';
            $subtotal += '0';
        } else
        if ($tax = $DB->get_record_select('local_tax_rate', "country = '" . $reports[$key]->country . "' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
            $line[] = $tax->rate . ' %';
            $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
            $line[] = $vatamount;
            $subtotal += $vatamount;
        } else if ($tax = $DB->get_record_select('local_tax_rate', "country = 'all' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
            $line[] = $tax->rate . ' %';
            $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
            $line[] = $vatamount;
            $subtotal += $vatamount;
        } else {
            $line[] = '0 %';
            $line[] = '0';
            $subtotal += '0';
        }
        $col = 0;
        foreach ($fields as $k => $v) {
            $worksheet[0]->write($sheetrow, $col, $line[$k]);
            $col++;
        }
        $sheetrow++;
        $i++;
        unset($ctrs[$key]);
    }
    if ($satisfy3) {
        $line = array('', '', '', '', '', 'Sub-Total', $subtotal);
        $col = 0;
        foreach ($fields as $k => $v) {
            $worksheet[0]->write($sheetrow, $col, $line[$k]);
            $col++;
        }
        $sheetrow++;
        $i++;
    }
    $grandtotal += $subtotal;

    if ($satisfy1 || $satisfy2 || $satisfy3) {
        $line = array('', '', '', '', '', 'Grand-Total', $grandtotal);
        $col = 0;
        foreach ($fields as $k => $v) {
            $worksheet[0]->write($sheetrow, $col, $line[$k]);
            $col++;
        }
        $sheetrow++;
        $i++;
    }
    $workbook->close();
    die;
}
