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
$conf = new object();
$currenttab = 'createclassroom';
$systemcontext = context_system::instance();
if ($id > 0) {
    if (!($tool = $DB->get_record('local_classroom', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_classroom');
    } else {
        $tool->schoolid = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
        $tool->buildingid = $DB->get_field('local_building', 'fullname', array('id' => $tool->buildingid));
        $tool->floorid = $DB->get_field('local_floor', 'fullname', array('id' => $tool->floorid));
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
$PAGE->set_url('/local/classroomresources/classroom.php', array('id' => $id));
$returnurl = new moodle_url('/local/classroomresources/viewclassroom.php', array('id' => $id));
$strheading = get_string('manageclass', 'local_classroomresources');

// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $conf->classroom = $DB->get_field('local_classroom', 'fullname', array('id' => $id));
        $resource = cobalt_resources::get_instance();
        $deleteclassroom = $resource->cobalt_class_delete($id);
        if ($deleteclassroom) {
            $message = get_string('classroomdelete', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('classroomdeletefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deleteclclasses', 'local_classroomresources');
    $PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewclassroom.php'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
    $resource = cobalt_resources::get_instance();
    // $resource->class_tabs($currenttab, $id);
    echo $OUTPUT->heading($strheading);
    $depend = check_classroom($id);
    if ($depend > 0) {
        echo '<div align="center">';
        $message = get_string('classroomcheck', 'local_classroomresources');
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        $yesurl = new moodle_url('/local/classroomresources/classroom.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('classroomdeletes', 'local_classroomresources');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

//Make the buildings to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    $value = $DB->set_field('local_classroom', 'visible', $visible, array('id' => $id));
    $DB->set_field('local_classroomresources', 'visible', $visible, array('classroomid' => $id));
    $data->room = $DB->get_field('local_classroom', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_classroom', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($value) {
        $message = get_string('successr', 'local_classroomresources', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_classroomresources');
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$heading = ($id > 0) ? get_string('editclassroom', 'local_classroomresources') : get_string('createclassroom', 'local_classroomresources');
$boxcontent = ($id > 0) ? get_string('classroomedit', 'local_classroomresources') : get_string('classroomcre', 'local_classroomresources');
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewclassroom.php'));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editform = new createclassroom_form(null, array('id' => $id));
$editform->set_data($tool);
// used enable some from fields when linking from other plugins
if($linkschool){
    $tool->schoolid=$linkschool;
    $editform->set_data($tool);
}
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $conf->classroom = $data->fullname;
    if ($data->id > 0) {
        $data->description = $data->description['text'];
        $data->schoolid = $DB->get_field('local_classroom', 'schoolid', array('id' => $data->id));
        $data->buildingid = $DB->get_field('local_classroom', 'buildingid', array('id' => $data->id));
        $data->floorid = $DB->get_field('local_classroom', 'floorid', array('id' => $data->id));
        $value = $DB->update_record('local_classroom', $data);
        if ($value) {
            $message = get_string('classroomupdate', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('classroomupdatefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    } else {
        $data->description = $data->description['text'];
        $value = $DB->insert_record('local_classroom', $data);
        if ($value) {
            $message = get_string('classroomcreate', 'local_classroomresources', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('classroomcreatefail', 'local_classroomresources', $conf);
            $style = array('style' => 'notifyproblem');
        }
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$resource = cobalt_resources::get_instance();
$resource->class_tabs($currenttab, $id);
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
