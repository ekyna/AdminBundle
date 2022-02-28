define(['jquery', 'ekyna-spinner'], function($) {
    "use strict";

    var $acl = $('.acl-list'),
        config = $acl.data('config'),
        busy = false;

    function checkModel(data, fields) {
        for (var f = 0; f < fields.length; f++) {
            if (fields.hasOwnProperty(f)) {
                if (!data.hasOwnProperty(fields[f])) {
                    throw 'Undefined property ' + fields[f];
                }
            }
        }
    }

    function parseResponse(data) {
        if (!data.hasOwnProperty('inheritance')) {
            return;
        }
        if (!data.hasOwnProperty('namespaces')) {
            return;
        }

        for (var n = 0; n < data.namespaces.length; n++) {
            var namespace = data.namespaces[n];
            checkModel(namespace, ['name', 'resources']);

            var $namespace = $acl.find('div.acl-namespace[data-name="' + namespace.name + '"]');
            if (1 !== $namespace.length) {
                throw 'Namespace element not found';
            }

            for (var r = 0; r < namespace.resources.length; r++) {
                var resource = namespace.resources[r];
                checkModel(resource, ['name', 'permissions']);

                var $resource = $namespace.find('div.acl-resource[data-name="' + resource.name + '"]');
                if (1 !== $resource.length) {
                    throw 'Resource element not found';
                }

                for (var p = 0; p < resource.permissions.length; p++) {
                    var perm = resource.permissions[p];
                    checkModel(perm, ['name', 'granted', 'value', 'inherited']);

                    var $permission = $resource.find('div.acl-permission[data-name="' + perm.name + '"] button');
                    if (1 !== $permission.length) {
                        throw 'Permission element not found';
                    }

                    var $inherited = $permission.next();

                    // Button
                    if (data.inheritance && (false === perm.inherited)) {
                        $permission
                            .prop('disabled', true)
                            .removeClass('acl-grant acl-deny')
                            .find('> i')
                            .addClass('fa-minus')
                            .removeClass('fa-check text-success fa-remove text-danger');
                    } else if (false === perm.value || ((null === perm.value) && (null === perm.inherited))) {
                        $permission
                            .prop('disabled', false)
                            .addClass('acl-grant')
                            .removeClass('acl-deny')
                            .find('> i')
                            .addClass('fa-check text-success')
                            .removeClass('fa-minus fa-remove text-danger');
                    } else {
                        $permission
                            .prop('disabled', false)
                            .addClass('acl-deny')
                            .removeClass('acl-grant')
                            .find('> i')
                            .addClass('fa-remove text-danger')
                            .removeClass('fa-minus fa-check text-success');
                    }

                    // Label
                    if (perm.granted) {
                        $permission.prev().addClass('btn-success').removeClass('btn-danger');
                    } else {
                        $permission.prev().addClass('btn-danger').removeClass('btn-success');
                    }

                    // Inherited
                    if (1 !== $inherited.length) {
                        continue;
                    }

                    if (true === perm.inherited) {
                        $inherited
                            .find('> i')
                            .addClass('fa-check text-success')
                            .removeClass('fa-remove text-danger');
                    } else {
                        $inherited
                            .find('> i')
                            .addClass('fa-remove text-danger')
                            .removeClass('fa-check text-success');
                    }
                }
            }
        }
    }

    function request(url, data) {
        if (busy) {
            return false;
        }

        $acl.loadingSpinner();

        var xhr = $.ajax({
            url: url,
            method: 'POST',
            data: data
        });

        xhr.done(parseResponse);

        xhr.always(function() {
            $acl.loadingSpinner('off');
            busy = false;
        });

        return false;
    }

    $('div.acl-permission button').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this), value;

        if ($button.hasClass('acl-grant')) {
            value = 1;
        } else if ($button.hasClass('acl-deny')) {
            value = 0;
        } else {
            return false;
        }

        request(config.permission, {
            namespace: $button.closest('div.acl-namespace').data('name'),
            resource: $button.closest('div.acl-resource').data('name'),
            permission: $button.closest('div.acl-permission').data('name'),
            value: value
        });

        return false;
    });

    $('button.acl-resource').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this);

        request(config.resource, {
            namespace: $button.closest('div.acl-namespace').data('name'),
            resource: $button.closest('div.acl-resource').data('name'),
            value: $button.hasClass('acl-grant') ? 1 : 0
        });

        return false;
    });

    $('button.acl-namespace').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this);

        request(config.namespace, {
            namespace: $button.closest('div.acl-namespace').data('name'),
            value: $button.hasClass('acl-grant') ? 1 : 0
        });

        return false;
    });
});
