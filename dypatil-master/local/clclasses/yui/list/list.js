YUI.add('moodle-local_clclasses-list', function(Y) {
    var ModulenameNAME = 'list';
    var list = function() {
        list.superclass.constructor.apply(this, arguments);
    };
    Y.extend(list, Y.Base, {
        initializer: function(config) {

            if (config && config.formid) {
                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect = Y.one('#' + config.formid + ' #fitem_id_startdate');
                var formatselect1 = Y.one('#' + config.formid + ' #fitem_id_enddate');
                var formatselect2 = Y.one('#' + config.formid + ' #fgroup_id_starttime');
                var formatselect3 = Y.one('#' + config.formid + ' #fgroup_id_endtime');
                var formatselects = Y.one('#' + config.formid + ' #fitem_id_choose');
                updatebut.setStyle('display', 'none');
                if (formatselects) {

                    formatselects.on('click', function() {
                        updatebut.simulate('click');
                    });
                }
                /*if (formatselect) {
                 
                 formatselect.on('change', function() {
                 updatebut.simulate('click');
                 });
                 }
                 if (formatselect1) {
                 
                 formatselect1.on('change', function() {
                 updatebut.simulate('click');
                 });
                 } 
                 if (formatselect2) {
                 
                 formatselect2.on('change', function() {
                 updatebut.simulate('click');
                 });
                 } 
                 if (formatselect3) {
                 
                 formatselect3.on('change', function() {
                 updatebut.simulate('click');
                 });
                 } */
            }
        }
    });
    M.local_clclasses = M.local_clclasses || {};
    M.local_clclasses.init_list = function(config) {

        return new list(config);
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});