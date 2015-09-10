YUI.add('moodle-local_classroomresources-building', function (Y) {
    var ModulenameNAME = 'building';
    var building = function () {
        building.superclass.constructor.apply(this, arguments);
    };
    Y.extend(building, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');

                var formatselect = Y.one('#' + config.formid + ' #id_schoolid');


                if (formatselect) {
                    updatebut.setStyle('display', 'none');
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }

            }
        }
    });
    M.local_classroomresources = M.local_classroomresources || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_classroomresources.init_building = function (config) { // 'config' contains the parameter values

        return new building(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});