YUI.add('moodle-local_admission-student', function(Y) {
    var ModulenameNAME = 'student';
    var student = function() {
        student.superclass.constructor.apply(this, arguments);
    };
    Y.extend(student, Y.Base, {
        initializer : function(config) { 
	
		  if (config && config.formid) {
		   
              var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
               
	        
	    	var formatselect = Y.one('#'+config.formid+' #id_typeofstudent');
				
		
		if (formatselect) {
		 updatebut.setStyle('display','none');
		       formatselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		
            }
        }
    });
    M.local_admission = M.local_admission || {};
    M.local_admission.init_student = function(config) {
        return new student(config);
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });