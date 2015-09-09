<?php

$myprogram = programs::getInstance();

//$hierarchy = new hierarchy();
class program_files_renderer {
    /*
     * @function dependency_viewer
     * @todo Displays the List of Dependencies on Program in the Table
     *  @param  $list(array)
     * */

    public function dependency_viewer($list, $param) {
        global $CFG;
        $table = new html_table();
        $table->align = array('left');
        $table->width = '100%';
        $table->data = array();
        foreach ($list as $li) {
            switch ($param) {
                case 1: $link = html_writer::tag('a', $li->fullname, array('href' => '' . $CFG->wwwroot . '/local/curriculum/viewcurriculum.php?id=' . $li->id . ''));
                    $head = get_string('curriculum', 'local_curriculum');
                    break;
                case 2: $link = html_writer::tag('a', $li->name, array('href' => '' . $CFG->wwwroot . '/local/batches/view.php?id=' . $li->id . ''));
                    $head = get_string('batchname', 'local_programs');
                    break;
                case 3: $link = html_writer::tag('a', $li->fullname, array('href' => '' . $CFG->wwwroot . '/local/modules/view.php?id=' . $li->id . ''));
                    $head = get_string('modulename', 'local_programs');
                    break;
                default : break;
            }
            $table->data[] = array($link);
        }
        $table->head = array($head);

        //$html = '<div style="float:left;width:30%;padding-right:3%;">';
        $html = html_writer::table($table);
        //$html .= '</div>';
        return $html;
    }

    /*
     * @function program_viewer
     * @todo Displays the Program details in the Table
     *  @param  $list(array)
     * */

    function program_viewer($list) {
        global $myprogram;
        $table = new html_table();
        $table->align = array('left', 'left', 'left', 'left');
        $table->size = array('15%', '35%', '15%', '35%');
        $table->width = '100%';

        $cell = new html_table_cell();
        $cell->text = $list->description;
        $cell->colspan = 3;

        $name = $myprogram->name($list);
        $table->data[] = array('<b>Short Name</b>', $list->shortname, '<b>Full Name</b>', $list->fullname);
        $table->data[] = array('<b>Organization</b>', $name->school, '<b>Duration</b>', $list->duration . ' months');
        $table->data[] = array('<b>Type</b>', $name->type, '<b>Level</b>', $name->level);
        $table->data[] = array('<b>Description</b>', $cell);
        return html_writer::table($table);
    }

}

?>