YUI.add('moodle-local_prefix-hideshow', function (Y) {
    // alert('hi');
    var ModulenameNAME = 'hideshow';
    var hideshow = function () {
        hideshow.superclass.constructor.apply(this, arguments);
    };
    Y.extend(hideshow, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {

                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                //var updatebut2 = Y.one('#'+config.formid+' #id_updatecourseformat2');
                var formatselect = Y.one('#' + config.formid + ' #id_schoolid');
                var formatselect2 = Y.one('#' + config.formid + ' #id_sectionid');

                if (formatselect) {
                    //  alert('hi');
                    updatebut.setStyle('display', 'none');
                    formatselect.on('change', function () {
                        updatebut.simulate('click');
                    });
                }



                if (formatselect2) {
                    formatselect2.on('change', function () {
                        updatebut.simulate('click');
                    });
                }

            }
        }
    });
    M.local_prefix = M.local_prefix || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_prefix.init_hideshow = function (config) { // 'config' contains the parameter values

        return new hideshow(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});