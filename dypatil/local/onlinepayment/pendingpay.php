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
 * @subpackage  onlinepayment
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$cancel = optional_param('cancel', 0, PARAM_INT);
$moduleid = optional_param('mid', 0, PARAM_INT);
$finalamount_moduleid = optional_param('mid', 0, PARAM_INT);
$oid = optional_param('oid', 0, PARAM_INT);
$order_id = optional_param('orderid', 0, PARAM_INT);
$order = optional_param('order', 0, PARAM_BOOL);
global $CFG, $DB, $OUTPUT;
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/onlinepayment/pendingpay.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/index.php'));
//$PAGE->navbar->add(get_string('deptlist', 'local_onlinepayment'));
$currenttab = 'paynow';
$trans = onlinepay_transaction::getInstance();
$flag = 0;
// after confirmation page  ,(after continue button)
if ($order) {
    $PAGE->url->param('order', 1);
    if ($confirm and confirm_sesskey()) {
        $ord = $trans->order_content($order_id);
        $trans->item_content($ord, $order_id);
        $traid = $trans->transaction_content($ord);
        $trans->enroll_mooccourses($traid);
        redirect('studentstatus.php');
    }
}

// after submitting data----------------
if (data_submitted()) {
    $paid_info = data_submitted();


// checking empty form to provide the proper message
    if ($paid_info->total == 0) {
        $flag = 1;
    }


    if ($flag != 1) {
//------------------------------------- getting form data-------------------------------------
//  confiramtion page starts---------------------------------
        if ($paid_info) {
            //print_object($paid_info);
            //exit;
            $orderid = $trans->order_content_temp($paid_info);
            $trans->item_content_temp($paid_info, $orderid);
            $returnurl = new moodle_url('/local/onlinepayment/pendingpay.php', array('oid' => $orderid, 'cancel' => 1));
            if ($paid_info->paynow == 1) {
                $strheading = get_string('confirmation_heading', 'local_onlinepayment');
                $PAGE->navbar->add($strheading);
                $PAGE->set_title($strheading);
                echo $OUTPUT->header();
                $currenttab = 'paynow';
                $trans->student_tabview($currenttab);


                //$dept_ob->dept_tabs($currenttab);
                echo $OUTPUT->heading($strheading);
                $yesurl = new moodle_url('/local/onlinepayment/pendingpay.php', array('order' => 1, 'confirm' => 1, 'orderid' => $orderid, 'sesskey' => sesskey()));
                //$message = get_string('payment_confirmation', 'local_onlinepayment');
                echo get_string('payment_confirmation', 'local_onlinepayment');
                $itemdetails = $DB->get_records('local_item_temp', array('orderid' => $orderid));
                //print_object($itemdetails);
                echo '<table>';
                foreach ($itemdetails as $itemd) {
                    $order = $DB->get_record('local_order_temp', array('id' => $orderid));
                    $local_user = $DB->get_record('local_users', array('userid' => $order->userid));
                    if ($itemd->itemtype == 'classtype' && $itemd->online_courseid == 0) {
                        $info = $DB->get_record('local_clclasses', array('id' => $itemd->moduleid));
                        $user_class = $DB->get_record('local_user_clclasses', array('userid' => $order->userid, 'semesterid' => $order->semesterid, 'classid' => $itemd->moduleid));
                        echo '<tr><td>' . $info->fullname . '</td><td width="50px;" align="center">=</td><td>&pound ' . $itemd->item_amount . '</td><td>';
                        if ($local_user->fundsbygovt && !$user_class->fundbyuk) {
                            echo '';
                        } else {
                            echo $trans->get_tax_information($order->userid);
                        }
                        echo '</td> </tr>';
                    }
                    if ($itemd->itemtype == 'mooctype') {
                        $onlinecourse_info = $DB->get_record('course', array('id' => $itemd->online_courseid));
                        echo '<tr><td>' . $onlinecourse_info->fullname . '</td><td width="50px;" align="center">=</td><td>&pound ' . $itemd->item_amount . '</td><td>';
                        echo $trans->get_tax_information($order->userid);
                        echo ' </td> </tr>';
                    }// end of if     
                }//main for loop


                $orderdetails = $DB->get_record('local_order_temp', array('id' => $orderid));
                // print_object($orderdetails);
                // echo '<tr><td width="150px;" >Tax added</td><td width="50px;">=</td><td>$'.$paid_info->tax.'</td> </tr>';

                echo '<tr><td></td><td width="50px;" ><b>Total :</b></td><td><b>&pound ' . number_format($orderdetails->amount, 2, '.', '') . '</b></td> </tr>';
                echo'</table>';
            }
            //--------by vinod--------
            //echo $output_info=$trans->get_tax_information($orderdetails->userid);
            //--------by vinod--------
            echo $OUTPUT->confirm('', $yesurl, $returnurl);

            echo $OUTPUT->footer();
            die;
        }// end of paid info  (end of confirmation page)
    }// end of flag
}//   ----------------------end of if



echo $OUTPUT->header();
require_login();
$trans->student_tabview($currenttab);
$usercontext = context_user::instance($USER->id);

//If the loggedin user have the required capability allow the page
//if (!has_capability('local/clclasses:enrollclass', $usercontext)) {
//print_error('You dont have permissions');
//}
$trans = onlinepay_transaction::getInstance();

$PAGE->requires->js('/local/onlinepayment/check_total.js');
//----------manage_dept heading------------------------
echo $OUTPUT->heading(get_string('onlinepay_heading', 'local_onlinepayment'));

echo $OUTPUT->box(get_string('make_payment', 'local_onlinepayment'));
try {
// only accounting period only transactiuon takes place
    $school = $DB->get_record('local_userdata', array('userid' => $USER->id));
    if (empty($school)) {
        $e = get_string('notenrolled_school', 'local_onlinepayment');
        throw new Exception($e);
    }
    $date = date_create();
    $unixtime = date_timestamp_get($date); // current date unix time

    $accounting_period = $DB->get_records_sql(" select id from {local_accounting_period} where  schoolid=$school->schoolid and
                             $unixtime BETWEEN datefrom AND dateto");
    $today = date('Y-m-d');
//print_object($accounting_period);
    if (empty($accounting_period)) {
        $e = get_string('transactionperiod_closed', 'local_onlinepayment');
        throw new Exception($e);
    }
    $currentsemesterid = $trans->get_student_currentsemester($USER->id);

    if (empty($currentsemesterid)) {
        $e = get_string('nocurrentsemesteravailable', 'local_onlinepayment');
        throw new Exception($e);
    }



// fetching cobalt courses
    $pendingcourse_list = $DB->get_records_sql("SELECT  uc.id,uc.timecreated,cc.id as courseid,cc.shortname,cl.fullname as cfullname,cl.id as clid,
                                          cc.fullname,userid,(select if(clcost.classcost=0 ,(clcost.credithourcost*cc.credithours),clcost.classcost)) as price, 'classtype' as itemtype,
                                          disc.discount as discountpercent, disc.discountcode as discountcode
                                          FROM
                                        {local_user_clclasses} as uc
                    JOIN {local_clclasses} as cl ON uc.classid=cl.id  and  cl.onlinecourseid!=0                 
                    JOIN {local_cobaltcourses} as cc
                    
                    ON cl.cobaltcourseid=cc.id
                    JOIN {local_classcost} as clcost ON clcost.classid=cl.id
                  LEFT JOIN {local_costdiscounts} as disc ON  clcost.id = disc.costid 
                    where uc.registrarapproval=1 and uc.userid=$USER->id and uc.semesterid=$currentsemesterid group by uc.id ");
//---------------------------------------------------------------------------------------------------------------------------------------
//(cl.classcost!=0 or cl.credithourcost!=0)and 
    // AND  '{$today}' BETWEEN FROM_UNIXTIME(disc.startdate, '%Y-%m-%d') AND FROM_UNIXTIME(disc.enddate, '%Y-%m-%d')
    // fetching mooc courses
    $clclasses = $DB->get_records('local_clclasses');
    $mooc_courses = $DB->get_records_select('course', 'id <> ?', array(1));
    foreach ($clclasses as $class) {
        if ($class->onlinecourseid) {
            unset($mooc_courses[$class->onlinecourseid]);
        }
    }



    foreach ($mooc_courses as $mcourses) {
        $costinfo = $DB->get_record('local_classcost', array('courseid' => $mcourses->id));
        if (!empty($costinfo)) {
            $m_c = new stdClass();
            $m_c->id = $mcourses->id;
            $m_c->clid = $mcourses->id;
            $m_c->courseid = $mcourses->id;
            $m_c->shortname = $mcourses->shortname;
            $m_c->fullname = $mcourses->fullname;
            $m_c->itemtype = 'mooctype';
            $m_c->timecreated = $mcourses->timecreated;
            $m_c->price = $costinfo->coursecost;
            if ($discs = $DB->get_records_select('local_costdiscounts', "'{$today}' BETWEEN FROM_UNIXTIME(startdate, '%Y-%m-%d') AND FROM_UNIXTIME(enddate, '%Y-%m-%d') AND costid = :costid ", array('costid' => $costinfo->id))) {
                foreach ($discs as $disc) {
                    $m_c->discountpercent = $disc->discount;
                    $m_c->discountcode = $disc->discountcode;
                }
            } else {
                $m_c->discountpercent = 0;
                $m_c->discountcode = null;
            }

            $mc[] = $m_c;
        }
    }
//------------------------------------------------------------------------------------------------------------------------------ 

    if (!empty($mc) and !empty($pendingcourse_list))
        $pendingcourse_list = array_merge($mc, $pendingcourse_list);
    else if (!empty($mc))
        $pendingcourse_list = $mc;
    else {
        if (!empty($pendingcourse_list))
            $pendingcourse_list = $pendingcourse_list;
    }


    //print_object($pendingcourse_list);
// checking  if already paid for the particular course list ,if paid not to show those courses
    if (!empty($pendingcourse_list)) {
        $paidcourselist = $DB->get_records_sql("select * from {local_order} as o
              JOIN {local_item} as item  ON o.id=item.orderid
              where o.userid=$USER->id");
        $pl = array();
        //print_object($paidcourselist);
        foreach ($paidcourselist as $paid) {
            if ($paid->itemtype == 'mooctype')
                $pl[] = $paid->online_courseid;
            else
                $pl[] = $paid->moduleid;
        }

        foreach ($pendingcourse_list as $pending) {
            if (!in_array($pending->clid, $pl)) {
                $pending_list[] = $pending;
            }
        }

        // removing paid course
        if ($flag == 1) {
            echo get_string('payment_missingcourselist', 'local_onlinepayment');
        }

        echo '<form id="pendingpay" action="pendingpay.php" method="post">';
        if ($cancel) {
            $recheck_items = $DB->get_records('local_item_temp', array('orderid' => $oid));
            $recheck_order = $DB->get_record('local_order_temp', array('id' => $oid));
        }


        $i = 0;
        if (!empty($pending_list)) {
            foreach ($pending_list as $key => $courselist) {
                $line = array();
                $checked = '';
                $reamount = '';
                $retax = '';
                $retotal = '';
                $itemamount = '';
                if (!empty($recheck_items)) {
                    $count = 0;
                    $checked = '';
                    foreach ($recheck_items as $recheck) {
                        if ($recheck->itemtype == 'classtype' && $courselist->clid == $recheck->moduleid) {
                            $checked = 'checked';
                            $itemamount = 'value=' . number_format($recheck->item_amount, 2, '.', '') . '';
                        }
                        if ($recheck->itemtype == 'mooctype' && $courselist->clid == $recheck->online_courseid) {
                            $checked = 'checked';
                            $itemamount = 'value=' . number_format($recheck->item_amount, 2, '.', '') . '';
                        }
                    }
                    if ($recheck_order->amount)
                        $reamount = 'value=' . number_format($recheck_order->amount, 2, '.', '') . '';

                    //$retax =$trans->cal_taxrate($USER->id,$recheck_order->amount);
                    //if($retax)
                    //$retotal=($reamount-$retax);
                    //else
                    //$retotal=$reamount;
                }// end of recheck item
                // by default check items

                if (!empty($moduleid) && $courselist->clid == $moduleid) {

                    $out = $trans->default_valuechecked_inpayment($moduleid);
                    $checked = $out[0];
                    $itemamount = $out[1];
                    //$reamount=$out[2];
                    $moduleid = 0;
                }

                // end of default check item
                //name="'.$courselist->clid.'"

                $line[] = '<input type="checkbox" id="' . $courselist->clid . '"   name="' . $courselist->clid . '"    ' . $checked . '  value="' . $courselist->price . '"  onClick="gettot(' . $courselist->price . ',' . $courselist->clid . ');">
    <input type="hidden" name="itemtype[' . $courselist->clid . ']" value="' . $courselist->itemtype . '" ></input>';
                if ($courselist->itemtype == 'classtype')
                    $line[] = $courselist->cfullname;
                else
                    $line[] = $courselist->fullname;
//$line[] = $courselist->fullname;
                //$line[] = date('Y-m-d',$courselist->timecreated);    
                $line[] = '&pound ' . $courselist->price . '<input type="hidden" id="' . $courselist->clid . 'cost" value="' . number_format($courselist->price, 2, '.', '') . '">';
                // $line[] = $courselist->discountpercent;
                $dis_r = number_format($courselist->discountpercent, 0, '.', '');
                if (empty($dis_r)) {
                    $line[] = '<input type="hidden" name="discountcode[' . $courselist->clid . ']" value="' . $courselist->discountpercent . '"></input>No Discount';
                } else {

                    $line[] = '<input type="text" name="discountcode[' . $courselist->clid . ']" id="dcode' . $courselist->clid . '"   class="dcode"  style="width:55px;height:30px !important;"  ></input><a style="cursor: pointer;"  class="go" onClick="get_discountrate(' . $i . ',' . $courselist->clid . ',\'' . $courselist->itemtype . '\',' . $courselist->price . ')"> <b>Enter</b></a>';
                    $i++;
                }
                $line[] = '<span >&pound</span> <input type="text" name="finalamount' . $courselist->clid . '"  class ="finalamount" id="finalamount' . $courselist->clid . '"  ' . $itemamount . ' readonly  style="width:55px;height:30px !important;" Onfocus="get_totalchange(' . $courselist->clid . ')"></input>';
                $data[] = $line;
            }//end of foreach

            if (!empty($finalamount_moduleid)) {
                // echo $finalamount_moduleid;
                $out = $trans->default_valuechecked_inpayment($finalamount_moduleid, 'finalamount');
                //print_object($out);
                $reamount = $out[2];
            }

            $total = array('', '', '', get_string('finalamount', 'local_onlinepayment'), '<span>&pound </span><input type="text" name="total" id="total" readonly   ' . $reamount . '  style="width:55px;height:30px !important;" ></input>');
            $data[] = $total;
            //$taxtotal=array('','VAT based on country','','','','<input type="text" name="tax"  '.$retax.'  readonly id="tax" style="width:55px;height:30px !important;"  ></input>');
            //$data[]=$taxtotal;
            //$final=array('','Final Amount','','','','<input type="text" id="final_amount" name="final_amount"  readonly '.$reamount.'  style="width:55px;height:30px !important;" ></input>');
            //$data[]=$final; 
            //$submitbutton .= '<input type="checkbox" name="mycheck" class="checkall" value="Check All" />';
            $submitbutton = ' <input type="hidden" name="userid" value="' . $USER->id . '"></input>                
                     <input type="hidden" name="paynow" value="1"></input>
                     <input type="submit"  value="Pay Now" ></input> ';
            //$cancelbutton=' <a href="../courseregistration/myclclasses.php"><button>Cancel</button></a> ';               


            $data[] = array('', '', $submitbutton, '', '');


            $table = new html_table();
            $table->id = 'setting';
            $table->head = array(
                get_string('onlinepay_select', 'local_onlinepayment'),
                //get_string('batch', 'local_cobaltsettings'),
                get_string('classmodname', 'local_onlinepayment'),
//get_string('onlinepay_shortname', 'local_onlinepayment'),
//get_string('onlinepay_enrollementdate', 'local_onlinepayment'),
//get_string('onlinepay_status', 'local_onlinepayment'),
                get_string('onlinepay_cost', 'local_onlinepayment'),
//get_string('discount_percent', 'local_onlinepayment'),
                get_string('discount_code', 'local_onlinepayment'),
                get_string('final_price', 'local_onlinepayment'));


            $table->size = array('5%', '40%', '10%', '30%', '15%');
            $table->align = array('left', 'left', 'left', 'center');
            $table->width = '100%';
            $table->data = $data;
            echo html_writer::table($table);
            echo'</form>';
            echo $cancelbutton = ' <a href="../courseregistration/stuclasses.php"><button style="float: right; margin-right: 310px; margin-top: -75px;">Cancel</button></a> ';
        } else
            echo get_string('no_pendingcourses', 'local_onlinepayment');
    } else
        echo get_string('noenrollments_currentcourses', 'local_onlinepayment');
} catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>




