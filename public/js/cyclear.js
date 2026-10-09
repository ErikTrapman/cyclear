// Bootstrap 3 data-api replacements: alert dismiss, collapse, dropdown and tabs.
document.addEventListener('click', function (event) {
    var dismiss = event.target.closest('[data-dismiss="alert"]');
    if (dismiss) {
        event.preventDefault();
        var alert = dismiss.closest('.alert');
        if (alert) {
            alert.remove();
        }
        return;
    }

    var collapse = event.target.closest('[data-toggle="collapse"]');
    if (collapse) {
        event.preventDefault();
        var target = document.querySelector(collapse.getAttribute('data-target') || collapse.getAttribute('href'));
        if (target) {
            var open = target.classList.toggle('in');
            collapse.classList.toggle('collapsed', !open);
            collapse.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        return;
    }

    var tab = event.target.closest('[data-toggle="tab"]');
    if (tab) {
        event.preventDefault();
        var li = tab.closest('li');
        Array.prototype.forEach.call(li.parentNode.children, function (sibling) {
            sibling.classList.remove('active');
        });
        li.classList.add('active');
        var pane = document.querySelector(tab.getAttribute('data-target') || tab.getAttribute('href'));
        if (pane) {
            Array.prototype.forEach.call(pane.parentNode.children, function (sibling) {
                sibling.classList.remove('active', 'in');
            });
            pane.classList.add('active', 'in');
        }
        return;
    }

    var dropdownToggle = event.target.closest('[data-toggle="dropdown"]');
    var current = dropdownToggle ? dropdownToggle.parentNode : null;
    document.querySelectorAll('.dropdown.open, .btn-group.open').forEach(function (dropdown) {
        if (dropdown !== current) {
            dropdown.classList.remove('open');
        }
    });
    if (current) {
        event.preventDefault();
        current.classList.toggle('open');
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.dropdown.open, .btn-group.open').forEach(function (dropdown) {
            dropdown.classList.remove('open');
        });
    }
});

// Rider autocomplete for inputs with the .ajax-typeahead class.
function riderTypeahead(input) {
    var wrapper = document.createElement('span');
    wrapper.className = 'twitter-typeahead';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    var menu = document.createElement('div');
    menu.className = 'tt-menu';
    wrapper.appendChild(menu);

    var results = [];
    var cursor = -1;
    var timer = null;
    var requestId = 0;

    function close() {
        menu.style.display = 'none';
        cursor = -1;
    }

    function setCursor(index) {
        var items = menu.querySelectorAll('.tt-suggestion');
        items.forEach(function (item, i) {
            item.classList.toggle('tt-cursor', i === index);
        });
        cursor = index;
    }

    function select(rider) {
        input.value = rider.value;
        close();
        if (input.hasAttribute('do-redirect')) {
            window.location = '/' + seizoenSlug + '/renner/' + rider.slug;
        }
    }

    function render() {
        menu.innerHTML = '';
        if (results.length === 0) {
            var empty = document.createElement('div');
            empty.className = 'empty-message';
            empty.textContent = 'No riders matching this criterium';
            menu.appendChild(empty);
        }
        results.forEach(function (rider) {
            var item = document.createElement('div');
            item.className = 'tt-suggestion';
            var name = document.createElement('strong');
            name.textContent = rider.name;
            item.append('[' + rider.identifier + '] ', name);
            item.addEventListener('mousedown', function (event) {
                event.preventDefault();
                select(rider);
            });
            menu.appendChild(item);
        });
        cursor = -1;
        menu.style.display = 'block';
    }

    function search() {
        var query = input.value.trim();
        if (query === '') {
            close();
            return;
        }
        var id = ++requestId;
        fetch('/renners/get?query=' + encodeURIComponent(query))
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (id === requestId) {
                    results = data;
                    render();
                }
            });
    }

    input.setAttribute('autocomplete', 'off');
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(search, 300);
    });
    input.addEventListener('blur', close);
    input.addEventListener('keydown', function (event) {
        if (menu.style.display !== 'block' || results.length === 0) {
            return;
        }
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setCursor((cursor + 1) % results.length);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setCursor(cursor <= 0 ? results.length - 1 : cursor - 1);
        } else if (event.key === 'Enter' && cursor >= 0) {
            event.preventDefault();
            select(results[cursor]);
        } else if (event.key === 'Escape') {
            close();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ajax-typeahead').forEach(riderTypeahead);
});
