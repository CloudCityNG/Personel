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
 *
 * @package    Cost center
 * @subpackage assign managers
 * @copyright  2015 hemalatha c arun{hemalatha@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//require_once($CFG->dirroot.'/local/costcenter/lib.php');
class local_batches_renderer extends plugin_renderer_base {

    /**
     * @method treeview
     * @todo To add action buttons
     */
    function display_view($cohorts, $context) {
        global $DB, $CFG, $OUTPUT, $PAGE;

        $manager = has_capability('moodle/cohort:manage', $context);
        $canassign = has_capability('moodle/cohort:assign', $context);
        $data = array();
        foreach ($cohorts as $cohort) {
            // print_object($cohort);
            $line = array();
            $batchmapinfo = $DB->get_record('local_batch_map', array('batchid' => $cohort->id));
            $userfields = "SELECT COUNT(1) ";
            $userfrom = "FROM {user} u
                 JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
                WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1";

            $params = array('cohortid' => $cohort->id, 'guestid' => 1);

            $assigned_user_count = $DB->count_records_sql($userfields . $userfrom, $params);

            $batch_assigned_users = 'Users: ' . $assigned_user_count . '';

            $publishstatus = ($cohort->visible ? get_string('yes') : get_string('no'));
            $batchdates = '';

            $innercontent = '' . $batchdates . '<div  class="local_batches_indexmiddlepart" id="demo' . $cohort->id . '">
                            <ul>
                            <li><a href="' . $CFG->wwwroot . '/local/batches/ajax.php?page=1&cohortid=' . $cohort->id . '">' . $batch_assigned_users . '</a></li>
                            <li><a href="' . $CFG->wwwroot . '/local/batches/ajax.php?page=2&cohortid=' . $cohort->id . '">Assign Existing Users</a></li>
			    
                            <li><a>Publish : ' . $publishstatus . '</a></li>
			    <li><a> Year  : ' . $batchmapinfo->academicyear . '</a></li>
                            </ul>
                            </div>';
            //$costcenter_name = $DB->get_field('local_costcenter','fullname',array('id'=>$cohort->id));
            $innercontent .= html_writer::script('$(function() {
                                    $( "#demo' . $cohort->id . '" ).tabs({
                                    beforeLoad: function( event, ui ) {
                                    ui.jqXHR.fail(function() {
                                                ui.panel.html(
                                                            "Couldn\'t load this tab. We\'ll try to fix this as soon as possible. " +
                                                             "If this wouldn\'t be a demo." );
                                                });
                                    ui.panel.html("<center><img src=\"' . $CFG->wwwroot . '/local/batches/pix/ajax-loader.gif\" /></center>")
                                    },
                                    collapsible: true,
                                    active: false
                                    });});');

            //---- Display table to view assigned users-----
            $batch_assigned_users_view = "<div id='batch_assigned_users_view$cohort->id' class='dialog1' style='display:none;'>";
            $userfields = "SELECT u.* ";
            $assigned_users = $DB->get_records_sql($userfields . $userfrom, $params);
            if (empty($assigned_users)) {
                $batch_assigned_users_view .= $OUTPUT->heading(get_string("nousersassigned", 'local_batches'), 5);
            } else {
                $batch_assigned_users_view .= html_writer::table($this->batch_assigned_users($assigned_users, $cohort->id));
            }
            $batch_assigned_users_view .="</div>";
            //---- End of display table to view assigned users-----
            //---- Display form to assign users to batches-----
            $batch_assign_users_form = "<div id='batch_assign_users_form$cohort->id' class='dialog1' style='display:none;'>";
            $batch_assign_users_form .= $this->assign_users($cohort->id);
            $batch_assign_users_form .= '</div>';
            //---- End of display form to assign users to batches-----

            $first_inner = $this->to_get_firstpartview($cohort, $batchmapinfo);
            $third_inner = $this->to_get_thirdpartview($cohort);




            $line[] = $first_inner . $innercontent . $third_inner;
            //if(is_siteadmin())
            //$line[] = $cohort->name;
            //$line[] = '<h5 id="lp_heading">'.format_string($cohort->name, false).' / '.$cohort->name.'</h5>' .  $batch_assigned_users_view  . $batch_assign_users_form ;

            $data[] = $line;
        }

        $PAGE->requires->js('/local/batches/js/batchespagination.js');

        $table = new html_table();
        $table->head = array('');
        //$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size','centeralign source', 'centeralign action');
        $table->id = 'batchtable';
        $table->attributes['class'] = 'batchtable generaltable';
        $table->data = $data;
        //if(is_siteadmin()){
        echo html_writer::link(new moodle_url('/local/batches/bulk_enroll.php'), 'Bulk upload', array('id' => 'back_tp_course'));
        echo "<div class='batch_filter'></div>";
        //}
        echo html_writer::table($table);
        //#batchtable
    }

    function assign_users($id) {
        global $DB, $CFG, $USER, $CFG;
        $output = '';
        $noschoolprogram = 0;
        $batchmapinfo = $DB->get_record('local_batch_map', array('batchid' => $id));

        if ($batchmapinfo->schoolid == 0 || $batchmapinfo->programid == 0) {
            $output .= '<div class="alert alert-error">' . get_string('batchnotunderanysp', 'local_batches') . '</div>';
            $noschoolprogram = 1;
        }

        //$costcenter = new costcenter();
        //if (is_siteadmin()) {
        //    $costcenters = $DB->get_records('local_costcenter', array('visible' => 1));
        //} else {
        //    $costcenters = $costcenter->get_assignedcostcenters();
        //}
        //$count = sizeof($costcenters);

        $output .= '<form autocomplete="off" id="assign_users' . $id . '" action="index.php" method="post" accept-charset="utf-8" class="mform">';
        $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

        //if($count == 1){
        //    $batches = new local_batches($id);
        //    
        //    foreach ($costcenters as $costcenter) {
        //        $output .= "<input type='hidden' id='select_costcenteruser$id' name='cost_id' value='".$costcenter->id."' />";
        //        $users = $batches->get_costcenter_users($costcenter->id);
        //    }
        //    
        //    $output .= '<fieldset class="hidden"><div><div id="fitem_id_t_id[]" class="fitem fitem_fselect "><div class="fitemtitle"><label for="id_u_id[]">Select users</label></div><div class="felement ftext"><select name="user_id[]" id="select_users'.$id.'" size="10" multiple onchange="activate_submit(\'assign_users'.$id.'\',\'assign-user-select'.$id.'\')" class="assign-user-select'.$id.'">'; 
        //    foreach($users as $user){
        //        $output .= "<option value='".$user->id."'>".fullname($user)."</option>";
        //    }
        //    $output .= "</select></div></div></div></fieldset>";
        //} else {
        //    $output .= "<fieldset class='hidden'><div><div id='fitem_id_t_id[]' class='fitem fitem_fselect'>";
        //    $output .=  "<div class='fitemtitle'><label for='id_u_id[]'>Select Cost Center</label></div>
        //            <div class='felement ftext'><select name='cost_id' id='select_costcenteruser$id' size='10' onchange='display_users(\"$id\", \"$CFG->wwwroot\")' class='assign-cost-select".$id."'>"; 
        //    foreach ($costcenters as $costcenter) {
        //        $output .= '<option value='.$costcenter->id.'>'.$costcenter->fullname.'</option>';
        //    }
        //    $output .= "</select></div></div></div></fieldset>";
        //$costcenterid = $DB->get_field('local_costcenter_batch','costcenterid',array('batchid'=>$id));


        if (!$noschoolprogram) {
           $sql1 = "SELECT user.*
        FROM {local_userdata} AS udata 
        JOIN {user} AS user ON user.id = udata.userid   
        AND user.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = {$id}) 
        AND udata.schoolid= {$batchmapinfo->schoolid} AND udata.programid= {$batchmapinfo->programid} AND udata.curriculumid= {$batchmapinfo->curriculumid} AND udata.batchid IS NULL     
        AND user.deleted <> 1 AND user.confirmed = 1 AND user.id <> 1
        UNION
        SELECT user.*
        FROM {local_userdata} AS udata 
        JOIN {user} AS user ON user.id = udata.userid   
        AND user.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = {$id}) 
        AND udata.schoolid={$batchmapinfo->schoolid} AND udata.programid != {$batchmapinfo->programid}
        AND user.deleted <> 1 AND user.confirmed = 1 AND user.id <> 1
           ";
            $users = $DB->get_records_sql("$sql1");
        } else {
            $users = array(get_string('nousersavailable', 'local_batches'));
        }
        $output .= '<fieldset class="hidden"><div><div id="fitem_id_t_id[]" class="fitem fitem_fselect "><div class="fitemtitle"><label for="id_u_id[]">Select users</label></div>'
                . '<div class="felement ftext">'
                . '<select name="user_id[]" id="select_users' . $id . '" size="10" multiple onchange="activate_submit(\'assign_users' . $id . '\',\'assign-user-select' . $id . '\')" class="assign-user-select' . $id . '">';
        //content will be added through ajax here
        foreach ($users as $user) {
            $output .="<option value=$user->id>" . fullname($user) . "</option>";
        }
        $output .= "</select></div></div></div></fieldset>";
        //}
        $output .= '<input type="hidden" name="id" value=' . $id . ' />';
        $output .= '<input type="hidden" name="moveto" value=' . $id . ' />';
        $output .= '<fieldset class="hidden"><div><div id="fitem_id_submitbutton" class="fitem fitem_actionbuttons fitem_fsubmit"><div class="felement fsubmit"><input type="submit" id="movetoid' . $id . '" class="form-submit" disabled value="Assign users" /></div></div>';
        $output .= '</div></fieldset></form>';
        $output .= '<div style="padding-left:20px;"><a href="' . $CFG->wwwroot . '/local/batches/assignusers.php?id=' . $id . '">Add Multiple Users</a></div>';
        //}
        return $output;
    }

    function batch_assigned_users($users, $cohortid) {
        global $DB, $PAGE, $OUTPUT;
        $table = new html_table();
        $table->head = array(get_string('rollid', 'local_batches'), get_string('username'), get_string('email'), get_string('action'));
        $table->id = "batch_user_display$cohortid";
        $table->attributes = array('style' => 'margin:0 auto');
        $table->align = array('left', 'left', 'left');
        $table->width = '90%';

        foreach ($users as $user) {
            $userdatainfo = $DB->get_record('local_userdata', array('userid' => $user->id ,'batchid'=>$cohortid));            
            $row = array();            
            $serviceid =(isset($userdatainfo->serviceid)?$userdatainfo->serviceid: '-------');
            $row[] = $serviceid;
            $row[] = fullname($user);
            $row[] = $user->email;
            $delete = html_writer::link(new moodle_url('/local/batches/index.php', array('id' => $cohortid, 'userid' => $user->id, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('unassign', 'local_curriculum'), 'alt' => get_string('unassign', 'local_curriculum'), 'class' => 'iconsmall')), array('id' => 'deleteuser_confirm' . $user->id . $cohortid));
            $PAGE->requires->event_handler('#deleteuser_confirm' . $user->id . $cohortid . '', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' => get_string('unassignuserconfirmation', 'local_batches'), 'callbackargs' => array('id' => $cohortid, 'extraparams' => '&confirm=1&unassign=1&userid=' . $user->id . '')));
            $row[] = $delete;
            $table->data[] = $row;
        }


        echo html_writer::script("
                $(document).ready( function() {
                    $('#batch_user_display$cohortid').dataTable( {
                      'iDisplayLength': 5,
                    'aLengthMenu': [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
                    'searching': false,
                    'aaSorting': [],
                  
                    'emptyTable': 'No data available in table',
                    'info': '',        
                    'zeroRecords': 'No matching records found',
                    'oLanguage': {
                        'oPaginate': {
                          'sFirst': '',
                          'sLast': ' ',
                          'sNext': ' >> ',
                          'bStateSave': true,
                          'sPrevious': ' << '
                           }    
	                }
                  
                    } );
                  } );
                ");

        return $table;
    }

    function to_get_firstpartview($cohort, $batchmapinfo) {
        global $DB, $PAGE, $OUTPUT;
        $first_inner = '';
        $buttons = array();
        if (empty($cohort->component)) {

            $buttons[] = html_writer::link(new moodle_url('/local/batches/index.php', array('id' => $cohort->id)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            $buttons[] = html_writer::link(new moodle_url('/local/batches/index.php', array('id' => $cohort->id, 'delete' => 1)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')), array('id' => 'batchdeleteconfirm' . $cohort->id . ''));
            $PAGE->requires->event_handler('#batchdeleteconfirm' . $cohort->id . '', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' => $message = get_string('batch_delconfirm', 'local_batches', format_string($cohort->name)), 'callbackargs' => array('id' => $cohort->id)));
        }
        $buttons_string = implode(' ', $buttons);


        $first_inner .='<h5 id="lp_heading">' . format_string($cohort->name, false) . '</h5><span id="lp_actions">' . $buttons_string . '</span>';



        $first_inner .= html_writer::start_tag('ul', array('class' => 'local_batches_first_innerclassdetail'));
        if (isset($batchmapinfo->schoolid) && $batchmapinfo->schoolid > 0)
            $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $batchmapinfo->schoolid));
        else
            $schoolname = '---------';

        if (isset($batchmapinfo->programid) && $batchmapinfo->programid > 0)
            $programname = $DB->get_field('local_program', 'fullname', array('id' => $batchmapinfo->programid));
        else
            $programname = '---------';

        if (isset($batchmapinfo->curriculumid) && $batchmapinfo->curriculumid > 0)
            $curriculumname = $DB->get_field('local_curriculum', 'fullname', array('id' => $batchmapinfo->curriculumid));
        else
            $curriculumname = '---------';


        $first_inner .= '<li class="item_gray">' . get_string('school', 'block_universitystructure') . ' :<b>' . $schoolname . '</b></li> ';
        $first_inner .= '<li class="item_gray">' . get_string('program', 'local_programs') . ' :<b>' . $programname . '</b></li> ';
        $first_inner .= '<li class="item_gray">' . get_string('curriculum', 'local_curriculum') . ' :<b>' . $curriculumname . '</b></li> ';

        $first_inner .= html_writer::end_tag('ul');
        return $first_inner;
    }

// end of function

    function to_get_thirdpartview($cohort) {
        global $DB, $PAGE, $OUTPUT;


        //   displaying buttons
        $options = array(
            array('link' => new moodle_url('/local/batches/bulk_enroll.php', array('bid' => $cohort->id, 'sesskey' => sesskey(), 'mode' => 'new')), 'string' => get_string('uploadnewstudentstobatch', 'local_batches')),
            array('link' => new moodle_url('/local/batches/bulk_enroll.php', array('bid' => $cohort->id, 'sesskey' => sesskey(), 'mode' => 'exists')), 'string' => get_string('uploadexiststudentstobatch', 'local_batches'))
        );
        $menulist = array();
        foreach ($options as $types) {
            $menulist[] = '<button>' . html_writer::link($types['link'], $types['string']) . '</button>';
            $menulist[] = '<hr />';
        }
        // Remove the last element (will be an HR)
        array_pop($menulist);

        // Display the content as a list
        $third_inner = html_writer::alist($menulist, array('class' => 'third_inner cl_buttonrow'), 'ul');
        //  $third_inner .= "<span id='classes_thirdinner'>$clas->semestername</span>";
        return $third_inner;
    }

// end of function
}

// end of class