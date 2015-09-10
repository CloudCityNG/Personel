YUI.add('moodle-local_modules-schoolchooser', function (Y) {
    var ModulenameNAME = 'schoolchooser';
    var schoolchooser = function () {
        schoolchooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(schoolchooser, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values
            if (config && config.formid) {
                var updatebut = Y.one('#' + config.formid + ' #id_updateschoolformat');
                var schoolselect = Y.one('#' + config.formid + ' #id_schoolid');
                updatebut.setStyle('display', 'none');
                if (schoolselect) {

                    schoolselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }

            }
        }
    });
    M.local_modules = M.local_modules || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_modules.init_schoolchooser = function (config) { // 'config' contains the parameter values
        return new schoolchooser(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});