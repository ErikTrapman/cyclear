$(document).ready(function() {
    
    
    $("a[data-toggle='tab']").on("shown", function(e) {
        var hash = $(e.target).attr("href");
        if (hash.substr(0, 1) === "#") {
            location.replace("#!" + hash.substr(1));
        }
    });

    if (location.hash.substr(0, 2) === "#!") {
        $("a[href='#" + location.hash.substr(2) + "']").tab("show");
    }
    
    /**
     * 
     * @see http://redotheweb.com/2012/05/17/enable-back-button-handling-with-twitter-bootstrap-tabs-plugin.html
     */
    // add a hash to the URL when the user clicks on a tab
    $('a[data-toggle="tab"]').on('click', function(e) {
        var href = $(this).attr('href');
        history.pushState(null, null, href);
        
    });

    // navigate to a tab when the history changes
    window.addEventListener("popstate", function(e) {
        //var activeTab = $('[href=' + location.hash + ']');
        if (location.hash.substr(0, 2) === "#!") {
            var activeTab = $('[href=' + location.hash.replace('!','') + ']');
        } else {
            var activeTab = $('[href=' + location.hash + ']');
        }
        if (activeTab.length) {
            activeTab.tab('show');
        } else {
            $('.nav-tabs a:first').tab('show');
        }
    });
    
    $(".pagination a").on('click', function(e) {
        if (location.hash.substr(0, 2) === "#!") {
            $(this).attr('href', $(this).attr('href') + location.hash);
        }
    });

});

