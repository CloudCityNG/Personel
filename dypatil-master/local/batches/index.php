<?php

// This file is part of Moodle - http://moodle.org/
//
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

/**
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage batches
 * @copyright  2014 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG, $PAGE;
require($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/local/batches/batch_form.php');
require_once($CFG->dirroot . '/local/batches/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
//require_once($CFG->dirroot . '/local/costcenter/lib.php');

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$userid = optional_param('userid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

if ($id) {
    $cohort = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
    $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
    $batchmapinfo = $DB->get_record('local_batch_map', array('batchid' => $cohort->id));
    //$cohort->costcenter_name = $DB->get_field('local_costcenter', 'fullname', array('id'=>$costcenter->costcenterid));
} else {
    $context = context_system::instance();
    $cohort = new stdClass();
    $cohort->id = 0;
    $cohort->contextid = $context->id;
    $cohort->name = '';
    $cohort->description = '';
}
$returnurl = new moodle_url('/local/batches/index.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
//$PAGE->requires->js('/blocks/learning_plan/js/jquery.dataTables.js');
$PAGE->requires->js('/local/batches/js/batches.js');
//$PAGE->requires->css('/blocks/learning_plan/css/jquery.dataTables.css');
$PAGE->requires->js('/local/batches/js/select2.full.js');
$PAGE->requires->css('/local/batches/css/select2.min.css');
$PAGE->requires->js('/local/batches/js/delete.js');
//$PAGE->requires->js('/local/costcenter/js/cofilter.js');
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($context);

require_login();
$PAGE->set_url('/local/batches/index.php');
$PAGE->set_title(get_string('pluginname', 'local_batches'));
//Header and the navigation bar
$PAGE->set_heading(get_string('pluginname', 'local_batches'));
$PAGE->requires->css('/local/batches/css/style.css');
$renderer = $PAGE->get_renderer('local_batches');

$batches = new local_batches($id);
$editoroptions = array('maxfiles' => 0, 'context' => $context);
if ($cohort->id) {
    // Edit existing.
    $cohort = file_prepare_standard_editor($cohort, 'description', $editoroptions, $context);
    $form_heading = get_string('editbatch', 'local_batches');
    $collapse = false;
} else {
    // Add new.
    $cohort = file_prepare_standard_editor($cohort, 'description', $editoroptions, $context);
    $form_heading = get_string('addbatch', 'local_batches');
    $collapse = true;
}

if ($unassign && $cohort->id) {
    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {
        if ($userid) {
            cohort_remove_member($cohort->id, $userid);
            $batchmapinfo = $DB->get_record('local_batch_map', array('batchid' => $cohort->id));
            $DB->set_field('local_userdata', 'batchid', 0, array('userid' => $userid, 'schoolid' => $batchmapinfo->schoolid, 'programid' => $batchmapinfo->programid));
        }
        redirect($returnurl);
    }
    $params = array('id' => $cohort->id, 'unassign' => 1, 'confirm' => 1, 'sesskey' => sesskey());
    if ($userid) {
        $strheading = get_string('unassignuser', 'local_batches');
        $message = get_string('unassignuserconfirmation', 'local_batches');
        $params['userid'] = $userid;
    }

    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($cohort->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $yesurl = new moodle_url('/local/batches/index.php', $params);
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}


if ($delete and $cohort->id) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $batches->delete();
        cohort_delete_cohort($cohort);
        redirect($returnurl);
    }
    $strheading = get_string('delcohort', 'local_batches');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($cohort->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/batches/index.php', array('id' => $cohort->id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirm', 'local_batches', format_string($cohort->name));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

// ---------------(assign existing user to batch )studen can be assigned multiple batches of different program---------------
if ($data = data_submitted()) {
   $batches->assign_existing_userto_batches($data);
}

$editform = new local_batches_edit_form(null, array('editoroptions' => $editoroptions, 'data' => $cohort));

if ($id > 0) {
    $cohort->schoolid = $batchmapinfo->schoolid;
    $cohort->programid = $batchmapinfo->programid;
    $cohort->curriculumid = $batchmapinfo->curriculumid;
    $cohort->academicyear = $batchmapinfo->academicyear;
    $editform->set_data($cohort);
}


if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $context);

    if ($data->id) {

        cohort_update_cohort($data);
        $batchmap = $DB->get_record('local_batch_map', array('batchid' => $data->id));
        $updatetemp = new stdclass();
        $updatetemp->id = $batchmap->id;
        $updatetemp->schoolid = $data->schoolid;
        $updatetemp->programid = $data->programid;
        $updatetemp->curriculumid = $data->curriculumid;
        $updatetemp->academicyear = $data->academicyear;
        $updatetemp->usermodified = $USER->id;
        $updatetemp->timemodified = time();
        $DB->update_record('local_batch_map', $updatetemp);
        $data->id;
    } else {

        $data->batchid = cohort_add_cohort($data);
        $batches->local_batches_add_map($data);
    }

    // Use new context id, it could have been changed.
    redirect(new moodle_url('/local/batches/index.php'));
}


if ($editform->is_submitted() && !$editform->is_validated())
    $collapse = false;

//$batches = new local_batches();
//$PAGE->navbar->add(get_string('pluginname', 'local_batches'));
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_batches'), '2', 'tmhead2');

print_collapsible_region_start('', 'batches-form', '<button>' . $form_heading . '</button>', false, $collapse);
$editform->display();
print_collapsible_region_end();


if (is_siteadmin()) {
    $school = $hierarchy->get_school_items();
    $sql = "SELECT c.* FROM {cohort} as c
            JOIN {local_batch_map} as map on map.batchid=c.id WHERE contextid = :context";
} else {
    $schools = $hierarchy->get_assignedschools();
    foreach ($schools as $school) {
        $school_id[] = $school->id;
    }
    $schoolids = implode(',', $school_id);

    $sql = "SELECT c.* FROM {cohort} as c
            JOIN {local_batch_map} as map on map.batchid=c.id WHERE contextid = :context 
            and (map.schoolid in ($school_id) or map.schoolid=0 ";
}

//if(!empty($assigned)){
//    $in = implode(', ', array_keys($assigned));
//    $sql .= " AND id IN (select batchid from {local_costcenter_batch} where costcenterid in ($in))";
//}
$sql .= " ORDER BY name ASC, idnumber ASC";
$cohorts = $DB->get_records_sql($sql, array('context' => $context->id));


//print_object($cohorts);

$renderer->display_view($cohorts, $context);

echo $OUTPUT->footer();
