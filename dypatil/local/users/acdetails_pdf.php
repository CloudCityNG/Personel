<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$userid = required_param('id', PARAM_INT);
$schoolid = required_param('sid', PARAM_INT);
$programid = required_param('pid', PARAM_INT);

require_once("$CFG->libdir/pdflib.php");
require_once("lib.php");
$doc = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$doc->setPrintHeader(false);
$doc->setPrintFooter(false);
$doc->Addpage();
$table = '';
$user = $DB->get_record('user', array('id' => $userid));
$userdata = $DB->get_record('local_userdata', array('userid' => $userid, 'schoolid' => $schoolid, 'programid' => $programid));
$school = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
$headers = "<h2 style=\"text-align:center;\">$school</h2>";
$headers .="<h3 style=\"text-align:center;\">" . strtoupper(fullname($user)) . "</h3>";
$myuser = users::getInstance();
$list = $myuser->names($userdata);

$courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $userdata->curriculumid));
$var = false;
foreach ($courses as $course) {
    $status = $myuser->get_coursestatus($course->courseid, $userid);
    if ($status != 'Not Enrolled')
        $var = true;
}
$table = "<table style=\"width:100%;\" cellpadding=\"6\" border=\"1\">
                <tr>";
if ($var)
    $table .= "<th width=\"40%\"><b>" . get_string('courses', 'local_users') . "</b></th><th width=\"30%\" align=\"center\"><b>" . get_string('status', 'local_users') . "</b></th><th width=\"30%\" align=\"center\"><b>" . get_string('semester', 'local_semesters') . "</b></th>";
else
    $table .= "<th width=\"60%\"><b>" . get_string('courses', 'local_users') . "</b></th><th width=\"40%\" align=\"center\"><b>" . get_string('status', 'local_users') . "</b></th>";
$table .= "</tr>";
$course_count = sizeof($courses);
$enrolled = 0;
$completed = 0;
foreach ($courses as $course) {
    $line = $myuser->names($course);
    $status = $myuser->get_coursestatus($course->courseid, $userid);
    $table .= "<tr style=\"font-size:26px;\">
                                            <td><b>" . $line->courseid . "</b>: " . $line->coursename . "</td>
                                            <td align=\"center\">" . $status . "</td>";
    if ($status != 'Not Enrolled') {
        $enrolled++;
        $table .= "<td align=\"center\">" . $myuser->get_coursestatus($course->courseid, $userid, true) . "</td>";
        if ($status != 'Enrolled (Inprogress)')
            $completed++;
    } else {
        $table .= "<td></td>";
    }
    $table .= "</tr>";
}
$table .= "</table>";

$heading = "<table style=\"width:100%;\" cellpadding=\"6\">
                        <tr><td width=\"10%\"><b>" . get_string('serviceid', 'local_users') . "</b></td><td width=\"60%\">: " . $userdata->serviceid . "</td><td colspan=\"2\"></td></tr>
                        <tr><td><b>" . get_string('schoolid', 'local_collegestructure') . "</b></td><td>: " . $list->school . "</td><td width=\"25%\"><b>" . get_string('total_courses', 'local_users') . "</b></td><td width=\"5%\">: " . $course_count . "</td></tr>
                        <tr><td><b>" . get_string('program', 'local_programs') . "</b></td><td>: " . $list->program . "</td><td><b>" . get_string('enrolled', 'local_users') . "</b></td><td>: " . $enrolled . "</td></tr>
                        <tr><td><b>" . get_string('curriculum', 'local_curriculum') . "</b></td><td>: " . $list->curriculum . "</td><td><b>" . get_string('completed', 'local_users') . "</b></td><td>: " . $completed . "</td></tr>
                    </table>";


$footer = "<br/><br/><table style=\"width:80%;\" cellpadding=\"10\"><tr><td><b>" . get_string('signature', 'local_users') . " : </b></td><td align=\"right\"><b>" . get_string('date', 'local_users') . " :</b></td></tr></table>";


$doc->writeHTMLCell($w = 0, $h = 25, $x = '', $y = '', $headers, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 45, $x = '', $y = '', $heading, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $table, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
$doc->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $footer, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

ob_end_clean();

$doc->Output('course_gradereport_pdf.pdf', 'I');
?>