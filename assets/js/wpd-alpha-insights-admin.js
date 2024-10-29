//https://graphicdesign.stackexchange.com/questions/83866/generating-a-series-of-colors-between-two-colors - RGB
function wpdInterpolateColor(color1, color2, factor) {
    if (arguments.length < 3) { 
        factor = 0.5; 
    }
    var result = color1.slice();
    for (var i = 0; i < 3; i++) {
        result[i] = Math.round(result[i] + factor * (color2[i] - color1[i]));
    }
    var string = "rgb(" + result[0] + ", " + result[1] + ", " + result[2] + ")";
    return string;
};
function wpdColourArray(color1, color2, steps) {
    if ( steps === 1 ) {
        return [color1];
    }
    var stepFactor = 1 / (steps - 1),
        interpolatedColorArray = [];
    color1 = color1.match(/\d+/g).map(Number);
    color2 = color2.match(/\d+/g).map(Number);
    for(var i = 0; i < steps; i++) {
        interpolatedColorArray.push(wpdInterpolateColor(color1, color2, stepFactor * i));
    }
    return interpolatedColorArray;
}
var wpdPopNotification;
jQuery(document).ready(function($) {
    wpdPopNotification = function(type, title, subtitle = null) {
        if ( type == 'loading' ) {
            var response = wpdAlphaInsights.processing;
        } else if ( type == 'success' ) {
            var response = wpdAlphaInsights.success;
            var timeout = true;
        } else if ( type == 'fail' ) {
            var response = wpdAlphaInsights.failure;
            var timeout = true;
        }
        $('.wpd-notification-pop-title').text(title);
        $('.wpd-notification-pop-subtitle').text(subtitle);
        $('.wpd-notification-pop-icon').html(response);
        $('.wpd-notification-pop').addClass('active');
        if ( timeout ) {
            window.setTimeout(function(){
              $('.wpd-notification-pop').removeClass("active");
            }, 3000);
        }
    }
    jQuery('.wpd-premium-content-overlay').click(function(e) {
        e.preventDefault();
        wpdPopNotification('fail', 'Premium Content', 'This action is only available in the premium version.');
    });
	jQuery('.wpd-jquery-datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		yearRange: "-10:+00",
		beforeShow: function( input, inst ){
	      jQuery(inst.dpDiv).addClass('wpd');
	    },
	});
	// jQuery Dialogs
    $(".additional-items").click(function (e) {
        e.preventDefault(); ///first, prevent the action
        var width = $(window).width() * .5; // 50%
        $("#wpd-dialog").dialog({
        	dialogClass: 'wpd-dialog',
            autoOpen: false,
            title: 'Alpha Insights by WP Davies',
            modal: true,
            height: 'auto',
            width: width,
            show: { duration: 300 },
            hide: { duration: 300 },
            maxHeight: false,
            maxWidth: false,
        });
        ///open the dialog window
        $("#wpd-dialog").dialog("open");
    });
    $(".wpd-combo-select").easySelect({
        // Options
        showEachItem: true,
        search: true,
        placeholder: '',
        buttons: true,
        dropdownMaxHeight: '300px',
    });
    $(".wpd-single-select").easySelect({
        // Options
        showEachItem: true,
        search: true,
        buttons: false,
        dropdownMaxHeight: '300px',
    });
    $(".wpd-exit-notification-pop").click(function (e) {
        $('.wpd-notification-pop').removeClass("active");
    });
});