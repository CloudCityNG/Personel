<?php

defined('MOODLE_INTERNAL') or die;

/**
 * @method progress_modules_in_use
 * @todo get no.of  modules active in course
 * @param int $id course id
 * @return-- active modules in course
 */
function progress_modules_in_use($id) {
    global $DB;
    $dbmanager = $DB->get_manager(); // Used to check if tables exist.
    $modules = progress_monitorable_modules();
    $modulesinuse = array();
    foreach ($modules as $module => $details) {
        if ($dbmanager->table_exists($module) && $DB->record_exists($module, array('course' => $id))) {
            $modulesinuse[$module] = $details;
        }
    }
    return $modulesinuse;
}

/**
 * @method progress_monitorable_modules
 * @todo Provides information about monitorable modules
 * @param int $id course id
 * @return-- array
 */

function progress_monitorable_modules() {
    global $DB;

    return array(
        'assign' => array(
            'defaultTime' => 'duedate',
            'actions' => array(
                'submitted' => "SELECT id
                                     FROM {assign_submission}
                                    WHERE assignment = :eventid
                                      AND userid = :userid
                                      AND status = 'submitted'",
                'marked' => "SELECT g.rawgrade
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'assign'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid
                                      AND g.finalgrade IS NOT NULL",
                'passed' => "SELECT g.finalgrade, i.gradepass
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'assign'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid"
            ),
            'defaultAction' => 'submitted'
        ),
        'assignment' => array(
            'defaultTime' => 'timedue',
            'actions' => array(
                'submitted' => "SELECT id
                                     FROM {assignment_submissions}
                                    WHERE assignment = :eventid
                                      AND userid = :userid
                                      AND (
                                          numfiles >= 1
                                          OR {$DB->sql_compare_text('data2')} <> ''
                                      )",
                'marked' => "SELECT g.rawgrade
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'assignment'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid
                                      AND g.finalgrade IS NOT NULL",
                'passed' => "SELECT g.finalgrade, i.gradepass
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'assignment'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid"
            ),
            'defaultAction' => 'submitted'
        ),
        'bigbluebuttonbn' => array(
            'defaultTime' => 'timedue',
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'bigbluebuttonbn'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'recordingsbn' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'recordingsbn'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'book' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'book'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'certificate' => array(
            'actions' => array(
                'awarded' => "SELECT id
                                     FROM {certificate_issues}
                                    WHERE certificateid = :eventid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'awarded'
        ),
        'chat' => array(
            'actions' => array(
                'posted_to' => "SELECT id
                                     FROM {chat_messages}
                                    WHERE chatid = :eventid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'posted_to'
        ),
        'choice' => array(
            'defaultTime' => 'timeclose',
            'actions' => array(
                'answered' => "SELECT id
                                     FROM {choice_answers}
                                    WHERE choiceid = :eventid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'answered'
        ),
        'data' => array(
            'defaultTime' => 'timeviewto',
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'data'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'feedback' => array(
            'defaultTime' => 'timeclose',
            'actions' => array(
                'responded_to' => "SELECT id
                                     FROM {feedback_completed}
                                    WHERE feedback = :eventid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'responded_to'
        ),
        'resource' => array(// AKA file.
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'resource'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'flashcardtrainer' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'flashcardtrainer'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'folder' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'folder'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'forum' => array(
            'defaultTime' => 'assesstimefinish',
            'actions' => array(
                'posted_to' => "SELECT id
                                     FROM {forum_posts}
                                    WHERE userid = :userid AND discussion IN (
                                          SELECT id
                                            FROM {forum_discussions}
                                           WHERE forum = :eventid
                                    )"
            ),
            'defaultAction' => 'posted_to'
        ),
        'glossary' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'glossary'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'hotpot' => array(
            'defaultTime' => 'timeclose',
            'actions' => array(
                'attempted' => "SELECT id
                                     FROM {hotpot_attempts}
                                    WHERE hotpotid = :eventid
                                      AND userid = :userid",
                'finished' => "SELECT id
                                     FROM {hotpot_attempts}
                                    WHERE hotpotid = :eventid
                                      AND userid = :userid
                                      AND timefinish <> 0",
            ),
            'defaultAction' => 'finished'
        ),
        'imscp' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'imscp'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'journal' => array(
            'actions' => array(
                'posted_to' => "SELECT id
                                     FROM {journal_entries}
                                    WHERE journal = :eventid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'posted_to'
        ),
        'lesson' => array(
            'defaultTime' => 'deadline',
            'actions' => array(
                'attempted' => "SELECT id
                                     FROM {lesson_attempts}
                                    WHERE lessonid = :eventid
                                      AND userid = :userid
                                UNION ALL
                                   SELECT id
                                     FROM {lesson_branch}
                                    WHERE lessonid = :eventid1
                                      AND userid = :userid1",
                'graded' => "SELECT g.rawgrade
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'lesson'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid
                                      AND g.finalgrade IS NOT NULL"
            ),
            'defaultAction' => 'attempted'
        ),
        'page' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'page'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'questionnaire' => array(
            'defaultTime' => 'closedate',
            'actions' => array(
                'attempted' => "SELECT id
                                     FROM {questionnaire_attempts}
                                    WHERE qid = :eventid
                                      AND userid = :userid",
                'finished' => "SELECT id
                                     FROM {questionnaire_response}
                                    WHERE complete = 'y'
                                      AND username = :userid
                                      AND survey_id = :eventid",
            ),
            'defaultAction' => 'finished'
        ),
        'quiz' => array(
            'defaultTime' => 'timeclose',
            'actions' => array(
                'attempted' => "SELECT id
                                     FROM {quiz_attempts}
                                    WHERE quiz = :eventid
                                      AND userid = :userid",
                'finished' => "SELECT id
                                     FROM {quiz_attempts}
                                    WHERE quiz = :eventid
                                      AND userid = :userid
                                      AND timefinish <> 0",
                'graded' => "SELECT g.rawgrade
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'quiz'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid
                                      AND g.finalgrade IS NOT NULL",
                'passed' => "SELECT g.finalgrade, i.gradepass
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'quiz'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid"
            ),
            'defaultAction' => 'finished'
        ),
        'scorm' => array(
            'actions' => array(
                'attempted' => "SELECT id
                                     FROM {scorm_scoes_track}
                                    WHERE scormid = :eventid
                                      AND userid = :userid",
                'completed' => "SELECT id
                                     FROM {scorm_scoes_track}
                                    WHERE scormid = :eventid
                                      AND userid = :userid
                                      AND element = 'cmi.core.lesson_status'
                                      AND {$DB->sql_compare_text('value')} = 'completed'",
                'passedscorm' => "SELECT id
                                     FROM {scorm_scoes_track}
                                    WHERE scormid = :eventid
                                      AND userid = :userid
                                      AND element = 'cmi.core.lesson_status'
                                      AND {$DB->sql_compare_text('value')} = 'passed'"
            ),
            'defaultAction' => 'attempted'
        ),
        'sociopedia' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'sociopedia'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'storywall' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'storywall'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'turnitintool' => array(
            'defaultTime' => 'defaultdtdue',
            'actions' => array(
                'submitted' => "SELECT id
                                     FROM {turnitintool_submissions}
                                    WHERE turnitintoolid = :eventid
                                      AND userid = :userid
                                      AND submission_score IS NOT NULL"
            ),
            'defaultAction' => 'submitted'
        ),
        'url' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'url'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'wiki' => array(
            'actions' => array(
                'viewed' => "SELECT id
                                     FROM {log}
                                    WHERE course = :courseid
                                      AND module = 'wiki'
                                      AND action = 'view'
                                      AND cmid = :cmid
                                      AND userid = :userid"
            ),
            'defaultAction' => 'viewed'
        ),
        'workshop' => array(
            'defaultTime' => 'assessmentend',
            'actions' => array(
                'submitted' => "SELECT id
                                     FROM {workshop_submissions}
                                    WHERE workshopid = :eventid
                                      AND authorid = :userid",
                'assessed' => "SELECT s.id
                                     FROM {workshop_assessments} a, {workshop_submissions} s
                                    WHERE s.workshopid = :eventid
                                      AND s.id = a.submissionid
                                      AND a.reviewerid = :userid
                                      AND a.grade IS NOT NULL",
                'graded' => "SELECT g.rawgrade
                                     FROM {grade_grades} g, {grade_items} i
                                    WHERE i.itemmodule = 'workshop'
                                      AND i.iteminstance = :eventid
                                      AND i.id = g.itemid
                                      AND g.userid = :userid
                                      AND g.finalgrade IS NOT NULL"
            ),
            'defaultAction' => 'submitted'
        ),
    );
}

/**
 * @method progress_event_information
 * @todo Gets event information about modules monitored by an instance of a Progress Bar block
 * @param stdClass $id  The block instance configuration values
 * @param array    $modules The modules used in the course
 * @return mixed   returns array of visible events monitored,
 *                 empty array if none of the events are visible,
 *                 null if all events are configured to "no" monitoring and
 *                 0 if events are available but no config is set
 */
function progress_event_information($modules, $id) {
    global $COURSE, $DB;
    $events = array();
    $numevents = 0;
    $numeventsconfigured = 0;
    $orderby = 'orderbycourse';
    $sections = $DB->get_records('course_sections', array('course' => $COURSE->id), 'section', 'id,sequence');
    foreach ($sections as $section) {
        $section->sequence = explode(',', $section->sequence);
    }
    // Check each known module (described in lib.php).
    foreach ($modules as $module => $details) {
        $fields = 'id, name';
        if (array_key_exists('defaultTime', $details)) {
            $fields .= ', ' . $details['defaultTime'] . ' as due';
        }

        // Check if this type of module is used in the course, gather instance info.
        $records = $DB->get_records($module, array('course' => $id), '', $fields);
        foreach ($records as $record) {
            // Get the course module info.
            $coursemodule = get_coursemodule_from_instance($module, $record->id, $id);
            // Check if the module is visible, and if so, keep a record for it.
            if ($coursemodule->visible == 1) {
                $event = array(
                    'expected' => $expected,
                    'type' => $module,
                    'id' => $record->id,
                    'name' => format_string($record->name),
                    'cmid' => $coursemodule->id,
                );
                $event['section'] = $coursemodule->section;
                $event['position'] = array_search($coursemodule->id, $sections[$coursemodule->section]->sequence);
                $events[] = $event;
            }
        }
    }

    sort($events);
    return $events;
}

/**
 * @method  progress_attempts
 * @todo Checked if a user has attempted/viewed/etc. an activity/resource
 * @param array    $modules The modules used in the course
 * @param stdClass $id  The blocks configuration settings
 * @param array    $events  The possible events that can occur for modules
 * @param int      $userid  The user's id
 * @return array   an describing the user's attempts based on module+instance identifiers
 */
function progress_attempts($modules, $id, $events, $userid) {
    global $COURSE, $DB;
    $attempts = array();

    foreach ($events as $event) {
        $module = $modules[$event['type']];
        $default = $module['defaultAction'];
        $uniqueid = $event['type'] . $event['id'];

        $parameters = array('courseid' => $id, 'courseid1' => $id,
            'userid' => $userid, 'userid1' => $userid,
            'eventid' => $event['id'], 'eventid1' => $event['id'],
            'cmid' => $event['cmid'], 'cmid1' => $event['cmid'],
        );

        // Check for passing grades as unattempted, passed or failed
        if (isset($default) &&
                $default == 'passed'
        ) {
            $query = $module['actions'][$default];
            $graderesult = $DB->get_record_sql($query, $parameters);
            if (!$graderesult) {
                $attempts[$uniqueid] = false;
            } else {
                $attempts[$uniqueid] = $graderesult->finalgrade >= $graderesult->gradepass ? true : 'failed';
            }
        } else {
            // If activity completion is used, check completions table.
            if (isset($default) && $default == 'activity_completion') {
                $query = 'SELECT id
                                                  FROM {course_modules_completion}
                                                 WHERE userid = :userid
                                                   AND coursemoduleid = :cmid
                                                   AND completionstate = 1';
            } else { // Determine the set action and develop a query.
                $action = isset($default) ? $default : $module['defaultAction'];
                $query = $module['actions'][$action];
            }
            // Check if the user has attempted the module.
            $attempts[$uniqueid] = $DB->record_exists_sql($query, $parameters) ? true : false;
        }
    }
    return $attempts;
}

/**
 * @method  progress_bar
 * @todo Draws a progress bar
 * @param array    $modules  The modules used in the course
 * @param stdClass $config   The blocks configuration settings
 * @param array    $events   The possible events that can occur for modules
 * @param int      $userid   The user's id
 * @param int      instance  The block instance (incase more than one is being displayed)
 * @param array    $attempts The user's attempts on course activities
 * @param bool     $simple   Controls whether instructions are shown below a progress bar
 */
function progress_bar($modules, $events, $userid, $attempts) {

    global $OUTPUT, $CFG;
    $simple = true;
    $now = time();
    $numevents = count($events);
    $dateformat = get_string('date_format', 'local_dashboard');
    $tableoptions = array('class' => 'progressBarProgressTable',
        'cellpadding' => '0',
        'cellspacing' => '0');
    /* added for solution */
    $default = $modules['defaultAction'];
    $progressBarIcons = 1;
    $orderby = 'orderbytime';
    $showpercentage = 1;
    $displayNow = 1;
    // Place now arrow.
    if ((!isset($orderby) || $orderby == 'orderbytime') && $displayNow == 1 && !$simple) {
        $content = HTML_WRITER::start_tag('table', $tableoptions);
        // Find where to put now arrow.
        $nowpos = 0;
        while ($nowpos < $numevents && $now > $events[$nowpos]['expected']) {
            $nowpos++;
        }
        $content .= HTML_WRITER::start_tag('tr');
        $nowstring = get_string('now_indicator', 'local_dashboard');
        if ($nowpos < $numevents / 2) {
            for ($i = 0; $i < $nowpos; $i++) {
                $content .= HTML_WRITER::tag('td', '&nbsp;', array('class' => 'progressBarHeader'));
            }
            $celloptions = array('colspan' => $numevents - $nowpos,
                'class' => 'progressBarHeader',
                'style' => 'text-align:left;');
            $content .= HTML_WRITER::start_tag('td', $celloptions);
            $content .= $OUTPUT->pix_icon('left', $nowstring, 'local_dashboard');
            $content .= $nowstring;
            $content .= HTML_WRITER::end_tag('td');
        } else {
            $celloptions = array('colspan' => $nowpos,
                'class' => 'progressBarHeader',
                'style' => 'text-align:right;');
            $content .= HTML_WRITER::start_tag('td', $celloptions);
            $content .= $nowstring;
            $content .= $OUTPUT->pix_icon('right', $nowstring, 'local_dashboard');
            $content .= HTML_WRITER::end_tag('td');
            for ($i = $nowpos; $i < $numevents; $i++) {
                $content .= HTML_WRITER::tag('td', '&nbsp;', array('class' => 'progressBarHeader'));
            }
        }
        $content .= HTML_WRITER::end_tag('tr');
    } else {
        $tableoptions['class'] = 'progressBarProgressTable noNow';
        $content = HTML_WRITER::start_tag('table', $tableoptions);
    }
    // Start progress bar.
    $width = 100 / $numevents;
    $content .= HTML_WRITER::start_tag('tr');
    $counter = 1;

    foreach ($events as $event) {
        $attempted = $attempts[$event['type'] . $event['id']];
        $action = isset($default) ? $default : $modules[$event['type']]['defaultAction'];

        // A cell in the progress bar.
        $celloptions = array(
            'class' => 'progressBarCell',
            'id' => '',
            'width' => $width . '%',
            'onclick' => 'document.location=\'' . $CFG->wwwroot . '/mod/' . $event['type'] .
            '/view.php?id=' . $event['cmid'] . '\';',
//            'onmouseover' => 'M.local_dashboard.showInfo('.
//                '\''.$event['type'].'\', '.
//                '\''.addslashes(get_string($event['type'], 'local_dashboard')).'\', '.
//                '\''.$event['cmid'].'\', '.
//                '\''.addslashes($event['name']).'\', '.
//                '\''.addslashes(get_string($action, 'local_dashboard')).'\', '.
//                '\''.addslashes(userdate($event['expected'], $dateformat, $CFG->timezone)).'\', '.
//                '\''.$userid.'\', '.
//                '\''.($attempted === true ? 'tick' : 'cross').'\''.
//                ');',
            'style' => 'background-color:');
        if ($attempted === true) {
            $celloptions['style'] .= get_string('attempted_colour', 'local_dashboard') . ';';
//            $cellcontent = $OUTPUT->pix_icon(
//                               isset($progressBarIcons) && $progressBarIcons == 1 ?
//                               'tick' : 'blank', '', 'local_dashboard');
        } else if (((!isset($orderby) || $orderby == 'orderbytime') && $event['expected'] < $now) ||
                ($attempted === 'failed')) {
            $celloptions['style'] .= get_string('notAttempted_colour', 'local_dashboard') . ';';
//            $cellcontent = $OUTPUT->pix_icon(
//                               isset($progressBarIcons) && $progressBarIcons == 1 ?
//                               'cross':'blank', '', 'local_dashboard');
        } else {
            $celloptions['style'] .= get_string('futureNotAttempted_colour', 'local_dashboard') . ';';
            $cellcontent = $OUTPUT->pix_icon('blank', '', 'local_dashboard');
        }
        if ($counter == 1) {
            $celloptions['id'] .= 'first';
        }
        if ($counter == $numevents) {
            $celloptions['id'] .= 'last';
        }
        $counter++;
        $content .= HTML_WRITER::tag('td', $cellcontent, $celloptions);
    }
    $content .= HTML_WRITER::end_tag('tr');
    $content .= HTML_WRITER::end_tag('table');

    // Add the info box below the table.
    $divoptions = array('class' => 'progressEventInfo',
        'id' => 'progressBarInfouser' . $userid);
    $content .= HTML_WRITER::start_tag('div', $divoptions);
    if (!$simple) {
        if (isset($showpercentage) && $showpercentage == 1) {
            $progress = progress_percentage($events, $attempts);
            $content .= get_string('progress', 'local_dashboard') . ': ';
            $content .= $progress . '%' . HTML_WRITER::empty_tag('br');
        }
        $content .= get_string('mouse_over_prompt', 'local_dashboard');
    }
    $content .= HTML_WRITER::end_tag('div');

    return $content;
}

/**
 * @method progress_percentage
 * @todo Calculates an overall percentage of progress
 * @param array $events   The possible events that can occur for modules
 * @param array $attempts The user's attempts on course activities
 * @return int of progress value
 */
function progress_percentage($events, $attempts) {
    $attemptcount = 0;
    foreach ($events as $event) {
        if ($attempts[$event['type'] . $event['id']] == 1) {
            $attemptcount++;
        }
    }
    $progressvalue = $attemptcount == 0 ? 0 : $attemptcount / count($events);
    return (int) ($progressvalue * 100);
}

/**
 * @method table_completion
 * @todo Dispaly table structure
 * @param array $events   The possible events that can occur for modules
 * @param array $attempts The user's attempts on course activities
 */
function table_completion($events, $attempts, $id) {
    global $OUTPUT, $CFG, $DB;
    foreach ($events as $event) {
        $attempted = $attempts[$event['type'] . $event['id']];
        $likes = $DB->get_records_sql('select id from {local_like} where activityid=' . $event['cmid'] . ' and courseid=' . $id . '');
        $likes = count($likes);
        $comments = $DB->get_records_sql('select id from {local_comment} where activityid=' . $event['cmid'] . ' and courseid=' . $id . '');
        $comments = count($comments);
        // $rating = $DB->get_record_sql('select rating from {local_rating} where activityid='.$event['cmid'].' and courseid='.$id.'');
        $rating = $DB->get_record('local_rating', array('courseid' => $id, 'activityid' => $event['cmid']), 'AVG(rating) AS rating, COUNT(userid) AS count');
        if (empty($rating))
            $rating->rating = 0;
        else
            $rating->rating = round($rating->rating, 1) . '/5';
        $string [] = '<div class="list_bar">
        <span class="task_list"><a href="' . $CFG->wwwroot . '/mod/' . $event['type'] . '/view.php?id=' . $event['cmid'] . '" target="_blank">' . $OUTPUT->pix_icon('icon', '', $event['type'], array('class' => 'icon')) . addslashes($event['name']) . '</a><div class="ratings">' . $OUTPUT->pix_icon('like', '', 'local_dashboard') . $likes . $OUTPUT->pix_icon('star', '', 'local_dashboard') . $rating->rating . $OUTPUT->pix_icon('comment', '', 'local_dashboard') . $comments . '</div></span><span class="task_list_bar">' . ($attempted === true ? $OUTPUT->pix_icon('tick', '', 'local_dashboard') : $OUTPUT->pix_icon('cross', '', 'local_dashboard')) . '</span></div>';
    }
    return $string;
}

?>  