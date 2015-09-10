YUI.add('moodle-local_classroomresources-floor', function(Y) {
    var ModulenameNAME = 'floor';
    var floor = function() {
        floor.superclass.constructor.apply(this, arguments);
    };
    Y.extend(floor, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
              var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
              
	        var formatselect = Y.one('#'+config.formid+' #id_buildingid');
	    	
				
		if (formatselect) {
		 updatebut.setStyle('display','none');
		       formatselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		
            }
        }
    });
    M.local_classroomresources = M.local_classroomresources || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_classroomresources.init_floor = function(config) { // 'config' contains the parameter values
        
        return new floor(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });
