// Command palette: open with "/", Ctrl+K or a [data-palette-open] button. Searches riders and pages, and
// makes a transfer in three steps (rider in, rider out, confirm) without leaving the page.
(function () {
    'use strict';

    var configElement = document.getElementById('palette-config');
    if (!configElement) {
        return;
    }
    var config = JSON.parse(configElement.textContent);

    // Steps of the open palette; history.state.palette is the index of the shown step, so the browser back
    // button and the back swipe on phones go back one step.
    var steps = [];
    var current = -1;
    // Identifies the history entries of this opening, so entries left behind by an earlier opening (for example
    // before a reload) are not mistaken for steps.
    var session = 0;
    var items = [];
    var cursor = -1;
    var timer = null;
    var requestId = 0;
    var team = null;

    var overlay = element('div', 'palette-overlay');
    var dialog = element('div', 'palette');
    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-label', 'Zoek renner of pagina');

    var header = element('div', 'palette-header');
    var backButton = element('button', 'palette-icon-button palette-back');
    backButton.type = 'button';
    backButton.setAttribute('aria-label', 'Terug');
    var input = element('input', 'palette-input');
    input.type = 'search';
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('autocorrect', 'off');
    input.setAttribute('autocapitalize', 'off');
    input.setAttribute('spellcheck', 'false');
    input.setAttribute('enterkeyhint', 'search');
    var closeButton = element('button', 'palette-icon-button palette-close');
    closeButton.type = 'button';
    closeButton.setAttribute('aria-label', 'Sluiten');
    closeButton.innerHTML = '&times;';
    header.append(backButton, input, closeButton);

    var title = element('div', 'palette-title');
    var body = element('div', 'palette-body');
    var footer = element('div', 'palette-footer');
    dialog.append(header, title, body, footer);
    overlay.appendChild(dialog);

    function element(tag, className) {
        var el = document.createElement(tag);
        if (className) {
            el.className = className;
        }
        return el;
    }

    function isOpen() {
        return overlay.classList.contains('palette-visible');
    }

    function open() {
        if (isOpen()) {
            input.focus();
            return;
        }
        if (!overlay.parentNode) {
            document.body.appendChild(overlay);
        }
        team = null;
        steps = [{type: 'search', query: ''}];
        current = 0;
        session = Date.now();
        history.pushState({palette: 0, paletteSession: session}, '');
        overlay.classList.add('palette-visible');
        document.body.classList.add('palette-open');
        show();
        // Focus within the same tap or key press, otherwise iOS does not open the keyboard.
        input.focus();
    }

    function hide() {
        overlay.classList.remove('palette-visible');
        document.body.classList.remove('palette-open');
        steps = [];
        current = -1;
        clearTimeout(timer);
        requestId++;
    }

    function close() {
        if (current >= 0) {
            history.go(-(current + 1));
        } else {
            hide();
        }
    }

    function back() {
        if (current > 0) {
            history.back();
        } else {
            close();
        }
    }

    function push(step) {
        steps = steps.slice(0, current + 1);
        steps.push(step);
        current = steps.length - 1;
        history.pushState({palette: current, paletteSession: session}, '');
        show();
        input.focus();
    }

    window.addEventListener('popstate', function (event) {
        if (!isOpen()) {
            return;
        }
        var state = event.state;
        if (state && state.paletteSession === session && steps[state.palette]) {
            current = state.palette;
            show();
            if (step().type !== 'confirm') {
                input.focus();
            }
        } else {
            hide();
        }
    });

    function step() {
        return steps[current];
    }

    // Renders the current step.
    function show() {
        var s = step();
        // At the first step the back button shows a search icon instead.
        backButton.disabled = current === 0;
        backButton.innerHTML = current > 0
            ? '<span class="glyphicon glyphicon-arrow-left"></span>'
            : '<span class="glyphicon glyphicon-search"></span>';
        input.style.display = s.type === 'confirm' ? 'none' : '';
        input.value = s.query || '';
        footer.textContent = '';
        if (s.type === 'search') {
            input.placeholder = 'Zoek renner of pagina…';
            setTitle('');
            footer.textContent = '↑↓ kiezen · ↵ actie · ⇧↵ bekijk renner · esc sluiten';
        } else if (s.type === 'pick') {
            input.placeholder = s.direction === 'in' ? 'Filter je renners…' : 'Zoek een beschikbare renner…';
            setTitle(s.direction === 'in'
                ? s.rider.name + ' komt erin. Wie gaat eruit?'
                : s.rider.name + ' gaat eruit. Wie komt erin?');
            footer.textContent = '↑↓ kiezen · ↵ kies · esc terug';
        }
        refresh();
    }

    function setTitle(text) {
        title.textContent = text;
        title.style.display = text ? '' : 'none';
    }

    // Loads the list for the current step, debounced while typing.
    function refresh(debounce) {
        clearTimeout(timer);
        var s = step();
        if (s.type === 'confirm') {
            renderConfirm(s);
        } else if (debounce) {
            // wait for a pause in typing, so the list does not jump around on every keystroke
            timer = setTimeout(load, 400);
        } else {
            load();
        }
    }

    function load() {
        var s = step();
        var id = ++requestId;
        var query = input.value.trim();
        s.query = input.value;
        if (s.type === 'search') {
            var links = config.links.filter(function (link) {
                return link.label.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            });
            if (query.length < 2 || !config.ridersUrl) {
                render(links.map(linkItem), query.length < 2 ? null : 'Geen renners gevonden');
                return;
            }
            render(links.map(linkItem), 'Zoeken…');
            getJson(config.ridersUrl + '?q=' + encodeURIComponent(query)).then(function (data) {
                if (id === requestId) {
                    render(links.map(linkItem).concat(data.riders.map(riderItem)), 'Geen renners gevonden');
                }
            }, function () {
                if (id === requestId) {
                    render([], 'Er ging iets mis, probeer het opnieuw.');
                }
            });
        } else if (s.direction === 'in') {
            loadTeam().then(function () {
                if (id !== requestId) {
                    return;
                }
                var riders = team.riders.filter(function (rider) {
                    return rider.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
                });
                render(riders.map(function (rider) {
                    return pickItem(rider, {rennerIn: s.rider, rennerUit: rider});
                }), 'Geen renners gevonden');
            }, function () {
                render([], 'Er ging iets mis, probeer het opnieuw.');
            });
        } else {
            if (query.length < 2) {
                render([], 'Typ minstens twee letters van een beschikbare renner.');
                return;
            }
            getJson(config.ridersUrl + '?available=1&q=' + encodeURIComponent(query)).then(function (data) {
                if (id === requestId) {
                    render(data.riders.map(function (rider) {
                        return pickItem(rider, {rennerIn: rider, rennerUit: s.rider});
                    }), 'Geen beschikbare renners gevonden');
                }
            }, function () {
                render([], 'Er ging iets mis, probeer het opnieuw.');
            });
        }
    }

    function loadTeam() {
        if (team) {
            return Promise.resolve(team);
        }
        return getJson(config.team.ridersUrl).then(function (data) {
            team = data;
            return team;
        });
    }

    function getJson(url) {
        return fetch(url, {headers: {Accept: 'application/json'}, credentials: 'same-origin'}).then(function (response) {
            if (!response.ok) {
                throw new Error(response.status);
            }
            return response.json();
        });
    }

    // An item is {el, primary, secondary}: Enter runs primary, Shift+Enter runs secondary.
    function render(list, emptyMessage) {
        items = list;
        body.textContent = '';
        list.forEach(function (item, index) {
            item.el.addEventListener('mousemove', function () {
                if (cursor !== index) {
                    setCursor(index);
                }
            });
            body.appendChild(item.el);
        });
        if (emptyMessage && !list.some(function (item) {
            return item.rider;
        })) {
            var empty = element('div', 'palette-empty');
            empty.textContent = emptyMessage;
            body.appendChild(empty);
        }
        setCursor(list.length > 0 ? 0 : -1);
    }

    function setCursor(index) {
        cursor = index;
        items.forEach(function (item, i) {
            item.el.classList.toggle('palette-active', i === index);
        });
        if (index >= 0 && items[index].el.scrollIntoView) {
            items[index].el.scrollIntoView({block: 'nearest'});
        }
    }

    function linkItem(link) {
        var el = element('a', 'palette-item palette-link');
        el.href = link.url;
        el.innerHTML = '<span class="glyphicon glyphicon-share-alt"></span> ';
        el.appendChild(document.createTextNode(link.label));
        return {el: el, primary: function () {
            window.location.href = link.url;
        }};
    }

    function riderRow(rider, meta) {
        var el = element('div', 'palette-item palette-rider');
        var main = element('div', 'palette-rider-main');
        var name = element('div', 'palette-rider-name');
        if (rider.country) {
            var flag = element('img', 'palette-flag');
            flag.src = '/icons/flags/16/' + rider.country + '.png';
            flag.alt = '';
            flag.width = 16;
            flag.height = 16;
            name.appendChild(flag);
        }
        name.appendChild(document.createTextNode(rider.name));
        var details = element('div', 'palette-rider-meta');
        details.textContent = meta + ' · ' + rider.points + ' pts';
        main.append(name, details);
        el.appendChild(main);
        return el;
    }

    function actionButton(label, className, action) {
        var button = element('button', 'btn btn-sm ' + className);
        button.type = 'button';
        button.textContent = label;
        onActivate(button, action);
        return button;
    }

    // The same transfer icon as on the team page, green to bring a rider in and red to sell one.
    function transferIcon(label, color, action) {
        var link = element('a', 'palette-transfer');
        link.href = '#';
        link.title = label;
        link.setAttribute('aria-label', label);
        var icon = element('span', 'glyphicon glyphicon-transfer');
        icon.style.color = color;
        link.appendChild(icon);
        onActivate(link, function () {
            action();
        });
        return link;
    }

    function onActivate(el, action) {
        el.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            action();
        });
    }

    function riderItem(rider) {
        var meta = rider.mine ? 'Jouw renner' : (rider.team ? rider.team.name : 'Beschikbaar');
        var el = riderRow(rider, meta);
        var view = function () {
            window.location.href = rider.url;
        };
        var primary = view;
        var actions = element('div', 'palette-actions');
        if (config.team && !rider.team) {
            primary = function () {
                push({type: 'pick', direction: 'in', rider: rider, query: ''});
            };
            actions.appendChild(transferIcon('Haal binnen', 'green', primary));
        } else if (config.team && rider.mine) {
            primary = function () {
                push({type: 'pick', direction: 'out', rider: rider, query: ''});
            };
            actions.appendChild(transferIcon('Verkoop', 'red', primary));
        }
        actions.appendChild(actionButton('Bekijk', 'btn-default', view));
        el.appendChild(actions);
        // Tapping the row itself opens the rider page; the buttons do the actions.
        el.addEventListener('click', view);
        return {el: el, rider: rider, primary: primary, secondary: view};
    }

    function pickItem(rider, transfer) {
        var meta = rider.since ? 'In je ploeg sinds ' + formatDate(rider.since) : (rider.team ? rider.team.name : 'Beschikbaar');
        var el = riderRow(rider, meta);
        var choose = function () {
            push({type: 'confirm', rennerIn: transfer.rennerIn, rennerUit: transfer.rennerUit, comment: ''});
        };
        el.classList.add('palette-choice');
        el.addEventListener('click', choose);
        return {el: el, rider: rider, primary: choose};
    }

    function formatDate(isoDate) {
        var date = new Date(isoDate + 'T00:00:00');
        return date.toLocaleDateString('nl-NL', {day: 'numeric', month: 'short'});
    }

    function renderConfirm(s) {
        items = [];
        cursor = -1;
        setTitle('Transfer bevestigen');
        body.textContent = 'Laden…';
        var id = ++requestId;
        loadTeam().then(function () {
            if (id !== requestId) {
                return;
            }
            body.textContent = '';
            var summary = element('div', 'palette-confirm');
            summary.append(
                confirmRow('In', 'text-success', s.rennerIn),
                confirmRow('Uit', 'text-danger', s.rennerUit)
            );
            var left = element('p', 'palette-transfers-left');
            left.textContent = team.transfersLeft > 0
                ? 'Nog ' + team.transfersLeft + (team.transfersLeft === 1 ? ' transfer' : ' transfers') + ' over, deze telt mee.'
                : 'Je hebt geen transfers meer over.';
            var comment = element('input', 'form-control palette-comment');
            comment.type = 'text';
            comment.maxLength = 255;
            comment.placeholder = 'Commentaar (optioneel)';
            comment.value = s.comment;
            comment.setAttribute('enterkeyhint', 'done');
            comment.addEventListener('input', function () {
                s.comment = comment.value;
            });
            var errors = element('div', 'palette-errors');
            var confirm = element('button', 'btn btn-primary btn-lg btn-block palette-confirm-button');
            confirm.type = 'button';
            confirm.textContent = 'Bevestig transfer';
            confirm.disabled = team.transfersLeft <= 0;
            var submit = function () {
                if (confirm.disabled) {
                    return;
                }
                confirm.disabled = true;
                confirm.textContent = 'Bezig…';
                errors.textContent = '';
                doTransfer(s).then(function (result) {
                    confirm.textContent = result.message;
                    window.location.href = result.url;
                }, function (messages) {
                    confirm.disabled = false;
                    confirm.textContent = 'Bevestig transfer';
                    messages.forEach(function (message) {
                        var line = element('div', 'alert alert-danger');
                        line.textContent = message;
                        errors.appendChild(line);
                    });
                });
            };
            confirm.addEventListener('click', submit);
            comment.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submit();
                }
            });
            summary.append(left, comment, errors, confirm);
            body.appendChild(summary);
            footer.textContent = '↵ bevestig · esc terug';
            // Only focus on devices with a keyboard; on phones the keyboard would cover the confirm button.
            if (window.matchMedia('(min-width: 768px)').matches) {
                comment.focus();
            } else {
                input.blur();
            }
        }, function () {
            body.textContent = 'Er ging iets mis, probeer het opnieuw.';
        });
    }

    function confirmRow(label, className, rider) {
        var row = element('div', 'palette-confirm-row');
        var tag = element('strong', 'palette-confirm-label ' + className);
        tag.textContent = label;
        row.appendChild(tag);
        row.appendChild(document.createTextNode(rider.name + ' · ' + rider.points + ' pts'));
        return row;
    }

    function doTransfer(s) {
        return fetch(config.team.transferUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-Token': config.team.token,
            },
            body: JSON.stringify({rennerIn: s.rennerIn.slug, rennerUit: s.rennerUit.slug, comment: s.comment || null}),
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (response.ok) {
                    return data;
                }
                throw data.errors || ['Er ging iets mis, probeer het opnieuw.'];
            });
        });
    }

    input.addEventListener('input', function () {
        refresh(true);
    });

    document.addEventListener('keydown', function (event) {
        if (!isOpen()) {
            return;
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            back();
        } else if (event.target === input && event.key === 'Backspace' && input.value === '' && current > 0) {
            event.preventDefault();
            back();
        } else if (event.target === input && event.key === 'ArrowDown' && items.length) {
            event.preventDefault();
            setCursor((cursor + 1) % items.length);
        } else if (event.target === input && event.key === 'ArrowUp' && items.length) {
            event.preventDefault();
            setCursor(cursor <= 0 ? items.length - 1 : cursor - 1);
        } else if (event.target === input && event.key === 'Enter' && cursor >= 0) {
            event.preventDefault();
            var item = items[cursor];
            (event.shiftKey && item.secondary ? item.secondary : item.primary)();
        }
    });

    backButton.addEventListener('click', back);
    closeButton.addEventListener('click', close);
    overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
            close();
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-palette-open]')) {
            event.preventDefault();
            open();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (isOpen()) {
            return;
        }
        var target = event.target;
        var typing = target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName);
        if (event.key === '/' && !typing && !event.ctrlKey && !event.metaKey && !event.altKey) {
            event.preventDefault();
            open();
        } else if (event.key.toLowerCase() === 'k' && (event.ctrlKey || event.metaKey) && !event.altKey) {
            event.preventDefault();
            open();
        }
    });
})();
