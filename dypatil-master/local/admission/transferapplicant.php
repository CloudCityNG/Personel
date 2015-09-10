<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE;
require_once($CFG->dirroot . '/local/admission/lib.php');
$PAGE->requires->js('/local/admission/js/validation.js');
$page = optional_param('page', 0, PARAM_INT);
$atype = optional_param('atype', 2, PARAM_INT);
$ptype = optional_param('ptype', 0, PARAM_INT);
$schoolid = optional_param('school', 0, PARAM_INT);
$programid = optional_param('program', 0, PARAM_INT);
$systemcontext =context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('transferapplicant_title', 'local_admission'));
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/programs:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/admission/transferapplicant.php');
$returnurl = new moodle_url('/local/admission/transferapplicant.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/transferapplicant.php'));
$PAGE->navbar->add(get_string('transferapplicant', 'local_admission'));
$PAGE->requires->css('/local/admission/css/style.css');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$baseurl = new moodle_url('/local/admission/transferapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . '&program=' . $programid . '');
$admission = cobalt_admission::get_instance();
$hierarchy = new hierarchy();
$currenttab = 'transferapplicant';
$admission->report_tabs($currenttab);
$level = $admission->ptype;

if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('transferapplicantdes', 'local_admission'));
}
if (is_siteadmin()) {
    $scho = $hierarchy->get_school_items();
} else {
    $scho = $hierarchy->get_assignedschools();
}
$school = $hierarchy->get_school_parent($scho);
echo '<div class="selfilterposition">';
$select = new single_select(new moodle_url('/local/admission/transferapplicant.php'), 'ptype', $level, $ptype, null);
$select->set_label(get_string('programtype', 'local_programs'));
echo $OUTPUT->render($select);
echo '</div>';
echo '<div class="selfilterposition">';
$select = new single_select(new moodle_url('/local/admission/transferapplicant.php?ptype=' . $ptype . ''), 'school', $school, $schoolid, null);
$select->set_label(get_string('schoolid', 'local_collegestructure'));
echo $OUTPUT->render($select);
echo '</div>';
if ($schoolid == 0) {
    if (is_siteadmin()) {
        $program = $hierarchy->get_records_cobaltselect_menu('local_program', 'visible=1', null, '', 'id,fullname', '--Select--');
    } else {
        $program = $admission->get_pgrms();
    }
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/transferapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . ''), 'program', $program, $programid, null);
    $select->set_label(get_string('program', 'local_programs'));
    echo $OUTPUT->render($select);
    echo '</div>';
} else {
    $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$schoolid AND visible=1", null, '', 'id,fullname', '--Select--');
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/transferapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . ''), 'program', $program, $programid, null);
    $select->set_label(get_string('program', 'local_programs'));
    echo $OUTPUT->render($select);
    echo '</div>';
}
echo html_writer::empty_tag('br');
$sql = $admission->cobalt_admission_applicant($atype, $ptype, $schoolid, $programid);
$applicants = $DB->get_records_sql($sql);
$totalcount = count($applicants);
$perpage = 10;
$list = $page * $perpage;
$applicant = $DB->get_records_sql('' . $sql . ' LIMIT ' . $list . ',' . $perpage . '');
$data = array();
try {
    if (empty($applicant)) {
        $e = get_string('no_applicants', 'local_admission');
        throw new Exception($e);
    } else {
        foreach ($applicant as $app) {
            $user = array();
            $user[] = html_writer::tag('a', $app->firstname . " " . $app->lastname, array('href' => '' . $CFG->wwwroot . '/local/admission/view.php?id=' . $app->id . ''));
            $user[] = $DB->get_field('local_school', 'fullname', array('id' => $app->schoolid));
            $user[] = $DB->get_field('local_program', 'fullname', array('id' => $app->programid));

            if ($app->typeofprogram == 1) {
                $app->typeofprogram = get_string('undergard', 'local_admission');
            } elseif ($app->typeofprogram == 2) {
                $app->typeofprogram = get_string('grad', 'local_admission');
            } else {
                $app->typeofprogram = get_string('postgrad', 'local_admission');
            }
            $user[] = $app->typeofprogram;
            $user[] = '<p><a href="viewfile.php/?id=' . $app->id . '" >' . get_string('viewfile', 'local_admission') . '</a></p>';
            $user[] = '<a  href="step1.php?id=' . $app->id . '">' . get_string('accept', 'local_admission') . '</a>';
            $user[] = '<p><a href="transferreject.php?id=' . $app->id . '&delete=1" >' . get_string('reject', 'local_admission') . '</a></p>';
            $user[] = '<p><a href="contact.php?id=' . $app->id . '" >' . get_string('contact', 'local_admission') . '</a></p>';
            $data[] = $user;
        }
        $table = new html_table();
        $table->head = array(
            get_string('name', 'local_admission'),
            get_string('schoolname', 'local_collegestructure'),
            get_string('program', 'local_programs'),
            get_string('programtype', 'local_programs'),
            get_string('viewfile', 'local_admission'),
            get_string('accept', 'local_admission'),
            get_string('reject', 'local_admission'),
            get_string('contact', 'local_admission')
        );
        $table->size = array('9%', '9%', '9%', '9%', '9%', '9%', '9%', '9%');
        $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>