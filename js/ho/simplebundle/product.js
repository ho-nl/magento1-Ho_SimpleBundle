/**
 * Ho_SimpleBundle
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_SimpleBundle
 * @copyright   Copyright © 2014 H&O (http://www.h-o.nl/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Paul Hachmang – H&O <info@h-o.nl>
 */

Product.SimpleBundle = Class.create();
Product.SimpleBundle.prototype = {
    initialize : function(idPrefix, grid) {
        this.idPrefix = idPrefix;
        this.grid = grid;

        return this;
    },
    initializeAdvicesForSimpleForm : function() {
        if ($(this.idPrefix + 'simplebundle_form').advicesInited) {
            return;
        }

        $(this.idPrefix + 'simplebundle_form').select('td.value').each( function(td) {
            var adviceContainer = $(Builder.node('div'));
            td.appendChild(adviceContainer);
            td.select('input', 'select').each( function(element) {
                element.advaiceContainer = adviceContainer;
            });
        });
        $(this.idPrefix + 'simplebundle_form').advicesInited = true;
    },
    quickCreateNewProduct : function() {
        this.initializeAdvicesForSimpleForm();

        $(this.idPrefix + 'simplebundle_form').removeClassName('ignore-validate');
        var validationResult = $(this.idPrefix + 'simplebundle_form').select('input',
                'select', 'textarea').collect( function(elm) {
            return Validation.validate(elm, {
                useTitle :false,
                onElementValidate : function() {
                }
            });
        }).all();
        $(this.idPrefix + 'simplebundle_form').addClassName('ignore-validate');

        if (!validationResult) {
            return;
        }

        var params = Form.serializeElements($(this.idPrefix + 'simplebundle_form')
                .select('input', 'select', 'textarea'), true);

        params.form_key = FORM_KEY;
        $('messages').update();
        new Ajax.Request(this.createQuickUrl, {
            parameters :params,
            method :'post',
            area :$(this.idPrefix + 'simplebundle_form'),
            onComplete :this.quickCreateNewProductComplete.bind(this)
        });
    },
    quickCreateNewProductComplete : function(transport) {
        var result = transport.responseText.evalJSON();

        if (result.error) {
            if (result.error.fields) {
                $(this.idPrefix + 'simplebundle_form').removeClassName(
                        'ignore-validate');
                $H(result.error.fields)
                        .each(
                                function(pair) {
                                    $('simple_product_' + pair.key).value = pair.value;
                                    $('simple_product_' + pair.key + '_autogenerate').checked = false;
                                    toggleValueElements(
                                            $('simple_product_' + pair.key + '_autogenerate'),
                                            $('simple_product_' + pair.key + '_autogenerate').parentNode);
                                    Validation.ajaxError(
                                            $('simple_product_' + pair.key),
                                            result.error.message);
                                });
                $(this.idPrefix + 'simplebundle_form')
                        .addClassName('ignore-validate');
            } else {
                if (result.error.message) {
                    alert(result.error.message);
                } else {
                    alert(result.error);
                }
            }
            return;
        } else if (result.messages) {
            $('messages').update(result.messages);
        }

        this.grid.reloadParams['products_upsell[]'].push(result.product_id);
        this.grid.reload();

        var input = $$('[name="links[upsell]"]').first();
        if (input.value) {
            input.value += result.product_id+'=cG9zaXRpb249';
        } else {
            input.value = result.product_id+'=cG9zaXRpb249';
        }
            console.log(input.value);

    },
    showNoticeMessage : function() {
        $('assign_product_warrning').show();
    }
}

//We have to define the variable globally else it wont be accessible later on.
var simpleBundle;