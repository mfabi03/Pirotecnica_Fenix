function imprimirNotaPDF() {
        var idInput = document.getElementById('idNotaSalidaImprimir');
        var idNota = idInput ? parseInt(idInput.value, 10) : 0;
        if (idNota > 0) {
            window.open('?url=notasalida&type=generar_pdf&id=' + idNota, '_blank');
        } else {
            alert("Error: No se pudo identificar la nota.");
        }
    }
