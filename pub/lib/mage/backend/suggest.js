/**
 * {license_notice}
 *
 * @category    mage
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint jquery:true browser:true*/

(function($) {
    'use strict';
    /**
     * Implement base functionality
     */
    $.widget('mage.suggest', {
        options: {
            template: '',
            minLength: 1,
            /**
             * @type {(string|Array)}
             */
            source: null,
            delay: 500,
            events: {},
            appendMethod: 'after',
            controls: {
                selector: ':ui-menu, .jstree',
                eventsMap: {
                    focus: ['menufocus', 'hover_node'],
                    blur: ['menublur', 'dehover_node'],
                    select: ['menuselect', 'select_tree_node']
                }
            },
            wrapperAttributes: {
                'class': 'mage-suggest'
            },
            attributes: {
                'class': 'mage-suggest-dropdown'
            }
        },

        /**
         * Component's constructor
         * @private
         */
        _create: function() {
            this._setTemplate();
            this._term = '';
            this._selectedItem = {value: '', label: ''};
            this.dropdown = $('<div/>', this.options.attributes).hide();
            this.element
                .wrap($('<div><div class="mage-suggest-inner"></div></div>')
                .prop(this.options.wrapperAttributes))
                [this.options.appendMethod](this.dropdown)
                .attr('autocomplete', 'off');
            if (this.options.valueField) {
                this.valueField = $(this.options.valueField);
            } else {
                this.valueField = this._createValueField()
                    .insertBefore(this.element)
                    .attr('name', this.element.attr('name'));
                this.element.removeAttr('name');
            }
            this._control = this.options.controls || {};
            this._bind();
        },

        /**
         * Create value field which keeps a value for selected option
         * can be overridden in descendants
         * @return {jQuery}
         * @private
         */
        _createValueField: function() {
            return $('<input/>', {
                type: 'hidden'
            });
        },

        /**
         * Component's destructor
         * @private
         */
        _destroy: function() {
            this.element
                .unwrap()
                .removeAttr('autocomplete');
            if (!this.options.valueField) {
                this.element.attr('name', this.valueField.attr('name'));
                this.valueField.remove();
            }
            this.dropdown.remove();
            this._off(this.element, 'keydown keyup blur');
        },

        /**
         * Return actual value of an "input"-element
         * @return {string}
         * @private
         */
        _value: function() {
            return $.trim(this.element[this.element.is(':input') ? 'val' : 'text']());
        },

        /**
         * Pass original event to a control component for handling it as it's own event
         * @param {Object} event
         * @private
         */
        _proxyEvents: function(event) {
            var fakeEvent = $.extend({}, $.Event(event.type), {
                ctrlKey: event.ctrlKey,
                keyCode: event.keyCode,
                which: event.keyCode
            });
            this.dropdown.find(this._control.selector).trigger(fakeEvent);
        },

        /**
         * Bind handlers on specific events
         * @private
         */
        _bind: function() {
            this._on($.extend({
                keydown: function(event) {
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                        case keyCode.HOME:
                        case keyCode.END:
                        case keyCode.PAGE_UP:
                        case keyCode.PAGE_DOWN:
                        case keyCode.UP:
                        case keyCode.DOWN:
                        case keyCode.LEFT:
                        case keyCode.RIGHT:
                            if (!event.shiftKey) {
                                this._proxyEvents(event);
                            }
                            break;
                        case keyCode.TAB:
                            if (this.isDropdownShown()) {
                                this._selectItem();
                                event.preventDefault();
                            }
                            break;
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            if (this.isDropdownShown()) {
                                this._proxyEvents(event);
                                event.preventDefault();
                            }
                            break;
                        case keyCode.ESCAPE:
                            this._hideDropdown();
                            break;
                    }
                },
                keyup: function(event) {
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                        case keyCode.HOME:
                        case keyCode.END:
                        case keyCode.PAGE_UP:
                        case keyCode.PAGE_DOWN:
                        case keyCode.ESCAPE:
                        case keyCode.UP:
                        case keyCode.DOWN:
                        case keyCode.LEFT:
                        case keyCode.RIGHT:
                            break;
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            if (this.isDropdownShown()) {
                                event.preventDefault();
                            }
                            break;
                        default:
                            this.search();
                    }
                },
                blur: this._hideDropdown,
                cut: this.search,
                paste: this.search,
                input: this.search
            }, this.options.events));

            this._bindDropdown();
        },

        /**
         * Bind handlers for dropdown element on specific events
         * @private
         */
        _bindDropdown: function() {
            var events = {
                //click: this._selectItem,
                mousedown: function(e) {
                    e.preventDefault();
                }
            };
            $.each(this._control.eventsMap, $.proxy(function(suggestEvent, controlEvents) {
                $.each(controlEvents, $.proxy(function(i, handlerName) {
                    switch(suggestEvent) {
                        case 'select' :
                            events[handlerName] = this._selectItem;
                            break;
                        case 'focus' :
                            events[handlerName] = this._focusItem;
                            break;
                        case 'blur' :
                            events[handlerName] = this._blurItem;
                            break;
                    }
                }, this))
            }, this));
            this._on(this.dropdown, events);
        },

        /**
         *
         * @param e
         * @param ui
         * @private
         */
        _focusItem: function(e, ui) {
            this.element.val(ui.item.text());
        },

        /**
         *
         * @private
         */
        _blurItem: function() {
            this.element.val(this._term);
        },

        /**
         * Save selected item and hide dropdown
         * @private
         */
        _selectItem: function(e) {
            var templateData = e && e.target ? $.tmplItem(e.target).data.items : this._items;
            var term = this._value();
            if (this.isDropdownShown() && term) {
                /**
                 * @type {(Object|null)} - label+value object of selected item
                 * @private
                 */
                this._selectedItem = $.grep(templateData, $.proxy(function(v) {
                    return v.label === term;
                }, this))[0] || {value: '', label: ''};
                if (this._selectedItem.value) {
                    this._term = this._selectedItem.label;
                    this.valueField.val(this._selectedItem.value);
                    this._hideDropdown();
                }
            }
        },

        /**
         * Check if dropdown is shown
         * @return {boolean}
         */
        isDropdownShown: function() {
            return this.dropdown.is(':visible');
        },

        /**
         * Open dropdown
         * @private
         */
        _showDropdown: function() {
            if (!this.isDropdownShown()) {
                this.dropdown.show();
            }
        },

        /**
         * Close and clear dropdown content
         * @private
         */
        _hideDropdown: function() {
            this.element.val(this._selectedItem.label);
            this.dropdown.hide().empty();
        },

        /**
         * Acquire content template
         * @private
         */
        _setTemplate: function() {
            this.templateName = 'suggest' + Math.random().toString(36).substr(2);
            if ($(this.options.template).length) {
                $(this.options.template).template(this.templateName);
            } else {
                $.template(this.templateName, this.options.template);
            }
        },

        /**
         * Execute search process
         * @public
         */
        search: function() {
            var term = this._value();
            if (this._term !== term) {
                this._term = term;
                if (term) {
                    this._search(term);
                } else {
                    this._selectedItem = {value: '', label: ''};
                    this.valueField.val(this._selectedItem.value);
                }
            }
        },

        /**
         * Actual search method, can be overridden in descendants
         * @param {string} term - search phrase
         * @param {Object} context - search context
         * @private
         */
        _search: function(term, context) {
            var renderer = $.proxy(function(items) {
                return this._renderDropdown(items, context || {});
            }, this);
            this.element.addClass('ui-autocomplete-loading');
            if (this.options.delay) {
                clearTimeout(this._searchTimeout);
                this._searchTimeout = this._delay(function() {
                    this._source(term, renderer);
                }, this.options.delay);
            } else {
                this._source(term, renderer);
            }
        },

        /**
         * Extend basic context with additional data (search results, search term)
         * @param {Object} context
         * @return {Object}
         * @private
         */
        _prepareDropdownContext: function(context) {
            return $.extend(context, {
                items: this._items,
                term: this._term,
                template: this.templateName
            });
        },

        /**
         * Render content of suggest's dropdown
         * @param {Array} items - list of label+value objects
         * @param {Object} context - template's context
         * @private
         */
        _renderDropdown: function(items, context) {
            this._items = items;
            $.tmpl(this.templateName, this._prepareDropdownContext(context))
                .appendTo(this.dropdown.empty());
            this.dropdown.trigger('contentUpdated');
            this._showDropdown();
        },

        /**
         * Implement search process via spesific source
         * @param {string} term - search phrase
         * @param {Function} renderer - search results handler, display search result
         * @private
         */
        _source: function(term, renderer) {
            if ($.isArray(this.options.source)) {
                renderer(this.filter(this.options.source, term));

            } else if ($.type(this.options.source) === 'string') {
                if (this._xhr) {
                    this._xhr.abort();
                }
                this._xhr = $.ajax($.extend({
                    url: this.options.source,
                    type: 'POST',
                    dataType: 'json',
                    data: {name_part: term},
                    success: renderer,
                    showLoader: true
                }, this.options.ajaxOptions || {}));
            }
        },

        _abortSearch: function() {
            clearTimeout(this._searchTimeout);
            if (this._xhr) {
                this._xhr.abort();
            }
        },

        /**
         * Perform filtering in advance loaded items and returns search result
         * @param {Array} items - all available items
         * @param {string} term - search phrase
         * @return {Object}
         */
        filter: function(items, term) {
            var matcher = new RegExp(term, 'i');
            return $.grep(items, function(value) {
                return matcher.test(value.label || value.value || value);
            });
        }
    });

    /**
     * Implements height prediction functionality to dropdown item
     */
    /*$.widget('mage.suggest', $.mage.suggest, {
        /**
         * Extension specific options
         *//*
        options: {
            bottomMargin: 35
        },

        /**
         * @override
         * @private
         *//*
        _renderDropdown: function() {
            this._superApply(arguments);
            this._recalculateDropdownHeight();
        },

        /**
         * Recalculates height of dropdown and cut it if needed
         * @private
         *//*
        _recalculateDropdownHeight: function() {
            var dropdown = this.dropdown.css('visibility', 'hidden'),
                fromTop = dropdown.offset().top,
                winHeight = $(window).height(),
                isOverflowApplied = (fromTop + dropdown.outerHeight()) > winHeight;

            dropdown
                .css('visibility', '')
                [isOverflowApplied ? 'addClass':'removeClass']('overflow-y')
                .height(isOverflowApplied ? winHeight - fromTop - this.options.bottomMargin : '');
        }
    });*/

    /**
     * Implement storing search history and display recent searches
     */
    $.widget('mage.suggest', $.mage.suggest, {
        options: {
            showRecent: true,
            storageKey: 'suggest',
            storageLimit: 10
        },

        /**
         * @override
         * @private
         */
        _create: function() {
            if (this.options.showRecent && window.localStorage) {
                var recentItems = JSON.parse(localStorage.getItem(this.options.storageKey));
                /**
                 * @type {Array} - list of recently searched items
                 * @private
                 */
                this._recentItems = $.isArray(recentItems) ? recentItems : [];
            }
            this._super();
        },

        /**
         * @override
         * @private
         */
        _bind: function() {
            this._super();
            if (!this.options.showRecent) {
                this._on({
                    focus: function() {
                        if (!this._value()) {
                            this._renderDropdown(this._recentItems);
                        }
                    }
                });
            }
        },

        /**
         * @override
         */
        search: function() {
            this._super();
            if (this.options.showRecent) {
                if (!this._term) {
                    this._abortSearch();
                    this._renderDropdown(this._recentItems);
                }
            }
        },

        /**
         * @override
         * @private
         */
        _selectItem: function() {
            this._superApply(arguments);
            if (this._selectedItem.value && this.options.showRecent) {
                this._addRecent(this._selectedItem);
            }
        },

        /**
         * Add selected item of search result into storage of recents
         * @param {Object} item - label+value object
         * @private
         */
        _addRecent: function(item) {
            this._recentItems = $.grep(this._recentItems, function(obj){
                return obj.value !== item.value;
            });
            this._recentItems.unshift(item);
            this._recentItems = this._recentItems.slice(0, this.options.storageLimit);
            localStorage.setItem(this.options.storageKey, JSON.stringify(this._recentItems));
        }
    });

    /**
     * Implement show all functionality
     */
    $.widget('mage.suggest', $.mage.suggest, {
        /**
         * @override
         * @private
         */
        _bind: function() {
            this._super();
            this._on(this.dropdown, {
                showAll: function() {
                    this._search('', {_allSown: true});
                }
            });
        },

        /**
         * @override
         * @private
         */
        _prepareDropdownContext: function() {
            var context = this._superApply(arguments);
            return $.extend(context, {allShown: function(){
                return !!context._allSown;
            }});
        }
    });

    /**
     * Implement category selector functionality
     */
    $.widget('mage.suggest', $.mage.suggest, {
        /**
         *
         * @private
         */
        _bind: function() {
            this._super();
            this._on({
                focus: function() {
                    this.search();
                }
            });
        },

        /**
         *
         */
        search: function() {
            this._super();
            if(!this.options.showRecent && !this._term) {
                this._abortSearch();
                this._search('', {_allSown: true});
            }
        }
    });
})(jQuery);
