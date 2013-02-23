$(document).ready(function(){
    
    $('.ajax-typeahead').typeahead({
        source: function(query, process) {
            return $.ajax({
                url: $(this)[0].$element[0].dataset.link,
                type: 'get',
                data: {
                    query: query
                },
                dataType: 'json',
                accepts: {
                    json: 'application/json; request=twitter.typeahead'
                },
                success: function(json) {
                    return typeof json.options == 'undefined' ? false : process(json.options);
                }
            });
        },
        minLength: 3
    });
    
});