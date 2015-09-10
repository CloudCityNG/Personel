<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/tag/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/request/lib/lib.php');
global $USER, $DB;
$profiletablecss = '/local/users/css/profiletable.css';
$PAGE->requires->css($profiletablecss);
$id = optional_param('id', $USER->id, PARAM_INT);

$PAGE->set_url('/local/users/profile.php', array('id' => $id));

$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
if (!is_siteadmin() && !has_capability('local/collegestructure:manage', $systemcontext) && !has_capability('local/clclasses:approvemystudentclclasses', $systemcontext) && !has_capability('local/clclasses:submitgrades', $systemcontext)) {
    if ($id != $USER->id) {
        print_error('You dont have permissions');
    }
} elseif ((has_capability('local/clclasses:approvemystudentclclasses', $systemcontext) || has_capability('local/clclasses:submitgrades', $systemcontext)) && !is_siteadmin()) {
    $instment = $DB->get_records('local_school_permissions', array('userid' => $USER->id));
    if ($id != $USER->id) {
        foreach ($instment as $users) {
            $student = $DB->get_record_sql("select * from {local_userdata} where schoolid = $users->schoolid and userid = $id group by userid");
            if (empty($student)) {
                print_error('You dont have permissions');
            }
        }
    }
}
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}
require_login();
$name = $DB->get_record('user', array('id' => $id));
$local_users = $DB->get_record('local_users', array('userid' => $id));

$PAGE->set_heading($SITE->fullname);
$strheading = get_string('viewprofile', 'local_users');

if ($id == $USER->id) {
    $PAGE->navbar->add(get_string('myprofile', 'local_users'));
} else {
    $PAGE->navbar->add(get_string('browseusers', 'local_users'));
}
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$requests = new requests();
$userid = $id ? $id : $USER->id;       // Owner of the page
if ((!$user = $DB->get_record('user', array('id' => $userid))) || ($user->deleted)) {

    echo $OUTPUT->header();
    if (!$user) {
        echo $OUTPUT->notification(get_string('invaliduser', 'error'));
    } else {
        echo $OUTPUT->notification(get_string('userdeleted'));
    }
    echo $OUTPUT->footer();
    die;
}

$currentuser = ($user->id == $id);
$context = $usercontext = context_user::instance($id, MUST_EXIST);

echo $OUTPUT->header();


echo '<br></br><div class="userprofilebox clearfix">';
echo "<span class='proheading'>" . $name->firstname . " " . $name->lastname . "</span>";
echo '<div class="profilepicture" >';
echo $OUTPUT->user_picture($user, array('size' => 50, 'class' => 'mypic'));
echo '</div><div class="myinfo" >';
echo "<table class='schoolinfo'  ><tr class='tableproheading'><td style='font-size:25px'>" . $name->firstname . " " . $name->lastname . "</td></tr>";
echo "<tr><td style='font-size:10px;color:#000;'><img src='" . $CFG->wwwroot . "/pix/emailpro.png' style='vertical-align:middle;' />&nbsp;<a href='mailto:" . $name->email . "'>" . $name->email . "</a>&nbsp;&nbsp;&nbsp;&nbsp;<img src='" . $CFG->wwwroot . "/pix/phonepro.png' style='vertical-align:middle;' />&nbsp;" . $name->phone1 . "</td></tr>";


$s1 = array();
$s3 = array();
$p = array();
if ($school = $requests->school($id)) {
    foreach ($school as $schools) {
        $schoolid = $schools->id;
        $schoolname = $schools->fullname;
        $s1[] = $schoolname;
        $programs = $requests->program($schoolid, $id);
        foreach ($programs as $program) {
            $proid = $program->id;
            $p[] = $DB->get_field('local_program', 'fullname', array('id' => $proid));
            if ($serviceid = $requests->service($schoolid, $proid, $id)) {
                $s3[] = $serviceid->serviceid;
            }
        }
    }

    if ($s1) {
        $s2 = implode(',&nbsp;', $s1);
        echo "<tr><td><b>School: </b>" . $s2 . "</td></tr>";
    }
    if ($p) {
        $pro = implode('&nbsp;', $p);
        echo "<tr><td><b>Program: </b>" . $pro . "</td></tr>";
    }
}

if ($others_school = $DB->get_records('local_school_permissions', array('userid' => $id))) {
    global $departmentname, $departmentid;
    $s1 = array();
    foreach ($others_school as $school) {

        $schoolid = $school->schoolid;
        $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
        $s1[] = $schoolname;
        $systemcontext = context_system::instance();

        $departmentid = $DB->get_records('local_dept_instructor', array('schoolid' => $schoolid, 'instructorid' => $id));
        if ($departmentid) {
            foreach ($departmentid as $dept) {
                $departmentname = $DB->get_field('local_department', 'fullname', array('id' => $dept->departmentid));
                echo "<tr><td>" . $departmentname . "</td></tr>";
            }
        }
    }

    $s2 = implode(',&nbsp;', $s1);
    echo "<tr><td>" . $s2 . "</td></tr>";
}


echo "</table>";

echo '</div>';

$context = context_user::instance($id);
if (!has_capability('local/clclasses:enrollclass', $context, $id) && !is_siteadmin()) {
    echo "<div class = 'address'>";
    if ($local_users->fathername && $local_users->currenthno && $local_users->pob && $local_users->region && $local_users->town && $name->country) {
        echo "<table  cellspacing=\"5\" cellpadding = \"5\" class='expstu1' >
          <tr><td style='width:30%'><b>" . get_string('contactname', 'local_admission') . "</b></td><td style='width:70%'>:&nbsp;&nbsp;" . $local_users->fathername . "</td></tr>
          <tr><td style='width:30%'><b>" . get_string('hno', 'local_admission') . "</b></td><td style='width:70%'>:&nbsp;&nbsp;" . $local_users->currenthno . "</td></tr>
          <tr><td style='width:30%'><b>" . get_string('pob', 'local_admission') . "</b></td><td style='width:70%'>:&nbsp;&nbsp;" . $local_users->pob . "</td></tr>
              </table><table cellspacing=\"5\" cellpadding = \"5\"  class='expstu2'>
              <tr><td style='width:30%'><b>" . get_string('region', 'local_admission') . "</b></td><td style='width:70%'>:&nbsp;&nbsp;" . $local_users->region . "</td></tr>
              <tr><td style='width:30%'><b>" . get_string('town', 'local_admission') . "</b></td><td style='width:70%'>:&nbsp;&nbsp;" . $local_users->town . "</td></tr>
              <tr><td style='width:30%'><b>" . get_string('pcountry', 'local_admission') . "</b></td><td style='width:70%'>:&nbsp;&nbsp;" . get_string($name->country, 'countries') . "</td></tr>
          
</table>";
        echo "<table  cellspacing=\"5\" cellpadding = \"5\" class='expstu3' >
          <tr><td ><b>" . get_string('contactname', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->fathername . "</td></tr>
          <tr><td ><b>" . get_string('hno', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->currenthno . "</td></tr>
          <tr><td ><b>" . get_string('pob', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->pob . "</td></tr>
              <tr><td ><b>" . get_string('region', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->region . "</td></tr>
              <tr><td ><b>" . get_string('town', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->town . "</td></tr>
              <tr><td ><b>" . get_string('pcountry', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . get_string($name->country, 'countries') . "</td></tr>
          
</table>";
    }
    echo '</div></div>';
    echo "<hr>";
    echo "<div style='text-align:justify;width:90%;margin:0 auto'>" . $name->description . "</div>";
} else {

    echo '</div>';
    echo "<hr>";

    if ($local_users) {
        echo "<div class='main' style=''>";
        echo "<div class='left_info'>
      <h4 style='background:#cae1fc;padding:0.3% 0% 0.3% 2%;color:#4692b8;clear:both;width:83%;font-size:14px'><b>" . get_string('p_details', 'local_users') . "</b></h4>
      <div class='left_info_1' >

<table cellspacing=\"5\" cellpadding = \"5\" style='margin-left:15%'>

<tr><td style='width:35%'><b>" . get_string('serviceid', 'local_users') . "</b></td>";
        if ($s3) {
            $s4 = implode(',&nbsp;', $s3);
            echo "<td>:&nbsp;&nbsp;" . $s4 . "</td>";
        }
        echo "</tr>
        <tr><td style='width:35%'><b>" . get_string('dob', 'local_admission') . "</b></td><td>:&nbsp;&nbsp;" . date("Y-m-d", $local_users->dob) . "</td></tr>
        <tr><td style='width:35%'><b>" . get_string('doj', 'local_users') . "</b></td><td>:&nbsp;&nbsp;" . date("Y-m-d", $local_users->timecreated) . "</td></tr>
        <tr><td style='width:35%'><b>" . get_string('genderheading', 'local_admission') . "</b></td><td>:&nbsp;&nbsp;" . $local_users->gender . "</td></tr>
      </table>
      </div>
      <div class='left_info_2'>
      
<table cellspacing=\"5\" cellpadding = \"5\" >
        
        <tr><td style='width:35%'><b>" . get_string('hschool', 'local_users') . "</b></td><td>:&nbsp;&nbsp;" . $local_users->primaryschoolname . "</td></tr>
        <tr><td style='width:35%'><b>" . get_string('primaryyear', 'local_admission') . "</b></td><td>:&nbsp;&nbsp;" . $local_users->primaryyear . "</td></tr>
        <tr><td style='width:35%'><b>" . get_string('score', 'local_admission') . "</b></td><td>:&nbsp;&nbsp;" . $local_users->primaryscore . "</td></tr>
        <tr><td style='width:35%'><b>" . get_string('pcountry', 'local_admission') . "</b></td><td>:&nbsp;&nbsp;" . $local_users->primaryplace . "</td></tr>
        </table> 
        </div>
</div>";

        echo "<div class='right_info'>
           <h4 style='background:#cae1fc;padding:0.3% 0% 0.3% 2%;color:#4692b8;clear:both;width:83%;font-size:14px'><b>" . strtoupper(get_string('address', 'local_users')) . "</b></h4>     
           <div class='right_info_1' >
<h5 style='margin-left:12%'><b>" . get_string('caddress', 'local_users') . ":</b></h5>     
<table cellspacing=\"5\" cellpadding = \"5\" style='margin-left:15%'>
     
     <tr><td style='width:35%'><b>" . get_string('contactname', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->fathername . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('hno', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->currenthno . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('pob', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->pob . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('city') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->region . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('state') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->state . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('pcountry', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;
	 " . get_string($name->country, 'countries') . "</td></tr>
     </table></div>";
        /*
         * ###Bugreport #180- Address details for student
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Hiding permanent address when both present and permanent address are same
         */
        if ($local_users->same == NULL) {
            echo "<div class='right_info_2'>
<h5><b>" . get_string('paddress', 'local_users') . ":</b></h5>     
<table cellspacing=\"5\" cellpadding = \"5\" >
     
     <tr><td style='width:35%'><b>" . get_string('contactname', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->contactname . "</td></tr>    
     <tr><td style='width:35%'><b>" . get_string('hno', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->permanenthno . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('pob', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->pincode . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('city') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->town . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('state') . "</b></td><td >:&nbsp;&nbsp;" . $local_users->state . "</td></tr>
     <tr><td style='width:35%'><b>" . get_string('pcountry', 'local_admission') . "</b></td><td >:&nbsp;&nbsp;" . get_string($name->country, 'countries') . "</td></tr>
     
    
     ";
        }
        if ($local_users->typeofstudent == '1') {
            
        }
        echo "</table></div>
</div></div>";
    }
}

echo $OUTPUT->footer();
