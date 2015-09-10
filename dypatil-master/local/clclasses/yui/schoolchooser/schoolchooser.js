YUI.add('moodle-local_clclasses-schoolchooser', function(Y) {
    var ModulenameNAME = 'schoolchooser';
    var schoolchooser = function() {
        schoolchooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(schoolchooser, Y.Base, {
        initializer: function(config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var updatedept = Y.one('#' + config.formid + ' #id_updatedepartment');
                var formatselect = Y.one('#' + config.formid + ' #id_schoolid');
                var departmentselect = Y.one('#' + config.formid + ' #id_departmentid');
                var online = Y.one('#' + config.formid + ' #id_updatecourseid');
                var onlineselect = Y.one('#' + config.formid + ' #id_online');

                var displayinstructor = Y.one('#' + config.formid + ' #id_departmentinid');
                var updateinstructor = Y.one('#' + config.formid + ' #id_updateinstructor');

                //for scheduling date
                var updatebutclass = Y.one('#' + config.formid + ' #id_updateclassrooms');
                var formatselect4 = Y.one('#' + config.formid + ' #fitem_id_choose');



                //end of scheduling date
                updatebut.setStyle('display', 'none');
                updatedept.setStyle('display', 'none');
                online.setStyle('display', 'none');
                updateinstructor.setStyle('display', 'none');
                updatebutclass.setStyle('display', 'none');
                if (formatselect) {
                    formatselect.on('change', function() {
                        updatebut.simulate('click');

                    });
                }

                if (departmentselect) {
                    departmentselect.on('change', function() {
                        updatedept.simulate('click');
                    });

                }

                if (onlineselect) {
                    onlineselect.on('change', function() {
                        online.simulate('click');
                    });
                }

                if (displayinstructor) {
                    displayinstructor.on('change', function() {
                        updateinstructor.simulate('click');
                    });

                }

                /*for scheduling class */

                if (formatselect4) {
                    formatselect4.on('change', function() {
                        updatebutclass.simulate('click');
                    });
                }


                /*end of scheduling class */

            }
        }
    });
    M.local_clclasses = M.local_clclasses || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_clclasses.init_schoolchooser = function(config) { // 'config' contains the parameter values

        return new schoolchooser(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});

$(document).ready(function() {

    var sslect = function() {
        $('#id_cobaltcourseid').change(function() {
            var school = $("#id_cobaltcourseid option:selected").text();
            document.getElementById("id_fullname").value = school;
        });


    }

    sslect();

});