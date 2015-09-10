YUI.add('moodle-local_cobaltcourses-equivalent', function(Y) {
    var ModulenameNAME = 'equivalent';
    var equivalent = function() {
        equivalent.superclass.constructor.apply(this, arguments);
    };
    Y.extend(equivalent, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
                var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
                var formatselect1 = Y.one('#'+config.formid+' #id_schoolid');
		var formatselect2 = Y.one('#'+config.formid+' #id_departmentid');
		var formatselect3 = Y.one('#'+config.formid+' #id_equivalentdeptid');
		var formatselect4 = Y.one('#'+config.formid+' #id_predeptid');
		var formatselect5 = Y.one('#'+config.formid+' #id_courseid');
		
                updatebut.setStyle('display','none');
                if (formatselect1) {
		       formatselect1.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		if (formatselect2) {
		       formatselect2.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		if (formatselect3) {
		       formatselect3.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		if (formatselect4) {
		       formatselect4.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		if (formatselect5) {
		       formatselect5.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
            }
        }
    });
    M.local_cobaltcourses = M.local_cobaltcourses || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_cobaltcourses.init_equivalent = function(config) { // 'config' contains the parameter values
        
        return new equivalent(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });