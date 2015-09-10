YUI.add('moodle-local_clclasses-chooser', function(Y) {
    var ModulenameNAME = 'chooser';
    var chooser = function() {
        chooser.superclass.constructor.apply(this, arguments);
    };
    Y.extend(chooser, Y.Base, {
        initializer: function(config) {

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #id_schoolid');
                var departmentselect = Y.one('#' + config.formid + ' #id_departmentid');
                var onlineselect = Y.one('#' + config.formid + ' #id_online');
                var cobaltcourseselect = Y.one('#' + config.formid + ' #id_cobaltcourseid');

                updatebut.setStyle('display', 'none');

                if (formatselect) {
                    formatselect.on('change', function() {
                        updatebut.simulate('click');
                    });
                }

                if (departmentselect) {
                    departmentselect.on('change', function() {
                        updatebut.simulate('click');
                    });
                }
                if (onlineselect) {
                    onlineselect.on('change', function() {
                        updatebut.simulate('click');
                        // var a=document.getElementByID('id_schoolid').value;
                        // alert(a);
                    });
                }
            }
        }
    });
    M.local_clclasses = M.local_clclasses || {};

    M.local_clclasses.init_chooser = function(config) {

        return new chooser(config);
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});

