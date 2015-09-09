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
$linkbuild = optional_param('linkbuild', 0, PARAM_INT);
$hierarchy = new hierarchy();
$conf = new object();
$currenttab = 'createfloor';
$systemcontext = context_system::instance();
if ($id > 0) {

    if (!($tool = $DB->get_record('local_floor', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_floor');
    } else {
        $tool->schoolid = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
        $tool->buildingid = $DB->get_field('local_building', 'fullname', array('id' => $tool->buildingid));
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
$PAGE->set_url('/local/classroomresources/floor.php', array('id' => $id));
$returnurl = new moodle_url('/local/classroomresources/viewfloor.php', array('id' => $id));
$strheading = get_string('managefloor', 'local_classroomresources');
// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $conf->floors = $DB->get_field('local_floor', 'fullname', array('id' => $id));
        $resource = cobalt_resources::get_instance();
        $deletefloor = $resource->cobalt_floor_delete($id);
        if ($deletefloor) {
            $message = get_string('floordelete', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('floordeletefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deletefloors', 'local_classroomresources');
    $PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewfloor.php'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
    $resource = cobalt_resources::get_instance();
    // $resource->floor_tabs($currenttab,$id);
    echo $OUTPUT->heading($strheading);
    $depend = check_floor($id);
    if ($depend > 0) {
        echo '<div align="center">';
        $message = get_string('floorcheck', 'local_classroomresources');
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        $yesurl = new moodle_url('/local/classroomresources/floor.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('deletefloor', 'local_classroomresources');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

//Make the buildings to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    $value = $DB->set_field('local_floor', 'visible', $visible, array('id' => $id));
    $DB->set_field('local_classroom', 'visible', $visible, array('floorid' => $id));
    $DB->set_field('local_classroomresources', 'visible', $visible, array('floorid' => $id));
    $data->floors = $DB->get_field('local_floor', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_floor', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($value) {
        $message = get_string('successf', 'local_classroomresources', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_classroomresources');
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$heading = ($id > 0) ? get_string('editfloor', 'local_classroomresources') : get_string('createfloor', 'local_classroomresources');
$boxcontent = ($id > 0) ? get_string('flooredit', 'local_classroomresources') : get_string('floorcreate', 'local_classroomresources');
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewfloor.php'));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editform = new createfloor_form(null, array('id' => $id));

$editform->set_data($tool);

// enabling school and building name while linking from other plugin
if ($linkschool) {
    $tool->schoolid = $linkschool;
    $tool->buildingid = $linkbuild;
    $editform->set_data($tool);
}
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $conf->floors = $data->fullname;
    if ($data->id > 0) {
        $data->description = $data->description['text'];
        $data->schoolid = $DB->get_field('local_floor', 'schoolid', array('id' => $data->id));
        $data->buildingid = $DB->get_field('local_floor', 'buildingid', array('id' => $data->id));
        $value = $DB->update_record('local_floor', $data);
        if ($value) {
            $message = get_string('floorupdate', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('floorupdatefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    } else {
        $data->description = $data->description['text'];
        $value = $DB->insert_record('local_floor', $data);
        if ($value) {
            $message = get_string('floorcreates', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('floorcreatefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$resource = cobalt_resources::get_instance();
$resource->floor_tabs($currenttab, $id);
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
