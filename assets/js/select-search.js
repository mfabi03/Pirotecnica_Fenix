document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('form select.form-select');

    selects.forEach(function (select) {
        if (select.dataset.searchable === 'true' || !select.parentNode) {
            return;
        }

        const options = Array.from(select.options).map(function (option) {
            return {
                value: option.value,
                label: option.textContent.trim(),
                disabled: option.disabled
            };
        });

        const wrapper = document.createElement('div');
        wrapper.className = 'ss-wrapper';
        wrapper.style.position = 'relative';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = select.className + ' ss-input';
        input.setAttribute('autocomplete', 'off');

        const disabledOption = select.querySelector('option[disabled]');
        input.placeholder = disabledOption ? disabledOption.textContent.trim() : '';

        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            input.value = selectedOption.textContent.trim();
        }

        const dropdown = document.createElement('ul');
        dropdown.className = 'ss-dropdown list-group';
        dropdown.style.position = 'absolute';
        dropdown.style.zIndex = 1050;
        dropdown.style.display = 'none';
        dropdown.style.maxHeight = '220px';
        dropdown.style.overflowY = 'auto';
        dropdown.style.left = '0';
        dropdown.style.right = '0';
        dropdown.style.marginTop = '4px';

        function renderItems(filter) {
            dropdown.innerHTML = '';
            const query = (filter || '').toLowerCase();
            const matches = options.filter(function (option) {
                return !option.disabled && (query === '' || option.label.toLowerCase().includes(query));
            });

            matches.forEach(function (option) {
                const item = document.createElement('li');
                item.className = 'list-group-item list-group-item-action ss-item';
                item.style.cursor = 'pointer';
                item.textContent = option.label;
                item.dataset.value = option.value;
                dropdown.appendChild(item);
            });

            if (matches.length === 0) {
                const item = document.createElement('li');
                item.className = 'list-group-item text-muted';
                item.textContent = 'No hay coincidencias';
                dropdown.appendChild(item);
            }
        }

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(input);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(select);
        select.style.display = 'none';
        select.dataset.searchable = 'true';

        input.addEventListener('focus', function () {
            renderItems('');
            dropdown.style.display = 'block';
        });

        let highlighted = null;
        input.addEventListener('input', function () {
            renderItems(this.value);
            highlighted = null;
            dropdown.style.display = 'block';
        });

        input.addEventListener('keydown', function (event) {
            const items = dropdown.querySelectorAll('.ss-item');
            if (!items.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (highlighted === null) {
                    highlighted = 0;
                } else {
                    highlighted = Math.min(highlighted + 1, items.length - 1);
                }
                items.forEach(function (item, index) {
                    item.classList.toggle('active', index === highlighted);
                });
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (highlighted === null) {
                    highlighted = items.length - 1;
                } else {
                    highlighted = Math.max(highlighted - 1, 0);
                }
                items.forEach(function (item, index) {
                    item.classList.toggle('active', index === highlighted);
                });
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (highlighted !== null) {
                    const item = items[highlighted];
                    select.value = item.dataset.value;
                    input.value = item.textContent;
                    dropdown.style.display = 'none';
                }
            } else if (event.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        dropdown.addEventListener('click', function (event) {
            const item = event.target.closest('.ss-item');
            if (!item) {
                return;
            }
            select.value = item.dataset.value;
            input.value = item.textContent;
            dropdown.style.display = 'none';
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        const form = select.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                if (!select.value && input.value.trim() !== '') {
                    const match = options.find(function (option) {
                        return option.label.toLowerCase().includes(input.value.trim().toLowerCase());
                    });
                    if (match) {
                        select.value = match.value;
                    }
                }
            });
        }
    });
});
