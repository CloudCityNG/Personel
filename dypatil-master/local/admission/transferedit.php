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
$schoolid = optional_param('schoolid', 0, PARAM_INT);
$programid = optional_param('programid', 0, PARAM_INT);
if ($flag == 0) {
    $cid = optional_param('cid', $user->curriculumid, PARAM_INT);
} else {
    $cid = optional_param('cid', 0, PARAM_INT);
}
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/transferedit.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('edit_title', 'local_admission'));
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/admissionreport.php'));
$PAGE->navbar->add(get_string('editapplicant', 'local_admission'));
$count = 0;
$previous = $DB->get_records('local_user_classgrades', array('userid' => $user->userid));
foreach ($previous as $prev) {
    $tool->courseid[] = $prev->courseid;
    $tool->grade[] = $prev->coursetotal;
    $count++;
}
$mform = new transfer_applicant_edit(null, array('id' => $id, 'userid' => $user->userid, 'sid' => $schoolid, 'pid' => $programid, 'cid' => $cid, 'count' => $count));
$mform->set_data($tool);
$data = $mform->get_data();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editapplicant', 'local_admission'));
$hierarchy = new hierarchy();
$admission = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('editdesc', 'local_admission'));
}

$user = $DB->get_record('local_userdata', array('applicantid' => $id));
$sql = "SELECT * FROM {local_user_clclasses} WHERE userid={$user->userid} AND programid={$user->programid} AND registrarapproval=1";
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
    $select = new single_select(new moodle_url('/local/admission/transferedit.php?id=' . $id . '&flag=1'), 'schoolid', $school, $schoolid, null);
    $select->set_label(get_string('school', 'local_admission'));
    echo $OUTPUT->render($select);
    echo '</div>';
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/transferedit.php?id=' . $id . '&flag=' . $flag . '&schoolid=' . $schoolid . ''), 'programid', $program, $programid, null);
    $select->set_label(get_string('programname', 'local_programs'));
    echo $OUTPUT->render($select);
    echo '</div>';
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/transferedit.php?id=' . $id . '&flag=' . $flag . '&schoolid=' . $schoolid . '&programid=' . $programid . ''), 'cid', $cur, $cid, null);
    $select->set_label(get_string('curriculum', 'local_curriculum'));
    echo $OUTPUT->render($select);
    echo '</div>';
    $mform->display();
    if ($data) {
        $records = new stdclass();
        $records->id = $DB->get_field('local_userdata', 'id', array('applicantid' => $data->id));
        $records->schoolid = $data->sid;
        $records->programid = $data->pid;
        $records->curriculumid = $data->cid;
        $records->usermodified = $USER->id;
        $records->timecreated = time();
        $records->timemodified = time();
        $DB->update_record('local_userdata', $records);
        $DB->delete_records('local_user_classgrades', array('userid' => $data->userid));
        $DB->delete_records('local_user_clsgrade_hist', array('userid' => $data->userid));
        $grades = transfer_score($data->courseid, $data->grade, $data->id);
    }
    if ($mform->is_cancelled()) {
        $returnurl = new moodle_url('/local/admission/admissionreport.php');
        redirect($returnurl);
    }
}
echo $OUTPUT->footer();
?>