
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 // task code: T1.8 - schedule multiple classtype in class plugin

require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');

class local_courseregistration_render implements renderable {

    public $schoollist = array();
    // to get specific school class list
    public $classlist = array();
    // to get specfic school active semester list
    public $s_semlist = array();
    
    private $activesemid;
    

  /*  public function __construct(array $schoollist) {
// here the widget is prepared and all necessary logic is performed
        global $CFG, $DB, $USER;
        $hierarchy = new hierarchy();
        $this->schoollist = $schoollist;
        $res = array();
        foreach ($schoollist as $school) {
            if ($school->id != null) {
                /* ---Get the records from the database---        
                $sql= "SELECT lc.*,cc.fullname AS coursename,cc.shortname as coursecode,
             lc.fullname AS classname,
             ls.fullname AS semestername,ls.id AS semesterid,
             s.fullname AS schoolname,s.id AS schoolid
              FROM {local_clclasses} lc
              JOIN {local_semester} ls ON ls.id = lc.semesterid
              JOIN {local_school} s ON s.id =lc.schoolid
              JOIN {local_cobaltcourses} cc ON cc.id = lc.cobaltcourseid
              where lc.schoolid={$school->id} group by lc.id";       
       
            }
            $classlists = $DB->get_records_sql($sql);
            $res += $classlists;
            // print_object($this->s_classlist);
// edited by hema on 23 jun 2014: used to fetch current semesterid
          
        }

        $this->s_classlist = $res;
    } */

// end of function 
    
    public function student_enrolled_classlist(){        
        global $CFG, $USER, $DB, $PAGE; 
         $classlist=array();
         $today = date('Y-m-d');
        $query = "SELECT lc.*,cc.id AS courseid,
                     cc.fullname AS coursename, s.id as semid, s.fullname as sem,
                     cc.credithours AS credithours                   
                     FROM {local_user_clclasses} c
                     JOIN {local_clclasses} lc ON c.classid=lc.id            
                     JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
                     JOIN {local_semester} s On s.id=c.semesterid
                     where c.userid={$USER->id} AND c.studentapproval=1 AND c.registrarapproval=1 AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate))";                        
        
        $classlist=$DB->get_records_sql($query);  
        
        $this->classlist=$classlist;
        return $classlist;
        
    }// end of function
    
    public function instructor_assigned_classlist(){
         global $CFG, $USER, $DB, $PAGE; 
         $classlist=array();
         $today = date('Y-m-d');
         
         $insquery = "SELECT lc.*,cc.id AS courseid,
                  cc.fullname AS coursename, s.id as semid,
                  cc.credithours AS credithours                    
                  FROM {local_scheduleclass} c
                  JOIN {local_clclasses} lc ON c.classid=lc.id
                  JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
                  JOIN {local_semester} s On s.id=c.semesterid           
                  where c.instructorid={$USER->id}  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate)) ";                         
        
        
         $classlist=$DB->get_records_sql($insquery);  
        
       $this->classlist=$classlist;
        return $classlist;
        
        
    } // end of function
    

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
    }// end of function
    
    
    public function get_class_scheduledinfo($classobject){
        global $CFG, $USER, $DB, $PAGE;
     $result=array();    
     $sql="select sh.*, stype.classtype as classtype 
           FROM {local_scheduleclass} as sh
           JOIN {local_class_scheduletype}  as stype ON  stype.id=sh.classtypeid
           JOIN {local_timeintervals} as ti ON ti.id=sh.timeintervalid
           
           WHERE sh.classid=$classobject->id";
           
     //$sql="select sh.*
     //      FROM {local_scheduleclass} as sh WHERE sh.classid=$classobject->id " ;     
     $result=$DB->get_records_sql($sql);
    
     return $result;       
        
    }// end of function


}// end of class



class local_courseregistration_renderer extends plugin_renderer_base {

    protected function render_local_courseregistration_render(local_courseregistration_render $clobject) {
        global $DB, $USER;
        if(isloggedin()){
           $context = context_user::instance($USER->id);
        }
        if (has_capability('local/courseregistration:view', $context)) {
            $classlist = $clobject->student_enrolled_classlist(); 
        } else{
            $classlist = $clobject->instructor_assigned_classlist();
        }
        return $this->mycalsses_view($classlist, $clobject);
    }

// end of function

    public function mycalsses_view($classlist, $clobject) {
         global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        if(isloggedin())
          $context = context_user::instance($USER->id);        
         $hierarchy = new hierarchy();
        $PAGE->requires->js('/local/timetable/js/tmember_toggle.js');
        $semclass = new schoolclasses();
        if ($classlist) {
             $j=0;
            foreach ($classlist as $clas) {
             if($j>0)
             $displaynone = "display:none";
             else
             $displaynone = "";
                $line = array();           

                $first_inner=$this->get_firstpart_ofrow( $clas);

             /*   $status = $clobject->class_status($clas->id);
                // $extrainfo = html_writer::start_tag('div', array('class' => 'myteam_ul'));
                
                $numberofapproved=(isset($status->noapproved)?$status->noapproved:0);
                $numberofpending=(isset($status->nopending)?$status->nopending:0);
                $numberofexams=(isset($status->noexams)?$status->noexams:0); */
                 if($clas->onlinecourseid){
                 $onlinecourseinfo = $DB->get_record('course',array('id'=>$clas->onlinecourseid,'visible'=>1));
                 $onlinecoursename= $onlinecourseinfo->fullname;
                 }else{
                  $onlinecoursename = 'Not assigned'; 
                 }
                 
                $second_inner = "<ul id='local_coursereg_second_inner'><li class='color_green'>Class  Code: $clas->shortname </li>";
                $second_inner .= "<li class='color_green'>Course : $onlinecoursename </li>";          
                $second_inner .= "<li class='color_green' >". html_writer::tag('a',get_string('launch','local_courseregistration'), array('href' => '' . $CFG->wwwroot . '/course/view.php?id=' . $clas->onlinecourseid . '', 'title' => get_string('view_academicdetail', 'local_courseregistration')))."</li>";
                $second_inner .= "<li class='color_green'>".get_string('scheduleinfo','local_timetable'). html_writer::empty_tag('img', array('src' =>  $OUTPUT->pix_url('t/switch'),  'class' => 'iconsmall', 'onclick' => 'teammember_list(' . $clas->id . ')', 'id' => 'tm_switchbutton'), array( 'style' => 'cursor:pointer')) ."</li>";
                if (!has_capability('local/courseregistration:view', $context)) {
                  $second_inner .= '<li> <a title="View Class Details" href="' . $CFG->wwwroot . '/local/courseregistration/mystudents.php?id=' . $clas->id . '" >' . get_string('view_student_progress', 'local_courseregistration') . '</a></li></ul>';
                 }
                //$schedule = $DB->get_field('local_scheduleclass', 'id', array('classid' => $clas->id));
                //if (!$schedule) {
                //    $schedule = -1;
                //}
                $toggle = "<div id = 'dialog$clas->id' class = 'tmem_toggle dialog1' style = '$displaynone;clear:both; '>";              
                $toggle .= $this->toggle_scheduleclassview($clas, $clobject);
                $toggle .="</div>";            

                //   displaying buttons
         /*       $options = array(array('link' => new moodle_url('/local/clclasses/classenrol.php', array('id' => $clas->id, 'semid' => $clas->semesterid, 'activesemid' =>  $activesemesterid)), 'string' => get_string('enrollusers', 'local_clclasses')),
                    array('link' => new moodle_url('/local/clclasses/scheduleclass.php', array('id' => -1, 'classid' => $clas->id, 'semesterid' => $clas->semesterid, 'schoolid' => $clas->schoolid, 'deptid' => $clas->departmentid, 'courseid' => $clas->cobaltcourseid, 'sesskey' => sesskey())), 'string' => get_string('scheduleclass_timetable', 'local_timetable')),
                    array('link' => new moodle_url('/local/clclasses/examsetting.php', array('id' => $clas->id, 'semid' => $clas->semesterid, 'schoolid' => $clas->schoolid, 'sesskey' => sesskey())), 'string' => get_string('criteria', 'local_clclasses')),
                    array('link' => new moodle_url('/local/evaluations/create_evaluation.php', array('clid' => $clas->id, 'sesskey' => sesskey())), 'string' => get_string('evaluation', 'local_clclasses'))
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
                $third_inner .= "<span id='classes_thirdinner'>$clas->semestername</span>"; */
                $cell1 = new html_table_cell();
                $cell1->attributes['class'] = 'colms_cell';
                $cell1->text = $first_inner . $second_inner . $toggle;
                $line[] = $cell1;
                $line[] = $clas->classname;
                $line[] = $clas->semestername;
                $line[] = ($clas->online==1? 'Online':'Offline');
                
                $data[] = $line;
              $j++;
            }// end of  foreach
        }// end of if
           
         
        $PAGE->requires->js('/local/clclasses/filters/classes.js');
        /*$output = "<div id='filter-box' >";
        $output .=  '<div class="filterarea"></div></div>';*/

        $table = new html_table();
        $table->head = array('','','');
        $table->id = "classtable";
        $table->size = array('100%');
        $table->align = array('left');
        $table->width = '99%';
        $table->data = $data;

        $output = html_writer::table($table);
        return $output;
    }
    
    
    public function get_firstpart_ofrow( $clas){
        global $CFG, $DB, $USER, $PAGE, $OUTPUT;
          $timetablelibob = manage_timetable::getInstance();
        $systemcontext = context_system::instance();
         $semclass = new schoolclasses();
         $first_inner ='';
          // to get instructor name
                $instructor = array();
                $instructor_info =$timetablelibob->timetable_display_instructorname($clas);
            
            
             //   displaying crud operation button
            $delete_cap = array('local/clclasses:manage', 'local/clclasses:delete');
            if (has_any_capability($delete_cap, $systemcontext)) {
                $options[] =array( 'link'=>new moodle_url('/local/clclasses/createclass.php', array('id' => $clas->id, 'delete' => 1, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
            }

            $update_cap = array('local/clclasses:manage', 'local/clclasses:update');
            if (has_any_capability($update_cap, $systemcontext)) {
               $options[]  = array( 'link'=>new moodle_url('/local/clclasses/createclass.php', array('id' => $clas->id, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }

            $visible_cap = array('local/clclasses:manage', 'local/clclasses:visible');
            if (has_any_capability($visible_cap, $systemcontext)) {
                if ($clas->visible) {
                     $options[]  = array( 'link'=>new moodle_url('/local/clclasses/createclass.php', array('id' => $clas->id,  'hide' => 1, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
                } else {
                     $options[]  = array( 'link'=>new moodle_url('/local/clclasses/createclass.php', array('id' => $clas->id,  'show' => 1, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
                }
            }
            

             /*   $menulist = array();
                
                $scheduled=$DB->get_records('local_scheduleclass',array('classid'=>$clas->id));
                if($scheduled)
                 $menulist[0] =get_string('scheduled','local_clclasses').html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/grade_correct'), 'title' => get_string('scheduled','local_clclasses'), 'alt' => get_string('scheduled','local_clclasses'), 'class' => 'iconsmall'));
                else
                 $menulist[0] =get_string('scheduled','local_clclasses').html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'), 'title' => get_string('scheduled','local_clclasses'), 'alt' => get_string('notyetscheduled','local_clclasses'), 'class' => 'iconsmall'));
                 
                foreach ($options as $types) {
                    $menulist[] =  html_writer::link($types['link'], $types['string']);
                    $menulist[] = '<hr />';
                }
                // Remove the last element (will be an HR)
                array_pop($menulist); */
                // Display the content as a list
                 $first_inner = html_writer::start_tag('div', array('class' => 'first_inner'));
               // $first_inner .= html_writer::alist($menulist, array('class' => 'cl_curdrow'), 'ul');
              
                $first_inner .= html_writer::start_tag('span', array('class' => 'first_inner1'));
                $first_inner .='<span class="td_mainitem" id="cl_name">'. $clas->shortname.': '.$clas->classname .'</span><span id="cl_instructorname" class="item_italic item_gray">  with '. $instructor_info . '</span>';
                //if ($clas->scheduledate) {
                //   $first_inner .='<span >'.  $clas->scheduledate;
                //    !empty($classlist->availableweekdays) ?  $first_inner .= $clas->scheduletime . '<br />(' . $clas->availableweekdays . ')' :  $first_inner .=  $clas->scheduletime;
                //    $first_inner .='</span>'; 
                //} else {                  
                //    $first_inner .='<span>'. html_writer::tag('a', get_string('scheduleclass', 'local_classroomresources'), array('href' => '' . $CFG->wwwroot . '/local/clclasses/scheduleclass.php?classid=' . $clas->id . '&semid=' . $clas->semesterid . '&schoid=' . $clas->schoolid . '&deptid=' . $clas->departmentid . '&courseid=' . $clas->cobaltcourseid . ''));
                //   $first_inner .=html_writer::tag('a', get_string('scheduleclass', 'local_classroomresources'), array('href' => '' . $CFG->wwwroot . '/local/clclasses/scheduleclass.php?classid=' . $clas->id . '&semid=' . $clas->semesterid . '&schoid=' . $clas->schoolid . '&deptid=' . $clas->departmentid . '&courseid=' . $clas->cobaltcourseid . '')).'</span>';
                //}
               /* $first_inner .=  html_writer::start_tag('ul', array('class' => 'first_innerclassdetail'));
                $first_inner .= '<li class="item_gray">'.get_string('offlinecoursename','local_clclasses').' :<b>'.$clas->coursename.'</b>, ';
                $first_inner .= get_string('type','local_clclasses').':<b>'.($clas->online==1? 'Online' :'Offline').'</b>';
                if($clas->online==1){
                $onlinecourse  =$DB->get_record('course',array('id'=>$clas->onlinecourseid));
                if($onlinecourse){
                   $onlinecourselink = html_writer::tag('a', $onlinecourse->fullname, array('href' => ''.$CFG->wwwroot.'/course/view.php?id='.$onlinecourse->id.''));
                   $first_inner .= ',   '. get_string('cobaltcourse','local_clclasses').': <b>'.$onlinecourselink.'</b></li>';
                  }
                }
                $first_inner .= html_writer::end_tag('ul'); */
                $first_inner .= html_writer::end_tag('span');
                
                
           
                
                
                $first_inner .= html_writer::end_tag('div');
        
        return $first_inner;
        
    }// end of function
    
    
       public function toggle_scheduleclassview( $classobject,$tmobject)  {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $schedulerecords=$tmobject->get_class_scheduledinfo($classobject);   
    
       // print_object($timeintervalslist);
        if($schedulerecords){
            
        foreach ($schedulerecords as  $record) {
                    $line=array();
                    $line[] = $record->classtype;                
                    
                    $date = date('d M Y',$record->startdate) .' - '. date('d M Y',$record->enddate);
                    $time = date('h:i a', strtotime($record->starttime)).' - '.date('h:i a', strtotime($record->endtime));
                //    $weekdays= $schedule->availableweekdays;    
                //    }
                //}
              
                    $datetime_inner ='<ul id="toggle_scview"><li>'.($date?$date:'------------------' ).'</li>';                   
                    $datetime_inner.='<li>'.($time?$time:'------------------' ).'</li></ul>';
                    $line[] = $datetime_inner;
                    
                //   $datetime_inner .='<li>'.get_string('availableweekdays','local_timetable').': <b>'.($weekdays?$weekdays:'------------------' ).'</b></li></ul>';
                
                 $line[]=$record->availableweekdays;
                 if($record->instructorid){
                 $insname =$DB->get_record_sql("select * from {user} where id=$record->instructorid  and deleted=0");
                 $line[]=$insname->firstname.' '.$insname->lastname;
                 }
                 else
                 $line[]=get_string('notassigned','local_clclasses');
                 
               //  $line[]=$this->action_buttons_toggle_scheduleclass_view($record);

              //  $line[] ='<a class="table-action-deletelink" href="deletedata.php?id='.$list->id.'">Delete</a>';
                $row = new html_table_row();
                $row->cells =$line;
               // $row->id=$list->id;
                $data[]=$row;
              //  $i++;
            }
            
        }
        else{
          $row = new html_table_row();                 
          $optioncell = new html_table_cell(get_string('no_records', 'local_request'));
          $optioncell->colspan = 7;   
          $row ->cells[] = $optioncell;
          $data[]=$row;            
        }
            

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
       $table->attributes = array('class'=>'custom_toggleview');
          $table->id = "schedule_toggleview";
        $table->head = array(get_string('classtype','local_timetable'),
                             get_string('datetimings','local_timetable'),
                             get_string('availableweekdays','local_timetable'),
                             get_string('instructor','local_cobaltcourses')
                            );
      
        
        $table->size = array('20%', '23%','15%','20%','12%');
        $table->align = array('center', 'center','center','center','center');
        $table->width = '99%';
        $table->data = $data;

        $output = html_writer::table($table);
        return $output;
    }// end of function
    
    
            public function action_buttons_toggle_scheduleclass_view($scheduledinfo){
           global $DB, $CFG, $OUTPUT, $USER, $PAGE;
           $rowid=$scheduledinfo->id;
           $classid= $scheduledinfo->classid;
           $systemcontext = context_system::instance();
            //   displaying crud operation button
            $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
            if (has_any_capability($delete_cap, $systemcontext)) {
                $options[] =array( 'link'=>new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $rowid, 'delete' => 1, 'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
            }

            $update_cap = array('local/timetable:manage', 'local/timetable:update');
            if (has_any_capability($update_cap, $systemcontext)) {
               $options[]  = array( 'link'=>new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $rowid, 'edit'=>1, 'classid'=>$classid ,'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }

            $visible_cap = array('local/timetable:manage', 'local/timetable:visible');
            if (has_any_capability($visible_cap, $systemcontext)) {
                if ($scheduledinfo->visible > 0) {
                    $options[]=  array( 'link'=>new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $rowid, 'visible' => 0, 'confirm'=>1,'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
                } else {
                    $options[]= array( 'link'=>new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $rowid, 'visible' => 1,'confirm'=>1,'sesskey' => sesskey())), 'string'=>html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
                }
            }

                $menulist = array();
                foreach ($options as $types) {
                    $menulist[] =  html_writer::link($types['link'], $types['string']);
                    $menulist[] = '<hr />';
                }
                // Remove the last element (will be an HR)
                array_pop($menulist);
                $action_buttons = html_writer::alist($menulist, array('class' => 'togglesc_actionbutton'), 'ul');
                return $action_buttons;
        
        }
    
    


}// end of class
?>