/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
(function($) {
    'use strict';

    var clearParentCategory = function () {
        $('#new_category_parent').find('option').each(function(){
            $('#new_category_parent-suggest').treeSuggest('removeOption', null, this);
        });
    };

    $.widget('mage.newCategoryDialog', {
        _create: function () {
            var widget = this;
            $('#new_category_parent').before($('<input>', {
                id: 'new_category_parent-suggest',
                placeholder: $.mage.__('start typing to search category')
            }));
            $('#new_category_parent-suggest').mage('treeSuggest', this.options.suggestOptions)
                .on('suggestbeforeselect', function (event) {
                    clearParentCategory();
                    $(event.target).treeSuggest('close');
                    $('#new_category_name').focus();
                });

            $.validator.addMethod('validate-parent-category', function() {
                return $('#new_category_parent').val() || $('#new_category_parent-suggest').val() === '';
            }, $.mage.__('Choose existing category.'));
            var newCategoryForm = this.element.find('#new_category_form').mage('validation', {
                errorPlacement: function(error, element) {
                    error.insertAfter(element.is('#new_category_parent') ?
                        $('#new_category_parent-suggest').closest('.mage-suggest') :
                        element);
                }
            }).on('highlight.validate', function(e) {
                    var options = $(this).validation('option');
                    if ($(e.target).is('#new_category_parent')) {
                        options.highlight($('#new_category_parent-suggest').get(0),
                            options.errorClass, options.validClass || '');
                    }
                })

            this.element.dialog({
                title: $.mage.__('Create Category'),
                autoOpen: false,
                minWidth: 560,
                dialogClass: 'mage-new-category-dialog form-inline',
                modal: true,
                multiselect: true,
                resizable: false,
                open: function() {
                    var enteredName = $('#category_ids-suggest').val();
                    $('#new_category_name').val(enteredName);
                    if (enteredName === '') {
                        $('#new_category_name').focus();
                    }
                    $('#new_category_messages').html('');
                },
                close: function() {
                    $('#new_category_name, #new_category_parent-suggest').val('');
                    clearParentCategory();
                    var validationOptions = newCategoryForm.validation('option');
                    validationOptions.unhighlight($('#new_category_parent-suggest').get(0),
                        validationOptions.errorClass, validationOptions.validClass || '');
                    newCategoryForm.validation('clearError');
                    $('#category_ids-suggest').focus();
                },
                buttons: [{
                    text: $.mage.__('Create Category'),
                    'class': 'action-create primary',
                    'data-action': 'save',
                    click: function(event) {
                        if (!newCategoryForm.valid()) {
                            return;
                        }

                        var thisButton = $(event.target).closest('[data-action=save]');
                        thisButton.prop('disabled', true);
                        $.ajax({
                            type: 'POST',
                            url: widget.options.saveCategoryUrl,
                            data: {
                                general: {
                                    name: $('#new_category_name').val(),
                                    is_active: 1,
                                    include_in_menu: 0
                                },
                                parent: $('#new_category_parent').val(),
                                use_config: ['available_sort_by', 'default_sort_by'],
                                form_key: FORM_KEY,
                                return_session_messages_only: 1
                            },
                            dataType: 'json',
                            context: $('body')
                        })
                            .success(
                                function (data) {
                                    if (!data.error) {
                                        $('#category_ids-suggest').trigger('select', {
                                            id: data.category.entity_id,
                                            label: data.category.name
                                        });
                                        $('#new_category_name, #new_category_parent-suggest').val('');
                                        $('#category_ids-suggest').val('');
                                        widget.element.dialog('close');
                                    } else {
                                        $('#new_category_messages').html(data.messages);
                                    }
                                }
                            )
                            .complete(
                                function () {
                                    thisButton.prop('disabled', false);
                                }
                            );
                    }
                },
                {
                    text: $.mage.__('Cancel'),
                    'class': 'action-cancel',
                    'data-action': 'cancel',
                    click: function() {
                        $(this).dialog('close');
                    }
                }]
            });
        }
    });
})(jQuery);
