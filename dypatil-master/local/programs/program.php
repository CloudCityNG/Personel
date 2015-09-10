<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/programs/program_form.php');
require_once($CFG->dirroot . '/local/programs/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$scid = optional_param('scid', 0, PARAM_INT);

$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();

if ($id > 0) {
    //get the records from the table to edit
    if (!($tool = $DB->get_record('local_program', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_programs');
    } else {
        $settings = $DB->get_record('local_level_settings', array('levelid' => $id));
        if ($graduatelevel = $DB->get_record('local_level_settings', array('levelid' => $id, 'entityid' => 1)))
            $tool->mincrhour = $graduatelevel->mincredithours;
        $countmincredithours = $DB->get_records('local_level_settings', array('levelid' => $id, 'entityid' => 2));

        foreach ($countmincredithours as $cchours) {

            if ($cchours->subentityid == 1)
                $tool->mincredithours[0] = $cchours->mincredithours;
            if ($cchours->subentityid == 2)
                $tool->mincredithours[1] = $cchours->mincredithours;
            if ($cchours->subentityid == 3)
                $tool->mincredithours[2] = $cchours->mincredithours;
            if ($cchours->subentityid == 4)
                $tool->mincredithours[3] = $cchours->mincredithours;
        }
    }

    if ($scid)
        $tool->schoolid = $scid;
    $tool->school_name = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    // to create a new Program
    $tool = new stdClass();
    $tool->id = -1;
}

$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//If the loggedin user have the capability of managing the batches allow the page
if (!has_any_capability(array('local/programs:manage','local/programs:create','local/programs:update'), $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_heading(get_string('programs', 'local_programs'));
$PAGE->set_url('/local/programs/program.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$myprogram = programs::getInstance();
$returnurl = new moodle_url('/local/programs/index.php', array('id' => $id));
if ($scid) {
    $returnurl = new moodle_url('/local/collegestructure/index.php', array('id' => $id, 'scid' => $scid, 'flat' => 2));
}
$strheading = get_string('pluginname', 'local_programs');

// If the $delete variable is set, then delete the record from the table
if ($delete) {
    $PAGE->url->param('delete', 1);
    $conf->program = $DB->get_field('local_program', 'fullname', array('id' => $id));
    if ($confirm and confirm_sesskey()) {
        $myprogram->cobalt_delete_program($id);
        $message = get_string('deletesuccess', 'local_programs', $conf);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deleteprogram', 'local_programs');
    $PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php', array('id' => $id)));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('programs', 'local_programs') . ': ' . $strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_programs'));
    $schoolid = $DB->get_field('local_program', 'schoolid', array('id' => $id));
    $check = $myprogram->get_dependency_list($id, $schoolid);
    $message = '<h3>' . $strheading . '</h3>';
    if ($check) {
        switch ($check) {
            case 1:$message .= get_string('eventenabledcantdelete', 'local_programs', $conf);
                break;
            case 2:$message .= get_string('assignedcurriculum', 'local_programs', $conf);
                break;
            case 3:$message .= get_string('assignedmodule', 'local_programs', $conf);
                break;
            case 4:$message .= get_string('assigneduser', 'local_programs', $conf);
                break;
            case 5:$message .= get_string('assignedbatch', 'local_programs', $conf);
                break;
            default:$message .= get_string('delconfirm', 'local_programs', $conf);
                break;
        }

        echo '<div align="center">';
        echo $OUTPUT->box($message);
        echo '<br/>';
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        $scl = array();
        if ($scid)
            $scl['scid'] = $scid;
        $yesurl = new moodle_url('/local/programs/program.php', $scl + array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message .= get_string('delconfirm', 'local_programs');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

//Make the program to active or inactive
if ($visible >= 0 && $id && confirm_sesskey()) {
    $conf->program = $DB->get_field('local_program', 'fullname', array('id' => $id));
    if ($visible == 0) {
        //if admission is enabled to this program dont allow to hide the program.
        $schoolid = $DB->get_field('local_program', 'schoolid', array('id' => $id));
        $check = $myprogram->get_dependency_list($id, $schoolid);
        if ($check == 1) {
            $message = get_string('eventenableddonthide', 'local_programs', $conf);
            $options = array('style' => 'notifyproblem');
            $hierarchy->set_confirmation($message, $returnurl, $options);
        } if ($check == 4) {
            $message = get_string('userassigneddonthide', 'local_programs', $conf);
            $options = array('style' => 'notifyproblem');
            $hierarchy->set_confirmation($message, $returnurl, $options);
        } else {
            //make inactive the Program.
            $DB->set_field('local_program', 'visible', $visible, array('id' => $id));
            $message = get_string('inactivesuccess', 'local_programs', $conf);
        }
    } else {
        //make active the Program.
        $DB->set_field('local_program', 'visible', $visible, array('id' => $id));
        $message = get_string('activesuccess', 'local_programs', $conf);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
$heading = ($id > 0) ? get_string('editprogram', 'local_programs') : get_string('createprogram', 'local_programs');
// Page navigation bar
$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php', array('id' => $id)));
$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('programs', 'local_programs') . ': ' . $heading);

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$params = array('id' => $id, 'editoroptions' => $editoroptions);
if ($id > 0) {
    $scl = array('schoolid' => $tool->schoolid, 'duration' => $tool->duration);
    $params = array_merge($params, $scl);
}
if ($scid) {
    $scl = array('scid' => $scid);
    $params = array_merge($params, $scl);
}
//print_object($params);
$program = new createprogram(null, $params);
$program->set_data($tool);

if ($program->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $program->get_data()) {
    $data->duration = isset($data->duration_1) ? $data->duration_1 : $data->duration_2;
    //print_object($data);exit;
    $data->usermodified = $USER->id;
    $data->timemodified = time();
    $data->description = $data->description['text'];
    $conf->program = $data->fullname;
    if ($data->id > 0) {
        $check = $myprogram->get_dependency_list($data->id, $data->schoolid);
        if ($check == 1) {
            //Admission event is enabled for this program, dont update the record
            $message = get_string('eventenabledcantupdate', 'local_programs', $conf);
            $options = array('style' => 'notifyproblem');
        } else if ($check == 4) {
            //if any users are assigned to the program, dont update the record
            $message = get_string('userassignedcannotupdate', 'local_programs', $conf);
            $options = array('style' => 'notifyproblem');
        } else {
            // Update record

            $myprogram->cobalt_update_program($data);
            $message = get_string('updatesuccess', 'local_programs', $conf);
            $options = array('style' => 'notifysuccess');
        }
    } else {
        // Add new record
        $data->timecreated = time();
        $myprogram->cobalt_insert_program($data);
        $message = get_string('createsuccess', 'local_programs', $conf);
        $options = array('style' => 'notifysuccess');
    }
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

//display the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_programs'));
// Tab view
$currenttab = 'create';
$myprogram->createtabview($currenttab, $id);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    if ($id < 0)
        echo $OUTPUT->box(get_string('createprogramspage', 'local_programs'));
    else
        echo $OUTPUT->box(get_string('editprogramspage', 'local_programs'));
}
// Display the form
$program->display();
echo $OUTPUT->footer();
?>
