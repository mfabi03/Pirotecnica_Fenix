(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ============================================================
        // 1. REGISTRAR ROL
        // ============================================================
        var formRegistrar = document.getElementById('formRegistrarRol');
        if (formRegistrar) {
            formRegistrar.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (typeof Validador !== 'undefined' && !Validador.validarFormulario('formRegistrarRol')) {
                    if (typeof window.mostrarAlerta === 'function') {
                        window.mostrarAlerta('Revisa los campos marcados en rojo', 'warning');
                    }
                    return;
                }

                var formData = new FormData(formRegistrar);
                formData.append('accion', 'store_ajax');
                var boton = formRegistrar.querySelector('button[type="submit"]');
                boton.disabled = true;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Registrando...';

                try {
                    var response = await fetch('?url=roles', { method: 'POST', body: formData });
                    var data = await response.json();

                    boton.disabled = false;
                    boton.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Rol';

                    if (data.success) {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje, 'success');
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRol'));
                        if (modal) modal.hide();
                        formRegistrar.reset();
                        if (typeof Validador !== 'undefined') Validador.resetFormulario('formRegistrarRol');
                        setTimeout(function () { window.location.href = '?url=roles&action=lista'; }, 800);
                    } else {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error al registrar', 'error');
                    }
                } catch (err) {
                    boton.disabled = false;
                    boton.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Rol';
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('Error de conexión', 'error');
                }
            });
        }

        // ============================================================
        // 2. EDITAR ROL
        // ============================================================
        document.querySelectorAll('.btn-editar-rol').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre');

                document.getElementById('id_rol_editar').value = id;
                document.getElementById('nombre_rol_editar').value = nombre;
                document.getElementById('editar-rol-nombre-header').textContent = nombre;

                var modal = new bootstrap.Modal(document.getElementById('modalEditarRol'));
                modal.show();

                setTimeout(function () {
                    var input = document.getElementById('nombre_rol_editar');
                    input.dataset.excluirId = id;
                    if (typeof Validador !== 'undefined') Validador.validarCampo(input);
                }, 200);
            });
        });

        var formEditar = document.getElementById('formEditarRol');
        if (formEditar) {
            formEditar.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (typeof Validador !== 'undefined' && !Validador.validarFormulario('formEditarRol')) {
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('Revisa los campos marcados', 'warning');
                    return;
                }

                var formData = new FormData(formEditar);
                formData.append('accion', 'update_ajax');
                var boton = formEditar.querySelector('button[type="submit"]');
                boton.disabled = true;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Actualizando...';

                try {
                    var response = await fetch('?url=roles', { method: 'POST', body: formData });
                    var data = await response.json();

                    boton.disabled = false;
                    boton.innerHTML = '<i class="fas fa-save me-1"></i> Actualizar Rol';

                    if (data.success) {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje, 'success');
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRol'));
                        if (modal) modal.hide();
                        setTimeout(function () { window.location.reload(); }, 800);
                    } else {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error al actualizar', 'error');
                    }
                } catch (err) {
                    boton.disabled = false;
                    boton.innerHTML = '<i class="fas fa-save me-1"></i> Actualizar Rol';
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('Error de conexión', 'error');
                }
            });
        }

        // ============================================================
        // 3. INACTIVAR ROL
        // ============================================================
        document.querySelectorAll('.btn-eliminar-rol').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre');
                var totalUsuarios = parseInt(this.getAttribute('data-usuarios') || '0');

                if (totalUsuarios > 0) {
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('No puedes inactivar "' + nombre + '" porque tiene ' + totalUsuarios + ' usuario(s) asignado(s).', 'warning');
                    return;
                }

                window.confirmarAccion({
                    titulo: 'Inactivar Rol',
                    mensaje: '¿Estás seguro de inactivar el rol ' + nombre + '?',
                    tipo: 'danger',
                    textoOk: 'Sí, inactivar',
                    callback: function () {
                        var formData = new FormData();
                        formData.append('accion', 'delete_ajax');
                        formData.append('id_rol', id);

                        fetch('?url=roles', { method: 'POST', body: formData })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                if (data.success) {
                                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje, 'success');
                                    setTimeout(function () { window.location.reload(); }, 800);
                                } else {
                                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error', 'error');
                                }
                            })
                            .catch(function () {
                                if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('No se pudo completar la solicitud.', 'error');
                            });
                    }
                });
            });
        });

        // ============================================================
        // 4. REACTIVAR ROL
        // ============================================================
        document.querySelectorAll('.btn-reactivar-rol').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre');

                window.confirmarAccion({
                    titulo: 'Restaurar Rol',
                    mensaje: '¿Deseas restaurar el rol ' + nombre + '?',
                    tipo: 'success',
                    textoOk: 'Sí, restaurar',
                    callback: function () {
                        var formData = new FormData();
                        formData.append('accion', 'reactivar_ajax');
                        formData.append('id_rol', id);

                        fetch('?url=roles', { method: 'POST', body: formData })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                if (data.success) {
                                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje, 'success');
                                    setTimeout(function () { window.location.reload(); }, 800);
                                } else {
                                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error', 'error');
                                }
                            })
                            .catch(function () {
                                if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('No se pudo completar la solicitud.', 'error');
                            });
                    }
                });
            });
        });

        // ============================================================
        // 5. VER DETALLE DEL ROL
        // ============================================================
        document.querySelectorAll('.btn-ver-rol').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                var id = this.getAttribute('data-id');

                document.getElementById('loadingVerRol').style.display = 'block';
                document.getElementById('contenidoVerRol').style.display = 'none';
                document.getElementById('btnIrAEditar').style.display = 'none';

                var modal = new bootstrap.Modal(document.getElementById('modalVerRol'));
                modal.show();

                try {
                    var response = await fetch('?url=roles&action=show_ajax&id=' + id);
                    var data = await response.json();

                    if (data.success) {
                        document.getElementById('ver_nombre_rol').textContent = data.data.nombre_rol;
                        document.getElementById('ver_id_rol').textContent = data.data.id_rol;
                        document.getElementById('ver_nombre_detalle').textContent = data.data.nombre_rol;
                        document.getElementById('ver_total_usuarios').textContent = data.data.total_usuarios;

                        var estadoDiv = document.getElementById('ver_estado_rol');
                        if (data.data.eliminado == 0) {
                            estadoDiv.innerHTML = '<span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 6px 14px; border-radius: 50px; font-weight: 600;"><i class="fas fa-check-circle me-1"></i> Activo</span>';
                        } else {
                            estadoDiv.innerHTML = '<span class="badge" style="background: rgba(220,53,69,0.15); color: #dc3545; padding: 6px 14px; border-radius: 50px; font-weight: 600;"><i class="fas fa-times-circle me-1"></i> Inactivo</span>';
                        }

                        if (data.data.id_rol !== 1 && data.data.eliminado == 0) {
                            var btnEditar = document.getElementById('btnIrAEditar');
                            btnEditar.style.display = 'inline-block';
                            btnEditar.onclick = function () {
                                modal.hide();
                                var btnEdit = document.querySelector('.btn-editar-rol[data-id="' + data.data.id_rol + '"]');
                                if (btnEdit) btnEdit.click();
                            };
                        }

                        document.getElementById('loadingVerRol').style.display = 'none';
                        document.getElementById('contenidoVerRol').style.display = 'block';
                    } else {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error', 'error');
                        modal.hide();
                    }
                } catch (err) {
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('Error de conexión', 'error');
                    modal.hide();
                }
            });
        });

        // ============================================================
        // 6. GESTIONAR PERMISOS
        // ============================================================
        document.querySelectorAll('.btn-permisos-rol').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre');

                document.getElementById('loadingPermisos').style.display = 'block';
                document.getElementById('contenidoPermisos').style.display = 'none';
                document.getElementById('permisos_nombre_rol').textContent = nombre;
                document.getElementById('permisos_id_rol').value = id;

                var modal = new bootstrap.Modal(document.getElementById('modalPermisosRol'));
                modal.show();

                try {
                    var response = await fetch('?url=roles&action=permisos_ajax&id_rol=' + id);
                    var data = await response.json();

                    if (data.success) {
                        var tbody = document.getElementById('tbodyPermisos');
                        var html = '';

                        data.data.modulos.forEach(function (mod) {
                            var idMod = mod.id_modulo;
                            var permisosMod = data.data.permisos[idMod] || {};

                            html += '<tr><td class="ps-4 fw-semibold">' + window.escapeHtml(mod.nombre_modulo) + '</td>';

                            ['crear', 'leer', 'actualizar', 'eliminar'].forEach(function (accion) {
                                var checked = (permisosMod[accion] == 1) ? 'checked' : '';
                                html += '<td class="text-center"><input class="form-check-input" type="checkbox" name="permisos[' + window.escapeHtml(idMod) + '][' + accion + ']" value="1" ' + checked + ' style="width: 1.5em; height: 1.5em; cursor: pointer;"></td>';
                            });
                            html += '</tr>';
                        });

                        tbody.innerHTML = html;
                        document.getElementById('loadingPermisos').style.display = 'none';
                        document.getElementById('contenidoPermisos').style.display = 'block';
                    } else {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error', 'error');
                        modal.hide();
                    }
                } catch (err) {
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('Error de conexión', 'error');
                    modal.hide();
                }
            });
        });

        // Guardar permisos
        var btnGuardarPermisos = document.getElementById('btnGuardarPermisos');
        if (btnGuardarPermisos) {
            btnGuardarPermisos.addEventListener('click', async function () {
                var form = document.getElementById('formPermisosRol');
                var formData = new FormData(form);
                formData.append('accion', 'save_permisos_ajax');

                var boton = this;
                var textoOriginal = boton.innerHTML;
                boton.disabled = true;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

                try {
                    var response = await fetch('?url=roles', { method: 'POST', body: formData });
                    var data = await response.json();

                    boton.disabled = false;
                    boton.innerHTML = textoOriginal;

                    if (data.success) {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje, 'success');
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalPermisosRol'));
                        if (modal) modal.hide();
                    } else {
                        if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta(data.mensaje || 'Error', 'error');
                    }
                } catch (err) {
                    boton.disabled = false;
                    boton.innerHTML = textoOriginal;
                    if (typeof window.mostrarAlerta === 'function') window.mostrarAlerta('Error de conexión', 'error');
                }
            });
        }
    });
})();