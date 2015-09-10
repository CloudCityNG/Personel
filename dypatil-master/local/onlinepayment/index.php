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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage programs
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $PAGE, $DB;
require_once($CFG->dirroot.'/local/onlinepayment/lib.php');
require_once($CFG->dirroot.'/local/lib.php');
?>

<!--<style type="text/css">
 .textfilterpos input[type=text]{
    padding:0 !important;

    }   
</style>-->
<?php
$hierarchy = new hierarchy();
//$myprogram = programs::getInstance();
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
 require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//If the loggedin user have the required capability allow the page
//if (!has_capability('local/payment:createtax', $systemcontext) && !has_capability('local/onlinepayment:view', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->set_url('/local/onlinepayment/index.php');
$PAGE->set_title(get_string('pluginname', 'local_onlinepayment'));
//Header and the navigation bar
$PAGE->set_heading(get_string('pluginname', 'local_onlinepayment'));


//$PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/index.php'));
$PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'));
echo $OUTPUT->header();
//Heading of the page
//echo $OUTPUT->heading(get_string('pluginname', 'local_onlinepayment'));

$tax = tax::getInstance();
$tax->createtabview('settings');

$tax->get_inner_headings('index');

if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewtaxratesettingspage', 'local_onlinepayment'));
}
//if (has_capability('local/onlinepayment:manage', $systemcontext)){

$create = html_writer::tag('a', get_string('createtaxrate', 'local_onlinepayment'), array('href'=>$CFG->wwwroot.'/local/onlinepayment/taxrate.php', 'style'=>'float:right;'));
echo '<h4>'.$create.'</h4><br/>';
//}
$rates = $DB->get_records('local_tax_rate');
$countries = get_string_manager()->get_list_of_countries(false);
foreach ($rates as $key => $rate) {
    if($rate->country == 'all'){
        $rates[$key]->country = 'All';
    }
    if (isset($countries[$rate->country])) {
        $rates[$key]->country = $countries[$rate->country];
    }
}
$data = array();
foreach($rates as $rate){
    $line = array();
    $line[] = $rate->name;
    $line[] = $DB->get_field('local_tax_type', 'display_name', array('id'=>$rate->typeid));
    $line[] = $rate->country;
    $line[] = $rate->rate . ' %';
    $line[] = date('d M, Y', $rate->startdate);
    $line[] = date('d M, Y', $rate->enddate);
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/onlinepayment/taxrate.php', array('id' => $rate->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/onlinepayment/taxrate.php', array('id' => $rate->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
   //if (has_capability('local/onlinepayment:manage', $systemcontext))
    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
$PAGE->requires->js('/local/onlinepayment/js/taxrate.js');
if(!empty($data)){
    echo "<div id='filter-box' class='tax_ratefilters'  >";
    echo '<div class="filterarea tr_customfilter"></div></div>';
}
if(empty($data)){
    echo get_string('noratescreatedyet', 'local_onlinepayment');
}
$table = new html_table();
$table->id = "taxratetable";
$table->head  = array(get_string('taxname', 'local_onlinepayment'),
                      get_string('taxtype', 'local_onlinepayment'),
                      get_string('country'),
                      get_string('taxrate', 'local_onlinepayment'),
                      get_string('startdate', 'local_academiccalendar'),
                      get_string('enddate', 'local_academiccalendar'),
                      get_string('action'));
//if (has_capability('local/onlinepayment:manage', $systemcontext))
//$table->head[]=get_string('action');
$table->size  = array('15%', '15%', '20%', '15%', '13%', '12%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'center', 'center');
$table->width = '100%';
$table->data  = $data;
if(!empty($data))
echo html_writer::table($table);
echo $OUTPUT->footer();
