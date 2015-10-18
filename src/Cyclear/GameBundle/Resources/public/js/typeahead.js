$(document).ready(function () {


    var defaultRiderGet = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: '/renners/get?query=%QUERY',
            wildcard: '%QUERY'
        }
    });

    $('.ajax-typeahead').typeahead(null, {
        name: 'default-rider-get',
        display: 'value',
        limit: 20,
        highlight: true,
        hint: true,
        source: defaultRiderGet,
        placeholder: 'Renner...',
        templates: {
            empty: [
                '<div class="empty-message">',
                'No riders matching this criterium',
                '</div>'
            ].join('\n'),
            suggestion: function (data) {
                //return '<div><a href="/' + seizoenSlug + '/renner/' + data.slug + '">' + data.name + '</a></div>';
                return '<div>[' + data.identifier + '] <strong>' + data.name + '</strong></div>';
            }
        }
    }).bind('typeahead:selected', function (event, obj) {
        if (event.currentTarget.hasAttribute('do-redirect')) {
            event.preventDefault();
            window.location = Routing.generate('renner_show', {seizoen: seizoenSlug, renner: obj.slug});
        }
    });


    return;

    $(".ajax-typeahead").typeahead(
        {
            minLength: 3,
            items: 20
        },
        {
            source: function (query, process) {
                return $.ajax({
                    url: $(this).data('link'),
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
            }

        });
});