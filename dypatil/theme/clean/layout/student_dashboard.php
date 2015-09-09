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
$dashboard_param = optional_param('dashboard',-1,PARAM_INT);
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hasheader = (empty($PAGE->layout_options['noheader']));

$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));

//$hashiddendock = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('hidden-dock', $OUTPUT));
$hasfooterfirst = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-first', $OUTPUT));
$hasfootersecond = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-second', $OUTPUT));
$hasfootermiddle = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-middle', $OUTPUT));
$hasfooterthird = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-third', $OUTPUT));
$hasfooterfourth = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-fourth', $OUTPUT));


$hasdashboardone = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('dashboard-one', $OUTPUT));
$hasdashboardtwo = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('dashboard-two', $OUTPUT));

$hasdashboardthree = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('dashboard-three', $OUTPUT));
$hasdashboardfour = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('dashboard-four', $OUTPUT));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

//$showhiddendock = ($hashiddendock && !$PAGE->blocks->region_completely_docked('hidden-dock', $OUTPUT));
$showfooterfirst = ($hasfooterfirst && !$PAGE->blocks->region_completely_docked('footer-first', $OUTPUT));
$showfootersecond = ($hasfootersecond && !$PAGE->blocks->region_completely_docked('footer-second', $OUTPUT));
$showfootermiddle = ($hasfootermiddle && !$PAGE->blocks->region_completely_docked('footer-middle', $OUTPUT));
$showfooterthird = ($hasfooterthird && !$PAGE->blocks->region_completely_docked('footer-third', $OUTPUT));
$showfooterfourth = ($hasfooterfourth && !$PAGE->blocks->region_completely_docked('footer-fourth', $OUTPUT));


$showdashboardone = ($hasdashboardone && !$PAGE->blocks->region_completely_docked('dashboard-one', $OUTPUT));
$showdashboardtwo = ($hasdashboardtwo && !$PAGE->blocks->region_completely_docked('dashboard-two', $OUTPUT));
$showdashboardthree = ($hasdashboardthree && !$PAGE->blocks->region_completely_docked('dashboard-three', $OUTPUT));
$showdashboarfour = ($hasdashboardfour && !$PAGE->blocks->region_completely_docked('dashboard-four', $OUTPUT));


// If there can be a sidepost region on this page and we are editing, always
 //show it so blocks can be dragged into it.
if ($PAGE->user_is_editing()) {
    if ($PAGE->blocks->is_known_region('side-pre')) {
        $showsidepre = true;
    }
    if ($PAGE->blocks->is_known_region('side-post')) {
        $showsidepost = false;
    }
}

//$haslogo = (!empty($PAGE->theme->settings->logo));

$hasfootnote = (!empty($PAGE->theme->settings->footnote));
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';

if (empty($PAGE->layout_options['nocourseheaderfooter'])) {
    $courseheader = $OUTPUT->course_header();
    $coursecontentheader = $OUTPUT->course_content_header();
    if (empty($PAGE->layout_options['nocoursefooter'])) {
        $coursecontentfooter = $OUTPUT->course_content_footer();
        $coursefooter = $OUTPUT->course_footer();
    }
}

$layout = 'pre-and-post';
if ($showsidepre && !$showsidepost) {
    if (!right_to_left()) {
        $layout = 'side-pre-only';
    } else {
        $layout = 'side-post-only';
    }
} else if ($showsidepost && !$showsidepre) {
    if (!right_to_left()) {
        $layout = 'side-post-only';
    } else {
        $layout = 'side-pre-only';
    }
} else if (!$showsidepost && !$showsidepre) {
    $layout = 'content-only';
}
$bodyclasses[] = $layout;

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <meta name="author" content="Site by Klevar" /> 
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <?php if ($PAGE->user_is_editing()) { ?>
   <style type="text/css">
    #dashboard-one, #dashboard-two, #dashboard-three, #dashboard-four{
        border:1px dashed #CCC !important;
    }
   </style>
   <?php } ?>
    <!--<link href='<?php /*echo $CFG->wwwroot */?>/theme/colms/style/student_style.css' rel='stylesheet' type='text/css'>-->
    <script src="http://api.html5media.info/1.1.6/html5media.min.js"></script>

</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php if ($hasheader) {
    
    include('header.php');
}
?>

<header role="banner" class="navbar">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
            <!--<a class="brand" href="<?php //echo $CFG->wwwroot;?>"><?php //echo $SITE->shortname; ?></a>-->
            <a class="btn btn-navbar" data-toggle="workaround-collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="nav-collapse collapse">
            <?php if ($hascustommenu) {
               if(isloggedin()){
               echo render_cocustommenu();
               } else {
               // echo $custommenu;
               }
            } ?>
            <ul class="nav pull-right">
            <li><?php //echo $PAGE->headingmenu ?></li>
            <li class="navbar-text"><?php //echo $OUTPUT->login_info() ?></li>
            </ul>
            </div>
        </div>
    </nav>
    
</header>

<div id="page" class="container-fluid">
    <div id="page-content" class="row-fluid">
<!-- Start Marketing Spots -->


<!-- <div class="bor"></div>-->
<aside class="span3">
     <div id="region-post" class="block-region">
                   
<?php if ($hasnavbar) { ?>
            <div class="region-content"><nav class="breadcrumb-button"><?php echo $PAGE->button; ?></nav></div>
                 
                                <?php } ?>
            <div class="region-content">
                <?php
                   echo $OUTPUT->blocks_for_region('side-post');
                ?>
            </div>
        </div>
    </aside>
        <section id="region-main" class="span9 desktop-first-column">
<?php
if(is_siteadmin()){
    ?>
    <div class="row-fluid" id="middle-blocks">
    <!--<div id="title"><h1>Dashboard</h1></div>-->
        <div class="span4" id="fulldashboard">
        <!-- Advert #1 -->
    		<div id="dashboard-one" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardone) {
            		echo $OUTPUT->blocks_for_region('dashboard-one');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-three" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardthree) {
            		echo $OUTPUT->blocks_for_region('dashboard-three');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-four" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardfour) {
            		echo $OUTPUT->blocks_for_region('dashboard-four');
        		} ?>
        		</div>
        	</div>
    </div>

    <?php
}
if(isloggedin() && !is_siteadmin()){
       global $DB,$USER;
       if($dashboard_param >=0 ){
       if($DB->execute('update {local_users} set dashboard='.$dashboard_param.' where userid='.$USER->id.'')){
       }
       }
          $dashboard = $DB->get_field('local_users','dashboard',array('userid'=>$USER->id));
    ?>
    <div class="row-fluid" id="middle-blocks">
    <?php if ($PAGE->user_is_editing()) { ?>
    <?php echo render_layout_buttons(); ?>
    <?php } ?>
    <!--<div id="title"><h1>Dashboard</h1></div>-->
   <?php 
    if($dashboard==0){ ?>
    <div class="span4" id="fulldashboard">
        <!-- Advert #1 -->
    		<div id="dashboard-one" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardone) {
            		echo $OUTPUT->blocks_for_region('dashboard-one');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-three" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardthree) {
            		echo $OUTPUT->blocks_for_region('dashboard-three');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-four" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardfour) {
            		echo $OUTPUT->blocks_for_region('dashboard-four');
        		} ?>
        		</div>
        	</div>
    </div>
    <?php
}
if($dashboard==1){ ?>
        <!-- Advert #1 -->
        <div class="span4">
    		<div id="dashboard-one" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardone) {
            		echo $OUTPUT->blocks_for_region('dashboard-one');
        		} ?>
        		</div>
        	</div>
        </div>
        <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-two" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardtwo) {
            		echo $OUTPUT->blocks_for_region('dashboard-two');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-three" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardthree) {
            		echo $OUTPUT->blocks_for_region('dashboard-three');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-four" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardfour) {
            		echo $OUTPUT->blocks_for_region('dashboard-four');
        		} ?>
        		</div>
        	</div>
    </div>
    <?php } if($dashboard==2){ ?>
      
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-three" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardthree) {
            		echo $OUTPUT->blocks_for_region('dashboard-three');
        		} ?>
        		</div>
        	</div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
    		<div id="dashboard-four" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardfour) {
            		echo $OUTPUT->blocks_for_region('dashboard-four');
        		} ?>
        		</div>
        	</div>
    </div>
         <div class="span4" id="fulldashboard">
        <!-- Advert #1 -->
    		<div id="dashboard-one" class="block-region">
    			<div class="region-content">
       			<?php if ($hasdashboardone) {
            		echo $OUTPUT->blocks_for_region('dashboard-one');
        		} ?>
        		</div>
        	</div>
         </div>

    <?php
}
}
echo "</div>";
 echo $OUTPUT->main_content() ?>
    </div>
</div>
<!--</div>-->
  
   
         </section>

     <a href="#" class="scrollup">Scroll</a>
        </div>
<!--<footer id="page-footer" class="container-fluid-footer">
            <?php //require('footer.php'); ?>
</footer> -->
<div class="custom_footer"><ul>
	  <li><a href="http://slp.cobaltlms.com" title="Terms and Conditions" target="_blank"><span color="#4d4d4d" style="color: #4d4d4d;"><span style="font-size: 10px;">Terms &amp; Conditions</span></span></a></li>
	  <li><a href="http://slp.cobaltlms.com" title="Privacy and Cookies Policy" target="_blank"><span style="color: #4d4d4d; font-size: 10px;">Privacy and Cookies Policy</span></a></li>
	  <li><a href="http://slp.cobaltlms.com" title="SLP Website" target="_blank"><span style="color: #4d4d4d; font-size: 10px;">SLP Website</span></a></li>
	  <li><span style="color: #4d4d4d; font-size: 10px;">© CobaltLMS.</span></li></ul><div>
<?php echo $OUTPUT->standard_footer_html(); ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>