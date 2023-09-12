og.render_script_for_budget_by_tasks_report = function() {
    $(document).ready(function() {
        // Register click event on the collapse icon
        $(".parentSwitch").click(function() {
            const parentRow = $(this).closest("tr");
            const parentIndex = parentRow.index();
            // Toggle the icon
            var action = "open";
            if ($(this).data("status") === "closed") {
                $(this).attr("src", "./plugins/project_reports/public/assets/images/close.svg");
                $(this).data("status", "open");
                parentRow.addClass("open");
            } else {
                $(this).attr("src", "./plugins/project_reports/public/assets/images/open.svg");
                $(this).data("status", "closed");
                action = "close";
                parentRow.removeClass("open");
            } 

            // Toggle child rows
            parentRow.nextAll("tr").each(function() {
                if(action == "open") {
                    if ($(this).data("level") > parentRow.data("level")) {
                        if($(this).data("level") - parentRow.data("level") == 1) {
                            $(this).toggle();
                        }
                    } else {
                        return false; // Exit the loop
                    }
                } else {
                    if ($(this).data("level") > parentRow.data("level")) {
                        if($(this).is(":visible")) {
                            $(this).toggle();
                        }
                        if($(this).find(".parentSwitch").length !== 0) {
                            $(this).find(".parentSwitch").attr("src", "./plugins/project_reports/public/assets/images/open.svg");
                            $(this).find(".parentSwitch").data("status", "closed");
                        }
                    } else {
                        return false; // Exit the loop
                    }
                }
            });
        });
    });
}
