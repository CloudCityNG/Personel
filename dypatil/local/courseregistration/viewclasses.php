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
 * 
 * @package    local
 * @subpackage course registration
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
global $CFG, $USER, $DB;

$systemcontext = context_system::instance();
$cid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
require_login();
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/courseregistration/index.php');

/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('viewclclasses', 'local_courseregistration'), "/local/courseregistration/index.php");


$PAGE->navbar->add(get_string('manageregistration', 'local_courseregistration'));
$usercurculum = $DB->get_field('local_userdata', 'curriculumid', array('userid' => $USER->id));
$schoolid = $DB->get_field('local_userdata', 'schoolid', array('userid' => $USER->id));
$course_exist = $DB->get_record('local_curriculum_plancourses', array('curriculumid' => $usercurculum, 'courseid' => $cid));
if (empty($course_exist)) {
    $star = '<b style="color:red;">* &nbsp;&nbsp;' . get_string('outofcurriculum', 'local_courseregistration') . '</b>';
} else {
    $star = '';
}
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('manageregistration', 'local_courseregistration'));

/* ---Moodle 2.2 and onwards--- */

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_courseregistration'));
}
$precourses = prerequisite_courses($cid);
$equcourses = equivalent_courses($cid);
$data = array();

//$query = "SELECT c.*,cc.fullname as coursename,cc.credithours credithours,cc.shortname courseshortname,
//             (SELECT MAX(CONCAT(cls.fullname,':',f.fullname,':',b.fullname)) as classroom FROM {local_scheduleclass} cs JOIN {local_classroom} cls ON cls.id=cs.classroomid JOIN {local_floor} f ON f.id=cls.floorid JOIN {local_building} b ON b.id=cls.buildingid WHERE cs.classid = c.id) AS classroom,
//             (select Max(concat(FROM_UNIXTIME(lsc.startdate, '%d/%b/%Y'),'&nbsp; - &nbsp;',FROM_UNIXTIME(lsc.enddate, '%d/%b/%Y'))) FROM {local_scheduleclass} as lsc where lsc.classid=c.id AND lsc.startdate>0 AND enddate>0 ) AS dates,
//             (select Max(concat(lsc.starttime,'&nbsp;-&nbsp;',lsc.endtime)) FROM {local_scheduleclass} as lsc where lsc.classid=c.id AND lsc.startdate>0 AND enddate>0 ) AS timing,
//             (select lsc.availableweekdays  FROM {local_scheduleclass} as lsc where lsc.classid=c.id AND lsc.startdate>0 AND enddate>0 ) as availableweekdays
//             FROM {local_clclasses} c,
//                  {local_cobaltcourses} cc 
//             where c.semesterid={$semid} AND c.cobaltcourseid={$cid} AND c.cobaltcourseid=cc.id AND c.schoolid={$schoolid}";
//

$query = "SELECT c.*,cc.fullname as coursename,cc.credithours credithours,cc.shortname courseshortname             
             FROM {local_clclasses} c,
                  {local_cobaltcourses} cc 
             where c.semesterid={$semid} AND c.cobaltcourseid={$cid} AND c.cobaltcourseid=cc.id AND c.schoolid={$schoolid}";

$classList = $DB->get_records_sql($query);

foreach ($classList as $list) {
    $classtype = $list->online == 1 ? 'Online' : 'Offline';

    $sql = "SELECT * FROM {local_clclasses} c,{local_user_clclasses} uc where c.cobaltcourseid={$cid} AND uc.userid={$USER->id} AND c.id=uc.classid AND uc.semesterid={$semid}";
    $exist = $DB->record_exists_sql($sql);
    if (empty($exist)) {
        $sql = "SELECT * FROM {local_clclasses} c,{local_course_adddrop} ad where c.cobaltcourseid={$cid} AND ad.userid={$USER->id} AND c.id=ad.classid AND ad.semesterid={$semid}";
        $exist = $DB->record_exists_sql($sql);
    }

    $enrollcount = $DB->count_records('local_user_clclasses', array('classid' => $list->id, 'semesterid' => $semid, 'registrarapproval' => 1));
    $existingseats = $list->classlimit - $enrollcount;
    if ($enrollcount > $list->classlimit) {
        $existingseats = 0;
    }
    $str = '<table cellspacing="0" cellpadding="3" border="0" style="margin-top:20px;width:100%;font-size:12px;border:1px solid #cccccc;line-height:24px">
                                    <tbody><tr>';
    $str .= '<td align="left" style="font-size:12px;background:#dddddd !important;" colspan="2">
                         <b><span style="color:#0088CC;text-decoration:none;cursor:pointer;">' . $list->courseshortname . ':&nbsp;</span></b> <b>' . $list->coursename . '</b><b>' . $star . '</b>
                             <span style="color:#333333;" id="spanhonors"></span> 
             </td>';
    $str .='<td align="right" style="font-size:12px;background:#dddddd !important;">';
    $str .='<table cellspacing="0" cellpadding="0" style="width:100%;">
                    <tbody>
                           <tr>
                               <td align="left"> </td>
                               <td align="right"> <span style="font-weight:bold;color:black;">' . strtoupper(get_string('class', 'local_clclasses')) . ':</span><span style="font-weight:bold;margin-right:20px;color:#0088CC;">&nbsp;' . $list->fullname . '</span>';
    $today = date('Y-m-d');
    $events = "SELECT ea.eventtypeid FROM {local_event_activities} ea where ea.semesterid={$semid} AND ea.eventtypeid IN(2,3) AND '{$today}' BETWEEN from_unixtime( startdate,'%Y-%m-%d' ) AND from_unixtime( enddate,'%Y-%m-%d' ) ";

    $eventid = $DB->get_record_sql($events);

    if ($eventid->eventtypeid == 3) {

        $existclass = $DB->get_record('local_course_adddrop', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));
    } else {
        // need to check student enrolled same class, which was rejected previously        
        $count_sameclassenrollment = $DB->count_records('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));
        if ($count_sameclassenrollment > 1)
            $existclass = $DB->get_record('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid, 'registrarapproval' => 1));
        else
            $existclass = $DB->get_record('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));
    }
    /* Bug report #322  -  Student>Course Registration>Enroll- Status
     * @author hemalatha c arun <hemalatha@eabyas.in> 
     * Resolved- Incase admin enrolled student to class during add and drop events 
     */
    if (empty($existclass)) {
        $count_sameclassenrollment = $DB->count_records('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));
        if ($count_sameclassenrollment > 1)
            $existclass = $DB->get_record('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid, 'registrarapproval' => 1));
        else
            $existclass = $DB->get_record('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));
    }
    if ($existclass) {

        if ($existclass->registrarapproval == 1)
            $str .= get_string('already_enrolled', 'local_courseregistration');
        elseif ($existclass->registrarapproval == 0)
            $str .= 'Waiting for approval';
        elseif ($existclass->registrarapproval == 5)
            $str .= 'Unenrolled';
        else {
            if ($existclass->registrarapproval == 2)
                $str .= 'Rejected';
        }
    } // end of if condition
    //------------------------------------------------------------------------------------------------------------
    else if (($exist) || ($list->classlimit == 0) || ($existingseats == 0))
        $str .="Can not Enroll";
    else
        $str .='<a href="' . $CFG->wwwroot . '//local/courseregistration/registration.php?id=' . $list->id . '&semid=' . $semid . '&courseid=' . $cid . '&addenroll=1&sesskey=' . sesskey() . '&schoolid=' . $list->schoolid . '" >  <button value="39543" title="Enroll to class." class="enrollme" type="button" id="enroll"  style="height: 30px;" ><span class="enroll-text">' . get_string('enroll', 'local_cobaltcourses') . '</span></button> </a>';

    $str.='</td></tr></tbody></table></td> </tr>';

    $str .=' <tr>
                 <td valign="top" align="right" style="width:310px;padding-left:20px;">';
    $str .='<table cellpadding="1" border="0">
                      <tbody>
                            <tr id="instucttr">
				<td><b>' . get_string('instructor', 'local_cobaltcourses') . ':&nbsp;</b></td>
				<td>';
                    
    $instructor = array();
    $instructor[] = get_classinst($list->id);

    $str .=implode(' ', $instructor[0]);

    $str .='</td>
		            </tr>
				
                              <tr>
                                  <td style="text-align:right">
                                      <b>' . get_string('date', 'local_courseregistration') . 's:&nbsp;</b>
                                      </td><td>';
    if (empty($list->dates))
        $str .=get_string('not_scheduled', 'local_courseregistration');
    else
        $str .=$list->dates;
    $str .='</td>
                              </tr>
                               </tbody></table>';

    $str .='</td>
                                        <td valign="top" align="left" style="width:155px;">
                                            <table cellpadding="1" border="0">
                                                <tbody><tr id="tr1">
					<td><b>' . get_string('credithours', 'local_cobaltcourses') . ':&nbsp;</b></td>
					<td>' . $list->credithours . '</td>
				    </tr>
				
                                                <tr>
                                                    <td align="right"><b>' . get_string('max_seats', 'local_courseregistration') . ':</b></td><td>' . $list->classlimit . '</td>
                                                </tr>
                                                <tr>
                                                    <td align="right"><b>' . get_string('left_seats', 'local_courseregistration') . ':</b></td><td>' . $existingseats . '</td>
                                                </tr>
                                             
                                            </tbody></table>
                                        </td> ';
    $str .=' <td valign="top" align="right">
                                            <div>';
                                            
					
                         $str .= table_multiplescheduled_view( $list->id);
				   $str .= '</div>
                                        </td>
                                    </tr>';
    $str .=' <tr>
                                        <td valign="top" align="left" style="padding-left:20px;" colspan="3">
                                            <table cellspacing="4" cellpadding="0" border="0" style="width:100%;">
                                                <tbody>';


    $str .=' <tr style="">
                                                    <td style="/*border:1px solid #dddddd;*/">
                                                        <table>
                                                            <tbody><tr>
                                                                <td valign="top" align="left"><b>' . get_string('Pre-Requisite', 'local_courseregistration') . ':</b></td>
                                                                <td valign="top" align="left">' . $precourses . '</td> 
                                                            </tr>
															<tr>
                                                                <td valign="top" align="left"><b>' . get_string('Equivalent', 'local_courseregistration') . ':</b></td>
                                                                <td valign="top" align="left">' . $equcourses . '</td> 
																
                                                            </tr>
															<tr><td><b>' . get_string('classtype', 'local_clclasses') . ':</b></td><td>' . $classtype . '</td></tr>
                                                        </tbody></table>
                                                    </td>
                                                </tr> ';
    $str .=' <tr style="">';
    $str .=' </tbody></table>
                                        </td>
                                    </tr>
                                </tbody></table>';
    echo $str;
    echo '<br>';
}



    
    
    
    
//    
//    <table cellspacing="0" border="1" style="font-size:10px;width:486px;border-collapse:collapse;" id="GridView1" rules="all">
//						<tbody><tr align="center" style="border-color:#CCCCCC;" class="meetheadrow">
//							<th align="center" scope="col">' . get_string('class_mode', 'local_courseregistration') . '</th><th scope="col">' . get_string('times', 'local_courseregistration') . '</th><th scope="col">' . get_string('bldg', 'local_courseregistration') . '.</th><th scope="col">' . get_string('floor', 'local_courseregistration') . '</th><th scope="col">' . get_string('room', 'local_courseregistration') . '</th>
//						</tr><tr align="center" class="meetrow">
//							<td align="center">
//                                                            <span style="text-decoration:none;" title="classtype">';
//
//    if ($list->type == 1)
//        $str .=get_string('lecture', 'local_courseregistration');
//    if ($list->type == 2)
//        $str .=get_string('equivalent', 'local_courseregistration');
//    $str .=' </span>  </td><td>
//                                                            <div> ' . $timings . '</div><span>' . $availableweekdays . '</span>
//                                                        </td>
//                                                        
//                                                        <td> ' . $building . '</td>
//                                                        <td> ' . $floor . '</td>
//                                                        <td>' . $classroomName . ' </td>
//						</tr>
//					</tbody></table>



echo $OUTPUT->footer();
