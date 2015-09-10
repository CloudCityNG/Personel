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

$hasfacebook    = (empty($PAGE->theme->settings->facebook)) ? false : $PAGE->theme->settings->facebook;
$hastwitter     = (empty($PAGE->theme->settings->twitter)) ? false : $PAGE->theme->settings->twitter;
$hasgoogleplus  = (empty($PAGE->theme->settings->googleplus)) ? false : $PAGE->theme->settings->googleplus;
$haslinkedin    = (empty($PAGE->theme->settings->linkedin)) ? false : $PAGE->theme->settings->linkedin;
$hasyoutube     = (empty($PAGE->theme->settings->youtube)) ? false : $PAGE->theme->settings->youtube;
$hasflickr      = (empty($PAGE->theme->settings->flickr)) ? false : $PAGE->theme->settings->flickr;
$hasbllogo = (!empty($PAGE->theme->settings->allogo));
$hasallogo = (!empty($PAGE->theme->settings->bllogo));
if ($hasbllogo) {
    $bllogo = $PAGE->theme->setting_file_url('bllogo', 'bllogo');
    if (is_null($bllogo)) {
        // Get default image 'slide3image' from themes 'images' folder.
        $bllogo = $OUTPUT->pix_url('images/bllogo', 'theme');
    }
}
if ($hasallogo) {
    $allogo = $PAGE->theme->setting_file_url('allogo', 'allogo');
    if (is_null($allogo)) {
        // Get default image 'slide3image' from themes 'images' folder.
        $allogo = $OUTPUT->pix_url('images/allogo', 'theme');
    }
}
/* Modified to check for IE 7/8. Switch headers to remove backgound-size CSS (in Custom CSS) functionality if true */
$checkuseragent = '';
if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $checkuseragent = $_SERVER['HTTP_USER_AGENT'];
}
?>

<?php
// Check if IE7 browser and display message
if (strpos($checkuseragent, 'MSIE 7')) {
	echo get_string('ie7message', 'theme_colms');
}?>

<?php
if (strpos($checkuseragent, 'MSIE 8') || strpos($checkuseragent, 'MSIE 7')) {?>
    <header id="page-header-IE7-8" class="clearfix">
<?php
} else { ?>
    <header id="page-header" class="clearfix">
<?php
} ?>

    <div class="container-fluid">
    <div class="row-fluid">
    <!-- HEADER: LOGO AREA -->
        <div class="span8">
            <?php
            if (!$hasbllogo && !$hasallogo) { ?>
                <i id="headerlogo" class="icon-<?php echo $PAGE->theme->settings->siteicon ?>"></i>
                <h1 id="title"><?php echo $SITE->shortname; ?></h1>
                <h2 id="subtitle"><?php p(strip_tags(format_text($SITE->summary, FORMAT_HTML))) ?></h2>
            <?php
            } else { ?>
                <!--<a class="logo" href="<?php //echo $CFG->wwwroot; ?>" title="<?php //print_string('home'); ?>"></a>-->
            <div id="headerlogo">
	<?php if(isloggedin() && $hasallogo) { ?>
                  <a class="logo" href="<?php echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>"><img src="<?php echo $allogo; ?>" alt="Custom logo here" /></a>
       <?php } else if(isloggedin() && !$haslogo) { ?>
                  <a class="logo" href="<?php echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>"><img src="<?php echo $bllogo; ?>" alt="Custom logo here" /></a>
       <?php }else if(!isloggedin() && $hasbllogo){ ?>
                  <a class="logo" href="<?php echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>"><img src="<?php echo $bllogo; ?>" alt="Custom logo here" /></a>
       <?php } ?>
                                    </div>
	    <?php
            } ?>
	    
        </div>

	<div class="span4 pull-right" id="loginlink">
	<?php
	    echo $OUTPUT->login_info() ?>
	   <?php if(!isloggedin()){echo colms_theme_login_form(); } ?>
        </div>

        <?php if (!empty($courseheader)) { ?>
        <div id="course-header"><?php echo $courseheader; ?></div>
        <?php } ?>
    </div>

</header>