YUI.add('moodle-local_collegestructure-facultychooser', function (Y) {
    var ModulenameNAME = 'facultychooser';
    var facultychooser = function () {
        facultychooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(facultychooser, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values
            if (config && config.formid) {
                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #id_schoolid');
                if (formatselect) {
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
            }
        }
    });
    M.local_collegestructure = M.local_collegestructure || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_collegestructure.init_facultychooser = function (config) { // 'config' contains the parameter values
        return new facultychooser(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});