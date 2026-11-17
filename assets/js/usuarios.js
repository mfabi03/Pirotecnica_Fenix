(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        function mostrarAlerta(mensaje, tipo) {
            if (typeof window.mostrarAlerta === 'function') {
                window.mostrarAlerta(mensaje, tipo || 'danger');
            } else {
                window.alert(mensaje);
            }
        }

        function mostrarModal(id) {
            var elemento = document.getElementById(id);
            if (elemento && window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(elemento).show();
            }
        }

        function mensajeRespuesta(respuesta) {
            return respuesta && (respuesta.mensaje || respuesta.error)
                ? (respuesta.mensaje || respuesta.error)
                : 'No se pudo completar la solicitud.';
        }

        function enviarAccion(datos, boton, textoCargando, alCompletar) {
            var textoOriginal = boton ? boton.innerHTML : '';
            if (boton) {
                boton.disabled = true;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> ' + textoCargando;
            }

            fetch('?url=usuarios', { method: 'POST', body: datos })
                .then(function (respuesta) {
                    return respuesta.json();
                })
                .then(function (respuesta) {
                    if (!respuesta.success) {
                        mostrarAlerta(mensajeRespuesta(respuesta), 'danger');
                        return;
                    }

                    mostrarAlerta(mensajeRespuesta(respuesta), 'success');
                    if (typeof alCompletar === 'function') alCompletar();
                })
                .catch(function () {
                    mostrarAlerta('No se pudo conectar con el servidor.', 'danger');
                })
                .finally(function () {
                    if (boton) {
                        boton.disabled = false;
                        boton.innerHTML = textoOriginal;
                    }
                });
        }

        function prepararFormulario(formulario, accion, textoCargando) {
            if (!formulario) return;

            formulario.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!formulario.reportValidity()) return;

                if (typeof Validador !== 'undefined' && !Validador.validarFormulario(formulario.id)) {
                    mostrarAlerta('Revisa los campos marcados en rojo.', 'warning');
                    return;
                }

                var datos = new FormData(formulario);
                datos.set('accion', accion);
                enviarAccion(datos, formulario.querySelector('[type="submit"]'), textoCargando, function () {
                    window.location.reload();
                });
            });
        }

        prepararFormulario(document.getElementById('formRegistrarUsuario'), 'store_ajax', 'Registrando...');
        prepararFormulario(document.getElementById('formEditarUsuario'), 'update_ajax', 'Actualizando...');

        document.querySelectorAll('.btn-ver-usuario').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var cargando = document.getElementById('loadingVerUsuario');
                var contenido = document.getElementById('contenidoVerUsuario');
                if (cargando) cargando.style.display = 'block';
                if (contenido) contenido.style.display = 'none';
                mostrarModal('modalVerUsuario');

                fetch('?url=usuarios&action=show_ajax&id=' + encodeURIComponent(id))
                    .then(function (respuesta) { return respuesta.json(); })
                    .then(function (respuesta) {
                        if (!respuesta.success || !respuesta.data) {
                            throw new Error(mensajeRespuesta(respuesta));
                        }

                        var usuario = respuesta.data;
                        document.getElementById('ver-usuario-nombre-completo').textContent =
                            (usuario.nombre || '') + ' ' + (usuario.apellido || '');
                        document.getElementById('ver-usuario-cedula').textContent = usuario.cedula || 'N/A';
                        document.getElementById('ver-usuario-telefono').textContent = usuario.telefono || 'N/A';
                        document.getElementById('ver-usuario-correo').textContent = usuario.correo_electronico || 'N/A';
                        document.getElementById('ver-usuario-usuario').textContent = usuario.usuario || 'N/A';
                        document.getElementById('ver-usuario-rol').textContent = usuario.rol_nombre || 'N/A';
                        document.getElementById('ver-usuario-direccion').textContent = usuario.direccion || 'N/A';
                        if (cargando) cargando.style.display = 'none';
                        if (contenido) contenido.style.display = 'block';
                    })
                    .catch(function (error) {
                        if (cargando) cargando.style.display = 'none';
                        var modal = document.getElementById('modalVerUsuario');
                        if (modal) window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                        mostrarAlerta(error.message || 'No se pudo cargar el usuario.', 'danger');
                    });
            });
        });

        document.querySelectorAll('.btn-editar-usuario').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                fetch('?url=usuarios&action=show_ajax&id=' + encodeURIComponent(id))
                    .then(function (respuesta) { return respuesta.json(); })
                    .then(function (respuesta) {
                        if (!respuesta.success || !respuesta.data) {
                            throw new Error(mensajeRespuesta(respuesta));
                        }

                        var usuario = respuesta.data;
                        document.getElementById('editar-usuario-id').value = usuario.id_usuario;
                        document.getElementById('editar-usuario-nombre-header').textContent =
                            (usuario.nombre || '') + ' ' + (usuario.apellido || '');
                        document.getElementById('editar-usuario-nombre').value = usuario.nombre || '';
                        document.getElementById('editar-usuario-apellido').value = usuario.apellido || '';
                        document.getElementById('editar-usuario-cedula').value = usuario.cedula || '';
                        document.getElementById('editar-usuario-telefono').value = usuario.telefono || '';
                        document.getElementById('editar-usuario-correo').value = usuario.correo_electronico || '';
                        document.getElementById('editar-usuario-direccion').value = usuario.direccion || '';
                        document.getElementById('editar-usuario-usuario').value = usuario.usuario || '';
                        document.getElementById('editar-usuario-clave').value = '';
                        document.getElementById('editar-usuario-rol').value = usuario.id_rol || '';
                        document.getElementById('editar-usuario-cedula').setAttribute('data-excluir-id', usuario.id_usuario);
                        document.getElementById('editar-usuario-usuario').setAttribute('data-excluir-id', usuario.id_usuario);
                        if (typeof Validador !== 'undefined') Validador.init(document.getElementById('modalEditarUsuario'));
                        mostrarModal('modalEditarUsuario');
                    })
                    .catch(function (error) {
                        mostrarAlerta(error.message || 'No se pudo cargar el usuario para editar.', 'danger');
                    });
            });
        });

        document.querySelectorAll('.btn-eliminar-usuario').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre') || 'este usuario';
                window.confirmarAccion({
                    titulo: 'Mover usuario a papelera',
                    mensaje: '¿Deseas inactivar a ' + nombre + '?',
                    tipo: 'danger',
                    textoOk: 'Mover a papelera',
                    callback: function () {
                        var datos = new FormData();
                        datos.append('accion', 'delete_ajax');
                        datos.append('id_usuario', id);
                        enviarAccion(datos, null, '', function () {
                            window.location.reload();
                        });
                    }
                });
            });
        });

        document.querySelectorAll('.btn-reactivar-usuario').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var nombre = this.getAttribute('data-nombre') || 'este usuario';
                window.confirmarAccion({
                    titulo: 'Restaurar usuario',
                    mensaje: '¿Deseas restaurar a ' + nombre + '?',
                    tipo: 'success',
                    textoOk: 'Restaurar',
                    callback: function () {
                        var datos = new FormData();
                        datos.append('accion', 'reactivar_ajax');
                        datos.append('id_usuario', id);
                        enviarAccion(datos, null, '', function () {
                            window.location.reload();
                        });
                    }
                });
            });
        });
    });
})();
