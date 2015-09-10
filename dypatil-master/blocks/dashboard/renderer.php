<?php
class block_dashboard_renderer extends plugin_renderer_base {

    /*
     * Displays all programs with the enrolled count
     */
    function display_programs($hierarchy, $programs){
        global $OUTPUT, $PAGE, $DB, $CFG;
        $data = array();
		$i = 1;
		$more = '';
		foreach($programs as $program){
			if($i > 3){
				$more = html_writer::tag('a', get_string('viewmore', 'block_dashboard'), array('href'=>$CFG->wwwroot.'/local/programs/index.php', 'target'=>'_blank', 'style'=>'float: right;'));
				break;
			}
			$class = 'dashboard';
			
			$rowitem = html_writer::tag('li', $program->fullname, array('class'=>'programname'));
			
			$enrolledcount = $DB->count_records('local_userdata', array('programid'=>$program->id, 'schoolid'=>$program->schoolid));
			$enrolledcount = html_writer::tag('a', $enrolledcount, array('href'=>'javascript:void(0)', 'id'=>'dashboard_program'.$program->id));
			$rowitem .= html_writer::tag('li', $enrolledcount, array('class'=>'enrolcount'));
			
			$rowitem = html_writer::tag('ul', $rowitem, array('class'=>$class));
			
			$PAGE->requires->event_handler('#dashboard_program'.$program->id.'', 'mouseenter', 'M.util.init_block_dashboard', array('id'=>'#dashboard_program'.$program->id.'','type'=>'program' ));
			
			$data[] = array($rowitem);
			
			$i++;
		}
		
		$link = html_writer::start_tag('div', array('class' => 'hierarchyitem'));
		
		$table = new html_table();
		//$table->id =  $itemdepth;
		$table->head = array('<ul class="dashboard">
								<li class="programname">'.get_string('program', 'block_dashboard').'</li>
								<li class="enrolcount">'.get_string('enrolments', 'block_dashboard').'</li>
							  </ul>'
							);
		$table->width = '100%';
		$table->data = $data;
        
		$head = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('programs1', 'block_dashboard')));
		$head .= $OUTPUT->heading(get_string('program', 'block_dashboard'), 6, 'program');
		$head = html_writer::tag('div', $head, array('class'=>'dashboard_heading'));
		
        $addnew = html_writer::tag('a', get_string('add'), array('href'=>$CFG->wwwroot.'/local/programs/program.php'));
        $addnew = html_writer::tag('button', $addnew, array('class'=>'addnewbutton'));
        $footer = html_writer::tag('div', $addnew . $more, array('class'=>'footer_notes'));
        return $head . html_writer::table($table) . $footer;
    }
    
    /*
     * Displays all Curriculums with the enrolled count
     */
    function display_curriculums($hierarchy, $curriculums){
        global $OUTPUT, $PAGE, $DB, $CFG;
        $data = array();
		$i = 1;
		$more = '';
		foreach($curriculums as $curriculum){
			if($i > 3){
				$more = html_writer::tag('a', get_string('viewmore', 'block_dashboard'), array('href'=>$CFG->wwwroot.'/local/curriculum/index.php', 'target'=>'_blank', 'style'=>'float: right;'));
				break;
			}
			
			$class = 'dashboard';
			
			$rowitem = html_writer::tag('li', $curriculum->fullname, array('class'=>'programname'));
			
			$enrolledcount = $DB->count_records('local_userdata', array('curriculumid'=>$curriculum->id, 'schoolid'=>$curriculum->schoolid));
			$enrolledcount = html_writer::tag('a', $enrolledcount, array('href'=>'javascript:void(0)', 'id'=>'dashboard_curriculum'.$curriculum->id));
			$rowitem .= html_writer::tag('li', $enrolledcount, array('class'=>'enrolcount'));
			
			$rowitem = html_writer::tag('ul', $rowitem, array('class'=>$class));
			$data[] = array($rowitem);
			$i++;
		}
		$table = new html_table();
		//$table->id =  $itemdepth;
		$table->head = array('<ul class="dashboard">
								<li class="curriculumname">'.get_string('curriculum', 'block_dashboard').'</li>
								<li class="enrolcount">'.get_string('enrolments', 'block_dashboard').'</li>
							  </ul>'
							);
		$table->width = '100%';
		$table->data = $data;
		
		$head = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('curriculums1', 'block_dashboard')));
		$head .= $OUTPUT->heading(get_string('curriculum', 'block_dashboard'), 6, 'curriculum');
		$head = html_writer::tag('div', $head, array('class'=>'dashboard_heading'));
		
        $addnew = html_writer::tag('a', get_string('add'), array('href'=>$CFG->wwwroot.'/local/curriculum/curriculum.php', 'target'=>'_blank'));
        $addnew = html_writer::tag('button', $addnew, array('class'=>'addnewbutton'));
        $footer = html_writer::tag('div', $addnew . $more, array('class'=>'footer_notes'));
        return $head . html_writer::table($table) . $footer;
    }
    
    
    function display_departments($hierarchy, $departments){
        global $OUTPUT, $PAGE, $DB, $CFG;
        $data = array();
		$i = 1;
		$more = '';
        foreach($departments as $department){
            if($i > 3){
				$more = html_writer::tag('a', get_string('viewmore', 'block_dashboard'), array('href'=>$CFG->wwwroot.'/local/department/index.php', 'target'=>'_blank', 'style'=>'float: right;'));
				break;
			}
			
			$class = 'dashboard';
			
			$rowitem = html_writer::tag('li', $department->fullname, array('class'=>'departmentname'));
			
			$enrolledcount = $DB->count_records('local_cobaltcourses', array('departmentid'=>$department->id, 'schoolid'=>$department->schoolid));
			$enrolledcount = html_writer::tag('a', $enrolledcount, array('href'=>'javascript:void(0)', 'id'=>'dashboard_department'.$department->id));
			$rowitem .= html_writer::tag('li', $enrolledcount, array('class'=>'enrolcount'));
			
			$rowitem = html_writer::tag('ul', $rowitem, array('class'=>$class));
			$data[] = array($rowitem);
			$i++;
        }
		$table = new html_table();
		//$table->id =  $itemdepth;
		$table->head = array('<ul class="dashboard">
								<li class="departmentname">'.get_string('department', 'block_dashboard').'</li>
								<li class="enrolcount">'.get_string('coursecount', 'block_dashboard').'</li>
							  </ul>'
							);
		$table->width = '100%';
		$table->data = $data;
		
		$head = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('departments', 'block_dashboard')));
		$head .= $OUTPUT->heading(get_string('department', 'block_dashboard'), 6, 'department');
		$head = html_writer::tag('div', $head, array('class'=>'dashboard_heading'));
		
        $addnew = html_writer::tag('a', get_string('add'), array('href'=>$CFG->wwwroot.'/local/departments/departments.php', 'target'=>'_blank'));
        $addnew = html_writer::tag('button', $addnew, array('class'=>'addnewbutton'));
        $footer = html_writer::tag('div', $addnew . $more, array('class'=>'footer_notes'));
        return $head . html_writer::table($table) . $footer;
    }
    
    function display_classes($hierarchy, $classes){
        global $OUTPUT, $PAGE, $DB, $CFG;
        $data = array();
		$i = 1;
		$more = '';
        foreach($classes as $class){
            if($i > 3){
				$more = html_writer::tag('a', get_string('viewmore', 'block_dashboard'), array('href'=>$CFG->wwwroot.'/local/clclasses/index.php', 'target'=>'_blank', 'style'=>'float: right;'));
				break;
			}
			
			$rowitem = html_writer::tag('li', $class->fullname, array('class'=>'classname'));
			
			$enrolledcount = $DB->count_records('local_user_clclasses', array('classid'=>$class->id));
			$enrolledcount = html_writer::tag('a', $enrolledcount, array('href'=>'javascript:void(0)', 'id'=>'dashboard_class'.$class->id));
			$rowitem .= html_writer::tag('li', $enrolledcount, array('class'=>'enrolcount'));
			
			$rowitem = html_writer::tag('ul', $rowitem, array('class'=>'dashboard'));
			$data[] = array($rowitem);
			$i++;
        }
		$table = new html_table();
		$table->head = array('<ul class="dashboard">
								<li class="classname">'.get_string('class', 'block_dashboard').'</li>
								<li class="enrolcount">'.get_string('enrolments', 'block_dashboard').'</li>
							  </ul>'
							);
		$table->width = '100%';
		$table->data = $data;
		
		$head = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('classes', 'block_dashboard')));
		$head .= $OUTPUT->heading(get_string('classes', 'block_dashboard'), 6, 'class');
		$head = html_writer::tag('div', $head, array('class'=>'dashboard_heading'));
		
        $addnew = html_writer::tag('a', get_string('add'), array('href'=>$CFG->wwwroot.'/local/clclasses/createclass.php', 'target'=>'_blank'));
        $addnew = html_writer::tag('button', $addnew, array('class'=>'addnewbutton'));
        $footer = html_writer::tag('div', $addnew . $more, array('class'=>'footer_notes'));
        return $head . html_writer::table($table) . $footer;
    }
    
    function display_courses($hierarchy, $courses){
        global $OUTPUT, $PAGE, $DB, $CFG;
        $data = array();
		$i = 1;
		$more = '';
        foreach($courses as $course){
            if($i > 3){
				$more = html_writer::tag('a', get_string('viewmore', 'block_dashboard'), array('href'=>$CFG->wwwroot.'/local/cobaltcourses/index.php', 'target'=>'_blank', 'style'=>'float: right;'));
				break;
			}
			
			$rowitem = html_writer::tag('li', $course->fullname, array('class'=>'coursename'));
			
			$enrolledcount = $DB->count_records('local_clclasses', array('cobaltcourseid'=>$course->id));
			$enrolledcount = html_writer::tag('a', $enrolledcount, array('href'=>'javascript:void(0)', 'id'=>'dashboard_course'.$course->id));
			$rowitem .= html_writer::tag('li', $enrolledcount, array('class'=>'enrolcount'));
			
			$rowitem = html_writer::tag('ul', $rowitem, array('class'=>'dashboard'));
			$data[] = array($rowitem);
			$i++;
        }
		$table = new html_table();
		$table->head = array('<ul class="dashboard">
								<li class="coursename">'.get_string('course', 'block_dashboard').'</li>
								<li class="enrolcount">'.get_string('classcount', 'block_dashboard').'</li>
							  </ul>'
							);
		$table->width = '100%';
		$table->data = $data;
		
		$head = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('courses', 'block_dashboard')));
		$head .= $OUTPUT->heading(get_string('courses', 'block_dashboard'), 6, 'course');
		$head = html_writer::tag('div', $head, array('class'=>'dashboard_heading'));
		
        $addnew = html_writer::tag('a', get_string('add'), array('href'=>$CFG->wwwroot.'/local/cobaltcourses/cobaltcourse.php', 'target'=>'_blank'));
        $addnew = html_writer::tag('button', $addnew, array('class'=>'addnewbutton'));
        $footer = html_writer::tag('div', $addnew . $more, array('class'=>'footer_notes'));
        return $head . html_writer::table($table) . $footer;
    }
    
    function display_exams($hierarchy, $exams){
        global $OUTPUT, $PAGE, $DB, $CFG;
        $data = array();
		$i = 1;
		$more = '';
        foreach($exams as $exam){
            if($i > 3){
				$more = html_writer::tag('a', get_string('viewmore', 'block_dashboard'), array('href'=>$CFG->wwwroot.'/local/cobaltcourses/index.php', 'target'=>'_blank', 'style'=>'float: right;'));
				break;
			}
			
            $class = $DB->get_record('local_clclasses', array('id'=>$exam->classid));
            $examtype = $DB->get_record('local_examtypes', array('id'=>$exam->examtype));
            
			$rowitem = html_writer::tag('li', '<b>'.$examtype->examtype.':</b> '.$class->fullname, array('class'=>'examname'));
			
			//$enrolledcount = $DB->count_records('local_clclasses', array('cobaltcourseid'=>$course->id));
			//$enrolledcount = html_writer::tag('a', $enrolledcount, array('href'=>'javascript:void(0)', 'id'=>'dashboard_exam'.$exam->id));
			$rowitem .= html_writer::tag('li', date('d M, Y', $exam->opendate), array('class'=>'enrolcount'));
			
			$rowitem = html_writer::tag('ul', $rowitem, array('class'=>'dashboard'));
			$data[] = array($rowitem);
			$i++;
        }
		$table = new html_table();
		$table->head = array('<ul class="dashboard">
								<li class="examname">'.get_string('exam', 'block_dashboard').'</li>
								<li class="enrolcount">'.get_string('date', 'block_dashboard').'</li>
							  </ul>'
							);
		$table->width = '100%';
		$table->data = $data;
		
		$head = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('exams', 'block_dashboard')));
		$head .= $OUTPUT->heading(get_string('exams', 'block_dashboard'), 6, 'exams');
		$head = html_writer::tag('div', $head, array('class'=>'dashboard_heading'));
		
        $addnew = html_writer::tag('a', get_string('add'), array('href'=>$CFG->wwwroot.'/local/scheduleexam/edit.php', 'target'=>'_blank'));
        $addnew = html_writer::tag('button', $addnew, array('class'=>'addnewbutton'));
        $footer = html_writer::tag('div', $addnew . $more, array('class'=>'footer_notes'));
        return $head . html_writer::table($table) . $footer;
    }
    
}