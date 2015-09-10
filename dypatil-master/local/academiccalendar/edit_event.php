<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Editing the event activities
 *
 * @package    local
 * @subpackage Academic calendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/academiccalendar/lib.php');
require_once($CFG->dirroot . '/local/academiccalendar/create_event_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$eveid = optional_param('eveid', 0, PARAM_INT);
$hierarchy = new hierarchy();
$acalendar = academiccalendar :: get_instatnce();
if ($id > 0) {
    if (!($tool = $DB->get_record('local_event_activities', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_academiccalendar');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/academiccalendar/edit_event.php', array('id' => $id));
$systemcontext = context_system::instance();
require_login();
if (isguestuser()) {
    print_error('noguest');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_academiccalendar'));
$returnurl = new moodle_url('/local/academiccalendar/index.php');
if ($id > 0) {
    $strheading = get_string('pluginname', 'local_academiccalendar') . ':' . get_string('editevent', 'local_academiccalendar');
} else {
    $strheading = get_string('pluginname', 'local_academiccalendar') . ':' . get_string('Createevent', 'local_academiccalendar');
}
if ($id > 0) {
    $strnavbar = get_string('editevent', 'local_academiccalendar');
} else {
    $strnavbar = get_string('Createevent', 'local_academiccalendar');
}
$PAGE->navbar->add(get_string('pluginname', 'local_academiccalendar'), new moodle_url('/local/academiccalendar/index.php'));
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
/* Deleting unpublished Event activities  */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $acalendar->event_delete_instance($id);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation(get_string('eventdeletedsuccess', 'local_academiccalendar'), $returnurl, $options);
    }
    $strheading = get_string('deleteevent', 'local_academiccalendar');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/academiccalendar/edit_event.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirm', 'local_academiccalendar');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$PAGE->navbar->add($strnavbar);

/* ---Hide and show for event activity--- */
if ($visible >= 0 and $id and confirm_sesskey()) {
    $result = $DB->set_field('local_event_activities', 'publish', $visible, array('id' => $id));
    $data->event = $DB->get_field('local_event_activities', 'eventtitle', array('id' => $id));
    $data->visible = $DB->get_field('local_event_activities', 'publish', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($result) {
        $message = get_string('success', 'local_academiccalendar', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_academiccalendar', $data);
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

/* ---Creating object for form--- */
$editoroptions = array('id' => $id);
$createeventform = new createevent_form(null, array('id' => $id));
/* ---Set Form data--- */
if ($tool->id > 0) {
    $tool->eveid = $eveid;
    $activity_n = $DB->get_record('local_event_types', array('id' => $tool->eventtypeid));
    $tool->activityname = $activity_n->eventtypename;
    $eventls = $acalendar->eventls;
    $tool->eventlevel != NULL ? $tool->eventlevelname = $eventls[$tool->eventlevel] : null;
    if (!empty($tool->schoolid)) {
        $school_inst = $DB->get_record('local_school', array('id' => $tool->schoolid));
        $tool->schoolname = $school_inst->fullname;
    }
    if (!empty($tool->programid)) {
        $program_inst = $DB->get_record('local_program', array('id' => $tool->programid));
        $tool->programname = $program_inst->fullname;
    }
    if (!empty($tool->semesterid)) {
        $semester_inst = $DB->get_record('local_semester', array('id' => $tool->semesterid));
        $tool->semestername = $semester_inst->fullname;
    }
    $tool->instance = $tool->id;
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
}
$createeventform->set_data($tool);

/* ---Form Cancellation-- */

if ($createeventform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $createeventform->get_data()) {
    if ($tool->id > 0) {
        $today = time();
        $d_event = array();
        $d_event['uuid'] = $data->id;
        $evename = $DB->get_record('local_event_types', array('id' => $tool->eventtypeid));
        $data->activityname = $evename->eventtypename;
        $data->description = $data->description['text'];
        $d_event['name'] = $data->eventtitle;
        isset($data->description['text']) ? $d_event['description'] = $data->description['text'] : null;
        $d_event['userid'] = $USER->id;
        $d_event['eventtype'] = $data->activityname;
        $d_event['timestart'] = $data->startdate;
        $d_event['timemodified'] = $today;
        $d_event['visible'] = $data->publish;
        /* ---Conditions for the Registration event--- */
        $data->userid = $USER->id;
        $data->timemodified = time();
        $deventid = $DB->get_record('event', array('uuid' => $data->id));
        $d_event['id'] = $deventid->id;
        /* ---Updating functionality starts here--- */
        $acalendar->event_update_instance($data);
        $acalendar->devent_update($d_event);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation(get_string('eventupdatesuccess', 'local_academiccalendar'), $returnurl, $options);
    } else {
        $today = time();
        $d_event = array();
        $evename = $DB->get_record('local_event_types', array('id' => $data->eventtypeid));
        $data->activityname = $evename->eventtypename;
        $data->description = $data->description['text'];
        $d_event['name'] = $data->eventtitle;
        $d_event['description'] = $data->description;
        $d_event['userid'] = $USER->id;
        $d_event['eventtype'] = $data->activityname;
        $d_event['timestart'] = $data->startdate;
        $d_event['timemodified'] = $today;
        $d_event['visible'] = $data->publish;
        $data->visble = $data->publish;
        $data->userid = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        if (isset($data->eventlevel) && $data->eventlevel == 1 && $data->eventtypeid == 1) {
            if (is_siteadmin($USER)) {
                $faculties = $DB->get_records('local_school', array('visible' => 1));
            } else {
                $faculties = $hierarchy->get_assignedschools();
            }

            foreach ($faculties as $faculty) {
                $programlist = $DB->get_records('local_program', array('schoolid' => $faculty->id, 'visible' => 1));
                foreach ($programlist as $program) {
                    $data->schoolid = $program->schoolid;
                    $data->programid = $program->id;
                    $eventid = $acalendar->event_add_instance($data);
                    $d_event['uuid'] = $eventid;
                    $acalendar->devent_add($d_event);
                }
            }

            $options = array('style' => 'notifysuccess');
            $hierarchy->set_confirmation(get_string('eventsuccess', 'local_academiccalendar'), $returnurl, $options);
        } elseif (isset($data->eventlevel) && $data->eventlevel == 2 && $data->eventtypeid == 1) {

            $programlist = $DB->get_records('local_program', array('schoolid' => $data->schoolid, 'visible' => 1));
            foreach ($programlist as $program) {
                $data->schoolid = $program->schoolid;
                $data->programid = $program->id;

                $eventid = $acalendar->event_add_instance($data);
                $d_event['uuid'] = $eventid;
                $acalendar->devent_add($d_event);
            }
            $options = array('style' => 'notifysuccess');
            $hierarchy->set_confirmation(get_string('eventsuccess', 'local_academiccalendar'), $returnurl, $options);
        } else {
            $eventid = $acalendar->event_add_instance($data);
            $d_event['uuid'] = $eventid;
            $acalendar->devent_add($d_event);
            $options = array('style' => 'notifysuccess');
            $hierarchy->set_confirmation(get_string('eventsuccess', 'local_academiccalendar'), $returnurl, $options);
        }
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_academiccalendar'));
/* ---Moodle 2.2 and onwards--- */
if ($id > 0) {
    $currenttab = 'edit';
} else {
    $currenttab = 'create';
}
require('tabs.php');
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    if ($id < 0)
        echo $OUTPUT->box(get_string('createeventdescription', 'local_academiccalendar'));
    else
        echo $OUTPUT->box(get_string('editeventdescription', 'local_academiccalendar'));
}
$createeventform->display();
echo $OUTPUT->footer();
