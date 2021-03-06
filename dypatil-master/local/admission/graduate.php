<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/admission/lib.php');
$PAGE->requires->js('/local/admission/js/toggle.js');
$page = optional_param('page', 0, PARAM_INT);
$pgmtype = optional_param('pgmtype', 2, PARAM_INT);
$conf = new object();
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/graduate.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('basic_title', 'local_admission'));
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/index.php'));
$PAGE->navbar->add(get_string('apply', 'local_admission'));
$PAGE->requires->css('/local/admission/css/style.css');
echo $OUTPUT->header();
echo '<div class="admission">';
$baseurl = new moodle_url('/local/admission/graduate.php');
$currenttab = 'graduate';
$hierarchy = new hierarchy();
$admission = cobalt_admission::get_instance();
$admission->admission_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('graduatedesc', 'local_programs'));
}
$sql = $admission->get_clalender_pgm($pgmtype);
$query = $DB->get_records_sql($sql);
$totalcount = count($query);
$perpage = 5;
$list = $page * $perpage;
$programs = $DB->get_records_sql('' . $sql . ' LIMIT ' . $list . ',' . $perpage . '');
try {
    if (empty($query)) {
        $e = '<p style="font-size:15px;font-style:italic;">' . get_string('no_adms', 'local_admission') . '</p>';
        throw new Exception($e);
    } else {
        foreach ($programs as $program) {
            $conf->sfn = $DB->get_field('local_school', 'fullname', array('id' => $program->schoolid));
            $conf->psn = $program->shortname;
            $conf->pfn = $program->fullname;
            $conf->sd = $program->startdate;
            $conf->ed = $program->enddate;
            $conf->sid = $program->schoolid;
            $conf->pid = $program->programid;
            $conf->ptype = $pgmtype;
            if ($program->type == 1) {
                echo '<ul id="programs" class="applyonline">';
                echo '<li>';
                echo '<a style="cursor:pointer">' . get_string('program', 'local_programs') . ' : ' . $program->fullname . ' ' . '<span style="float:right;width:40%;">School : ' . $conf->sfn . '</span></a>';
                echo '<ul class="desc">';
                echo '<hr class="line">';
                if ($program->enddates != 0) {
                    echo get_string('graduatelist', 'local_collegestructure', $conf);
                } else {
                    echo get_string('graduatelists', 'local_collegestructure', $conf);
                }
                echo html_writer::tag('h6', '<b style="color:#30add1;">' . get_string('startdate', 'local_clclasses') . ' : ' . $program->startdate . '</b>');
                if ($program->enddates != 0) {
                    echo html_writer::tag('h6', '<b style="color:#30add1;">' . get_string('to', 'local_clclasses') . '   : ' . $program->enddate . '</b>');
                }
                // to check admission even date less than or equal to today date
                $prgr_admissionenddate = $program->enddates;
                $today=strtotime("today");
                if($today>$prgr_admissionenddate)
                echo html_writer::tag('a', 'Download', array('href' => '' . $CFG->wwwroot . '/local/admission/application.php?sid=' . $program->schoolid . '&pid=' . $program->programid . '&ptype=' . $pgmtype . '', 'class' => 'downloadbutton'));
                else
                echo html_writer::tag('a', 'Apply Now', array('href' => '' . $CFG->wwwroot . '/local/admission/basic.php?sid=' . $program->schoolid . '&pid=' . $program->programid . '&ptype=' . $pgmtype . '', 'class' => 'downloadbutton'));
                echo '</ul></li> </ul>';
            } else {
                echo '<ul id="programs" class="applyonline">';
                echo '<li>';
                echo '<a style="cursor:pointer">' . get_string('program', 'local_programs') . ' : ' . $program->fullname . ' ' . '<span style="float:right;width:40%;">School : ' . $conf->sfn . '</span></a>';
                echo '<ul class="desc">';
                echo '<hr class="line">';
                if ($program->enddates != 0) {
                    echo get_string('offgraduatelist', 'local_collegestructure', $conf);
                } else {
                    echo get_string('offgraduatelists', 'local_collegestructure', $conf);
                }
                echo html_writer::tag('h6', '<b style="color:#30add1;">' . get_string('startdate', 'local_clclasses') . ' : ' . $program->startdate . '</b>');
                if ($program->enddates != 0) {
                    echo html_writer::tag('h6', '<b style="color:#30add1;">' . get_string('to', 'local_clclasses') . '   : ' . $program->enddate . '</b>');
                }
                echo html_writer::tag('a', 'Download', array('href' => '' . $CFG->wwwroot . '/local/admission/application.php?sid=' . $program->schoolid . '&pid=' . $program->programid . '&ptype=' . $pgmtype . '', 'class' => 'downloadbutton'));
                echo '</ul></li> </ul>';
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
echo '</div>';
echo $OUTPUT->footer();
?>