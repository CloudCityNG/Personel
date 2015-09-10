YUI.add('moodle-local_cobaltcourses-courses', function(Y) {
    var ModulenameNAME = 'courses';
    var courses = function() {
        courses.superclass.constructor.apply(this, arguments);
    };
    Y.extend(courses, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
                var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
                var formatselect1 = Y.one('#'+config.formid+' #id_schoolid');
		
                updatebut.setStyle('display','none');
                if (formatselect1) {
		       formatselect1.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
            }
        }
    });
    M.local_cobaltcourses = M.local_cobaltcourses || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_cobaltcourses.init_courses = function(config) { // 'config' contains the parameter values
        
        return new courses(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });