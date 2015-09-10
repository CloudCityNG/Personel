<?php
// This file is part of Moodle - http://moodle.org/
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

/*
	pdf for graduation
*/
/**
 * General plugin functions.
 *
 * @package    local
 * @subpackage graduation
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// function print_grades() {
global $CFG,$DB;
require_once ('../../config.php');
require_once($CFG->dirroot.'/local/graduation/lib.php');
require_once($CFG->dirroot.'/local/lib.php'); 
$curid = optional_param ('curid', 0, PARAM_INT);
$userid = optional_param ('userid', 0, PARAM_INT);
$prgid = optional_param ('prgid', 0, PARAM_INT);
$year = optional_param ('year', 0, PARAM_INT);
ob_clean() ;
require_once($CFG->dirroot.'/lib/pdflib.php');

$doc = new pdf;
$doc->setPrintHeader(false);
$doc->setPrintFooter(false);
$doc->AddPage();
$doc->SetFont('times', 'B', 25, '', 'false');
$gradst = $DB->get_record('local_graduation', array('userid'=>$userid, 'curriculumid'=>$curid, 'programid'=>$prgid));
$txt = $DB->get_field('local_school', 'fullname', array('id'=>$gradst->schoolid));
$doc->Write(0, $txt, '', 0, 'C', true, 0, false, false, 20);
$linestyle = array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '2', 'phase' => 0, 'color' => array(0, 0, 0));
$doc->Line(10, 22, 200, 22, $linestyle);
$doc->SetFont('times', 'BI', 14, '', 'false');
$txt = get_string('memo', 'local_graduation');
$doc->Write(12, $txt, '', 0, 'C', true, 0, false, false, 20);
$linestyle = array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '2', 'phase' => 0, 'color' => array(0, 0, 0));
$doc->Line(80, 30, 130, 30, $linestyle);
$doc->SetFont('times', '', 12, '', 'false');
$gradst = $DB->get_record('local_graduation', array('userid'=>$userid, 'curriculumid'=>$curid, 'programid'=>$prgid));
$gradst->userid;$gradst->programid;$gradst->curriculumid;
$table = '<table>
<tr><td style="width:100px;" align="right"><b>Student Name</b></td><td style="width:170px;"> : ' .''.$DB->get_field('user', 'firstname', array('id'=>$gradst->userid)). ' ' . $DB->get_field('user', 'lastname', array('id'=>$gradst->userid)) . '</td>
<td align="right"><b>'.get_string('studentid', 'local_graduation').'</b></td><td> : ' .''.$DB->get_field('local_userdata', 'serviceid', array('userid'=>$gradst->userid)).'</td></tr>
<tr><td align="right"><b>'.get_string('program', 'local_programs').'</b></td><td> : ' .''.$DB->get_field('local_program', 'fullname', array('id'=>$gradst->programid)).'</td>
<td align="right"><b>'.get_string('curriculum', 'local_curriculum').'</b></td><td> : ' .''.$DB->get_field('local_curriculum', 'fullname', array('id'=>$gradst->curriculumid)).'</td></tr>
<tr><td align="right"><b>'.get_string('primaryyear', 'local_admission').'</b></td><td> : ' .''.$gradst->year .'</td>
<td align="right"><b>'.get_string('final_cgpa', 'local_graduation').'</b></td><td> : ' .''.$gradst->finalcgpa .'</td></tr>
</table>';
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '35', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$stu_semsql="SELECT ls.id,ls.fullname,us.userid
				   FROM {local_user_semester} AS us
				   JOIN {local_semester} AS ls
				   ON us.semesterid=ls.id where ls.visible=1 and registrarapproval=1 and us.userid={$userid} group by ls.id";

$student_sems = $DB->get_records_sql($stu_semsql);
foreach ($student_sems as $stusem) {
	$tbl = '<table  cellpadding="2" width="460" border="0.1">';
	$tbl .= '<tr style="width:1200px;" bgcolor="#CAE1FC">';
	$tbl .= '<th style="width:150px;" align="center">'.get_string('course', 'local_cobaltcourses').'</th><th>'.get_string('maxscore', 'local_graduation').'</th><th>'.get_string('score', 'local_gradesubmission').'</th><th>'.get_string('percentage', 'local_graduation').'</th><th>'.get_string('gradeletter', 'local_gradesubmission').'</th><th align="left">'.get_string('wgp','local_graduation').'</th>';
	$tbl .= '</tr>';
	$sname = $DB->get_field('local_semester', 'fullname', array('id'=>$stusem->id));
	$semuserid = $stusem->userid;
	$clclasses = student_clclasses_grad($stusem->id, $semuserid);
	$sem_gpa = $DB->get_field('local_user_sem_details', 'gpa', array('userid'=>$semuserid, 'semesterid'=>$stusem->id));
	if(empty($sem_gpa)){
		$sem_gpa = get_string('none');
	}
  	$stu_status = $DB->get_field('local_user_sem_details', 'studentstatus', array('userid'=>$semuserid, 'semesterid'=>$stusem->id));
 	if($stu_status == 0){
		$status = get_string('good_standing','local_graduation');
	}
	elseif($stu_status == 1){
		$status = get_string('probation','local_graduation');
	}
	elseif($stu_status == 2){
		$status = get_string('academic_dismissal','local_graduation');
	}
	elseif($stu_status == 99){
		$status = "-";
	}
	else{
		$status = "-";
	}
	
	$tbl1="<table width=100% >
			<tr>
			<td align='left'><span><h4><b>".strtoupper(get_string('semester', 'local_semesters'))." : </span>". $sname ."</b></h4></td>
			<td ><span><h4><b>".get_string('gp', 'local_request')." : </span>". $sem_gpa ."</b></h4></td>
			<td align='right'><span><h4><b>".strtoupper(get_string('status', 'local_request'))." : </span>". $status ."</b></h4></td>
			</tr>
			</table>";
	if ($sem_gpa != None)
	$doc->writeHTML($tbl1, true, false, false, false, '');
	$i = 0;
	foreach ($clclasses as $cls) {
		$cocourseid = $DB->get_field('local_clclasses','cobaltcourseid',array('id'=>$cls->classid));
		$cur_pro_sql = "SELECT * FROM {local_curriculum_plancourses} planco 
						JOIN {local_userdata} udat on udat.curriculumid=planco.curriculumid 
						where planco.curriculumid={$curid} and planco.courseid={$cocourseid} and udat.userid={$semuserid}";
		$course_exist_incur = $DB->get_records_sql($cur_pro_sql);
		if($course_exist_incur) {
			$coursename = get_cobalt_course_grad($cls->classid);
			if ($i % 2 == 0)
			$tbl .= '<tr bgcolor="#ffffff">';
			else
			$tbl .= '<tr bgcolor="#F5F5F5">';
			$tbl .= '<td style="width:150px;">'.$coursename.'</td>';
			$maxmarks=$DB->get_field('local_scheduledexams','sum(grademax) AS grademax',array('classid'=>$cls->classid));
			if(empty($maxmarks)) {
				$tbl .= '<td>Exams are not yet scheduled</td>';
			} else {
				$tbl .= '<td>'.$maxmarks.'</td>';
			}
			$mark = $DB->get_record('local_user_classgrades', array('classid' => $cls->classid, 'userid' => $semuserid));
			if(!empty($mark)) {
				$tbl .= '<td>'.$mark->coursetotal .'</td>';
				$tbl .= '<td>'.$mark->percentage .'%'.'</td>';
				$tbl .= '<td>'.$mark->gradeletter .'</td>';
				$wgp=total_grade_points_grad($mark->gradepoint,$mark->classid);
			} else {
			$a = empty($maxmarks)? "-" : " Not yet Graded ";
				$tbl .= '<td>'. $a. '</td>';
				$b = empty($maxmarks)? "-" : "-";
				$tbl .= '<td>'. $b . '</td>';
				$c = empty($maxmarks)? "-" : "-" ;
				$tbl .= '<td>'.$c . '</td>';
				$wgp =empty($maxmarks)?'-':0;
			}
			$tbl .= '<td>'.$wgp.'</td>';
			$tbl .= '</tr>';
		}
		$i++;
	}
	$tbl .= '</table>';
	// $doc->writeHTML($tbl, true, false, false, false, '');
	if ($sem_gpa != None)
	$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $tbl, $border = array('LTRB' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))), $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
	
}
$doc->PageNo();
ob_end_clean();
$downloadfilename = clean_filename("memo.pdf");
$doc->Output($downloadfilename,'I');
