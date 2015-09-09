<?php

require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/academiccalendar/lib.php');

function get_events() {
    global $DB, $CFG, $USER;
    $systemcontext = get_context_instance(CONTEXT_SYSTEM);
    ?>
    <?php

    if (is_siteadmin()) {
$time = time();

        $sql = 'select * from {local_event_activities} where FROM_UNIXTIME("%d-%m-%y",startdate) >= FROM_UNIXTIME("%d-%m-%y",'.$time.') OR 
              FROM_UNIXTIME("%d-%m-%y",'.$time.') <= FROM_UNIXTIME("%d-%m-%y",enddate) LIMIT 0,3';
        
        $eactivities = $DB->get_records_sql($sql);
        if (!empty($eactivities)) {
            $data = array();
			$i = 1;
            foreach ($eactivities as $eactivity) {
				if($i > 3){
					break;
				}
                $line = array();
                $eactivity->startdate = date('d-m-Y', $eactivity->startdate);
                $startdate = $eactivity->startdate;
                $eactivity->enddate > 0 ? $eactivity->enddate = date('d-m-Y', $eactivity->enddate) : $eactivity->enddate = '';
                $enddate = $eactivity->enddate;
                $view = new moodle_url('' . $CFG->wwwroot . '/local/academiccalendar/viewevent.php', array('id' => $eactivity->id));
                $line[] = html_writer::link($view, $eactivity->eventtitle);
                if ($enddate) {
                    $line[] = $startdate . '&nbsp;&nbsp;-&nbsp;&nbsp;' . $enddate;
                } else {
                    $line[] = $startdate;
                }
				$i++;
                $data[] = $line;
            }
            $table = new html_table();
            $table->head = array(
                get_string('eventtitle', 'local_academiccalendar'),
                get_string('date')
            );
            $table->width = '100%';
            $table->align = array('left','left');
            $table->size = array('50%','50%');
            $table->data = $data;
            $string = html_writer::table($table);
			if(sizeof($data) > 3)
				$string.='<a href="../local/academiccalendar/index.php">View more...</a>';
            return $string;
        } else {
            $string = '<p>No events created till now.</p>';
            return $string;
        } 
    } elseif (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
        $hier = new hierarchy();
        $time = time();
        $schoolslist = $hier->get_assignedschools();
        $array = array();
        $schools = $hier->get_school_parent($schoolslist, $array, false, false);
        foreach ($schools as $key => $value) {
		if($key!=null) {
            $schoollist[] = $key;
			}
        }
        $schoollist_string = implode(',', $schoollist);
        $sql = "select * from {local_event_activities} where eventlevel=2 and schoolid in ( $schoollist_string ) and (FROM_UNIXTIME('%d-%m-%y',startdate) >= FROM_UNIXTIME('%d-%m-%y',$time) OR 
              FROM_UNIXTIME('%d-%m-%y',$time) <= FROM_UNIXTIME('%d-%m-%y',enddate)) LIMIT 0,3";
        $eactivities = $DB->get_records_sql($sql);
        if (!empty($eactivities)) {
            $data = array();
            foreach ($eactivities as $eactivity) {
                $line = array();
                $eactivity->startdate = date('d-m-Y', $eactivity->startdate);
                $startdate = $eactivity->startdate;
                $eactivity->enddate > 0 ? $eactivity->enddate = date('d M Y', $eactivity->enddate) : $eactivity->enddate = '';
                $enddate = $eactivity->enddate;
                $view = new moodle_url('' . $CFG->wwwroot . '/local/academiccalendar/viewevent.php', array('id' => $eactivity->id));
                $line[] = html_writer::link($view, $eactivity->eventtitle);
                if ($enddate) {
                    $line[] = $startdate . '&nbsp;&nbsp;-&nbsp;&nbsp;' . $enddate;
                } else {
                    $line[] = $startdate;
                }
                $data[] = $line;
            } 
            $table = new html_table();
            $table->head = array(
                get_string('eventtitle', 'local_academiccalendar'),
                get_string('date', 'block_classrooms')
                
            );
            $table->width = '100%';
            $table->align = array('left', 'left');
            $table->size = array('50%', '50%');
            $table->data = $data;
            $string = html_writer::table($table);
            return $string;
        } else {
            $string = '<p>No events created till now.</p>';
            return $string;
        }
    } else {
        $time = time();
        $userdetails = $DB->get_records('local_userdata', array('userid' => $USER->id));
        if ($userdetails) {
            foreach ($userdetails as $userdetail) {
                $schoollist[] = $userdetail->schoolid;
                $programlist[] = $userdetail->programid;
            }
            $schoollist_string = implode(',', $schoollist);
            $programlist_string = implode(',', $programlist);
            $string = '<div id="event_tabs">
		<ul>
		<li><a href="#fragment-3"><span>Academic Events</span></a></li>
		<li><a href="#fragment-2"><span>Global Events</span></a></li>
		</ul>';

            $string.='<div id="fragment-3">';
            $sql = 'select * from {local_event_activities} where (FROM_UNIXTIME("%d-%m-%y",startdate) >= FROM_UNIXTIME("%d-%m-%y",'.$time.') OR 
              FROM_UNIXTIME("%d-%m-%y",'.$time.') <= FROM_UNIXTIME("%d-%m-%y",enddate)) and 
                                     (eventlevel=2 and schoolid in (' . $schoollist_string . ')) OR
                                     (
                                     eventlevel=3 and schoolid in (' . $schoollist_string . ') AND
                                     programid in (' . $programlist_string . ')
                                     ) LIMIT 0,3';

            $eactivities1 = $DB->get_records_sql($sql);

            $data = array();
            foreach ($eactivities1 as $eactivity) {
                $line = array();
                $eactivity->startdate = strtoupper(date('d-M-Y', $eactivity->startdate));
                $startdate = $eactivity->startdate;
                $eactivity->enddate > 0 ? $eactivity->enddate = strtoupper(date('d-M-Y', $eactivity->enddate)) : $eactivity->enddate = '';
                $enddate = $eactivity->enddate;
                $view = new moodle_url('' . $CFG->wwwroot . '/local/academiccalendar/viewevent.php', array('id' => $eactivity->id));
                $line[] = html_writer::link($view, $eactivity->eventtitle);
                if ($enddate) {
                    $line[] = $startdate . '&nbsp;&nbsp;-&nbsp;&nbsp;' . $enddate;
                } else {
                    $line[] = $startdate;
                }
                $data[] = $line;
            }
            $table = new html_table();
            $table->head = array(
                get_string('eventtitle', 'local_academiccalendar'),
                get_string('date', 'block_classrooms')
//                                   get_string('enddate','local_academiccalendar')
            );
            $table->width = '100%';
            $table->align = array('left', 'center');
            $table->size = array('50%', '50%');
            $table->data = $data;
            $string.= html_writer::table($table);
            if($data)
                $string .= '<a href="' . $CFG->wwwroot . '/local/academiccalendar/index.php" > View All </a> ';
            else
                $string .= '<p>No events created.</p>';
            $string.='</div>';
            
            $string.='<div id="fragment-2">';
            $sql = 'select * from {local_event_activities} where eventlevel=1 and (FROM_UNIXTIME("%d-%m-%y",startdate) >= FROM_UNIXTIME("%d-%m-%y",'.$time.') OR 
              FROM_UNIXTIME("%d-%m-%y",'.$time.') <= FROM_UNIXTIME("%d-%m-%y",enddate)) LIMIT 0,3';
            $eactivities = $DB->get_records_sql($sql);
            $data1 = array();
            foreach ($eactivities as $eactivity) {
                $line1 = array();
                $eactivity->startdate = strtoupper(date('d-M-Y', $eactivity->startdate));
                $startdate = $eactivity->startdate;
                $eactivity->enddate > 0 ? $eactivity->enddate = strtoupper(date('d-M-Y', $eactivity->enddate)) : $eactivity->enddate = '';
                $enddate = $eactivity->enddate;
                $view = new moodle_url('' . $CFG->wwwroot . '/local/academiccalendar/viewevent.php', array('id' => $eactivity->id));
                $line1[] = html_writer::link($view, $eactivity->eventtitle);
                if ($enddate) {
                    $line1[] = $startdate . '&nbsp;&nbsp;-&nbsp;&nbsp;' . $enddate;
                } else {
                    $line1[] = $startdate;
                }
//				$line1[] =  $enddate;
                $data1[] = $line1;
            }
            $table1 = new html_table();
            $table1->head = array(
                get_string('eventtitle', 'local_academiccalendar'),
                get_string('date', 'block_classrooms'),
//                                   get_string('enddate','local_academiccalendar')
            );
            $table1->width = '100%';
            $table1->align = array('left', 'center');
            $table1->size = array('50%', '50%');
            $table1->data = $data1;
            $string.= html_writer::table($table1);
            if($data1)
                $string .= '<a href="' . $CFG->wwwroot . '/local/academiccalendar/index.php" > View All </a> ';
            else
                $string .= '<p>No events created.</p>';
            $string.='</div>';
            $string .= '</div>';
            return $string;
        }
        else
            $string = '<p>You are not registered to any type of courses.</p>';
        return $string;
    }
    ?>
<?php } ?>

