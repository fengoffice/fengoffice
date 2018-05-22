/**
 * This file is for all that functions of feng that work correctly without bootstrap
 * And need a little twist to work properly with bootstrap
 *
 * Please be a good programer and document the functions yes ;)
 * Also when you see this file is going bananas of huge or complicated, alert the others programmers
 *
 */


og.bootstrap_helper = {
    /**
     * This function render the contact selector but remove the class container of the content and also the style in line
     *
     * @param json_object config all the configuration required for the function og.renderContactSelector
     */
    renderContactCombo: function (config) {
        config.custom_selected_class='col-md-6';
        config.no_style_in_selected=true;
        og.renderContactSelector(config);
        console.info(config.id);
        $($("#" + config.id).children()[0]).removeClass().removeAttr("style");
    },
}
