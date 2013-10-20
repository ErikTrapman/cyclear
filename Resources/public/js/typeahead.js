$(document).ready(function () {

    $(document).on("focus", ".ajax-typeahead", function () {
        $(this).typeahead({
            source: function (query, process) {
                return $.ajax({
                    url: $(this)[0].$element[0].dataset.link,
                    type: 'get',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function (json) {
                        var options = [];
                        for (i in json) {
                            options.push(json[i].naam);
                        }
                        typeof json == 'undefined' ? false : process(options);
                        return;
                    }
                });
            },
            minLength: 3,
            items: 20
        });
    });
});