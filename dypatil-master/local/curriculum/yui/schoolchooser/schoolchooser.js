YUI.add('moodle-local_curriculum-schoolchooser', function (Y) {
    var ModulenameNAME = 'schoolchooser';
    var schoolchooser = function () {
        schoolchooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(schoolchooser, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #id_schoolid');
                updatebut.setStyle('display', 'none');
//                var updatepro = Y.one('#'+config.formid+' #id_updatecuformat');
//                var proselect = Y.one('#'+config.formid+' #id_programid');
                //for assign courses to plan-----------
                var radioselect1 = Y.one('#' + config.formid + ' #id_type_1');
                var radioselect2 = Y.one('#' + config.formid + ' #id_type_2');
                var programselect = Y.one('#' + config.formid + ' #id_pid');
                var moduleselect = Y.one('#' + config.formid + ' #id_moduleid');
                var deptselect = Y.one('#' + config.formid + ' #id_did');

                updatebut.setStyle('display', 'none');
                if (formatselect) {
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
//                if(proselect) {
//                     proselect.on('change', function() {
//                       updatepro.simulate('click');
//                    });
//                }
                //for assign courses to curriculum plan
                if (radioselect1) {
                    radioselect1.on('click', function () {
                        updatebut.simulate('click');
                    });
                }
                if (radioselect2) {
                    radioselect2.on('click', function () {
                        updatebut.simulate('click');
                    });
                }
                //assign courses to curriculumplan
                if (programselect) {
                    programselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (moduleselect) {
                    moduleselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (deptselect) {
                    deptselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }

            }
        }
    });
    M.local_curriculum = M.local_curriculum || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_curriculum.init_schoolchooser = function (config) { // 'config' contains the parameter values

        return new schoolchooser(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});