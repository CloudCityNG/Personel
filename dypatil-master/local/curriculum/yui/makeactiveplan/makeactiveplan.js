YUI.add('moodle-local_curriculum-makeactiveplan', function (Y) {
    var ModulenameNAME = 'makeactiveplan';
    var makeactiveplan = function () {
        makeactiveplan.superclass.constructor.apply(this, arguments);
    };
    Y.extend(makeactiveplan, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var schoolselect = Y.one('#' + config.formid + ' #id_schoolid');
                var programselect = Y.one('#' + config.formid + ' #id_programid');
                var batchselect = Y.one('#' + config.formid + ' #id_batchid');
                var semselect = Y.one('#' + config.formid + ' #id_semesterid');
                updatebut.setStyle('display', 'none');

                updatebut.setStyle('display', 'none');
                if (schoolselect) {
                    schoolselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (programselect) {
                    programselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (batchselect) {
                    batchselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                //if (semselect) {
                //    semselect.on('change', function () {
                //        updatebut.simulate('click');
                //    });
                //}
            }
        }
    });
    M.local_curriculum = M.local_curriculum || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_curriculum.init_makeactiveplan = function (config) { // 'config' contains the parameter values

        return new makeactiveplan(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});