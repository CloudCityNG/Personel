<?php

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/renderables.php');
require_once(dirname(__FILE__) . '/renderhelpers.php');

class local_attendance_renderer extends plugin_renderer_base {
    // External API - methods to render attendance renderable components.

    /**
     * Renders tabs for attendance
     *
     * @param atttabs - tabs to display
     * @return string html code
     */
    protected function render_local_attendance_tabs(local_attendance_tabs $atttabs) {
        return print_tabs($atttabs->get_tabs(), $atttabs->currenttab, null, null, true);
    }

    /**
     * Renders filter controls for attendance
     *
     * @param fcontrols - filter controls data to display
     * @return string html code
     */
    protected function render_local_attendance_filter_controls(local_attendance_filter_controls $fcontrols) {
        $filtertable = new html_table();
        $filtertable->attributes['class'] = ' ';
        $filtertable->width = '100%';
        $filtertable->align = array('left', 'center', 'right');



        $filtertable->data[0][] = $this->render_local_curdate_controls($fcontrols);

        $filtertable->data[0][] = $this->render_local_view_controls($fcontrols);

        $o = html_writer::table($filtertable);
        $o = $this->output->container($o, 'attfiltercontrols attwidth');

        return $o;
    }

    protected function render_local_curdate_controls(local_attendance_filter_controls $fcontrols) {
        global $CFG;

        $curdate_controls = '';
        if ($fcontrols->curdatetxt) {
            $this->page->requires->strings_for_js(array('calclose', 'caltoday'), 'local_attendance');
            $jsvals = array(
                'cal_months' => explode(',', get_string('calmonths', 'local_attendance')),
                'cal_week_days' => explode(',', get_string('calweekdays', 'local_attendance')),
                'cal_start_weekday' => $CFG->calendar_startwday,
                'cal_cur_date' => $fcontrols->curdate);
            $curdate_controls = html_writer::script(js_writer::set_variable('M.attendance', $jsvals));
            $this->page->requires->js('/local/attendance/calendar.js');
            $curdate_controls .= html_writer::link($fcontrols->url(array('curdate' => $fcontrols->prevcur)), $this->output->larrow());
            $params = array(
                'title' => get_string('calshow', 'local_attendance'),
                'id' => 'show',
                'type' => 'button');
            $button_form = html_writer::tag('button', $fcontrols->curdatetxt, $params);
            foreach ($fcontrols->url_params(array('curdate' => '')) as $name => $value) {
                $params = array(
                    'type' => 'hidden',
                    'id' => $name,
                    'name' => $name,
                    'value' => $value);
                $button_form .= html_writer::empty_tag('input', $params);
            }
            $params = array(
                'id' => 'currentdate',
                'action' => $fcontrols->url_path(),
                'method' => 'post'
            );

            $button_form = html_writer::tag('form', $button_form, $params);
            $curdate_controls .= $button_form;

            $curdate_controls .= html_writer::link($fcontrols->url(array('curdate' => $fcontrols->nextcur)), $this->output->rarrow());
        }

        return $curdate_controls;
    }

    protected function render_local_view_controls(local_attendance_filter_controls $fcontrols) {
        $views[LOCAL_ATT_VIEW_ALL] = get_string('all', 'local_attendance');
        $views[LOCAL_ATT_VIEW_ALLPAST] = get_string('allpast', 'local_attendance');
        $views[LOCAL_ATT_VIEW_MONTHS] = get_string('months', 'local_attendance');
        $views[LOCAL_ATT_VIEW_WEEKS] = get_string('weeks', 'local_attendance');
        $views[LOCAL_ATT_VIEW_DAYS] = get_string('days', 'local_attendance');
        $viewcontrols = '';
        foreach ($views as $key => $sview) {
            if ($key != $fcontrols->pageparams->view) {
                $link = html_writer::link($fcontrols->url(array('view' => $key)), $sview);
                $viewcontrols .= html_writer::tag('span', $link, array('class' => 'attbtn'));
            } else {
                $viewcontrols .= html_writer::tag('span', $sview, array('class' => 'attcurbtn'));
            }
        }

        return html_writer::tag('nobr', $viewcontrols);
    }

    /**
     * Renders attendance sessions managing table
     *
     * @param attendance_manage_data $sessdata to display
     * @return string html code
     */
    protected function render_local_attendance_manage_data(local_attendance_manage_data $sessdata) {
        $o = $this->render_local_sess_manage_table($sessdata) . $this->render_local_sess_manage_control($sessdata);
        $o = html_writer::tag('form', $o, array('method' => 'post', 'action' => $sessdata->url_sessions()->out()));
        $o = $this->output->container($o, 'generalbox attwidth');
        $o = $this->output->container($o, 'attsessions_manage_table');

        return $o;
    }

    protected function render_local_sess_manage_table(local_attendance_manage_data $sessdata) {
        global $DB;
        $this->page->requires->js_init_call('M.local_attendance.init_manage');

        $table = new html_table();
        $table->width = '100%';
        $table->head = array(
            '#',
            get_string('class', 'local_attendance'),
            //get_string('course'),
            //  get_string('sessiontypeshort', 'local_attendance'),
            get_string('date'),
            get_string('time'),
            get_string('actions'),
            html_writer::checkbox('cb_selector', 0, false, '', array('id' => 'cb_selector'))
        );
        $table->align = array('', '', '', '', '', 'center', 'center');
        $table->size = array('1px', '1px', '1px', '1px', '1px', '1px', '1px');
        $i = 0;
        //print_object($sessdata);

        foreach ($sessdata->sessions as $key => $sess) {
            $i++;
            $attendanceinfo = $DB->get_record('local_attendance', array('id' => $sess->attendanceid));
            $dta = $this->construct_date_time_actions($sessdata, $sess);

            $table->data[$sess->id][] = $i;
//            $table->data[$sess->id][] = $sess->groupid ? $sessdata->groups[$sess->groupid]->name :
//                                             (            get_string('commonsession', 'local_attendance');
            if ($attendanceinfo->classid)
                $classinfo = $DB->get_record('local_clclasses', array('id' => $attendanceinfo->classid));
            $table->data[$sess->id][] = $classinfo->fullname;
            //  $table->data[$sess->id][] = $k12hierarchy_info->course;
            $table->data[$sess->id][] = $dta['date'];
            $table->data[$sess->id][] = $dta['time'];
            $table->data[$sess->id][] = $dta['actions'];
            $table->data[$sess->id][] = html_writer::checkbox('sessid[]', $sess->id, false);
        }

        return html_writer::table($table);
    }

    private function construct_date_time_actions(local_attendance_manage_data $sessdata, $sess) {
        $actions = '';
        $date = userdate($sess->sessdate, get_string('strftimedmyw', 'local_attendance'));

        $time = $this->construct_time($sess->sessdate, $sess->duration);
        if ($sess->lasttaken > 0) {
            if ($sessdata->perm->can_change()) {
                $url = $sessdata->url_take($sess->id, $sess->groupid);
                $title = get_string('changeattendance', 'local_attendance');

                $date = html_writer::link($url, $date, array('title' => $title));
                $time = html_writer::link($url, $time, array('title' => $title));

                $actions = $this->output->action_icon($url, new pix_icon('redo', $title, 'local_attendance'));
            } else {
                $date = '<i>' . $date . '</i>';
                $time = '<i>' . $time . '</i>';
            }
        } else {
            if ($sessdata->perm->can_take()) {
                $url = $sessdata->url_take($sess->id, $sess->groupid);
                $title = get_string('takeattendance', 'local_attendance');
                $actions = $this->output->action_icon($url, new pix_icon('t/go', $title));
            }
        }
        if ($sessdata->perm->can_manage()) {
            $url = $sessdata->url_sessions($sess->id, local_att_sessions_page_params::ACTION_UPDATE);
            $title = get_string('editsession', 'local_attendance');
            $actions .= $this->output->action_icon($url, new pix_icon('t/edit', $title));

            $url = $sessdata->url_sessions($sess->id, local_att_sessions_page_params::ACTION_DELETE);
            $title = get_string('deletesession', 'local_attendance');
            $actions .= $this->output->action_icon($url, new pix_icon('t/delete', $title));
        }

        return array('date' => $date, 'time' => $time, 'actions' => $actions);
    }

    protected function render_local_sess_manage_control(local_attendance_manage_data $sessdata) {
        $table = new html_table();
        $table->attributes['class'] = ' ';
        $table->width = '100%';
        $table->align = array('left', 'right');

        $table->data[0][] = $this->output->help_icon('hiddensessions', 'local_attendance', get_string('hiddensessions', 'local_attendance') . ': ' . $sessdata->hiddensessionscount);

        if ($sessdata->perm->can_manage()) {
            $options = array(
                local_att_sessions_page_params::ACTION_DELETE_SELECTED => get_string('delete'),
                local_att_sessions_page_params::ACTION_CHANGE_DURATION => get_string('changeduration', 'local_attendance'));
            $controls = html_writer::select($options, 'action');
            $attributes = array(
                'type' => 'submit',
                'name' => 'ok',
                'value' => get_string('ok'));
            $controls .= html_writer::empty_tag('input', $attributes);
        } else {
            $controls = get_string('youcantdo', 'local_attendance'); // You can't do anything.
        }
        $table->data[0][] = $controls;

        return html_writer::table($table);
    }

    protected function render_local_attendance_take_data(local_attendance_take_data $takedata) {
        $controls = $this->render_local_attendance_take_controls($takedata);

        if ($takedata->pageparams->viewmode == local_att_take_page_params::SORTED_LIST) {
            $table = $this->render_local_attendance_take_list($takedata);
        } else {
            $table = $this->render_local_attendance_take_grid($takedata);
        }
        $table .= html_writer::input_hidden_params($takedata->url(array('sesskey' => sesskey())));
        $params = array(
            'type' => 'submit',
            'value' => get_string('save', 'local_attendance'));
        $table .= html_writer::tag('center', html_writer::empty_tag('input', $params));
        $table = html_writer::tag('form', $table, array('method' => 'post', 'action' => $takedata->url_path()));

        return $controls . $table;
    }

    protected function render_local_attendance_take_controls(local_attendance_take_data $takedata) {
        $table = new html_table();
        $table->attributes['class'] = ' ';

        $table->data[0][] = $this->construct_take_session_info($takedata);
        $table->data[0][] = $this->construct_take_controls($takedata);

        return $this->output->container(html_writer::table($table), 'generalbox takecontrols');
    }

    private function construct_take_session_info(local_attendance_take_data $takedata) {
        if (sizeof($takedata->sessioninfo) > 0) {
            $sess = $takedata->sessioninfo;
            $date = userdate($sess->sessdate, get_string('strftimedate'));
            $starttime = userdate($sess->sessdate, get_string('strftimehm', 'local_attendance'));
            $endtime = userdate($sess->sessdate + $sess->duration, get_string('strftimehm', 'local_attendance'));
            $time = html_writer::tag('nobr', $starttime . ($sess->duration > 0 ? ' - ' . $endtime : ''));
            $sessinfo = $date . ' ' . $time;
            $sessinfo .= html_writer::empty_tag('br');
            $sessinfo .= html_writer::empty_tag('br');
            //  $sessinfo .= $sess->description;

            return $sessinfo;
        } else
            return '';
    }

    private function construct_take_controls(local_attendance_take_data $takedata) {
        $controls = '';
        if ($takedata->pageparams->grouptype == local_attendance::SESSION_COMMON and ( $takedata->groupmode == VISIBLEGROUPS or ( $takedata->groupmode and $takedata->perm->can_access_all_groups()))) {
            $controls .= groups_print_activity_menu($takedata->cm, $takedata->url(), true);
        }

        $controls .= html_writer::empty_tag('br');

        $options = array(
            local_att_take_page_params::SORTED_LIST => get_string('sortedlist', 'local_attendance'),
            local_att_take_page_params::SORTED_GRID => get_string('sortedgrid', 'local_attendance'));
        $select = new single_select($takedata->url(), 'viewmode', $options, $takedata->pageparams->viewmode, null);
        $select->set_label(get_string('viewmode', 'local_attendance'));
        $select->class = 'singleselect inline';
        $controls .= $this->output->render($select);

        if ($takedata->pageparams->viewmode == local_att_take_page_params::SORTED_GRID) {
            $options = array(1 => '1 ' . get_string('column', 'local_attendance'), '2 ' . get_string('columns', 'local_attendance'),
                '3 ' . get_string('columns', 'local_attendance'), '4 ' . get_string('columns', 'local_attendance'),
                '5 ' . get_string('columns', 'local_attendance'), '6 ' . get_string('columns', 'local_attendance'),
                '7 ' . get_string('columns', 'local_attendance'), '8 ' . get_string('columns', 'local_attendance'),
                '9 ' . get_string('columns', 'local_attendance'), '10 ' . get_string('columns', 'local_attendance'));
            $select = new single_select($takedata->url(), 'gridcols', $options, $takedata->pageparams->gridcols, null);
            $select->class = 'singleselect inline';
            $controls .= $this->output->render($select);
        }

        if (count($takedata->sessions4copy) > 1) {
            $controls .= html_writer::empty_tag('br');
            $controls .= html_writer::empty_tag('br');

            $options = array();
            foreach ($takedata->sessions4copy as $sess) {
                $start = userdate($sess->sessdate, get_string('strftimehm', 'local_attendance'));
                $end = $sess->duration ? ' - ' . userdate($sess->sessdate + $sess->duration, get_string('strftimehm', 'local_attendance')) : '';
                $options[$sess->id] = $start . $end;
            }
            $select = new single_select($takedata->url(array(), array('copyfrom')), 'copyfrom', $options);
            $select->set_label(get_string('copyfrom', 'local_attendance'));
            $select->class = 'singleselect inline';
            $controls .= $this->output->render($select);
        }

        return $controls;
    }

    protected function render_local_attendance_take_list(local_attendance_take_data $takedata) {
        $table = new html_table();
        $table->width = '0%';
        $table->head = array(
            '#',
            $this->construct_fullname_head($takedata)
        );
        $table->align = array('left', 'left');
        $table->size = array('20px', '');
        $table->wrap[1] = 'nowrap';
        foreach ($takedata->statuses as $st) {
            $table->head[] = html_writer::link("javascript:select_all_in(null, 'st" . $st->id . "', null);", $st->acronym, array('title' => get_string('setallstatusesto', 'local_attendance', $st->description)));
            $table->align[] = 'center';
            $table->size[] = '20px';
        }
        $table->head[] = get_string('remarks', 'local_attendance');
        $table->align[] = 'center';
        $table->size[] = '20px';
        $table->attributes['class'] = 'generaltable takelist';

        $i = 0;
        foreach ($takedata->users as $user) {
            $i++;
            $row = new html_table_row();
            $row->cells[] = $i;
            $fullname = html_writer::link($takedata->url_view(array('studentid' => $user->id)), fullname($user));
            $fullname = $this->output->user_picture($user) . $fullname;

            $ucdata = $this->construct_take_user_controls($takedata, $user);
            if (array_key_exists('warning', $ucdata)) {
                $fullname .= html_writer::empty_tag('br');
                $fullname .= $ucdata['warning'];
            }
            $row->cells[] = $fullname;

            if (array_key_exists('colspan', $ucdata)) {
                $cell = new html_table_cell($ucdata['text']);
                $cell->colspan = $ucdata['colspan'];
                $row->cells[] = $cell;
            } else {
                $row->cells = array_merge($row->cells, $ucdata['text']);
            }

            if (array_key_exists('class', $ucdata)) {
                $row->attributes['class'] = $ucdata['class'];
            }

            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    protected function render_local_attendance_take_grid(local_attendance_take_data $takedata) {
        $table = new html_table();
        for ($i = 0; $i < $takedata->pageparams->gridcols; $i++) {
            $table->align[] = 'center';
            $table->size[] = '110px';
        }
        $table->attributes['class'] = 'generaltable takegrid';
        $table->headspan = $takedata->pageparams->gridcols;
        $head = array();
        foreach ($takedata->statuses as $st) {
            $head[] = html_writer::link("javascript:select_all_in(null, 'st" . $st->id . "', null);", $st->acronym, array('title' => get_string('setallstatusesto', 'local_attendance', $st->description)));
        }
        $table->head[] = implode('&nbsp;&nbsp;', $head);

        $i = 0;
        $row = new html_table_row();
        foreach ($takedata->users as $user) {
            $celltext = $this->output->user_picture($user, array('size' => 100));
            $celltext .= html_writer::empty_tag('br');
            $fullname = html_writer::link($takedata->url_view(array('studentid' => $user->id)), fullname($user));
            $celltext .= html_writer::tag('span', $fullname, array('class' => 'fullname'));
            $celltext .= html_writer::empty_tag('br');
            $ucdata = $this->construct_take_user_controls($takedata, $user);
            $celltext .= is_array($ucdata['text']) ? implode('', $ucdata['text']) : $ucdata['text'];
            if (array_key_exists('warning', $ucdata)) {
                $celltext .= html_writer::empty_tag('br');
                $celltext .= $ucdata['warning'];
            }

            $cell = new html_table_cell($celltext);
            if (array_key_exists('class', $ucdata)) {
                $cell->attributes['class'] = $ucdata['class'];
            }
            $row->cells[] = $cell;

            $i++;
            if ($i % $takedata->pageparams->gridcols == 0) {
                $table->data[] = $row;
                $row = new html_table_row();
            }
        }
        if ($i % $takedata->pageparams->gridcols > 0) {
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    private function construct_fullname_head($data) {
        global $CFG;

        if ($data->pageparams->sort == LOCAL_ATT_SORT_LASTNAME) {
            $firstname = html_writer::link($data->url(array('sort' => LOCAL_ATT_SORT_FIRSTNAME)), get_string('firstname'));
        } else {
            $firstname = get_string('firstname');
        }

        if ($data->pageparams->sort == LOCAL_ATT_SORT_FIRSTNAME) {
            $lastname = html_writer::link($data->url(array('sort' => LOCAL_ATT_SORT_LASTNAME)), get_string('lastname'));
        } else {
            $lastname = get_string('lastname');
        }

        if ($CFG->fullnamedisplay == 'lastname firstname') {
            $fullnamehead = "$lastname / $firstname";
        } else {
            $fullnamehead = "$firstname / $lastname";
        }

        return $fullnamehead;
    }

    private function construct_take_user_controls(local_attendance_take_data $takedata, $user) {

        $celldata = array();
        if ($user->enrolmentend and $user->enrolmentend < $takedata->sessioninfo->sessdate) {
            $celldata['text'] = get_string('enrolmentend', 'local_attendance', userdate($user->enrolmentend, '%d.%m.%Y'));
            $celldata['colspan'] = count($takedata->statuses) + 1;
            $celldata['class'] = 'userwithoutenrol';
        } else if (!$user->enrolmentend and $user->enrolmentstatus == ENROL_USER_SUSPENDED) {
            // No enrolmentend and ENROL_USER_SUSPENDED.
            $celldata['text'] = get_string('enrolmentsuspended', 'local_attendance');
            $celldata['colspan'] = count($takedata->statuses) + 1;
            $celldata['class'] = 'userwithoutenrol';
        } else {
            if ($takedata->updatemode and ! array_key_exists($user->id, $takedata->sessionlog)) {
                $celldata['class'] = 'userwithoutdata';
            }

            $celldata['text'] = array();
            foreach ($takedata->statuses as $st) {
                $params = array(
                    'type' => 'radio',
                    'name' => 'user' . $user->id,
                    'class' => 'st' . $st->id,
                    'value' => $st->id);
                if (array_key_exists($user->id, $takedata->sessionlog) and $st->id == $takedata->sessionlog[$user->id]->statusid) {
                    $params['checked'] = '';
                }

                $input = html_writer::empty_tag('input', $params);

                if ($takedata->pageparams->viewmode == local_att_take_page_params::SORTED_GRID) {
                    $input = html_writer::tag('nobr', $input . $st->acronym);
                }

                $celldata['text'][] = $input;
            }
            $params = array(
                'type' => 'text',
                'name' => 'remarks' . $user->id);
            if (array_key_exists($user->id, $takedata->sessionlog)) {
                $params['value'] = $takedata->sessionlog[$user->id]->remarks;
            }
            $celldata['text'][] = html_writer::empty_tag('input', $params);

            if ($user->enrolmentstart > $takedata->sessioninfo->sessdate + $takedata->sessioninfo->duration) {
                $celldata['warning'] = get_string('enrolmentstart', 'attendance', userdate($user->enrolmentstart, '%H:%M %d.%m.%Y'));
                $celldata['class'] = 'userwithoutenrol';
            }
        }

        return $celldata;
    }

    protected function render_local_attendance_user_data(local_attendance_user_data $userdata) {
        $o = $this->render_user_report_tabs($userdata);

        $table = new html_table();

        $table->attributes['class'] = 'userinfobox';
        $table->colclclasses = array('left side', '');
        $table->data[0][] = $this->output->user_picture($userdata->user, array('size' => 100));
        $table->data[0][] = $this->construct_user_data($userdata);

        $o .= html_writer::table($table);

        return $o;
    }

    protected function render_user_report_tabs(attendance_user_data $userdata) {
        $tabs = array();

        $tabs[] = new tabobject(att_view_page_params::MODE_THIS_COURSE, $userdata->url()->out(true, array('mode' => att_view_page_params::MODE_THIS_COURSE)), get_string('thiscourse', 'local_attendance'));

        $tabs[] = new tabobject(att_view_page_params::MODE_ALL_COURSES, $userdata->url()->out(true, array('mode' => att_view_page_params::MODE_ALL_COURSES)), get_string('allcourses', 'local_attendance'));

        return print_tabs(array($tabs), $userdata->pageparams->mode, null, null, true);
    }

    private function construct_user_data(attendance_user_data $userdata) {
        $o = html_writer::tag('h2', fullname($userdata->user));

        if ($userdata->pageparams->mode == att_view_page_params::MODE_THIS_COURSE) {
            $o .= html_writer::empty_tag('hr');

            $o .= construct_user_data_stat($userdata->stat, $userdata->statuses, $userdata->gradable, $userdata->grade, $userdata->maxgrade, $userdata->decimalpoints);

            $o .= $this->render_attendance_filter_controls($userdata->filtercontrols);

            $o .= $this->construct_user_sessions_log($userdata);
        } else {
            $prevcid = 0;
            foreach ($userdata->coursesatts as $ca) {
                if ($prevcid != $ca->courseid) {
                    $o .= html_writer::empty_tag('hr');
                    $prevcid = $ca->courseid;

                    $o .= html_writer::tag('h3', $ca->coursefullname);
                }
                $o .= html_writer::tag('h4', $ca->attname);

                $o .= construct_user_data_stat($userdata->stat[$ca->attid], $userdata->statuses[$ca->attid], $userdata->gradable[$ca->attid], $userdata->grade[$ca->attid], $userdata->maxgrade[$ca->attid], $userdata->decimalpoints);
            }
        }

        return $o;
    }

    private function construct_user_sessions_log(attendance_user_data $userdata) {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable attwidth boxaligncenter';
        $table->head = array(
            '#',
            get_string('sessiontypeshort', 'local_attendance'),
            get_string('date'),
            get_string('time'),
            get_string('description', 'local_attendance'),
            get_string('status', 'local_attendance'),
            get_string('remarks', 'local_attendance')
        );
        $table->align = array('', '', '', 'left', 'left', 'center', 'left');
        $table->size = array('1px', '1px', '1px', '1px', '*', '1px', '1px');

        $i = 0;
        foreach ($userdata->sessionslog as $sess) {
            $i++;

            $row = new html_table_row();
            $row->cells[] = $i;
            $row->cells[] = html_writer::tag('nobr', $sess->groupid ? $userdata->groups[$sess->groupid]->name :
                                    get_string('commonsession', 'local_attendance'));
            $row->cells[] = userdate($sess->sessdate, get_string('strftimedmyw', 'local_attendance'));
            $row->cells[] = $this->construct_time($sess->sessdate, $sess->duration);
            $row->cells[] = $sess->description;
            if (isset($sess->statusid)) {
                $row->cells[] = $userdata->statuses[$sess->statusid]->description;
                $row->cells[] = $sess->remarks;
            } else if ($sess->sessdate < $userdata->user->enrolmentstart) {
                $cell = new html_table_cell(get_string('enrolmentstart', 'local_attendance', userdate($userdata->user->enrolmentstart, '%d.%m.%Y')));
                $cell->colspan = 2;
                $row->cells[] = $cell;
            } else if ($userdata->user->enrolmentend and $sess->sessdate > $userdata->user->enrolmentend) {
                $cell = new html_table_cell(get_string('enrolmentend', 'local_attendance', userdate($userdata->user->enrolmentend, '%d.%m.%Y')));
                $cell->colspan = 2;
                $row->cells[] = $cell;
            } else {
                $row->cells[] = '?';
                $row->cells[] = '';
            }

            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    private function construct_time($datetime, $duration) {
        $time = html_writer::tag('nobr', construct_local_session_time($datetime, $duration));

        return $time;
    }

    protected function render_local_attendance_report_data(local_attendance_report_data $reportdata) {

        // Initilise Javascript used to (un)check all checkboxes.
        $this->page->requires->js_init_call('M.local_attendance.init_manage');

        $systemcontext = context_system::instance();
        // Check if the user should be able to bulk send messages to other users on the course.
        $bulkmessagecapability = has_capability('local/attendance:manageattendances', $systemcontext);

        $table = new html_table();

        $table->attributes['class'] = 'generaltable attwidth';

        // User picture.
        $table->head[] = '';
        $table->align[] = 'left';
        $table->size[] = '1px';

        $table->head[] = $this->construct_fullname_head($reportdata);
        $table->align[] = 'left';
        $table->size[] = '';
        $sessionstats = array();

        foreach ($reportdata->sessions as $sess) {
            $sesstext = userdate($sess->sessdate, get_string('strftimedm', 'local_attendance'));
            $sesstext .= html_writer::empty_tag('br');
            $sesstext .= userdate($sess->sessdate, '(' . get_string('strftimehm', 'local_attendance') . ')');
            if (is_null($sess->lasttaken) and $reportdata->perm->can_take() or $reportdata->perm->can_change()) {
                $sesstext = html_writer::link($reportdata->url_take($sess->id, $sess->groupid), $sesstext);
            }
            $sesstext .= html_writer::empty_tag('br');
            $sesstext .= $sess->groupid ? $reportdata->groups[$sess->groupid]->name : get_string('commonsession', 'local_attendance');

            $table->head[] = $sesstext;
            $table->align[] = 'center';
            $table->size[] = '1px';
        }

        foreach ($reportdata->statuses as $status) {
            $table->head[] = $status->acronym;
            $table->align[] = 'center';
            $table->size[] = '1px';
            $sessionstats[$status->id] = 0;
        }

        if ($reportdata->gradable) {
            $table->head[] = get_string('grade');
            $table->align[] = 'center';
            $table->size[] = '1px';
        }

        if ($reportdata->sessionslog) {
            $table->head[] = get_string('remarks', 'local_attendance');
            $table->align[] = 'center';
            $table->size[] = '200px';
        }

        if ($bulkmessagecapability) { // Display the table header for bulk messaging.
            // The checkbox must have an id of cb_selector so that the JavaScript will pick it up.
            $table->head[] = html_writer::checkbox('cb_selector', 0, false, '', array('id' => 'cb_selector'));
            $table->align[] = 'center';
            $table->size[] = '1px';
        }
        // $sessionstats = array();
        //print_object($reportdata->statuses);
        foreach ($reportdata->users as $user) {
            $row = new html_table_row();

            $row->cells[] = $this->output->user_picture($user);
            $row->cells[] = html_writer::link($reportdata->url_view(array('studentid' => $user->id)), fullname($user));
            $cellsgenerator = new local_user_sessions_cells_html_generator($reportdata, $user);
            $row->cells = array_merge($row->cells, $cellsgenerator->get_cells());

            foreach ($reportdata->statuses as $status) {
                if (array_key_exists($status->id, $reportdata->usersstats[$user->id])) {
                    $row->cells[] = $reportdata->usersstats[$user->id][$status->id]->stcnt;
                } else {
                    // No attendance data for this $status => no statistic for this status.
                    $row->cells[] = 0;
                }
            }

            if ($reportdata->gradable) {
                $row->cells[] = $reportdata->grades[$user->id] . ' / ' . $reportdata->maxgrades[$user->id];
            }

            if ($reportdata->sessionslog) {
                if (isset($sess) && isset($reportdata->sessionslog[$user->id][$sess->id]->remarks)) {
                    $row->cells[] = $reportdata->sessionslog[$user->id][$sess->id]->remarks;
                } else {
                    $row->cells[] = '';
                }
            }

            if ($bulkmessagecapability) { // Create the checkbox for bulk messaging.
                $row->cells[] = html_writer::checkbox('user' . $user->id, 'on', false);
            }

            $table->data[] = $row;
        }

        // Calculate the sum of statuses for each user
        $statrow = new html_table_row();
        $statrow->cells[] = '';
        $statrow->cells[] = get_string('summary');
        foreach ($reportdata->sessions as $sess) {
            $sessionstats = array();
            foreach ($reportdata->users as $user) {
                foreach ($reportdata->statuses as $status) {
                    // for next attendance session initialize summary status to zero
                    if (empty($sessionstats))
                        $sessionstats[$status->id] = 0;
                    if (!empty($reportdata->sessionslog[$user->id][$sess->id])) {
                        if ($reportdata->sessionslog[$user->id][$sess->id]->statusid == $status->id)
                            $sessionstats[$status->id] ++;
                    }
                }
            }

            $statsoutput = '<br/>';
            foreach ($reportdata->statuses as $status) {
                if (empty($sessionstats[$status->id]))
                    $sessionstats[$status->id] = 0;
                $statsoutput .= "$status->description:" . $sessionstats[$status->id] . " <br/>";
            }
            $statrow->cells[] = $statsoutput;
        }
        $table->data[] = $statrow;

        if ($bulkmessagecapability) { // Require that the user can bulk message users.
            // Display check boxes that will allow the user to send a message to the students that have been checked.
            $output = html_writer::empty_tag('input', array('name' => 'sesskey', 'type' => 'hidden', 'value' => sesskey()));
            $output .= html_writer::empty_tag('input', array('name' => 'formaction', 'type' => 'hidden', 'value' => 'messageselect.php'));
            $output .= html_writer::empty_tag('input', array('name' => 'id', 'type' => 'hidden', 'value' => $GLOBALS['COURSE']->id));
            $output .= html_writer::empty_tag('input', array('name' => 'returnto', 'type' => 'hidden', 'value' => s(me())));
            $output .= html_writer::table($table) . html_writer::tag('div', get_string('users') . ': ' . count($reportdata->users));
            ;
            $output .= html_writer::tag('div', html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('messageselectadd'))), array('class' => 'buttons'));
            $url = new moodle_url('/user/action_redir.php');
            return html_writer::tag('form', $output, array('action' => $url->out(), 'method' => 'post'));
        } else {
            return html_writer::table($table) . html_writer::tag('div', get_string('users') . ': ' . count($reportdata->users));
        }
    }

    protected function render_local_attendance_preferences_data(local_attendance_preferences_data $prefdata) {
        $this->page->requires->js('/local/attendance/module.js');

        $table = new html_table();
        $table->width = '100%';
        $table->head = array('#',
            get_string('acronym', 'local_attendance'),
            get_string('description'),
            get_string('grade'),
            get_string('action'));
        $table->align = array('center', 'center', 'center', 'center', 'center', 'center');

        $i = 1;
        foreach ($prefdata->statuses as $st) {
            $table->data[$i][] = $i;
            $table->data[$i][] = $this->construct_text_input('acronym[' . $st->id . ']', 2, 2, $st->acronym);
            $table->data[$i][] = $this->construct_text_input('description[' . $st->id . ']', 30, 30, $st->description);
            $table->data[$i][] = $this->construct_text_input('grade[' . $st->id . ']', 4, 4, $st->grade);
            $table->data[$i][] = $this->construct_preferences_actions_icons($st, $prefdata);

            $i++;
        }

        $table->data[$i][] = '*';
        $table->data[$i][] = $this->construct_text_input('newacronym', 2, 2);
        $table->data[$i][] = $this->construct_text_input('newdescription', 30, 30);
        $table->data[$i][] = $this->construct_text_input('newgrade', 4, 4);
        $table->data[$i][] = $this->construct_preferences_button(get_string('add', 'local_attendance'), local_att_preferences_page_params::ACTION_ADD);

        $o = html_writer::tag('h1', get_string('myvariables', 'local_attendance'));
        $o .= html_writer::table($table);
        $o .= html_writer::input_hidden_params($prefdata->url(array(), false));
        $o .= $this->construct_preferences_button(get_string('update', 'local_attendance'), local_att_preferences_page_params::ACTION_SAVE);
        $o = html_writer::tag('form', $o, array('id' => 'preferencesform', 'method' => 'post',
                    'action' => $prefdata->url(array(), false)->out_omit_querystring()));
        $o = $this->output->container($o, 'generalbox attwidth');

        return $o;
    }

    private function construct_text_input($name, $size, $maxlength, $value = '') {
        $attributes = array(
            'type' => 'text',
            'name' => $name,
            'size' => $size,
            'maxlength' => $maxlength,
            'value' => $value);
        return html_writer::empty_tag('input', $attributes);
    }

    private function construct_preferences_actions_icons($st, $prefdata) {
        global $OUTPUT;

        if ($st->visible) {
            $params = array(
                'action' => local_att_preferences_page_params::ACTION_HIDE,
                'statusid' => $st->id);
            $showhideicon = $OUTPUT->action_icon(
                    $prefdata->url($params), new pix_icon("t/hide", get_string('hide')));
        } else {
            $params = array(
                'action' => local_att_preferences_page_params::ACTION_SHOW,
                'statusid' => $st->id);
            $showhideicon = $OUTPUT->action_icon(
                    $prefdata->url($params), new pix_icon("t/show", get_string('show')));
        }
        if (!$st->haslogs) {
            $params = array(
                'action' => local_att_preferences_page_params::ACTION_DELETE,
                'statusid' => $st->id);
            $deleteicon = $OUTPUT->action_icon(
                    $prefdata->url($params), new pix_icon("t/delete", get_string('delete')));
        } else {
            $deleteicon = '';
        }

        return $showhideicon . $deleteicon;
    }

    private function construct_preferences_button($text, $action) {
        $attributes = array(
            'type' => 'submit',
            'value' => $text,
            'onclick' => 'M.local_attendance.set_preferences_action(' . $action . ')');
        return html_writer::empty_tag('input', $attributes);
    }

}
