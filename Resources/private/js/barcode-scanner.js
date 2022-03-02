define(['jquery'], function ($) {

    function BarcodeScanner() {
        this.binded = false;
        this.prevent = false;
        this.listeners = [];
        this.stack = [];
        this.timeout = null;
        this.config = BarcodeScanner.defaults;

        // Debug
        this.start = null;
    }

    BarcodeScanner.defaults = {
        filter: /^[0-9A-Za-z]$/,
        interval: 60,
        lengths: [8, 13],
        endCode: 'Enter',
        debug: false
    };

    BarcodeScanner.prototype.log = function (message) {
        if (!this.config.debug) {
            return;
        }

        console.log('BarcodeScanner', message);
    };

    BarcodeScanner.prototype.addListener = function (listener) {
        this.listeners.push(listener);
    };

    BarcodeScanner.prototype.removeListener = function (listener) {
        this.listeners = this.listeners.filter(function (l) {
            return l !== listener
        });
    };

    BarcodeScanner.prototype.notify = function () {
        if (0 === this.stack.length) {
            return;
        }

        if (this.config.debug) {
            const ms = (new Date()).getMilliseconds() - this.start;
            this.log(this.stack.length + ' chars in ' + ms + ' ms. Avg: ' + Math.round(ms / this.stack.length) + ' ms.');
        }

        const code = this.stack.join('');
        this.clear();

        this.listeners.forEach(function (listener) {
            listener(code)
        });
    };

    BarcodeScanner.prototype.clear = function () {
        this.stack = [];
        this.start = null;

        if (this.timeout) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }
    };

    BarcodeScanner.prototype.onFormFocus = function (e) {
        if ($(e.currentTarget).is('[data-barcode-scanner]')) {
            return;
        }

        this.log('Disabled by form focus');

        this.prevent = true;

        this.clear();
    };

    BarcodeScanner.prototype.onFormBlur = function () {
        this.log('Enabled by form blur');

        this.prevent = false;

        this.clear();
    };

    BarcodeScanner.prototype.onTimeout = function () {
        this.log('Timeout ' + this.stack.length);

        if (-1 !== this.config.lengths.indexOf(this.stack.length)) {
            this.notify();

            return;
        }

        this.clear();
    };

    BarcodeScanner.prototype.onKeydown = function (e) {
        this.log('Key down ' + e.key);

        if (this.prevent) {
            return;
        }

        if (this.config.endCode && (e.key === this.config.endCode)) {
            this.notify();

            return;
        }

        if (!e.key || !(this.config.filter && this.config.filter.test(e.key))) {
            return;
        }

        if (this.config.debug && (null === this.start)) {
            this.start = (new Date()).getMilliseconds();
        }

        this.stack.push(e.key);

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.timeout = setTimeout(() => this.onTimeout(), this.config.interval);
    };

    BarcodeScanner.prototype.init = function (config) {
        this.config = $.extend({}, BarcodeScanner.defaults, config || {});

        this.log(this.config);

        if (this.binded) {
            return;
        }

        $(document)
            .on('focus', 'input, select, textarea, button', () => this.onFormFocus())
            .on('blur', 'input, select, textarea, button', () => this.onFormBlur())
            .on('keydown', () => this.onKeydown());

        this.binded = true;

    };

    return new BarcodeScanner();
});
