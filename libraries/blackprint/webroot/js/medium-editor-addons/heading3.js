;(function ($, window, document, undefined) {

    'use strict';

    /** Default values */
    var pluginName = 'heading3',
        addonName = 'Heading3', // first char is uppercase
        defaults = {
            label: '<span class="fa fa-plus-circle"></span>'
        };

    /**
     * Custom Addon object
     *
     * Sets options, variables and calls init() function
     *
     * @constructor
     * @param {DOM} el - DOM element to init the plugin on
     * @param {object} options - Options to override defaults
     * @return {void}
     */

    function Heading3 (el, options) {
        this.el = el;
        this.$el = $(el);
        this.templates = window.MediumInsert.Templates;
        this.core = this.$el.data('plugin_'+ pluginName);

        this.options = $.extend(true, {}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    /**
     * Initialization
     *
     * @return {void}
     */

    Heading3.prototype.init = function () {
        this.events();
    };

    /**
     * Event listeners
     *
     * @return {void}
     */

    Heading3.prototype.events = function () {

    };

    /**
     * Get the Core object
     *
     * @return {object} Core object
     */
    Heading3.prototype.getCore = function () {
        return this.core;
    };

    /**
     * Add custom content
     *
     * This function is called when user click on the addon's icon
     *
     * @return {void}
     */

    Heading3.prototype.add = function () {

    };


    /** Addon initialization */

    $.fn[pluginName + addonName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName + addonName)) {
                $.data(this, 'plugin_' + pluginName + addonName, new Heading3(this, options));
            }
        });
    };

})(jQuery, window, document);