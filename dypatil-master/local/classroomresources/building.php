<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/resource_form.php');
$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$linkschool = optional_param('linkschool', 0, PARAM_INT);
$hierarchy = new hierarchy();


//error handling 
$hierarchy->get_school_items();

$conf = new object();
$currenttab = 'create';
$systemcontext = context_system::instance();
if ($id > 0) {

    if (!($tool = $DB->get_record('local_building', array('id' => $id)))) {
        throw new exception(get_string('invalidtoolid', 'local_building'));
    } else {
        $tool->schoolid = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
        $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/classroomresources:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->set_url('/local/classroomresources/building.php', array('id' => $id));
$returnurl = new moodle_url('/local/classroomresources/index.php', array('id' => $id));
$strheading = get_string('managebuildings', 'local_classroomresources');
// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $conf->building = $DB->get_field('local_building', 'fullname', array('id' => $id));
        $resource = cobalt_resources::get_instance();
        $deletebuilding = $resource->cobalt_building_delete($id);
        if ($deletebuilding) {
            $message = get_string('deletesuccess', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('deletefailure', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deletebuildings', 'local_classroomresources');
    $PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/index.php'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
    $resource = cobalt_resources::get_instance();
    // $resource->building_tabs($currenttab, $id);
    echo $OUTPUT->heading($strheading);
    $depend = check_building($id);
    if ($depend > 0) {
        echo '<div align="center">';
        $message = get_string('buildingcheck', 'local_classroomresources');
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        $yesurl = new moodle_url('/local/classroomresources/building.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('deletebuilding', 'local_classroomresources');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
//Make the buildings to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {

    $value = $DB->set_field('local_building', 'visible', $visible, array('id' => $id));
    $DB->set_field('local_floor', 'visible', $visible, array('buildingid' => $id));
    $DB->set_field('local_classroom', 'visible', $visible, array('buildingid' => $id));
    $DB->set_field('local_classroomresources', 'visible', $visible, array('buildingid' => $id));
    $data->building = $DB->get_field('local_building', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_building', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($value) {
        $message = get_string('successb', 'local_classroomresources', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_classroomresources');
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$heading = ($id > 0) ? get_string('editbuilding', 'local_classroomresources') : get_string('createbuilding', 'local_classroomresources');
$boxcontent = ($id > 0) ? get_string('editdescription', 'local_classroomresources') : get_string('buildingdescription', 'local_classroomresources');
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/index.php'));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editform = new createbuilding_form(null, array('id' => $id));

$editform->set_data($tool);
// enabling school and building name while linking from other plugin
if ($linkschool) {
    $tool->schoolid = $linkschool;
    $editform->set_data($tool);
}
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $conf->building = $data->fullname;
    if ($data->id > 0) {
        $data->description = $data->description['text'];
        $data->schoolid = $DB->get_field('local_building', 'schoolid', array('id' => $data->id));
        $value = $DB->update_record('local_building', $data);
        if ($value) {
            $message = get_string('updatesuccess', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('updatefailure', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    } else {
        $data->description = $data->description['text'];
        $value = $DB->insert_record('local_building', $data);
        if ($value) {
            $message = get_string('createsuccess', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('createfailure', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$resource = cobalt_resources::get_instance();
$resource->building_tabs($currenttab, $id);
//echo $OUTPUT->heading($heading);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box($boxcontent);
}
try {
    if (is_siteadmin()) {
        $scho = $hierarchy->get_school_items();
    } else {
        $scho = $hierarchy->get_assignedschools();
    }
    $count = count($scho);
    if ($count > 0) {
        $editform->display();
    } else {
        $e = get_string('notassignedschool', 'local_collegestructure');
        throw new Exception($e);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>
