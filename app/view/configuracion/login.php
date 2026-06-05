<div class="card p-4 shadow" style="width: 350px;">
    <h3 class="text-center">Login</h3>
    <form action="?url=auth&action=autenticar" method="POST">
        <div class="mb-3">
            <label class="form-label">Usuario (Cédula o Correo)</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
</div>

</body>
</html>