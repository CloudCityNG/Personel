<script>
    function fnexpandcollapse(classid, path) {
        $(".body_" + classid).toggle(function () {
            if ($(".body_" + classid).is(":visible")) {
                $(".heading_" + classid + " img.smallimg").attr("src", path + "/local/myacademics/pix/expanded.svg");
            }
            if ($(".body_" + classid).is(":hidden")) {
                $(".heading_" + classid + " img.smallimg").attr("src", path + "/local/myacademics/pix/collapsed.svg");
            }
        });
    }
</script>
<style>
    .studentheader{
        padding: 3px;
        background-color:#F9F9F9;
        margin-top: 2px;
        cursor:pointer;
    }
    .studentbody{
        margin-bottom: 3px;
        padding: 10px;
    }
</style>
<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/myacademics/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
$totalgradepoints = optional_param('totalgradepoints', 0, PARAM_INT);
$totalcredits = optional_param('totalcredits', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/myacademics/transcript.php');
$PAGE->set_pagelayout('admin');
$conf = new object();
$exams = new schedule_exam();
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_myacademics'));

$PAGE->navbar->add(get_string('viewtranscript', 'local_myacademics'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('myacademics', 'local_courseregistration'));
$currenttab = 'mytranscript';
$help = $OUTPUT->help_icon('wgp', 'local_myacademics', get_string('file', 'local_admission'));
$helps = $OUTPUT->help_icon('gpa', 'local_myacademics', get_string('file', 'local_admission'));
$exams->studentside_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('transcriptdesc', 'local_myacademics'));
}
$returnurl = new moodle_url('/local/myacademics/transcript.php');
$curculumid = $DB->get_field('local_userdata', 'curriculumid', array('userid' => $USER->id));
$sem = student_enrolled_semesters($USER->id);
if ($semid == 0) {
    $today = date('Y-m-d');
    $sql = "SELECT ls.id,ls.fullname,FROM_UNIXTIME(ls.startdate,'%Y-%m-%d') as startdate,
	      FROM_UNIXTIME(ls.enddate,'%Y-%m-%d') as enddate 
	      FROM {local_user_semester} AS us,{local_semester} AS ls 
		  WHERE us.userid={$USER->id} AND 
		  us.registrarapproval=1 AND 
		  us.semesterid=ls.id AND 
		  ls.visible=1 ";
    $semesters = $DB->get_records_sql($sql);
    foreach ($semesters as $semester) {
        if ($semester->startdate <= $today && $semester->enddate >= $today) {
            $semid = $semester->id;
        }
    }
}
try {
    if (count($sem) > 1) {
        echo '<div class="selfilterposition" style="text-align:center;margin:20px;">';
        $select = new single_select(new moodle_url('/local/myacademics/transcript.php'), 'semid', $sem, $semid, null);
        $select->set_label(get_string('semestertranscript', 'local_semesters'));
        echo $OUTPUT->render($select);
        echo '</div>';
        if ($semid > 0) {
            $grades = student_academic_grades($semid);
            if (empty($grades)) {
                echo get_string('nogrades', 'local_myacademics');
            } else {
                $data = array();
                echo '<table style="font-size:11px;text-align:center;border-collapse: separate; border-spacing: 0px 5px;width:100% !important;" class="generaltable" id="transcriptbk">';
                echo "<thead><tr style='text-align:left;' >
<th style='width:20%;
padding:5px;' class='header c0'>Course</th>
<th class='header c1'>Class Name</th>
<th class='header c2' style='width:10%'>Max.Score</th>
<th class='header c3'>Score</th>
<th class='header c4'>Percentage</th>
<th class='header c5' style='width:13%;'>Grade Letter</th>
<th class='header c6'>W.G.P</th>
</tr></thead>";
                foreach ($grades as $grade) {

                    $coursename = $DB->get_field_sql('SELECT lcc.fullname FROM {local_clclasses} AS lc,{local_cobaltcourses} AS lcc
	                                                                            WHERE lc.cobaltcourseid=lcc.id AND lc.id=' . $grade->classid . '');
                    $cobid = $DB->get_field_sql('SELECT lcc.id FROM {local_clclasses} AS lc,{local_cobaltcourses} AS lcc
	                                                                            WHERE lc.cobaltcourseid=lcc.id AND lc.id=' . $grade->classid . '');
                    $course_exist = $DB->get_record('local_curriculum_plancourses', array('curriculumid' => $curculumid, 'courseid' => $cobid));
                    if (empty($course_exist))
                        $star = '<b style="color:red;">*</b>';
                    else
                        $star = '';
                    $marks = $DB->get_field('local_scheduledexams', 'sum(grademax) AS grademax', array('classid' => $grade->classid));
                    $mark = $DB->get_record('local_user_classgrades', array('classid' => $grade->classid, 'userid' => $USER->id));
                    $classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $grade->classid));
                    echo "<tr  class='studentheader heading_" . $grade->classid . "' onClick='fnexpandcollapse(" . $grade->classid . ", \"$CFG->wwwroot\")' style='height:40px;'>";
                    echo "<td>$coursename $star</td>";
                    echo "<td>$classname</td>";
                    if (empty($marks)) {
                        echo "<td>" . get_string('no_exams', 'local_myacademics') . "</td>";
                    } else {
                        echo "<td>$marks</td>";
                    }
                    if (!empty($mark)) {
                        echo "<td>$mark->coursetotal</td>";
                        echo "<td>$mark->percentage</td>";
                        echo "<td>$mark->gradeletter</td>";
                        $wgp = total_grade_points($mark->gradepoint, $mark->classid);
                    } else {
                        if (empty($marks)) {
                            $a = '-';
                        } else {
                            $a = 'Not Graded';
                        }
                        echo "<td>$a</td>";
                        echo "<td>$a</td>";
                        echo "<td>$a</td>";
                        $wgp = empty($marks) ? '-' : 0;
                    }
                    echo "<td>$wgp</td>";
                    $totalgradepoints = $totalgradepoints + $wgp;
                    $credits = total_grade_credits($grade->classid);
                    $totalcredits = $totalcredits + $credits;
                    echo '</tr>';
                    echo '<tr class="studentbody body_' . $grade->classid . '" style="display: none;" ><td colspan=7>';
                    $examsql = "SELECT ue.finalgrade,ue.examid,se.examtype,FROM_UNIXTIME(se.opendate,'%D %M, %Y') as examdate,se.grademax FROM {local_user_examgrades} ue,{local_scheduledexams} se WHERE ue.userid={$USER->id} AND ue.classid={$grade->classid} AND ue.semesterid={$semid} AND ue.examid=se.id";
                    $examqry = $DB->get_records_sql($examsql);
                    if (empty($examqry)) {
                        echo "No Exams scheduled for you!";
                    } else {
                        echo '<table style="width:85%;margin-left:10%;border:1px dottod #000;" class="generaltable">';
                        echo '<thead style="background:#0EABB7;"><tr style="text-align:left;">
<th>Exam Name</th>
<th>Exam Date</th>
<th>Max.Score</th>
<th>Score</th>
</tr></thead>';
                        foreach ($examqry as $exam) {
                            $a = $DB->get_field('local_examtypes', 'examtype', array('id' => $exam->examtype));
                            echo '<tr style="background-color: #ddd;">';
                            echo '<td style="border-bottom:0px !important;font-size:11px;">' . $a . '</td>';
                            echo '<td style="border-bottom:0px !important;font-size:11px;">' . $exam->examdate . '</td>';
                            echo '<td style="border-bottom:0px !important;font-size:11px;">' . $exam->grademax . '</td>';
                            echo '<td style="border-bottom:0px !important;font-size:11px;">' . $exam->finalgrade . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    echo '</td></tr>';
                }
                echo '</table>';
                $gpa = round($totalgradepoints) / round($totalcredits);
                $conf->gpa = sprintf("%.2f", $gpa);
                if ($conf->gpa != null) {
                    $conf->help = $helps;
                    echo get_string('gpa', 'local_request', $conf);
                }
            }
        }
    } else {
        $e = get_string('nosems', 'local_myacademics');
        throw new Exception($e);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo '<b>Note:</b> <span> <label style="color:red;">*</label> Indicates that the course is not available in your Curriculum.These courses are not calculated for the program graduation. </span>';
echo $OUTPUT->footer();
?>