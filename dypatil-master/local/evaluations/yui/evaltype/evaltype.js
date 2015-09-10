YUI.add('moodle-local_evaluations-evaltype', function(Y) {
    var ModulenameNAME = 'evaltype';
    var evaltype = function() {
        evaltype.superclass.constructor.apply(this, arguments);
    };
    Y.extend(evaltype, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
              var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
	        var formatselect = Y.one('#'+config.formid+' #id_evaluationtype');
		var evalinst = Y.one('#'+config.formid+' #id_evaluatinginstructor');
	    
		if (formatselect) {
		      updatebut.setStyle('display', 'none');
		       formatselect.on('change', function() {
                       updatebut.simulate('click');
                    });
		}
		if (evalinst) {
		      updatebut.setStyle('display', 'none');
		       evalinst.on('change', function() {
                       updatebut.simulate('click');
                    });
		}
            }
        }
    });
    M.local_evaluations = M.local_evaluations || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_evaluations.init_evaltype = function(config) { // 'config' contains the parameter values
        
        return new evaltype(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });