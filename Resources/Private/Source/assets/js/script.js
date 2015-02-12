$(function() {
    jQuery(window).resize(function() {
        var myWidth = 960, percentageWidth = .95;
        if ($('#cboxOverlay').is(':visible')) {
            $.colorbox.resize({width: ( $(window).width() > ( myWidth + 20) ) ? myWidth : Math.round($(window).width() * percentageWidth)});
            $('.cboxPhoto').css({
                width: $('#cboxLoadedContent').innerWidth(),
                height: 'auto'
            });
            $('#cboxLoadedContent').height($('.cboxPhoto').height());
            $.colorbox.resize();
        }
    });
});