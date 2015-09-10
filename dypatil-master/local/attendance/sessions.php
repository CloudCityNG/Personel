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
 * Adding attendance sessions
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/add_form.php');
require_once(dirname(__FILE__).'/update_form.php');
require_once(dirname(__FILE__).'/duration_form.php');

$pageparams = new local_att_sessions_page_params();

$id                     = required_param('id', PARAM_INT);
$pageparams->action     = required_param('action', PARAM_INT);
$fromcalendarview=optional_param('fromcalendar',0,PARAM_INT);


$att            = $DB->get_record('local_attendance', array('id' => $id), '*', MUST_EXIST);
$classinfo      = $DB->get_record('local_clclasses',array('id'=>$att->classid));
//$sacademicyearinfo        = $DB->get_record('local_academicyear', array('id' =>$sessioninfo ->academicyearid ));
$PAGE->set_context(context_system::instance());


require_login();
//print_object($att);

$att = new local_attendance($att, $classinfo, $PAGE->context, $pageparams);
$att->perm->require_manage_capability();

$PAGE->set_url($att->url_sessions());
$PAGE->set_pagelayout('admin');
//$PAGE->set_title($course->shortname. ": ".$att->name);
//$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add($att->name);

$formparams = array('attendanceid' => $att->id ,'classid'=>$att->classid ,'fromcalendar'=>$fromcalendarview);
switch ($att->pageparams->action) {
    case local_att_sessions_page_params::ACTION_ADD:
        $url = $att->url_sessions(array('action' => local_att_sessions_page_params::ACTION_ADD));
        $mform = new local_attendance_add_form($url, $formparams);

        if ($formdata = $mform->get_data()) {
            $sessions = local_construct_sessions_data_for_add($formdata);
            $att->add_sessions($sessions);
            redirect($url, get_string('sessionsgenerated', 'local_attendance'));
        }
        break;
    case local_att_sessions_page_params::ACTION_UPDATE:
        $sessionid = required_param('sessionid', PARAM_INT);

        $url = $att->url_sessions(array('action' => local_att_sessions_page_params::ACTION_UPDATE, 'sessionid' => $sessionid));
        $formparams['sessionid'] = $sessionid;
        $mform = new local_attendance_update_form($url, $formparams);

        if ($mform->is_cancelled()) {
            redirect($att->url_manage());
        }

        if ($formdata = $mform->get_data()) {
            $att->update_session_from_form_data($formdata, $sessionid);

            redirect($att->url_manage(), get_string('sessionupdated', 'local_attendance'));
        }
        break;
    case local_att_sessions_page_params::ACTION_DELETE:
        $sessionid = required_param('sessionid', PARAM_INT);
        $confirm   = optional_param('confirm', null, PARAM_INT);

        if (isset($confirm) && confirm_sesskey()) {
            $att->delete_sessions(array($sessionid));
            if ($att->grade > 0) {
                local_att_update_all_users_grades($att->id, $att->semesterinfo ,$att->context, $att->classinfo ,$att);
            }
            redirect($att->url_manage(), get_string('sessiondeleted', 'local_attendance'));
        }

        $sessinfo = $att->get_session_info($sessionid);

        $message = get_string('deletecheckfull', '', get_string('session', 'local_attendance'));
        $message .= str_repeat(html_writer::empty_tag('br'), 2);
        $message .= userdate($sessinfo->sessdate, get_string('strftimedmyhm', 'local_attendance'));
        $message .= html_writer::empty_tag('br');
        $message .= $sessinfo->description;

        $params = array('action' => $att->pageparams->action, 'sessionid' => $sessionid, 'confirm' => 1, 'sesskey' => sesskey());

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('attendanceforthecourse', 'local_attendance').' :: ' );
        echo $OUTPUT->confirm($message, $att->url_sessions($params), $att->url_manage());
        echo $OUTPUT->footer();
        exit;
    case local_att_sessions_page_params::ACTION_DELETE_SELECTED:
        $confirm    = optional_param('confirm', null, PARAM_INT);

        if (isset($confirm) && confirm_sesskey()) {
            $sessionsids = required_param('sessionsids', PARAM_ALPHANUMEXT);
            $sessionsids = explode('_', $sessionsids);

            $att->delete_sessions($sessionsids);
            if ($att->grade > 0) {
                local_att_update_all_users_grades($att->id,  $att->semesterinfo, $att->context,  $att->classinfo, $att);
            }
            redirect($att->url_manage(), get_string('sessiondeleted', 'local_attendance'));
        }
        $sessid = required_param('sessid', PARAM_SEQUENCE);

        $sessionsinfo = $att->get_sessions_info($sessid);

        $message = get_string('deletecheckfull', '', get_string('session', 'local_attendance'));
        $message .= html_writer::empty_tag('br');
        foreach ($sessionsinfo as $sessinfo) {
            $message .= html_writer::empty_tag('br');
            $message .= userdate($sessinfo->sessdate, get_string('strftimedmyhm', 'local_attendance'));
            $message .= html_writer::empty_tag('br');
            $message .= $sessinfo->description;
        }

        $sessionsids = implode('_', $sessid);
        $params = array('action' => $att->pageparams->action, 'sessionsids' => $sessionsids,
                        'confirm' => 1, 'sesskey' => sesskey());

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('attendanceforthecourse', 'local_attendance').' :: ');
        echo $OUTPUT->confirm($message, $att->url_sessions($params), $att->url_manage());
        echo $OUTPUT->footer();
        exit;
    case local_att_sessions_page_params::ACTION_CHANGE_DURATION:
        $sessid = optional_param('sessid', '', PARAM_SEQUENCE);
        $ids = optional_param('ids', '', PARAM_ALPHANUMEXT);

        $slist = isset($sessid) ? implode('_', $sessid) : '';

        $url = $att->url_sessions(array('action' => local_att_sessions_page_params::ACTION_CHANGE_DURATION));
        $formparams['ids'] = $slist;
        $mform = new local_attendance_duration_form($url, $formparams);

        if ($mform->is_cancelled()) {
            redirect($att->url_manage());
        }

        if ($formdata = $mform->get_data()) {
            $sessionsids = explode('_', $ids);
            $duration = $formdata->durtime['hours']*HOURSECS + $formdata->durtime['minutes']*MINSECS;
            $att->update_sessions_duration($sessionsids, $duration);
            redirect($att->url_manage(), get_string('sessionupdated', 'local_attendance'));
        }

        if ($slist === '') {
            print_error('nosessionsselected', 'local_attendance', $att->url_manage());
        }

        break;
}

$output = $PAGE->get_renderer('local_attendance');
$tabs = new local_attendance_tabs($att, local_attendance_tabs::TAB_ADD);
echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'local_attendance').' :: ' );
echo $output->render($tabs);

$mform->display();

echo $OUTPUT->footer();

//function local_construct_sessions_data_for_add($formdata) {
//    global $CFG;
//   
//    echo $duration = $formdata->durtime['hours']*HOURSECS + $formdata->durtime['minutes']*MINSECS;
//    echo '</br>';
//    echo $starttime = $startdate - usergetmidnight($startdate);
//    echo $startdate = $formdata->sessiondate;
//     echo '</br>';
//    echo    $enddate = $formdata->sessionenddate + DAYSECS; // Because enddate in 0:0am.
//
//   
//    $now = time();
//
//    $sessions = array();
//    if (isset($formdata->addmultiply)) {
//        $startdate = $formdata->sessiondate;
//        $starttime = $startdate - usergetmidnight($startdate);
//        $enddate = $formdata->sessionenddate + DAYSECS; // Because enddate in 0:0am.
//
//        if ($enddate < $startdate) {
//            return null;
//        }
//
//        $days = (int)ceil(($enddate - $startdate) / DAYSECS);
//
//        // Getting first day of week.
//        $sdate = $startdate;
//        $dinfo = usergetdate($sdate);
//        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
//            $startweek = $startdate - $dinfo['wday'] * DAYSECS; // Call new variable.
//        } else {
//            $wday = $dinfo['wday'] === 0 ? 7 : $dinfo['wday'];
//            $startweek = $startdate - ($wday-1) * DAYSECS;
//        }
//
//        $wdaydesc = array(0=>'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
//
//        while ($sdate < $enddate) {
//            if ($sdate < $startweek + WEEKSECS) {
//                $dinfo = usergetdate($sdate);
//                if (isset($formdata->sdays) && array_key_exists($wdaydesc[$dinfo['wday']], $formdata->sdays)) {
//                    $sess = new stdClass();
//                    $sess->sessdate =  usergetmidnight($sdate) + $starttime;
//                    $sess->duration = $duration;
//                    $sess->descriptionitemid = $formdata->sdescription['itemid'];
//                    $sess->description = $formdata->sdescription['text'];
//                    $sess->descriptionformat = $formdata->sdescription['format'];
//                    $sess->timemodified = $now;
//                    if (isset($formdata->studentscanmark)) { // Students will be able to mark their own attendance.
//                        $sess->studentscanmark = 1;
//                    }             
//                     
//                    fill_groupid($formdata, $sessions, $sess);
//                }
//                $sdate += DAYSECS;
//            } else {
//                $startweek += WEEKSECS * $formdata->period;
//                $sdate = $startweek;
//            }
//        }
//    } else {
//        $sess = new stdClass();
//        $sess->sessdate = $formdata->sessiondate;
//        $sess->duration = $duration;
//        $sess->descriptionitemid = $formdata->sdescription['itemid'];
//        $sess->description = $formdata->sdescription['text'];
//        $sess->descriptionformat = $formdata->sdescription['format'];
//        $sess->timemodified = $now;
//        if (isset($formdata->studentscanmark)) { // Students will be able to mark their own attendance.
//            $sess->studentscanmark = 1; 
//        }
//
//        fill_groupid($formdata, $sessions, $sess);
//    }
// //  exit;
//    return $sessions;
//   }
//
//function fill_groupid($formdata, &$sessions, $sess) {
//    if ($formdata->sessiontype == attendance::SESSION_COMMON) {
//        $sess = clone $sess;
//        $sess->groupid = 0;
//        $sessions[] = $sess;
//    } else {
//        foreach ($formdata->groups as $groupid) {
//            $sess = clone $sess;
//            $sess->groupid = $groupid;
//            $sessions[] = $sess;
//        }
//    }
//}