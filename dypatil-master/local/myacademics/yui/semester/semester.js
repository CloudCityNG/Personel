YUI.add('moodle-local_myacademics-semester', function (Y) {
    var ModulenameNAME = 'semester';
    var semester = function () {
        semester.superclass.constructor.apply(this, arguments);
    };
    Y.extend(semester, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var updatebut2 = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #id_programid');
                var formatselect2 = Y.one('#' + config.formid + ' #id_semesterid');
                //var formatselect3 = Y.one('#'+config.formid+' #id_sem');
                //var formatselect4 = Y.one('#'+config.formid+' #id_course');
                // /*var formatselect5 = Y.one('#'+config.formid+' #id_course');*/
                // var formatselect6 = Y.one('#'+config.formid+' #id_dept');

                if (formatselect) {
                    updatebut.setStyle('display', 'none');
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (formatselect2) {
                    updatebut2.setStyle('display', 'none');
                    formatselect2.on('change', function () {
                        updatebut2.simulate('click');
                    });
                }
            }
        }
    });
    M.local_myacademics = M.local_myacademics || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_myacademics.init_semester = function (config) { // 'config' contains the parameter values

        return new semester(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});