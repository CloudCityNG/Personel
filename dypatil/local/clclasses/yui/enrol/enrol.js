YUI.add('moodle-local_clclasses-enrol', function(Y) {
    var ModulenameNAME = 'enrol';
    var enrol = function() {
        enrol.superclass.constructor.apply(this, arguments);
    };
    Y.extend(enrol, Y.Base, {
        initializer: function(config) {

            if (config && config.formid) {
                var button = Y.one('#' + config.formid + ' #id_addall');
                var users = Y.one('#' + config.formid + ' #id_susers');
                button.setAttribute("disabled", "disabled");
                if (users) {
                    users.on('change', function() {
                        button.removeAttribute("disabled")
                    });
                }
            }
        }
    });
    M.local_clclasses = M.local_clclasses || {};
    M.local_clclasses.init_enrol = function(config) {

        return new enrol(config);
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});