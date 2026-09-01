document.addEventListener('DOMContentLoaded', function() {
    // Transformar selects dentro de formularios en input con datalist para búsqueda rápida
    const selects = document.querySelectorAll('form select.form-select');

    selects.forEach(function(select) {
        // No volver a transformar si ya se hizo
        if (select.dataset.searchable === 'true') return;

        // Recolectar opciones
        document.addEventListener('DOMContentLoaded', function() {
            // Aplicar autocompletado avanzado a selects dentro de formularios
            const selects = document.querySelectorAll('form select.form-select');

            selects.forEach(function(select) {
                if (select.dataset.searchable === 'true') return;

                const options = Array.from(select.options).map(o => ({ value: o.value, label: o.textContent.trim(), disabled: o.disabled }));

                // Crear wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'ss-wrapper';
                wrapper.style.position = 'relative';

                // Crear input visible
                const input = document.createElement('input');
                input.type = 'text';
                input.className = select.className + ' ss-input';
                input.setAttribute('autocomplete', 'off');
                input.placeholder = select.querySelector('option[disabled]') ? select.querySelector('option[disabled]').textContent.trim() : '';
                // Si hay una opción seleccionada en el select, mostrarla en el input visible
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    input.value = selectedOption.textContent.trim();
                }

                // Crear dropdown
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

                // Llenar dropdown con items
                function renderItems(filter) {
                    dropdown.innerHTML = '';
                    const q = (filter || '').toLowerCase();
                    const matches = options.filter(o => !o.disabled && (q === '' || o.label.toLowerCase().includes(q)));
                    matches.forEach(o => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action ss-item';
                        li.style.cursor = 'pointer';
                        li.textContent = o.label;
                        li.dataset.value = o.value;
                        dropdown.appendChild(li);
                    });
                    if (matches.length === 0) {
                        const li = document.createElement('li');
                        li.className = 'list-group-item text-muted';
                        li.textContent = 'No hay coincidencias';
                        dropdown.appendChild(li);
                    }
                }

                // Insertar wrapper antes del select y mover select dentro
                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(input);
                wrapper.appendChild(dropdown);
                wrapper.appendChild(select);
                select.style.display = 'none';
                select.dataset.searchable = 'true';

                // Mostrar dropdown al enfocar
                input.addEventListener('focus', function() {
                    renderItems('');
                    dropdown.style.display = 'block';
                });

                // Filtrado en vivo
                let highlighted = null;
                input.addEventListener('input', function() {
                    renderItems(this.value);
                    highlighted = null;
                    dropdown.style.display = 'block';
                });

                // Navegación por teclado
                input.addEventListener('keydown', function(e) {
                    const items = dropdown.querySelectorAll('.ss-item');
                    if (!items.length) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (highlighted === null) highlighted = 0;
                        else highlighted = Math.min(highlighted + 1, items.length - 1);
                        items.forEach((it, idx) => it.classList.toggle('active', idx === highlighted));
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (highlighted === null) highlighted = items.length - 1;
                        else highlighted = Math.max(highlighted - 1, 0);
                        items.forEach((it, idx) => it.classList.toggle('active', idx === highlighted));
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (highlighted !== null) {
                            const it = items[highlighted];
                            select.value = it.dataset.value;
                            input.value = it.textContent;
                            dropdown.style.display = 'none';
                        }
                    } else if (e.key === 'Escape') {
                        dropdown.style.display = 'none';
                    }
                });

                // Click en item
                dropdown.addEventListener('click', function(e) {
                    const li = e.target.closest('.ss-item');
                    if (!li) return;
                    select.value = li.dataset.value;
                    input.value = li.textContent;
                    dropdown.style.display = 'none';
                });

                // Click fuera -> ocultar
                document.addEventListener('click', function(e) {
                    if (!wrapper.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });

                // Al enviar el formulario, si select vacío intentar encontrar por texto
                const form = select.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        if (!select.value && input.value.trim() !== '') {
                            const match = options.find(o => o.label.toLowerCase().includes(input.value.trim().toLowerCase()));
                            if (match) select.value = match.value;
                        }
                    });
                }
            });
        });
