YUI.add('moodle-local_gradeletter-gradeletterchooser', function(Y) {
    var ModulenameNAME = 'gradeletterchooser';
    var gradeletterchooser = function() {
        gradeletterchooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(gradeletterchooser, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
                var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
                var facultyselect = Y.one('#'+config.formid+' #id_schoolid');
                var programselect = Y.one('#'+config.formid+' #id_programid');
		
                if (facultyselect) {
		updatebut.setStyle('display','none');		
		       facultyselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
                 if (programselect) {
				
		       programselect.on('change', function() {
                       updatebut.simulate('click');
                    }); 
                }
            }
        }
    });
    M.local_gradeletter= M.local_gradeletter || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_gradeletter.init_gradeletterchooser = function(config) { // 'config' contains the parameter values
        
        return new gradeletterchooser(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });