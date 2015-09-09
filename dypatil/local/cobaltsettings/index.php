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
 * @subpackage  providing  cobalt settings
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/schoolsettings_form.php');
global $CFG, $DB, $USER;
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/cobaltsettings/index.php');
$PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/index.php'));
echo $OUTPUT->header();
/* ---global settings heading--- */
echo $OUTPUT->heading(get_string('cobalt_settings', 'local_cobaltsettings'));
$currenturl = "{$CFG->wwwroot}/local/cobaltsettings/index.php";
$hier1 = new hierarchy();
/* ---instance of school settings form--- */
$system = new schoolsettings_form();

try {
    /* ---adding tabs using cobalt_settings_tabs function--- */
    $currenttab = 'system';
    $global_ob = global_settings::getInstance();
    $global_ob->globalsettings_tabs($currenttab);
    /* ---description of the cobalt level settings table--- */
    echo $OUTPUT->box(get_string('des_slsettings', 'local_cobaltsettings'));
    /* ---checking if login user is registrar or admin--- */
    $schoolid = $global_ob->check_loginuser_registrar_admin();
    /* ---used to fectching exists schoolids in cobalt settings table--- */
    $tool = $DB->get_records('local_school_settings');
    $temp1_cond = array_chunk($tool, 4);
    $exists_schoolids[] = 0;
    if (!empty($temp1_cond)) {
        foreach ($temp1_cond as $tc)
            $exists_schoolids[] = $tc[0]->schoolid;
    }
    /* ---feteching form data--- */
    $form_data = ($_POST);
    if ($form_data) {
        $temp_set = array('batch', 'prefix_suffix', 'onlineapp', 'certificate');
        $temp = new stdClass();
        /* ---if schoolid is exists..it moves to updation part else move to insertion part--- */
        if (in_array($form_data['schoolid'], $exists_schoolids)) {
            foreach ($temp_set as $k => $v) {
                /* ---it fectch the id...which we going to update--- */
                $sql = "select id from {$CFG->prefix}local_school_settings where schoolid= $form_data[schoolid] and name='$v'";
                $update_id = $DB->get_record_sql($sql);
                $temp->id = $update_id->id;
                $temp->schoolid = $form_data['schoolid'];
                $temp->name = $v;
                if (empty($form_data[$v]))
                    $form_data[$v] = 0;
                $temp->value = $form_data[$v];
                $DB->update_record('local_school_settings', $temp);
            }
        }
        else {
            /* ---insertion part--- */
            foreach ($temp_set as $k => $v) {
                $temp->schoolid = $form_data['schoolid'];
                $temp->name = $v;
                if (empty($form_data[$v]))
                    $form_data[$v] = 0;
                $temp->value = $form_data[$v];
                $DB->insert_record('local_school_settings', $temp);
            }
        }
        /* ---end of else--- */
        $confirm_msg = get_string('suc_update', 'local_cobaltsettings');
        $hier1->set_confirmation($confirm_msg, $currenturl);
    }
    /* ---end of main if--- */

    $registrar_asigned_school = $schoolid;
    foreach ($registrar_asigned_school as $r_s) {
        $line = array();
        $school = $DB->get_record('local_school', array('id' => $r_s->id));
        $line[] = $school->fullname;
        /* ---used to fetch existing school settings--- */
        $exists_set = $DB->get_records('local_school_settings', array('schoolid' => $school->id));
        $bcheck = '';
        $pcheck = '';
        $ocheck = '';
        $ccheck = '';
        foreach ($exists_set as $exists) {
            if ($exists->name == 'batch')
                $bcheck = ($exists->value == 1) ? 'checked' : '';
            if ($exists->name == 'prefix_suffix')
                $pcheck = ($exists->value == 1) ? 'checked' : '';
            if ($exists->name == 'onlineapp')
                $ocheck = ($exists->value == 1) ? 'checked' : '';
            if ($exists->name == 'certificate')
                $ccheck = ($exists->value == 1) ? 'checked' : '';
        }
        $line[] = '<form  method="post"><input type="hidden" name="schoolid" value=' . $school->id . '>' .
                '<input type="checkbox" name="prefix_suffix" ' . $pcheck . ' value="1" >';
        $line[] = '<input type="checkbox" name="onlineapp" ' . $ocheck . ' value="1">';
        $line[] = '<input type="checkbox" name="certificate" ' . $ccheck . ' value="1" >';
        $line[] = '<input type="submit" value="Save Changes" ></form>';
        $data[] = $line;
    }
    $PAGE->requires->js('/local/cobaltsettings/js/pagi.js');
    $table = new html_table();
    $table->id = 'setting';
    $table->head = array(
        get_string('school', 'local_cobaltsettings'),
        get_string('prefix', 'local_cobaltsettings'),
        get_string('online', 'local_cobaltsettings'),
        get_string('certificate', 'local_cobaltsettings'),
        get_string('action', 'local_cobaltsettings'));
    $table->size = array('25%', '15%', '15%', '15%', '15%');
    $table->align = array('left', 'left', 'left', 'left', 'center');
    $table->width = '100%';
    $table->data = $data;
    echo html_writer::table($table);
}
/* ---end of try block--- */ catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




