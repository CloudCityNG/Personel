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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage prefix and suffix
 * @copyright  2012 Hemalatha c arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/prefix/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/prefix/prefix_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$systemcontext = context_system::instance();
try {
//checking the id is greater than one...if its fetching content from table...used in edit purpose
    if ($id > 0) {
        if (!($tool = $DB->get_record('local_prefix_suffix', array('id' => $id)))) {
            $e = get_string('invalidtoolid', 'local_prefix');
            throw new Exception($e);
        }
        $update_cap = array('local/prefix:manage', 'local/prefix:update');
        if (!has_any_capability($update_cap, $systemcontext))
            throw new Exception('you dont have permissions');
    }
    else {
        $tool = new stdClass();
        $tool->id = -1;
    }

    $PAGE->set_url('/local/prefix/prefix2.php', array('id' => $id));

    $PAGE->set_context($systemcontext);
    $PAGE->set_pagelayout('admin');

//this is the return url 
    $returnurl = new moodle_url('/local/prefix/index.php', array('id' => $id));
    $currenturl = "{$CFG->wwwroot}/local/prefix/index.php";
    $strheading = get_string('prefix_suffix', 'local_prefix');

// calling prefix_Suffix class instance.....
    $prefix = prefix_suffix::getInstance();

    /* Start of delete code(prefix_suffix) */
    if ($delete) {
        $PAGE->url->param('delete', 1);
        if ($confirm and confirm_sesskey()) {
            $res = $prefix->pre_del_ins($id);
            $prefix->success_error_msg($res, 'success_del_prefix', 'error_del_prefix', $currenturl);
        }
        $strheading = get_string('deletefaculty', 'local_prefix');
        $PAGE->navbar->add($strheading);
        $PAGE->set_title($strheading);
        echo $OUTPUT->header();
        $currenttab = 'view';
        //$prefix->prefix_tabs($currenttab);
        echo $OUTPUT->heading($strheading);
        $yesurl = new moodle_url('/local/prefix/prefix2.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_prefix');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        echo $OUTPUT->footer();
        die;
    }
    /* End of delete (prefix_Suffix ) */

//-----code used to hide and show--------------------------------------------
    if ($visible != -1 and $id and confirm_sesskey()) {
        $res11 = $DB->set_field('local_prefix_suffix', 'visible', $visible, array('id' => $id));
        redirect($returnurl);
    }
//-------end of code hide and show-------------------------------------------

    $PAGE->navbar->add(get_string('prefix_suffix', 'local_prefix'), new moodle_url('/local/prefix/index.php'));
    $PAGE->navbar->add(get_string('createprefixsuffix', 'local_prefix'));
//$PAGE->set_title($strheading);
//echo '<h4>Prefix and Suffix Setting</h4>';

    $hier = new hierarchy();
    $schoolids = $prefix->check_loginuser_registrar_admin();

//----creating prefix suffix form object------------
    $preform = new prefix_form(null, array('temp' => $tool));
    $preform->set_data($tool);

    if ($preform->is_cancelled()) {
        redirect($returnurl);
    } else if ($data = $preform->get_data()) {
//print_object($data);
//This is the edit form condition
        if ($data->id > 0) {
// Update code
            $result = $prefix->pre_update_instance($data);
            $prefix->success_error_msg($result, 'success_up_prefix', 'error_up_prefix', $currenturl);
        } else {
            $res = $prefix->pre_add_instance($data);
            //----if sending already exists value it sends -3
            if ($res == false) {
                $confirm_msg = get_string('already', 'local_prefix');
                $hier->set_confirmation($confirm_msg, $currenturl);
            }
            $school = $DB->get_record_sql("select fullname from {local_prefix_suffix} as ps join {local_school} as s on s.id= ps.schoolid where ps.id=$res");
            $school->fullname;

            $prefix->success_error_msg($res, 'success_add_prefix', 'error_add_prefix', $currenturl, $school->fullname);
        }
    }
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('prefixs', 'local_prefix'));
    $currenttab = 'create_prefix';
    $prefix->prefix_tabs($currenttab, $id);

    echo $OUTPUT->box(get_string('prefix2', 'local_prefix'));

    $preform->display();
} catch (Exception $e) {
    echo $OUTPUT->header();
    echo $e->getMessage();
}
echo $OUTPUT->footer();
