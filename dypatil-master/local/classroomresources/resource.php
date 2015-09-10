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
$conf = new object();
$currenttab = 'createresource';
$systemcontext = context_system::instance();
if ($id > 0) {

    if (!($tool = $DB->get_record('local_resource', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_resource');
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
$PAGE->set_url('/local/classroomresources/resource.php', array('id' => $id));
$returnurl = new moodle_url('/local/classroomresources/viewresource.php', array('id' => $id));
$strheading = get_string('manageresource', 'local_classroomresources');
// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $conf->resources = $DB->get_field('local_resource', 'fullname', array('id' => $id));
        $resource = cobalt_resources::get_instance();
        $deleteresource = $resource->cobalt_resource_delete($id);
        if ($deleteresource) {
            $message = get_string('resourcedelete', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('resourcedeletefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deleteresources', 'local_classroomresources');
    $PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewresource.php'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
    $resource = cobalt_resources::get_instance();

    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/classroomresources/resource.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('deleteresource', 'local_classroomresources');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}
//Make the buildings to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    $value = $DB->set_field('local_resource', 'visible', $visible, array('id' => $id));
    $data->resources = $DB->get_field('local_resource', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_resource', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($value) {
        $message = get_string('successrs', 'local_classroomresources', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_classroomresources');
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$heading = ($id > 0) ? get_string('editresource', 'local_classroomresources') : get_string('createresource', 'local_classroomresources');
$boxcontent = ($id > 0) ? get_string('resourceedit', 'local_classroomresources') : get_string('resourcecre', 'local_classroomresources');
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewclassroom.php'));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editform = new createresource_form(null, array('id' => $id));
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $conf->resources = $data->fullname;
    if ($data->id > 0) {
        $data->description = $data->description['text'];
        $data->schoolid = $DB->get_field('local_resource', 'schoolid', array('id' => $data->id));
        $value = $DB->update_record('local_resource', $data);
        if ($value) {
            $message = get_string('resourceupdate', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('resourceupdatefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    } else {
        $data->description = $data->description['text'];
        $value = $DB->insert_record('local_resource', $data);
        if ($value) {
            $message = get_string('resourcecreate', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('resourcecreatefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$resource = cobalt_resources::get_instance();
$resource->resource_tabs($currenttab, $id);

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
