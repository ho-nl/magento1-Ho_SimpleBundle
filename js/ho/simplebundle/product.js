
Product.SimpleBundle = Class.create();
Product.SimpleBundle.prototype = {
    initialize : function(idPrefix, grid) {
        this.idPrefix = idPrefix;
        this.grid = grid;

        return this;
    },
    createPopup : function(url) {
        if (this.win && !this.win.closed) {
            this.win.close();
        }

        this.win = window.open(url, '',
                'width=1000,height=700,resizable=1,scrollbars=1');
        this.win.focus();
    },
//    registerProduct : function(grid, element, checked) {
//        if (checked) {
//            if (element.linkAttributes) {
//                this.links.set(element.value, element.linkAttributes);
//            }
//        } else {
//            this.links.unset(element.value);
//        }
//        this.updateGrid();
//        this.grid.rows.each( function(row) {
//            this.revalidateRow(this.grid, row);
//        }.bind(this));
//        this.updateValues();
//    },
//    updateProduct : function(productId, attributes) {
//        var isAssociated = false;
//
//        if (typeof this.links.get(productId) != 'undefined') {
//            isAssociated = true;
//            this.links.unset(productId);
//        }
//
//        if (isAssociated && this.checkAttributes(attributes)) {
//            this.links.set(productId, this.cloneAttributes(attributes));
//        } else if (isAssociated) {
//            this.newProducts.push(productId);
//        }
//
//        this.updateGrid();
//        this.updateValues();
//        this.grid.reload(null);
//    },
//    cloneAttributes : function(attributes) {
//        var newObj = [];
//        for ( var i = 0, length = attributes.length; i < length; i++) {
//            newObj[i] = Object.clone(attributes[i]);
//        }
//        return newObj;
//    },
//    updateGrid : function() {
//        this.grid.reloadParams = {
//            'products[]' :this.links.keys().size() ? this.links.keys() : [ 0 ],
//            'new_products[]' :this.newProducts
//        };
//    },
//    updateValues : function() {
//        var uniqueAttributeValues = $H( {});
//        /* Collect unique attributes */
//        this.links.each( function(pair) {
//            for ( var i = 0, length = pair.value.length; i < length; i++) {
//                var attribute = pair.value[i];
//                if (uniqueAttributeValues.keys()
//                        .indexOf(attribute.attribute_id) == -1) {
//                    uniqueAttributeValues.set(attribute.attribute_id, $H( {}));
//                }
//                uniqueAttributeValues.get(attribute.attribute_id).set(
//                        attribute.value_index, attribute);
//            }
//        });
//        /* Updating attributes value container */
//        this.container
//                .childElements()
//                .each(
//                        function(row) {
//                            var attribute = row.attributeObject;
//                            for ( var i = 0, length = attribute.values.length; i < length; i++) {
//                                if (uniqueAttributeValues.keys().indexOf(
//                                        attribute.attribute_id) == -1
//                                        || uniqueAttributeValues
//                                                .get(attribute.attribute_id)
//                                                .keys()
//                                                .indexOf(
//                                                        attribute.values[i].value_index) == -1) {
//                                    row.attributeValues
//                                            .childElements()
//                                            .each(
//                                                    function(elem) {
//                                                        if (elem.valueObject.value_index == attribute.values[i].value_index) {
//                                                            elem.remove();
//                                                        }
//                                                    });
//                                    attribute.values[i] = undefined;
//
//                                } else {
//                                    uniqueAttributeValues.get(
//                                            attribute.attribute_id).unset(
//                                            attribute.values[i].value_index);
//                                }
//                            }
//                            attribute.values = attribute.values.compact();
//                            if (uniqueAttributeValues
//                                    .get(attribute.attribute_id)) {
//                                uniqueAttributeValues.get(
//                                        attribute.attribute_id).each(
//                                        function(pair) {
//                                            attribute.values.push(pair.value);
//                                            this
//                                                    .createValueRow(row,
//                                                            pair.value);
//                                        }.bind(this));
//                            }
//                        }.bind(this));
//        this.updateSaveInput();
//        this.updateSimpleForm();
//    },
//    createValueRow : function(container, value) {
//        var templateVariables = $H( {});
//        if (!this.valueAutoIndex) {
//            this.valueAutoIndex = 1;
//        }
//        templateVariables.set('html_id', container.id + '_'
//                + this.valueAutoIndex);
//        templateVariables.update(value);
//        var pricingValue = parseFloat(templateVariables.get('pricing_value'));
//        if (!isNaN(pricingValue)) {
//            templateVariables.set('pricing_value', pricingValue);
//        } else {
//            templateVariables.unset('pricing_value');
//        }
//        this.valueAutoIndex++;
//
//        // var li = $(Builder.node('li', {className:'attribute-value'}));
//        var li = $(document.createElement('LI'));
//        li.className = 'attribute-value';
//        li.id = templateVariables.get('html_id');
//        li.update(this.addValueTemplate.evaluate(templateVariables));
//        li.valueObject = value;
//        if (typeof li.valueObject.is_percent == 'undefined') {
//            li.valueObject.is_percent = 0;
//        }
//
//        if (typeof li.valueObject.pricing_value == 'undefined') {
//            li.valueObject.pricing_value = '';
//        }
//
//        container.attributeValues.appendChild(li);
//
//        var priceField = li.down('.attribute-price');
//        var priceTypeField = li.down('.attribute-price-type');
//
//        if (priceTypeField != undefined && priceTypeField.options != undefined) {
//            if (parseInt(value.is_percent)) {
//                priceTypeField.options[1].selected = !(priceTypeField.options[0].selected = false);
//            } else {
//                priceTypeField.options[1].selected = !(priceTypeField.options[0].selected = true);
//            }
//        }
//
//        Event.observe(priceField, 'keyup', this.onValuePriceUpdate);
//        Event.observe(priceField, 'change', this.onValuePriceUpdate);
//        Event.observe(priceTypeField, 'change', this.onValueTypeUpdate);
//        var useDefaultEl = li.down('.attribute-use-default-value');
//        if (useDefaultEl) {
//            if (li.valueObject.use_default_value) {
//                useDefaultEl.checked = true;
//                this.updateUseDefaultRow(useDefaultEl, li);
//            }
//            Event.observe(useDefaultEl, 'change', this.onValueDefaultUpdate);
//        }
//    },
//    updateValuePrice : function(event) {
//        var li = Event.findElement(event, 'LI');
//        li.valueObject.pricing_value = (Event.element(event).value.blank() ? null
//                : Event.element(event).value);
//        this.updateSimpleForm();
//        this.updateSaveInput();
//    },
//    updateValueType : function(event) {
//        var li = Event.findElement(event, 'LI');
//        li.valueObject.is_percent = (Event.element(event).value.blank() ? null
//                : Event.element(event).value);
//        this.updateSimpleForm();
//        this.updateSaveInput();
//    },
//    updateValueUseDefault : function(event) {
//        var li = Event.findElement(event, 'LI');
//        var useDefaultEl = Event.element(event);
//        li.valueObject.use_default_value = useDefaultEl.checked;
//        this.updateUseDefaultRow(useDefaultEl, li);
//    },
//    updateUseDefaultRow : function(useDefaultEl, li) {
//        var priceField = li.down('.attribute-price');
//        var priceTypeField = li.down('.attribute-price-type');
//        if (useDefaultEl.checked) {
//            priceField.disabled = true;
//            priceTypeField.disabled = true;
//        } else {
//            priceField.disabled = false;
//            priceTypeField.disabled = false;
//        }
//        this.updateSimpleForm();
//        this.updateSaveInput();
//    },
//    updateSaveInput : function() {
//        $(this.idPrefix + 'save_attributes').value = Object.toJSON(this.attributes);
//        $(this.idPrefix + 'save_links').value = Object.toJSON(this.links);
//    },

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

        this.grid.reloadParams['products_upsell[]'].push(result.product_id)
        this.grid.reload();
    },
//    checkCreationUniqueAttributes : function() {
//        var attributes = [];
//        this.attributes
//                .each( function(attribute) {
//                    attributes
//                            .push( {
//                                attribute_id :attribute.attribute_id,
//                                value_index :$('simple_product_' + attribute.attribute_code).value
//                            });
//                }.bind(this));
//
//        return this.checkAttributes(attributes);
//    },
//    getAttributeByCode : function(attributeCode) {
//        var attribute = null;
//        this.attributes.each( function(item) {
//            if (item.attribute_code == attributeCode) {
//                attribute = item;
//                throw $break;
//            }
//        });
//        return attribute;
//    },
//    getAttributeById : function(attributeId) {
//        var attribute = null;
//        this.attributes.each( function(item) {
//            if (item.attribute_id == attributeId) {
//                attribute = item;
//                throw $break;
//            }
//        });
//        return attribute;
//    },
//    getValueByIndex : function(attribute, valueIndex) {
//        var result = null;
//        attribute.values.each( function(value) {
//            if (value.value_index == valueIndex) {
//                result = value;
//                throw $break;
//            }
//        });
//        return result;
//    },
    showNoticeMessage : function() {
        $('assign_product_warrning').show();
    }
}

//We have to define the variable globally else it wont be accessible later on.
var simpleBundle;