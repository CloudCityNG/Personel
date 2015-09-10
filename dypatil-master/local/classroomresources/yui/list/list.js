YUI.add('moodle-local_classroomresources-list', function (Y) {
    var ModulenameNAME = 'list';
    var list = function () {
        list.superclass.constructor.apply(this, arguments);
    };
    Y.extend(list, Y.Base, {
        initializer: function (config) {

            if (config && config.formid) {
                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #fitem_id_startdate');
                var formatselect1 = Y.one('#' + config.formid + ' #fitem_id_enddate');
                var formatselect2 = Y.one('#' + config.formid + ' #fgroup_id_starttime');
                var formatselect3 = Y.one('#' + config.formid + ' #fgroup_id_endtime');

                if (formatselect) {
                    updatebut.setStyle('display', 'none');
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (formatselect1) {
                    updatebut.setStyle('display', 'none');
                    formatselect1.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (formatselect2) {
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
            }
        }
    });
    M.local_classroomresources = M.local_classroomresources || {};
    M.local_classroomresources.init_list = function (config) {

        return new list(config);
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});