YUI.add('moodle-local_batches-batches', function (Y) {
    var ModulenameNAME = 'batches';
    var batches = function () {
        batches.superclass.constructor.apply(this, arguments);
    };
    Y.extend(batches, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect1 = Y.one('#' + config.formid + ' #id_schoolid');
                var formatselect2 = Y.one('#' + config.formid + ' #id_programid');
                var formatprogram = Y.one('#' + config.formid + ' #id_program');
                var formatcurriculum = Y.one('#' + config.formid + ' #id_curriculumid');
                
                updatebut.setStyle('display', 'none');
                if (formatselect1) {
                    formatselect1.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                
                
                if (formatprogram) {
                    formatprogram.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                
                 if (formatcurriculum) {
                    formatcurriculum.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                
                
                if (formatselect2) {
                    formatselect2.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                
                //if (formatselect3) {
                //    formatselect3.on('change', function () {
                //        updatebut.simulate('click');
                //    });
                //}
            }
        }
    });
    M.local_batches = M.local_batches || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_batches.init_batches = function (config) { // 'config' contains the parameter values

        return new batches(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});