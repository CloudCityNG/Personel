<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/cobaltcourses/lib.php');
require_once($CFG->dirroot . '/local/cobaltcourses/cobaltcourse_form.php');
require_once($CFG->dirroot . '/local/lib.php');
global $USER, $DB, $CFG;

$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);

$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/cobaltcourses:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('cobaltcourses', 'local_cobaltcourses'));
$PAGE->set_url('/local/cobaltcourses/cobaltcourse.php', array('id' => $id));
$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php', array('id' => $id)));
$returnurl = new moodle_url('/local/cobaltcourses/index.php', array('id' => $id));
/* ---delete the record if the parameter delete is set--- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    $conf->course = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $id));
    if ($confirm and confirm_sesskey()) {
        if (delete_cobaltcourses($id)) {
            $message = get_string('deletesuccess', 'local_cobaltcourses', $conf);
            $style = array('style' => 'notifysuccess');
        }
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $strheading = get_string('deletecourse', 'local_cobaltcourses');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . $strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    /* ---check if the course is assigned to any other modules--- */
    $check = get_course_dependencies($id);
    if ($check) {
        switch ($check) {
            case 1: $message = get_string('assignedtocurriculumdelete', 'local_cobaltcourses', $conf);
                break;
            case 2: $message = get_string('assignedtoclassdelete', 'local_cobaltcourses', $conf);
                break;
            case 3: $message = get_string('usersenrolleddelete', 'local_cobaltcourses', $conf);
                break;
        }
        /* ---not allow to delete the course--- */
        echo '<div align="center">';
        echo $OUTPUT->box($message);
        echo '<br/>';
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        /* --- Confirm to delete the course--- */
        $yesurl = new moodle_url('/local/cobaltcourses/cobaltcourse.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_cobaltcourses');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
if ($id > 0) {
    /* ---edit the existing cobalt course--- */
    if (!($record = $DB->get_record('local_cobaltcourses', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_cobaltcourses');
    }
    $check = get_course_dependencies($id);
    if ($check == 3 && $visible < 0) {
        $message = get_string('usersenrolledupdate', 'local_cobaltcourses', $record);
        $style = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $record->summary = array('text' => $record->summary, 'format' => FORMAT_HTML);
    $record->school_name = $DB->get_field('local_school', 'fullname', array('id' => $record->schoolid));
    $record->dept_name = $DB->get_field('local_department', 'fullname', array('id' => $record->departmentid));
} else {
    /* ---create a new cobalt course--- */
    $record = new stdClass();
    $record->id = -1;
}
/* ---Make the cobalt course to active or inactive--- */
if ($visible >= 0 && $id && confirm_sesskey()) {
    $conf->course = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $id));
    if ($visible == 0) {
        $check = get_course_dependencies($id);
        if ($check) {
            switch ($check) {
                case 1: $message = get_string('assignedtocurriculumhide', 'local_cobaltcourses', $record);
                    break;
                case 2: $message = get_string('assignedtoclasshide', 'local_cobaltcourses', $record);
                    break;
                case 3: $message = get_string('usersenrolledhide', 'local_cobaltcourses', $record);
                    break;
            }
            /* ---$message = get_string('usersenrolledhide', 'local_cobaltcourses', $record);--- */
            $style = array('style' => 'notifyproblem');
            $hierarchy->set_confirmation($message, $returnurl, $style);
        } else {
            $success = $DB->set_field('local_cobaltcourses', 'visible', $visible, array('id' => $id));
            $message = get_string('inactivatesuccess', 'local_cobaltcourses', $conf);
        }
    } else {
        $success = $DB->set_field('local_cobaltcourses', 'visible', $visible, array('id' => $id));
        $message = get_string('activatesuccess', 'local_cobaltcourses', $conf);
    }
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

$heading = ($id > 0) ? get_string('editcourse', 'local_cobaltcourses') : get_string('createcourse', 'local_cobaltcourses');

$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . $heading);
/* ---Object for the form--- */
$course = new createcourse(null, array('id' => $id));
$course->set_data($record);

if ($course->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $course->get_data()) {
    $data->usermodified = $USER->id;
    $data->timemodified = time();
    $data->summary = $data->summary['text'];
    $conf->course = $data->fullname;
    if ($data->id > 0) {

        if (update_cobaltcourses($data)) {
            $message = get_string('updatesuccess', 'local_cobaltcourses', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('updatefails', 'local_cobaltcourses', $conf);
            $style = array('style' => 'notifyproblem');
        }
    } else {
        /* ---Add new record--- */
        $data->timecreated = time();
        if (insert_cobaltcourses($data)) {
            $message = get_string('createsuccess', 'local_cobaltcourses', $conf);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('createfails', 'local_cobaltcourses', $conf);
            $style = array('style' => 'notifyproblem');
        }
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_cobaltcourses'));
try {
    if (is_siteadmin($USER->id)) {
        $schoollist = $DB->get_records('local_school', array('visible' => 1));
    } else {
        $schoollist = $hierarchy->get_assignedschools();
    }

    $count = count($schoollist);
    /* ---Count of schools to which registrar is assigned--- */
    if ($count < 1) {
        throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
    }
    /* --- Current tab--- */
    $currenttab = 'create';
    /* ---adding tabs--- */
    createtabview($currenttab, $id);

    if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
        if ($id < 0)
            echo $OUTPUT->box(get_string('coursecreatepage', 'local_cobaltcourses'));
        else
            echo $OUTPUT->box(get_string('courseeditpage', 'local_cobaltcourses'));
    }
    /* ---display the form--- */
    $course->display();
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
