<?php
require_once('../../config.php');
global $CFG,$DB;
require_once($CFG->dirroot.'/local/batches/lib.php');
$page = required_param('page',PARAM_INT);
$cohortid = required_param('cohortid',PARAM_INT);
$PAGE->set_context(context_system::instance());
$output = $PAGE->get_renderer('local_batches');
switch($page){
    case 1:
$userfields = "SELECT u.* ";
 $userfrom = "FROM {user} u
                 JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
                WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1";
                $params = array('cohortid'=>$cohortid, 'guestid'=>1);
                $assigned_users = $DB->get_records_sql($userfields . $userfrom, $params);
            if(empty($assigned_users)){
              echo  $batch_assigned_users_view = $output->heading(get_string("nousersassigned", 'local_batches'),5);
            } else {
            echo    $batch_assigned_users_view = html_writer::table($output->batch_assigned_users($assigned_users, $cohortid));
            }
    break;
    case 2:
      echo $output->assign_users($cohortid);
    break;

   
}
echo html_writer::script('    $(".assign-course-select"+'.$cohortid.').select2();
    $(".assign-cost-select"+'.$cohortid.').select2();
    $(".assign-user-select"+'.$cohortid.').select2();');