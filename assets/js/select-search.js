document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('form select.form-select');

    selects.forEach(function (select) {
        if (select.closest('.ss-wrapper')) {
            return;
        }

        if (select.dataset.searchable === 'false' || select.hasAttribute('data-no-search')) {
            return;
        }

        const name = (select.name || '').toLowerCase();
        if (name === 'por_pagina' || name === 'pagina' || name === 'limit' || name === 'page' || name === 'tipo_movimiento') {
            return;
        }

        if (select.options.length <= 2) {
            return;
        }

        const options = Array.from(select.options).map(function (option) {
            return {
                value: option.value,
                label: option.textContent.trim(),
                disabled: option.disabled,
                selected: option.selected
            };
        }).filter(function (option) {
            return !(option.value === '' && !option.selected);
        });

        const wrapper = document.createElement('div');
        wrapper.className = 'ss-wrapper';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control ss-input';
        input.setAttribute('autocomplete', 'off');

        const placeholderOption = select.querySelector('option[disabled]');
        input.placeholder = placeholderOption ? placeholderOption.textContent.trim() : 'Buscar...';

        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            input.value = selectedOption.textContent.trim();
        }

        const dropdown = document.createElement('ul');
        dropdown.className = 'ss-dropdown list-group';

        let highlightedIndex = -1;

        function renderItems(filterText) {
            dropdown.innerHTML = '';
            const query = (filterText || '').toLowerCase().trim();
            const matches = options.filter(function (option) {
                return !option.disabled && (query === '' || option.label.toLowerCase().includes(query));
            });

            matches.forEach(function (option, index) {
                const item = document.createElement('li');
                item.className = 'list-group-item list-group-item-action ss-item';
                item.style.cursor = 'pointer';
                item.textContent = option.label;
                item.dataset.value = option.value;
                if (index === highlightedIndex) {
                    item.classList.add('active');
                }
                dropdown.appendChild(item);
            });

            if (matches.length === 0) {
                const item = document.createElement('li');
                item.className = 'list-group-item text-muted';
                item.textContent = 'Sin coincidencias';
                dropdown.appendChild(item);
            }
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            highlightedIndex = -1;
        }

        function selectValue(value) {
            const targetOption = Array.from(select.options).find(function (option) {
                return option.value === value;
            });

            if (!targetOption) {
                return;
            }

            select.value = value;
            input.value = targetOption.textContent.trim();
            closeDropdown();
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(input);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(select);
        select.style.display = 'none';
        select.dataset.searchable = 'true';

        input.addEventListener('focus', function () {
            renderItems(this.value);
            dropdown.style.display = 'block';
        });

        input.addEventListener('input', function () {
            highlightedIndex = -1;
            renderItems(this.value);
            dropdown.style.display = 'block';
        });

        input.addEventListener('keydown', function (event) {
            const items = dropdown.querySelectorAll('.ss-item');
            if (!items.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlightedIndex = highlightedIndex < items.length - 1 ? highlightedIndex + 1 : 0;
                items.forEach(function (item, index) {
                    item.classList.toggle('active', index === highlightedIndex);
                });
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlightedIndex = highlightedIndex > 0 ? highlightedIndex - 1 : items.length - 1;
                items.forEach(function (item, index) {
                    item.classList.toggle('active', index === highlightedIndex);
                });
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (highlightedIndex >= 0) {
                    const selectedItem = items[highlightedIndex];
                    if (selectedItem && selectedItem.dataset.value) {
                        selectValue(selectedItem.dataset.value);
                    }
                } else {
                    const match = options.find(function (option) {
                        return option.label.toLowerCase().includes(input.value.trim().toLowerCase());
                    });
                    if (match) {
                        selectValue(match.value);
                    }
                }
            } else if (event.key === 'Escape') {
                closeDropdown();
            }
        });

        dropdown.addEventListener('click', function (event) {
            const item = event.target.closest('.ss-item');
            if (!item || !item.dataset.value) {
                return;
            }
            selectValue(item.dataset.value);
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                const activeElement = document.activeElement;
                if (!wrapper.contains(activeElement)) {
                    closeDropdown();
                }
            }, 120);
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                closeDropdown();
            }
        });

        const form = select.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                const typedValue = input.value.trim();
                if (!select.value && typedValue !== '') {
                    const match = options.find(function (option) {
                        return option.label.toLowerCase().includes(typedValue.toLowerCase());
                    });
                    if (match) {
                        selectValue(match.value);
                    }
                }
            });
        }

        renderItems(input.value);
    });
});
