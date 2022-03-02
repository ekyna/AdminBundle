require(
    ['require', 'jquery', 'routing', 'ekyna-api', 'bootstrap', 'jquery/form', 'ekyna-clipboard-copy', 'ekyna-spinner'],
    function(require, $, Router, Api) {

    Api.init('admin_api_login');

    // XHR forbidden access handler
    $(document).ajaxError(function (event, jqXHR) {
        if (403 === jqXHR.status) {
            window.location.reload();
        }
    });

    function storageAvailable(type) {
        let storage, x;
        try {
            storage = window[type];
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
                storage && storage.length !== 0;
        }
    }

    (function() {
        const $navTab = $('ul.nav-tabs[data-tab-key]');
        if (1 === $navTab.length && storageAvailable('localStorage')) {
            const tabKey = $navTab.data('tab-key') + '.tab_id';

            $navTab.on('click', 'a', function (e) {
                e.preventDefault();
                $(this).tab('show');

                const id = $(this).attr('id');
                if (id) {
                    localStorage.setItem(tabKey, id);
                }
            });

            const tabId = localStorage.getItem(tabKey);
            if (tabId) {
                $navTab.find('a#' + tabId).trigger('click');
            }
        }
    })();

    /*var $ = require('jquery'),
        Router = require('routing');
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
        const $item = $(this).parent();
        $item.toggleClass("active");
        if ($item.hasClass("active")) {
            $item.find(".submenu").slideDown("fast");
        } else {
            $item.find(".submenu").slideUp("fast");
        }
    });

    // mobile side-menu slide toggler
    const $menu = $("#sidebar-nav");
    $('body').on('click', function() {
        if ($(this).hasClass('menu')) {
            $(this).removeClass('menu');
        }
    });
    $menu.on('click', function(e) {
        e.stopPropagation();
    });
    $("#menu-toggler").on('click', function(e) {
        e.stopPropagation();
        $('body').toggleClass('menu');
    });
    $(window).on('resize', function() {
        $(this).width() > 769 && $('body.menu').removeClass('menu');
    });

    // quirk to fix dark skin sidebar menu because of B3 border-box
    if ($menu.height() > $('.content').height()) {
        $('html').addClass('small');
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

    // hack to fix jquery 3.6 focus security patch that bugs auto search in select-2
    // https://forums.select2.org/t/search-being-unfocused/1203/10
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });

    /* Tabs */
    $(document).on('click', '.nav-tabs a', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // Forms
    const $forms = $('form');
    if ($forms.length > 0) {
        require(['ekyna-form'], function(Form) {
            $forms.each(function(i, f) {
                const form = Form.create(f);
                form.init();
            });
        });
    }

    // Tables
    const $tables = $('.ekyna-table');
    if ($tables.length > 0) {
        require(['ekyna-table'], function(Table) {
            $tables.each(function(i, t) {
                Table.create(t);
            });
        });
    }

    // Media thumb (if available)
    try {
        require(['ekyna-media/thumb'], function (Thumb) {
            Thumb.init()
        });
    } catch (e) {
        console.log('Media thumb is not available.');
    }

    /* -----------------------------------------------------------------------------------------------------------------
     * Toggle details
     */
    $(document).on('click', 'a[data-toggle-details]', function(e) {
        e.preventDefault();

        var $this = $(this), $target = $('#' + $this.data('toggle-details'));

        if (1 === $target.length) {
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
     * Barcode scanner
     */
    const $bcBtn = $('#barcode-scanner-button');
    require(['ekyna-admin/barcode-scanner'], function(bsScanner) {
        bsScanner.init({
            //debug: true
        });
        bsScanner.addListener(function(barcode) {
            $bcBtn.find('> i').removeClass('fa-barcode').addClass('fa-spinner fa-pulse fa-3x fa-fw');

            const xhr = $.ajax({
                url: Router.generate('admin_toolbar_barcode', {
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
     * Wide search
     */
    (function() {
        const
            $searchForm = $('#wide-search > form'),
            $searchInput = $searchForm.find('input[type=text]'),
            $searchFilters = $searchForm.find('input[type=checkbox]'),
            $searchIcon = $searchForm.find('.input-group-addon > i'),
            $searchResults = $('#wide-search > div.list-group');

        let busy = false, searchXhr, searchTimeout;

        this.start = () => {
            busy = true;

            $searchIcon.removeClass('fa-search').addClass('fa-spinner fa-spin');

            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            if (searchXhr) {
                searchXhr.abort();
            }

            searchTimeout = setTimeout(this.search, 300);
        };

        this.stop = function() {
            busy = false;
            $searchIcon.removeClass('fa-spinner fa-spin').addClass('fa-search');
        };

        this.search = function() {
            $searchResults.empty();

            if ('' === $searchInput.val()) {
                this.stop();
                return;
            }

            searchXhr = $.ajax({
                url: Router.generate('admin_toolbar_search'),
                method: 'POST',
                data: $searchForm.formSerialize(),
                dataType: 'json'
            });

            searchXhr.done((data) => {
                if (!data.hasOwnProperty('results')) {
                    return;
                }

                if (0 === data.results.length) {
                    return;
                }

                data.results.forEach(function(result) {
                    let $a = $('<a class="list-group-item"></a>');

                    if (result.icon) {
                        $('<i></i>').addClass(result.icon).appendTo($a);
                    }

                    $a.append(document.createTextNode(result.title));
                    $a.attr('href', result.url);
                    $a.appendTo($searchResults);
                });

                $searchResults.show();
            });

            searchXhr.always(() => this.stop());
        };

        this.init = function() {
            // Prevent filters dropdown to hide on choice selection
            $searchForm
                .find('.dropdown-menu input, .dropdown-menu label')
                .on('click', function(e) {
                    e.stopPropagation();
                });

            // Prevent submitting search form
            $searchForm.on('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                return false;
            });

            $searchFilters.on('change', this.start);

            $searchInput.on('keyup', (e) => {
                if (!/Key[A-Z]|Digit[0-9]|Numpad[0-9]|Space|Comma|Period|Semicolon|(Back)?Slash|Minus|Equal|IntlBackslash|Bracket(Left|Right)|Quote|Backspace/.test(e.code)) {
                    e.stopPropagation();
                    e.preventDefault();

                    return false;
                }

                this.start();
            });

            $searchInput.on('focus', () => {
                if ($searchResults.is(':empty')) {
                    if ('' !== $searchInput.val()) {
                        this.start();
                    }

                    return false;
                }

                $searchResults.show();
            });

            $(document).on('click', (e) => {
                if (busy) {
                    return;
                }

                if (1 === $(e.target).closest('#wide-search').length) {
                    return;
                }

                $searchResults.hide();
            });

            /*$searchInput.on('blur', () => {
                $searchResults.hide();
            });*/
        };

        this.init();
    })();

    /* -----------------------------------------------------------------------------------------------------------------
     * User Pins
     */
    (function() {
        const $pinList = $('.navbar li.user-pins > div');

        function handlePinResponse(data) {
            let $userPinLink;

            if (data.hasOwnProperty('added')) {
                // Add new entry in user pins list
                let $span = $('<span data-id="' + data.added.id + '"></span>'),
                    $link = $('<a href="' + data.added.path + '" title="' + data.added.label + '">' + data.added.label + '</a>'),
                    path = Router.generate('admin_pin_remove', {id: data.added.id}),
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
                if (1 === $userPinLink.length) {
                    $userPinLink
                        .addClass('unpin')
                        .attr('href', Router.generate('admin_pin_resource_unpin', {
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
                if (1 === $userPinLink.length) {
                    $userPinLink
                        .removeClass('unpin')
                        .attr('href', Router.generate('admin_pin_resource_pin', {
                            name: data.removed.resource,
                            identifier: data.removed.identifier
                        }));
                }
            }
        }

        $(document).on('click', 'a.user-pin', function(e) {
            e.preventDefault();

            $.ajax({url: $(this).attr('href'), method: 'GET', dataType: 'json'}).done(handlePinResponse);

            return false;
        });

        $('li.user-pins').on('click', 'a:last-child', function(e) {
            e.preventDefault();

            $.ajax({url: $(this).attr('href'), method: 'GET', dataType: 'json'}).done(handlePinResponse);

            return false;
        });
    })();

    /* -----------------------------------------------------------------------------------------------------------------
     * Helpers
     */
    (function() {
        const $helperContent = $('#helper-content:visible');
        if (1 !== $helperContent.length) {
            return;
        }

        if (!storageAvailable('localStorage')) {
            return;
        }

        function helperKey(reference) {
            return 'ekyna_cms_helper[' + reference + ']';
        }

        /**
         * @param reference String
         * @returns Promise<string>
         */
        function loadHelper(reference) {
            if (!reference) {
                return Promise.reject('Empty reference');
            }

            let key = 'ekyna_cms_helper[' + reference + ']',
                cache = localStorage.getItem(key);

            if (cache) {
                cache = JSON.parse(cache);

                if (cache.expiresAt > Math.floor((new Date()).getTime() / 1000)) {
                    return Promise.resolve(cache.content);
                }
            }

            $helperContent.loadingSpinner('on');
            const expiresAt = Math.floor((new Date()).getTime() / 1000) + 60*60*24*7; // One week

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: Router.generate('ekyna_setting_api_helper_fetch'),
                    data: {reference: reference},
                    type: 'GET',
                    dataType: 'xml'
                })
                .done(function (xml) {
                    let $content = $(xml).find('content'),
                        content = '';

                    if (1 === $content.length) {
                        content = $content.text();
                    }

                    localStorage.setItem(key, JSON.stringify({expiresAt, content}));

                    resolve(content);
                })
                .fail(function () {
                    localStorage.setItem(key, JSON.stringify({expiresAt, content: ''}));

                    reject('Helper not found');
                })
                .always(function () {
                    $helperContent.loadingSpinner('off');
                });
            })
        }

        function displayHelper(reference) {
            $helperContent.empty();

            loadHelper(reference)
                .then((content) => {
                    $('<div />').append(content).appendTo($helperContent);
                })
                .catch(() => {});
        }

        let defaultReference = $helperContent.data('helper') || null;
        displayHelper(defaultReference);

        $forms
            .on('focus', '*[data-helper]', function() {
                displayHelper($(this).data('helper'));
            })
            .on('blur', '*[data-helper]', function() {
                displayHelper(defaultReference);
            });
    })();
});
