<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/semesters/semester_form.php');
require_once($CFG->dirroot . '/local/semesters/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

$id = optional_param('id', -1, PARAM_INT); //semester id
$schoolid = optional_param('scid', -1, PARAM_INT); //school id
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$mode = optional_param('mode', 'all', PARAM_RAW); //for the current tab

$hierarchy = new hierarchy();
$conf = new object();
$mysemester = semesters::getInstance();
$systemcontext = context_system::instance();

if ($id > 0) {
    //get the records from the table to edit
    if (!($tool = $DB->get_record('local_semester', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_semesters');
    }
    $tool->schoolid = $mysemester->get_assignedschools($id, $hierarchy);
    if (sizeof($tool->schoolid) == 1) {
        foreach ($tool->schoolid as $ts)
            $tool->schoolid = $ts;
    }
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    // to create a new semester
    $tool = new stdClass();
    $tool->id = -1;
}
$tool->mode = $mode;

$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//If the loggedin user have the capability of managing the Semesters allow the page
if (!has_any_capability(array('local/semesters:manage','local/semesters:update','local/semesters:create'), $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_heading(get_string('semesters', 'local_semesters'));
$PAGE->set_url('/local/semesters/semester.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);


$returnurl = new moodle_url('/local/semesters/index.php', array('id' => $id, 'mode' => $mode));
$strheading = get_string('pluginname', 'local_semesters');

// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    $conf->semester = $DB->get_field('local_semester', 'fullname', array('id' => $id));
    if ($confirm and confirm_sesskey()) {
        //delete the semester
        $mysemester->cobalt_delete_semester($id, $schoolid);
        $message = get_string('deletesuccess', 'local_semesters', $conf);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $PAGE->navbar->add($strheading, new moodle_url('/local/semesters/index.php', array('id' => $id)));
    $strheading = get_string('deletesemester', 'local_semesters');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('semesters', 'local_semesters') . ': ' . $strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_semesters'));
    // check whether any class is created under this semester or registration is enabled.

    $check = $mysemester->get_dependency_list($id);
    $message = '<h3>' . $strheading . '</h3>';
    if ($check) {
        //dont allow to delete the semester
        switch ($check) {
            case 1:$message .= get_string('assignedclclassesdontdelete', 'local_semesters', $conf);
                break;
            case 2:$message .= get_string('eventenableddontdelete', 'local_semesters', $conf);
                break;
        }

        echo '<div align="center">';
        echo $OUTPUT->box($message);
        echo '<br/>';
        echo $OUTPUT->continue_button(new moodle_url('/local/semesters/index.php', array('id' => $id)));
        echo '</div>';
    } else {
        //display confirmation message to delete.
        $yesurl = new moodle_url('/local/semesters/semester.php', array('id' => $id, 'scid' => $schoolid, 'delete' => 1, 'mode' => $mode, 'confirm' => 1, 'sesskey' => sesskey()));
        $message .= get_string('delconfirm', 'local_semesters');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

//Make the Semesters to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    $conf->semester = $DB->get_field('local_semester', 'fullname', array('id' => $id));
    if ($visible == 0) {
        //if registration is enabled to this semester dont allow to hide the semester.
        $check = $mysemester->get_dependency_list($id);
        if ($check == 2) {
            $message = get_string('eventenableddonthide', 'local_semesters', $conf);
            $options = array('style' => 'notifyproblem');
            $hierarchy->set_confirmation($message, $returnurl, $options);
        } else {
            //make inactive the semester.
            $DB->set_field('local_semester', 'visible', $visible, array('id' => $id));
            $message = get_string('inactivesuccess', 'local_semesters', $conf);
        }
    } else {
        //make active the semester.
        $DB->set_field('local_semester', 'visible', $visible, array('id' => $id));
        $message = get_string('activesuccess', 'local_semesters', $conf);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
$heading = ($id > 0) ? get_string('editsemester', 'local_semesters') : get_string('createsemester', 'local_semesters');
// Page navigation bar
$PAGE->navbar->add(get_string('pluginname', 'local_semesters'), new moodle_url('/local/semesters/index.php', array('id' => $id)));
$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('semesters', 'local_semesters') . ': ' . $heading);

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true, $id);
//object for the form
$semester = new createsemester(null, array('id' => $id, 'editoroptions' => $editoroptions));
$semester->set_data($tool);

if ($semester->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $semester->get_data()) {
    $data->usermodified = $USER->id;
    $data->timemodified = time();
    $data->description = $data->description['text'];
    $conf->semester = $data->fullname;
    if ($data->id > 0) {
        $check = $mysemester->get_dependency_list($data->id);
        if ($check == 2) {
            //if registration event is enabled to the semester, dont update the record
            $message = get_string('eventenabledcantupdate', 'local_semesters', $conf);
            $options = array('style' => 'notifyproblem');
        } else {
            // Update record
            $mysemester->cobalt_update_semester($data);
            $message = get_string('updatesuccess', 'local_semesters', $conf);
            $options = array('style' => 'notifysuccess');
        }
    } else {
        // Add new record
        $data->timecreated = time();
        $mysemester->cobalt_insert_semester($data);
        $message = get_string('createsuccess', 'local_semesters', $conf);
        $options = array('style' => 'notifysuccess');
    }
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

//display the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_semesters'));
// Tab view
$currenttab = 'create';
$mysemester->createtabview($currenttab, $id);
// tab description
if ($id < 0)
    echo $OUTPUT->box(get_string('createsemtabdes', 'local_semesters'));
else
    echo $OUTPUT->box(get_string('editsemtabdes', 'local_semesters'));
// Display the form
$semester->display();
echo $OUTPUT->footer();
?>
