YUI.add('moodle-local_graduation-heirarchyselector', function(Y) {
    var ModulenameNAME = 'heirarchyselector';
    var heirarchyselector = function() {
        heirarchyselector.superclass.constructor.apply(this, arguments);
    };
    Y.extend(heirarchyselector, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		   
                var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
                var schoolselect = Y.one('#'+config.formid+' #id_schoolid');
                var programselect = Y.one('#'+config.formid+' #id_programid');
                
                
                
		
                if (schoolselect) {
		updatebut.setStyle('display','none');		
		       schoolselect.on('change', function() {
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
    M.local_graduation= M.local_graduation || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_graduation.init_heirarchyselector = function(config) { // 'config' contains the parameter values
        
        return new heirarchyselector(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });