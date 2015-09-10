<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/resource_form.php');
$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$hierarchy = new hierarchy();
$currenttab = 'creater';
$systemcontext = context_system::instance();
if ($id > 0) {

    if (!($tool = $DB->get_record('local_classroomresources', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_classroomresources');
    } else {
        $tool->schoolid = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
        $tool->buildingid = $DB->get_field('local_building', 'fullname', array('id' => $tool->buildingid));
        $tool->floorid = $DB->get_field('local_floor', 'fullname', array('id' => $tool->floorid));
        $tool->classroomid = $DB->get_field('local_classroom', 'fullname', array('id' => $tool->classroomid));
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
$PAGE->set_url('/local/classroomresources/assignresource.php', array('id' => $id));
$returnurl = new moodle_url('/local/classroomresources/view.php', array('id' => $id));
$strheading = get_string('manageresources', 'local_classroomresources');
// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $resource = cobalt_resources::get_instance();
        $deletelist = $resource->cobalt_delete_list($id);
        if ($deletelist) {
            $message = get_string('assignresourcedelete', 'local_classroomresources');
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('assignresourcedeletefail', 'local_classroomresources');
            $style = array('style' => 'notifyproblem');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deleteassigned', 'local_classroomresources');
    $PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/view.php'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
    $resource = cobalt_resources::get_instance();
    //$resource->assignresource_tabs($currenttab);
    $resource->resource_tabs($currenttab, $id);
    //echo $OUTPUT->heading($strheading);
    $depend = check_resource($id);
    if ($depend > 0) {
        echo '<div align="center">';
        $message = get_string('resourcecheck', 'local_classroomresources');
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        $yesurl = new moodle_url('/local/classroomresources/assignresource.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('deletebuilding', 'local_classroomresources');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
//Make the buildings to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    $value = $DB->set_field('local_classroomresources', 'visible', $visible, array('id' => $id));
    if ($value) {
        $message = get_string('success', 'local_classroomresources');
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_classroomresources');
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$heading = ($id > 0) ? get_string('editassign', 'local_classroomresources') : get_string('createassign', 'local_classroomresources');
$boxcontent = ($id > 0) ? get_string('editassignresource', 'local_classroomresources') : get_string('createassignresource', 'local_classroomresources');
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/view.php'));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$schoolid = $DB->get_field('local_classroomresources', 'schoolid', array('id' => $id));
$editform = new assignresource_form(null, array('id' => $id, 'sid' => $schoolid));
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    if ($data->id > 0) {
        $data->schoolid = $DB->get_field('local_classroomresources', 'schoolid', array('id' => $data->id));
        $data->buildingid = $DB->get_field('local_classroomresources', 'buildingid', array('id' => $data->id));
        $data->floorid = $DB->get_field('local_classroomresources', 'floorid', array('id' => $data->id));
        $data->classroomid = $DB->get_field('local_classroomresources', 'classroomid', array('id' => $data->id));
        $data->resourceid = implode(",", $data->resourceid);
        $value = $DB->update_record('local_classroomresources', $data);
        if ($value) {
            $message = get_string('assignresourceupdate', 'local_classroomresources');
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('assignresourceupdatefail', 'local_classroomresources');
            $style = array('style' => 'notifyproblem');
        }
    } else {
        $data->resourceid = implode(",", $data->resourceid);
        $value = $DB->insert_record('local_classroomresources', $data);
        if ($value) {
            $message = get_string('assignresourcecreate', 'local_classroomresources');
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('assignresourcecreatefail', 'local_classroomresources');
            $style = array('style' => 'notifyproblem');
        }
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$resource = cobalt_resources::get_instance();
//$resource->assignresource_tabs($currenttab);
$resource->resource_tabs($currenttab, $id = -1);
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
    $school = count($scho);
    if ($school > 0) {
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
