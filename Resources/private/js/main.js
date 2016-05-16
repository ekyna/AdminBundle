require(['require', 'jquery', 'routing', 'bootstrap'], function(require, $, router) {

    /*var $ = require('jquery'),
        router = require('routing');
    require('bootstrap');*/

    // navbar notification popups
    /*$(".notification-dropdown").each(function(index, el) {
        var $el = $(el);
        var $dialog = $el.find(".pop-dialog");
        var $trigger = $el.find(".trigger");

        $dialog.click(function(e) {
            e.stopPropagation();
        });
        $dialog.find(".close-icon").click(function(e) {
            e.preventDefault();
            $dialog.removeClass("is-visible");
            $trigger.removeClass("active");
        });
        $("body").click(function() {
            $dialog.removeClass("is-visible");
            $trigger.removeClass("active");
        });

        $trigger.click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            // hide all other pop-dialogs
            $(".notification-dropdown .pop-dialog").removeClass("is-visible");
            $(".notification-dropdown .trigger").removeClass("active");

            $dialog.toggleClass("is-visible");
            if ($dialog.hasClass("is-visible")) {
                $(this).addClass("active");
            } else {
                $(this).removeClass("active");
            }
        });
    });*/

    // sidebar menu dropdown toggle
    $('#sidebar-menu').on('click', '.dropdown-toggle', function(e) {
        e.preventDefault();
        var $item = $(this).parent();
        $item.toggleClass("active");
        if ($item.hasClass("active")) {
            $item.find(".submenu").slideDown("fast");
        } else {
            $item.find(".submenu").slideUp("fast");
        }
    });

    // mobile side-menu slide toggler
    var $menu = $("#sidebar-nav");
    $("body").click(function() {
        if ($(this).hasClass("menu")) {
            $(this).removeClass("menu");
        }
    });
    $menu.click(function(e) {
        e.stopPropagation();
    });
    $("#menu-toggler").click(function(e) {
        e.stopPropagation();
        $("body").toggleClass("menu");
    });
    $(window).resize(function() {
        $(this).width() > 769 && $("body.menu").removeClass("menu");
    });

    // quirk to fix dark skin sidebar menu because of B3 border-box
    if ($menu.height() > $(".content").height()) {
        $("html").addClass("small");
    }

    // build all tooltips from data-attributes
    /*$("[data-toggle='tooltip']").each(function(index, el) {
        $(el).tooltip({
            placement : $(this).data("placement") || 'top'
        });
    });*/

    // custom uiDropdown element, example can be seen in user-list.html on the
    // 'Filter users' button
    /*var uiDropdown = new function() {
        var self;
        self = this;
        this.hideDialog = function($el) {
            return $el.find(".dialog").hide().removeClass("is-visible");
        };
        this.showDialog = function($el) {
            return $el.find(".dialog").show().addClass("is-visible");
        };
        return this.initialize = function() {
            $("html").click(function() {
                $(".ui-dropdown .head").removeClass("active");
                return self.hideDialog($(".ui-dropdown"));
            });
            $(".ui-dropdown .body").click(function(e) {
                return e.stopPropagation();
            });
            return $(".ui-dropdown").each(function(index, el) {
                return $(el).click(function(e) {
                    e.stopPropagation();
                    $(el).find(".head").toggleClass("active");
                    if ($(el).find(".head").hasClass("active")) {
                        return self.showDialog($(el));
                    } else {
                        return self.hideDialog($(el));
                    }
                });
            });
        };
    };
    // instantiate new uiDropdown from above to build the plugins
    new uiDropdown();*/

    // toggle all checkboxes from a table when header checkbox is clicked
    /*$(".table th input:checkbox").click(function() {
        $checks = $(this).closest(".table").find("tbody input:checkbox");
        if ($(this).is(":checked")) {
            $checks.prop("checked", true);
        } else {
            $checks.prop("checked", false);
        }
    });*/

    /* Tabs */
    $(document).on('click', '.nav-tabs a', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // Forms
    var $forms = $('.form-body');
    if ($forms.size() > 0) {
        require(['ekyna-form'], function(Form) {
            $forms.each(function(i, f) {
                var form = Form.create(f);
                form.init();
            });
        });
    }

    // Tables
    var $tables = $('.ekyna-table');
    if ($tables.size() > 0) {
        require(['ekyna-table'], function(Table) {
            $tables.each(function(i, t) {
                Table.create(t);
            });
        });
    }

    /* Helpers */
    var $helperContent = $('#helper-content:visible');
    var $helperLoading = $('<p id="helper-content-loading"><i class="fa fa-spinner fa-spin fa-2x"></i></p>');

    if ($helperContent.length == 1) {
        function loadHelper(reference) {
            $helperContent.empty();
            if (reference) {
                $helperContent.append($helperLoading);
                $.ajax({
                    url: router.generate('ekyna_setting_api_helper_fetch'),
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

        $forms.on('focus', '*[data-helper]', function() {
            loadHelper($(this).data('helper'));
        }).on('blur', '*[data-helper]', function() {
            loadHelper(defaultReference);
        });
    }
});
