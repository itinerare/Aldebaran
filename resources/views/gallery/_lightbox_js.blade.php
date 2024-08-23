<script>
    $(document).ready(function() {
        $('.image-link, .video-link').magnificPopup({
            gallery: {
                enabled: true
            },
            type: 'image',
            image: {
                markup: '<div class="mfp-figure">' +
                    '<div class="mfp-close"></div>' +
                    '<div class="mfp-img"></div>' +
                    '<div class="mfp-bottom-bar">' +
                    '<div class="mfp-title"></div>' +
                    '</div>' +
                    '</div>',
            },
            ajax: {
                settings: null,
                cursor: 'mfp-ajax-cur',
                tError: '<a href="%url%">The content</a> could not be loaded.'
            },
            callbacks: {
                elementParse: function(item) {
                    if (item.el[0].classList.contains('video-link')) {
                        item.type = 'ajax';
                    } else {
                        item.type = 'image';
                    }
                },
                parseAjax: function(mfpResponse) {
                    console.log('Ajax content loaded:', mfpResponse);
                },
                ajaxContentAdded: function() {
                    //
                }
            }
        });
    });
</script>
