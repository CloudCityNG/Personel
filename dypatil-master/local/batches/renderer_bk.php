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
 * @copyright  2015 Vinod {naveen@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//require_once($CFG->dirroot.'/local/costcenter/lib.php');
class local_batches_renderer extends plugin_renderer_base {

    /**
     * @method treeview
     * @todo To add action buttons
     */
    function display_view($cohorts, $context){
        global $DB, $CFG, $OUTPUT, $PAGE;
        
        $manager = has_capability('moodle/cohort:manage', $context);
        $canassign = has_capability('moodle/cohort:assign', $context);
        if (!$manager) {
            require_capability('moodle/cohort:view', $context);
        }

        $data = array();
        foreach($cohorts as $cohort) {
            $line = array();
//$costcenter = $DB->get_record_sql("SELECT lc.*,lcb.* FROM {local_costcenter_batch} lcb, {local_costcenter} lc WHERE lc.id = lcb.costcenterid AND lcb.batchid = $cohort->id");            
//	        $coursefields = "SELECT COUNT(1) ";
//            $coursefrom = " FROM {local_batch_courses} AS bc
//                        JOIN {course} AS c ON c.id = bc.courseid
//                        WHERE bc.batchid = {$cohort->id} AND c.visible = 1";
//            $assigned_course_count = $DB->count_records_sql($coursefields . $coursefrom);
//            
            $userfields = "SELECT COUNT(1) ";
            $userfrom = "FROM {user} u
                 JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
                WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1";
		
            $params = array('cohortid'=>$cohort->id, 'guestid'=>1);
	    
           $assigned_user_count = $DB->count_records_sql($userfields . $userfrom, $params);
           
            $batch_assigned_users = 'Users: '. $assigned_user_count.'';
//            $batch_assign_users = get_string('assign_learningplan_user','block_learning_plan');
//            $batch_assigned_courses = 'Courses: '. $assigned_course_count.'';
//            $batch_assign_courses = get_string('add_training','block_learning_plan');
//			if($costcenter->startdate!=0 || $costcenter->enddate!=0)
//			$batchdates ='<span class="batchdates">';
//			if($costcenter->startdate!=0)
//			$batchdates .= 'From '.date("d M Y",$costcenter->startdate).' <b>';
//			if($costcenter->startdate==0 && $costcenter->enddate!=0)
//			$batchdates .='</b> Upto '.date("d M Y",$costcenter->enddate).'';
//			elseif($costcenter->enddate!=0)
//			$batchdates .='</b> To '.date("d M Y",$costcenter->enddate).'';
//             if($costcenter->startdate!=0 || $costcenter->enddate!=0)
//			 $batchdates .='</span>';
//			 else
//			 $batchdates ='';

                   //         <li><a href="' . $CFG->wwwroot . '/local/batches/ajax.php?page=3&cohortid=' . $cohort->id . '">0</a></li>
		//	    <li><a href="' . $CFG->wwwroot . '/local/batches/ajax.php?page=4&cohortid=' . $cohort->id . '">0</a></li>
			 $innercontent = ''.$batchdates.'<div id="demo' . $cohort->id . '">
                            <ul>
                            <li><a href="' . $CFG->wwwroot . '/local/batches/ajax.php?page=1&cohortid=' . $cohort->id . '">Users :0</a></li>
                            <li><a href="' . $CFG->wwwroot . '/local/batches/ajax.php?page=2&cohortid=' . $cohort->id . '">Assign Users:0</a></li>

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
                                    ui.panel.html("<center><img src=\"' . $CFG->wwwroot . '/blocks/learning_plan/images/loading.gif\" /></center>")
                                    },
                                    collapsible: true,
                                    active: false
                                    });});');
            
            //---- Display table to view assigned users-----
            $batch_assigned_users_view = "<div id='batch_assigned_users_view$cohort->id' class='dialog1' style='display:none;'>";
            $userfields = "SELECT u.* ";
            $assigned_users = $DB->get_records_sql($userfields . $userfrom, $params);
            if(empty($assigned_users)){
                $batch_assigned_users_view .= $OUTPUT->heading(get_string("nousersassigned", 'block_learning_plan'),5);
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
            
            
            ////---- Display table to view assigned Courses-----
            //$batch_assigned_courses_view = "<div id='batch_assigned_courses_view$cohort->id' class='dialog1' style='display:none;'>";
            //$coursefields = "SELECT c.* ";
            //$assigned_courses = $DB->get_records_sql($coursefields . $coursefrom, $params);
            //if(empty($assigned_courses)){
            //    $batch_assigned_courses_view .= $OUTPUT->heading(get_string("nocoursesassigned", 'block_learning_plan'),5);
            //} else {
            //    $batch_assigned_courses_view .= html_writer::table($this->batch_assigned_courses($cohort->id));
            //}
            //$batch_assigned_courses_view .= "</div>";
            ////---- End of display table to view assigned Courses-----
            
            
            ////---- Display form to assign courses to batches-----
            //$batch_assign_courses_form = "<div id='batch_assign_courses_form$cohort->id' class='dialog1' style='display:none;'>";
            //$batch_assign_courses_form .= $this->assign_courses($cohort->id);
            //$batch_assign_courses_form .= '</div>';
            ////---- End of display form to assign courses to batches-----

            if(is_siteadmin())
			$costcenterinfo = '<span class="lp_ccinfo">'.get_string("pluginname","local_costcenter").':<b>'.$costcenter->fullname.'</b></span>';
            else
			$costcenterinfo ='';
            $buttons = array();
            if (empty($cohort->component)) {
                if ($manager) {
					   $buttons[] =  html_writer::link(new moodle_url('/local/batches/index.php', array('id'=>$cohort->id)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>get_string('edit'), 'class'=>'iconsmall')));
                    $buttons[] = html_writer::link(new moodle_url('/local/batches/index.php', array('id'=>$cohort->id, 'delete'=>1)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>get_string('delete'), 'class'=>'iconsmall')), array('id' => 'batchdeleteconfirm' . $cohort->id . ''));                    
					$PAGE->requires->event_handler('#batchdeleteconfirm'.$cohort->id.'', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' =>  $message = get_string('delconfirm', 'cohort', format_string($cohort->name)), 'callbackargs' => array('id' => $cohort->id)));
                }
            }
            $buttons_string = implode(' ',$buttons);
            
            
            $line[] = '<h5 id="lp_heading">'.format_string($cohort->name, false).'/'.$cohort->idnumber.'</h5><span id="lp_actions">' . $buttons_string .'</span>' . $innercontent . $costcenterinfo;
            if(is_siteadmin())
			$line[] = $cohort->fullname;
			$line[] = '<h5 id="lp_heading">'.format_string($cohort->name, false).' / '.$cohort->fullname.'</h5>' . $displaytab . $batch_assigned_users_view . $batch_assigned_courses_view . $batch_assign_users_form . $batch_assign_courses_form;
            $line[] = $buttons_string;
            $data[] = $line;
        }
        $table = new html_table();
        $table->head = array('');
        //$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size','centeralign source', 'centeralign action');
        $table->id = 'batchtable';
        $table->attributes['class'] = 'batchtable generaltable';
        $table->data  = $data;
      if(is_siteadmin()){
	    echo html_writer::link(new moodle_url('/local/batches/bulk_enroll.php'),'Bulk upload',array('id'=>'back_tp_course'));
	      echo "<div class='batch_filter'></div>";
	  }
        echo html_writer::table($table);
		//#batchtable
		if(is_siteadmin()){
			echo html_writer::script('$(document).ready(function() {
                        var table = $("#batchtable").dataTable({

                        searching: true,
                         "bSort" : false,
                         //stateSave: true,

                         "aaSorting": [],
                         //"bStateSave": true,
			  "fnDrawCallback": function(oSettings) { 
                                           if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {         
                                           $("#batchtable_paginate").hide();
                                            $("#batchtable_length").hide();  
                                        }
                                    },
                         
                         "lengthMenu": [[5, 10, 25,50,100, -1], [5,10,25, 50,100, "All"]],
                        "aoColumnDefs": [{ \'bSortable\': false, \'aTargets\': [ 0 ] },
                        {"bVisible": false, "aTargets": [1]},
                        { "aTargets": [0], 
      "sType": "html", 
      "fnRender": function(o, val) { 
          return $("<div/>").html(o.aData[2]).text();
      } 
    }
                        ],

			"language": {
                          "paginate": {
                          "previous": "<<",
                          "next": ">>"
                        }
                        },
                        });

table.coFilter({
        sPlaceHolder: ".batch_filter",
        aoColumns: [1],
        columntitles: { 1: "Cost center"},
        filtertype: {1: "select"}
    });
                        });');
		}else{
        echo html_writer::script(' $(document).ready(function() {
                        $("#batchtable").dataTable({
                        searching: true,
			  "fnDrawCallback": function(oSettings) {
                                        if ( 5 > oSettings.fnRecordsDisplay()) {
                                           $("#batchtable_paginate").hide();
                                            $("#batchtable_length").hide();
                                            $("#batchtable_filter").hide();  
                                        }
                                    }, 
                         "aaSorting": [],
                         "lengthMenu": [[5, 10, 25,50,100, -1], [5,10,25, 50,100, "All"]],
                        "aoColumnDefs": [{ \'bSortable\': false, \'aTargets\': [ 0 ] }],
			"language": {
                          "paginate": {
                          "previous": "<<",
                          "next": ">>"
                        }
                        }
                        });
                        });');
		}
    }
    
    
    
    
    
    function assign_users($id){
        global $DB, $CFG, $USER, $CFG;
        
        //$costcenter = new costcenter();
        //if (is_siteadmin()) {
        //    $costcenters = $DB->get_records('local_costcenter', array('visible' => 1));
        //} else {
        //    $costcenters = $costcenter->get_assignedcostcenters();
        //}
        //$count = sizeof($costcenters);
        $output = '';
        $output .= '<form autocomplete="off" id="assign_users'.$id.'" action="index.php" method="post" accept-charset="utf-8" class="mform">';
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
		$sql1 = "SELECT user.*
        FROM {local_userdata} AS udata 
        JOIN {user} AS user ON user.id = udata.userid   
        AND user.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = {$id})
        AND user.deleted <> 1 AND user.confirmed = 1 AND user.id <> 1";
//$sql2 = "SELECT user.*
//        FROM {local_costcenter_permissions} AS cp
//        JOIN {user} AS user ON user.id = cp.userid
//        WHERE cp.costcenterid = {$costcenterid}
//        AND user.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = {$id})
//        AND user.id <> $USER->id
//        AND user.deleted <> 1 AND user.confirmed = 1 AND user.id <> 1";
//$users = $DB->get_records_sql("$sql1 UNION $sql2");

        $users = $DB->get_records_sql("$sql1");
            $output .= '<fieldset class="hidden"><div><div id="fitem_id_t_id[]" class="fitem fitem_fselect "><div class="fitemtitle"><label for="id_u_id[]">Select users</label></div><div class="felement ftext"><select name="user_id[]" id="select_users'.$id.'" size="10" multiple onchange="activate_submit(\'assign_users'.$id.'\',\'assign-user-select'.$id.'\')" class="assign-user-select'.$id.'">'; 
            //content will be added through ajax here
			foreach($users as $user){
				$output .="<option value=$user->id>".fullname($user)."</option>";
			}
            $output .= "</select></div></div></div></fieldset>";
        //}
        $output .= '<input type="hidden" name="id" value='.$id.' />';
        $output .= '<input type="hidden" name="moveto" value='.$id.' />';
        $output .= '<fieldset class="hidden"><div><div id="fitem_id_submitbutton" class="fitem fitem_actionbuttons fitem_fsubmit"><div class="felement fsubmit"><input type="submit" id="movetoid'.$id.'" class="form-submit" disabled value="Assign users" /></div></div>';
        $output .= '</div></fieldset></form>';
        $output .= '<div style="padding-left:20px;"><a href="'.$CFG->wwwroot.'/local/batches/assignusers.php?id='.$id.'">Add Multiple Users</a></div>';
        //}
        return $output;
    }
    
    
    function batch_assigned_users($users,$cohortid){
	global $DB,$PAGE,$OUTPUT;
	$table = new html_table();
        $table->head = array(get_string('username'), get_string('email'),get_string('action'));
	$container_id = "batch_user_display".$cohortid;
        $table->id = $container_id;
	$table->attributes = array('style'=>'margin:0 auto');
        $table->align = array('left','left');
        $table->width = '70%';
        
        foreach($users as $user){
            $row = array();
            $row[] = fullname($user);
			$row[] = $user->email;
            $delete = html_writer::link(new moodle_url('/local/batches/index.php', array('id'=>$cohortid, 'userid'=>$user->id,'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('unassign','local_costcenter'), 'alt' => get_string('unassign','local_costcenter'), 'class' => 'iconsmall')),array('id'=>'deleteuser_confirm'.$user->id.$cohortid));
            $PAGE->requires->event_handler('#deleteuser_confirm'.$user->id.$cohortid.'', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' =>  get_string('unassignuserconfirmation', 'local_batches'), 'callbackargs' => array('id' => $cohortid,'extraparams'=>'&confirm=1&unassign=1&userid='.$user->id.'')));
            $row[] = $delete;
            $table->data[] = $row;
        }
        echo html_writer::script(' $(document).ready(function() {
                        $("#'.$container_id.'").dataTable({

                        searching: true,
			  "fnDrawCallback": function(oSettings) { 
                                           if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {         
                                           $("#'.$container_id.'_paginate").hide();
                                            $("#'.$container_id.'_length").hide();  
                                        }
                                    }, 
                         "aaSorting": [],
                         "lengthMenu": [[5, 10, 25,50,100, -1], [5,10,25, 50,100, "All"]],
                        "aoColumnDefs": [{ \'bSortable\': false, \'aTargets\': [ 0 ] }],
			"language": {
                          "paginate": {
                          "previous": "<<",
                          "next": ">>"
                        }
                        }
                        });
                        });');
	return $table;
    }
    
    function batch_assigned_courses($id){
        global $CFG,$DB,$PAGE,$OUTPUT;
        
        $container_id = "batch_course_display".$id;
        
        $table = new html_table();
        $table->head = array('Course Name', 'Cost Center','Enrolled','Completed',get_string('actions'));
	$table->id = $container_id;
        $table->attributes = array('style'=>'margin: 0 auto;');
	$table->size = array('40%','20%','20%','20%');
        $table->align = array('left', 'left', 'center', 'center', 'center');
        $table->width = '80%';
        
        $sql = "SELECT c.* FROM {local_batch_courses} AS bc
                        JOIN {course} AS c ON c.id = bc.courseid
                        WHERE bc.batchid = {$id} AND c.visible = 1";
        $courses = $DB->get_records_sql($sql);
        foreach ($courses as $course) {
            $row = array();
                    $batch_members = $DB->get_fieldset_sql("select userid from {cohort_members} where cohortid = $id");
                    if(!empty($batch_members)){
                        $batch_members = implode(',',$batch_members);
	           $course_statistics = $DB->get_record_sql("SELECT count(ue.userid) as enrolled,count(cc.course) as completed
                                              FROM {user_enrolments} as ue 
                                              JOIN {enrol} as e ON e.id=ue.enrolid
                                        RIGHT JOIN {course} as c ON c.id =e.courseid
                                         LEFT JOIN {course_completions} cc ON cc.course=e.courseid and ue.userid=cc.userid
                                             WHERE c.id=$course->id AND ue.userid in($batch_members)
                                             AND e.status = 0 AND ue.status = 0
                                          group by e.courseid");
                    }
            $delete = html_writer::link(new moodle_url('/local/batches/index.php', array('id'=>$id,'courseid' => $course->id, 'unassign'=>1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('unassign','local_costcenter'), 'alt' => get_string('unassign','local_costcenter'), 'class' => 'iconsmall')),array('id'=>'deleteconfirm_course'.$id.$course->id.''));
            $PAGE->requires->event_handler('#deleteconfirm_course'.$id.$course->id.'', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' =>  get_string('unassigncourseconfirmation', 'local_batches'), 'callbackargs' => array('id' => $id, 'courseid'=>$course->id, 'unassign'=>1)));

	    $row[] = html_writer::link(new moodle_url('/course/view.php',array('id'=>$course->id)),format_string($course->fullname, false));
            $row[] = $DB->get_field('local_costcenter', 'fullname', array('id'=>$course->costcenter));
            if(!empty($course_statistics)){
	    $row[] = $course_statistics->enrolled;
            $row[] = $course_statistics->completed;
            }else{
            $row[] = 0;
            $row[] = 0;
            }
            $row[] = $delete;
            $table->data[] = $row;
	    
        }
	echo html_writer::script(' $(document).ready(function() {
                        $("#'.$container_id.'").dataTable({

                        searching: true,
			  "fnDrawCallback": function(oSettings) { 
                                           if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {         
                                           $("#'.$container_id.'_paginate").hide();
                                            $("#'.$container_id.'_length").hide();  
                                        }
                                    }, 
                         "aaSorting": [],
                         "lengthMenu": [[5, 10, 25,50,100, -1], [5,10,25, 50,100, "All"]],
                        "aoColumnDefs": [{ \'bSortable\': false, \'aTargets\': [ 0 ] }],
			"language": {
                          "paginate": {
                          "previous": "<<",
                          "next": ">>"
                        }
                        }
                        });
                        });');
            return $table;  
    }
    
    function assign_courses($id){
        global $DB, $CFG, $USER, $CFG;
        //$costcenter = new costcenter();
        //if (is_siteadmin()) {
        //    $costcenters = $DB->get_records('local_costcenter', array('visible' => 1));
        //} else {
        //    $costcenters = $costcenter->get_assignedcostcenters();
        //}
        //$count = sizeof($costcenters);
        $output = '';
        $output .= '<form autocomplete="off" id="assign_courses'.$id.'" action="index.php" method="post" accept-charset="utf-8" class="mform">';
        $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

        
        /*f($count == 1){
            $batches = new local_batches($id);
            
            foreach($costcenters as $costcenter){
                $output .= "<input type='hidden' name='cost_id' id='select_costcentercourse$id' value='$costcenter->id' />";
                $courses = $batches->get_costcenter_courses($costcenter->id);
            }
            $output .= '<fieldset class="hidden"><div><div id="fitem_id_t_id[]" class="fitem fitem_fselect "><div class="fitemtitle"><label for="id_u_id[]">Select Courses</label></div><div class="felement ftext"><select name="course_id[]" id="select_courses'.$id.'" size="10" multiple onchange="activate_submit(\'assign_courses'.$id.'\',\'assign-course-select'.$id.'\')" class="assign-course-select'.$id.'">'; 
            foreach($courses as $course){
                $output .= "<option value='".$course->id."'>".$course->fullname."</option>";
            }
            $output .= "</select></div></div></div></fieldset>";
        } else {
            $output .= "<fieldset class='hidden'><div><div id='fitem_id_t_id[]' class='fitem fitem_fselect'>
                        <div class='fitemtitle'><label for='id_u_id[]'>Select Cost Center</label></div>
                        <div class='felement ftext'><select name='cost_id' id='select_costcentercourse$id' size='10' onchange='display_courses(\"$id\", \"$CFG->wwwroot\")' class='assign-cost-select".$id."'>"; 
            foreach ($costcenters as $costcenter) {
                $output .= '<option value='.$costcenter->id.'>'.$costcenter->fullname.'</option>';
            }
            $output .= "</select></div></div></div></fieldset>";
    
        }*/
		$costcenterid = $DB->get_field('local_costcenter_batch','costcenterid',array('batchid'=>$id));
		$courses = $DB->get_records_sql_menu("SELECT id,fullname FROM {course}
                                                         WHERE costcenter = {$costcenterid}
        AND id NOT IN (select courseid FROM {local_batch_courses} WHERE batchid = {$id})
        AND visible = 1");
		  $output .= '<fieldset class="hidden"><div><div id="fitem_id_t_id[]" class="fitem fitem_fselect "><div class="fitemtitle"><label for="id_u_id[]">Select Courses</label></div><div class="felement ftext"><select name="course_id[]" id="select_courses'.$id.'" size="10" multiple onchange="activate_submit(\'assign_courses'.$id.'\',\'assign-course-select'.$id.'\')" class="assign-course-select'.$id.'">'; 
		  foreach($courses as $key=>$value){
			$output .='<option value='.$key.'>'.$value.'</option>';
		  }
		  $output .= "</select></div></div></div></fieldset>";
        
        $output .= '<input type="hidden" name="id" value='.$id.' />';
        $output .= '<input type="hidden" name="moveto" value='.$id.' />';
        $output .= '<fieldset class="hidden"><div><div id="fitem_id_submitbutton" class="fitem fitem_actionbuttons fitem_fsubmit"><div class="felement fsubmit"><input type="submit" id="movetoid'.$id.'" class="form-submit" disabled value="Assign Courses" /></div></div>';
        $output .= '</div></fieldset></form>';
        //}
        return $output;
    }
}