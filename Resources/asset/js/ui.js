(function($, Router) {

    $(document).on('fos_js_routing_loaded', function() {

        var $helperContent = $('#helper-content:visible');
        var $helperLoading = $('<p id="helper-content-loading"><i class="fa fa-spinner fa-spin fa-2x"></i></p>');

        if ($helperContent.length == 1) {
            function loadHelper(reference) {
                $helperContent.empty();
                if (reference) {
                    $helperContent.append($helperLoading);
                    $.ajax({
                        url: Router.generate('ekyna_setting_api_helper_fetch'),
                        data: {reference: reference},
                        type: 'GET',
                        dataType: 'xml'
                    })
                    .done(function(xmldata) {
                        $helperLoading.remove();
                        var $content = $(xmldata).find('content');
                        if ($content.length == 1) {
                            var $div = $('<div />');
                            $($content.text()).appendTo($div);
                            $div.appendTo($helperContent);
                        }
                    })
                    .always(function() {
                        $helperLoading.remove();
                    });
                }
            }

            var defaultReference = $helperContent.data('helper') || null;
            loadHelper(defaultReference);

            $('.form-body').on('focus', '*[data-helper]', function(e) {
                loadHelper($(this).data('helper'));
            }).on('blur', '*[data-helper]', function(e) {
                loadHelper(defaultReference);
            });
        }
	});

})(jQuery, Routing);