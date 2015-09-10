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
 * This is built using the Clean template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 *
 * @package   theme_colms
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once $CFG->dirroot . '/local/request/lib/lib.php';

function colms_process_css($css, $theme) {

    // Set the theme color.
    if (!empty($theme->settings->themecolor)) {
        $themecolor = $theme->settings->themecolor;
    } else {
        $themecolor = null;
    }
    $css = colms_set_themecolor($css, $themecolor);

    // Set the theme hover color.
    if (!empty($theme->settings->themehovercolor)) {
        $themehovercolor = $theme->settings->themehovercolor;
    } else {
        $themehovercolor = null;
    }
    $css = colms_set_themehovercolor($css, $themehovercolor);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = colms_set_customcss($css, $customcss);

    // Set the background image for the logo.
//if(isloggedin()){
//    $alogo = $theme->setting_file_url('allogo', 'allogo');
//    $css = co_set_logo($css, $alogo);
//} else{  
//   $blogo = $theme->setting_file_url('bllogo', 'bllogo');
//    $css = co_set_logo($css, $blogo);
//}
    // Set Slide Images.
    $setting = 'slide1image';
    // Creates the url for image file which is then served up by 'theme_co_pluginfile' below.
    $slideimage = $theme->setting_file_url($setting, $setting);
    $css = colms_set_slideimage($css, $slideimage, $setting);

    $setting = 'slide2image';
    $slideimage = $theme->setting_file_url($setting, $setting);
    $css = colms_set_slideimage($css, $slideimage, $setting);

    $setting = 'slide3image';
    $slideimage = $theme->setting_file_url($setting, $setting);
    $css = colms_set_slideimage($css, $slideimage, $setting);

    $setting = 'slide4image';
    $slideimage = $theme->setting_file_url($setting, $setting);
    $css = colms_set_slideimage($css, $slideimage, $setting);

    return $css;
}

function colms_set_logo($css, $logo) {
    global $OUTPUT;
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_colms_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'bllogo') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('bllogo', $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'allogo') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('allogo', $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'slide1image') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('slide1image', $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'slide2image') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('slide2image', $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'slide3image') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('slide3image', $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'slide4image') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('slide4image', $args, $forcedownload, $options);
    } if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing1content') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('marketing1content', $args, $forcedownload, $options);
    }if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing2content') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('marketing2content', $args, $forcedownload, $options);
    }if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing3content') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('marketing3content', $args, $forcedownload, $options);
    }if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing4content') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('marketing4content', $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'video') {
        $theme = theme_config::load('colms');
        return $theme->setting_file_serve('video', $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

function colms_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

function colms_set_themecolor($css, $themecolor) {
    $tag = '[[setting:themecolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#30add1';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function colms_set_themehovercolor($css, $themehovercolor) {
    $tag = '[[setting:themehovercolor]]';
    $replacement = $themehovercolor;
    if (is_null($replacement)) {
        $replacement = '#29a1c4';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function colms_set_slideimage($css, $slideimage, $setting) {
    global $OUTPUT;
    $tag = '[[setting:' . $setting . ']]';
    $replacement = $slideimage;
    if (is_null($replacement)) {
        // Get default image from themes 'images' folder of the name in $setting.
        $replacement = $OUTPUT->pix_url('images/' . $setting, 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_colms_page_init(moodle_page $page) {
    $page->requires->jquery();
    $page->requires->jquery_plugin('ui', 'core');
    $page->requires->jquery_plugin('modernizr', 'theme_colms');
    $page->requires->jquery_plugin('cslider', 'theme_colms');
    $page->requires->jquery_plugin('custom', 'theme_colms');
}

function colms_theme_login_form() {
    global $CFG;
    if (empty($CFG->loginhttps)) {
        $wwwroot = $CFG->wwwroot;
    } else {
        // This actually is not so secure ;-), 'cause we're
        // in unencrypted connection...
        $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
    }

    //  if (!empty($CFG->registerauth)) {
    //   $authplugin = get_auth_plugin($CFG->registerauth);
    //    if ($authplugin->can_signup()) {
    //$signup = $wwwroot . '/login/signup.php';
    //   }
    //}
    // TODO: now that we have multiauth it is hard to find out if there is a way to change password
    $forgot = $wwwroot . '/login/forgot_password.php';

    if (!empty($CFG->loginpasswordautocomplete)) {
        $autocomplete = 'autocomplete="off"';
    } else {
        $autocomplete = '';
    }

    $username = get_moodle_cookie();
    $content = '<div id="front-login"><form class="loginform" id="login" method="post" action="' . get_login_url() . '" ' . $autocomplete . ' >';

    //  $content .= '<label for="login_username">'.get_string('username').'</label>';
    $content .= '<div class="c1 fld username"><input type="text" name="username" id="login_username" placeholder="Username" value="' . s($username) . '" /></div>';

    //    $content .= '<label for="login_password">'.get_string('password').'</label>';

    $content .= '<div class="c1 fld password"><input type="password" name="password" id="login_password" placeholder="Password" value="" ' . $autocomplete . ' /></div>';

    if (isset($CFG->rememberusername) and $CFG->rememberusername == 2) {
        $checked = $username ? 'checked="checked"' : '';
        $content .= '<div class="c1 rememberusername"><input type="checkbox" name="rememberusername" id="rememberusername" value="1" />';
        $content .= ' <label for="rememberusername">' . get_string('rememberusername', 'admin') . '</label></div>';
    }

    $content .= '<div class="c1 btn"><input type="submit" value="' . get_string('login') . '" id="loginsubmit" /></div>';

    $content .= "</form>";
    if (!empty($forgot)) {
        $content .= '<div><a href="' . $forgot . '">' . get_string('forgotaccount') . '</a></div>';
    }
    $content .='</div>';
    return $content;
}

function colms_get_latest_events() {
    global $CFG, $DB;
    $eventslist = $DB->get_records_sql('select * from {local_event_activities} where eventlevel=1 and startdate >' . time() . ' LIMIT 0,7');
    //$events = '<ul>';
    $events = '<table>';
    foreach ($eventslist as $eventlist) {
        $events .= '<tr><td><span id="eventtitl"><a href=' . $CFG->wwwroot . '/local/academiccalendar/viewevent.php?id=' . $eventlist->id . '> ' . $eventlist->eventtitle . '</a></span></td><td><span id="eventdate">' . date('M d', $eventlist->startdate) . '</span></td></tr>';
    }
    $events .='</table>';
    return $events;
}

function render_layout_buttons() {
    global $OUTPUT, $PAGE, $CFG;
    $url = $PAGE->url;
    $filterids = array('0', '1', '2');
    $filters = '<div id="layoutoptions">';
    //$filters =  '<ul style="list-style:none;display:block;">';
    foreach ($filterids as $filterid) {
        $filterurl = new moodle_url($url, array('dashboard' => $filterid));
        $filters .= html_writer::link($filterurl, html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/theme/colms/pix/layout_' . $filterid . '.png', 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'dashboard_iconsmall')));
    }
    $filters .='</div>';
    return $filters;
}

function render_cocustommenu() {
    global $CFG, $USER, $DB, $OUTPUT, $PAGE;
    $systemcontext = context_system::instance();
    $usercontext =context_user::instance($USER->id);
    $content = '<ul class="nav">';
    if (has_capability('local/cobaltsettings:manage', $systemcontext)) {
        $content .="<li class='dropdown'>
                                  <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>Organization Settings<b class='caret'></b></a>
                                  <ul class='dropdown-menu'>
                                   <li><a href=$CFG->wwwroot/local/cobaltsettings/category_level.php>CobaltLMS Entity Settings</a></li>
                                   <li><a href=$CFG->wwwroot/local/cobaltsettings/school_settings.php>Organization Settings</a></li>
                                   <li><a href=$CFG->wwwroot/local/cobaltsettings/gpa_settings.php>GPA/CGPA Settings</a></li>
                                   <li><a href=$CFG->wwwroot/local/prefix>Prefix and Suffix</a></li>
                                   </ul></li>";
    }
    if (has_capability('local/collegestructure:manage', $systemcontext)) {
        $content .="<li class='dropdown'>
                                  <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>Hierarchy<b class='caret'></b></a>
                                  <ul class='dropdown-menu'>";
    }
    if (has_capability('local/collegestructure:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/collegestructure>Organizations</a></li>";
    }
    if (has_capability('local/departments:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/departments>Course Libraries</a></li>";
    }
    if (has_capability('local/programs:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/programs>Programs</a></li>";
    }
    if (has_capability('local/semesters:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/semesters>Course Offerings</a></li>";
    }
    if (has_capability('local/curriculum:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/curriculum>Curriculums</a></li>";
    }
    if (has_capability('local/modules:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/modules>Modules</a></li>";
    }
    if (has_capability('local/cobaltcourses:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/cobaltcourses>Courses</a></li>";
    }
    if (has_capability('local/classes:manage', $systemcontext)) {
        $content .="<li><a href=$CFG->wwwroot/local/classes>Classes Management</a></li>";
    }
    if (has_capability('local/collegestructure:manage', $systemcontext)) {
        $content .="</ul></li>";
    }
    if (has_capability('local/gradeletter:manage', $systemcontext)) {
        $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>Assesments<b class='caret'></b></a>
                            <ul class='dropdown-menu'>";
        if (has_capability('local/gradeletter:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/gradeletter>Grade letters</a></li>";
        }
        if (has_capability('local/examtype:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/examtype>Assesment type</a></li>";
        }
        if (has_capability('local/lecturetype:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/lecturetype>Lecture type</a></li>";
        }
        if (has_capability('local/gradesubmission:manage', $systemcontext) && has_capability('local/lecturetype:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/gradesubmission>Grade Submission</a></li>";
        }
        if (has_capability('local/scheduleexam:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/scheduleexam>Assesments</a></li>";
        }
        $content .="</ul></li>";
        $content .="<li><a href=$CFG->wwwroot/local/helpmanuals/registrar/index.html  target='_blank'>Help Manual</a></li>";
    }
    /*   if(has_capability('local/classroomresources:manage', $systemcontext)){
      $content .="<li class='dropdown'>
      <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>Resourse Management<b class='caret'></b></a>
      <ul class='dropdown-menu'>";
      $content .="<li><a href=$CFG->wwwroot/local/classroomresources/index.php>Manage Buildings</a></li>";
      $content .="<li><a href=$CFG->wwwroot/local/classroomresources/viewfloor.php>Manage Floors</a></li>";
      $content .="<li><a href=$CFG->wwwroot/local/classroomresources/viewclassroom.php>Manage Classrooms</a></li>";
      $content .="<li><a href=$CFG->wwwroot/local/classroomresources/viewresource.php>Manage Resources</a></li>";
      $content .="<li><a href=$CFG->wwwroot/local/classroomresources/view.php>Assign Resources</a></li>";
      $content .="</ul></li>";
      } */

    //   $content .='</ul>';
    //for instructor starts-------------------------------------------------------
    if (has_capability('local/classes:submitgrades', $systemcontext)) {
        if (!is_siteadmin($USER->id)) {
            $content .="<li><a href=$CFG->wwwroot/local/academiccalendar>Events Calendar</a></li>";

            $content .="<li><a href=$CFG->wwwroot/local/classroomresources/timetable.php>Class Schedule</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/scheduleexam>Assesments Schedule</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/helpmanuals/instructor/index.html target='_blank'>Help Manual</a></li>";
        }
    }

    //for instructor ends-------------------------------------------------------
    //For students starts-----------------------------------------------------
    if (isloggedin()) {
        $context = context_user::instance($USER->id);
        if (has_capability('local/classes:enrollclass', $context) && !is_siteadmin()) {
            //----------------------starts of my academics------------------------------
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>My Learning<b class='caret'></b></a>
                            <ul class='dropdown-menu'>";
            $content .="<li><a href=$CFG->wwwroot/local/courseregistration/mycur.php>Curriculum</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/courseregistration/myclasses.php>Current Classes</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/scheduleexam>Scheduled Assesments</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/myacademics/transcript.php>Transcript</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/academiccalendar>Events Calender</a></li>";
            $content .="</ul></li>";
            //--------------------------------end of my academics--------------------------------------------------------------------------------
            //--------------------------------course registration--------------------------------------------------------------------------------
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>Course Registration<b class='caret'></b></a>
                            <ul class='dropdown-menu'>";
            $content .="<li><a href=$CFG->wwwroot/local/courseregistration/index.php>Register to Course/Class</a></li>";
            $content .="</ul></li>";
            //--------------------end of course registration----------------------------------------------------------------------------------------------
            //--------------------Requests links----------------------------------------------------------------------------------------------------------
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown'>My Requests<b class='caret'></b></a>
                            <ul class='dropdown-menu'>";
            $content .="<li><a href=$CFG->wwwroot/local/request/request_id.php>ID Card</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/request_profile.php>Profile Change</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/request_transcript.php>Transcript</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/course_exem.php>Course Exemption</a></li>";
            $content .="</ul></li>";
            //-----------------------------end of requests links-------------------------------------------------------------------------------------------
            $content .="<li><a href=$CFG->wwwroot/local/helpmanuals/student/index.html target='_blank'>Help Manual</a></li>";
        }
    }
    // for mentor starts-------------------------------------------
    if (has_capability('local/classes:approvemystudentclasses', $systemcontext) && !is_siteadmin()) {
        $content .="<li><a href=$CFG->wwwroot/local/mentor>My Students</a></li>";
        $content .="<li><a href=$CFG->wwwroot/local/helpmanuals/mentor/index.html target='_blank'>Help Manual</a></li>";
    }
    // mentor ends
    if (isloggedin() & !isguestuser()) {

        $msgnotification = message_count_unread_messages($USER);
        $heirarchy = new hierarchy();
        $request = new requests();
        $applicationnotice = $heirarchy->count_admissions_from_applicants($USER->id);
        $requestnotice = $request->all_student_requests_count($USER->id);
        $courserequestnotice = $heirarchy->count_course_requests_from_students($USER->id);
        $transcriptnotice = $heirarchy->count_transcript_req_from_student($USER->id);
        $courseexemptionnotice = $heirarchy->count_coureexe_req_from_student($USER->id);
        $profilechangenotice = $heirarchy->count_profilechange_req_from_student($USER->id);
        $idcardnotice = $heirarchy->count_idcard_req_from_student($USER->id);
        $newappnotice = $heirarchy->count_new_admission_req_from_student($USER->id);
        $transferappnotice = $heirarchy->count_transfer_admission_req_from_student($USER->id);
        $totalrequest = $courserequestnotice + $requestnotice;

        $content .="<li><a href=$CFG->wwwroot/message/ title='Messages' id='messages'>
         <sup  id='msgnotice'>$msgnotification</sup></a></li>";
        $context = context_user::instance($USER->id);
        if (has_capability('local/classes:enrollclass', $context) && !is_siteadmin()) {
            $allapprovals = $request->all_approved_requests($USER->id);
            $transcriptapprovals = $heirarchy->count_trasncripts_approve_from_registrar($USER->id);
            $courseexemptionapproval = $heirarchy->count_courseexe_approve_from_registrar($USER->id);
            $idcardapproval = $heirarchy->count_idcard_approve_from_registrar($USER->id);
            $profilechangeapproval = $heirarchy->count_profilechange_approve_from_registrar($USER->id);
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle open'  data-toggle='dropdown' id='allrequests' title='Request Approvals'>
                            <sup id='arequests'>$allapprovals</sup></a>
                            <ul class='dropdown-menu'>";
            $content .="<li><a href=$CFG->wwwroot/local/request/request_transcript.php>Transcripts($transcriptapprovals)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/course_exem.php>Coures Exemptions($courseexemptionapproval)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/request_id.php>ID Card($idcardapproval)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/request_profile.php>Profile Change($profilechangeapproval)</a></li>";
            $content .="</ul></li>";
        }
        if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin($USER->id)) {
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown' id='apprequests' title='Applicant Requests'>
                            <sup id='requests'>$applicationnotice</sup></a>
                            <ul class='dropdown-menu'>";
            $content .="<li><a href=$CFG->wwwroot/local/admission/viewapplicant.php>New Application($newappnotice)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/admission/transferapplicant.php>Transfer Application($transferappnotice)</a></li>";
            $content .="</ul></li>";
        }
        if ((has_capability('local/collegestructure:manage', $systemcontext) || has_capability('local/classes:approvemystudentclasses', $systemcontext)) && !is_siteadmin($USER->id)) {
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown' id='allrequests' title='Requests'>
                            
                            <sup id='arequests'>$totalrequest</sup></a>
                            <ul class='dropdown-menu'>";
        }
        if ((has_capability('local/collegestructure:manage', $systemcontext) || has_capability('local/classes:approvemystudentclasses', $systemcontext)) && !is_siteadmin($USER->id)) {
            $content .="<li><a href=$CFG->wwwroot/local/courseregistration/registrar.php?current=pending>Approve Course (<b class='counts'>$courserequestnotice </b>)</a></li>";
        }
        if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin($USER->id)) {
            $content .="<li><a href=$CFG->wwwroot/local/request/approval_transcript.php>Approve Transcripts (<b class='counts'>$transcriptnotice</b>)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/approveexem.php>Approve Coures Exemptions (<b class='counts'>$courseexemptionnotice</b>)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/approval_id.php>Approve ID Card (<b class='counts'>$idcardnotice</b>)</a></li>";
            $content .="<li><a href=$CFG->wwwroot/local/request/approval_profile.php>Approve Profile Change (<b class='counts'>$profilechangenotice</b>)</a></li>";
        }
        if ((has_capability('local/collegestructure:manage', $systemcontext) || has_capability('local/classes:approvemystudentclasses', $systemcontext)) && !is_siteadmin($USER->id)) {
            $content .="</ul></li>";
        }
        if (has_capability('local/academiccalendar:manage', $systemcontext)) {
            $content .="<li class='dropdown'>
                            <a href='#' class='dropdown-toggle'  data-toggle='dropdown' id='quicklinks'><img src= $CFG->wwwroot/theme/colms/pix/quicklinks.png class='dropdown-toggle'  data-toggle='dropdown'/><b class='caret'></b></a>
                            <ul class='dropdown-menu'>";
        }
        if (has_capability('local/academiccalendar:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/academiccalendar>Events Calendar</a></li>";
        }
        if (has_capability('local/admission:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/admission/viewapplicant.php>Online Registrations</a></li>";
        }
        if (has_capability('local/courseregistration:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/courseregistration/registrar.php?current=pending>Course Enrollments</a></li>";
        }
        if (has_capability('local/courseregistration:manage', $systemcontext)) {
            $content .="<li><a href=$CFG->wwwroot/local/evaluations/index.php>Evaluations</a></li>";
        }
        if (has_capability('local/academiccalendar:manage', $systemcontext)) {
            $content .="</ul></li>";
        }
    }

    $content .="</ul>";

    //For students ends------------------------------------------------------
    return $content;
}
