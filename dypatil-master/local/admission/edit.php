<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$id = optional_param('id', 0, PARAM_INT);
$PAGE->requires->js('/local/admission/js/validation.js');
$applicant = $DB->get_record('local_admission', array('id' => $id));
$user = $DB->get_record('local_userdata', array('applicantid' => $id));
$flag = optional_param('flag', 0, PARAM_INT);
$schoolid = optional_param('schoolid', $user->schoolid, PARAM_INT);
$programid = optional_param('programid', $user->programid, PARAM_INT);
$cid = optional_param('cid', $user->curriculumid, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/edit.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('edit_title', 'local_admission'));
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/admissionreport.php'));
$PAGE->navbar->add(get_string('editapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editapplicant', 'local_admission'));
$hierarchy = new hierarchy();
$admission = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('editdesc', 'local_admission'));
}
$user = $DB->get_record('local_userdata', array('applicantid' => $id));
$sql = "SELECT * FROM {local_user_clclasses} WHERE userid={$user->userid} AND registrarapproval=1 ";
$clclasses = $DB->get_records_sql($sql);
$currentyear = date("Y");
$year = date("Y", $applicant->dateofapplication);
if ($year < $currentyear) {
    echo get_string('cant_edit', 'local_admission');
} elseif (!empty($clclasses)) {
    echo get_string('user_cant_edit', 'local_admission');
} else {
    $applicant = $DB->get_record('local_admission', array('id' => $id));
    $school = get_new_schools($id);
    if ($flag == 0) {
        $program = get_new_program($id, $schoolid);
        $cur = get_new_curculum($id, $schoolid, $programid);
    } else {
        $program = get_new_program($id, $schoolid);
        $cur = get_new_curculum($id, $schoolid, $programid);
    }
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/edit.php?id=' . $id . '&flag=1'), 'schoolid', $school, $schoolid, null);
    $select->set_label((get_string('schoolname', 'local_collegestructure')) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    $select->attributes['id'] = 'vij';
    echo $OUTPUT->render($select);
    echo '</div>';
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/edit.php?id=' . $id . '&flag=' . $flag . '&schoolid=' . $schoolid . ''), 'programid', $program, $programid, null);
    $select->set_label(get_string('programname', 'local_programs') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    echo $OUTPUT->render($select);
    echo '</div>';
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/edit.php?id=' . $id . '&flag=' . $flag . '&schoolid=' . $schoolid . '$programid=' . $programid . ''), 'cid', $cur, $cid, null);
    $select->set_label(get_string('curriculum', 'local_curriculum') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    echo $OUTPUT->render($select);
    echo '</div>';
    if ($applicant->typeofapplication == 1) {
        echo '<form action="editdetails.php" method="POST" onSubmit="return check_all()">';
        echo '<input type="hidden" name="schoid" value="' . $schoolid . '">';
        echo '<input type="hidden" name="progid" value="' . $programid . '">';
        echo '<input type="hidden" name="curid" value="' . $cid . '">';
        echo '<input type="hidden" name="appid" value="' . $id . '">';
        echo '<input type="hidden" name="userid" value="' . $user->userid . '">';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    }
}
echo $OUTPUT->footer();
?>