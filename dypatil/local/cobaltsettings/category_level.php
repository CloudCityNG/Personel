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
require_once($CFG->dirroot . '/local/cobaltsettings/settings_form.php');
global $CFG, $DB;
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/cobaltsettings/category_level.php');
$hier1 = new hierarchy();
$global_ob = global_settings::getInstance();
$PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/school_settings.php'));
/* ---changing navigation sub links name--- */
$nav_subhead = ($id > 0 ? 'editentity_level_settings' : 'entity_level');
$PAGE->navbar->add(get_string($nav_subhead, 'local_cobaltsettings'));
$currenturl = "{$CFG->wwwroot}/local/cobaltsettings/view_categorylevel.php";
$returnurl = new moodle_url('/local/cobaltsettings/view_categorylevel.php');
/* ---Start code of delete the entitylevel settings code--- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $res = $DB->delete_records('local_cobalt_entitylevels', array('id' => $id));
        $global_ob->success_error_msg($res, 'success_delete_enitysettings', 'error_delete_entitysettings', $currenturl);
    }
    $strheading = get_string('delete_entitylevel', 'local_cobaltsettings');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/cobaltsettings/category_level.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $exists = $DB->get_record('local_cobalt_entitylevels', array('id' => $id));
    $dataexists = $DB->get_record('local_level_settings', array('entityid' => $exists->entityid, 'schoolid' => $exists->schoolid));
    if ($dataexists) {
        $entityname = $DB->get_record('local_cobalt_entity', array('id' => $dataexists->entityid));
        echo $confirm_msg = get_string('usedentity', 'local_cobaltsettings', $entityname->fullname);
        echo $OUTPUT->continue_button(new moodle_url('/local/cobaltsettings/view_categorylevel.php'));
    } else {
        $message = get_string('delconfirm_entity', 'local_cobaltsettings');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
/* ---End of delete entity level settings--- */


if ($id > 0) {
    if (!($tool = $DB->get_record('local_cobalt_entitylevels', array('id' => $id)))) {
        
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

echo $OUTPUT->header();

/* ---cobalt settings heading--- */
echo $OUTPUT->heading(get_string('cobalt_entitysettings', 'local_cobaltsettings'));


try {
    /* ---adding tabs using global_settings_tabs function--- */
    $currenttab = 'define_entitylevel';

    $global_ob->globalsettings_tabs($currenttab, 'entitysettings', $id);
    if ($id < 0)
        echo $OUTPUT->box(get_string('categorylevelsettingstabdes', 'local_cobaltsettings'));
    else
        echo $OUTPUT->box(get_string('editcategorylevelsettingstabdes', 'local_cobaltsettings'));
    /* ---checking if login user is registrar or admin--- */
    $schoolid = $global_ob->check_loginuser_registrar_admin();

    if (!empty($tool)) {
        $dataexists = $DB->get_record('local_level_settings', array('entityid' => $tool->entityid, 'schoolid' => $tool->schoolid));
        if ($dataexists) {
            $confirm_msg = get_string('already_entitylevel_settings', 'local_cobaltsettings');
            $hier1->set_confirmation($confirm_msg, $currenturl);
        }


        $tool->radioarray[level] = $tool->level;
    }


    $system = new categorylevel_settingsform(null, array('temp' => $tool));

    $system->set_data($tool);
    if ($system->is_cancelled()) {
        redirect($returnurl);
    }
    if ($data = $system->get_data()) {
        if ($data->id > 0) {
            /* ---updation part--- */

            $dataexists = $DB->get_record('local_level_settings', array('entityid' => $data->entityid, 'schoolid' => $data->schoolid));
            if ($dataexists) {
                $confirm_msg = get_string('already_entitylevel_settings', 'local_cobaltsettings');
                $hier1->set_confirmation($confirm_msg, $currenturl);
            } else {
                $res = $DB->update_record('local_cobalt_entitylevels', $data);
                $global_ob->success_error_msg($res, 'success_update_entitylevels', 'error_update_entitylevels', $currenturl);
            }

            redirect(new moodle_url('/local/cobaltsettings/view_categorylevel.php'));
        } else {
            /* ---insertion part--- */

            $sql = "select * from {$CFG->prefix}local_cobalt_entitylevels where schoolid=$data->schoolid and entityid=$data->entityid  and level='{$data->radioarray['level']}' ";
            $exists_data = $DB->get_record_sql($sql);
            if ($exists_data) {
                $confirm_msg = get_string('exists_categorylevel', 'local_cobaltsettings');
                $hier1->set_confirmation($confirm_msg, $currenturl);
            }
            $temp = new stdClass();
            $temp->schoolid = $data->schoolid;
            $temp->entityid = $data->entityid;
            $temp->level = $data->level;
            $res = $DB->insert_record('local_cobalt_entitylevels', $temp);
            $global_ob->success_error_msg($res, 'success_catlevel', 'error_catlevel', $currenturl);
        }
    }
    $system->display();
}
/* ---end of try block--- */ catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




