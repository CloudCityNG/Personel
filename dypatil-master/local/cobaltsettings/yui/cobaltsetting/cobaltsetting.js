YUI.add('moodle-local_cobaltsettings-cobaltsetting', function (Y) {
    var ModulenameNAME = 'cobaltsetting';
    var cobaltsetting = function () {
        cobaltsetting.superclass.constructor.apply(this, arguments);
    };
    Y.extend(cobaltsetting, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {
                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #id_entityid');
                if (formatselect) {
                    //  alert('hi');
                    updatebut.setStyle('display', 'none');
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }



            }
        }
    });
    M.local_cobaltsettings = M.local_cobaltsettings || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_cobaltsettings.init_cobaltsetting = function (config) { // 'config' contains the parameter values

        return new cobaltsetting(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});