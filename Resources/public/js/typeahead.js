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
                success: function(json) {
                    var options = [];
                    for(i in json){
                        options.push(json[i].naam);
                    }
                    typeof json == 'undefined' ? false : process(options);
                    return;
                }
            });
        },
        minLength: 3,
        items: 20 /* TODO how to get this from config.yml? */
    });
    
});