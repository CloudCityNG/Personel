YUI.add('moodle-local_admission-program', function(Y) {
    var ModulenameNAME = 'program';
    var program = function() {
        program.superclass.constructor.apply(this, arguments);
    };
    Y.extend(program, Y.Base, {
        initializer : function(config) { 
	
		  if (config && config.formid) {
		   
              var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
               
	        var formatselect = Y.one('#'+config.formid+' #id_programid');
	    	var formatselect2 = Y.one('#'+config.formid+' #id_typeofstudent');
				
		if (formatselect) {
		 updatebut.setStyle('display','none');
		       formatselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		if (formatselect2) {
		 updatebut.setStyle('display','none');
		       formatselect2.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		
            }
        }
    });
    M.local_admission = M.local_admission || {};
    M.local_admission.init_program = function(config) {
        return new program(config);
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });