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
 * @subpackage audit trail
 * @copyright  2012 Hemalatha c arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/audittrail/audittrail_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
global $DB, $USER, $CFG;
$PAGE->set_url('/local/audittrail/index.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$returnurl = new moodle_url('/local/audittrail/index.php');
$strheading = get_string('reason', 'local_audittrail');
$PAGE->navbar->add(get_string('pluginname', 'local_audittrail'), new moodle_url('/local/audittrail/index.php'));
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
/* ----creating audit trail form object----- */
$audit = new audittrail_form(null);
if ($audit->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $audit->get_data()) {

    $auditid = 1;
    $data->auditid = $auditid;
    $res_id = $DB->insert_record('local_audit_reason', $data);
    $audit_log = new stdClass();
    $audit_log->auditid = $auditid;
    $audit_log->reasonid = $res_id;
    $audit_log->userid = $USER->id;
    $syst =context_system::instance();
    $role_id = $DB->get_record('role_assignments', array('userid' => $USER->id, 'contextid' => $syst->id));
    if (!empty($role_id)) {
        $audit_log->time = time();
        $audit_log->auditid = $auditid;
        $audit_log->roleid = $role_id->roleid;
        $res = $DB->insert_record('local_audit_trail_log', $audit_log);
    } else {
        echo get_string('role_miss', 'local_audittrail');
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('audittrail', 'local_audittrail'));
$audit->display();
echo $OUTPUT->footer();
