require(['require', 'jquery', 'routing', 'bootstrap'], function(require, $, router) {

    // XHR forbidden access handler
    $(document).ajaxError(function (event, jqXHR) {
        if (403 === jqXHR.status) {
            window.location.reload();
        }
    });

    function storageAvailable(type) {
        try {
            var storage = window[type],
                x = '__storage_test__';
            storage.setItem(x, x);
            storage.removeItem(x);
            return true;
        }
        catch(e) {
            return e instanceof DOMException && (
                    // everything except Firefox
                e.code === 22 ||
                // Firefox
                e.code === 1014 ||
                // test name field too, because code might not be present
                // everything except Firefox
                e.name === 'QuotaExceededError' ||
                // Firefox
                e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
                // acknowledge QuotaExceededError only if there's something already stored
                storage.length !== 0;
        }
    }

    var $navTab = $('ul.nav-tabs[data-tab-key]');
    if (1 === $navTab.size() && storageAvailable('localStorage')) {
        var tabKey = $navTab.data('tab-key') + '.tab_id';

        $navTab.on('click', 'a', function (e) {
            e.preventDefault();
            $(this).tab('show');

            var id = $(this).attr('id');
            if (id) {
                localStorage.setItem(tabKey, id);
            }
        });

        var tabId = localStorage.getItem(tabKey);
        if (tabId) {
            $navTab.find('a#' + tabId).trigger('click');
        }
    }


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
    var $forms = $('form');
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

    /* -----------------------------------------------------------------------------------------------------------------
     * Clipboard copy
     */
    $(document).on('click', '[data-clipboard-copy]', function (e) {
        if (typeof window['ontouchstart'] !== 'undefined') {
            return true;
        }

        e.preventDefault();
        e.stopPropagation();

        var element = e.currentTarget;
        element.addEventListener('copy', function (event) {
            event.preventDefault();
            if (event.clipboardData) {
                event.clipboardData.setData("text/plain", $(element).data('clipboard-copy'));

                $(element)
                    .tooltip({
                        title: 'Copied to clipboard',
                        placement: 'auto',
                        trigger: 'manual',
                        container: 'body'
                    })
                    .tooltip('show');

                setTimeout(function () {
                    $(element).tooltip('hide');
                }, 1500);
            }
        });

        document.execCommand("Copy");

        return false;
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Toggle details
     */
    $(document).on('click', 'a[data-toggle-details]', function(e) {
        e.preventDefault();

        var $this = $(this), $target = $('#' + $this.data('toggle-details'));

        if (1 === $target.size()) {
            if ($target.is(':visible')) {
                $target.hide();
            } else {
                $target.show();
            }
        }

        return false;
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Resource summary
     */
    require(['ekyna-admin/summary'], function(Summary) {
        Summary.init();
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Resource side detail
     */
    require(['ekyna-admin/side-detail'], function(SideDetail) {
        SideDetail.init();
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Resource side detail
     */
    var $bcBtn = $('#barcode-scanner-button');
    require(['ekyna-admin/barcode-scanner'], function(bsScanner) {
        bsScanner.init({
            //debug: true
        });
        bsScanner.addListener(function(barcode) {
            $bcBtn.find('> i').removeClass('fa-barcode').addClass('fa-spinner fa-pulse fa-3x fa-fw');

            var xhr = $.ajax({
                url: router.generate('ekyna_admin_barcode', {
                    barcode: barcode
                }),
                method: 'GET'
            });

            xhr.done(function(data, textStatus, jqXHR) {
                if ("text/html" === jqXHR.getResponseHeader("Content-Type")) {
                    return;
                }

                if ("application/json" !== jqXHR.getResponseHeader("Content-Type")) {
                    throw "Unexpected barcode response type";
                }

                if (!data.hasOwnProperty('results')) {
                    return;
                }

                if (0 === data.results.length) {
                    return;
                }

                if (1 === data.results.length) {
                    if (data.results[0].type === 'redirect') {
                        window.location.href = data.results[0].url;
                    } else if (data.results[0].type === 'modal') {
                        // TODO
                        console.log('Not yet implemented');
                    } else {
                        throw 'Unexpected result type';
                    }

                    return;
                }

                // Dropdown
                data.results.forEach(function(result) {
                    // TODO
                });
            });

            xhr.always(function() {
                $bcBtn.find('> i').removeClass('fa-spinner fa-pulse fa-3x fa-fw').addClass('fa-barcode');
            });
        });
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * User Pins
     */
    var $pinList = $('.navbar li.user-pins > div');

    function handlePinResponse(data) {
        var $userPinLink;

        if (data.hasOwnProperty('added')) {
            // Add new entry in user pins list
            var $span = $('<span data-id="' + data.added.id + '"></span>'),
                $link = $('<a href="' + data.added.path + '">' + data.added.label + '</a>'),
                path = router.generate('ekyna_admin_pin_remove', {id: data.added.id}),
                $remove = ('<a href="' + path + '"><i class="fa fa-remove"></i></a>');

            $span
                .append($link)
                .append($remove)
                .prependTo($pinList);

            // Toggle (resource) user pin link
            $userPinLink = $(
                'a.user-pin' +
                '[data-resource="' + data.added.resource + '"]' +
                '[data-identifier="' + data.added.identifier + '"]'
            );
            if (1 === $userPinLink.size()) {
                $userPinLink
                    .addClass('unpin')
                    .attr('href', router.generate('ekyna_admin_pin_resource_unpin', {
                        name: data.added.resource,
                        identifier: data.added.identifier
                    }));
            }
        } else if (data.hasOwnProperty('removed')) {
            // Remove entry in user pins list
            $pinList
                .find('span[data-id=' + data.removed.id + ']')
                .remove();

            // Toggle (resource) user pin link
            $userPinLink = $(
                'a.user-pin' +
                '[data-resource="' + data.removed.resource + '"]' +
                '[data-identifier="' + data.removed.identifier + '"]'
            );
            if (1 === $userPinLink.size()) {
                $userPinLink
                    .removeClass('unpin')
                    .attr('href', router.generate('ekyna_admin_pin_resource_pin', {
                        name: data.removed.resource,
                        identifier: data.removed.identifier
                    }));
            }
        }
    }

    $(document).on('click', 'a.user-pin', function(e) {
        e.preventDefault();

        var $this = $(this),
            url = $this.attr('href');

        $.ajax({url: url, method: 'GET', dataType: 'json'}).done(handlePinResponse);

        return false;
    });

    $('li.user-pins').on('click', 'a:last-child', function(e) {
        e.preventDefault();

        var $this = $(this), url = $this.attr('href');

        $.ajax({url: url, method: 'GET', dataType: 'json'}).done(handlePinResponse);

        return false;
    });

    /* -----------------------------------------------------------------------------------------------------------------
     * Helpers
     */
    var $helperContent = $('#helper-content:visible');
    var $helperLoading = $('<p id="helper-content-loading"><i class="fa fa-spinner fa-spin fa-2x"></i></p>');

    if ($helperContent.length === 1) {
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
                    if ($content.length === 1) {
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
