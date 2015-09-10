<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/admission/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/admissionreport.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('admissionreport_title', 'local_admission'));
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('report', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$hierarchy = new hierarchy();
$currenttab = 'report';
$admission = cobalt_admission::get_instance();
$admission->report_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('reportdes', 'local_collegestructure'));
}
$lists = $admission->admission_report();
if (empty($lists)) {
    echo get_string('no_applicants', 'local_admission');
} else {
    $data = array();
    foreach ($lists as $list) {
        $result = array();
        $result[] = $list->firstname . ' ' . $list->lastname;
        $result[] = $list->email;
        $result[] = $DB->get_field('local_school', 'fullname', array('id' => $list->schoolid));
        $result[] = $DB->get_field('local_program', 'fullname', array('id' => $list->programid));
        if ($list->typeofapplication == 1) {
            $listtypeofapplication = get_string('newapp', 'local_admission');
        } elseif ($list->typeofapplication == 2) {
            $listtypeofapplication = get_string('traapp', 'local_admission');
        } else {
            $listtypeofapplication = get_string('readapp', 'local_admission');
        }
        $result[] = $listtypeofapplication;
        if ($list->typeofprogram == 1) {
            $list->typeofprogram = get_string('undergard', 'local_admission');
        } elseif ($list->typeofprogram == 2) {
            $list->typeofprogram = get_string('grad', 'local_admission');
        } else {
            $list->typeofprogram = get_string('postgrad', 'local_admission');
        }
        $result[] = $list->typeofprogram;
        if ($list->typeofstudent == 1) {
            $list->typeofstudent = get_string('localstu', 'local_admission');
        } elseif ($list->typeofstudent == 2) {
            $list->typeofstudent = get_string('interstu', 'local_admission');
        } else {
            $list->typeofstudent = get_string('matstu', 'local_admission');
        }
        $result[] = $list->typeofstudent;
        if ($list->status == 0) {
            $status = get_string('incomplete', 'local_semesters');
        } elseif ($list->status == 1) {
            $status = get_string('approved', 'local_request');
        } else {
            $status = get_string('rejectedc', 'local_request');
        }
        $result[] = $status;
        if ($list->status == 1) {
            if ($list->typeofapplication == 2) {
                $result[] = html_writer::link(new moodle_url('/local/admission/transferedit.php', array('id' => $list->id)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            } else {
                $result[] = html_writer::link(new moodle_url('/local/admission/edit.php', array('id' => $list->id)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }
        } else {
            $result[] = get_string('no');
        }
        $data[] = $result;
    }
    $PAGE->requires->js('/local/admission/js/report.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "report";
    $table->head = array(
        get_string('fullname', 'local_admission'),
        get_string('emailid', 'local_admission'),
        get_string('schoolname', 'local_collegestructure'),
        get_string('programname', 'local_programs'),
        get_string('adtype', 'local_admission'),
        get_string('programtype', 'local_programs'),
        get_string('studenttype', 'local_admission'),
        get_string('status', 'local_admission'),
        get_string('edit', 'local_admission')
    );
    $table->size = array('12%', '12%', '12%', '12%', '12%', '12%', '10%', '10%', '7%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>