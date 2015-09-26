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
 * This is built using the bootstrapbase template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 * @package     theme_essential
 * @copyright   2013 Julian Ridden
 * @copyright   2014 Gareth J Barnard, David Bezemer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

echo $OUTPUT->doctype();

theme_essential_initialise_zoom($PAGE);
$setzoom = theme_essential_get_zoom();

require_once($OUTPUT->get_include_file('pagesettings'));
?>
<html <?php echo $OUTPUT->htmlattributes(); ?> class="no-js">
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>"/>
    <?php 
    echo $OUTPUT->get_csswww();
    echo $OUTPUT->standard_head_html();
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google web fonts -->
    <?php require_once($OUTPUT->get_include_file('fonts')); ?>
    <!-- iOS Homescreen Icons -->
    <?php require_once($OUTPUT->get_include_file('iosicons')); ?>
    <!-- Start Analytics -->
    <?php require_once($OUTPUT->get_include_file('analytics')); ?>
    <!-- End Analytics -->
    
    <!--include custom fonts-->
    <style>
        @font-face {
            font-family: "Minion Pro Regular";
            src: url('<?php echo $CFG->wwwroot; ?>/theme/essential/fonts/Minion-Pro-Regular.ttf');
        }
        @font-face {
            font-family: "SourceSansPro-Regular";
            src: url('<?php echo $CFG->wwwroot; ?>/theme/essential/fonts/SourceSansPro-Regular.ttf');
        }
        @font-face {
            font-family: "SourceSansPro-Light";
            src: url('<?php echo $CFG->wwwroot; ?>/theme/essential/fonts/SourceSansPro-Light.ttf');
        }
        @font-face {
            font-family: "SourceSansPro-Semibold";
            src: url('<?php echo $CFG->wwwroot; ?>/theme/essential/fonts/SourceSansPro-Semibold.ttf');
        }
    </style>
    
    <script type="text/javascript">
		$(document).ready(function() {
	  	var $pwidth = $(window).width();
			var $loggedin = $('body').hasClass('loggedin');
			
			/*if($pwidth >= 768){
				$('.offcanvas, #content-container').addClass('active');
			}*/	
			
		  $('[data-toggle=offcanvas]').click(function() {
		  
		  var $pwidth = $(window).width();
			$('.offcanvas').toggleClass('active');
			$('#content-container').toggleClass('active');
			
			//do this when in nmobile view
			if($pwidth <= 480 ){
				var $main = $('main').hasClass('active');
				
				if( $main == true){
					$('body, html').addClass('noscroll');
				}else{
					$('body, html').removeClass('noscroll');
				}
			}
			
			//do this when in tab/desktop view
			if($pwidth >= 481 ){
			
			var $main = $('main').hasClass('active');
			
			if( $main == false){
					$('body, html').removeClass('noscroll');
				}
			}
			
		  })
        });
    </script>
</head>
<?php $bodyclasses[] = 'zoomin'; ?>
<body <?php echo $OUTPUT->body_attributes(array_merge($bodyclasses, array('two-column', $setzoom))); ?>>

<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<header role="banner">
    <div id="page-header" class="clearfix<?php echo ($oldnavbar) ? ' oldnavbar' : ''; ?>">
        <div class="container-fluid">
            <div class="row-fluid">
                <!-- HEADER: LOGO AREA -->
                <div class="<?php echo $logoclass;
                echo (!$left) ? ' pull-right' : ' pull-left'; ?>">
                    <?php if (!$haslogo) { ?>
                        <a class="textlogo" href="<?php echo preg_replace("(https?:)", "", $CFG->wwwroot); ?>">
                            <i id="headerlogo" class="fa fa-<?php echo $OUTPUT->get_setting('siteicon'); ?>"></i>
                            <?php echo $OUTPUT->get_title('header'); ?>
                        </a>
                    <?php } else { ?>
                        <a class="logo" href="<?php echo preg_replace("(https?:)", "", $CFG->wwwroot); ?>" title="<?php print_string('home'); ?>"></a>
                    <?php }
					?>
					<nav role="navigation">
						<div class="topnavbar navbar<?php echo ($oldnavbar) ? ' oldnavbar' : ''; ?> moodle-has-zindex">
							<div class="container-fluid navbar-inner">
								<div class="row-fluid">
									<div class="pull-<?php echo ($left) ? 'right' : 'left'; ?>">
										<div class="usermenu">
											<?php echo $OUTPUT->custom_menu_user(); ?>
										</div>
										<div class="messagemenu">
											<?php echo $OUTPUT->custom_menu_messages(); ?>
										</div>
										<div class="messagemenu">
											<?php echo $OUTPUT->cobalt_applications(); ?>
										</div>
										<div class="messagemenu">
											<?php echo $OUTPUT->cobalt_new_requests(); ?>
										</div>
										<div class="gotobottommenu">
											<?php echo $OUTPUT->custom_menu_goto_bottom(); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</nav>
                </div>
			
                <?php if ($hassocialnetworks || $hasmobileapps) { ?>
                <a class="btn btn-icon" data-toggle="collapse" data-target=".icon-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <div class="icon-collapse collapse pull-<?php echo ($left) ? 'right' : 'left'; ?>">
                    <?php
                    }
                    // If true, displays the heading and available social links; displays nothing if false.
                    if ($hassocialnetworks) {
                        ?>
                        <div class="pull-<?php echo ($left) ? 'right' : 'left'; ?>" id="socialnetworks">
                            <p id="socialheading"><?php echo get_string('socialnetworks', 'theme_essential') ?></p>
                            <ul class="socials unstyled">
                                <?php
                                echo $OUTPUT->render_social_network('googleplus');
                                echo $OUTPUT->render_social_network('twitter');
                                echo $OUTPUT->render_social_network('facebook');
                                echo $OUTPUT->render_social_network('linkedin');
                                echo $OUTPUT->render_social_network('youtube');
                                echo $OUTPUT->render_social_network('flickr');
                                echo $OUTPUT->render_social_network('pinterest');
                                echo $OUTPUT->render_social_network('instagram');
                                echo $OUTPUT->render_social_network('vk');
                                echo $OUTPUT->render_social_network('skype');
                                echo $OUTPUT->render_social_network('website');
                                ?>
                            </ul>
                        </div>
                    <?php
                    }
                    // If true, displays the heading and available social links; displays nothing if false.
                    if ($hasmobileapps) { ?>
                        <div class="pull-<?php echo ($left) ? 'right' : 'left'; ?>" id="mobileapps">
                            <p id="socialheading"><?php echo get_string('mobileappsheading', 'theme_essential') ?></p>
                            <ul class="socials unstyled">
                                <?php
                                echo $OUTPUT->render_social_network('ios');
                                echo $OUTPUT->render_social_network('android');
                                echo $OUTPUT->render_social_network('winphone');
                                echo $OUTPUT->render_social_network('windows');
                                ?>
                            </ul>
                        </div>
                    <?php
                    }
                    if ($hassocialnetworks || $hasmobileapps) {
                    ?>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>
    <nav role="navigation">
        <div id='essentialnavbar' class="navbar<?php echo ($oldnavbar) ? ' oldnavbar' : ''; ?> moodle-has-zindex">
            <div class="container-fluid navbar-inner">
                <div class="row-fluid">
                    <div class="custommenus pull-<?php echo ($left) ? 'left' : 'right'; ?>">
                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>
                        <?php echo $OUTPUT->get_title('navbar'); ?>
                    <!--<div class="pull-<?php echo ($left) ? 'right' : 'left'; ?>">-->
                    <!--    <div class="usermenu">-->
                    <!--        <?php echo $OUTPUT->custom_menu_user(); ?>-->
                    <!--    </div>-->
                    <!--    <div class="messagemenu">-->
                    <!--        <?php echo $OUTPUT->custom_menu_messages(); ?>-->
                    <!--    </div>-->
                    <!--    <div class="messagemenu">-->
                    <!--        <?php echo $OUTPUT->cobalt_applications(); ?>-->
                    <!--    </div>-->
                    <!--    <div class="messagemenu">-->
                    <!--        <?php echo $OUTPUT->cobalt_new_requests(); ?>-->
                    <!--    </div>-->
                    <!--    <div class="gotobottommenu">-->
                    <!--        <?php echo $OUTPUT->custom_menu_goto_bottom(); ?>-->
                    <!--    </div>-->
                    <!--</div>-->
                        <div class="nav-collapse collapse pull-<?php echo ($left) ? 'left' : 'right'; ?>">
                            <div id="custom_menu_language">
                                <?php echo $OUTPUT->custom_menu_language(); ?>
                            </div>
                            <div id="custom_menu_courses">
                                <?php echo $OUTPUT->custom_menu_courses(); ?>
                            </div>
                            <?php if ($colourswitcher) { ?>
                                <div id="custom_menu_themecolours">
                                    <?php echo $OUTPUT->custom_menu_themecolours(); ?>
                                </div>
                            <?php } ?>
                            <div id="custom_menu">
                                <?php echo $OUTPUT->custom_menu(); ?>
                            </div>
                            <div id="custom_menu_activitystream">
                                <?php echo $OUTPUT->custom_menu_activitystream(); ?>
                            </div>
                            <ul class="nav pull-left">
                                <?php
                                if (empty($PAGE->layout_options['langmenu']) || $PAGE->layout_options['langmenu']) {
                                    echo $OUTPUT->lang_menu();
                                }
                                if(isloggedin()){
                                ?>
                                <li class="hbl"><a href="#" class="moodlezoom"><i class="fa fa-outdent fa-lg"></i> <span class="zoomdesc"><?php /*echo get_string('hideblocks', 'theme_essential')*/ ?></span></a></li>
                                <li class="sbl"><a href="#" class="moodlezoom"><i class="fa fa-indent fa-lg"></i> <span class="zoomdesc"><?php /*echo get_string('showblocks', 'theme_essential')*/ ?></span></a></li>
                                 <?php
                                }
                                ?>
                            </ul>
							
                    <!--******************code added by anil-started here*******************-->
							<div id="custom_menu_topmenu">
                                <?php
                                global $USER, $CFG;
                                 require_once($CFG->dirroot .'/blocks/ranking/lib.php');
                                //function is_instructor($userid) {
                                //  return user_has_role_assignment($userid, 10);
                                //}
                                 $icon = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/navigationitem.png'));
                                 //**********************for siteadmin login************************//
                                if(is_siteadmin($USER->id)) {
                                echo html_writer:: start_tag('ul',array('class'=>'nav'));
                                
                                 echo html_writer:: start_tag('li',array('class'=>'dropdown'));
                                    echo html_writer:: tag('a',get_string('grades','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'navbarlinks','data-toggle'=>'dropdown'));
                                        echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu pull-right'));
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('gradeletter','theme_essential'),array('href'=>$CFG->wwwroot.'/local/gradeletter/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('examtype','theme_essential'),array('href'=>$CFG->wwwroot.'/local/examtype/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('modeofexam','theme_essential'),array('href'=>$CFG->wwwroot.'/local/lecturetype/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                        echo html_writer:: end_tag('ul');
                                    echo html_writer:: end_tag('li');
                                    
                                    echo html_writer:: start_tag('li',array('class'=>'dropdown'));
                                    echo html_writer:: tag('a',get_string('departments','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'navbarlinks','data-toggle'=>'dropdown'));
                                        echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu pull-right'));
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('addeditdepartments','theme_essential'),array('href'=>$CFG->wwwroot.'/local/departments/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('courses','theme_essential'),array('href'=>$CFG->wwwroot.'/local/cobaltcourses/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('instructor','theme_essential'),array('href'=>$CFG->wwwroot.'/local/departments/display_instructor.php','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                        echo html_writer:: end_tag('ul');
                                    echo html_writer:: end_tag('li');
                                    
                                    echo html_writer:: start_tag('li',array('class'=>'dropdown'));
                                    echo html_writer:: tag('a',get_string('programs','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'navbarlinks','data-toggle'=>'dropdown'));
                                        echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu pull-right'));
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('addeditprograms','theme_essential'),array('href'=>$CFG->wwwroot.'/local/programs/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array('class'=>'dropdown-submenu preferences'));
                                            echo html_writer:: tag('a',$icon.get_string('curriculums','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'dropdowmlinks dropdown-toggle','data-toggle'=>'dropdown'));
                                                echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu subulright'));
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addeditcurriculums','theme_essential'),array('href'=>$CFG->wwwroot.'/local/curriculum/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('manageplans','theme_essential'),array('href'=>$CFG->wwwroot.'/local/curriculum/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                echo html_writer:: end_tag('ul');
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array('class'=>'dropdown-submenu preferences'));
                                            echo html_writer:: tag('a',$icon.get_string('modules','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'dropdowmlinks dropdown-toggle','data-toggle'=>'dropdown'));
                                                echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu subulright'));
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addeditmodules','theme_essential'),array('href'=>$CFG->wwwroot.'/local/curriculum/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                echo html_writer:: end_tag('ul');
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array('class'=>'dropdown-submenu preferences'));
                                            echo html_writer:: tag('a',$icon.get_string('admissions','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'dropdowmlinks dropdown-toggle','data-toggle'=>'dropdown'));
                                                echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu subulright'));
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('approveapplicants','theme_essential'),array('href'=>$CFG->wwwroot.'/local/admission/viewapplicant.php','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('enrollstudent','theme_essential'),array('href'=>$CFG->wwwroot.'/local/admission/uploaduser.php','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addnewapplicants','theme_essential'),array('href'=>$CFG->wwwroot.'/local/admission/uploadapplicant.php','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                echo html_writer:: end_tag('ul');
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: end_tag('li');
                                        echo html_writer:: end_tag('ul');
                                    echo html_writer:: end_tag('li');
                                    
                                    echo html_writer:: start_tag('li',array('class'=>'dropdown'));
                                    echo html_writer:: tag('a',get_string('semesters','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'navbarlinks','data-toggle'=>'dropdown'));
                                        echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu pull-right sem_dropdowm'));
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('addeditsemisters','theme_essential'),array('href'=>$CFG->wwwroot.'/local/semesters/','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array('class'=>'dropdown-submenu preferences'));
                                            echo html_writer:: tag('a',$icon.get_string('academiccalender','theme_essential'),array('href'=>$CFG->wwwroot.'/local/examtype/','class'=>'dropdowmlinks dropdown-toggle','data-toggle'=>'dropdown'));
                                                echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu subulrightacademic_cal'));
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addeditacademiccalender','theme_essential'),array('href'=>$CFG->wwwroot.'/local/curriculum/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('registrationevent','theme_essential'),array('href'=>$CFG->wwwroot.'/local/curriculum/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('adddropevent','theme_essential'),array('href'=>$CFG->wwwroot.'/local/curriculum/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                echo html_writer:: end_tag('ul');
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array('class'=>'dropdown-submenu preferences'));
                                            echo html_writer:: tag('a',$icon.get_string('Timetable','theme_essential'),array('href'=>$CFG->wwwroot.'/local/lecturetype/','class'=>'dropdowmlinks dropdown-toggle','data-toggle'=>'dropdown'));
                                                 echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu subulright'));
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addedittimeintervals','theme_essential'),array('href'=>$CFG->wwwroot.'/local/timetable/','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addeditclasstypes','theme_essential'),array('href'=>$CFG->wwwroot.'/local/timetable/classtype.php','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('addeditscheduleclass','theme_essential'),array('href'=>$CFG->wwwroot.'/local/timetable/scheduleclassview.php','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('calendarview','theme_essential'),array('href'=>$CFG->wwwroot.'/local/timetable/calendarview.php','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                echo html_writer:: end_tag('ul');
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array('class'=>'dropdown-submenu preferences'));
                                            echo html_writer:: tag('a',$icon.get_string('courseregistration','theme_essential'),array('href'=>$CFG->wwwroot.'/local/lecturetype/','class'=>'dropdowmlinks dropdown-toggle','data-toggle'=>'dropdown'));
                                                echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu subulrightacademic_cal'));
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('approveenrolledcourses','theme_essential'),array('href'=>$CFG->wwwroot.'/local/courseregistration/registrar.php?current=pending','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                    echo html_writer:: start_tag('li',array());
                                                    echo html_writer:: tag('a',$icon.get_string('approveadddropcourses','theme_essential'),array('href'=>$CFG->wwwroot.'/local/adddrop/registrar.php?current=pending','class'=>'dropdowmlinks'));
                                                    echo html_writer:: end_tag('li');
                                                echo html_writer:: end_tag('ul');
                                            echo html_writer:: end_tag('li');
                                        echo html_writer:: end_tag('ul');
                                    echo html_writer:: end_tag('li');
                                    echo html_writer:: start_tag('li',array('class'=>'dropdown'));
                                    echo html_writer:: tag('a',get_string('managebatches','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'navbarlinks','data-toggle'=>'dropdown'));
                                        echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu pull-right'));
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('addeditbatches','theme_essential'),array('href'=>$CFG->wwwroot.'/local/batches/index.php','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('uploadnewstudentstobatches','theme_essential'),array('href'=>$CFG->wwwroot.'/local/batches/bulk_enroll.php?mode=new','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('uploadexiststudentstobatches','theme_essential'),array('href'=>$CFG->wwwroot.'/local/batches/bulk_enroll.php?mode=exists','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                        echo html_writer:: end_tag('ul');
                                    echo html_writer:: end_tag('li');
                                    
                                echo html_writer:: end_tag('ul');
                                }
                                elseif(is_student($USER->id)){   /***********for student login***************/
                                        echo html_writer:: start_tag('ul',array('class'=>'nav'));
                                        echo html_writer:: start_tag('li',array());
                                        echo html_writer:: tag('a',get_string('mycurriculum','theme_essential'),array('href'=>$CFG->wwwroot.'/local/courseregistration/mycurplans.php','class'=>'navbarlinks'));
                                        echo html_writer:: end_tag('li');
                                        echo html_writer:: start_tag('li',array());
                                        echo html_writer:: tag('a',get_string('currentclasses','theme_essential'),array('href'=>$CFG->wwwroot.'/local/courseregistration/myclasses.php','class'=>'navbarlinks'));
                                        echo html_writer:: end_tag('li');
                                        echo html_writer:: start_tag('li',array());
                                        echo html_writer:: tag('a',get_string('timetable','theme_essential'),array('href'=>$CFG->wwwroot.'/local/timetable/calendarview.php','class'=>'navbarlinks'));
                                        echo html_writer:: end_tag('li');
                                        echo html_writer:: start_tag('li',array());
                                        echo html_writer:: tag('a',get_string('scheduledexams','theme_essential'),array('href'=>$CFG->wwwroot.'/local/scheduleexam/','class'=>'navbarlinks'));
                                        echo html_writer:: end_tag('li');
                                        echo html_writer:: start_tag('li',array());
                                        echo html_writer:: tag('a',get_string('transcript','theme_essential'),array('href'=>$CFG->wwwroot.'/local/myacademics/transcript.php','class'=>'navbarlinks'));
                                        echo html_writer:: end_tag('li');
                                        echo html_writer:: start_tag('li',array('class'=>'dropdown'));
                                        echo html_writer:: tag('a',get_string('myrequests','theme_essential'),array('href'=>$CFG->wwwroot.'/#','class'=>'dropdown-toggle navbarlinks','data-toggle'=>'dropdown'));
                                       
                                            echo html_writer:: start_tag('ul',array('class'=>'dropdown-menu pull-right'));
                                        
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('idcard','theme_essential'),array('href'=>$CFG->wwwroot.'/local/request/request_id.php','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('profilechangerequest','theme_essential'),array('href'=>$CFG->wwwroot.'/local/request/request_profile.php','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('transcript','theme_essential'),array('href'=>$CFG->wwwroot.'/local/request/request_transcript.php','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            echo html_writer:: start_tag('li',array());
                                            echo html_writer:: tag('a',$icon.get_string('courseexemption','theme_essential'),array('href'=>$CFG->wwwroot.'/local/request/course_exem.php','class'=>'dropdowmlinks'));
                                            echo html_writer:: end_tag('li');
                                            
                                            echo html_writer:: end_tag('ul');
                                           
                                        echo html_writer:: end_tag('li');
                                        echo html_writer:: end_tag('ul');
                                }
                                ?>
                    <!--******************code added by anil-ended here*******************-->
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
