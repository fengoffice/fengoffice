og.EventRelatedPopUp = function(action) {
    var html_option  = "<div style='width:100%; margin: 10px; padding: 10px;'>";
        html_option += "<div>" + lang('apply changes to') + "</div>";
        html_option += "<div><input type='radio' name='type_related' value='only' onclick='selectEventRelated(this.value)' checked/>" + lang('only this event') + "</div>";
        html_option += "<div><input type='radio' name='type_related' value='news' onclick='selectEventRelated(this.value)'/>" + lang('this event alone and all to come forward') + "</div>";
        html_option += "<div><input type='radio' name='type_related' value='all' onclick='selectEventRelated(this.value)'/>" + lang('all events related') + "</div>";
        html_option += "<div><input type='hidden' name='action_related' id='action_related' value='" + action + "'/></div>";
        html_option += "</div>";
    og.EventRelatedPopUp.superclass.constructor.call(this, {
                y: 220,
                width: 350,
                height: 230,
                id: 'event-related',
                layout: 'border',
                modal: true,
                resizable: false,
                closeAction: 'close',
                border: false,
                buttons: [{
                        text: lang('accept'),
                        handler: this.accept,
                        scope: this
                }],
                items: [{
                        region: 'center',
                        layout: 'fit',
                        html: html_option
                }]
        });
}