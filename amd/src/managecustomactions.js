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
    }

    const EVENT = {
        CHANGE: 'change',
    }

    const fieldcount=10;

    var stringStore = {};

    const get_fieldname = function(index){
        return $('#fitem_id_customtext' + index + ' input');
    };

    const get_fieldhelp = function(index){
        return $('#fitem_id_customhelp' + index + ' input');
    };


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

    const clearFields = function(that) {

        $(SELECTOR.CA_DESCRIPTION).val('');
        $(SELECTOR.CA_PROTOCOL).val('');
        for(var i=1;i<=fieldcount;i++) {
            get_fieldname(i).val('');
            get_fieldhelp(i).val('');
        }
    };


    const initListeners = function() {
        var that = this;

        $(SELECTOR.CA_SELECTBOX).on(EVENT.CHANGE, function (e) {
            log.debug("changed");
            log.debug($(this).val());

            //clear fields
            clearFields(that);

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
                for(var thefieldname in fdata.parameters_desc.keys) {
                    var thefield = fdata.parameters_desc.keys[thefieldname];
                    if(thefield.required) {
                        index++;
                        get_fieldname(index).val(thefieldname);
                        var thelabel ='(' + thefield.type + ') ' + thefield.desc + ' (required)';
                        get_fieldhelp(index).val(thelabel);
                    }
                }
                //do optional fields next
                for(var thefieldname in fdata.parameters_desc.keys) {
                    var thefield = fdata.parameters_desc.keys[thefieldname];
                    if(!thefield.required) {
                        index++;
                        get_fieldname(index).val(thefieldname);
                        var thelabel ='(' + thefield.type + ') ' + thefield.desc + ' (optional)';
                        get_fieldhelp(index).val(thelabel);
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