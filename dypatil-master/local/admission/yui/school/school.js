YUI.add('moodle-local_admission-school', function(Y) {
    var ModulenameNAME = 'school';
    var school = function() {
        school.superclass.constructor.apply(this, arguments);
    };
    Y.extend(school, Y.Base, {
        initializer : function(config) { 
	
		  if (config && config.formid) {
		   
              var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
               
	        var formatselect = Y.one('#'+config.formid+' #id_typeofprogram');
	    	
				
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
    M.local_admission.init_school = function(config) {
        return new school(config);
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });