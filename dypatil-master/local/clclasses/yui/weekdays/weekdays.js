YUI.add('moodle-local_clclasses-weekdays', function(Y) {
    var ModulenameNAME = 'weekdays';
    var weekdays = function() {
        weekdays.superclass.constructor.apply(this, arguments);
    };
    Y.extend(weekdays, Y.Base, {
        initializer: function(config) {

            if (config && config.formid) {
                var updatebut = document.getElementsByClassName('checkboxgroup1');
                var avalilabledates = new Array();
                var days = new Array();
                days[1] = 'id_mon';
                days[2] = 'id_tue';
                days[3] = 'id_wed';
                days[4] = 'id_thu';
                days[5] = 'id_fri';
                var dateevents = new Array();
                dateevents[0] = 'id_startdate_day';
                dateevents[1] = 'id_startdate_month';
                dateevents[2] = 'id_startdate_year';
                dateevents[3] = 'id_enddate_day';
                dateevents[4] = 'id_enddate_month';
                dateevents[5] = 'id_enddate_year';
                for (var i = 0; i < dateevents.length; i++)
                {
                    document.getElementById(dateevents[i]).onchange = function() {
                        for (var i = 0; i < updatebut.length; i++)
                        {
                            updatebut[i].addEventListener('click', function() {
                                var startdateday = document.getElementById('id_startdate_day');
                                var startdatemonth = document.getElementById('id_startdate_month');
                                var startdateyear = document.getElementById('id_startdate_year');
                                var startdate = new Date(startdateyear.value, (startdatemonth.value - 1), startdateday.value);
                                var enddateday = document.getElementById('id_enddate_day');
                                var enddatemonth = document.getElementById('id_enddate_month');
                                var enddateyear = document.getElementById('id_enddate_year');
                                var enddate = new Date(enddateyear.value, (enddatemonth.value - 1), enddateday.value);
                                var timediff = Math.abs(startdate.getTime() - enddate.getTime());
                                var diffdays = Math.ceil(timediff / (1000 * 3600 * 24));
                                var i = 0;
                                var d2 = new Array();
                                var availableday = [1, 2, 3, 4, 5];
                                for (var d = startdate; d <= enddate; d.setDate(d.getDate() + 1)) {
                                    avalilabledates[i] = d.getDay();
                                    i++;
                                }
                                var f = 0;
                                for (var a = 0; a < availableday.length; a++) {
                                    for (var b = 0; b < avalilabledates.length; b++) {
                                        if (availableday[a] != avalilabledates[b]) {
                                            //code
                                            alert(days[availableday[a]]);
                                            document.getElementById(days[availableday[a]]).checked = false;
                                            document.getElementById(days[availableday[a]]).disabled = true;
                                            d2[f] = availableday[a];
                                            f++
                                        }
                                    }
                                }
                                //for(var d1=0;d1<d2.length;d1++){
                                //if([1,2,3,4,5].indexOf(avalilabledates[d1]) <= -1){
                                // alert(avalilabledates[d1]);
                                //  document.getElementById(days[avalilabledates[d1]]).disabled;
                                //}
                                //}
                                console.log(d2);
                                console.log(avalilabledates);
                            });
                        }
                    }
                }
            }
        }
    });
    M.local_clclasses = M.local_clclasses || {};

    M.local_clclasses.init_weekdays = function(config) {

        return new weekdays(config);
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});

