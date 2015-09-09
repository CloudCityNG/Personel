<?php

class tax {

    private static $_tax;
    private $dbHandle;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_tax) {
            self::$_tax = new tax();
        }
        return self::$_tax;
    }

    function get_tax_types() {
        global $DB, $CFG;
        $out = array(null => 'Select Type');
        $types = $DB->get_records('local_tax_type');
        foreach ($types as $type) {
            $out[$type->id] = format_string($type->display_name);
        }
        return $out;
    }

    /**
     * @method get_payment_status
     * @todo Status for the user for particular school and order id
     * @param array $schoollist Schools list
     * @param int $orderid Order ID
     **/
    function get_payment_status($schoollist, $orderid) {
        global $DB, $CFG, $USER;

        $sql = "SELECT o.*, d.serviceid, d.schoolid, CONCAT(u.firstname, ' ', u.lastname) AS fullname, t.transactionid, t.payment_method, t.amount, t.timecreated AS paidon, t.currency_code, t.status AS paymentstatus
                        FROM {local_order} AS o
                        JOIN {local_item} AS i ON i.orderid = o.id 
                        JOIN {local_userdata} AS d ON d.userid = o.userid
                        JOIN {user} AS u ON u.id = d.userid
                        JOIN {local_payment_transaction} AS t ON t.orderid = o.id AND t.userid = o.userid ";
        if ($schoollist) {
            $schoolin = implode(',', array_keys($schoollist));
            $sql .= " WHERE d.schoolid IN ($schoolin) ";
        } else {
            $sql .= " WHERE o.userid = {$USER->id} ";
        }
        if ($orderid) {
            $sql .= " AND o.id = {$orderid} ";
        }
        $sql .= " GROUP BY i.orderid ORDER BY o.timecreated DESC ";
        if ($orderid) {
            return $DB->get_record_sql($sql);
        } else {
            return $DB->get_records_sql($sql);
        }
    }



    /**
     * @method get_course_items
     * @todo To get the courses for particular order ID
     * @param int $recordid Order ID
     * @return array $course Courses list
     */
    function get_course_items($recordid) {
        global $DB, $CFG;
        $items = $DB->get_records('local_item', array('orderid' => $recordid));
        $course = array();
        foreach ($items as $item) {
            if ($item->moduleid)
                $course[] = $DB->get_record('local_clclasses', array('id' => $item->moduleid));
            else if ($item->online_courseid)
                $course[] = $DB->get_record('course', array('id' => $item->online_courseid));
        }
        return $course;
    }

    /**
     * @method check_academic_period_change
     * @todo To check the order time period in between academic period startdate and enddate
     * @param object $accperiod Academic period
     * @return boolean Records exist or not
     */
    function check_academic_period_change($accperiod) {
        global $DB, $CFG;
        $startdate = date('Y-m-d', $accperiod->datefrom);
        $enddate = date('Y-m-d', $accperiod->dateto);

        $sql = "SELECT o.* FROM {local_order} o JOIN {local_userdata} d ON d.userid = o.userid
	WHERE d.schoolid = {$accperiod->schoolid} AND FROM_UNIXTIME(o.timecreated, '%Y-%m-%d') BETWEEN '{$startdate}' AND '{$enddate}'";
        $records = $DB->get_records_sql($sql);
        if (!empty($records)) {
            return true;
        }
        return false;
    }

    /**
     * @method randomAlphaNum
     * @todo to generate random character
     * @param int $length
     * @return string, result in random character
     */
    function randomAlphaNum($length) {
        $rangeMin = pow(36, $length - 1); //smallest number to give length digits in base 36 
        $rangeMax = pow(36, $length) - 1; //largest number to give length digits in base 36 
        $base10Rand = rand($rangeMin, $rangeMax); //get the random number 
        $newRand = base_convert($base10Rand, 10, 36); //convert it 
        return $newRand; //spit it out 
    }

    /**
     * @method check_for_date_change
     * @todo To check order placed in between these(record) startdate and enddate
     * @param object $record perticular order record 
     * @return boolean Records exist or not
     */
    function check_for_date_change($record) {
        global $DB, $CFG;
        $startdate = date('Y-m-d', $record->startdate);
        $enddate = date('Y-m-d', $record->enddate);
        $sql = "SELECT o.* FROM {local_order} o JOIN {user} u ON u.id = o.userid
	WHERE FROM_UNIXTIME(o.timecreated, '%Y-%m-%d') BETWEEN '{$startdate}' AND '{$enddate}'";
        if ($record->country !== 'all') {
            $sql .= " AND u.country = '{$record->country}' ";
        }
        $records = $DB->get_records_sql($sql);
        if (!empty($records)) {
            return true;
        }
        return false;
    }

    /**
     * @method get_thisuser_schools
     * @todo To get schools list for perticular user(for registrar)
     * @param object $hierarchy (hierarchy class object)
     * @return array of school list
     */
    function get_thisuser_schools($hierarchy) {
        global $DB, $CFG;
        $schools = $hierarchy->get_school_items();
        return $hierarchy->get_school_parent($schools, array(), $top = true, $all = false);
    }

    /**
     * @method get_allprograms
     * @todo To get all program list
     * @return array of program list
     */
    function get_allprograms() {
        global $DB, $CFG;
        $out = array(null => '---Select---');
        $programs = $DB->get_records('local_program', array('visible' => 1));
        foreach ($programs as $program) {
            $out[$program->id] = format_string($program->fullname);
        }
        return $out;
    }

    /**
     * @method get_accounting_periods
     * @todo To get accounting period dates(startdate, enddate)
     * @return array of accounting period dates 
     */
    function get_accounting_periods() {
        global $DB, $CFG;
        $accperiods = $DB->get_records('local_accounting_period');
        $acc = array('---Select---');
        foreach ($accperiods as $accperiod) {
            $acc[$accperiod->id] = date('d M, Y', $accperiod->datefrom) . '&nbsp;&nbsp;-&nbsp;&nbsp;' . date('d M, Y', $accperiod->dateto);
        }
        return $acc;
    }

    /**
     * @method get_months
     * @todo to get months in the form of associative array
     * @return array of months
     */
    function get_months() {
        return array(
            //'0'=>'Month',
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        );
    }

    /**
     * @method get_years
     * @todo to get list of years should less than transaction year
     * @return array of years
     */
    function get_years() {
        global $DB, $CFG;
        $record = $DB->get_record_sql("SELECT FROM_UNIXTIME(timecreated, '%Y') AS year FROM {local_payment_transaction} ORDER BY timecreated ASC LIMIT 1");
        $currentyear = date('Y');
        //$years = array('0'=>'Year');
        for ($i = $currentyear; $i >= $record->year; $i--) {
            $years[$i] = $i;
        }
        return $years;
    }

    /**
     * @method get_class_assignedcourses
     * @todo to get list of courses of all active clclasses
     * @return array of courselist
     */
    function get_class_assignedcourses() {
        global $DB, $CFG;
        $out = array(null => '---Select---');
        $courses = $DB->get_records_sql("SELECT * FROM {local_cobaltcourses} WHERE id IN (select cobaltcourseid from {local_clclasses} where visible=1)");
        foreach ($courses as $course) {
            $out[$course->id] = format_string($course->fullname);
        }
        return $out;
    }

    /**
     * @method get_moodle_courses
     * @todo to get list of moodle courses of all active clclasses
     * @return array of moodle courselist
     */
    function get_moodle_courses() {
        global $DB, $CFG;
        $out = array(null => '---Select---');
        $clclasses = $DB->get_records('local_clclasses', array('visible' => 1));
        $mooc_courses = $DB->get_records_select('course', 'id > 1');
        foreach ($mooc_courses as $course) {
            $out[$course->id] = format_string($course->fullname);
        }
        return $out;
    }

    /**
     * @method createtabview
     * @todo to generate tab view(tab tree)
     * @param text $currenttab (current tab name)
     * @return print tab tree(in form of html)
     */
    function createtabview($currenttab, $id = -1) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('settings', new moodle_url('/local/onlinepayment/accperiod.php'), get_string('settings'));
        $tabs[] = new tabobject('orders', new moodle_url('/local/onlinepayment/paymentstatus.php'), get_string('orders', 'local_onlinepayment'));
        $tabs[] = new tabobject('reports', new moodle_url('/local/onlinepayment/vatreport.php'), get_string('reports', 'local_collegestructure'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /**
     * @method get_inner_headings
     * @todo to set inner headings(accounting period, taxrate, modulecost settings) of (main)setting tab
     * @param string $mypage it holds the sub heding url
     * @return print sub(inner) headings
     */
    function get_inner_headings($mypage) {
        global $DB, $CFG;
        $headlist = array('accperiod' => get_string('accperiodsettings', 'local_onlinepayment'),
            'index' => get_string('taxratesettings', 'local_onlinepayment'),
            'modcost' => get_string('modcostsettings', 'local_onlinepayment'));
        $heading = array();
        foreach ($headlist as $page => $head) {
            $link = html_writer::tag('a', $head, array('href' => $page . '.php'));
            if ($page == $mypage) {
                $link = html_writer::tag('b', $link, array());
                $link = html_writer::tag('font', $link, array('size' => '5px;'));
            }
            $heading[] = $link;
        }
        echo implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $heading);
    }

    /**
     * @method get_report_headings
     * @todo to set inner headings(vat, payment report) of (main)report tab
     * @param string $mypage it holds the sub heding url
     * @return print sub(inner) headings
     */
    function get_report_headings($mypage) {
        global $DB, $CFG;
        $headlist = array('vatreport' => get_string('monthlyvatreport', 'local_onlinepayment'),
            'report' => get_string('paymentreport', 'local_onlinepayment')
        );
        $heading = array();
        foreach ($headlist as $page => $head) {
            $link = html_writer::tag('a', $head, array('href' => $page . '.php'));
            if ($page == $mypage) {
                $link = html_writer::tag('b', $link, array());
                $link = html_writer::tag('font', $link, array('size' => '5px;'));
            }
            $heading[] = $link;
        }
        echo implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $heading);
    }

    /**
     * @method display_vat_data
     * @todo to calculate value aided tax amount based on country vat rate
     * @param object $reports(holds user records)
     * @param int $key (index of records)
     * @param int $subtotal
     * @return array of tax rate and deduction amount
     */
    function display_vat_data($reports, $key, $subtotal) {
        global $DB, $CFG;
        $countries = get_string_manager()->get_list_of_countries(false);
        $euCountries = $this->get_eu_countries();
        $line = array();
        $line[] = $reports[$key]->studentname; //Student Name
        //$line[] = $reports[$key]->transactionid;
        $line[] = '&pound ' . round($reports[$key]->amount, 2);
        $line[] = date('d M, Y', $reports[$key]->timecreated);
        //$items = $DB->get_records('local_item', array('orderid'=>$reports[$key]->orderid));
        //$modules = array();
        //foreach($items as $item){
        if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
            $line[] = $DB->get_field('local_clclasses', 'fullname', array('id' => $reports[$key]->moduleid));
            //$onlinecourseid = $DB->get_field('local_clclasses', 'onlinecourseid', array('id'=>$reports[$key]->moduleid));;
        } else
        if ($reports[$key]->itemtype == 'mooctype' && $reports[$key]->online_courseid) {
            //$onlinecourseid = $reports[$key]->online_courseid;
            $line[] = $DB->get_field('course', 'fullname', array('id' => $reports[$key]->online_courseid));
        }

        //$line[] = '<a target="_blank" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->fullname.'</a>';
        //}
        //$line[] = implode(',&nbsp;&nbsp;', $modules);
        $line[] = $countries[$reports[$key]->country]; //Student country
        $no_vat = false;
        if ($reports[$key]->itemtype == 'classtype' && $reports[$key]->moduleid) {
            $local_user = $DB->get_record('local_users', array('userid' => $reports[$key]->userid));
            $user_class = $DB->get_record('local_user_clclasses', array('userid' => $reports[$key]->userid, 'classid' => $reports[$key]->moduleid));
            if ($local_user->fundsbygovt && !$user_class->fundbyuk) {
                $no_vat = true;
            }
        }
        if ($no_vat) {
            $line[] = 'No VAT applied';
            $line[] = '&pound ' . '0';
            $subtotal += '0';
        } else
        if ($tax = $DB->get_record_select('local_tax_rate', "country = '" . $reports[$key]->country . "' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
            $line[] = $tax->rate . ' %';
            $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
            $line[] = '&pound ' . $vatamount;
            $subtotal += $vatamount;
        } else if ($tax = $DB->get_record_select('local_tax_rate', "country = 'all' AND typeid = 1 AND '" . date('d-m-Y', $reports[$key]->timecreated) . "' BETWEEN FROM_UNIXTIME(startdate, '%d-%m-%Y') AND FROM_UNIXTIME(enddate, '%d-%m-%Y')")) {
            $line[] = $tax->rate . ' %';
            $vatamount = ( $tax->rate / 100 ) * $reports[$key]->amount;
            $line[] = '&pound ' . $vatamount;
            $subtotal += $vatamount;
        } else {
            $line[] = '0 %';
            $line[] = '&pound ' . '0';
            $subtotal += '0';
        }
        return array($line, $subtotal);
    }

    /**
     * @method get_eu_countries
     * @todo to get countries list in the form of array  
     * @return array of countries list
     */
    function get_eu_countries() {
        $countries = array();
        //Member states of the EU 
        $countries['AT'] = 'Austria';
        $countries['BE'] = 'Belgium';
        $countries['BG'] = 'Bulgaria';
        $countries['HR'] = 'Croatia';
        $countries['CY'] = 'Cyprus';
        $countries['CZ'] = 'Czech Republic';
        $countries['DK'] = 'Denmark';
        $countries['EE'] = 'Estonia';
        $countries['FI'] = 'Finland';
        $countries['FR'] = 'France';
        $countries['DE'] = 'Germany';
        $countries['GR'] = 'Greece';
        $countries['HU'] = 'Hungary';
        $countries['IE'] = 'Ireland';
        $countries['IT'] = 'Italy';
        $countries['LV'] = 'Latvia';
        $countries['LT'] = 'Lithuania';
        $countries['LU'] = 'Luxembourg';
        $countries['MT'] = 'Malta';
        $countries['NL'] = 'Netherlands';
        $countries['PL'] = 'Poland';
        $countries['PT'] = 'Portugal';
        $countries['RO'] = 'Romania';
        $countries['SK'] = 'Slovakia';
        $countries['SI'] = 'Slovenia';
        $countries['ES'] = 'Spain';
        $countries['SE'] = 'Sweden';
        //$countries['GB'] = 'United Kingdom';
        return $countries;
    }

}

class onlinepay_transaction {

    private static $_singleton;

    //----constructor not called by outside of the class...only possible with inside the class	 
    private function __construct() {
        
    }

    //----used to crate a object---when the first time of usage of this function ..its create object
    //--by the next time its link to the same object(single tone object)instead of creating new object...
    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new onlinepay_transaction();
        }
        return self::$_singleton;
    }

    /**
     * @method  get_student_currentsemester
     * @todo to pericular student current semester based on current dates(which lies in between semester startdate and enddate)
     * @param int $userid it holds studentid
     * @return int ,student semesterid,if not belongs any current semester it returns boolean.
     */
    function get_student_currentsemester($userid) {
        global $DB, $CFG, $USER;
        $date = date_create();
        $unixtime = date_timestamp_get($date);
        $school = $DB->get_record('local_userdata', array('userid' => $userid));
        $semesterlist = $DB->get_records('local_school_semester', array('schoolid' => $school->schoolid));

        foreach ($semesterlist as $sem) {
            $semid = $DB->get_records_sql(" select id from {local_semester} where  id=$sem->semesterid and
                             $unixtime BETWEEN startdate AND enddate");
            if (!empty($semid)) {
                foreach ($semid as $sid)
                    $semesterid = $sid->id;
                $semsid[] = $semesterid;
                return $semesterid;
            }
        }
        return false;
    }

    /**
     * @method  order_content_temp
     * @todo To insert form submitted value to temporary order table.
     * @param object $submitteddata (form submitted data)
     * @return int, inserted row id.
     */
    function order_content_temp($submitteddata) {
        global $DB, $CFG, $USER;
        $semid = $this->get_student_currentsemester($submitteddata->userid);
        $count = 0;
        foreach ($submitteddata as $key => $value) {
            if (is_numeric($key)) {
                $count++;
            }
        }

        $ordertemp = new stdClass();
        $ordertemp->userid = $submitteddata->userid;
        $ordertemp->semesterid = $semid;
        $ordertemp->status = "Processing";
        $ordertemp->quantity = $count;
        $ordertemp->amount = $submitteddata->total;
        $ordertemp->timecreated = time();
        $res = $DB->insert_record('local_order_temp', $ordertemp);
        //  echo $res;
        return $res;
    }

    /**
     * @method  item_content_temp
     * @todo To insert form submitted value to temporary item table.
     * @param object $submitteddata (form submitted data)
     * @param int $orderid (orderid of items)
     * @return int, inserted row id
     */
    function item_content_temp($submitteddata, $orderid) {
        global $DB, $CFG, $USER;

        $itemtype_array = ($submitteddata->itemtype);

        foreach ($submitteddata as $key => $value) {

            if (is_numeric($key)) {
                $keys = "finalamount$key";
                $itemprice = $submitteddata->$keys;
                $itemtemp = new stdClass();
                $itemtemp->orderid = $orderid;
                // store discount percent;
//	     $dis_code=$submitteddata->discountcode[$key];
//	    if($dis_code){
//              //$dis_code=$submitteddata->discountcode[$key];			
//	        $dis=$DB->get_record_sql("select dis.id, dis.discount as discountpercent  from {local_costdiscounts} dis
//				     JOIN {local_classcost} clcost ON clcost.id=dis.costid and dis.discountcode='{$dis_code}'" );
//		if($dis)
//		 $itemtemp->dis_percent= $dis->discountpercent;
//		 else
//		 $itemtemp->dis_percent= 0;
//	   
//	    }
//	    else
//	    $itemtemp->dis_percent=0;
                // end of discount percent 
                $itemtemp->itemtype = $itemtype_array[$key];
                if ($itemtemp->itemtype == 'classtype')
                    $itemtemp->moduleid = $key;
                else
                    $itemtemp->online_courseid = $key;
                $itemtemp->item_amount = $itemprice;
                $itemtemp->timecreated = time();
                $itemtemp->usermodified = $USER->id;
                $res = $DB->insert_record('local_item_temp', $itemtemp);
            }
        }
    }

// end of function

    /**
     * @method  order_content
     * @todo To inserting order data from temporary table to order table(when they confirmed) 
     * @param int $temporderid  temporary order ID
     * @return int, inserted row ID.
     */
    function order_content($temporderid) {
        global $DB, $CFG, $USER;
        $tempinfo = $DB->get_record('local_order_temp', array('id' => $temporderid));
        $temp = new stdClass();
        $temp->orderid = $temporderid;
        $temp->userid = $tempinfo->userid;
        $temp->semesterid = $tempinfo->semesterid;
        $temp->quantity = $tempinfo->quantity;
        $temp->amount = $tempinfo->amount;
        $temp->status = $tempinfo->status;
        $temp->timecreated = $tempinfo->timecreated;
        $res = $DB->insert_record('local_order', $temp);
        return $res;
    }

    /**
     * @method  item_content
     * @todo To inserting order items values from temporary table to item table(when they confirmed) 
     * @param int $temporderid  temporary item ID
     * @param int $orderid  order ID
     * @return int, inserted row ID.
     */
    function item_content($orderid, $temporderid) {

        global $DB, $CFG, $USER;
        $submitteddata = $DB->get_records('local_item_temp', array('orderid' => $temporderid));
        foreach ($submitteddata as $sd) {

            $itemtemp = new stdClass();
            $itemtemp->orderid = $orderid;
            $itemtemp->itemtype = $sd->itemtype;
            $itemtemp->moduleid = $sd->moduleid;
            $itemtemp->online_courseid = $sd->online_courseid;
            //  $itemtemp->dis_percent=$sd->dis_percent;
            $itemtemp->item_amount = $sd->item_amount;
            $itemtemp->timecreated = $sd->timecreated;
            $itemtemp->usermodified = $sd->usermodified;
            $res = $DB->insert_record('local_item', $itemtemp);
        }
    }

// end of function

    /**
     * @method  rand_string
     * @todo To generate random string 
     * @param int $length specifying length of random strings 
     * @return string, Random string.
     */
    function rand_string($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $size = strlen($chars);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }

        return $str;
    }

    /**
     * @method  transaction_content
     * @todo To inserting order data to transaction table   
     * @param int $orderid  order ID
     * @return int, inserted row ID.
     */
    function transaction_content($orderid) {
        global $DB, $CFG, $USER;
        $tempinfo = $DB->get_record('local_order', array('id' => $orderid));

        $transid = $this->rand_string(10);

        $transtemp = new stdClass();
        $transtemp->transactionid = $transid;
        $transtemp->userid = $tempinfo->userid;
        $transtemp->orderid = $orderid;
        $transtemp->payment_method = 'credit card';
        $transtemp->currency_code = 'GBP';
        $transtemp->amount = $tempinfo->amount;
        $transtemp->status = "Success";
        $transtemp->timecreated = time();
        $transtemp->timemodified = time();
        return $DB->insert_record('local_payment_transaction', $transtemp);
    }

    /**
     * @method enroll_mooccourses
     * @todo To enroll student to moodle(online)course, after done with payment successfully.   
     * @param int $traid  transaction ID
     * @return int, inserted row ID.
     */
    function enroll_mooccourses($traid) {
        global $DB, $CFG, $USER;
        $transaction = $DB->get_record('local_payment_transaction', array('id' => $traid, 'userid' => $USER->id));
        if ($transaction->status == 'Success') {
            if ($moocs = $DB->get_records('local_item', array('orderid' => $transaction->orderid, 'itemtype' => 'mooctype'))) {
                foreach ($moocs as $mooc) {
                    $context = context_course::instance($mooc->online_courseid);
                    $role_assign = new stdClass();
                    $role_assign->userid = $transaction->userid;
                    $role_assign->roleid = 5;
                    $role_assign->contextid = $context->id;
                    $role_assign->timemodified = time();
                    $role_assign->modifierid = $USER->id;
                    if (!$DB->record_exists('role_assignments', array('userid' => $transaction->userid, 'contextid' => $context->id, 'roleid' => 5)))
                        $DB->insert_record('role_assignments', $role_assign);

                    $enrol = $DB->get_record('enrol', array('courseid' => $mooc->online_courseid, 'status' => 0));
                    $user_enrol = new stdClass();
                    $user_enrol->status = $enrol->status;
                    $user_enrol->enrolid = $enrol->id;
                    $user_enrol->userid = $transaction->userid;
                    $user_enrol->modifierid = $USER->id;
                    $user_enrol->timecreated = time();
                    $user_enrol->timemodified = time();
                    if (!$DB->record_exists('user_enrolments', array('userid' => $transaction->userid, 'enrolid' => $enrol->id)))
                        $DB->insert_record('user_enrolments', $user_enrol);
                }
            }
        }
    }

    /**
     * @method cal_taxrate
     * @todo To calculate tax amount.   
     * @param int $userid  User ID
     * @param int $total Total amount  
     * @return int, final tax amount.
     */
    function cal_taxrate($userid, $total) {
        global $DB, $CFG, $USER;
        $userinfo = $DB->get_record('user', array('id' => $USER->id));
        $tax_percent = $DB->get_records_sql("select id,rate from {local_tax_rate} where country='$userinfo->country'");
        if (empty($tax_percent))
            return 0;
        else {
            foreach ($tax_percent as $tax)
                $taxp = $tax->rate;
            $sum = ($taxp * $total);
            $final_tax = ($sum / 100);
            return $final_tax;
        }
    }

    /**
     * @method student_tabview
     * @todo To generate tab tree or view.   
     * @param string $currenttab  Current tab name
     * @param int $id Oreder id 
     * @return print tab view.
     */
    function student_tabview($currenttab, $id = -1) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('status', new moodle_url('/local/onlinepayment/studentstatus.php'), get_string('status', 'local_onlinepayment'));
        $tabs[] = new tabobject('paynow', new moodle_url('/local/onlinepayment/pendingpay.php'), get_string('paynow', 'local_onlinepayment'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

// end of function

    /**
     * @method get_tax_information
     * @todo To get tax information such as tax rate, tax name.   
     * @param int $userid UserID    
     * @return array of tax information.
     */
    function get_tax_information($userid) {
        global $DB, $CFG, $USER;
        $user = $DB->get_record('user', array('id' => $userid));
        $today = date('Y-m-d');
        $types = $DB->get_records('local_tax_type');
        $taxes = array();
        foreach ($types as $type) {
            if ($rate = $DB->get_record_select('local_tax_rate', "'{$today}' BETWEEN FROM_UNIXTIME(startdate, '%Y-%m-%d') AND FROM_UNIXTIME(enddate, '%Y-%m-%d') AND country = '{$user->country}' AND typeid = {$type->id}")) {
                $taxes[] = $rate->name . ': ' . $rate->rate . '%';
            } else if ($rate = $DB->get_record_select('local_tax_rate', "'{$today}' BETWEEN FROM_UNIXTIME(startdate, '%Y-%m-%d') AND FROM_UNIXTIME(enddate, '%Y-%m-%d') AND country = 'all' AND typeid = {$type->id}")) {
                $taxes[] = $rate->name . ' - ' . $rate->rate . '%';
                //} else {
                // $taxes[] = null;
            }
        }
        //$taxes = array_filter($taxes);
        $count = sizeof($taxes);
        if ($count > 1)
            return('( Including :  ' . implode(', ', $taxes) . ' )');
        else
            return ( '( Including :  ' . implode(', ', $taxes) . ' )');
    }

// end of function

    /**
     * @method default_valuechecked_inpayment
     * @todo used To make checkbox checked, if default value is available   
     * @param int $moduleid module ID    
     * @param int $finalamount Final amount
     * @return array of result(amount and check or uncheck).
     */
    function default_valuechecked_inpayment($moduleid, $finalamount = '') {
        global $DB, $CFG, $USER;
        $checked = '';
        $reamount = '';
        $retax = '';
        $retotal = '';
        $itemamount = '';
        if (!empty($moduleid)) {
            $select = "classid=$moduleid or courseid=$moduleid";
            $default_checkedvalues = $DB->get_record_select('local_classcost', $select);

            //print_object ($default_checkedvalues); 
            if (!empty($default_checkedvalues->classcost) or !empty($default_checkedvalues->coursecost)) {
                $res[] = 'checked';
                if ($default_checkedvalues->classcost) {
                    $def_cost = $default_checkedvalues->classcost;
                } else {

                    $def_cost = $default_checkedvalues->coursecost;
                }
            } else {
                if (!empty($default_checkedvalues->credithourcost)) {
                    $res[] = 'checked';
                    $credithour = $DB->get_record('local_cobaltcourses', array('id' => $moduleid));
                    $def_cost = $credithour->credithours * $default_checkedvalues->credithourcost;
                }
            }

            // echo $def_cost;
            $res[] = 'value=' . number_format($def_cost, 2, '.', '') . '';
            $res[] = 'value=' . number_format($def_cost, 2, '.', '') . '';
            return $res;


            //$moduleid=0;
        }
        return '';
    }

// edn of function
}

//end of classs
?>