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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage  providing  global settings
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/gpasettings_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
global $CFG, $DB;
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
$returnurl = new moodle_url('/local/cobaltsettings/view_gpasettings.php');
require_login();


try {
    /* ---checking the id is greater than one...if its fetching content from table...used in edit purpose--- */
    if ($id > 0) {
        if (!($tool = $DB->get_record('local_cobalt_gpasettings', array('id' => $id)))) {
            $e = get_string('invalidtoolid', 'local_prefix');
            throw new Exception($e);
        }
    } else {
        $tool = new stdClass();
        $tool->id = -1;
    }
    $PAGE->set_url('/local/cobaltsettings/gpa_settings.php');
    $PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/school_settings.php'));
    if ($delete == 0) {
        $nav_subhead = ($id > 0 ? 'editgpasettings' : 'gpasettings');
        $PAGE->navbar->add(get_string($nav_subhead, 'local_cobaltsettings'));
    }
    $currenturl = "{$CFG->wwwroot}/local/cobaltsettings/view_gpasettings.php";
    $hier1 = new hierarchy();
    $global_ob = global_settings::getInstance();

  
    /* --- Start of delete code semester gpa settings--- */
    if ($delete) {
        $PAGE->url->param('delete', 1);
        if ($confirm and confirm_sesskey()) {
            $res = $DB->delete_records('local_cobalt_gpasettings', array('id' => $id));
            $global_ob->success_error_msg($res, 'success_del_semgpa', 'error_del_semgpa', $currenturl);
        }
        $strheading = get_string('delete_gpasettings', 'local_cobaltsettings');
        $PAGE->navbar->add($strheading);
        $PAGE->set_title($strheading);
        echo $OUTPUT->header();
        $currenttab = 'gpa_settings';
        $global_ob->globalsettings_tabs($currenttab, 'gpa_settings', $id);
        echo $OUTPUT->heading($strheading);
        $yesurl = new moodle_url('/local/cobaltsettings/gpa_settings.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_cobaltsettings');
        echo $OUTPUT->confirm($message, $yesurl, $currenturl);
        echo $OUTPUT->footer();
        die;
    }
    /* ---End of delete semester gpa settings--- */
    echo $OUTPUT->header();
    /* ----cobalt settings heading--- */
    echo $OUTPUT->heading(get_string('gpa/cgpa_settings', 'local_cobaltsettings'));
    /* ---checking if login user is registrar or admin--- */
    $school_id = $global_ob->check_loginuser_registrar_admin();
    
    $system = new gpasettings_form(null, array('gpa' => 1, 'stu' => 0));
    /* ---adding tabs using global_settings_tabs function--- */
    $currenttab = 'gpa_settings';
    $global_ob->globalsettings_tabs($currenttab, 'gpasettings', $id);
    if ($id < 0)
        echo $OUTPUT->box(get_string('gpasettingstabdes', 'local_cobaltsettings'));
    else
        echo $OUTPUT->box(get_string('editgpasettingstabdes', 'local_cobaltsettings'));


    $system->set_data($tool);
    if ($system->is_cancelled()) {
        redirect($returnurl);
    }

    if ($data = $system->get_data()) {
        if ($data->id > 0) {

            $data->semid = -1;
            $data->timemodified = time();
            $res = $DB->update_record('local_cobalt_gpasettings', $data);
            $global_ob->success_error_msg($res, 'success_update_gpa', 'error_update_gpa', $currenturl);
        } else {
            $exists_data = $DB->get_record('local_cobalt_gpasettings', array('schoolid' => $data->schoolid, 'sub_entityid' => $data->sub_entityid));
            if ($exists_data) {
                $confirm_msg = get_string('already_sem_gpa', 'local_cobaltsettings');
                $hier1->set_confirmation($confirm_msg, $currenturl);
            }

            $temp = new stdClass();
            $temp->schoolid = $data->schoolid;
            $temp->sub_entityid = $data->sub_entityid;
            $temp->semid = -1;
            $temp->gpa = $data->gpa;
            $temp->cgpa = $data->cgpa;
            $temp->probationgpa = $data->probationgpa;
            $temp->dismissalgpa = $data->dismissalgpa;
            $temp->timecreated = time();
            $temp->usermodified = $USER->id;

            $res = $DB->insert_record('local_cobalt_gpasettings', $temp);
            $global_ob->success_error_msg($res, 'success_add_gpa', 'error_add_gpa', $currenturl);
        }
    }

    $system->display();
}
/* ---end of try block---- */ 
catch (schoolnotfound_exception $e) {
    echo '<p class="errormessage">'.$e->getMessage().$OUTPUT->continue_button($e->link).'</p>';
}
catch (notassignedschool_exception $e) {
    echo '<p class="errormessage">'.$e->getMessage().$OUTPUT->continue_button($e->link).'</p>';
}
catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




