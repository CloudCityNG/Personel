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
 $PAGE->requires->css('/theme/essential/style/course_custom.css');
require_once($OUTPUT->get_include_file('header'));

?>

<div id="page" class="container-fluid">
    <div id="page-navbar" class="clearfix row-fluid">
        <div
            class="breadcrumb-nav pull-<?php echo ($left) ? 'left' : 'right'; ?>"><?php echo $OUTPUT->navbar(); ?></div>
        <nav
            class="breadcrumb-button pull-<?php echo ($left) ? 'right' : 'left'; ?>"><?php echo $OUTPUT->page_heading_button(); ?></nav>
    </div>
    <section role="main-content">
        <!-- Start Main Regions -->
        <?php
                   //if ($course instanceof stdClass) {
                            require_once($CFG->libdir . '/coursecatlib.php');
                            $course = new course_in_list($COURSE);
                        //}
                        //$url = $OUTPUT->pix_url('image-01', 'theme');
                        foreach ($course->get_course_overviewfiles() as $file) {
                            $isimage = $file->is_valid_image();
                            if($isimage)
                            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                            $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
                        }
                        if($url)
                        echo '<img src='.$url.' />';
                        ?>
        <div id="page-content" class="row-fluid">
            <div id="<?php echo $regionbsid ?>" class="span6">
                <div class="row-fluid">
                    <?php if ($hasboringlayout) { ?>
                    <section id="region-main" class="span12 pull-right">
                        <?php } else { ?>
                        <section id="region-main" class="span12 pull-right">
                            <?php } ?>
                            <?php if ($COURSE->id > 1) {
                          //      echo $OUTPUT->heading(format_string($COURSE->fullname), 1, 'coursetitle');
                            //    echo '<div class="bor"></div>';
                            } ?>
                            <?php echo $OUTPUT->course_content_header(); ?>
                            <?php echo $OUTPUT->main_content(); ?>
                            <?php if (empty($PAGE->layout_options['nocoursefooter'])) {
                                echo $OUTPUT->course_content_footer();
                            }?>
                        </section>
                        <?php if ($hasboringlayout) { ?>
                            <?php //echo $OUTPUT->blocks('side-pre', 'span4 desktop-first-column'); ?>
                        <?php } else { ?>
                            <?php //echo $OUTPUT->blocks('side-pre', 'span4 desktop-first-column'); ?>
                        <?php } ?>
                </div>
            </div>
            <?php echo $OUTPUT->blocks('side-post', 'span6'); ?>
        
        <!-- End Main Regions -->
    </section>
                    
</div>

<?php require_once($OUTPUT->get_include_file('footer')); ?>
</body>
</html>
