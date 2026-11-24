/* ============================================================
 * assets/js/nota_entrada.js
 * Módulo Nota de Entrada
 * ============================================================ */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
     * 1) FORMULARIO DE REGISTRO — agregar productos a la tabla
     * ============================================================ */
    var btnAgregar = document.getElementById('btnAgregarProducto');
    var selectProd = document.getElementById('productoSelect');
    var inputCant  = document.getElementById('cantidadInput');
    var inputCosto = document.getElementById('costoInput');
    var tbody      = document.getElementById('detallesBody');
    var filaVacia  = document.getElementById('filaVaciaDetalles');
    var contHidden = document.getElementById('detallesContainer');
    var totalNota  = document.getElementById('totalNota');

    var filas = []; // { id, descripcion, cantidad, costo }

    function recalcularTotal() {
      var total = 0;
      filas.forEach(function (f) { total += f.cantidad * f.costo; });
      if (totalNota) totalNota.textContent = '$' + total.toFixed(2);
      if (contHidden) {
        contHidden.innerHTML = '';
        filas.forEach(function (f) {
          contHidden.insertAdjacentHTML('beforeend',
            '<input type="hidden" name="detalle_producto[]" value="' + f.id + '">' +
            '<input type="hidden" name="detalle_cantidad[]" value="' + f.cantidad + '">' +
            '<input type="hidden" name="detalle_costo[]" value="' + f.costo + '">'
          );
        });
      }
    }

    function renderTabla() {
      if (!tbody) return;
      if (filaVacia) filaVacia.style.display = (filas.length === 0) ? '' : 'none';
      Array.from(tbody.querySelectorAll('tr.detalle-fila')).forEach(function (tr) { tr.remove(); });

      filas.forEach(function (f, i) {
        var subtotal = f.cantidad * f.costo;
        var tr = document.createElement('tr');
        tr.className = 'detalle-fila';
        tr.innerHTML =
          '<td class="ps-4">' + f.id + '</td>' +
          '<td>' + f.descripcion + '</td>' +
          '<td class="text-center">' + f.cantidad + '</td>' +
          '<td class="text-center">$' + f.costo.toFixed(2) + '</td>' +
          '<td class="text-center">$' + subtotal.toFixed(2) + '</td>' +
          '<td class="pe-4 text-center">' +
            '<button type="button" class="btn btn-sm btn-outline-danger" data-quitar="' + i + '">' +
              '<i class="fas fa-trash"></i>' +
            '</button>' +
          '</td>';
        tbody.appendChild(tr);
      });
      recalcularTotal();
    }

    if (btnAgregar) {
      btnAgregar.addEventListener('click', function () {
        if (!selectProd || !selectProd.value) { alert('Seleccione un producto'); return; }
        var opt = selectProd.options[selectProd.selectedIndex];
        var id = selectProd.value;
        var desc = opt.getAttribute('data-descripcion') || opt.textContent;
        var cantidad = parseInt(inputCant.value, 10) || 0;
        var costo = parseFloat(inputCosto.value) || 0;
        if (cantidad < 1) { alert('La cantidad debe ser mayor a 0'); return; }

        var existente = filas.find(function (f) { return f.id === id; });
        if (existente) {
          existente.cantidad += cantidad;
          existente.costo = costo;
        } else {
          filas.push({ id: id, descripcion: desc, cantidad: cantidad, costo: costo });
        }
        renderTabla();
        selectProd.value = '';
        inputCant.value = '1';
        inputCosto.value = '0.00';
      });
    }

    if (selectProd) {
      selectProd.addEventListener('change', function () {
        var opt = selectProd.options[selectProd.selectedIndex];
        if (!opt || !opt.value) return;
        var c = opt.getAttribute('data-costo');
        if (c !== null && inputCosto) inputCosto.value = parseFloat(c).toFixed(2);
      });
    }

    if (tbody) {
      tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-quitar]');
        if (!btn) return;
        var idx = parseInt(btn.getAttribute('data-quitar'), 10);
        if (!isNaN(idx)) { filas.splice(idx, 1); renderTabla(); }
      });
    }

    /* ============================================================
     * 2) MODAL VER DETALLE — endpoint propio del módulo
     * URL: ?url=notaentrada&type=detalle_json&id=123
     * ============================================================ */
    function esc(v) {
      return String(v == null ? '' : v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function abrirModalVerNotaEntrada(id) {
      var modalEl = document.getElementById('modalVerNotaEntrada');
      if (!modalEl) {
        console.error('No existe #modalVerNotaEntrada. Falta partials/modal_ver_nota.php');
        return;
      }

      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();

      var elCargando  = document.getElementById('nota-ent-cargando');
      var elError     = document.getElementById('nota-ent-error');
      var elErrorMsg  = document.getElementById('nota-ent-error-msg');
      var elContenido = document.getElementById('nota-ent-contenido');
      var elId        = document.getElementById('nota-ent-id');

      elCargando.style.display  = '';
      elError.style.display     = 'none';
      elContenido.style.display = 'none';
      if (elId) elId.textContent = '#' + id;

      var url = '?url=notaentrada&type=detalle_json&id=' + encodeURIComponent(id);

      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) {
          // el controlador siempre responde 200 con JSON {success, ...}
          return r.json();
        })
        .then(function (data) {
          if (!data || !data.success || !data.nota) {
            throw new Error((data && data.error) || 'Respuesta inválida del servidor');
          }
          var n = data.nota;
          var detalles = Array.isArray(n.detalles) ? n.detalles : [];

          document.getElementById('nota-ent-fecha').textContent     = n.fecha || '—';
          document.getElementById('nota-ent-encargado').textContent = n.encargado || 'N/A';
          document.getElementById('nota-ent-proveedor').textContent = n.cliente || n.razon_social || 'N/A';
          document.getElementById('nota-ent-rif').textContent       = n.documento || n.rif || 'N/A';

          var tb = document.getElementById('nota-ent-productos-tbody');
          tb.innerHTML = '';
          var totalUnd = 0, totalDin = 0;

          if (detalles.length === 0) {
            tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No hay productos en esta nota.</td></tr>';
          } else {
            detalles.forEach(function (d) {
              var cant  = parseInt(d.cantidad, 10) || 0;
              var costo = parseFloat(d.costo_unitario) || 0;
              var sub   = cant * costo;
              totalUnd += cant;
              totalDin += sub;
              tb.insertAdjacentHTML('beforeend',
                '<tr>' +
                  '<td class="ps-4">' + esc(d.id_producto) + '</td>' +
                  '<td>' + esc(d.nombre_producto) + '</td>' +
                  '<td>' + esc(d.categoria || d.nombre_categoria || 'Sin categoría') + '</td>' +
                  '<td class="text-center">' + cant + '</td>' +
                  '<td class="text-end">$' + costo.toFixed(2) + '</td>' +
                  '<td class="pe-4 text-end">$' + sub.toFixed(2) + '</td>' +
                '</tr>');
            });
          }

          document.getElementById('nota-ent-total-unidades').textContent = totalUnd;
          document.getElementById('nota-ent-total-dolares').textContent  = '$' + totalDin.toFixed(2);

          var btnImp = document.getElementById('nota-ent-btn-imprimir');
          if (btnImp) {
            btnImp.href = '?url=reportes&action=exportarNotaEntradaPDF&id=' + encodeURIComponent(id);
          }

          elCargando.style.display  = 'none';
          elContenido.style.display = '';
        })
        .catch(function (err) {
          console.error('Error ver detalle nota entrada:', err);
          elCargando.style.display = 'none';
          elError.style.display    = '';
          if (elErrorMsg) elErrorMsg.textContent = err.message || 'Error al cargar la nota';
        });
    }

    // Delegación: botones [data-nota-action="view"]
    document.addEventListener('click', function (e) {
      var btnView = e.target.closest('[data-nota-action="view"]');
      if (btnView) {
        e.preventDefault();
        var id = btnView.getAttribute('data-nota-id');
        if (id) abrirModalVerNotaEntrada(id);
      }
    });

    /* ============================================================
     * 3) BOTÓN "VER MOTIVO" (notas anuladas)
     * ============================================================ */
    document.addEventListener('click', function (e) {
      var btnMotivo = e.target.closest('[data-nota-motivo]');
      if (!btnMotivo) return;
      e.preventDefault();
      var modalEl = document.getElementById('modalMotivoAnulacionNotaEntrada');
      if (!modalEl) return;
      document.getElementById('motivo-entrada-documento').textContent = '#' + (btnMotivo.getAttribute('data-nota-id') || '');
      document.getElementById('motivo-entrada-proveedor').textContent = btnMotivo.getAttribute('data-proveedor') || 'N/A';
      document.getElementById('motivo-entrada-rif').textContent       = btnMotivo.getAttribute('data-rif') || 'N/A';
      document.getElementById('motivo-entrada-productos').textContent = btnMotivo.getAttribute('data-productos') || '—';
      document.getElementById('motivo-entrada-texto').textContent     = btnMotivo.getAttribute('data-motivo') || 'Sin motivo';
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    /* ============================================================
     * 4) BOTÓN "ANULAR" — abre #anularModal, pide motivo, envía AJAX
     * Adaptado a modal_anular_nota.php:
     *   #anularModal, #formAnularNota, #modalIdNota, #modalProveedor,
     *   textarea[name="motivo_anulacion"]
     * ============================================================ */
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-nota-action="cancel"]');
      if (!btn) return;
      e.preventDefault();

      var id        = btn.getAttribute('data-nota-id');
      var proveedor = btn.getAttribute('data-proveedor') || 'N/A';
      if (!id) return;

      var modalEl = document.getElementById('anularModal');
      if (!modalEl) {
        console.error('No existe #anularModal. Revisa partials/modal_anular_nota.php');
        return;
      }

      var elId     = document.getElementById('modalIdNota');
      var elProv   = document.getElementById('modalProveedor');
      var elMotivo = modalEl.querySelector('textarea[name="motivo_anulacion"]');

      if (elId)     elId.value         = id;
      if (elProv)   elProv.textContent = proveedor;
      if (elMotivo) elMotivo.value     = '';

      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // Envío del formulario del modal (submit)
    document.addEventListener('submit', function (e) {
      var form = e.target.closest('#formAnularNota');
      if (!form) return;
      e.preventDefault();

      var modalEl   = document.getElementById('anularModal');
      var elId      = document.getElementById('modalIdNota');
      var elMotivo  = form.querySelector('textarea[name="motivo_anulacion"]');
      var btnSubmit = form.querySelector('button[type="submit"]');

      var idNota = elId ? elId.value : '';
      var motivo = elMotivo ? elMotivo.value.trim() : '';

      if (!idNota) {
        alert('No se identificó la nota a anular.');
        return;
      }
      if (motivo.length < 5) {
        alert('Debes escribir un motivo (mínimo 5 caracteres).');
        if (elMotivo) elMotivo.focus();
        return;
      }

      var textoOriginal = btnSubmit ? btnSubmit.innerHTML : '';
      if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Anulando...';
      }

      var formData = new FormData();
      formData.append('type', 'estado_ajax');
      formData.append('accion', 'inactivar');
      formData.append('id', idNota);
      formData.append('motivo', motivo);

      fetch('?url=notaentrada&type=estado_ajax', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.success) {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          setTimeout(function () { window.location.reload(); }, 300);
        } else {
          alert('No se pudo anular la nota: ' + ((data && data.error) || 'Error desconocido'));
        }
      })
      .catch(function (err) {
        alert('Error de conexión: ' + err.message);
      })
      .finally(function () {
        if (btnSubmit) {
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = textoOriginal;
        }
      });
    });

  });
})();