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
 * @subpackage  team manager
 * @copyright  2015 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/teammanager/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/local/teammanager/employeeformlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$employeeid = optional_param('empid', 0, PARAM_INT);
 $teammanagerid = optional_param('tmid', 0, PARAM_INT);
$form_ccid = optional_param('costcenter', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$sid = optional_param('sid', 0, PARAM_INT);
$fromajax = optional_param('frajax', 0, PARAM_INT);
global $CFG, $DB, $USER, $PAGE;



if ($fromajax) {

    // for ajax response
    $costcenteridaj = $fromajax;
    $smanager = assign_smanager::getInstance();
    $smanager->tm_include_jqueryfiles();
    $employeelist = $smanager->tm_get_employeelist($USER->id, null, true, false, 'select employee(who become a teammanager)', $costcenteridaj);
   $employeelist['js'] =  '<script type="text/javascript"> $(".js-example-basic-single").select2();</script>';
    $emplist = json_encode($employeelist);
    echo $emplist;
} else {
    ob_start("ob_gzhandler");
    header("Content-type: text/javascript");
    $systemcontext = context_system::instance();
    $PAGE->set_pagelayout('admin');
    /* ---check the context level of the user and check weather the user is login to the system or not--- */
    $PAGE->set_context($systemcontext);
    require_login();
    $PAGE->requires->css('/blocks/learning_plan/css/jquery.dataTables.css');
    $PAGE->requires->css('/local/teammanager/css/select2.min.css');

    $PAGE->set_url('/local/teammanager/index.php?rid=1');
    $PAGE->set_heading($SITE->fullname);
    $PAGE->navbar->add(get_string('pluginname', 'local_teammanager'), new moodle_url('/local/teammanager/index.php'));
    $PAGE->navbar->add(get_string('assignteammanager', 'local_teammanager'));

    $smanager = assign_smanager::getInstance();
    $smanager->tm_include_jqueryfiles();
    $output = $PAGE->get_renderer('local_teammanager');

    echo $output->header();

    
    $reportingmanagerinfo = $smanager->tm_is_manager($USER->id);
    if (!is_siteadmin($USER->id) && empty($reportingmanagerinfo)) {
        print_error('You dont have permissions, Contact site admin');
    } else {
        $rmanager_costcenterid = 0;
        if (is_siteadmin()) {
            $adminview = true;
        } else {
            $reportingmanagerid = $reportingmanagerinfo->userid;
            $rmanager_costcenterid = $reportingmanagerinfo->costcenterid;
        }
    }

    /* unassigning employee from team */
    if ($unassign) {
        if ($unassign and confirm_sesskey()) {
            $res = $smanager->tm_unassign_employee_fromteam($employeeid, $teammanagerid);
            $smanager->tm_success_failure_notification($res, 'unassignsuccess', 'unassignfailure');
        }
    }
    /* End of unassigning */

    echo $output->heading(get_string('pluginname', 'local_teammanager'));



//$assignee_ob->assignmentor_tabs($currenttab);
    if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
        echo $output->box(get_string('descview_assigningteammanager', 'local_teammanager'));
    }


    $teammember = new class_teammembers_selector('removeselect', array('teammanagerid' => $teammanagerid));
    $non_teammember = new class_non_teammembers_selector('addselect', array('teammanagerid' => $teammanagerid,'costcenterid'=> $form_ccid));
    if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
        // get lie list of selected users
        $data = $non_teammember->get_selected_users();
        $response = $smanager->tm_mapping_employee_team($data, $teammanagerid);
        $smanager->tm_success_failure_notification($response, 'mappingemployeesuccess', 'mappingemployeefailure');
        $teammember->invalidate_selected_users();
        $non_teammember->invalidate_selected_users();
        //}
    }
    if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
        $unassigning = $teammember->get_selected_users();
        if (!empty($unassigning)) {
            foreach ($unassigning as $user) {
                $res = $smanager->tm_unassign_employee_fromteam($user->id, $teammanagerid);
            }
            $smanager->tm_success_failure_notification($res, 'unassignsuccess', 'unassignfailure');
            $teammember->invalidate_selected_users();
            $non_teammember->invalidate_selected_users();
        }
    }
    $record_existence = $smanager->tm_records_exists_ornot($rmanager_costcenterid);

    if ($teammanagerid || empty($record_existence))
        $collapse = 0;
    else
        $collapse = 1;

    print_collapsible_region_start('', 'assignemployee_form', get_string('assignemployee_formlabel', 'local_teammanager'), false, $collapse);
    $smanager->tm_assign_employee_select_form($teammanagerid, $form_ccid );
    $smanager->tm_assign_employee_form($teammanagerid, $form_ccid);
    print_collapsible_region_end();
    
    if ($rmanager_costcenterid)
        $tmobject = new teammanager($rmanager_costcenterid);
    else
        $tmobject = new teammanager(null, null, $adminview);

    echo $output->render($tmobject);

    echo $output->footer();
} // endof else