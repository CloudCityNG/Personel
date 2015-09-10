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
class block_dashboard extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_dashboard');
    }

    function has_config() {
        return true;
    }

    function get_required_javascript() {
		$this->page->requires->jquery();
        $this->page->requires->js('/blocks/dashboard/javascript/jquery.qtip.min.js',true);
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
		$schoolidin = '';
		if(!empty($schools)){
			$schoolidin = implode(', ', array_keys($schools));
		}

		$renderer = $this->page->get_renderer('block_dashboard');
		
		//Programs...
		$programs    = $DB->get_records_select('local_program', 'schoolid IN ('.$schoolidin.') AND visible = 1');
		$programtable = $renderer->display_programs($hierarchy, $programs);
		
		//Curriculums...
		$curriculums = $DB->get_records_select('local_curriculum', 'schoolid IN ('.$schoolidin.') AND visible = 1');
		$curriculumtable = $renderer->display_curriculums($hierarchy, $curriculums);
		
		//Departments...
		$departments = $DB->get_records_select('local_department', 'schoolid IN ('.$schoolidin.') AND visible = 1');
		$depts       = $DB->get_records_sql("SELECT * FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid IN ($schoolidin) AND d.visible=1");
		$departments = $departments + $depts;		
		$departmenttable = $renderer->display_departments($hierarchy, $departments);
		
		//Classes...
		$classes = $DB->get_records_select('local_clclasses', 'schoolid IN ('.$schoolidin.') AND visible = 1');
		$classtable = $renderer->display_classes($hierarchy, $classes);
		
		//Courses...
		$courses = $DB->get_records_select('local_cobaltcourses', 'schoolid IN ('.$schoolidin.') AND visible = 1');
		$coursetable = $renderer->display_courses($hierarchy, $courses);
		
		//Exams...
		$exams = $DB->get_records_select('local_scheduledexams', 'schoolid IN ('.$schoolidin.') AND visible = 1');
		$examtable = $renderer->display_exams($hierarchy, $exams);
		
		$this->content->items[] = '<div class="row row-fluid span12 dashboard_tablerow desktop-first-column">' .
									html_writer::tag('div', $head . $programtable, array('class'=>'span4 dashboard_table')) .
									html_writer::tag('div', $curriculumtable, array('class'=>'span4 dashboard_table')) .
									html_writer::tag('div', $departmenttable, array('class'=>'span4 dashboard_table')) .
									'</div><div class="row row-fluid span12 dashboard_tablerow desktop-first-column">' .
									html_writer::tag('div', $coursetable, array('class'=>'span4 dashboard_table')) .
									html_writer::tag('div', $classtable, array('class'=>'span4 dashboard_table')) .
									html_writer::tag('div', $examtable, array('class'=>'span4 dashboard_table')) .
									'</div>';
			
		$this->content->footer = '';
		$this->page->requires->js('/blocks/dashboard/javascript/custom.js');
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
