<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE;
require_once($CFG->dirroot . '/local/admission/lib.php');
$PAGE->requires->js('/local/admission/js/validation.js');
$page = optional_param('page', 0, PARAM_INT);
$atype = optional_param('atype', 1, PARAM_INT);
$ptype = optional_param('ptype', 0, PARAM_INT);
$schoolid = optional_param('school', 0, PARAM_INT);
$programid = optional_param('program', 0, PARAM_INT);
$curculumid = optional_param('curculum', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('newapplicant_title', 'local_admission'));
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//if (!has_capability('local/programs:manage', $systemcontext)) {
//   print_cobalterror('permissions_error','local_collegestructure');
//}
$PAGE->set_url('/local/admission/viewapplicant.php');
$returnurl = new moodle_url('/local/admission/viewapplicant.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('manage', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('viewapplicants', 'local_admission'));
$PAGE->requires->css('/local/admission/css/style.css');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$baseurl = new moodle_url('/local/admission/viewapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . '&program=' . $programid . '');
$admission = cobalt_admission::get_instance();
$hierarchy = new hierarchy();
$currenttab = 'viewapplicant';

$level = $admission->pgm;

if (is_siteadmin()) {
    $scho = $hierarchy->get_school_items();
} else {
    $scho = $hierarchy->get_assignedschools();
}
$admission->report_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewapplicantsdes', 'local_collegestructure'));
}
$school = $hierarchy->get_school_parent($scho);

echo '<div class="selfilterposition">';
$select = new single_select(new moodle_url('/local/admission/viewapplicant.php'), 'ptype', $level, $ptype, null);
$select->set_label(get_string('programlevel', 'local_programs'));
echo $OUTPUT->render($select);
echo '</div>';

echo '<div class="selfilterposition">';
$select = new single_select(new moodle_url('/local/admission/viewapplicant.php?ptype=' . $ptype . ''), 'school', $school, $schoolid, null);
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
    $select = new single_select(new moodle_url('/local/admission/viewapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . ''), 'program', $program, $programid, null);
    $select->set_label(get_string('program', 'local_programs'));
    echo $OUTPUT->render($select);
    echo '</div>';
} else {

    $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$schoolid AND visible=1 AND programlevel=$ptype", null, '', 'id,fullname', '--Select--');
    echo '<div class="selfilterposition">';
    $select = new single_select(new moodle_url('/local/admission/viewapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . ''), 'program', $program, $programid, null);
    $select->set_label(get_string('program', 'local_programs'));
    echo $OUTPUT->render($select);
    echo '</div>';
}
echo html_writer::empty_tag('br');
echo '<form action="approveapplicant.php" method="POST" onSubmit="return checklist(' . $schoolid . ',' . $programid . ')">';
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
// title='.get_string('disabledcheck_applicant','local_admission').'
        foreach ($applicant as $app) {
            $user = array();
            /* Bug report #290  -  Admissions>New Applicants>Filters- Modifications
             * @author hemalatha c arun<hemalatha@eabyas.in>
             * Resolved - added condition to enable the checkbox
             */
            if (!empty($ptype) && !empty($schoolid) && !empty($programid))
                $user[] = '<input type="checkbox" name="check_list[]"  value="' . $app->id . '">';
            else
                $user[] = '<input type="checkbox" name="check_list[]" title="Please select program level,school and program filter to enable the  checkbox" disabled = disabled value="' . $app->id . '">';

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
            $user[] = '<p><a href="viewfile.php/?id=' . $app->id . '" >View Files</a></p>';
            $user[] = '<a  href="accept.php?id=' . $app->id . '">Accept</a>';
            $user[] = '<p><a href="reject.php?id=' . $app->id . '&delete=1" >Reject</a></p>';
            $user[] = '<p><a href="contact.php?id=' . $app->id . '" >Contact</a></p>';
            $data[] = $user;
        }
        $table = new html_table();
        $table->head = array(
            get_string('choose', 'local_admission'),
            get_string('name', 'local_admission'),
            get_string('schoolid', 'local_collegestructure'),
            get_string('programname', 'local_programs'),
            get_string('programlevel', 'local_programs'),
            get_string('viewfile', 'local_admission'),
            get_string('accept', 'local_admission'),
            get_string('reject', 'local_admission'),
            get_string('contact', 'local_admission')
        );
        $table->size = array('9%', '9%', '9%', '9%', '9%', '9%', '9%', '9%', '9%');
        $table->align = array('center', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
        echo '<input type="hidden" name="schoolid" value="' . $schoolid . '">';
        echo '<input type="hidden" name="programid" value="' . $programid . '">';
        echo '<input type="hidden" name="atype" value="' . $atype . '">';
        echo '<input type="submit" value="Accept" >';
        echo html_writer::empty_tag('br');
        echo '</form>';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>
