M.local_dashboard = {
    wwwroot: '',
    preLoadArray: new Array(),
    tickIcon: new Image(),
    crossIcon: new Image(),
    displayDate: 1,
    init: function (YUIObject, root, modulesInUse, date) {
        var i;

        // Remember the web root.
        this.wwwroot = root;

        // Rember whether the now indicator is displayed (also hides date).
        this.displayDate = date;

        // Preload icons for modules in use
        for (i = 0; i < modulesInUse.length; i++) {
            // this.preLoadArray[i] = new Image();
            //  this.preLoadArray[i].src = M.util.image_url('icon', modulesInUse[i]);
        }
        //this.tickIcon.src = M.util.image_url('tick', 'local_dashboard');
        // this.crossIcon.src = M.util.image_url('cross', 'local_dashboard');
    },
    showInfo: function (mod, type, id, name, message, dateTime, userID, icon) {

        // Dynamically update the content of the information window below the progress bar.
        var content = '<a href="' + this.wwwroot + '/mod/' + mod + '/view.php?id=' + id + '">';
        content += '<img src="' + M.util.image_url('icon', mod) + '" alt="" class="moduleIcon" />';
        content += name + '</a><br />' + type + ' ' + message + '&nbsp;';
        content += '<img src="' + M.util.image_url(icon, 'local_dashboard') + '" alt="" /><br />';
        if (this.displayDate) {
            content += M.str.local_dashboard.time_expected + ': ' + dateTime + '<br />';
        }
        document.getElementById('progressBarInfouser' + userID).innerHTML = content;
    }
};