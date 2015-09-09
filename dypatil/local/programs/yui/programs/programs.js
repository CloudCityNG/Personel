YUI.add('moodle-local_programs-programs', function (Y) {
    var ModulenameNAME = 'programs';
    var programs = function () {
        programs.superclass.constructor.apply(this, arguments);
    };
    Y.extend(programs, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                //using this for enabling the credit hour settings for a program
                var updatebut = Y.one('#' + config.formid + ' #id_updatesettings');
                var formatselect1 = Y.one('#' + config.formid + ' #id_schoolid');
                var formatselect2 = Y.one('#' + config.formid + ' #id_programlevel');

                updatebut.setStyle('display', 'none');
                if (formatselect1) {
                    formatselect1.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (formatselect2) {
                    formatselect2.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
            }
        }
    });
    M.local_programs = M.local_programs || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_programs.init_programs = function (config) { // 'config' contains the parameter values

        return new programs(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});