/**
 * Module to handle js on webhooks page in local trigger.
 *
 * @package local_trigger
 * @author Justin Hunt - justin@poodll.com
 */
define(['jquery', 'core/ajax', 'core/str', 'core/log'], function($, ajax, str,log) {
    const SELECTOR = {
        CA_SELECTBOX: '.local_trigger_ca_selectbox select',
        CA_DESCRIPTION: '#fitem_id_description input',
        CA_PROTOCOL: '#fitem_id_protocol  input',
        CA_CUSTOMTEXT1: '#fitem_id_customtext1 input',
        CA_CUSTOMTEXT2: '#fitem_id_customtext2 input',
        CA_CUSTOMTEXT3: '#fitem_id_customtext3 input',
        CA_CUSTOMTEXT4: '#fitem_id_customtext4 input',
        CA_CUSTOMTEXT5: '#fitem_id_customtext5 input',
        CA_CUSTOMTEXT6: '#fitem_id_customtext6 input',
        CA_CUSTOMTEXT7: '#fitem_id_customtext7 input',
        CA_CUSTOMTEXT8: '#fitem_id_customtext8 input',
        CA_CUSTOMTEXT9: '#fitem_id_customtext9 input',
        CA_CUSTOMTEXT10: '#fitem_id_customtext10 input',
    }

    const EVENT = {
        CHANGE: 'change',
    }

    var stringStore = {};

    const initStrings = function (callback) {
        str.get_strings([
            {key: "webhooks_explanation", component: "local_trigger"},
            {key: "customactions_explanation", component: "local_trigger"},
        ]).done(function (strings) {
            log.debug(strings);
            stringStore = strings;
            if (typeof callback == 'function') {
                callback();
            }
        });
    };

    const clearFields = function() {
        $(SELECTOR.CA_DESCRIPTION).val('');
        $(SELECTOR.CA_PROTOCOL).val('');
        $(SELECTOR.CA_CUSTOMTEXT1).val('');
        $(SELECTOR.CA_CUSTOMTEXT2).val('');
        $(SELECTOR.CA_CUSTOMTEXT3).val('');
        $(SELECTOR.CA_CUSTOMTEXT4).val('');
        $(SELECTOR.CA_CUSTOMTEXT5).val('');
        $(SELECTOR.CA_CUSTOMTEXT6).val('');
        $(SELECTOR.CA_CUSTOMTEXT7).val('');
        $(SELECTOR.CA_CUSTOMTEXT8).val('');
        $(SELECTOR.CA_CUSTOMTEXT9).val('');
        $(SELECTOR.CA_CUSTOMTEXT10).val('');
        for(var i=1;i<=10;i++) {
            $('#customtext' + i + '_label').text('');
        }
    }


    const initListeners = function() {
        var that = this;

        $(SELECTOR.CA_SELECTBOX).on(EVENT.CHANGE, function (e) {
            log.debug("changed");
            log.debug($(this).val());

            //clear fields
            clearFields();

            ajax.call([{
                methodname: 'local_trigger_fetch_function_details',
                args: {
                    functionname: $(this).val()
                }
            }])[0].done(function (data) {
                var fdata = JSON.parse(data);
                $(SELECTOR.CA_DESCRIPTION).val(fdata.description);
                var index=0;
                //do required fields first
                for(var fieldname in fdata.parameters_desc.keys) {
                    var thefield = fdata.parameters_desc.keys[fieldname];
                    if(thefield.required) {
                        index++;
                        $(SELECTOR['CA_CUSTOMTEXT' + (index)]).val(fieldname);
                        $('#customtext' + index + '_label').text('(' + thefield.type + ') ' + thefield.desc + ' (required)');
                    }
                }
                //do optional fields next
                for(var fieldname in fdata.parameters_desc.keys) {
                    var thefield = fdata.parameters_desc.keys[fieldname];
                    if(!thefield.required) {
                        index++;
                        $(SELECTOR['CA_CUSTOMTEXT' + (index)]).val(fieldname);
                        $('#customtext' + index + '_label').text('(' + thefield.type + ') ' + thefield.desc + ' (optional)');
                    }
                }

                log.debug(data);
            });
        });
    };

    return {
        init: function () {
                initStrings();
                initListeners();
        }
    }
});