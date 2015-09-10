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

require_once($OUTPUT->get_include_file('header'));

$enable1alert = $OUTPUT->get_setting('enable1alert');
$enable2alert = $OUTPUT->get_setting('enable2alert');
$enable3alert = $OUTPUT->get_setting('enable3alert');

if ($enable1alert || $enable2alert || $enable3alert) {
    $alertinfo = '<span class="fa-stack "><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-info fa-stack-1x fa-inverse"></i></span>';
    $alerterror = '<span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-warning fa-stack-1x fa-inverse"></i></span>';
    $alertsuccess = '<span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-bullhorn fa-stack-1x fa-inverse"></i></span>';
}
?>

<?php if(!isloggedin()) { ?>
<style>
#page {
    max-width: 1680px;
    /*background: #FFF;*/
    padding-top: 0;
}
#page > section[role="main-content"] {
    margin: 0 auto;
    width: 1200px;
}
.container-fluid {
    padding-left: 0px;
    padding-right: 0px;
}
#page #page-content {
    margin-top: 16px !important;
}
#block-region-side-pre .block h2, #block-region-side-post .block h2, #middle-blocks .block .header .title h2{
    color: #003366;
 
#block-region-side-pre .block, #block-region-side-post .block{
    border: none;
    border-radius: 6px;
}
#page-site-index #middle-blocks .block .content{
    padding-top: 16px;
}
</style>
<?php } ?>

<div id="page" class="container-fluid">
    <div id="page-navbar" class="clearfix row-fluid">
        <div class="breadcrumb-nav pull-<?php echo ($left) ? 'left' : 'right'; ?>"><?php echo $OUTPUT->navbar(); ?></div>
        <nav class="breadcrumb-button pull-<?php echo ($left) ? 'right' : 'left'; ?>"><?php echo $OUTPUT->page_heading_button(); ?></nav>
    </div>
    <section role="main-content">
        <!-- Start Main Regions -->

        <div id="page-content" class="row-fluid">
            <section id="<?php echo $regionbsid; ?>">
                <?php if(!isloggedin()){ ?>
                    <section id="region-main" class="span8 pull-right">
                <?php } else                
                if ($OUTPUT->get_setting('frontpageblocks')) { ?>
                <section id="region-main" class="span9 pull-right">
                    <?php } else { ?>
                    <section id="region-main" class="span9 desktop-first-column">
                        
                        <?php } ?>
                        <?php
                        if(isloggedin()){
                            if(has_capability('local/collegestructure:manage', context_system::instance())){
                                //registrar also for admin
                                echo $OUTPUT->heading('Office of the Registrar');
                            } else {
                                echo $OUTPUT->heading('My Dashboard');
                            }
                        }
                        // Start Middle Blocks 
                        $frontpagemiddleblocks = $OUTPUT->get_setting('frontpagemiddleblocks');
                        
                        if ($frontpagemiddleblocks == 1) {
                            require_once($OUTPUT->get_include_file('middleblocks'));
                        } else if ($frontpagemiddleblocks == 2 && !isloggedin()) {
                            require_once($OUTPUT->get_include_file('middleblocks'));
                        } else if ($frontpagemiddleblocks == 3 && isloggedin()) {
                            require_once($OUTPUT->get_include_file('middleblocks'));
                        }
                        //End Middle Blocks
                        
                        //frontpage middle content
                        if ((isloggedin() && $PAGE->user_is_editing()) || !isloggedin()) {
                            require_once($OUTPUT->get_include_file('frontblocks'));
                        } 
                        
                        echo $OUTPUT->course_content_header();
                        
                        echo $OUTPUT->main_content();
                        echo $OUTPUT->course_content_footer();
                        ?>
                    </section>
                    <?php
                    if(!isloggedin()){
                        echo $OUTPUT->blocks('side-pre', 'span4 desktop-first-column');
                    } else
                    if ($OUTPUT->get_setting('frontpageblocks')) {
                        echo $OUTPUT->blocks('side-pre', 'span3 desktop-first-column');
                    } else {
                        echo $OUTPUT->blocks('side-pre', 'span3 pull-right');
                    }
                    ?>
                </section>
        </div>

        <!-- End Main Regions -->

        <?php if (is_siteadmin()) { ?>
            <div class="hidden-blocks">
                <div class="row-fluid">
                    <h4><?php echo get_string('visibleadminonly', 'theme_essential'); ?></h4>
                    <?php echo $OUTPUT->blocks('hidden-dock'); ?>
                </div>
            </div>
        <?php } ?>

    </section>
</div>

<?php require_once($OUTPUT->get_include_file('footer')); ?>

<!-- Initialize slideshow -->
<script type="text/javascript">
    jQuery(document).ready(function () {
        $('.carousel').carousel();
    });
</script>
</body>
</html>
