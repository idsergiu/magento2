/**
 * {license_notice}
 *
 * @category    frontend configurable product price option
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/

(function ($, undefined) {
    $.widget('mage.configurable', {
        options: {
            state: {}
        },

        _create: function () {
            this.options.taxConfig = this.options.spConfig.taxConfig;
            this.options.settings = (this.options.spConfig.containerId) ?
                $(this.options.spConfig.containerId).find(".super-attribute-select") :
                $('.super-attribute-select');

            this.options.values = this.options.spConfig.defaultValues || {};

            // Override defaults by URL query parameters
            var hashIndex = window.location.href.indexOf('#');
            if (hashIndex !== -1) {
                var queryParams = $.parseQuery({query: window.location.href.substr(hashIndex + 1)});
                $.each(queryParams, $.proxy(function(key, value) {
                    this.options.values[key] = value;
                }, this));
            }

            // Overwrite defaults by inputs values if needed
            if (this.options.spConfig.inputsInitialized) {
                this.options.values = {};
                $.each(this.options.settings, $.proxy(function(index, element) {
                    if (element.value) {
                        var attributeId = element.id.replace(/[a-z]*/, '');
                        this.options.values[attributeId] = element.value;
                    }
                }, this));
            }

            // Put events to check select reloads
            $.each(this.options.settings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));

            // Fill state
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '');
                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));

            var childSettings = [];
            for (var j = this.options.settings.length - 1; j >= 0; j--) {
                var prevSetting = this.options.settings[j - 1] ? this.options.settings[j - 1] : false;
                var nextSetting = this.options.settings[j + 1] ? this.options.settings[j + 1] : false;
                if (j === 0) {
                    this._fillSelect(this.options.settings[j]);
                } else {
                    this.options.settings[j].disabled = true;
                }
                this.options.settings[j].childSettings = childSettings.slice(0);
                this.options.settings[j].prevSetting = prevSetting;
                this.options.settings[j].nextSetting = nextSetting;
                childSettings.push(this.options.settings[j]);
            }

            // Set values to inputs
            this._configureForValues();
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function() {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;
                    element.value = (typeof(this.options.values[attributeId]) === 'undefined') ?
                        '' :
                        this.options.values[attributeId];
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param event Event triggered to configure an option.
         */
        _configure: function(event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param element The element associated with a configurable option.
         */
        _configureElement: function(element) {
            this._reloadOptionLabels(element);
            if (element.value) {
                this.options.state[element.config.id] = element.value;
                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                }
            }
            else {
                this._resetChildren(element);
            }
            this._reloadPrice();
        },

        /**
         * Option labels show the option value and its price. This method reloads these labels
         * for a specified option.
         * @private
         * @param element The element associated with the configurable option.
         */
        _reloadOptionLabels: function(element) {
            var selectedPrice = 0;
            if (element.options[element.selectedIndex].config && !this.options.spConfig.stablePrices) {
                selectedPrice = parseFloat(element.options[element.selectedIndex].config.price);
            }
            for (var i = 0; i < element.options.length; i++) {
                if (element.options[i].config) {
                    element.options[i].text =
                        this._getOptionLabel(element.options[i].config, element.options[i].config.price - selectedPrice);
                }
            }
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param element The element associated with a configurable option.
         */
        _resetChildren: function(element) {
            if (element.childSettings) {
                for (var i = 0; i < element.childSettings.length; i++) {
                    element.childSettings[i].selectedIndex = 0;
                    element.childSettings[i].disabled = true;
                    if (element.config) {
                        this.options.state[element.config.id] = false;
                    }
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param element Element associated with a configurable option.
         */
        _fillSelect: function(element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId);
            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;

            var prevConfig = false;
            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }
            if (options) {
                var index = 1;
                for (var i = 0; i < options.length; i++) {
                    var allowedProducts = [];
                    if (prevConfig) {
                        for (var j = 0; j < options[i].products.length; j++) {
                            if (prevConfig.config.allowedProducts && prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }
                    if (allowedProducts.size() > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i], options[i].price), options[i].id);
                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }
                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param option A single choice among a group of choices for a configurable option.
         * @param price The price associated with the option choice.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function(option, price) {
            price = parseFloat(price);
            var tax, incl, excl;
            if (this.options.taxConfig.includeTax) {
                tax = price / (100 + this.options.taxConfig.defaultTax) * this.options.taxConfig.defaultTax;
                excl = price - tax;
                incl = excl * (1 + (this.options.taxConfig.currentTax / 100));
            } else {
                tax = price * (this.options.taxConfig.currentTax / 100);
                excl = price;
                incl = excl + tax;
            }
            price = (this.options.taxConfig.showIncludeTax || this.options.taxConfig.showBothPrices) ? incl : excl;
            var str = option.label;
            if (price) {
                str = (this.options.taxConfig.showBothPrices) ?
                    str += ' ' + this._formatPrice(excl, true) + ' (' + this._formatPrice(price, true) + ' ' + this.options.taxConfig.inclTaxTitle + ')' :
                    str += ' ' + this._formatPrice(price, true);
            }
            return str;
        },

        /**
         * Format's the price of a configurable option's choice. Add sign as needed, round,
         * and format the rounded price with the appropriate sign.
         * @private
         * @param price An option choice's price
         * @param showSign Whether to show the sign as '-' or '+' in the formatted price.
         * @return {String} Returns the formatted price with or without the sign.
         */
        _formatPrice: function(price, showSign) {
            var str = '';
            price = parseFloat(price);
            if (showSign) {
                if (price < 0) {
                    str += '-';
                    price = -price;
                }
                else {
                    str += '+';
                }
            }
            var roundedPrice = (Math.round(price * 100) / 100).toString();
            str = (this.options.spConfig.prices && this.options.spConfig.prices[roundedPrice]) ?
                str + this.options.spConfig.prices[roundedPrice] :
                str + this.options.spConfig.template.replace(/#\{(.*?)\}/, price.toFixed(2));
            return str;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param element The element associated with a configurable option.
         */
        _clearSelect: function(element) {
            for (var i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param attributeId The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function(attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Reload the price of the configurable product incorporating the prices of all of the
         * configurable product's option selections.
         * @private
         * @return {Number} The price of the configurable product including selected options.
         */
        _reloadPrice: function() {
            if (this.options.spConfig.disablePriceReload) {
                return true;
            }
            var price = 0,
                oldPrice = 0;
            for (var i = this.options.settings.length - 1; i >= 0; i--) {
                var selected = this.options.settings[i].options[this.options.settings[i].selectedIndex];
                if (selected.config) {
                    price += parseFloat(selected.config.price);
                    oldPrice += parseFloat(selected.config.oldPrice);
                }
            }
            this.options.priceOptionInstance.changePrice('config', {'price': price, 'oldPrice': oldPrice});
            this.options.priceOptionInstance.reloadPrice();
            return price;
        }
    });
})(jQuery);