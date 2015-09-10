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
 * Bulk grades registration script from a comma separated file
 *
 * @package    local
 * @subpackage grades
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/gradesubmission/lib.php');
require_once('upload_grades_lib.php');
require_once('upload_grades_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60 * 60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);
require_login();
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
if (!has_capability('local/gradesubmission:manage', $systemcontext)) {
    print_error('permissions_error','local_collegestructure');
}
$returnurl = new moodle_url('/local/gradesubmission/index.php');
$PAGE->set_url('/local/gradesubmission/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_gradesubmission') . ' : ' . get_string('uploadgrades', 'local_gradesubmission');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_gradesubmission'), new moodle_url('/local/gradesubmission/index.php'));
$PAGE->navbar->add(get_string('uploadgrades', 'local_gradesubmission'));
global $USER, $DB;
// array of all valid fields for validation
$STD_FIELDS = array(
    'schoolname' => 'schoolname',
    'semestername' => 'semestername',
    'classname' => 'classname',
    'studentname' => 'studentname'
);
$ltypes = $DB->get_records_sql("select * from {local_lecturetype}");
foreach ($ltypes as $ltype) {
    $list[] = $ltype->id;
}
$etypes = $DB->get_records_sql("select * from {local_examtypes}");
foreach ($etypes as $etype) {
    $elist[] = $etype->id;
}
$llist = implode(',', $list);
$ellist = implode(',', $elist);

$PRF_FIELDS = array();
if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
    $schools = get_assignedschools_inst();
    // error handling
    if(empty($schools))
    throw new cobalt_exception('not_assigned_dept','local_gradesubmission');
    
    foreach ($schools as $school) {
        $schoollist[] = $school->id;
    }
    
    $schoollist_string = implode(',', $schoollist);
    $today = date('Y-m-d');
    $mysql = "SELECT sem.* FROM {local_semester} AS sem
                        JOIN {local_school_semester} AS scl
                        ON scl.semesterid = sem.id";
    $where = " WHERE scl.schoolid IN ($schoollist_string)";
    $where .= " AND  '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' )";
    $group = " GROUP BY scl.semesterid";
    $sems = $DB->get_records_sql($mysql . $where . $group);
    foreach ($sems as $sem) {
        $sem_lists[] = $sem->id;
    }
    $sem_list = implode(',', $sem_lists);
    $class_insts = $DB->get_records_sql('select * from {local_scheduleclass} where instructorid =' . $USER->id . ' and semesterid in (' . $sem_list . ') ');
    foreach ($class_insts as $class_inst) {
        $ins_lists[] = $class_inst->classid;
    }
    $ins_list = implode(',', $ins_lists);
    $sql = "SELECT  exam.id as eid, type.examtype as examtype, exam.classid as csid, lt.lecturetype as lecturetype, lt.id as lid, exam.schoolid FROM {local_scheduledexams}  exam,  {local_lecturetype}  lt, {local_examtypes}  type, {local_clclasses} cs where  exam.classid in ($ins_list) AND type.id = exam.examtype and lt.id =exam.lecturetype ";
    $exams = $DB->get_records_sql($sql);
    // print_object($exams);exit;
} else {
    $hier = new hierarchy();
    $schools = $hier->get_assignedschools();
    if (is_siteadmin()) {
        $schools = $hier->get_school_items();
    }
    // Error handling
    if(empty($schools))
    print_error('not_assignedanyschool','local_collegestructure');
    
    foreach ($schools as $school) {
        $schoollist[] = $school->id;
    }
    $schoollist_string = implode(',', $schoollist);
    $today = date('Y-m-d');
    $mysql = "SELECT sem.* FROM {local_semester} AS sem
						JOIN {local_school_semester} AS scl
						ON scl.semesterid = sem.id";
    $where = " WHERE scl.schoolid IN ($schoollist_string)";
    $where .= " AND  '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' )";
    $group = " GROUP BY scl.semesterid";
    $sems = $DB->get_records_sql($mysql . $where . $group);
    foreach ($sems as $sem) {
        $sem_lists[] = $sem->id;
    }
    $sem_list = implode(',', $sem_lists);

    if(empty($sem_list)){
    echo $OUTPUT->header();    
    print_cobalterror('noactive_semester','local_gradesubmission');   
    }
    $sql = "SELECT  exam.id as eid, type.examtype as examtype, exam.classid as csid, lt.lecturetype as lecturetype, lt.id as lid, exam.schoolid FROM {local_scheduledexams}  exam,  {local_lecturetype}  lt, {local_examtypes}  type, {local_clclasses} cs where  exam.classid= cs.id AND exam.schoolid in ($schoollist_string) AND exam.semesterid in ($sem_list) AND type.id = exam.examtype AND lt.id =exam.lecturetype ";
    $exams = $DB->get_records_sql($sql);
}

// $sql = "SELECT  exam.id as eid, type.examtype as examtype, exam.classid as csid, lt.lecturetype as lecturetype, lt.id as lid, exam.schoolid FROM {local_scheduledexams}  exam,  {local_lecturetype}  lt, {local_examtypes}  type, {local_clclasses} cs where  exam.classid= cs.id AND exam.schoolid in ($schoollist_string) AND exam.semesterid in ($sem_list) AND type.schoolid = exam.schoolid AND lt.schoolid =exam.schoolid AND type.id = exam.examtype AND lt.id =exam.lecturetype ";
$exams = $DB->get_records_sql($sql);
foreach ($exams as $exam) {
    $exam->examtype = strtolower($exam->examtype);
    $exam->lecturetype = strtolower($exam->lecturetype);
    $cname = $DB->get_field('local_clclasses', 'fullname', array('id' => $exam->csid));
    $cname = strtolower($cname);
    $PRF_FIELDS[$exam->csid . ':' . $exam->eid . ':' . $exam->lid] = $cname . ':' . $exam->examtype . ':' . $exam->lecturetype;
}
// print_object( $PRF_FIELDS);
//-------- if variable $iid equal to zero,it allows enter into the form-----------------------------------
if (empty($iid)) {
    $mform1 = new admin_grades_form1();
    if ($mform1->is_cancelled())
        redirect($returnurl);
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadgrades');
        $cir = new csv_import_reader($iid, 'uploadgrades'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('gradesfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_grades_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('managegradesubmission', 'local_gradesubmission'));
        //--------tab code----------------------
        $currenttab = 'upload';
        createtabview_gsub($currenttab, 0);
        //--------------------------------------------

        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('uploaddes', 'local_gradesubmission'));
        }
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';
        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploadgrades');
    $filecolumns = uu_validate_grades_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadgradessresult', 'local_gradesubmission'));
$currenttab = 'upload';
createtabview_gsub($currenttab, 0);
$optype = 'UU_GRADES_ADDNEW';
$gradesnew = 0;
$gradesupdated = 0;
$gradesuptodate = 0; //not printed yet anywhere
$gradeserrors = 0;
$gradesskipped = 0;
// init csv import helper
$cir->init();
$linenum = 1; //column header is first line
// init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
$upt = new uu_progress_tracker();
// $upt->start(); // start table
loop:
while ($line = $cir->next()) {
    $upt->flush();
    $linenum++;
    // $upt->track('line', $linenum);
    $grades = new stdClass();
    // add fields to grades object
    foreach ($line as $keynum => $value) {
        if (!isset($filecolumns[$keynum])) {
            // this should not happen
            continue;
        }
        $key = $filecolumns[$keynum];
        $grades->$key = $value;
    }
    if (!isset($grades->classname)) {
        // prevent warnings below
        $grades->classname = '';
    }
    if (!isset($grades->semestername)) {
        // prevent warnings below
        $grades->semestername = '';
    }
    if (!isset($grades->studentname)) {
        // prevent warnings below
        $grades->studentname = '';
    }
    //--------------------------------this is used to include only new grades-----------------
    if ($optype == 'UU_GRADES_ADDNEW') {
        // grades creation is a special case - the gradesname may be constructed from templates using firstname and lastname
        // better never try this in mixed update types
        $error = false;
        if ((!isset($grades->classname) || !isset($grades->semestername) || !isset($grades->studentname)) or ( ($grades->classname === '') || ($grades->semestername === '') || ($grades->studentname === ''))) {
            $error = true;
        }
        if ($error) {
            $gradeserrors++;
            continue;
        }
    }
    // make sure we really have gradesname
    if (empty($grades->studentname) || empty($grades->semestername) || empty($grades->classname)) {
        $gradeserrors++;
        continue;
    }
    // add default values for remaining fields
    $formdefaults = array();
    foreach ($STD_FIELDS as $field) {
        if (isset($grades->$field)) {
            continue;
        }
        // all validation moved to form2
        if (isset($formdata->$field)) {
            // process templates
            $formdefaults[$field] = true;
        }
    }
    foreach ($PRF_FIELDS as $field) {
        if (isset($grades->$field)) {
            continue;
        }
        if (isset($formdata->$field)) {
            // process templates
            $formdefaults[$field] = true;
        }
    }
    // save the new grades to the database
    $school = $DB->get_record('local_school', array('fullname' => $grades->schoolname));
    $semester = $DB->get_record('local_semester', array('fullname' => $grades->semestername));
    $class = $DB->get_record('local_clclasses', array('fullname' => $grades->classname));
    $user = $DB->get_record_select("user", "CONCAT(firstname, ' ', lastname) = :student", array('student' => $grades->studentname));
    $exgrades = new stdclass();
    $clsgrades = new stdclass();

    if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
        $schools = get_inst_assignedschools();
        $c = 0;
        foreach ($schools as $scl) {
            if ($scl->id == $school->id) {
                ++$c;
                break;
            }
        }
        if ($c == 0) {
            echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $grades->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // check semester
        $semesters = get_inst_school_semesters($school->id);
        $d = 0;
        foreach ($semesters as $sem) {
            if ($sem->id == $semester->id) {
                ++$d;
                break;
            }
        }
        if ($d == 0) {
            echo '<h3 style="color:red;">Sorry you are not assigned to this semester "' . $grades->semestername . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // check class
        $class_asgd = $DB->record_exists('local_scheduleclass', array('classid' => $class->id, 'instructorid' => $USER->id));
        if ($class_asgd == false) {
            echo '<h3 style="color:red;">Sorry you are not assigned to this class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
    } else {
        $hier = new hierarchy();
        $schools = $hier->get_assignedschools();
        if (is_siteadmin()) {
            $schools = $hier->get_school_items();
        }
        // check schools
        if (empty($grades->schoolname)) {
            echo '<h3 style="color:red;">Please enter Schoolname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($school)) {
            echo '<h3 style="color:red;">Invalid school "' . $grades->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $c = 0;
        foreach ($schools as $scl) {
            if ($school->id == $scl->id) {
                ++$c;
                break;
            }
        }
        if ($c == 0) {
            echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $grades->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
    }
    // ============Check Semester =================
    if (empty($grades->semestername)) {
        echo '<h3 style="color:red;">Please enter Semestername in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        goto loop;
    }
    if (empty($semester)) {
        echo '<h3 style="color:red;">Invalid Semestername "' . $grades->semestername . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        goto loop;
    }
    if (!$record = $DB->get_record_sql("SELECT * FROM {local_semester} sem, {local_school_semester} sch WHERE sch.semesterid = sem.id AND sem.id = $semester->id AND sch.schoolid = $school->id")) {
        echo '<h3 style="color:red;">The Semestername: "' . $grades->semestername . '" doesn\'t exists under the School: "' . $grades->school . '" in line no. ' . $linenum . ' of uploaded excelsheet</h3>';
        goto loop;
    }
    // check if instructor assigned to semester
    // check Class 
    if (empty($grades->classname)) {
        echo '<h3 style="color:red;">Please enter Class name in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        goto loop;
    }
    if (empty($class)) {
        echo '<h3 style="color:red;">Invalid Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        goto loop;
    }

    $x = $DB->record_exists('local_clclasses', array('id' => $class->id, 'schoolid' => $school->id, 'semesterid' => $semester->id));
    if ($x == false) {
        echo '<h3 style="color:red;">The Class: "' . $grades->classname . '" doesn\'t exists under the Semester: "' . $grades->semestername . '" in line no. ' . $linenum . ' of uploaded excelsheet</h3>';
        goto loop;
    }

    // check users enrolled to the class 
    $gradesub = grade_submission::getInstance();
    $true = $DB->record_exists('local_user_clclasses', array('classid' => $class->id, 'semesterid' => $semester->id, 'userid' => $user->id, 'registrarapproval' => 1));
    if ($true == false) {
        echo '<h3 style="color:red;">Student "' . $grades->studentname . '" is not enrolled to the Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        goto loop;
    }
    // check exams are created for the class
    $classexams = $gradesub->get_class_exams($semester->id, $class->id);
    if (empty($classexams)) {
        echo '<h3 style="color:red;">No Exam is selected in Class Completion criteria for this Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        goto loop;
    }
    // check exams are completed to the class
    $today = date('Y-m-d');
    foreach ($classexams as $exam) {
        if (date('Y-m-d', $exam->opendate) > $today) {
            echo '<h3 style="color:red;">Exams are not  completed for the Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of 	uploaded excelsheet.</h3>';
            goto loop;
        }
    }
    $sum = 0;
    $grademaxtot = 0;
    $source = 'offline';
    // print_object($PRF_FIELDS);
    foreach ($PRF_FIELDS as $key => $value) {
        $e = explode(':', $key);
        $e[1] = $DB->get_field('local_scheduledexams', 'examtype', array('id' => $e[1]));
        if (isset($grades->$value) && !empty($grades->$value)) {
            $existype = $DB->get_record_sql("select id from {local_scheduledexams} where examtype= $e[1] and schoolid = $school->id  and semesterid = $semester->id and classid = $class->id and lecturetype = $e[2]");
            if (empty($existype)) {
                echo '<h3 style="color:red;">Invalid gradesubmission at line no' . $linenum . ' .This "' . $value . '" type of exam  is not scheduled for class ' . $grades->classname . ' </h3>';
                goto loop;
            } else {
                $max = $DB->get_record('local_scheduledexams', array('examtype' => $e[1], 'schoolid' => $school->id, 'semesterid' => $semester->id, 'classid' => $class->id, 'lecturetype' => $e[2]));
                if ($max->grademax < $grades->$value) {
                    echo '<h3 style="color:red;">Maximum marks exceeded for exam "' . $value . '" of class "' . $grades->classname . '"at line no ' . $linenum . ' of excelsheet</h3>';
                    goto loop;
                }

                $sum = $sum + $grades->$value;
                $grademaxtot = $grademaxtot + $max->grademax;
                $exgrades->userid = $user->id;
                $exgrades->source = $source;
                $exgrades->schoolid = $school->id;
                $exgrades->programid = 0;
                $exgrades->semesterid = $semester->id;
                $exgrades->classid = $class->id;
                $exgrades->courseid = $class->cobaltcourseid;
                $exgrades->examid = $max->id;
                $exgrades->finalgrade = $grades->$value;
                $exgrades->timecreated = time();
                $exgrades->timemodified = 0;
                $exgrades->usermodified = $USER->id;
                $sql = "SELECT * from {local_user_examgrades} where userid={$user->id} and semesterid={$semester->id} and classid={$class->id} and examid={$max->id} and source='{$source}'";
                $record_exist = $DB->get_record_sql($sql);
                if ($record_exist) {
                    $exgrades->id = $record_exist->id;
                    $exgrades->timemodified = time();
                    $exam->usermodified = $USER->id;
                    $DB->update_record('local_user_examgrades', $exgrades);
                    $DB->update_record('local_user_exgrade_hist', $exgrades);
                } else {
                    $exgrades->id = $DB->insert_record('local_user_examgrades', $exgrades);
                    $DB->insert_record('local_user_exgrade_hist', $exgrades);
                }
            }
        }
    }

    // foreach($PRF_FIELDS as $key=>$value) {
    // if (isset($grades->$PRF_FIELDS[$value] ) && !empty($grades->$PRF_FIELDS[$value])) {
    // $examtypeid = $DB->get_record_sql("select id from {local_examtypes} where examtype='$PRF_FIELDS[$key]' and schoolid = $school->id  ");
    // $existype = $DB->get_record_sql("select id from {local_scheduledexams} where examtype= $examtypeid->id and schoolid = $school->id  and semesterid = $semester->id and classid = $class->id");
    // if (empty ($existype)) {
    // echo '<h3 style="color:red;">Invalid gradesubmission at '.$linenum.' This type of exam "'.$PRF_FIELDS[$key].'" is not scheduled for class '.$grades->classname.' </h3>';
    // goto loop;
    // } else {
    // $max = $DB->get_record('local_scheduledexams', array('examtype'=>$examtypeid->id, 'schoolid'=>$school->id,  'semesterid'=>$semester->id, 'classid'=>$class->id));
    // if ($max->grademax < $grades->$PRF_FIELDS[$value]) {
    // echo '<h3 style="color:red;">Maximum marks exceeded for exam "'.$PRF_FIELDS[$key].'" of class "'.$grades->classname.'"at line no '.$linenum.' of excelsheet</h3>';
    // goto loop;
    // }
    // $sum = $sum + $grades->$PRF_FIELDS[$value];
    // $grademaxtot = $grademaxtot + $max->grademax;
    // $exgrades->userid = $user->id;
    // $exgrades->source = $source;
    // $exgrades->schoolid = $school->id;
    // $exgrades->programid = 0;
    // $exgrades->semesterid = $semester->id;
    // $exgrades->classid = $class->id;
    // $exgrades->courseid = $class->cobaltcourseid;
    // $exgrades->examid = $max->id;
    // $exgrades->finalgrade = $grades->$PRF_FIELDS[$value];
    // $exgrades->timecreated = time();
    // $exgrades->timemodified = 0;
    // $exgrades->usermodified = $USER->id;
    // $sql = "SELECT * from {local_user_examgrades} where userid={$user->id} and semesterid={$semester->id} and classid={$class->id} and examid={$max->id} and source='{$source}'";
    // $record_exist = $DB->get_record_sql($sql);
    // if ($record_exist){
    // $exgrades->id = $record_exist->id; 
    // $exgrades->timemodified = time();
    // $exam->usermodified = $USER->id;
    // $DB->update_record('local_user_examgrades', $exgrades);
    // $DB->update_record('local_user_exgrade_hist', $exgrades);
    // } else {                                                    
    // $exgrades->id = $DB->insert_record('local_user_examgrades', $exgrades);
    // $DB->insert_record('local_user_exgrade_hist', $exgrades);
    // }
    // }
    // }
    // }
    $sql = "SELECT * from {local_user_classgrades} where userid={$user->id} and semesterid={$semester->id} and classid={$class->id}";
    $record_exists = $DB->get_record_sql($sql);
    if ($record_exists) {
        $clsgrades->id = $record_exists->id;
        $clsgrades->coursetotal = $sum;
        $clsgrades->percentage = ($sum / $grademaxtot) * 100;
        $per = $clsgrades->percentage;
        $psql = "SELECT letter,gradepoint from {local_gradeletters} where {$per} BETWEEN markfrom and markto and schoolid=$school->id";
        $gradepoint = $DB->get_record_sql($psql);
        if ($gradepoint) {
            $gletter = $gradepoint->letter;
            $gpoint = $gradepoint->gradepoint;
        } else {
            $gletter = 'Not Defined';
            $gpoint = '0';
        }
        $clsgrades->gradeletter = $gletter;
        $clsgrades->gradepoint = $gpoint;
        $clsgrades->timemodified = time();
        $clsgrades->usermodified = $USER->id;
        $DB->update_record('local_user_classgrades', $clsgrades);
        $DB->update_record('local_user_clsgrade_hist', $clsgrades);
    } else {
        $clsgrades->userid = $user->id;
        $clsgrades->schoolid = $school->id;
        $clsgrades->programid = 0;
        $clsgrades->semesterid = $semester->id;
        $clsgrades->classid = $class->id;
        $clsgrades->courseid = $class->cobaltcourseid;
        $clsgrades->coursetotal = $sum;
        $clsgrades->percentage = ($sum / $grademaxtot) * 100;
        $per = $clsgrades->percentage;
        $psql = "SELECT letter,gradepoint from {local_gradeletters} where {$per} BETWEEN markfrom and markto and schoolid=$school->id";
        $gradepoint = $DB->get_record_sql($psql);
        if ($gradepoint) {
            $gletter = $gradepoint->letter;
            $gpoint = $gradepoint->gradepoint;
        } else {
            $gletter = 'Not Defined';
            $gpoint = '0';
        }
        $clsgrades->gradeletter = $gletter;
        $clsgrades->gradepoint = $gpoint;
        $clsgrades->timecreated = time();
        $clsgrades->timemodified = 0;
        $clsgrades->usermodified = $USER->id;
        $clsgrades->id = $DB->insert_record('local_user_classgrades', $clsgrades);
        $DB->insert_record('local_user_clsgrade_hist', $clsgrades);
    }
    $clsgrades->id++;
    $gradesnew++;
}
// $upt->close(); // close table
// $cir->close();
$cir->cleanup(true);
echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
echo '<p>';
if ($optype != UU_GRADES_UPDATE) {
    echo get_string('gradescreated', 'local_gradesubmission') . ': ' . $gradesnew . '<br />';
}
if ($gradesskipped) {
    echo get_string('gradesskipped', 'local_gradesubmission') . ': ' . $gradesskipped . '<br />';
}
if ($gradeserrors)
    echo get_string('errors', 'local_gradesubmission') . ': ' . $gradeserrors;
echo '</p>';
if ($gradeserrors)
    echo '<h4> Please fill the sheet without any errors. Refer Help Manual for assistance.</h4>';
echo $OUTPUT->box_end();

if ($gradesskipped) {
    echo $OUTPUT->box_start('generalbox');
    if ($gradesskipped == 1)
        echo '<h4> Grades skipped because record with that name is  already exists.</h4>';
    else
        echo '<h4>' . $gradesskipped . ' Grades skipped because records with those names are already exist.</h4>';
    echo $OUTPUT->box_end();
}
echo '<div style="margin-left:35%;"><a href="index.php"><button>Continue</button></a></div>';
echo $OUTPUT->footer();
die;
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadgradespreview', 'local_gradesubmission'));
$currenttab = 'upload';
createtabview_gsub($currenttab, 0);
// Print the form if valid values are available
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;

