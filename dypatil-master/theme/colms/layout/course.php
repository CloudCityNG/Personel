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
 * @package   theme_co
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hasheader = (empty($PAGE->layout_options['noheader']));

$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));

$hasfooterfirst = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-first', $OUTPUT));
$hasfootersecond = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-second', $OUTPUT));
$hasfootermiddle = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-middle', $OUTPUT));
$hasfooterthird = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-third', $OUTPUT));
$hasfooterfourth = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-fourth', $OUTPUT));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$showfooterfirst = ($hasfooterfirst && !$PAGE->blocks->region_completely_docked('footer-first', $OUTPUT));
$showfootersecond = ($hasfootersecond && !$PAGE->blocks->region_completely_docked('footer-second', $OUTPUT));
$showfootermiddle = ($hasfootermiddle && !$PAGE->blocks->region_completely_docked('footer-middle', $OUTPUT));
$showfooterthird = ($hasfooterthird && !$PAGE->blocks->region_completely_docked('footer-third', $OUTPUT));
$showfooterfourth = ($hasfooterfourth && !$PAGE->blocks->region_completely_docked('footer-fourth', $OUTPUT));

// If there can be a sidepost region on this page and we are editing, always
// show it so blocks can be dragged into it.
if ($PAGE->user_is_editing()) {
    if ($PAGE->blocks->is_known_region('side-pre')) {
        $showsidepre = true;
    }
    if ($PAGE->blocks->is_known_region('side-post')) {
        $showsidepost = true;
    }
}

//if(isloggedin()){
//    $haslogo = (!empty($PAGE->theme->settings->allogo));
//}  else{
//$haslogo = (!empty($PAGE->theme->settings->bllogo));
//}

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
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google web fonts -->
    <link href='http://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'>
        
    <link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic' rel='stylesheet' type='text/css'>
       <style type="text/css">
        @font-face {
  		font-family: 'FontAwesome';
  		src: url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/fontawesome-webfont.eot?v=3.2.1');
  		src: url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/fontawesome-webfont.eot?#iefix&v=3.2.1') format('embedded-opentype'), 
  			url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/fontawesome-webfont.woff?v=3.2.1') format('woff'), 
  			url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/fontawesome-webfont.ttf?v=3.2.1') format('truetype'), 
  			url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/fontawesome-webfont.svg#fontawesomeregular?v=3.2.1') format('svg');
  		font-weight: normal;
  		font-style: normal;
		}
                @font-face {
  		font-family: 'MyriadPro-Regular';
 	src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/MyriadPro-Regular.otf');
  		font-weight: normal;
  		font-style: normal;
		}
            @font-face {
  		font-family:'Myriad Pro Condensed';
 	        src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/MYRIADPRO-COND.otf');
  		font-weight: normal;
  		font-style: normal;
		}
            @font-face {
  		font-family:'Myriad Pro Semibold';
 	        src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/MYRIADPRO-SEMIBOLD.otf');
  		font-weight: normal;
  		font-style: normal;
	    }
            @font-face {
  		font-family:'Myriad Cn Semibold';
 	        src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/Myriad Cn Semibold.ttf');
  		font-weight: normal;
  		font-style: normal;
	    }
                                @font-face {
  		font-family: 'MyriadPro-Bold';
 	src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/MyriadPro-Bold.otf');
  		font-weight: normal;
  		font-style: normal;
		}
        @font-face {
  		font-family: 'Opensans-Regular';
 	src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/OpenSans-Regular.ttf');
  		font-weight: normal;
  		font-style: normal;
		}
                        @font-face {
  		font-family: 'OpenSans-Semibold';
 	src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/OpenSans-Semibold.ttf');
  		font-weight: normal;
  		font-style: normal;
		}
    </style>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php 
if ($hasheader) {
    include('header.php');
}
?>

<header role="banner" class="navbar">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
     <!--<a class="brand" href="<?php// echo $CFG->wwwroot;?>"><i class="icon-home"></i>&nbsp;<?php //print_string('home'); ?></a>-->

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
                    <?php if ($hasnavbar) { ?>
                   <div id="bnavbar"><div id="onavbar"><?php echo $OUTPUT->navbar(); ?></div>
            <nav class="breadcrumb-button"><?php echo $PAGE->button; ?></nav></div>
            <?php } ?>
        
         <?php if ($layout === 'pre-and-post') { ?>
        <div class="span6">
        <?php } else if ($layout === 'side-post-only') { ?>
        <div class="span6">
        <?php } else if ($layout === 'side-pre-only') { ?>
        <div class="span6">
        <?php } else if ($layout === 'content-only') { ?>
        <div class="span0">
        <?php } ?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                    <?php echo $OUTPUT->blocks_for_region('side-pre'); ?>
                    </div>
                </div>
            </div>
        <?php if ($layout === 'pre-and-post') { ?>
        <section id="region-main" class="span6 desktop-first-column">
        <?php } else if ($layout === 'side-post-only') { ?>
        <section id="region-main" class="span8 desktop-first-column">
        <?php } else if ($layout === 'side-pre-only') { ?>
        <section id="region-main" class="span9 desktop-first-column">
        <?php } else if ($layout === 'content-only') { ?>
        <section id="region-main" class="span12">
        <?php } ?>

            <?php echo $coursecontentheader; ?>
            <h2 class="pagetitle"><span><?php echo $PAGE->title ?></span></h2>  
            <?php echo $OUTPUT->main_content() ?>
            <?php echo $coursecontentfooter; ?>
        </section>
<?php if ($layout === 'pre-and-post') { ?>
        <div class="span6">
        <?php } else if ($layout === 'side-post-only') { ?>
        <div class="span6">
        <?php } else if ($layout === 'side-pre-only') { ?>
        <div class="span6">
        <?php } else if ($layout === 'content-only') { ?>
        <div class="span0">
        <?php } ?>
            <div id="region-post" class="block-region">
                    <div class="region-content">
                    <?php echo $OUTPUT->blocks_for_region('side-post'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
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