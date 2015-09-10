<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/classtype_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
global $DB, $CFG, $USER, $PAGE, $OUTPUT;

//checking the id is greater than one...if its fetching content from table...used in edit purpose
if ($id > 0) {
    if (!($tool = $DB->get_record('local_class_scheduletype', array('id' => $id)))) {
        print_error('invalidtoolid');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

$PAGE->set_url('/local/timetable/classtype.php', array('id' => $id));
$systemcontext =  context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
//-------successful message returnurl --------
$sreturnurl = new moodle_url('/local/timetable/classtype.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/timetable/classtype.php";

//this is the return url ---------------------
$returnurl = new moodle_url('/local/timetable/classtype.php');
$strheading = get_string('setclasstype', 'local_timetable');
$PAGE->navbar->add(get_string('pluginname', 'local_timetable'), new moodle_url('/local/timetable/classtype.php'));
$PAGE->navbar->add(get_string('classtype', 'local_timetable'));
$PAGE->set_title($strheading);

// calling timetable_manage class instanc.....
$timetableob = manage_timetable::getInstance();
$hier = new hierarchy();

/* Start of delete the classtype ----------------- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $res = $timetableob->timetable_delete_classtype($id, $currenturl);   
    }
    $strheading = get_string('delete_classtype', 'local_timetable');
    $PAGE->navbar->add($strheading);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $result = $DB->record_exists('local_scheduleclass', array('classtypeid' => $id));
    if ($result) {
        echo $confirm_msg = get_string('usedinscheduleclass', 'local_timetable');
       echo $OUTPUT->continue_button( $currenturl);     
    }
    else{
     $yesurl = new moodle_url('/local/timetable/classtype.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
     $message = get_string('delconfirm_classtype', 'local_timetable');
     echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
/* End of delete the class type----------------------- */

//-----code used to hide and show--------------------------------------------
if ($visible != -1 and $id and $confirm and confirm_sesskey()) {
    $visible_result = $DB->set_field('local_class_scheduletype', 'visible', $visible, array('id' => $id));
    $classtype_name = $DB->get_field('local_class_scheduletype', 'classtype', array('id' => $id));
    $visible = $DB->get_field('local_class_scheduletype', 'visible', array('id' => $id));

    if ($visible == 1) {
        $visible = 'Activated';
    } else {
        $visible = 'Inactivated';
    }
    if ($visible_result ) {
        $message = get_string('scheduleclasstype_success', 'local_timetable',  $visible);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('scheduleclasstype_failure', 'local_timetable',  $visible);
        $style = array('style' => 'notifyproblem');
    }
    $hier->set_confirmation($message, $returnurl, $style);
}
//-------end of code hide and show-------------------------------------------
//-------------creating instance of entity form-------------

$classtypeob = new classtypes_form();
//-------setting tool array value to entity form used for editing purpose--------------  
$classtypeob->set_data($tool);


if ($classtypeob->is_cancelled()) {
    redirect($returnurl);
}


if ($data = $classtypeob->get_data()) {


    if ($data->id > 0) {

        // Update code         
        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();
        if(!isset($data->visible))
        $data->visible=0;

        $result = $DB->update_record('local_class_scheduletype', $data);
        $timetableob->success_error_msg($result, 'success_up_classtype', 'error_up_classtype', $currenturl);
    } else {
        //-------------adding code(insert entity)
        //----------checking case sensitive------------------------------

        $classtype = strtolower($data->classtype);
        $classtypelists = $DB->get_records('local_class_scheduletype');
        foreach ($classtypelists as $ent) {
            if ($classtype == strtolower($classtypelists[$ent->id]->classtype)) {
                $confirm_msg = get_string('already', 'local_timetable');
                $hier->set_confirmation($confirm_msg, $currenturl);
            }
        }
        // print_object($entitylists);
        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        $re = $DB->insert_record('local_class_scheduletype', $data);
        $timetableob->success_error_msg($re, 'success_add_classtype', 'error_add_classtype', $currenturl);
    }
}


$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('classtype', 'local_timetable'));

// adding tab code----------------------------------
$currenttab = 'set_classtype';
$timetableob->timetable_tabs($currenttab);
echo $OUTPUT->box(get_string('setclasstype_desc', 'local_timetable'));

$schoolid = $timetableob->check_loginuser_registrar_admin();
$classtypeob->display();
$output = $PAGE->get_renderer('local_timetable');
$tmobject = new timetable($schoolid, "classtype");
echo $output->render($tmobject);

echo $output->footer();
?>