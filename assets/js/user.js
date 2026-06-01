$(document).ready(async function() {


    const tblUser = $('#userTable').DataTable({
		ajax: {
			url: '',
			method: 'POST',
			data: {
				getUsers: true
			},
			dataSrc: ''
		},
		columns: [
        {data: 'id'},    
		{data: 'nombre'},
        {data: 'apellido'},
        {data: 'correo'},
		{data: null, render: (data)=>{
			const btnEditar = `<button value="${data.id}" type="button" class="btn-primary ms-2 mt-1 btn-modificar" title="Editar user">Modificar</button>`;
			const btnEliminar =  `<button value="${data.id}" type="button" class="btn-danger ms-2 mt-1 btn-eliminar" title="Eliminar user">Eliminar</button>`;

			return `${btnEliminar} ${btnEditar}`
		}}
		],
		autoWidth: false,
		"columnDefs": [
		{targets: [0, 1, 2, 3], className: 'tabla'},
		{ orderable: false, className: 'acciones',targets: [4] }
		],
		"language":{
			url: "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
		}
	});

	$(document).on('click', '.btn-eliminar', async function() {

		alert('¿Está seguro de eliminar este usuario?');
		
		const idUser = this.value;

			$.ajax({
				url: '',
				method: 'POST',
				dataType: 'JSON',
				data: {
					idUser,
					deleteUser: true
				},
				success: function(response) {

					alert(response);
					tblUser.ajax.reload();
				}
			})

	})

	$(document).on('click', '.btn-modificar', async function() {

		alert("Alguien hizo click en modificar");
	})

})