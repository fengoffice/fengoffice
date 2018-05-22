/*
    Globals vars for the resize tab menu
 */
og.padding_rigth = 9;
og.padding = 15;
og.font_size = 11;

og.lastWidthTab = 0;
og.lastLengthTab = 0;

og.checkAndAdjustTabsSize = function(widthtab,length) {
    if(widthtab != undefined){
        if ((widthtab == og.lastWidthTab) && (og.lastLengthTab == length)){ // this control is for does't resize the tabs if only show the image icon

            if($(".x-tab-with-icon:not(.x-tab-strip-active):first .x-tab-strip-text:first").width() == 0){
                $(".x-tab-with-icon .x-tab-strip-text").css('width', '0px');
                $(".x-tab-with-icon.x-tab-strip-active .x-tab-strip-text").css('width', 'auto');
                return true;
            }
        }
        og.lastWidthTab = widthtab;
        og.lastLengthTab = length;
    }
    var deltaWidth = 100;
    var total_tabs_w = 40;
    var all_tabs = $("li.x-tab-with-icon");
    for (var j=0; j<all_tabs.length; j++) {
        total_tabs_w += $(all_tabs[j]).outerWidth();
    }
    var container_w = $(".x-tab-strip-wrap").width();
    if (container_w > total_tabs_w){
        if (container_w-deltaWidth > total_tabs_w){
            og.calculateTotalTabsWidthUp(container_w-deltaWidth,function (flag) {
                if (flag){
                    og.checkAndAdjustTabsSize();
                }
            })
        }
    }else{
        og.calculateTotalTabsWidthDown(container_w,function (flagResize) {
            if (flagResize) {
                og.checkAndAdjustTabsSize();
            }
        })
    }
}

/**
 * Increase the size of the menu items
 * @param container_w
 * @param callback
 */
og.calculateTotalTabsWidthUp = function (container_w,callback) {
    var total_tabs_w = 40;
    var all_tabs = $("li.x-tab-with-icon");
    for (var j=0; j<all_tabs.length; j++) {
        total_tabs_w += $(all_tabs[j]).outerWidth();
    }
    if(og.padding_rigth<10){
        og.padding_rigth ++
    }
    if (og.font_size < 11){
        og.font_size ++
    }
    og.padding++
    if (og.padding > 15){
        callback(false);
        return false;
    }
    $(".x-tab-with-icon .x-tab-strip-text").css('width', 'auto');;

    og.changeTabsSize(og.font_size,og.padding,og.padding_rigth);

    callback(true);
}

/**
 * Decreases the size of the menu items
 * @param container_w
 * @param callback
 */
og.calculateTotalTabsWidthDown = function (container_w,callback) {
    var flagChanges = false;
    var total_tabs_w = 40;
    var all_tabs = $("li.x-tab-with-icon");
    for (var j=0; j<all_tabs.length; j++) {
        total_tabs_w += $(all_tabs[j]).outerWidth();
    }
    if (og.padding_rigth > 2){
        og.padding_rigth --
        flagChanges = true;
    }
    if (og.font_size> 9){
        og.font_size --
        flagChanges = true;
    }
    if (og.padding < 6){
        if (!flagChanges && (container_w < total_tabs_w)){
            $(".x-tab-with-icon .x-tab-strip-text").css('width', '0px');
            $(".x-tab-with-icon.x-tab-strip-active .x-tab-strip-text").css('width', 'auto');
            $(".x-tab-with-icon .x-tab-left").css('padding-right', '0px');
            $(".x-tab-with-icon.x-tab-strip-active .x-tab-left").css('padding-right', '10px');
        }
        callback(false,flagChanges);
    }else{
        og.padding --
        flagChanges = true;
        $(".x-tab-with-icon. .x-tab-strip-text").css('width', 'auto');
        og.changeTabsSize(og.font_size,og.padding,og.padding_rigth);
        callback(true,flagChanges);
    }
}

og.changeTabsSize = function (font_size,padding,padding_rigth) {
    $(".x-tab-with-icon .x-tab-left").css('padding-right', padding_rigth+'px');
    $(".x-tab-with-icon.x-tab-strip-active .x-tab-left").css('padding-right', padding_rigth+'px');
    $(".x-tab-with-icon").css('padding-left', padding+'px');
    $(".x-tab-with-icon").css('padding-right', padding+'px');
    $(".x-tab-strip-text").css('font-size', font_size+'px');
}