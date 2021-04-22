(function($) {
    'use strict';

    const maxDeskWidth = 480;
    const sizesAvail = [
        // Desktop version
        ['big', 'med', 'min'],
        // "Mobile" version
        ['med', 'min', 'mic'],
    ];

    $.hasTouchScreen = function() {
        let hasTouchScreen = false;
        if ("maxTouchPoints" in navigator) {
            hasTouchScreen = navigator.maxTouchPoints > 0;
        }
        else if ("msMaxTouchPoints" in navigator) {
            hasTouchScreen = navigator.msMaxTouchPoints > 0;
        }
        return hasTouchScreen;
    }
    
    $(window).on('resize', function() {
        $('.thumbs-cnt .thumb-img').each(function(idx, item) {
            const $img = $(item).find('img[data-src]');
            const name = $img.data('src');
            const thumbSize = ($(window).width() < maxDeskWidth) || $.hasTouchScreen() ? 'mic' : 'min';
            $img.data('size', thumbSize);
            $(item).removeClass(['image-min', 'image-mic']).addClass(`image-${thumbSize}`);
            $img.attr('src', `/generator.php?name=${name}&size=${thumbSize}`);
        });
    }).trigger('resize');

    $('.thumbs-cnt .thumb-img').on('click', function(evt) {
        const name = $(this).find('img[data-src]').data('src');
        let items = sizesAvail[$.hasTouchScreen() + 0].map((size) => ({
            'src': `/generator.php?name=${name}&size=${size}`,
            opts: { thumb: `/generator.php?name=${name}&size=mic` }
        }));
        $.fancybox.open(items, { thumbs: { autoStart: true }});
        return false;
    });

})(window.jQuery);
