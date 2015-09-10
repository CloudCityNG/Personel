<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
global $DB;
require_once("$CFG->libdir/pdflib.php");
require_once ("../../local/request/lib/lib.php");
require_once('../../message/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$doc = new TCPDF(null, 'cm', 'A4', true, 'UTF-8', false);
$doc->setPrintHeader(false);
$doc->setPrintFooter(false);
$doc->Addpage();
$doc->setPageOrientation($orientation = 'P', $unit = 'cm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false);
$request = new requests();

if ($semid) {
    $studentid = $DB->get_field('local_request_transcript', 'studentid', array('id' => $id));
    $DB->set_field('local_request_transcript', 'reg_approval', 1, array('id' => $id, 'req_semester' => $semid));
    $DB->set_field('local_request_transcript', 'regapproval_date', time(), array('id' => $id, 'req_semester' => $semid));
    $userto = $DB->get_record('user', array('id' => $studentid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $message = get_string('transcriptapproved', 'local_request');
    $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
}

$record = $DB->get_records('local_user_classgrades', array('semesterid' => $semid, 'userid' => $studentid));
$table = "<table style='width:75%;margin:auto' cellpadding=\"5\" border = \"1px\">
<tr><th style=\"text-align:center;\" width=\"40%\"><b>" . get_string("class", "local_clclasses") . "</b></th><th style=\"text-align:center\" width=\"20%\"><b>" . get_string("finalgrade", "local_gradesubmission") . "</b></th><th style=\"text-align:center\" width=\"20%\"><b>" . get_string("wgp", "local_request") . "</b></th><th style=\"text-align:center\" width=\"20%\"><b>" . get_string("credit", "local_request") . "</b></th></tr>";
$gpa = 0;
$totCredithours = 0;
foreach ($record as $records) {

    $studentid = $DB->get_field('local_request_transcript', 'studentid', array('id' => $id));
    $sem = $DB->get_field('local_semester', 'fullname', array('id' => $records->semesterid));
    $name = $DB->get_record_sql("select CONCAT(firstname,' ',lastname) as fullname from {user} where id = $studentid");
    $course = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $records->courseid));
    $credithours = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $records->courseid));

    $sql = "SELECT data.* FROM {local_userdata} AS data
                JOIN {local_curriculum_plancourses} AS plan ON plan.curriculumid = data.curriculumid
                WHERE data.schoolid = {$records->schoolid} AND data.userid = {$studentid} AND plan.courseid = {$records->courseid}";
    $userdata = $DB->get_record_sql($sql);
    $serviceid = $userdata->serviceid;
    $school = $DB->get_field('local_school', 'fullname', array('id' => $records->schoolid));
    $program = $DB->get_field('local_program', 'fullname', array('id' => $userdata->programid));

    $headers = "<h2 style=\"text-align:center;color:#fc4705\">" . strtoupper($school) . "</h2>";

    $headers .='<h5 style="text-align:center;color:#0f44d6">' . strtoupper($sem) . '&nbsp;&nbsp; ' . get_string('stuview', 'local_gradesubmission') . ' </h5>';
    $htable = "<br><br><br><br><table style=\"width:100%;\" cellpadding=\"5\">
    <tr><td><b>" . get_string("student_name", "local_request") . ":</b> $name->fullname</td><td></td></tr>
    <tr><td><b>" . get_string("student_id", "local_request") . ":</b> $serviceid</td><td style=\"text-align:right\"><b>" . get_string('program', 'local_programs') . ":</b> $program</td></tr>";

    $htable .= "</table><br><br>";


    $table .= "<tr><td style=\"text-align:center\">$course</td><td style=\"text-align:center\">$records->gradeletter</td>";

    $table .= "<td style=\"text-align:center\">" . $records->gradepoint * $credithours . "</td>";

    $gpa = $gpa + ($records->gradepoint * $credithours);
    $table .= "<td style=\"text-align:center\">$credithours</td></tr>";
    $totCredithours = $totCredithours + $credithours;
}
$table .= "<tr ><td colspan=\"2\" style=\"text-align:right\"><b>Total</b></td><td style=\"text-align:center\"><b>$gpa</b></td><td style=\"text-align:center\"><b>$totCredithours</b></td></tr>";

$table .= "</table>";
$table1 .= "<br><br><table width=\"100%\"  >
<tr><td colspan=\"7\" style=\"text-align:right\"><b><span>" . get_string("gp", "local_request") . ":</span></b></td><td><b>         " . round($gpa / $totCredithours, 2) . "</b></td></tr>    
</table>";
$footer = "<br/><br/><br/><table style=\"width:100%;\" cellpadding=\"10\"><tr><td><b>" . get_string("officer", "local_request") . " :</b></td>";
$footer .= "<td><b>" . get_string("sign", "local_request") . " :</b></td>";
$footer .="<td><b>" . get_string("Date", "local_request") . " :</b></td></tr></table><br><br><br><br><br><br>";
$footer .="<table width=\"55%\">
                          <tr><td width=\"15%\" style=\"font-size:250px\"><b>" . get_string("gradepoint", "local_request") . "</b></td><td style=\"font-size:220px\"> " . get_string("gradepoints", "local_request") . " </td></tr>
                          <tr><td width=\"15%\" style=\"font-size:250px\"><b>" . get_string("gp", "local_request") . "</b></td><td style=\"font-size:220px\"> " . get_string("gps", "local_request") . " </td></tr>
                          </table>";
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $headers, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htable, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table1, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $footer, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

ob_end_clean();

$doc->Output($sem . '_report.pdf', 'I');
?>
