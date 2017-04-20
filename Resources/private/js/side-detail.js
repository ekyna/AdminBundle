define(['jquery', 'routing', 'bootstrap'], function ($, Router) {

    var SideDetail = {
        $container: null
    };

    SideDetail.load = function ($element) {
        var that = this;

        if ($element.data('side-detail-content')) {
            that.open($element);

            return;
        }

        var config = $element.data('side-detail'), url;

        if (typeof config === 'object') {
            url = Router.generate(config.route, config.parameters);
        } else if (typeof config === 'string') {
            url = config;
        } else {
            throw 'Unexpected summary config.';
        }

        var xhr = $.ajax({
            url: url,
            dataType: 'html'
        });

        xhr.done(function (content) {
            $element.data('side-detail-content', content);
            $element.removeData('side-detail-xhr');

            that.open($element);
        });

        $element.data('side-detail-xhr', xhr);
        $element.removeData('side-detail-timeout');
    };

    SideDetail.open = function ($element) {
        this.$container.addClass('opened').find('> div').html($element.data('side-detail-content'));
    };

    SideDetail.close = function () {
        this.$container.removeClass('opened');
    };

    SideDetail.isOpened = function () {
        return this.$container.hasClass('opened');
    };

    SideDetail.lock = function () {
        this.$container.addClass('locked');
    };

    SideDetail.unlock = function () {
        this.$container.removeClass('locked');
    };

    SideDetail.isLocked = function () {
        return this.$container.hasClass('locked');
    };

    SideDetail.init = function () {
        // Abort if mobile device
        if ('ontouchstart' in window) {
            return;
        }

        this.$container = $(
            '<div id="side-detail"><span><i class="glyphicon glyphicon-pushpin"/></span><div></div></div>'
        ).appendTo('body');

        $(document)
            .on('mouseenter', '[data-side-detail]', function (e) {
                if (SideDetail.isLocked()) {
                    return;
                }

                var $this = $(e.currentTarget);

                if ($this.data('side-detail-xhr') || $this.data('side-detail-timeout')) {
                    return;
                }

                $this.data('side-detail-timeout', setTimeout(function() {
                    SideDetail.load($this);
                }, 300));
            })
            .on('mouseleave', '[data-side-detail]', function (e) {
                if (SideDetail.isLocked()) {
                    return;
                }

                var $this = $(e.currentTarget);

                if ($this.data('side-detail-xhr')) {
                    return;
                }

                var timeout = $this.data('side-detail-timeout');
                clearTimeout(timeout);
                $this.removeData('side-detail-timeout');

                SideDetail.close();
            })
            .on('keyup', function (e) {
                if (e.which === 17) { // Ctrl
                    if (SideDetail.isLocked()) {
                        SideDetail.unlock();
                    } else if (SideDetail.isOpened()) {
                        SideDetail.lock();
                    }
                } else if (e.which === 27) { // Escape
                    if (SideDetail.isLocked()) {
                        SideDetail.unlock();
                    }
                    if (SideDetail.isOpened()) {
                        SideDetail.close();
                    }
                }
            });
    };

    return SideDetail;
});
