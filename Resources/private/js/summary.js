define(['jquery', 'routing', 'bootstrap'], function($, Router) {

    var Summary = {};

    Summary.load = function($element) {
        var config = $element.data('summary');

        var xhr = $.ajax({
            url: Router.generate(config.route, config.parameters),
            dataType: 'html'
        });

        xhr.done(function (content) {
            $element.popover({
                content: content,
                template: '<div class="popover summary" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                container: 'body > div.content',
                html: true,
                placement: 'auto',
                trigger: 'hover'
            });

            if ($element.is(':hover')) {
                $element.popover('show');
            }
        });

        $element.data('summary-xhr', xhr);
    };

    Summary.init = function() {
        // Abort if mobile device
        if ('ontouchstart' in window) {
            return;
        }

        $(document).on('mouseenter', '[data-summary]', function() {
            var $this = $(this);

            if ($this.data('summary-xhr')) {
                return;
            }

            Summary.load($this);
        });
    };

    return Summary;
});