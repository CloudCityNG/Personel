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
 * General plugin functions.
 *
 * @package    local
 * @subpackage Curriculum
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;
global $CFG;
require_once($CFG->dirroot . '/local/lib.php');



/*
 * Function to add the data into the database
 */

class curricula {
    /**
     * @method curriculum_add_instance
     * @todo Inserts a new curriculum
     * @param object $data
     * @return confirmation message
     * */
    function curriculum_add_instance($data) {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $currenturl = "{$CFG->wwwroot}/local/curriculum/index.php";
        $data->id = $DB->insert_record('local_curriculum', $data);
        $entersettings = $hierarchy->entity_settings($data);
        $conf = new object();
        $conf->curriculum = $data->fullname;
        if ($data->id || $entersettings) {
            $message = get_string('createcurricsuccess', 'local_curriculum', $conf);
            $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
        }
    }

    /**
     * @method curriculum_settings
     * @todo Inserts a new curriculum settings
     * @param object $data
     * */
    function curriculum_settings($data) {
        global $DB;
        $setting = new stdClass();
        $setting->entityid = $data->entityid;
        $setting->level = $data->level;
        $setting->levelid = $data->id;
        $mincredithours = $data->mincredithours;
        $setting->schoolid = $data->schoolid;
        $subentityid = $data->subentityid;
        if ($data->mincrhour) {

            $setting->entityid = $data->entityids;
            $setting->subentityid = $data->subentityidse;
            $setting->mincredithours = $data->mincrhour;
            $data->id = $DB->insert_record('local_level_settings ', $setting);
        }
        if (is_array($mincredithours)) {
            $i = 1;
            foreach ($mincredithours as $mincredit) {
                $setting->entityid = $data->entityid;
                $setting->mincredithours = $mincredit;
                $setting->subentityid = $i;
                // $level="CL";
                //  if(!$DB->record_exists('local_level_settings', array('schoolid'=>$setting->schoolid,'levelid'=>$data->id, 'level'=>$level)))
                $DB->insert_record('local_level_settings', $setting);
                $i++;
            }
        }
    }

    /**
     * @method curriculum_update_instance
     * @todo to update exist curriculum 
     * @param object $data
     * @return print confirmation message
     * */
    function curriculum_update_instance($data) {
        global $DB, $CFG;
        $curriculums = $DB->update_record('local_curriculum', $data);
        $currenturl = "{$CFG->wwwroot}/local/curriculum/index.php";
        $conf = new object();
        $DB->delete_records('local_level_settings', array('levelid' => $data->id));
        $hierarchy = new hierarchy();
        $entersettings = $hierarchy->entity_settings($data);
        $conf->curriculum = $data->fullname;
        if ($curriculums || $entersettings) {
            $message = get_string('updatecurricsuccess', 'local_curriculum', $conf);
            $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
        }
    }

    /**
     * @method curriculum_delete_instance
     * @todo to delete curriculum 
     * @param object $data
     * @return print confirmation message
     * */
    function curriculum_delete_instance($data) {
        global $DB;
        $hierarchy = new hierarchy();
        $curricula = $DB->delete_records('local_curriculum', array('id' => $data));
        $curricula = $DB->delete_records('local_level_settings', array('levelid' => $data->id));
        $message = get_string('deletedcurricsuccess', 'local_curriculum');
        $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method add_cuplan_to_curriculum
     * @todo to create new curriculum plan
     * @param array $moduleids
     * @param int $curriculumid
     * */
    function add_cuplan_to_curriculum($moduleids, $curriculumid) {
        global $CFG, $DB, $OUTPUT, $USER;
        if (empty($moduleids)) {
            return;
        }
        $moduleids = array_reverse($moduleids);
        foreach ($moduleids as $moduleid) {
            $checkexist = $DB->get_records('local_curriculum_modules', array('curriculumid' => $curriculumid, 'moduleid' => $moduleid));
            if ($checkexist) {
                return;
            } else {
                $course = new stdClass();
                $course->curriculumid = $curriculumid;
                $course->moduleid = $moduleid;
                $now = date("d-m-Y");
                $course->timecreated = strtotime($now);
                $course->usermodified = $USER->id;
                $DB->insert_record('local_curriculum_modules', $course);
            }
        }
    }

    /**
     * @method unassign_module_instance
     * @todo to delete curriculum plan
     * @param int $moduleid Module ID
     * @param object $tool exist record
     * @return void
     * */
    function unassign_module_instance($tool, $moduleid) {
        global $DB;
        return $DB->delete_records('local_curriculum_modules', array('curriculumid' => $tool, 'moduleid' => $moduleid));
    }

    //function to get the curriculum id and name to assign the modules to it
    function get_curriculumf($id) {
        global $CFG, $DB;
        $hierarchy = new hierarchy();
        $out = $hierarchy->get_records_cobaltselect_menu('local_curriculum', "id=$id AND visible=1", null, '', 'id,fullname', 'Assign Modules To');
        return $out;
    }

    /**
     * @method print_curriculumtabs
     * @todo to display tab view
     * @param string $currenttab Current tab
     * @param int $cid Course ID
     * @return print tab view
     * */
    function print_curriculumtabs($currenttab, $cid = null) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();

        if ($cid < 0 || empty($cid)) {
            $create_cap = array('local/curriculum:manage', 'local/curriculum:create');
            if (has_any_capability($create_cap, $systemcontext))
                $toprow[] = new tabobject('create', new moodle_url('/local/curriculum/curriculum.php'), get_string('create', 'local_curriculum'));
        }
        else {
            $update_cap = array('local/curriculum:manage', 'local/curriculum:update');
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('edit', new moodle_url('/local/curriculum/curriculum.php'), get_string('editcurriculum', 'local_curriculum'));
        }

        $toprow[] = new tabobject('view', new moodle_url('/local/curriculum/index.php'), get_string('view', 'local_curriculum'));

        if ($currenttab == 'manageplan')
            $toprow[] = new tabobject('manageplan', new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid, 'assign' => 1)), get_string('manageplan', 'local_curriculum'));
        if ($currenttab == 'assign')
            $toprow[] = new tabobject('assign', new moodle_url('/local/curriculum/assigncourses.php'), get_string('assigncourses', 'local_curriculum'));
        if ($currenttab == 'addnewplan') {
            $string = $cid ? get_string('editplan', 'local_curriculum') : get_string('createplan', 'local_curriculum');
            $toprow[] = new tabobject('addnewplan', new moodle_url('/local/curriculum/plan.php'), $string);
        }

//     $toprow[] = new tabobject('setting', new moodle_url('/local/curriculum/setting.php'), get_string('curriculumsettings','local_curriculum'));
        $toprow[] = new tabobject('info', new moodle_url('/local/curriculum/info.php'), get_string('info', 'local_curriculum'));
//     $toprow[] = new tabobject('reports', new moodle_url('/local/curriculum/report.php'), get_string('reports','local_curriculum'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method settingelements
     * @todo to set form elements
     * @param object $mform object
     * @param string $place1
     * @param string $place2 
     * @return array of objects ($mfrom objects)
     * */
    public function settingelements($mform, $place1, $place2) {
        global $hierarchy;
        $hierarchy = new hierarchy();
        $faculties = $hierarchy->get_assignedschools();
        $school = $hierarchy->get_school_parent($faculties);
        $newel = $mform->createElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
        $mform->insertElementBefore($newel, $place1);
        $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        $school_value = $mform->getElementValue('schoolid');

        //Creating program element after getting the school value
        if (isset($school_value) && !empty($school_value)) {
            $school_id = $school_value[0];
            $programs = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$school_id AND visible=1", null, '', 'id,fullname', '--Select--');
            $newel2 = $mform->createElement('select', 'programid', get_string('selectprogram', 'local_programs'), $programs);
            $mform->insertElementBefore($newel2, $place2);
            $mform->addRule('programid', get_string('missingfullname', 'local_programs'), 'required', null, 'client');
            $program_value = $mform->getElementValue('programid');
            return $program_value;
        }
    }

    /**
     * @method curriculum_add_settings
     * @todo to add new curriculum to settings
     * @param object $data(new record)     
     * @return int inserted record ID
     * * */
    function curriculum_add_settings($data) {
        global $DB;
        return $DB->insert_record('local_settings', $data);
    }

    function get_credithoursetting($id) {
        global $DB;
        $sql = "SELECT lls.* from {local_level_settings} lls where levelid={$id}";
        return $DB->get_records_sql($sql);
    }

    /**
     * @method view_curriculum
     * @todo to print curriculums information
     * @param int $id curriculum ID      
     * * */
    function view_curriculum($id) {
        global $DB;
        $sql = "SELECT cu.*,s.fullname as school,p.fullname as program,p.programlevel,p.type from {local_curriculum} cu JOIN {local_program} p ON p.id=cu.programid JOIN {local_school} s On s.id=cu.schoolid where cu.id={$id} ";

        $curriculum = $DB->get_record_sql($sql);
        $table = new html_table();
        $table->align = array('right', 'left', 'right', 'left');
        $table->size = array('20%', '30%', '20%', '30%');
        $table->width = '100%';

        $curric = new html_table_cell();
        $curric->text = $curriculum->description;
        $curric->colspan = 3;

        $setting = $this->get_credithoursetting($id);

        if ($setting) {
            foreach ($setting as $settings) {
                if ($settings->entityid == 1)
                    $mincredithours = $settings->mincredithours;
                if ($settings->entityid == 2) {
                    if ($settings->subentityid == 1)
                        $freshmanhours = $settings->mincredithours;
                    if ($settings->subentityid == 2)
                        $sofomore = $settings->mincredithours;
                    if ($settings->subentityid == 3)
                        $junior = $settings->mincredithours;
                    if ($settings->subentityid == 4)
                        $senior = $settings->mincredithours;
                }
            }



            $curricachieve = new html_table_cell();
            $curricachieve->text = get_string('curriculumcriteria', 'local_curriculum') . $mincredithours;
            $curricachieve->colspan = 3;

            $curriculafsetting = new html_table_cell();
            //   $curriculafsetting->text.=get_string('curriculumfcriteria','local_curriculum');
            $curriculafsetting->text = get_string('freshmancrhr', 'local_curriculum') . '&nbsp;-&nbsp;' . $freshmanhours . '</br>';
            $curriculafsetting->text.=get_string('sophomorecrhr', 'local_curriculum') . '&nbsp;-&nbsp;' . $sofomore . '</br>';
            $curriculafsetting->text.=get_string('juniorcrhr', 'local_curriculum') . '&nbsp;-&nbsp;' . $junior . '</br>';
            $curriculafsetting->text.=get_string('seniorcrhr', 'local_curriculum') . '&nbsp;-&nbsp;' . $senior;
            $curriculafsetting->colspan = 3;
        }
        $programlevel = ($curriculum->programlevel == 1) ? 'Intermediate' : 'Advanced';
        $programtype = ($curriculum->type == 1) ? 'Online' : 'Offiline';
        $table->data[] = array('<b>Short Name:</b>', $curriculum->shortname, '<b>Valid Till:</b>', date('d-M-Y', $curriculum->enddate));
        $table->data[] = array('<b>Organization:</b>', $curriculum->school, '<b>Program:</b>', $curriculum->program);
        $table->data[] = array('<b>Program Type:</b>', $programtype, '<b>Program Level:</b>', $programlevel);
        $table->data[] = array('<b>Description:</b>', $curric);
        if ($setting) {
            $table->data[] = array('<b>Curriculum Criteria:</b>', $curricachieve);
            $table->data[] = array('<b>Curriculum Criteria-2:</b>', $curriculafsetting);
        }
        echo html_writer::table($table);
    }

}

class curriculumplan {

    private static $_plan;
    private $dbHandle;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_plan) {
            self::$_plan = new curriculumplan();
        }
        return self::$_plan;
    }

    /**
     * @function get_parentplans
     * @return parent plans in the format of array.
     * */
    function get_parentplans($curriculumid) {
        global $DB, $CFG;
        $out = array();
        $out[0] = "Select Plan";
        $plans = $DB->get_records('local_curriculum_plan', array('parentid' => 0, 'curriculumid' => $curriculumid));
        foreach ($plans as $plan) {
            $check = $this->get_dependency_list($plan->id);
            if ($check != 1)
                $out[$plan->id] = format_string($plan->fullname);
        }
        return $out;
    }

    /**
     * @method insert_plan
     * @todo Inserts a new record
     * @param  $plan(array)
     * @return Id of the inserted data
     * */
    function insert_plan($plan) {
        global $DB;
        $hierarchy = new hierarchy;
        if ($plan->parentid == 0) {
            $plan->depth = 1;
            $plan->path = '';
        } else {
            // parent item must exist
            $parent = $DB->get_record('local_curriculum_plan', array('id' => $plan->parentid));
            $plan->depth = $parent->depth + 1;
            $plan->path = $parent->path;
        }
        //get next child item that need to provide
        if (!$sortorder = $hierarchy->get_next_child_sortthread($plan->parentid, 'local_curriculum_plan')) {
            return false;
        }
        $plan->sortorder = $sortorder;
        //$plan->sortorder = $hierarchy->get_next_child_sortthread($plan->parentid, $table = 'local_curriculum_plan');
        $newplan = $DB->insert_record('local_curriculum_plan', $plan);
        $DB->set_field('local_curriculum_plan', 'path', $plan->path . '/' . $newplan, array('id' => $newplan));
        return $plan->id;
    }

    /**
     * @method update_plan
     * @todo Update the details of the existing plans
     * @param  $plan(array)
     * */
    function update_plan($planid, $newplan) {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $planupdate = new school();
        $oldplan = $DB->get_record('local_curriculum_plan', array('id' => $planid));
        $currenturl = "{$CFG->wwwroot}/local/curriculum/index.php";
        //check if the parentid is the same as that of new parentid
        if ($newplan->parentid != $oldplan->parentid) {
            $newparentid = $newplan->parentid;
            $newplan->parentid = $oldplan->parentid;
        }
        $now = date("d-m-Y");
        $now = strtotime($now);
        $newplan->timemodified = $now;

        $updated = $DB->update_record('local_curriculum_plan', $newplan);

        if (isset($newparentid)) {
            $updatedplan = $DB->get_record('local_curriculum_plan', array('id' => $planid));
            $newparentid = isset($newparentid) ? $newparentid : 0;
            //if the new parentid is different then update 
            $updated = $planupdate->update_school($updatedplan, $newparentid, 'local_curriculum_plan');
        }
        return $updated;
      
    }

    /**
     * @method delete_plan
     * @todo Delete the records from local_curriculum_plan
     * @param  $id(int)
     * */
    function delete_plan($id) {
        global $DB;
        return $DB->delete_records('local_curriculum_plan', array('id' => $id));
    }

    /**
     * @method display_curriculum_plan
     * @todo displays the plans in tree format
     * @param  $plan(array)
     * */
    function display_curriculum_plan($plan) {
        global $DB, $OUTPUT, $CFG;
        $itemdepth = 'depth' . $plan->depth;
        $itemicon = $OUTPUT->pix_url('/i/item');
        $out = html_writer::start_tag('div', array('class' => 'plan ' . $itemdepth, 'style' => 'background-image: url("' . $itemicon . '")'));
        $out .= html_writer::tag('a', format_string($plan->fullname), array('href' => '' . $CFG->wwwroot . '/local/curriculum/viewplan.php?id=' . $plan->id . '&cid=' . $plan->curriculumid . ''));
        $out .= html_writer::end_tag('div');

        $sql = "SELECT * FROM {local_curriculum_plancourses} cp,{local_cobaltcourses} cc where cp.planid={$plan->id} AND cp.courseid=cc.id";
        $plancourses = $DB->get_records_sql($sql);
        $itemdepth12 = "9";
        $itemdepth12 = 'equlanetdev';
        foreach ($plancourses as $plancourse) {
            $labelInd = "Equalents";
            $out .= html_writer::start_tag('div', array('class' => 'plan ' . $itemdepth12, 'style' => 'background-image: url("' . $itemicon . '");i'));
            $out .= html_writer::tag('a', format_string($plancourse->fullname), array('href' => '' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $plancourse->id . '&plugin=curriculum&page=viewcurriculum&title=View Curriculum'));
            $out .= html_writer::end_tag('div');
        }
        return $out;
    }

    /**
     * @method get_dependency_list
     * @todo checks any courses or other plans are depended on a plan
     * @param  $id(int) planid
     * */
    function get_dependency_list($id) {
        global $DB, $CFG;
        if ($DB->record_exists('local_curriculum_plancourses', array('planid' => $id)))
            return 1;
        if ($DB->record_exists('local_curriculum_plan', array('parentid' => $id)))
            return 2;
        return 0;
    }

    /**
     * @method name
     * @todo to get name based on condition
     * @param object $list (data)
     * @return string type of name
     * */
    function name($list) {
        global $DB, $CFG;
        $name = new stdClass();
        if (isset($list->schoolid))
            $name->school = $DB->get_field('local_school', 'fullname', array('id' => $list->schoolid));
        if (isset($list->programid))
            $name->program = $DB->get_field('local_program', 'fullname', array('id' => $list->programid));
        if (isset($list->curriculumid))
            $name->curriculum = $DB->get_field('local_curriculum', 'fullname', array('id' => $list->curriculumid));
        return $name;
    }

    /**
     * @method assign_courses
     * @todo assigns courses to the curriculum plan
     * @param  $courses(array) list of courses to be assigned
     * @param  $planid(int) Curriculum plan id
     * @param  $moduleid(int) module id to which courses are belongs to.
     * */
    function assign_courses($planid, $courses, $moveto, $moduleid, $departmentid = 0) {
        global $DB, $CFG;
        $courses = array_reverse($courses);
        foreach ($courses as $course) {
            $plan = new stdClass();
            $plan->planid = $planid;
            $plan->courseid = $course;
            if ($planid)
                $plan->curriculumid = $DB->get_field('local_curriculum_plan', 'curriculumid', array('id' => $planid));
            else
                $plan->curriculumid = $moveto;
            $plan->moduleid = $moduleid;
            $plan->departmentid = $departmentid;
            $plan->id = $DB->insert_record('local_curriculum_plancourses', $plan);
        }
        return true;
    }

    /**
     * @method unassign_course
     * @todo Un assigns the courses from the Curriculum plan
     * @param  int $planid Curriculm plan id
     * @param  int $cid Curriculum  id
     * @param  $courseid(int) Course ID.
     * */
    function unassign_course($planid, $cid, $courseid) {
        global $DB, $CFG;
        $params = array('courseid' => $courseid, 'curriculumid' => $cid);
        if ($planid) {
            $params['planid'] = $planid;
        }

        $DB->delete_records('local_curriculum_plancourses', $params);
    }

    /**
     * @method assign_modules
     * @todo Assigns all the courses under the module to the plan
     * @param  int $planid Curriculm plan ID
     * @param  int $moveto Curriculum id
     * @param  int $module Module ID.
     * */
    function assign_modules($planid, $moveto, $module) {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $cobaltcourses = $hierarchy->get_courses_module($module, $none = true);
        $crs = array();
        foreach ($cobaltcourses as $courseid => $coursename) {
            $params = array('courseid' => $courseid);
            if ($planid) {
                $params['planid'] = $planid;
            } else {
                $params['curriculumid'] = $moveto;
            }
            if (!$DB->record_exists('local_curriculum_plancourses', $params))
                $crs[] = $courseid;
        }
        return $this->assign_courses($planid, $crs, $moveto, $module);
    }

    /**
     * @method course_count
     * @todo Check the condition whether all the courses of the module are assigned to curriculum plan
      If so disable the assign buttons
     * @param  int $planid Curriculm plan ID
     * @param  int $cid Curriculum id
     * @param int $deptid Department id 
     * @param  int $moduleid Module ID.
     * @return boolean type of value
     * */
    function course_count($planid, $cid, $moduleid, $deptid) {
        global $DB, $CFG;
        if ($moduleid)
            $coursecount = $DB->count_records('local_module_course', array('moduleid' => $moduleid));
        $params = array('curriculumid' => $cid);
        if ($planid) {
            $params['planid'] = $planid;
        }
        $courses = $DB->get_records('local_curriculum_plancourses', $params);
        if ($deptid)
            $coursecount = $DB->count_records('local_cobaltcourses', array('departmentid' => $deptid));
        $mod = array();
        foreach ($courses as $course) {
            if ($moduleid && $DB->record_exists('local_module_course', array('moduleid' => $moduleid, 'courseid' => $course->courseid)))
                $mod[] = $course->courseid;
            if ($deptid && $DB->record_exists('local_cobaltcourses', array('departmentid' => $deptid, 'id' => $course->courseid)))
                $mod[] = $course->courseid;
        }
        if (sizeof($mod) == $coursecount)
            return true;
        return false;
    }


    /**
     * @method get_assigned_deptlist
     * @todo to get department list of a schools
     * @param array of objects $school school list
     * @param boolean $concate_withschoolname (if true it display department name with school name)
     * @return array of department list 
     * */
    function get_assigned_deptlist($school, $concate_withschoolname = false) {
        global $DB, $CFG;

        list($usql, $params) = $DB->get_in_or_equal($school);
        /*
         * ###Bugreport #188- Colleges and Assigned Departments
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Added school condition to get only departments from only assigned schools
         */

        /* Bug report #273  and  Bug report #188  -  Colleges and Assigned Departments
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved - added code to display shared departments and also displaying parent school with department name.
         */


        $departments = $DB->get_records_sql("SELECT * FROM {local_department} WHERE visible=1 AND schoolid $usql", $params);
        $depts = $DB->get_records_sql("SELECT d.* FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND d.visible=1 AND sd.assigned_schoolid $usql GROUP BY sd.deptid", $params);

        $departments = $departments + $depts;

        $d = array('Select Course Libraries');
        foreach ($departments as $dept) {
            // Edited by hema----------------------------------------------
            $school = $DB->get_record('local_school', array('id' => $dept->schoolid));
            if ($concate_withschoolname)
                $deptname = format_string($dept->fullname . ' - ' . $school->fullname);
            else
                $deptname = format_string($dept->fullname);
            //-------------------------------------------------------------   
            $d[$dept->id] = $deptname;
            // $d[$dept->id] = format_string($dept->fullname);
        }
        return $d;
    }

    /**
     * @method get_actions
     * @todo to get action button for view page
     * @return array of buttons
     * */
    public function get_actions($pluginname, $plugin, $id, $visible, $cid = null) {
        global $CFG, $OUTPUT;
        $buttons = html_writer::link(new moodle_url('/local/' . $pluginname . '/' . $plugin . '.php', array('id' => $id, 'curriculumid' => $cid, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
        $buttons .= html_writer::link(new moodle_url('/local/' . $pluginname . '/' . $plugin . '.php', array('id' => $id, 'curriculumid' => $cid, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
        if ($visible > 0) {
            $buttons .= html_writer::link(new moodle_url('/local/' . $pluginname . '/' . $plugin . '.php', array('id' => $id, 'curriculumid' => $cid, 'visible' => !$visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
        } else {
            $buttons .= html_writer::link(new moodle_url('/local/' . $pluginname . '/' . $plugin . '.php', array('id' => $id, 'curriculumid' => $cid, 'visible' => !$visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
        }
        return $buttons;
    }

}
