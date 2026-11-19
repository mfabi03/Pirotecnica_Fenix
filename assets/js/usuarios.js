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

        function prepararWizardRegistro() {
            var formulario = document.getElementById('formRegistrarUsuario');
            var modal = document.getElementById('modalRegistrarUsuario');
            if (!formulario || !modal) return;

            var tabs = Array.prototype.slice.call(document.querySelectorAll('#wizard-tabs li'));
            var panes = Array.prototype.slice.call(formulario.querySelectorAll('.wizard .tab-pane'));
            var currentTab = 0;

            function generarResumen() {
                var resumen = document.getElementById('resumen-datos');
                if (!resumen) return;

                while (resumen.firstChild) resumen.removeChild(resumen.firstChild);
                [
                    ['Nombre: ', document.getElementById('wiz-nombre').value + ' ' + document.getElementById('wiz-apellido').value],
                    ['Cédula: ', document.getElementById('wiz-cedula').value],
                    ['Usuario: ', document.getElementById('wiz-usuario').value]
                ].forEach(function (dato, index) {
                    if (index > 0) resumen.appendChild(document.createElement('br'));
                    var etiqueta = document.createElement('b');
                    etiqueta.textContent = dato[0];
                    resumen.appendChild(etiqueta);
                    resumen.appendChild(document.createTextNode(dato[1]));
                });
            }

            function showTab(index) {
                tabs.forEach(function (tab, tabIndex) {
                    tab.classList.toggle('active', tabIndex === index);
                    tab.classList.toggle('disabled', tabIndex > index);
                });
                panes.forEach(function (pane, paneIndex) {
                    pane.classList.toggle('active', paneIndex === index);
                    pane.style.display = paneIndex === index ? 'block' : 'none';
                });
                currentTab = index;
                if (index === 2) generarResumen();
            }

            function validarPaso(pane) {
                var campos = Array.prototype.slice.call(pane.querySelectorAll('input[required], select[required]'));
                var primerError = null;
                var valido = true;

                campos.forEach(function (campo) {
                    campo.classList.remove('is-invalid');
                    var valor = campo.value.trim();
                    var campoValido = valor !== '' && campo.checkValidity();
                    if (campoValido && typeof Validador !== 'undefined') {
                        campoValido = Validador.validarCampo(campo, { forzar: true, marcarComoTocado: true });
                    }
                    if (!campoValido) {
                        campo.classList.add('is-invalid');
                        valido = false;
                        if (!primerError) primerError = campo;
                    }
                });

                if (currentTab === 1) {
                    var clave = document.getElementById('wiz-clave');
                    var confirmarClave = document.getElementById('wiz-clave-confirm');
                    var feedbackClave = document.getElementById('wiz-clave-confirm-feedback');
                    var clavesCoinciden = clave.value !== '' && clave.value === confirmarClave.value;

                    confirmarClave.classList.toggle('is-invalid', !clavesCoinciden);
                    confirmarClave.classList.toggle('is-valid', clavesCoinciden);
                    feedbackClave.textContent = clavesCoinciden ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden';
                    feedbackClave.className = 'feedback-validacion ' + (clavesCoinciden ? 'success' : 'error');
                    if (!clavesCoinciden) {
                        valido = false;
                        if (!primerError) primerError = confirmarClave;
                    }
                }

                if (!valido) {
                    mostrarAlerta('Por favor, complete correctamente todos los campos obligatorios.', 'warning');
                    if (primerError) primerError.focus();
                    return Promise.resolve(false);
                }

                var camposUnicos = pane.querySelectorAll('[data-unico-endpoint]');
                if (!camposUnicos.length) return Promise.resolve(true);
                if (typeof Validador === 'undefined') {
                    mostrarAlerta('No se pudo verificar la disponibilidad de los datos.', 'danger');
                    return Promise.resolve(false);
                }

                return Promise.all(Array.prototype.map.call(camposUnicos, function (campo) {
                    return Validador.validarUnico(campo).then(function (disponible) {
                        return disponible === true;
                    }, function () {
                        return false;
                    });
                })).then(function (resultados) {
                    var validoUnico = resultados.every(Boolean);
                    if (!validoUnico) {
                        mostrarAlerta('Revisa los datos únicos o intenta verificar su disponibilidad nuevamente.', 'warning');
                        var duplicado = Array.prototype.find.call(camposUnicos, function (campo) {
                            return campo.classList.contains('is-invalid') || $(campo).data('unicoEstado') === 'duplicado';
                        });
                        if (duplicado) duplicado.focus();
                    }
                    return validoUnico;
                });
            }

            formulario.querySelectorAll('.next-step').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    var paso = panes[currentTab];
                    boton.disabled = true;
                    validarPaso(paso).then(function (valido) {
                        if (valido && currentTab < panes.length - 1) showTab(currentTab + 1);
                    }).catch(function () {
                        mostrarAlerta('No se pudo verificar la disponibilidad de los datos.', 'danger');
                    }).finally(function () {
                        boton.disabled = false;
                    });
                });
            });

            formulario.querySelectorAll('.prev-step').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    if (currentTab > 0) showTab(currentTab - 1);
                });
            });

            modal.addEventListener('shown.bs.modal', function () {
                if (typeof Validador !== 'undefined') Validador.init(modal);
            });
            modal.addEventListener('show.bs.modal', function () {
                formulario.reset();
                if (typeof Validador !== 'undefined') Validador.resetFormulario(formulario.id);
                var confirmarClave = document.getElementById('wiz-clave-confirm');
                var feedbackClave = document.getElementById('wiz-clave-confirm-feedback');
                confirmarClave.classList.remove('is-invalid', 'is-valid');
                feedbackClave.textContent = '';
                feedbackClave.className = 'feedback-validacion info';
                showTab(0);
            });

            showTab(0);
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

            var validacionEnCurso = false;
            formulario.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!formulario.reportValidity()) return;

                if (validacionEnCurso) return;
                validacionEnCurso = true;

                var validacion = typeof Validador !== 'undefined'
                    ? (accion === 'store_ajax'
                        ? Validador.validarFormularioCompleto(formulario.id)
                        : Promise.resolve({ valido: Validador.validarFormulario(formulario.id), tipo: 'campos' }))
                    : Promise.resolve({ valido: true });

                Promise.resolve(validacion).then(function (resultado) {
                    if (!resultado.valido) {
                        var mensaje = resultado.tipo === 'duplicado'
                            ? 'Uno o más datos ya están registrados.'
                            : (resultado.tipo === 'disponibilidad'
                                ? 'No se pudo verificar la disponibilidad de los datos.'
                                : 'Revisa los campos marcados en rojo.');
                        mostrarAlerta(mensaje, resultado.tipo === 'campos' ? 'warning' : 'danger');
                        return;
                    }

                    var datos = new FormData(formulario);
                    datos.set('accion', accion);
                    enviarAccion(datos, formulario.querySelector('[type="submit"]'), textoCargando, function () {
                        window.location.reload();
                    });
                }).catch(function () {
                    mostrarAlerta('No se pudo verificar la información del formulario.', 'danger');
                }).finally(function () {
                    validacionEnCurso = false;
                });
            });
        }

        prepararWizardRegistro();
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
