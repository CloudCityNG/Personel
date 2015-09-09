<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../lib.php');
global $CFG, $DB, $PAGE, $USER;

ini_set('error_reporting', 0);

function timetable_local_construct_sessions_data_for_add($sessionsinfo) {
    global $CFG;
    $addmultiply = 1;
    if (isset($addmultiply)) {
        //    $startdate = $formdata->sessiondate;
        //    $starttime = $startdate - usergetmidnight($startdate);
        //    $enddate = $formdata->sessionenddate + DAYSECS; // Because enddate in 0:0am.
        //  
        $st_array = explode(':', $sessionsinfo->starttime);
        $starttime_seconds = $st_array[0] * 3600 + $st_array[1] * 60 + 0;
        //$parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

        $startdate = $sessionsinfo->startdate;
        $enddate = $sessionsinfo->enddate;
        $starttime = $sessionsinfo->starttime;
        $days = (int) ceil(($enddate - $startdate) / DAYSECS);

        if ($enddate < $startdate) {
            return null;
        }

        // Getting first day of week.
        $sdate = $startdate;
        $dinfo = usergetdate($sdate);
        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
            $startweek = $startdate - $dinfo['wday'] * DAYSECS; // Call new variable.
        } else {
            $wday = $dinfo['wday'] === 0 ? 7 : $dinfo['wday'];
            $startweek = $startdate - ($wday - 1) * DAYSECS;
        }

        $wdaydesc = array(0 => 'SU', 'M', 'TU', 'W', 'TH', 'F', 'SA');
        $sdays = explode('-', $sessionsinfo->availableweekdays);
        // if not specified any days,we are considering monday to saturday


        if (empty($sessionsinfo->availableweekdays))
            $sdays = array('M', 'TU', 'W', 'TH', 'F', 'SA');

        while ($sdate <= $enddate) {
            if ($sdate <= $startweek + WEEKSECS) {
                $dinfo = usergetdate($sdate);
                if (isset($sdays) && in_array($wdaydesc[$dinfo['wday']], $sdays)) {
                    $sess = new stdClass();
                    $sess->id = $sessionsinfo->id;
                    $sess->sessdate = usergetmidnight($sdate) + $starttime_seconds;
                    $sessions[] = $sess;
                }
                $sdate += DAYSECS;
            } else {
                $period = 1;
                $startweek += WEEKSECS * $period;
                $sdate = $startweek;
            }
        }
    } else {
        //$sess = new stdClass();
        //$sess->sessdate = $formdata->sessiondate;
        //$sess->duration = $duration;
        //$sess->descriptionitemid = $formdata->sdescription['itemid'];
        //$sess->description = $formdata->sdescription['text'];
        //$sess->descriptionformat = $formdata->sdescription['format'];
        //$sess->timemodified = $now;
        //
        //local_fill_groupid($formdata, $sessions, $sess);
    }
    //   print_object($sessions);
    if (empty($sessions))
        $sessions = 0;
    return $sessions;
}

$context = context_user::instance($USER->id);
$systemcontext = context_system::instance();
// only for student
if (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
// function student_semesters($userid, $from = null) {
    require_once($CFG->dirroot . '/local/courseregistration/lib.php');
    $semester = student_semesters($USER->id, 'courseregistration');
    // print_object($semester);
    foreach ($semester as $key => $value) {
        $current_semid = $key;
    }
    if (empty($current_semid))
        print_error(get_string('noactivesem', 'local_timetable'));

    $sessionsinfo = $DB->get_records_sql("select sd.* FROM {local_user_clclasses} as cl
                       JOIN {local_scheduleclass} as sd ON sd.classid= cl.classid and sd.visible=1
                       WHERE cl.semesterid=$current_semid and cl.userid=$USER->id  and from_unixtime('%Y',sd.startdate) > 2014 and sd.visible=1   ");
}
// only for registrar
else if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
    $hier = new hierarchy();
    $schoolist = $hier->get_assignedschools();
    if ($schoolist) {
        $result = '';
        foreach ($schoolist as $school)
            $result[] = $school->id;
        $result = implode(',', $result);
        $sessionsinfo = $DB->get_records_sql("select * from {local_scheduleclass} where schoolid IN ($result) and  from_unixtime('%Y',startdate) > 2014 and visible=1");
    }
}
// only for instructor
else if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {

    $sessionsinfo = $DB->get_records_sql(" select sh.*  from  mdl_local_clclasses as cl
           JOIN mdl_local_scheduleclass as sh ON sh.classid=cl.id
     where sh.instructorid=$USER->id and from_unixtime('%Y',sh.startdate) > 2013 and sh.visible=1");
} else {
    if (is_siteadmin())
        $sessionsinfo = $DB->get_records_sql("select * from {local_scheduleclass} where from_unixtime('%Y',startdate) > 2014 and visible=1");
}



$color_codes = array('#737CA1', '#B4CFEC', '#C2DFFF', '#EOFFFF', '#ADDFFF', '#CCFFFF', '#CFECFC', '#FAEBD7', '#FFE5B4', '#F5F5DC', 'E3E4FA', '357EC7'
    , '368BC1', '488AC7', '3090C7', '659EC7', '3BB9FF', '82CAFA', '#43C6DB');

//print_object($sessionsinfo);'

function rand_color() {
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

$i = 0;
$numberofcolor_code = sizeof($color_codes);






foreach ($sessionsinfo as $key => $session) {
    if ($i == $numberofcolor_code)
        $i = 0;
    // if (!isset($color_codes[$key]))
    //     $ccode = '#90EE90';
    //  else
    //      $ccode = $color_codes[$key];
    //$ccode= rand_color();
    $ccode = $color_codes[$i];
    $session_availableday = timetable_local_construct_sessions_data_for_add($session);
    $calssinfo = $DB->get_record('local_clclasses', array('id' => $session->classid));
    $instructorinfo = $DB->get_record('user', array('id' => $session->instructorid));
    $location_info = $DB->get_record_sql(" select clroom.id, clroom.fullname as classroom , b.fullname as building , fl.fullname as floor FROM  {local_classroom} as clroom 
           JOIN {local_building} as b ON b.id= clroom.buildingid and b.visible=1
           JOIN {local_floor} as fl ON fl.id=clroom.floorid and fl.visible=1 where clroom.id=$session->classroomid");
    if (empty($location_info))
        $classroom = '----';
    else
        $classroom = $location_info->classroom;



    if ($session_availableday) {

        // specific  class scheduled sessions  
        foreach ($session_availableday as $sess) {
            $startdate_time = '';
            $enddate_time = '';


            $startdate = date('Y-m-d', $sess->sessdate);
            $starttime = $session->starttime;
            $startdate_time = $startdate . 'T' . $starttime;



            $enddate = date('Y-m-d', $sess->sessdate);
            $endtime = $session->endtime;
            $enddate_time = $enddate . 'T' . $endtime;
            if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
                // only for instructor
                //checking today date is current date to provide the attendance link 
                $today = date(' Y-m-d');       
                $today_unixtimestamp = strtotime($today);
                $startdate_unixtimestamp = strtotime($startdate);
                //$date1 = date_create($startdate);
                //$date2 = date_create(date(' Y-m-d'));
                //$diff = date_diff($date1, $date2);
                //$differdays = $diff->format("%a");
                
                $differdays=($startdate_unixtimestamp - $today_unixtimestamp);    
             
           $events[] = array('id' => $session->classid,
                    'title' => $calssinfo->fullname,
                    'instructor' => $instructorinfo->firstname . '' . $instructorinfo->lastname,
                    'start' => $startdate_time,
                    'end' => $enddate_time,
                    'color' => $ccode,
                    'classroom' => $classroom,
                    'today' => $differdays,
                    'starttime' => $session->starttime,
                    'attendance' => 'Take Attendance<img scr=' . $CFG->wwwroot . '/pix/c/group.gif' . '></img>',
                    'allDay' => false);
            } else {
                $events[] = array('id' => $session->id,
                    'title' => $calssinfo->fullname,
                    'instructor' => $instructorinfo->firstname . '' . $instructorinfo->lastname,
                    'start' => $startdate_time,
                    'end' => $enddate_time,
                    'color' => $ccode,
                    'classroom' => $classroom,
                    // 'url' => "http://yahoo.com/",
                    'allDay' => false);
            }
        }
        $i++;
    }
}

//print_object($events);

$response = json_encode($events);
echo $response;
?>