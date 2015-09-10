YUI.add('moodle-local_request-schoolchooser', function(Y) {
    var ModulenameNAME = 'schoolchooser';
    var schoolchooser = function() {
        schoolchooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(schoolchooser, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
                var updatebut = Y.one('#'+config.formid+' #id_chooseschool');
		var updatebut1 = Y.one('#'+config.formid+' #id_fieldset');
		 var formatselect4 = Y.one('#'+config.formid+' #id_field_select');
                var formatselect = Y.one('#'+config.formid+' #id_school_name');
		var formatselect2 = Y.one('#'+config.formid+' #id_program_name')
		var formatselect3 = Y.one('#'+config.formid+' #id_program_name1')
				updatebut.setStyle('display', 'none');
                if (formatselect) {
				updatebut.setStyle('display', 'none');
		       formatselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		        if (formatselect2) {
				updatebut.setStyle('display', 'none');
		       formatselect2.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		        if (formatselect3) {
				updatebut.setStyle('display', 'none');
		       formatselect3.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		        if (formatselect4) {
				updatebut1.setStyle('display', 'none');
		       formatselect4.on('change', function() {
                       updatebut1.simulate('click');
                    });
                }
                
            }
        }
    });
    M.local_request = M.local_request || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_request.init_schoolchooser = function(config) { // 'config' contains the parameter values
        
        return new schoolchooser(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });