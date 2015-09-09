<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
global $DB, $USER, $val;
require_once("$CFG->libdir/pdflib.php");
require_once ("../../local/request/lib/lib.php");
require_once('../../message/lib.php');
$id = optional_param('id', $USER->id, PARAM_INT);

$sch = optional_param('school', 0, PARAM_INT);
$activ = optional_param('activitytype', 0, PARAM_INT);
$sem = optional_param('semester', 0, PARAM_INT);
$eve = optional_param('eventlev', 0, PARAM_RAW);
$systemcontext = context_system::instance();
$doc = new TCPDF(null, 'cm', 'A4', true, 'UTF-8', false);
$doc->setPrintHeader(FALSE);
$doc->setPrintFooter(FALSE);
$doc->Addpage($orientation = 'P', $format = 'A4', $keepmargins = true, $tocpage = true);
$re = new requests();
$schoolid = $DB->get_field('local_school', 'id', array('fullname' => $_GET['school']));
$semester = $DB->get_field('local_semester', 'id', array('fullname' => $_GET['semester']));
$activitytype = $DB->get_field('local_event_types', 'id', array('eventtypename' => $_GET['activitytype']));
$programid = $DB->get_field('local_program', 'id', array('fullname' => $_GET['program']));
$strdate = strtotime($_GET['startdate']);
"<br>";
$enddate = strtotime($_GET['enddate']);
$acivitylev = $_GET['eventlev'];
if ($acivitylev) {
    if ($acivitylev == 'Global') {
        $actlev = 1;
    }
    if ($acivitylev == 'School') {
        $actlev = 2;
    }
    if ($acivitylev == 'Program') {
        $actlev = 3;
    }
    if ($acivitylev == 'Semester') {
        $actlev = 4;
    }
}

$sql = 'select * from {local_event_activities} where publish = 1';
if ($activitytype) {
    $sql .=" AND eventtypeid = '$activitytype' ";
}
if ($schoolid) {
    $sql .=" AND schoolid = '$schoolid' ";
}
if ($semester) {
    $sql .=" AND semesterid = '$semester'";
}
if ($acivitylev) {
    $sql .=" AND eventlevel = '$actlev'";
}
if ($programid) {
    $sql .=" AND programid = '$programid'";
}
if ($strdate) {
    $sql .=" AND startdate = '$strdate'";
}
if ($enddate) {
    $sql .=" AND enddate = '$enddate'";
}
$sql .= " ORDER BY academicyear";
$events = $DB->get_records_sql($sql);
$semlist = array();
$schoollist = array();
$list = array();
if ($events) {
    foreach ($events as $event) {
        if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
            $user_school = $DB->get_records_sql("select *
                                         from {local_school_permissions} lsp INNER JOIN {local_program} lp ON
                                         lsp.schoolid = lp.schoolid
                                         where lsp.schoolid = {$event->schoolid} and lsp.userid = {$USER->id} group by lp.id");
            $user_sem = $DB->get_records_sql("select ls.id as semesterid from {local_school_permissions} lsp INNER JOIN {local_school_semester} lss ON
                                      lsp.schoolid = lss.schoolid INNER JOIN {local_semester} ls ON lss.semesterid = ls.id
                                      where lsp.schoolid = {$event->schoolid} and lss.schoolid = {$event->schoolid} and
                                      lsp.userid = {$USER->id}");
        } elseif (has_capability('local/clclasses:approvemystudentclclasses', $systemcontext) && !is_siteadmin()) {
            $user_school = $DB->get_records_sql("select * from {local_school_permissions} lsp INNER JOIN
                                        {local_assignmentor_tostudent} lat ON lsp.schoolid = lat.schoolid and lsp.userid = lat.mentorid 
                                        where lsp.userid = {$USER->id}");
            $user_sem = $DB->get_records_sql("select * from {local_school_permissions} lsp INNER JOIN {local_assignmentor_tostudent} lat
                                      ON lsp.schoolid = lat.schoolid and lsp.userid = lat.mentorid INNER JOIN {local_userdata} lud ON
                                      lud.userid = lat.studentid and lat.schoolid = lud.schoolid INNER JOIN {local_user_semester} lus ON 
                                      lud.userid = lus.userid where lsp.userid = {$USER->id}");
        } elseif (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
            $user_school = $DB->get_records_sql("select lud.* from {local_school_permissions} lsp INNER JOIN {local_scheduleclass} ls ON
                                        lsp.schoolid = ls.schoolid and lsp.userid = ls.instructorid INNER JOIN {local_user_clclasses} luc ON
                                        luc.classid = ls.classid INNER JOIN {local_userdata} lud ON lud.userid = luc.userid and ls.schoolid = lud.schoolid where lsp.userid = {$USER->id} and 
                                        lsp.schoolid = {$event->schoolid}");
            $user_sem = $DB->get_records_sql("select * from {local_scheduleclass} where instructorid = {$USER->id}");
        } else {
            $user_sem = $DB->get_records_sql("SELECT * FROM {local_user_semester} lus INNER JOIN {local_userdata} lu ON lu.userid = lus.userid WHERE lus.semesterid = {$event->semesterid} AND lu.userid = {$USER->id} AND lu.schoolid = {$event->schoolid}");
            $user_school = $DB->get_records_sql("SELECT * FROM {local_userdata} WHERE schoolid = {$event->schoolid} AND userid = {$USER->id}");
        }

        if ($user_school) {
            foreach ($user_school as $userschool) {
                $list1 = array();
                $list1['id'] = $userschool->schoolid;
                $list1['fullname'] = $DB->get_field('local_school', 'fullname', array('id' => $userschool->schoolid));
                if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
                    $list1['programid'] = $userschool->id;
                    $list1['programname'] = $userschool->fullname;
                } elseif (has_capability('local/clclasses:approvemystudentclclasses', $systemcontext) && !is_siteadmin()) {
                    $list1['programid'] = $userschool->programid;
                    $list1['programname'] = $DB->get_field('local_program', 'fullname', array('id' => $userschool->programid));
                } elseif (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
                    $list1['programid'] = $userschool->programid;
                    $list1['programname'] = $DB->get_field('local_program', 'fullname', array('id' => $userschool->programid));
                } else {
                    $list1['programid'] = $userschool->programid;
                    $list1['programname'] = $DB->get_field('local_program', 'fullname', array('id' => $userschool->programid));
                }
                $schoollist[] = $list1;
            }
        }
        if ($event->eventlevel == 4) {
            if ($user_sem) {

                foreach ($user_sem as $usersem) {
                    if ($usersem->semesterid != $val) {
                        $list = array();
                        $list['id'] = $usersem->semesterid;
                        $list['fullname'] = $DB->get_field('local_semester', 'fullname', array('id' => $usersem->semesterid));
                        $semstart = $DB->get_field('local_semester', 'startdate', array('id' => $usersem->semesterid));
                        $list['semstrdate'] = $semstart;
                        $list['semdate'] = date('Y', $semstart);
                        $semlist[] = $list;
                    }
                }
                $val = $usersem->semesterid;
            }
        }
    }
} else {
    die("no records found");
}
$schoolslist = $re->array_unique_multidimensional($schoollist);
$input = $re->array_unique_multidimensional($semlist);

if ($acivitylev != 'Global') {
    foreach ($schoolslist as $schoolkey => $myschools) {
        if ($schoolkey == 0) {
            $topheader = "<h3 style=\"text-align:center;color:#fc4705\">" . strtoupper($schoolslist[$schoolkey]['fullname']) . "</h3>";
            if ($acivitylev == 'Program') {
                $htable2 = "<br><table><tr><th style=\"text-align:center;color:#0f44d6\"><b>PROGRAM EVENTS</b></th></tr></table><br>";
            }if ($acivitylev == 'School') {
                $htable3 = "<br><table><tr><th style=\"text-align:center;color:#0f44d6\"><b>SCHOOL EVENTS</b></th></tr></table><br>";
            }
        } else {
            if ($schoolslist[$schoolkey - 1]['fullname'] == $schoolslist[$schoolkey]['fullname']) {
                $topheader = "";
                $htable2 = "";
                $htable3 = "";
            } else {
                $topheader = "<h3 style=\"text-align:center;color:#fc4705\">" . strtoupper($schoolslist[$schoolkey]['fullname']) . "</h3>";
                if ($acivitylev == 'Program') {
                    $htable2 = "<br><table><tr><th style=\"text-align:center;color:#0f44d6\"><b>PROGRAM EVENTS</b></th></tr></table><br>";
                }if ($acivitylev == 'School') {
                    $htable3 = "<br><table><tr><th style=\"text-align:center;color:#0f44d6\"><b>SCHOOL EVENTS</b></th></tr></table><br>";
                }
            }
        }
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $topheader, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable2, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable3, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        if ($input) {
            foreach ($input as $key => $sems) {
                $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";
                if ($sems[id]) {
                    $eventsquery .=" AND semesterid = '$sems[id]' AND schoolid = '$myschools[id]' ";
                }
                if ($activitytype) {
                    $eventsquery .=" AND eventtypeid = '$activitytype' ";
                }
                if ($schoolid) {
                    $eventsquery .=" AND schoolid = '$schoolid' ";
                }
                if ($semester) {
                    $eventsquery .=" AND semesterid = '$semester'";
                }
                if ($acivitylev) {
                    $eventsquery .=" AND eventlevel = '$actlev'";
                }
                if ($programid) {
                    $eventsquery .=" AND programid = '$programid'";
                }
                if ($strdate) {
                    $eventsquery .=" AND startdate = '$strdate'";
                }
                if ($enddate) {
                    $eventsquery .=" AND enddate = '$enddate'";
                }
                $ac_years = $DB->get_records_sql($eventsquery);

                $sem_name = $DB->get_record_sql("SELECT * FROM {local_semester} WHERE id=$sems[id]");

                if ($ac_years) {
                    if ($key == 0) {
                        $headers = "<h4 style=\"text-align:center;color:#0f44d6\">" . $sems['semdate'] . '/' . date('y', strtotime('+1 year', $sems['semstrdate'])) . ' ACADEMIC CALENDAR' . "</h4>";
                    } else {
                        if ($input[$key - 1]['semdate'] == $input[$key]['semdate']) {
                            $headers = "";
                        } else {
                            $headers = "<br><br><h4 style=\"text-align:center;margin-bottom:5px;color:#0f44d6\">" . $sems['semdate'] . '/' . date('y', strtotime('+1 year', $sems['semstrdate'])) . ' ACADEMIC CALENDAR' . "</h4>";
                        }
                    }
                    $htable = "<br><table><tr><th style=\"text-align:center;color:#0f44d6\"><b>" . $sem_name->fullname . "</b></th></tr></table><br>";
                    $table = "<table border=\"1\" cellpadding=\"5\" style=\"font-size:300px\"><tr><th style=\"text-align:center\"><b>EVENT TITLE</b></th><th style=\"text-align:center\"><b>DATES</b></th></tr>";

                    foreach ($ac_years as $academicyear) {
                        $evnt = array();
                        $evnt['semester'] = $academicyear->semesterid;
                        if ($academicyear->enddate) {
                            $table .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . '&nbsp;&nbsp;-&nbsp;&nbsp;' . strtoupper(date('d-M-Y', $academicyear->enddate)) . "</th></tr>";
                        } else {
                            $table .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . "</th></tr>";
                        }
                    }
                    $table .= "</table>";
                    if ($schoolkey == 0) {
                        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $headers, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                    } else {
                        if ($schoolslist[$schoolkey - 1]['fullname'] != $schoolslist[$schoolkey]['fullname']) {
                            $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $headers, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                            $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                            $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                        }
                    }
                }
            }
        } else {
            if ($acivitylev == 'School') {
                $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";

                $eventsquery .=" AND eventlevel = 2 AND schoolid = $myschools[id]";
                if ($activitytype) {
                    $eventsquery .=" AND eventtypeid = '$activitytype' ";
                }
                if ($strdate) {
                    $eventsquery .=" AND startdate = '$strdate'";
                }
                if ($enddate) {
                    $eventsquery .=" AND enddate = '$enddate'";
                }
                if ($schoolid) {
                    $eventsquery .=" AND schoolid = '$schoolid' ";
                }
                $ac_years = $DB->get_records_sql($eventsquery);

                $table3 = "<table border=\"1\" cellpadding=\"5\" style=\"font-size:300px\"><tr><th style=\"text-align:center\"><b>EVENT TITLE</b></th><th style=\"text-align:center\"><b>DATES</b></th></tr>";

                foreach ($ac_years as $academicyear) {
                    $evnt = array();
                    $evnt['semester'] = $academicyear->semesterid;
                    if ($academicyear->enddate) {
                        $table3 .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . '&nbsp;&nbsp;-&nbsp;&nbsp;' . strtoupper(date('d-M-Y', $academicyear->enddate)) . "</th></tr>";
                    } else {
                        $table3 .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . "</th></tr>";
                    }
                }
                $table3 .= "</table>";
                $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }
            if ($acivitylev == 'Program') {
                $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";

                $eventsquery .=" AND eventlevel = 3 AND programid = $myschools[programid] AND schoolid = $myschools[id]";
                if ($activitytype) {
                    $eventsquery .=" AND eventtypeid = '$activitytype' ";
                }
                if ($strdate) {
                    $eventsquery .=" AND startdate = '$strdate'";
                }
                if ($enddate) {
                    $eventsquery .=" AND enddate = '$enddate'";
                }
                if ($programid) {
                    $eventsquery .=" AND programid = '$programid'";
                }
                if ($schoolid) {
                    $eventsquery .=" AND schoolid = '$schoolid'";
                }
                $ac_years = $DB->get_records_sql($eventsquery);
                if ($ac_years) {
                    if ($schoolkey == 0) {
                        $topheader1 = "<h3 style=\"text-align:center;color:#fc4705\">" . strtoupper($schoolslist[$schoolkey]['programname']) . "</h3>";
                    } else {
                        if ($schoolslist[$schoolkey - 1]['programid'] == $schoolslist[$schoolkey]['programid']) {
                            $topheader1 = "";
                        } else {
                            $topheader1 = "<h3 style=\"text-align:center;color:#fc4705\">" . strtoupper($schoolslist[$schoolkey]['programname']) . "</h3>";
                        }
                    }

                    $table2 = "<table border=\"1\" cellpadding=\"5\" style=\"font-size:300px\"><tr><th style=\"text-align:center\"><b>EVENT TITLE</b></th><th style=\"text-align:center\"><b>DATES</b></th></tr>";

                    foreach ($ac_years as $academicyear) {
                        $evnt = array();
                        $evnt['semester'] = $academicyear->semesterid;
                        if ($academicyear->enddate) {
                            $table2 .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . '&nbsp;&nbsp;-&nbsp;&nbsp;' . strtoupper(date('d-M-Y', $academicyear->enddate)) . "</th></tr>";
                        } else {
                            $table2 .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . "</th></tr>";
                        }
                    }
                    $table2 .= "</table>";
                    $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $topheader1, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                    $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table2, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                }
            }
        }
        if ($schoolkey == 0) {
            $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table3, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        } else {
            if ($schoolslist[$schoolkey - 1]['fullname'] != $schoolslist[$schoolkey]['fullname']) {
                $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table3, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }
        }
    }
} else {
    $context = context_user::instance($id);
    if (has_capability('local/collegestructure:manage', $systemcontext)) {
        $htable = "<table><tr><th style=\"text-align:center;color:#0f44d6\"><b>GLOBAL EVENTS</b></th></tr></table><br>";
        $htable1 = "<span>Academic Year:date('Y')</span><br><span>ASSINGED SCHOOLS:</span><br><table width=\"50%\">";
        $schooname = $DB->get_records('local_school_permissions', array('userid' => $id));

        $count = 0;
        foreach ($schooname as $sa) {
            $schoolsarray = $DB->get_field('local_school', 'fullname', array('id' => $sa->schoolid));

            $htable1 .= "<tr><td style=\"text-align:left;color:#fc4705\"><b>" . strtoupper($schoolsarray) . "</b></td></tr>";
        }


        $htable1 .= "</table><br>";
    } elseif (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
        $htable = "<table><tr><th style=\"text-align:center;color:#0f44d6\"><b>GLOBAL EVENTS</b></th></tr></table><br>";
        $htable1 = "<span>ASSINGED SCHOOLS:</span><br><table width=\"50%\">";
        $schoolname = $DB->get_records('local_userdata', array('userid' => $id));

        foreach ($schoolname as $schname) {
            $schoolnames = $DB->get_field('local_school', 'fullname', array('id' => $schname->schoolid));
            $htable1 .= "<tr><td style=\"text-align:left;color:#fc4705\"><b>" . strtoupper($schoolnames) . "</b></td></tr>";
        }


        $htable1 .= "</table><br>";
    } else {

        $htable = "<table><tr><th style=\"text-align:center;color:#0f44d6\"><b>GLOBAL EVENTS</b></th></tr></table><br>";
        $schooname = $DB->get_records('local_school_permissions', array('userid' => $id));

        foreach ($schooname as $sa) {
            $schoolsarray = $DB->get_field('local_school', 'fullname', array('id' => $sa->schoolid));

            $htable1 .= "<table><tr><th style=\"text-align:center;color:#fc4705\"><b>" . strtoupper($schoolsarray) . "</b></th></tr></table>";
        }
    }
    if (has_capability('local/clclasses:approvemystudentclclasses', $systemcontext) && !is_siteadmin()) {
        $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";
        $eventsquery .=" AND eventlevel = 1 AND eventtypeid != 1";
    } elseif (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
        $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";
        $eventsquery .=" AND eventlevel = 1 AND eventtypeid != 1";
    } elseif (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
        $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";
        $eventsquery .=" AND eventlevel = 1";
    } else {
        $eventsquery = "SELECT *
FROM mdl_local_event_activities  where publish=1";
        $eventsquery .=" AND eventlevel = 1 AND eventtypeid != 1";
    }
    if ($activitytype) {
        $eventsquery .=" AND eventtypeid = '$activitytype' ";
    }
    if ($strdate) {
        $eventsquery .=" AND startdate = '$strdate'";
    }
    if ($enddate) {
        $eventsquery .=" AND enddate = '$enddate'";
    }
    $ac_years = $DB->get_records_sql($eventsquery);
    $table = "<table border=\"1\" cellpadding=\"5\" style=\"font-size:300px\"><tr><th style=\"text-align:center\"><b>EVENT TITLE</b></th><th style=\"text-align:center\"><b>DATES</b></th></tr>";

    foreach ($ac_years as $academicyear) {
        $evnt = array();
        $evnt['semester'] = $academicyear->semesterid;
        if ($academicyear->enddate) {
            $table .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . '&nbsp;&nbsp;-&nbsp;&nbsp;' . strtoupper(date('d-M-Y', $academicyear->enddate)) . "</th></tr>";
        } else {
            $table .= "<tr><th style=\"padding-left:10px\">" . $academicyear->eventtitle . "</th><th style=\"text-align:center\">" . strtoupper(date('d-M-Y', $academicyear->startdate)) . "</th></tr>";
        }
    }
    $table .= "</table>";
    if (has_capability('local/collegestructure:manage', $systemcontext)) {
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable1, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
    } elseif (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable1, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
    } else {
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable1, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
    }
    $doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
}


ob_end_clean();

$doc->Output('Academiccalendar.pdf', 'I');
?>