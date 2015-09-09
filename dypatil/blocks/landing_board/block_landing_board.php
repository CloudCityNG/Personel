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
 * Form for editing Cobalt report dashboard block instances.
 * @package  block_reportdashboard
 * @author Naveen kumar <naveen@eabyas.in>
 */
class block_landing_board extends block_list {

    function init() {
        $this->title = '';
    }

    function has_config() {
        return true;
    }

    function get_required_javascript() {
	$this->page->requires->jquery();
        $this->page->requires->js('/blocks/landing_board/javascript/jquery.qtip.min.js',true);
	
    }

    function get_content() {
        global $CFG, $DB, $PAGE,$USER,$OUTPUT;
        require_once($CFG->dirroot.'/local/lib.php');
		if(!isloggedin()){
			return $this->content;
		}
        if ($this->content !== NULL) {
            return $this->content;
        }
		if(!has_capability('local/collegestructure:manage', context_system::instance())){
			return $this->content;
		}
		$this->content = new stdClass();
		$this->content->items = array();
		$hierarchy = new hierarchy;
		$schools = $hierarchy->get_school_items();
		foreach($schools as $school){
			$list=array();
			$programs    = $DB->count_records('local_program',array('schoolid'=>$school->id,'visible'=>1));
			$departments = $DB->count_records('local_department', array('schoolid' => $school->id, 'visible' => 1));
			$depts       = $DB->count_records_sql("SELECT count(distinct d.id) FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid = $school->id AND d.visible=1");
			$departments = $departments + $depts;
			if(is_siteadmin())
				$semesters = $DB->count_records('local_school_semester',array('schoolid'=>$school->id));
			else
				$semesters = $DB->count_records_sql("SELECT count(distinct ss.id) FROM {local_semester} AS se JOIN {local_school_semester} ss ON ss.semesterid=se.id JOIN {local_school_permissions} AS sp ON sp.schoolid=ss.schoolid WHERE sp.userid={$USER->id} and ss.schoolid=$school->id");
			
			$itemdepth = 'depth' . min(10, $school->depth);
			// @todo get based on item type or better still, don't use inline styles :-(
			$itemicon = $OUTPUT->pix_url('/i/item');
			$cssclass = !$school->visible ? 'dimmed' : '';
			$link = html_writer::start_tag('div', array('class' => 'hierarchyitem '));
			
			$link .= $OUTPUT->action_link(new moodle_url('/local/collegestructure/view.php', array('id' => $school->id)), format_string($school->fullname), null, array('class' => $cssclass));
			
			if ($school->type == 2)
				$link .="-(Organization)";
			
			$link .= html_writer::end_tag('div');
			$table = new html_table();
			//$table->id =  $itemdepth;
			$table->head = array();
			$table->width = '100%';
			$table->align = array('left', 'center', 'center');
			$table->size = array('60%', '20%', '20%');
			$programlink = html_writer::link(new moodle_url('/local/programs/program.php'),'<button>Add</button>',array('schoolid'=>$school->id));
			$departmentlink = html_writer::link(new moodle_url('/local/departments/departments.php'),'<button>Add</button>',array('schoolid'=>$school->id));
			$semesterlink = html_writer::link(new moodle_url('/local/semesters/semester.php'),'<button>Add</button>',array('schoolid'=>$school->id));
			$programs = $programs ? '<a href="javascript:void(0)" id="program'.$school->id.'">'.$programs.'</a>' : 'NA' ;
			$departments = $departments ? '<a href="javascript:void(0)" id="department'.$school->id.'">'.$departments.'</a>' : 'NA';
			$semesters = $semesters ? '<a href="javascript:void(0)" id="semester'.$school->id.'">'.$semesters.'</a>' : 'NA' ;
			$table->data = array(
								array('Programs','<b>'.$programs.'</b>',$programlink),
								array('Departments','<b>'.$departments.'</b>',$departmentlink),
								array('Semesters','<b>'.$semesters.'</b>',$semesterlink)
								);
			
			$left_panel = '<div class="display_table left_panel">' .html_writer::table($table).'</div>';
			
			
			/* By Vinod for right side info */
			$today = date('Y-m-d');
			//active semester
			$activesem = $DB->get_record_sql("SELECT ls.id,ls.fullname
                                    FROM {local_school_semester} AS ss
                                    JOIN {local_semester} AS ls
                                      ON ss.semesterid=ls.id where ss.schoolid={$school->id} AND ls.visible = 1
									   AND  '{$today}' BETWEEN from_unixtime( ls.startdate,  '%Y-%m-%d' ) AND from_unixtime( ls.enddate,  '%Y-%m-%d' )
									   group by ls.id");
			$active = $activesem ? $activesem->fullname : 'NA';
			// enrolled count in active semester
			$enrolled = $DB->count_records('local_user_clclasses', array('semesterid'=>$activesem->id, 'registrarapproval'=>1));
			
			//classes count in current semester
			$classes = $DB->count_records('local_clclasses', array('semesterid'=>$activesem->id, 'visible'=>1));
			
			//Pending Approvals to all the classes
			$pending = $DB->count_records('local_user_clclasses', array('semesterid'=>$activesem->id, 'registrarapproval'=>0));
			
			//New admissions
			$admissions = $DB->count_records_sql("SELECT count(id) FROM {local_admission} WHERE schoolid = {$school->id} AND id NOT IN (SELECT applicationid FROM {local_userdata})");
			
			$right_panel = '<p><span class="right_lable">Active Semester: </span><span class="right_value">'. $active .'</span></p>
							<p><span class="right_lable">Classes in Current Semester: </span><span class="right_value">'.$classes.'</span></p>
							<p><span class="right_lable">Enrollments in Current Semester: </span><span class="right_value">'.$enrolled.'</span></p>
							<p><span class="right_lable">Pending Approvals: </span><span class="right_value">'.$pending.'</span></p>
							<p><span class="right_lable">New Admissions: </span><span class="right_value">'.$admissions.'</span></p>';
			
			$right_panel = '<div class="display_table right_panel">'.$right_panel.'</div>';
			
			
			$this->content->items[] =  $link . $left_panel .$right_panel;
			
			$PAGE->requires->event_handler('#program'.$school->id.'', 'mouseenter', 'M.util.init_block_landing_board', array('schoolid'=>$school->id,'id'=>'#program'.$school->id.'','type'=>'program' ));
			$PAGE->requires->event_handler('#department'.$school->id.'', 'mouseenter', 'M.util.init_block_landing_board', array('schoolid'=>$school->id,'id'=>'#department'.$school->id.'','type'=>'department' ));
			$PAGE->requires->event_handler('#semester'.$school->id.'', 'mouseenter', 'M.util.init_block_landing_board', array('schoolid'=>$school->id,'id'=>'#semester'.$school->id.'','type'=>'semester' ));	
		}
		$this->content->footer = '';
		$this->page->requires->js('/blocks/landing_board/javascript/custom.js');
		return $this->content;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }
}
