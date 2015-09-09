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


$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

//$showhiddendock = ($hashiddendock && !$PAGE->blocks->region_completely_docked('hidden-dock', $OUTPUT));
$showfooterfirst = ($hasfooterfirst && !$PAGE->blocks->region_completely_docked('footer-first', $OUTPUT));
$showfootersecond = ($hasfootersecond && !$PAGE->blocks->region_completely_docked('footer-second', $OUTPUT));
$showfootermiddle = ($hasfootermiddle && !$PAGE->blocks->region_completely_docked('footer-middle', $OUTPUT));
$showfooterthird = ($hasfooterthird && !$PAGE->blocks->region_completely_docked('footer-third', $OUTPUT));
$showfooterfourth = ($hasfooterfourth && !$PAGE->blocks->region_completely_docked('footer-fourth', $OUTPUT));

$hasslide1 = (!empty($PAGE->theme->settings->slide1));
$hasslide1image = (!empty($PAGE->theme->settings->slide1image));
$hasslide1caption = (!empty($PAGE->theme->settings->slide1caption));
$hasslide1url = (!empty($PAGE->theme->settings->slide1url));
$hasslide2 = (!empty($PAGE->theme->settings->slide2));
$hasslide2image = (!empty($PAGE->theme->settings->slide2image));
$hasslide2caption = (!empty($PAGE->theme->settings->slide2caption));
$hasslide2url = (!empty($PAGE->theme->settings->slide2url));
$hasslide3 = (!empty($PAGE->theme->settings->slide3));
$hasslide3image = (!empty($PAGE->theme->settings->slide3image));
$hasslide3caption = (!empty($PAGE->theme->settings->slide3caption));
$hasslide3url = (!empty($PAGE->theme->settings->slide3url));
$hasslide4 = (!empty($PAGE->theme->settings->slide4));
$hasslide4image = (!empty($PAGE->theme->settings->slide4image));
$hasslide4caption = (!empty($PAGE->theme->settings->slide4caption));
$hasslide4url = (!empty($PAGE->theme->settings->slide4url));
$hasslideshow = ($hasslide1||$hasslide2||$hasslide3||$hasslide4);

 $market1img = $PAGE->theme->setting_file_url('marketing1content', 'marketing1content');
  $market2img = $PAGE->theme->setting_file_url('marketing2content', 'marketing2content');
   $market3img = $PAGE->theme->setting_file_url('marketing3content', 'marketing3content'); 
 $market4img = $PAGE->theme->setting_file_url('marketing4content', 'marketing4content');
 $hasmarketing1link      = (empty($PAGE->theme->settings->marketing1link)) ? false : $PAGE->theme->settings->marketing1link;
  $hasmarketing2link      = (empty($PAGE->theme->settings->marketing2link)) ? false : $PAGE->theme->settings->marketing2link;
   $hasmarketing3link      = (empty($PAGE->theme->settings->marketing3link)) ? false : $PAGE->theme->settings->marketing3link;
    $hasmarketing4link      = (empty($PAGE->theme->settings->marketing4link)) ? false : $PAGE->theme->settings->marketing4link;
$hasvideo = $PAGE->theme->setting_file_url('video', 'video');
// If there can be a sidepost region on this page and we are editing, always
// show it so blocks can be dragged into it.
//if ($PAGE->user_is_editing()) {
//    if ($PAGE->blocks->is_known_region('side-pre')) {
//        $showsidepre = true;
//    }
//    if ($PAGE->blocks->is_known_region('side-post')) {
//        $showsidepost = false;
//    }
//}

/* Slide1 settings */
$hideonphone = $PAGE->theme->settings-> hideonphone;
if ($hasslide1) {
    $slide1 = $PAGE->theme->settings->slide1;
}
if ($hasslide1image) {
    $slide1image = $PAGE->theme->setting_file_url('slide1image', 'slide1image');
    if (is_null($slide1image)) {
        // Get default image 'slide1image' from themes 'images' folder.
        $slide1image = $OUTPUT->pix_url('images/slide1image', 'theme');
    }
}
if ($hasslide1caption) {
    $slide1caption = $PAGE->theme->settings->slide1caption;
}
if ($hasslide1url) {
    $slide1url = $PAGE->theme->settings->slide1url;
}

/* slide2 settings */
if ($hasslide2) {
    $slide2 = $PAGE->theme->settings->slide2;
}
if ($hasslide2image) {
    $slide2image = $PAGE->theme->setting_file_url('slide2image', 'slide2image');
    if (is_null($slide2image)) {
        // Get default image 'slide2image' from themes 'images' folder.
        $slide3image = $OUTPUT->pix_url('images/slide2image', 'theme');
    }
}
if ($hasslide2caption) {
    $slide2caption = $PAGE->theme->settings->slide2caption;
}
if ($hasslide2url) {
    $slide2url = $PAGE->theme->settings->slide2url;
}

/* slide3 settings */
if ($hasslide3) {
    $slide3 = $PAGE->theme->settings->slide3;
}
if ($hasslide3image) {
    $slide3image = $PAGE->theme->setting_file_url('slide3image', 'slide3image');
    if (is_null($slide3image)) {
        // Get default image 'slide3image' from themes 'images' folder.
        $slide3image = $OUTPUT->pix_url('images/slide3image', 'theme');
    }
}
if ($hasslide3caption) {
    $slide3caption = $PAGE->theme->settings->slide3caption;
}
if ($hasslide3url) {
    $slide3url = $PAGE->theme->settings->slide3url;
}

/* slide4 settings */
if ($hasslide4) {
    $slide4 = $PAGE->theme->settings->slide4;
}
if ($hasslide4image) {
    $slide4image = $PAGE->theme->setting_file_url('slide4image', 'slide4image');
    if (is_null($slide4image)) {
        // Get default image 'slide4image' from themes 'images' folder.
        $slide4image = $OUTPUT->pix_url('images/slide4image', 'theme');
    }
}
if ($hasslide4caption) {
    $slide4caption = $PAGE->theme->settings->slide4caption;
}
if ($hasslide4url) {
    $slide4url = $PAGE->theme->settings->slide4url;
}

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

    <link href='<?php echo $CFG->wwwroot ?>/theme/colms/style/front-style.css' rel='stylesheet' type='text/css'>
    <script src="http://api.html5media.info/1.1.6/html5media.min.js"></script>
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
 	        src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/MYRIADPRO-COND.OTF');
  		font-weight: normal;
  		font-style: normal;
		}
            @font-face {
  		font-family:'Myriad Pro Semibold';
 	        src:  url('<?php echo $CFG->wwwroot ?>/theme/colms/fonts/MYRIADPRO-SEMIBOLD.OTF');
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

<?php if ($hasheader) {
    require_once($CFG->dirroot.'/local/lib.php');
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
                 echo $custommenu;
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

<!-- Start Slideshow -->
<?php 
   // if ($hasslideshow && !strpos($checkuseragent, 'MSIE 7')) { // Hide slideshow for IE7
if(!isloggedin()){
?>
<div id="sliderout">
    <div id="da-slider" class="da-slider <?php echo $hideonphone ?>" >

    <?php if ($hasslide1) { ?>
        <div class="da-slide da-slide-toleft">
            <h2><?php echo $slide1 ?></h2>
            <?php if ($hasslide1caption) { ?>
                <p><?php echo $slide1caption ?></p>
            <?php } ?>
            <?php if ($hasslide1url) { ?>
                <a href="<?php echo $slide1url ?>" class="da-link"><?php echo get_string('readmore','theme_colms')?></a>
            <?php } ?>
            <?php if ($hasslide1image) { ?>
            <div class="da-img"><img src="<?php echo $slide1image ?>" alt="<?php echo $slide1 ?>"></div>
            <?php } ?>
        </div>
    <?php } ?>
    

    <?php if ($hasslide2) { ?>
        <div class="da-slide da-slide-toleft">
            <h2><?php echo $slide2 ?></h2>
            <?php if ($hasslide2caption) { ?>
                <p><?php echo $slide2caption ?></p>
            <?php } ?>
            <?php if ($hasslide2url) { ?>
                <a href="<?php echo $slide2url ?>" class="da-link"><?php echo get_string('readmore','theme_colms')?></a>
            <?php } ?>
            <?php if ($hasslide2image) { ?>
            <div class="da-img"><img src="<?php echo $slide2image ?>" alt="<?php echo $slide2 ?>"></div>
            <?php } ?>
        </div>
    <?php } ?>
    

    <?php if ($hasslide3) { ?>
        <div class="da-slide da-slide-toleft">
            <h2><?php echo $slide3 ?></h2>
            <?php if ($hasslide3caption) { ?>
                <p><?php echo $slide3caption ?></p>
            <?php } ?>
            <?php if ($hasslide3url) { ?>
                <a href="<?php echo $slide3url ?>" class="da-link"><?php echo get_string('readmore','theme_colms')?></a>
            <?php } ?>
            <?php if ($hasslide3image) { ?>
            <div class="da-img"><img src="<?php echo $slide3image ?>" alt="<?php echo $slide3 ?>"></div>
            <?php } ?>
        </div>
    <?php } ?>
    

    <?php if ($hasslide4) { ?>
        <div class="da-slide da-slide-toleft">
            <h2><?php echo $slide4 ?></h2>
            <?php if ($hasslide4caption) { ?>
                <p><?php echo $slide4caption ?></p>
            <?php } ?>
            <?php if ($hasslide4url) { ?>
                <a href="<?php echo $slide4url ?>" class="da-link"><?php echo get_string('readmore','theme_colms')?></a>
            <?php } ?>
            <?php if ($hasslide4image) { ?>
            <div class="da-img"><img src="<?php echo $slide4image ?>" alt="<?php echo $slide4 ?>"></div>
            <?php } ?>
        </div>
    <?php } ?>
     
   <nav class="da-arrows">
            <span class="da-arrows-prev"></span>
            <span class="da-arrows-next"></span>
        </nav>
</div>

</div>
<?php } ?>
<!-- End Slideshow -->

<!-- Start Marketing Spots -->
        <div id="page-content" class="row-fluid">
<?php if ($hasnavbar) { ?>
            <nav class="breadcrumb-button"><?php echo $PAGE->button; ?></nav></div>
            <?php } ?>
<!-- <div class="bor"></div>-->
<?php if(isloggedin()){ ?>
<aside class="span3" style="width:23% !important;">
        <div id="region-post" class="block-region">
            <div class="region-content">
                <?php
                 //   echo $OUTPUT->blocks_for_region('side-pre');
                   echo $OUTPUT->blocks_for_region('side-post');
                ?>
            </div>
        </div>
    </aside>
<?php } ?>
 <?php if ($layout === 'pre-and-post') { ?>
        <section id="region-main" class="span6 desktop-first-column">
        <?php } else if ($layout === 'side-post-only') { ?>
        <?php if(isloggedin()){ ?>
        <section id="region-main" class="span8 desktop-first-column">
        <?php } else { ?>
        <section id="region-main" class="span8 desktop-first-column">
        <?php } } else if ($layout === 'side-pre-only') { ?>
        <section id="region-main" class="span9 desktop-first-column">
        <?php } else if ($layout === 'content-only') { ?>
        <section id="region-main" class="span12">
        <?php } ?>
        <?php if($PAGE->theme->settings->togglemarketing==1 && !isloggedin()) { ?>

<div class="row-fluid" id="middle-blocks">
    <!--<div id="title"><h1>Recentlinks</h1></div>-->
    <div class="span4">
        <!-- Advert #1 -->
        <div class="service">
            <!-- Icon & title. Font Awesome icon used. -->
            <a class="hovertext" href="<?php echo $hasmarketing1link; ?>" title="<?php echo $PAGE->theme->settings->marketing1 ?>"> 
           <img src="<?php echo $market1img ?>"  alt=""></a>
        </div>
    </div>     
    <div class="span4">
        <!-- Advert #2 -->
        <div class="service">
            <!-- Icon & title. Font Awesome icon used. -->
            <a class="hovertext" href="<?php echo $hasmarketing2link; ?>" title="<?php echo $PAGE->theme->settings->marketing2 ?>"> 
           <img src="<?php echo $market2img ?>"  alt=""></a>
        </div>
    </div>
    
    <div class="span4">
        <!-- Advert #3 -->
        <div class="service">
            <!-- Icon & title. Font Awesome icon used. -->
            <a class="hovertext" href="<?php echo $hasmarketing3link; ?>" title="<?php echo $PAGE->theme->settings->marketing3 ?>"> 
           <img src="<?php echo $market3img ?>"  alt=""></a>
        </div>
    </div>
    <div class="span4">
        <!-- Advert #3 -->
        <div class="service">
            <!-- Icon & title. Font Awesome icon used. -->
<a class="hovertext" href="<?php echo $hasmarketing4link; ?>" title="<?php echo $PAGE->theme->settings->marketing4 ?>"> 
<img src="<?php echo $market4img ?>"  alt=""></a>

        </div>
    </div>
            <div class="span4">
        <!-- Advert #3 -->
        <div class="events">
                <div id="title"><h1>Events</h1></div>
               <?php echo colms_get_latest_events(); ?>
        </div>
            </div>
        <div class="span4">
        <!-- Advert #3 -->
        <div class="front_video">
             <div id="title"><h1>Video</h1></div>
        <video width="320" height="240"  controls preload>
  <source src="<?php echo $hasvideo ?>" type="video/mp4">
</video>
        </div>
        </div>
</div>

<!-- End Marketing Spots -->

<?php } ?>
<?php
// $showcharts = $PAGE->theme->settings-> charts;
// if(isloggedin() && $showcharts ==''){
    // require_once($CFG->dirroot .'/chart/trail1.php');
// }
 echo $OUTPUT->main_content() ?>
         </section>
<?php if(!isloggedin()){ ?>
        <aside class="span3">
        <div id="region-post" class="block-region">
            <div class="region-content">
                <?php
                 //   echo $OUTPUT->blocks_for_region('side-pre');
                   echo $OUTPUT->blocks_for_region('side-post');
                ?>
            </div>
        </div>
    </aside>
        <?php } ?>
        <!--<div class="bor"></div>-->
        

    <?php //if ($layout === 'content-only') { ?>
    <!--<section id="region-main" class="span12">-->
    <?php //} else { ?>
    <!--<section id="region-main" class="span9">-->
    <?php //} ?>
        <?php //echo $coursecontentheader; ?>
        <?php //echo $coursecontentfooter; ?>
    <!--</section>-->
    
    
</div>

<?php
/* ***** Commented out as the following hard-coded content was displaying in the source code when hidden via CSS
<div class="sponsors">
        <h4>Our Sponsors</h4>
        <a href="<?php echo $CFG->wwwroot;?>/mod/book/view.php?id=39">
        <img src="<?php echo $OUTPUT->pix_url('sponsors/pukunui', 'theme'); ?>" alt="Pukunui" a href="">
        <img src="<?php echo $OUTPUT->pix_url('sponsors/blindside', 'theme'); ?>" alt="Blindside Networks" a href="">
        <img src="<?php echo $OUTPUT->pix_url('sponsors/packt', 'theme'); ?>" alt="Packt Publishing" a href="">
        <img src="<?php echo $OUTPUT->pix_url('sponsors/freemoodle', 'theme'); ?>" alt="Free Moodle" a href="">
        </a>
</div>
*/
?>


<?php //if (is_siteadmin()) { ?>
<!--<div class="hidden-blocks">
    <div class="row-fluid">
        <h4>Blocks moved into the area below will only be seen by admins</h4>
        <div id="hidden-dock" class="block-region">
          <div class="region-content">
            <?php
               //echo $OUTPUT->blocks_for_region('hidden-dock');
               ?>
            </div>
        </div>
  </div>
</div>
<?php// } ?>-->
     <a href="#" class="scrollup">Scroll</a>
        </div>
<!-- <footer id="page-footer" class="container-fluid-footer">
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