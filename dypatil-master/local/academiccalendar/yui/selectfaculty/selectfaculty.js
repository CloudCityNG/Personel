YUI.add('moodle-local_academiccalendar-selectfaculty', function (Y) {
    var ModulenameNAME = 'selectfaculty';
    var selectfaculty = function () {
        selectfaculty.superclass.constructor.apply(this, arguments);
    };
    Y.extend(selectfaculty, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {
                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #id_eventlevel');
                var formatselect2 = Y.one('#' + config.formid + ' #id_schoolid');
                var formatselect3 = Y.one('#' + config.formid + ' #id_eventtypeid');
                var semesterselect = Y.one('#' + config.formid + ' #id_semesterid');
                if (formatselect) {
                    var eventtitle = document.getElementById('id_eventlevel').value;
                }
                if (formatselect) {
                    updatebut.setStyle('display', 'none');
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (formatselect2 && eventtitle != 2) {
                    updatebut.setStyle('display', 'none');
                    formatselect2.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (formatselect3) {
                    updatebut.setStyle('display', 'none');
                    formatselect3.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (semesterselect) {
                    updatebut.setStyle('display', 'none');
                    semesterselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
            }
        }
    });
    M.local_academiccalendar = M.local_academiccalendar || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_academiccalendar.init_selectfaculty = function (config) { // 'config' contains the parameter values

        return new selectfaculty(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});