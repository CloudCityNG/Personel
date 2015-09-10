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
/* Learning Plan Block
 * This plugin serves as a database and plan for all learning activities in the organziation, 
 * where such activities are organized for a more structured learning program.
 * @package blocks
 * @author: Azmat Ullah, Talha Noor
 * @date: 20-Aug-2014
 * @copyright  Copyrights � 2012 - 2014 | 3i Logic (Pvt) Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->dirroot.'/local/batches/assignuserlib.php');
require_once($CFG->dirroot.'/local/batches/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
//require_once($CFG->dirroot . '/cohort/locallib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');

$id = required_param('id', PARAM_INT);
$costid = optional_param('costid', 0, PARAM_INT);
global $OUTPUT;

require_login();

$cohort = $DB->get_record('cohort', array('id'=>$id), '*', MUST_EXIST);
$context = context::instance_by_id($cohort->contextid, MUST_EXIST);

require_capability('moodle/cohort:assign', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/batches/assignusers.php', array('id'=>$id));
$PAGE->set_pagelayout('admin');

$returnurl = new moodle_url('/local/batches/index.php');

$batches = new local_batches($id);

if (!empty($cohort->component)) {
    // We can not manually edit cohorts that were created by external systems, sorry.
    redirect($returnurl);
}

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

$PAGE->navbar->add(get_string('pluginname', 'local_batches'), new moodle_url('/local/batches/index.php'));
$PAGE->navbar->add(get_string('assignusers', 'local_batches'));

$PAGE->set_title(get_string('pluginname', 'local_batches'));
$PAGE->set_heading($cohort->name);
$PAGE->requires->jquery();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignto', 'local_batches', format_string($cohort->name)), 4);

$batchmapinfo=$DB->get_record('local_batch_map',array('batchid'=>$id));
$scool_prginfo = new stdclass();
if(isset($batchmapinfo->schoolid) && $batchmapinfo->schoolid>0 ){
$schoolname =$DB->get_field('local_school','fullname',array('id'=>$batchmapinfo->schoolid));
$scool_prginfo->schoolname= $schoolname;
}
if(isset($batchmapinfo->programid) && $batchmapinfo->programid>0 ){
$programname =$DB->get_field('local_program','fullname',array('id'=>$batchmapinfo->programid));
$scool_prginfo->programname= $programname;
}

if(($batchmapinfo->schoolid > 0 )&& ($batchmapinfo->programid > 0 ) )
echo $OUTPUT->notification(get_string('enrollexistinguser_warnings', 'local_batches'), $scool_prginfo);
else
echo $OUTPUT->notification(get_string('enrollexistinguser_war', 'local_batches'));

echo html_writer::link(new moodle_url('/local/batches/index.php'),'<button>'.get_string('backtobatches', 'local_batches').'</button>',array('id'=>'back_to_local_batches'));

//$costcenter = new costcenter();
//if (is_siteadmin()) {
//    $costcenters = $DB->get_records('local_costcenter', array('visible' => 1));
//} else {
//    $costcenters = $costcenter->get_assignedcostcenters();
//}
//$centers = array('---Select---');
//foreach($costcenters as $costcenter){
//    $centers[$costcenter->id] = $costcenter->fullname;
//}
//if(is_siteadmin()){
//echo '<div style="text-align: center;margin-bottom: 20px;">';
//$sch = new single_select(new moodle_url('/local/batches/assignusers.php', array('id'=>$id)), 'costid', $centers, $costid, null);
//$sch->set_label(get_string('costcenterid', 'local_costcenter') . ':');
//echo $OUTPUT->render($sch);
//echo '</div>';
//}

// Get the user_selector we will need.
$potentialuserselector = new batch_candidate_selector('addselect', array( 'cohortid'=>$cohort->id, 'accesscontext'=>$context));
$existinguserselector = new batch_existing_selector('removeselect', array('cohortid'=>$cohort->id, 'accesscontext'=>$context));

// Process incoming user assignments to the cohort

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    
    print_object($userstoassign);
    
    if (!empty($userstoassign)) {

       // foreach ($userstoassign as $adduser) {
            $batches->assign_existing_userto_batches_from_assignuser_interface($userstoassign,$cohort->id);
            
            
         //   cohort_add_member($cohort->id, $adduser->id);
     //   }
        //$batches->enrol_course();

        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Process removing user assignments to the cohort
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $existinguserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            cohort_remove_member($cohort->id, $removeuser->id);
           // $batches->unenrol_user($removeuser->id);
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Print the form.
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
  <input type="hidden" name="costid" value="<?php echo $costid; ?>" />

  <table summary="" class="generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td width="40%" id="existingcell">
          <p><label for="removeselect"><?php print_string('currentusers', 'cohort'); ?></label></p>
          <input type="button" id="select_remove_all" name="select_remove_all" value="Select all">
          <input type="button" id="select_remove_none" name="select_remove_none" value="Select none">
          <?php $existinguserselector->display() ?>
      </td>
      <td width="20%" id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.s(get_string('add')); ?>" title="<?php p(get_string('add')); ?>" /><br />
          </div>

          <div id="removecontrols">
              <input name="remove" id="remove" type="submit" value="<?php echo s(get_string('remove')).'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php p(get_string('remove')); ?>" />
          </div>
      </td>
      <td width="40%" id="potentialcell">
          <p><label for="addselect"><?php print_string('potusers', 'cohort'); ?></label></p>
               <input type="button" id="select_add_all" name="select_add_all" value="Select all">
         <input type="button" id="select_add_none" name="select_add_none" value="Select none">
          <?php $potentialuserselector->display() ?>
      </td>
    </tr>
    <tr><td colspan="3" id='backcell'>
      <input type="submit" name="cancel" value="<?php p(get_string('backtobatches', 'local_batches')); ?>" />
    </td></tr>
  </table>
</div></form>

<?php

echo $OUTPUT->footer();
?>
<script type="text/javascript">
    $('#select_add_all').click(function() {
        $('#addselect option').prop('selected', true);
    });
    $('#select_remove_all').click(function() {
        $('#removeselect option').prop('selected', true);
    });
    $('#select_add_none').click(function() {
        $('#addselect option').prop('selected', false);
    });
    $('#select_remove_none').click(function() {
        $('#removeselect option').prop('selected', false);
    });
</script>