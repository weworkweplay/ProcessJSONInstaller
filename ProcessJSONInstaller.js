(function($, window, undefined) { // safe closure

    $(function() { // dom loaded

        var $uninstallLinks = $("a[href*='uninstall']");

        $uninstallLinks.on("click", function(){

            var confirmMessage = [
                "Warning: This feature is experimental."
                , ""
                , "The uninstall process will try to delete all pages, templates and fields defined in this module json, so if you included directives to merely modify existing pages, templates or fields, you should add a 'prefab' property to each of those and set it to 'true'."
            ];

            return confirmed = confirm(confirmMessage.join("\n"));

        });

    });

})(jQuery, document.window);