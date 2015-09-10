YUI.add('moodle-local_scheduleexam-scheduleexamchooser', function(Y) {
    var ModulenameNAME = 'scheduleexamchooser';
    var scheduleexamchooser = function() {
        scheduleexamchooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(scheduleexamchooser, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
                var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
                var schoolselect = Y.one('#'+config.formid+' #id_schoolid');
                var semesterselect = Y.one('#'+config.formid+' #id_semesterid');
                var classselect = Y.one('#'+config.formid+' #id_classid');
                
                if (schoolselect) {
		updatebut.setStyle('display','none');		
		       schoolselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
                 if (semesterselect) {
				
		       semesterselect.on('change', function() {
                       updatebut.simulate('click');
                    }); 
                }
                
                 if (classselect) {
				
		       classselect.on('change', function() {
                       updatebut.simulate('click');
                    }); 
                }
                                
               
            }
        }
    });
    M.local_scheduleexam= M.local_scheduleexam || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_scheduleexam.init_scheduleexamchooser = function(config) { // 'config' contains the parameter values
        
        return new scheduleexamchooser(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });