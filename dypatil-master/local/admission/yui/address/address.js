YUI.add('moodle-local_admission-address', function(Y) {
    var ModulenameNAME = 'address';
    var address = function() {
        address.superclass.constructor.apply(this, arguments);
    };
    Y.extend(address, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
              var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
              var formatselect = Y.one('#'+config.formid+' #id_same');
	    	if (formatselect) {
		 updatebut.setStyle('display','none');
		       formatselect.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
		
            }
        }
    });
    M.local_admission = M.local_admission || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_admission.init_address = function(config) { // 'config' contains the parameter values
        
        return new address(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });