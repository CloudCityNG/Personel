<?php
//function to get data if loggedin user is registrar
function block_queries_getrole_user($courses, $rolename){
     global $CFG, $USER, $PAGE, $DB;
      /************Query to get data if loggedin user is registrar*************/
      $registrarlogin = array();
      foreach($courses as $course) {
        $sql="SELECT u.id, u.email, u.firstname, u.lastname
            FROM {context} AS cxt
            JOIN {role} AS role
            JOIN {role_assignments} AS ra
            ON cxt.id = ra.contextid 
            JOIN {user} AS u
            ON ra.userid = u.id
            WHERE cxt.instanceid = $course->id AND ra.roleid = role.id AND role.shortname = '$rolename' AND cxt.contextlevel = 50 AND u.id = $USER->id";
        $registrarrecord =  $DB->get_record_sql($sql);
        if($registrarrecord){
          $registrarlogin[] = $registrarrecord->id;
        }
      }
    return $registrarlogin;
}//end of function to get data if loggedin user is registrar

//function to diplay the data according to the user role 
function block_queries_display_view($USER,$instructorlogin,$registrarlogin,$studentlogin){
     global $CFG, $USER, $PAGE, $DB;
     $blockqueries_returncontent = array();
           $sql ="SELECT * FROM {block_queries} WHERE userid = $USER->id";
     if(is_siteadmin($USER->id)){
          $sql .=" AND userrole = 'admin' ORDER BY id DESC LIMIT 3";
          $blockqueries_returncontent[]=blockqueries_tablecontent($sql);
          $blockqueries_returncontent = implode('',$blockqueries_returncontent);
     }
     elseif(!empty($instructorlogin) ){
          $sql .=" AND userrole = 'instructor' ORDER BY id DESC LIMIT 3";
          $blockqueries_returncontent[]=blockqueries_tablecontent($sql);
          $blockqueries_returncontent = implode('',$blockqueries_returncontent);
     }
     elseif(!empty($registrarlogin)){
          $sql .=" AND userrole = 'registrar' ORDER BY id DESC LIMIT 3";
          $blockqueries_returncontent[]=blockqueries_tablecontent($sql);
          $blockqueries_returncontent = implode('',$blockqueries_returncontent);
     }
     elseif(!empty($studentlogin)){
          $formdata = new stdClass();
          $actionpage = $CFG->wwwroot.'/blocks/queries/sendingemail.php';
          $mform= new block_queries_form($actionpage);
          $blockqueries_returncontent[] = $mform->render();  //to display form in block
          $blockqueries_returncontent = implode('',$blockqueries_returncontent);
     }
     else {
          $blockqueries_returncontent[] = html_writer:: tag('p',get_string('noprevioussubjects','block_queries'),array());  
     }
     return $blockqueries_returncontent;
} // end of function 

function   blockqueries_tablecontent($sql){
     global $CFG, $USER, $PAGE, $DB;
     $blockqueries_displaycontent = array();
          $querieslists=$DB->get_records_sql($sql);
          
          $data = array();
            foreach($querieslists as $querieslist){
              $row = array();
              $adminqueryid = $querieslist->id;
              $row[] = html_writer:: tag('p',$querieslist->subject,array());
              $comment_image = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/t/message.svg',"id"=>"showDialog$adminqueryid",'title'=>get_string('addacomment','block_queries'),'onclick'=>"mycommentpopupform($adminqueryid)",'class'=>'queries_iconclass'));
              $comment_popup = commenthtmlform($adminqueryid);
              $row[] = $comment_image.$comment_popup;
              $data[] = $row;
            }
          
          $table = new html_table();
          $table->head  = array(get_string('subjectt','block_queries'),get_string('comment','block_queries'));
          $table->width = '100%';
          $table->size = array('95%','5%');
          $table->align = array('left','center');
          $table->data  = $data;
         
          $blockqueries_displaycontent[] = html_writer::table($table);
          $blockqueries_displaycontent[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
          $blockqueries_displaycontent = implode('',$blockqueries_displaycontent);
         
  return    $blockqueries_displaycontent;
}