<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/cobaltcourses/lib.php');
require_once($CFG->dirroot . '/local/cobaltcourses/prerequisite_form.php');
require_once($CFG->dirroot . '/local/lib.php');
global $USER, $DB, $CFG;
$id = optional_param('id', -1, PARAM_INT);  //course equivalent id
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$cid = optional_param('cid', 0, PARAM_INT);
$did = optional_param('did', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 5;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$prerequisite_cap = array('local/cobaltcourses:manage', 'local/cobaltcourses:courseprerequisiteassign');
if (!has_any_capability($prerequisite_cap, $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . get_string('precourse', 'local_cobaltcourses'));
$PAGE->set_heading(get_string('cobaltcourses', 'local_cobaltcourses'));
$PAGE->set_url('/local/cobaltcourses/prerequisite.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url($CFG->wwwroot . '/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('precourse', 'local_cobaltcourses'));
$returnurl = new moodle_url('/local/cobaltcourses/prerequisite.php', array('id' => $id));
echo $OUTPUT->header();
/* ---allow this page only if the registrar is assigned to any of the schools--- */
$hierarchy = new hierarchy();
$schoollist = $hierarchy->get_assignedschools();
if (is_siteadmin())
    $schoollist = $hierarchy->get_school_items();
$count = count($schoollist);
/* ---Count of schools to which registrar is assigned--- */
if ($count < 1) {
    throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
}
$parent = $hierarchy->get_school_parent($schoollist, '', false, false);
$schoolvalues = array();
foreach ($parent as $k => $p) {
    $schoolvalues[$k] = $k;
}
list($usql, $params) = $DB->get_in_or_equal($schoolvalues);

$heading = get_string('courseprerequisite', 'local_cobaltcourses');

echo $OUTPUT->heading(get_string('pluginname', 'local_cobaltcourses'));
/* ---tab view for this page--- */
$currenttab = 'prerequisite';
if ($delete == 0) {
    createtabview($currenttab);
}
/* ---delete the course equivalent record if the parameter delete is set--- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    $systemcontext = context_system::instance();
    $prerequisite_cap = array('local/cobaltcourses:manage', 'local/cobaltcourses:courseprerequisiteunassign');
    if (!has_any_capability($prerequisite_cap, $systemcontext)) {
        print_error('You dont have permissions');
    }
    if ($confirm and confirm_sesskey()) {
        $conf = $DB->get_record('local_course_prerequisite', array('id' => $id));
        $course = $DB->get_record('local_cobaltcourses', array('id' => $conf->courseid));
        if (delete_prerequisitecourse($id)) {
            $message = get_string('deleteprerequisitesuccess', 'local_cobaltcourses', $course);
            $style = array('style' => 'notifysuccess');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deleteprerequisite', 'local_cobaltcourses');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . $strheading);
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/cobaltcourses/prerequisite.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('deleteprerequisiteconfirm', 'local_cobaltcourses');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses'));

/* ---Object for the form--- */
$prerequisite = new prerequisite_form(null, array('id' => $id));
/* ---to create a new prerequisite course--- */
$record = new stdClass();
$record->id = -1;
$prerequisite->set_data($record);

if ($prerequisite->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $prerequisite->get_data()) {
    /* ---Add new record--- */
    $data->usermodified = $USER->id;
    $data->timecreated = time();

    $eqs = $data->precourseid;
    foreach ($eqs as $eq) {
        $data->precourseid = $eq;
        insert_prerequisitecourse($data);
    }

    $style = array('style' => 'notifysuccess');
    $conf = $DB->get_record('local_cobaltcourses', array('id' => $data->courseid));
    $message = get_string('insertprerequisitesuccess', 'local_cobaltcourses', $conf);
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

/* ---Description for the page--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('courseprerequisitepage', 'local_cobaltcourses'));
}
/* ---display the form--- */
$prerequisite->display();
/* ---display the details of the assigned schools only--- */
$totalcount = sizeof($DB->get_records_sql("SELECT DISTINCT(courseid) FROM {local_course_prerequisite} WHERE schoolid $usql", $params));
$spage = $page * $perpage;
$sql = "SELECT *, count(id) AS count FROM {local_course_prerequisite} WHERE schoolid $usql ";
if ($cid) {
    $sql .= "AND courseid = ? ";
    $params = array_merge($params, array($cid));
}
if ($did) {
    $sql .= "AND departmentid = ? ";
    $params = array_merge($params, array($did));
}
$sql .= " GROUP BY courseid LIMIT $spage, $perpage ";
$records = $DB->get_records_sql($sql, $params);

$data = array();
foreach ($records as $record) {
    $span = $record->count + 1;
    $line = array();
    $cell1 = new html_table_cell();
    $cell2 = new html_table_cell();
    $cell3 = new html_table_cell();

    $cell1->text = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $record->courseid));
    $cell1->rowspan = $span;
    $cell1->style = 'vertical-align:middle;';

    $cell2->text = $DB->get_field('local_department', 'fullname', array('id' => $record->departmentid));
    $cell2->rowspan = $span;
    $cell2->style = 'vertical-align:middle;';

    $cell3->text = $DB->get_field('local_school', 'fullname', array('id' => $record->schoolid));
    $cell3->rowspan = $span;
    $cell3->style = 'vertical-align:middle;';

    $line[] = $cell1;
    $line[] = $cell2;
    $line[] = $cell3;
    $data[] = $line;

    $eques = $DB->get_records('local_course_prerequisite', array('courseid' => $record->courseid));
    foreach ($eques as $eq) {
        $line = array();
        $cell4 = new html_table_cell();
        $cell5 = new html_table_cell();

        $cell4->text = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $eq->precourseid)) . " (" . $DB->get_field('local_department', 'fullname', array('id' => $eq->predeptid)) . ")";
        $cell5->text = html_writer::link(new moodle_url('/local/cobaltcourses/prerequisite.php', array('id' => $eq->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
        $line[] = $cell4;
        $line[] = $cell5;
        $data[] = $line;
    }
}
if (!empty($records)) {
    $c = coursefilter($schoolvalues, 'local_course_prerequisite');
    echo '<div style="float: left;width:100%;">';
    echo '<div style="margin-bottom: 10px;float: left;width:49%;">';
    $select1 = new single_select(new moodle_url('/local/cobaltcourses/prerequisite.php?did=' . $did . ''), 'cid', $c, $cid, null, 'switchcategory');
    $select1->set_label(get_string('selectcourse', 'local_cobaltcourses') . ':&nbsp&nbsp&nbsp&nbsp');
    echo $OUTPUT->render($select1);
    echo '</div>';

    $d = departmentfilter($schoolvalues, 'local_course_prerequisite');
    echo '<div style="margin-bottom: 10px;float: right;width:49%;">';
    $select2 = new single_select(new moodle_url('/local/cobaltcourses/prerequisite.php?cid=' . $cid . ''), 'did', $d, $did, null, 'switchcategory');
    $select2->set_label(get_string('selectdepartment', 'local_cobaltcourses') . ':&nbsp&nbsp&nbsp&nbsp');
    echo $OUTPUT->render($select2);
    echo '</div>';
    echo '</div>';
}
/* ---View Part starts--- */
/* ---start the table--- */
$table = new html_table();
$table->id = "equivalenttable";
$head = array();
$head[] = get_string('course', 'local_cobaltcourses');
$head[] = get_string('department', 'local_cobaltcourses');
$head[] = get_string('schoolid', 'local_collegestructure');
$head[] = get_string('precourse', 'local_cobaltcourses');
$head[] = get_string('unassign', 'local_curriculum');
$table->head = $head;
$table->size = array('25%', '20%', '20%', '25%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;
/* ---Display the table--- */
echo html_writer::table($table);
echo $OUTPUT->footer();
