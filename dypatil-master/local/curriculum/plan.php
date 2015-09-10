<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/curriculum/curriculum_form.php');
require_once($CFG->dirroot . '/local/curriculum/lib.php');
require_once($CFG->dirroot . '/local/collegestructure/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

$id = optional_param('id', -1, PARAM_INT); //plan id
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$cid = optional_param('curriculumid', 0, PARAM_INT);
$pid = optional_param('programid', 0, PARAM_INT);
$sid = optional_param('schoolid', 0, PARAM_INT);

$hierarchy = new hierarchy();
$conf = new object();
$cplan = curriculumplan::getInstance();
$systemcontext = context_system::instance();

if ($cid) {
    $curr = $DB->get_record('local_curriculum', array('id' => $cid));
    if (!$curr->enableplan) {
        $message = get_string('cantcreateplan', 'local_curriculum', $curr->fullname);
        $url = new moodle_url($CFG->wwwroot . '/local/curriculum/index.php');
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $url, $options);
    }
}

if ($id > 0) {
    //get the records from the table to edit
    if (!($tool = $DB->get_record('local_curriculum_plan', array('id' => $id)))) {
        print_error('invalidtoolid');
    }
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    // to create a new plan
    $tool = new stdClass();
    $tool->id = -1;
    $tool->schoolid = $sid;
    $tool->programid = $pid;
    $tool->curriculumid = $cid;
}
$tool->curriculum_name = $DB->get_field('local_curriculum', 'fullname', array('id' => $tool->curriculumid));
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();

//If the loggedin user have the capability of managing the plans, allow the page
//if (!has_capability('local/curriculumplan:manage', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->set_heading(get_string('pluginname', 'local_curriculum'));
$PAGE->set_url('/local/curriculum/plan.php', array('id' => $id));
$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);

$returnurl = new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid, 'assign' => 1));
$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
$strheading = get_string('manageplan', 'local_curriculum');

// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $conf->plan = $DB->get_field('local_curriculum_plan', 'fullname', array('id' => $id));
        //delete the plan
        $cplan->delete_plan($id);
        $message = get_string('deletesuccess', 'local_curriculum', $conf);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $PAGE->navbar->add($strheading, new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid)));
    $strheading = get_string('deleteplan', 'local_curriculum');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
    $message = '<h3>' . $strheading . '</h3>';
    // check whether childs are created for this plan or any courses are assigned.
    $check = $cplan->get_dependency_list($id);
    if ($check) {
        //dont allow to delete the semester
        switch ($check) {
            case 1:$message .= get_string('assignedcoursesdelete', 'local_curriculum');
                break;
            case 2:$message .= get_string('containschilddontdelete', 'local_curriculum');
                break;
        }

        echo '<div align="center">';
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        //display confirmation message to delete.
        $yesurl = new moodle_url('/local/curriculum/plan.php', array('id' => $id, 'curriculumid' => $cid, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message .= get_string('delplanconfirm', 'local_curriculum');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
//End of the delete plan.
//Make the Curriculumplan to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    if ($visible == 0) {
        $check = $cplan->get_dependency_list($id);
        if ($check == 2) {
            $message = get_string('containschilddonthide', 'local_curriculum');
            $options = array('style' => 'notifyproblem');
            $hierarchy->set_confirmation($message, $returnurl, $options);
        }
    }
    $DB->set_field('local_curriculum_plan', 'visible', $visible, array('id' => $id));
    $message = get_string('success', 'local_curriculum');
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}



$heading = ($id > 0) ? get_string('editplan', 'local_curriculum') : get_string('createplan', 'local_curriculum');
// Page navigation bar

$PAGE->navbar->add(get_string('manageplan', 'local_curriculum'), new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid, 'assign' => 1)));
$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $heading);

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true, $id);
$params = array('id' => $id, 'cid' => $cid, 'editoroptions' => $editoroptions);

//object for the form
$plan = new curriculumplan_form(null, $params);
$plan->set_data($tool);

if ($plan->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $plan->get_data()) {
    $data->usermodified = $USER->id;
    $data->timemodified = time();
    $data->description = $data->description['text'];
    $conf->plan = $data->fullname;
    if ($data->id > 0) {
        // Update record
        $cplan->update_plan($data->id, $data);
        $message = get_string('updatesuccess', 'local_curriculum', $conf);
    } else {
        // Add new record
        $data->timecreated = time();
        $cplan->insert_plan($data);
        $message = get_string('createsuccess', 'local_curriculum', $conf);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

//display the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
// Tab view
$curriculum = new curricula();
$currenttab = 'addnewplan';
$curriculum->print_curriculumtabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewplancreationpage', 'local_curriculum'));
}
// Display the form
$plan->display();
echo $OUTPUT->footer();
?>
