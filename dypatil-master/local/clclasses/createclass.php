<?php

echo '<div id="myratings"></div>';
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/clclasses/createclass_form.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
global $CFG, $DB, $USER, $PAGE;
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui', 'core');
$PAGE->requires->css('/local/ratings/css/jquery-ui.css');
$PAGE->requires->css('/local/ratings/css/style.css');
$PAGE->requires->js('/local/ratings/js/ratings.js');
$PAGE->requires->js('/local/clclasses/js/dynamic.js');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$page = optional_param('page', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
require_login();
$hierarchy = new hierarchy();
$semclass = new schoolclasses();
$systemcontext = context_system::instance();
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    $returnurl = new moodle_url('/local/error.php');
    redirect($returnurl);
}
if ($id > 0) {
    if (!($tool = $DB->get_record('local_clclasses', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_clclasses');
    } else {
        //$tool->schoolid=$DB->get_field('local_school','fullname',array('id'=>$tool->schoolid));
        $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$PAGE->set_url('/local/clclasses/createclass.php', array('id' => $id));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('manageclasses', 'local_clclasses');
$returnurl = new moodle_url('/local/clclasses/index.php', array('id' => $id, 'page' => $page));
//delete starts
if ($delete) {

    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $data = $DB->get_record('local_clclasses', array('id' => $id));
        $semclass->classes_delete_instance($id);
        $message = get_string('classdeletesuccess', 'local_clclasses', $data);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deleteclasses', 'local_clclasses');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $checkclasses = $DB->get_records('local_user_clclasses', array('classid' => $id));


    if ($checkclasses) {
        $yesurl = new moodle_url('/local/clclasses/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('cannotdelete', 'local_clclasses');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    } else {
        $yesurl = new moodle_url('/local/clclasses/classes.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delclassesconfirm', 'local_clclasses');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
//
/* Start of hide or display the faculty */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    $data = $DB->get_record('local_clclasses', array('id' => $id));
    if (!empty($hide)) {
        $disabled = 0;
        $message = get_string('classinactivesuccess', 'local_clclasses', $data);
    } else {
        $disabled = 1;
        $message = get_string('classactivesuccess', 'local_clclasses', $data);
    }
    $DB->set_field('local_clclasses', 'visible', $disabled, array('id' => $id));
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
$heading = ($id > 0) ? get_string('editclasses', 'local_clclasses') : get_string('createclasses', 'local_clclasses');
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php', array('id' => $id)));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editform = new createclass_form(null, array('id' => $id));

// Task code : T1.6 - Assigning instructor to class
if (isset($tool->instructor))
    $tool->instructor = explode(',', $tool->instructor);
$editform->set_data($tool);
$clob = new schoolclasses();
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $data->description = $data->description['text'];
    if ($data->id > 0) {
        $_REQUEST['newcobaltcourseid1'];
        echo'<br>';
        $_REQUEST['newonlinecourseid1'];
        echo'<br>';
        if ($_REQUEST['newcobaltcourseid1'] != null) {
            $data->cobaltcourseid = $_REQUEST['newcobaltcourseid1'];
        }
        if ($_REQUEST['newonlinecourseid1'] != null) {
            $data->onlinecourseid = $_REQUEST['newonlinecourseid1'];
        }
        /*
         * ###BUG(Bugreport#111)-Grade Submission
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Deleting the online course id for the class from database.
         * When it's updated from online to offline
         */
        if ($data->online == 2) {
            $data->onlinecourseid = '';
        }        

        $clob->enrollinstructor_tocourse_duringclassupdation($data);

        // Task code : T1.6 - Assigning instructor to class    
        $data->instructor = implode(',', $data->instructor);



        //$semclass->classes_update_instance($data);
        if( empty($data->instructor))
        $data->instructor=0;
        $classid = $DB->update_record('local_clclasses', $data);
        $message = get_string('classupdatesuccess', 'local_clclasses', $data);
        $schoid = $data->schoolid;
        $semid = $data->semesterid;
        $deptid = $data->departmentid;
        $courseid = $data->cobaltcourseid;
        $returnurl = new moodle_url('/local/clclasses/index.php');


        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    } else {

        $_REQUEST['newcobaltcourseid1'];
        echo'<br>';
        $_REQUEST['newonlinecourseid1'];
        echo'<br>';
        if ($_REQUEST['newcobaltcourseid1'] != null) {
            $data->cobaltcourseid = $_REQUEST['newcobaltcourseid1'];
        }
        if ($_REQUEST['newonlinecourseid1'] != null) {
            $data->onlinecourseid = $_REQUEST['newonlinecourseid1'];
        }
        // Task code : T1.6 - Assigning instructor to class    
        $data->instructor = implode(',', $data->instructor);

        if (empty($data->instructor)) {
            $data->instructor = 0;
        }

        $classid = $DB->insert_record('local_clclasses', $data);
        if ($data->instructor != 0)
            $clob->enrollinstructor_tocourse_duringclasscreation($classid);
        $message = get_string('classcreatesuccess', 'local_clclasses', $data);

        $schoid = $data->schoolid;
        $semid = $data->semesterid;
        $deptid = $data->departmentid;
        $courseid = $data->cobaltcourseid;
        $returnurl = new moodle_url('/local/clclasses/scheduleclass.php', array('schoid' => $schoid, 'semid' => $semid, 'deptid' => $deptid, 'courseid' => $courseid, 'classid' => $classid));


        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
}
echo $OUTPUT->header();
$currenttab = "create";
echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
$semclass->print_classestabs($currenttab, $id);
if ($id < 0)
    echo $OUTPUT->box(get_string('addcoursetabdes', 'local_clclasses'));
else
    echo $OUTPUT->box(get_string('editcoursetabdes', 'local_clclasses'));
$editform->display();
echo $OUTPUT->footer();
