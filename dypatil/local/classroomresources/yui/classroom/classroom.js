YUI.add('moodle-local_classroomresources-classroom', function (Y) {    var ModulenameNAME = 'classroom';    var classroom = function () {        classroom.superclass.constructor.apply(this, arguments);    };    Y.extend(classroom, Y.Base, {        initializer: function (config) {            if (config && config.formid) {                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');                var formatselect = Y.one('#' + config.formid + ' #id_floorid');                if (formatselect) {                    updatebut.setStyle('display', 'none');                    formatselect.on('change', function () {                        updatebut.simulate('click');                    });                }            }        }    });    M.local_classroomresources = M.local_classroomresources || {};    M.local_classroomresources.init_classroom = function (config) {        return new classroom(config);    }}, '@VERSION@', {    requires: ['base', 'node', 'node-event-simulate']});