<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/timetable/renderhelper.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');

class timetable implements renderable {

    public $schoollist = array();
    public $sem_timingslist = array();
    public $scheduled_list = array();
    public $classtype_schools = array();
    public $view = '';

    public function __construct($schoollist = null, $view = null) {
        // here the widget is prepared and all necessary logic is performed
        global $CFG, $DB, $USER, $OUTPUT;

        $this->schoollist = $schoollist;
        $this->view = $view;
        $temp = array();
        if (isset($schoollist)) {
            $res = array();
            foreach ($schoollist as $school) {
                $res = $DB->get_records_sql("select * from {local_timeintervals} as time 
                  where time.schoolid=$school->id  group by schoolid,semesterid");
                $temp = $temp + $res;
            }
            $this->sem_timingslist = $temp;
        }
        if ($view == 'scheduled') {
            $res = array();
            foreach ($schoollist as $school) {

                //$tools = classes_get_school_semesters($school->id);
                //          $sql = "SELECT lc.id,lc.online,
                //      lc.visible AS visible,
                //      lc.classlimit AS classlimit,lc.departmentid,lc.cobaltcourseid,
                //      lc.shortname,cc.fullname AS coursename,cc.shortname as coursecode,
                //      lc.fullname AS classname,
                //      ls.fullname AS semestername,ls.id AS semesterid,
                //      s.fullname AS schoolname,s.id AS schoolid,
                //     (select Max(concat(FROM_UNIXTIME(lsc.startdate, '%d %b %Y'),'&nbsp; - &nbsp;',FROM_UNIXTIME(lsc.enddate, '%d %b %Y'))) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduledate,
                //     (select Max(concat(lsc.starttime,'&nbsp;-&nbsp;',lsc.endtime)) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduletime,
                //     (select DISTINCT lsc.availableweekdays FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS availableweekdays
                //
       //FROM {local_clclasses} lc JOIN {local_semester} ls 
                //ON lc.semesterid=ls.id JOIN {local_school} s 
                //ON lc.schoolid=s.id JOIN {local_cobaltcourses} cc 
                //ON lc.cobaltcourseid=cc.id where lc.schoolid={$school->id} order by scheduledate DESC";


                $sql = "SELECT lc.*,cc.fullname AS coursename,cc.shortname as coursecode,
             lc.fullname AS classname,
             ls.fullname AS semestername,ls.id AS semesterid,
             s.fullname AS schoolname,s.id AS schoolid
              FROM {local_clclasses} lc
              JOIN {local_semester} ls ON ls.id = lc.semesterid
              JOIN {local_school} s ON s.id =lc.schoolid
              JOIN {local_cobaltcourses} cc ON cc.id = lc.cobaltcourseid
              where lc.schoolid={$school->id} group by lc.id";


                $classlists = $DB->get_records_sql($sql);
                $res += $classlists;
            }
            //$res = $DB->get_records_sql("select * from {local_scheduleclass} as sc 
            //  where sc.schoolid=$school->id ");
            //$temp = $temp + $res;
            $this->scheduled_list = $res;
        }
    }

// end of function

    public function time_intervalslist($schoolid, $semesterid) {
        global $CFG, $DB, $USER, $OUTPUT;
        $res = array();
        $res = $DB->get_records_sql("select * from {local_timeintervals} where
                  schoolid=$schoolid and semesterid=$semesterid");

        return $res;
    }

    public function class_status($classid) {
        global $CFG, $USER, $DB, $PAGE;
        if ($classid) {
            $sql = " select noapproved, pendinguc + pending_adddrop as nopending, noexams

              FROM (
                   select IF(uc.registrarapproval=0,count(userid),0) as pendinguc,
                   IF(uc.registrarapproval=1,count(userid),0) as noapproved,    
                  (select count(userid) from {local_course_adddrop} where registrarapproval=0 and classid = $classid) as pending_adddrop ,
                      
                   (select count(id) from {local_scheduledexams} where classid= $classid  and visible=1 ) as noexams
                 FROM  {local_user_clclasses} as uc  where uc.classid = $classid) as t ";
            $classstatus = $DB->get_record_sql($sql);
            return $classstatus;
        } else
            return array();
    }

    public function get_class_scheduledinfo($classobject) {
        global $CFG, $USER, $DB, $PAGE;
        $result = array();
        $sql = "select sh.*, stype.classtype as classtype 
           FROM {local_scheduleclass} as sh
           JOIN {local_class_scheduletype}  as stype ON  stype.id=sh.classtypeid
           JOIN {local_timeintervals} as ti ON ti.id=sh.timeintervalid
           
           WHERE sh.classid=$classobject->id";

        //$sql="select sh.*
        //      FROM {local_scheduleclass} as sh WHERE sh.classid=$classobject->id " ;     
        $result = $DB->get_records_sql($sql);

        return $result;
    }

// end of function
}

// end of class

class local_timetable_renderer extends plugin_renderer_base {

    protected function render_timetable(timetable $tmobject) {
        if (!empty($tmobject->scheduled_list))
            return $this->timetable_scheduledclass_view($tmobject);
        else if ($tmobject->view == 'classtype') {
            $classtypeob = new classtype_view($tmobject->schoollist);
            return $classtypeob->timetable_school_classtypes_view();
        } else
            return $this->timetable_standaradtimings_view($tmobject);
    }

    public function timetable_standaradtimings_view($tmobject) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $PAGE->requires->js('/local/timetable/js/tmember_toggle.js');
        $systemcontext = context_system::instance();
        $tmobject->sem_timingslist;
        $j = 0;
        foreach ($tmobject->sem_timingslist as $list) {
           // print_object($tmobject->sem_timingslist);
            
            //  echo $list->teammanagerid;
            $line = array();
            $schoolinfo = $DB->get_record('local_school', array('id' => $list->schoolid, 'visible' => 1));
            $semesterinfo = $DB->get_record('local_semester', array('id' => $list->semesterid, 'visible' => 1));

            //  $userinfo = $this->user_picturedisplay($list, true);
            //  $tmobject1 = new teammanager(null, $list->userid);
            //  $tmcount = $tmobject1->teammembercount;
            if ($j > 0)
                $displaynone = "display:none";
            else
                $displaynone = "";
                
            $buttons = $this->to_get_action_buttons($list->id, $list, true, true, false,0);
            $firstrow = "<ul id='settiming_firstrow'><li>" . get_string('school_time', 'local_timetable') . '<b>' . $schoolinfo->fullname . "</b></li>";
            $firstrow .="<li>" . get_string('semester_time', 'local_timetable') . '<b>' . $semesterinfo->fullname . "</b></li></ul>";
            $firstrow .=$buttons;

            $extrainfo = "<ul id = 'tm-info' class = 'mview'>";
            $extrainfo .= "<li>" . get_string('timescheduled', 'local_timetable') . "<b> Yes</b></li>";
            $extrainfo .= "<li>" . get_string('updateddate', 'local_timetable') . '<b>' . date('d-m-Y', $list->timemodified) . "</b></li>";
           // $extrainfo .= "<li>" . get_string('publish', 'local_timetable') . ':<b>' . ($list->visible ? 'Yes' : 'No') . "</b></li>";
            $extrainfo .= "<li>" . get_string('timeintervals', 'local_timetable') . html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch'), 'class' => 'iconsmall', 'onclick' => 'teammember_list(' . $list->id . ')', 'id' => 'tm_switchbutton'), array('style' => 'cursor:pointer')) . "</li>";
            $extrainfo .= "</ul>";

            // $extrainfo .= "<li>No of Team members: $tmcount "  "</li>";
            // $extrainfo .= "<li>".html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch'), 'alt' => get_string('teammembercount', 'local_teammanager'), 'class' => 'iconsmall', 'onclick' => 'teammember_list(' . $list->userid . ')'), array('title' => get_string('teammembercount', 'local_teammanager'), 'id' => 'teammanager' . $list->userid . '', 'onclick' => 'teammember_list(' . $list->userid . ')'))."</li></ul>";                  
            // $extrainfo .=html_writer::tag('div', 'Last login: ' . Date('D -M -Y', $member->lastlogin), array('style' => 'float:right;', 'id' => 'tmlastlogindiv'));
            $toggle = "<div id = 'dialog$list->id' class = 'tmem_toggle dialog1' style = '$displaynone;clear:both; '>";
            $timingslist = $tmobject->time_intervalslist($list->schoolid, $list->semesterid);
            $toggle .= $this->toggle_timeintervalsview($list->schoolid, $list->semesterid, $timingslist);
            $toggle .="</div>";

            $cell1 = new html_table_cell();
            $cell1->attributes['class'] = 'tmcell';
            $cell1->text = $firstrow . $extrainfo . $toggle;
            $line[] = $cell1;
            $line[] = $schoolinfo->fullname;
            $line[] = $semesterinfo->fullname;

            $data[] = $line;
            $j++;
        }

        $PAGE->requires->js('/local/timetable/js/timeintervalsview.js');
        $table = new html_table();
        //if (has_capability('local/costcenter:manage', $systemcontext))
        $table->head = array(get_string('schooltimesettings', 'local_timetable'));
        $table->id = "timeintervalsmain_view";
        $table->size = array('100%');
        $table->align = array('left', 'left', 'left');
        $table->width = '99%';
        $table->data = $data;

        $output = "<div id='filter-box' >";
        $output .= '<div class="filterarea"></div></div>';
        $output .= html_writer::table($table);



        return $output;
    }

    public function includde_jquery_files() {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        //$PAGE->requires->js('/local/timetable/dtjs/jquery.min.js');
        //$PAGE->requires->js('/local/timetable/dtjs/jquery.dataTables.min.js');
        //$PAGE->requires->js('/local/timetable/dtjs/jquery.jeditable.js');
        // $PAGE->requires->js('/local/timetable/dtjs/jquery-ui.js');   
        //$PAGE->requires->js('/local/timetable/dtjs/jquery.validate.js');
        //$PAGE->requires->js('/local/timetable/dtjs/jquery.dataTables.editable.js');        
    //
    }

    public function toggle_timeintervalsview($schoolid, $semesterid, $timeintervalslist) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $this->includde_jquery_files();
        //$PAGE->requires->jquery_plugin('migrate');
        // $PAGE->requires->js('/local/timetable/js/jquery.dataTables.editable.js');

        $scid = $schoolid;
        $semid = $semesterid;
        $i = 0;
        // print_object($timeintervalslist);
        foreach ($timeintervalslist as $key => $list) {
            $line = array();
            $line[] = get_string('intervals', 'local_timetable', $i);
            $line[] = date('h:i a', strtotime($list->starttime));
            $line[] = date('h:i a', strtotime($list->endtime));
            $buttons = $this->to_get_action_buttons($list->id, $list, true, false, true, 1);
            $line[] = $buttons;
            $row = new html_table_row();
            $row->cells = $line;
            $row->id = $list->id;
            $data[] = $row;
            $i++;
        }

 
        if(empty($data)){            
         $row = new html_table_row();                 
        $optioncell = new html_table_cell(get_string('no_records', 'local_request'));
        $optioncell->colspan = 5;   
        $row ->cells[] = $optioncell;
        $data[]=$row;
        }
        //$('#timeinterval_toggleview$semesterid'+'_paginate').hide();

        echo html_writer::script("
                $(document).ready(function() {
                $('#timeinterval_toggleview$semesterid').dataTable({
             
              
                  'iDisplayLength': 5,
                  'sPaginationType': 'bootstrap',
                   'aaSorting': [],
               
                'fnDrawCallback': function(oSettings) {
                if(oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
          
                }
                },
 
        'oLanguage': {
            'oPaginate': {
                'sFirst': '<<',
                'sLast': '  >>  ',
                'sNext': '  >',
                'bStateSave': true,
                'sPrevious': ' <  '
            },
                        'sLengthMenu': ''
            },
                  'bInfo' : false,
                'searching': false,
                bFilter: false,
                'aaSorting': [],
                'lengthMenu':''
        } );
                } );
                ");


        $table = new html_table();
        $table->head = array(get_string('interval_tablehead', 'local_timetable'),
            get_string('starttime', 'local_timetable'),
            get_string('endtime', 'local_timetable'),
            get_string('action', 'local_timetable'));
        $table->id = "timeinterval_toggleview$semesterid";
        $table->class = 'tmember';
        $table->size = array('30%', '30%', '30%');
        $table->align = array('left', 'left', 'left');
        $table->width = '80%';
        $table->data = $data;

        $output = html_writer::table($table);
        return $output;
    }

    public function to_get_action_buttons($rowid, $list, $delete = true, $update = true, $visible = true, $toggle = 0) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $systemcontext = context_system::instance();
        //   displaying crud operation button
        if ($delete) {
            $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
            if (has_any_capability($delete_cap, $systemcontext)) {
                $options[] = array('link' => new moodle_url('/local/timetable/settimings.php', array('id' => $rowid, 'delete' => 1, 'from' => $toggle, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
            }
        }


        if ($update) {
            $update_cap = array('local/timetable:manage', 'local/timetable:update');
            if (has_any_capability($update_cap, $systemcontext)) {
                $options[] = array('link' => new moodle_url('/local/timetable/settimings.php', array('id' => $rowid, 'from' => $toggle, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }
        }

        if ($visible) {
            $visible_cap = array('local/timetable:manage', 'local/timetable:visible');
            if (has_any_capability($visible_cap, $systemcontext)) {
                if ($list->visible) {
                    $options[] = array('link' => new moodle_url('/local/timetable/settimings.php', array('id' => $list->id, 'from' => $toggle, 'hide' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
                } else {
                    $options[] = array('link' => new moodle_url('/local/timetable/settimings.php', array('id' => $list->id, 'show' => 1, 'from' => $toggle, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
                }
            }
        }
        $menulist = array();
        foreach ($options as $types) {
            $menulist[] = html_writer::link($types['link'], $types['string']);
            $menulist[] = '<hr />';
        }
        // Remove the last element (will be an HR)
        array_pop($menulist);
        if ($toggle) {
            $menulist = '';
            foreach ($options as $types) {
                $menulist .= html_writer::link($types['link'], $types['string']);
            }

            return $menulist;
        } else {
            $action_buttons = html_writer::alist($menulist, array('class' => 'cl_curdrow'), 'ul');
            return $action_buttons;
        }
    }

    public function timetable_scheduledclass_view($tmobject) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $PAGE->requires->js('/local/timetable/js/tmember_toggle.js');
        $output = '';
        $timetablelibob = manage_timetable::getInstance();
        $classlist = $tmobject->scheduled_list;
        $semclass = new schoolclasses();
        if ($classlist) {
            $j = 0;
            foreach ($classlist as $clas) {
                if ($j > 0)
                    $displaynone = "display:none";
                else
                    $displaynone = "";
                $line = array();
                // $instructor[] = $clas->instructor;
                $instructor_info = $timetablelibob->timetable_display_instructorname($clas);
                $first_inner = $this->get_firstpart_ofrow($clas);
                $status = $tmobject->class_status($clas->id);

                // $extrainfo = html_writer::start_tag('div', array('class' => 'myteam_ul'));
                $second_inner = "<ul id='timetable_secondinner'><li>" . get_string('instructor', 'local_cobaltcourses') . " :" . $instructor_info;
                $second_inner .= "<li>" . get_string('noenrollments', 'local_timetable') . ": $status->noapproved</li>";
                $second_inner .= "<li>" . get_string('scheduleinfo', 'local_timetable') . html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch'), 'class' => 'iconsmall', 'onclick' => 'teammember_list(' . $clas->id . ')', 'id' => 'tm_switchbutton'), array('style' => 'cursor:pointer')) . "</li></ul>";

                $toggle = "<div id = 'dialog$clas->id' class = 'tmem_toggle dialog1' style = '$displaynone;clear:both; '>";

                $toggle .= $this->toggle_scheduleclassview($clas, $tmobject);
                $toggle .="</div>";
                //$schedule = $DB->get_field('local_scheduleclass', 'id', array('classid' => $clas->id));
                //if (!$schedule) {
                //    $schedule = -1;
                //}
                //
                ////   displaying buttons
                //$options = array(array('link' => new moodle_url('/local/clclasses/enroluser.php', array('id' => $clas->id, 'semid' => $clas->semesterid, 'activesemid' => $activesemesterid)), 'string' => get_string('enrollusers', 'local_clclasses')),
                //    array('link' => new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $schedule, 'classid' => $clas->id, 'semid' => $clas->semesterid, 'schoid' => $clas->schoolid, 'deptid' => $clas->departmentid, 'courseid' => $clas->cobaltcourseid, 'sesskey' => sesskey())), 'string' => get_string('assigninstructor', 'local_clclasses')),
                //    array('link' => new moodle_url('/local/clclasses/examsetting.php', array('id' => $clas->id, 'semid' => $clas->semesterid, 'schoolid' => $clas->schoolid, 'sesskey' => sesskey())), 'string' => get_string('criteria', 'local_clclasses')),
                //    array('link' => new moodle_url('/local/evaluations/create_evaluation.php', array('clid' => $clas->id, 'sesskey' => sesskey())), 'string' => get_string('evaluation', 'local_clclasses'))
                //);
                //$menulist = array();
                //foreach ($options as $types) {
                //    $menulist[] = '<button>' . html_writer::link($types['link'], $types['string']) . '</button>';
                //    $menulist[] = '<hr />';
                //}
                //// Remove the last element (will be an HR)
                //array_pop($menulist);
                // Display the content as a list
                //  $third_inner = html_writer::alist($menulist, array('class' => 'third_inner cl_buttonrow'), 'ul');
                $third_inner = "<div id='timetable_thirdinner'>$clas->semestername</div>";
                $cell1 = new html_table_cell();
                $cell1->attributes['class'] = 'colms_cell';
                $cell1->text = $first_inner . $second_inner . $toggle . $third_inner;
                $line[] = $cell1;
                $line[] = $clas->classname;
                $line[] = $clas->semestername;
                $line[] = ($clas->online == 1 ? 'Online' : 'Offline');
                $data[] = $line;
                $j++;
            }// end of  foreach
        }// end of if



        $PAGE->requires->js('/local/timetable/js/scheduleview.js');
        $output = "<div id='filter-box' >";
        $output .= '<div class="filterarea"></div></div>';

        $table = new html_table();
        $table->head = array('', '', '', '');
        $table->id = "timetable_sc";
        $table->size = array('100%');
        $table->align = array('left');
        $table->width = '99%';
        $table->data = $data;

        $output .= html_writer::table($table);
        return $output;
    }

//end of function

    public function get_firstpart_ofrow($clas) {
        global $CFG, $DB, $USER, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $semclass = new schoolclasses();
        $first_inner = '';
        // to get instructor name
        //$instructor = array();
        //$instructor[] = $clas->instructor;
        //$instructor_info = implode(', ', $instructor[0]);
        //   displaying crud operation button
        $delete_cap = array('local/clclasses:manage', 'local/clclasses:delete');


        $schedule = $DB->get_records('local_scheduleclass', array('classid' => $clas->id));

        $update_cap = array('local/clclasses:manage', 'local/clclasses:update');
        if (has_any_capability($update_cap, $systemcontext)) {
            //  if(empty($schedule))
            $options[] = array('link' => new moodle_url('/local/timetable/scheduleclass.php', array('classid' => $clas->id, 'sesskey' => sesskey())), 'string' => get_string('scheduleclass', 'local_timetable'), 'action' => 'update');
            // else
            //$options[]  = array( 'link'=>new moodle_url('/local/timetable/scheduleclass.php', array('id' =>$schedule->id, 'classid' => $clas->id, 'sesskey' => sesskey())), 'string'=>get_string('editscheduledclass','local_timetable'),'action'=>'update');
        }

        if (has_any_capability($delete_cap, $systemcontext)) {
            $options[] = array('link' => new moodle_url('/local/timetable/scheduleclass.php', array('classid' => $clas->id, 'delete' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')), 'action' => 'delete');
        }
        //$visible_cap = array('local/clclasses:manage', 'local/clclasses:visible');
        //if (has_any_capability($visible_cap, $systemcontext)) {
        //    if ($classlist->visible) {
        //         $options[]  = array( 'link'=>new moodle_url('/local/timetable/schedule_class.php', array('id' => $classlist->id, 'page' => $page, 'hide' => 1, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
        //    } else {
        //         $options[]  = array( 'link'=>new moodle_url('/local/timetable/schedule_class.php', array('id' => $classlist->id, 'page' => $page, 'show' => 1, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
        //    }
        //}

        $menulist = array();
        foreach ($options as $types) {
            if ($types['action'] === 'update')
                $menulist[] = html_writer::link($types['link'], $types['string'], array('class' => 'timetable_scbutton'));
            else
                $menulist[] = html_writer::link($types['link'], $types['string']);
            $menulist[] = '<hr />';
        }
        // Remove the last element (will be an HR)
        array_pop($menulist);


        // Display the content as a list
        $first_inner = '';
        $first_inner = html_writer::start_tag('div', array('class' => 'first_inner'));
        $first_inner .= html_writer::alist($menulist, array('class' => 'cl_curdrow'), 'ul');

        $first_inner .= html_writer::start_tag('span', array('class' => 'first_inner1'));
        $first_inner .='<span id="timetable_clinfo">' . $clas->classname . '</span>';




        //if ($clas->scheduledate) {
        //    if(isset($schedule->starttime)){
        //    $date = date('d M Y',$schedule->startdate) .' - '. date('d M Y',$schedule->enddate);
        //    $time = date('h:i a', strtotime($schedule->starttime)).' - '.date('h:i a', strtotime($schedule->endtime));
        //    $weekdays= $schedule->availableweekdays;    
        //    }
        //}

        if ($schedule)
            $first_inner .= get_string('scheduled', 'local_clclasses') . html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid'), 'title' => get_string('scheduled', 'local_clclasses'), 'alt' => get_string('scheduled', 'local_clclasses'), 'class' => 'iconsmall'));
        else
            $first_inner .= get_string('scheduled', 'local_clclasses') . html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'), 'title' => get_string('scheduled', 'local_clclasses'), 'alt' => get_string('notyetscheduled', 'local_clclasses'), 'class' => 'iconsmall'));
        //   $first_inner .='<li>'.get_string('time','local_timetable').': <b>'.($time?$time:'------------------' ).'</b></li>';
        //   $first_inner .='<li>'.get_string('availableweekdays','local_timetable').': <b>'.($weekdays?$weekdays:'------------------' ).'</b></li></ul>';




        $first_inner .= html_writer::end_tag('span');

        $first_inner .= html_writer::end_tag('div');

        return $first_inner;
    }

    public function toggle_scheduleclassview($classobject, $tmobject) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $schedulerecords = $tmobject->get_class_scheduledinfo($classobject);

        // print_object($timeintervalslist);
        if ($schedulerecords) {

            foreach ($schedulerecords as $record) {
                $line = array();
                $line[] = $record->classtype;

                $date = date('d M Y', $record->startdate) . ' - ' . date('d M Y', $record->enddate);
                $time = date('h:i a', strtotime($record->starttime)) . ' - ' . date('h:i a', strtotime($record->endtime));
                //    $weekdays= $schedule->availableweekdays;    
                //    }
                //}

                $datetime_inner = '<ul id="toggle_scview"><li>' . ($date ? $date : '------------------' ) . '</li>';
                $datetime_inner.='<li>' . ($time ? $time : '------------------' ) . '</li></ul>';
                $line[] = $datetime_inner;

                //   $datetime_inner .='<li>'.get_string('availableweekdays','local_timetable').': <b>'.($weekdays?$weekdays:'------------------' ).'</b></li></ul>';

                $line[] = $record->availableweekdays;
                if ($record->instructorid) {
                    $insname = $DB->get_record_sql("select * from {user} where id=$record->instructorid  and deleted=0");
                    $line[] = $insname->firstname . ' ' . $insname->lastname;
                } else
                    $line[] = get_string('notassigned', 'local_clclasses');
                $line[] = $this->action_buttons_toggle_scheduleclass_view($record);

                //  $line[] ='<a class="table-action-deletelink" href="deletedata.php?id='.$list->id.'">Delete</a>';
                $row = new html_table_row();
                $row->cells = $line;
                // $row->id = $list->id;
                $data[] = $row;
                // $i++;
            }
        }
        else {
            $row = new html_table_row();                 
        $optioncell = new html_table_cell(get_string('notscheduledyet', 'local_timetable'));
        $optioncell->colspan = 5;   
        $row ->cells[] = $optioncell;
        $data[]=$row;
        }

        // print_object($data);

        //echo html_writer::script("
        //                $(document).ready(function() {
        //                $('#tm_toggleview$scid').dataTable({
        //                'iDisplayLength': 5,
        //                'fnDrawCallback': function(oSettings) {
        //                if(oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
        //                console.log('hi');
        //                $('#tm_toggleview$scid'+'_paginate').hide();
        //                $('#tm_toggleview$scid'+'_length').hide();
        //
        //                }
        //                },
        //                'aLengthMenu': [[5,  10, 25, 50, -1], [ 5,  10, 25, 50, 'All']],
        //        'searching': false,
        //        'aaSorting': [],
        //        'emptyTable': 'No data available in table',
        //        'info': '',
        //        'zeroRecords': 'No matching records found',
        //        'language': {
        //        'paginate': {
        //        'next': '>>',
        //        'previous':'<<'
        //        }
        //        },
        //        } );
        //        } );
        //        ");


        $table = new html_table();
        $table->attributes = array('class' => 'custom_toggleview');
        $table->id = "schedule_toggleview";
        $table->head = array(get_string('classtype', 'local_timetable'),
            get_string('datetimings', 'local_timetable'),
            get_string('availableweekdays', 'local_timetable'),
            get_string('instructor', 'local_cobaltcourses'),
            get_string('action', 'local_clclasses'));


        $table->size = array('20%', '23%', '15%', '20%', '12%');
        $table->align = array('center', 'center', 'center', 'center', 'center');
        $table->width = '90%';
        $table->data = $data;

        $output = html_writer::table($table);
        return $output;
    }

// end of function

    public function action_buttons_toggle_scheduleclass_view($scheduledinfo) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $rowid = $scheduledinfo->id;
        $classid = $scheduledinfo->classid;
        $systemcontext = context_system::instance();
        //   displaying crud operation button
        $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
        if (has_any_capability($delete_cap, $systemcontext)) {
            $options[] = array('link' => new moodle_url('/local/timetable/scheduleclass.php', array('id' => $rowid, 'delete' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
        }

        $update_cap = array('local/timetable:manage', 'local/timetable:update');
        if (has_any_capability($update_cap, $systemcontext)) {
            $options[] = array('link' => new moodle_url('/local/timetable/scheduleclass.php', array('id' => $rowid, 'edit' => 1, 'classid' => $classid, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
        }

        $visible_cap = array('local/timetable:manage', 'local/timetable:visible');
        if (has_any_capability($visible_cap, $systemcontext)) {
            if ($scheduledinfo->visible > 0) {
                $options[] = array('link' => new moodle_url('/local/timetable/scheduleclass.php', array('id' => $rowid, 'visible' => 0, 'confirm' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
            } else {
                $options[] = array('link' => new moodle_url('/local/timetable/scheduleclass.php', array('id' => $rowid, 'visible' => 1, 'confirm' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
            }
        }

        $menulist = array();
        foreach ($options as $types) {
            $menulist[] = html_writer::link($types['link'], $types['string']);
            $menulist[] = '<hr />';
        }
        // Remove the last element (will be an HR)
        array_pop($menulist);
        $action_buttons = html_writer::alist($menulist, array('class' => 'togglesc_actionbutton'), 'ul');
        return $action_buttons;
    }

}

// end of class
?>