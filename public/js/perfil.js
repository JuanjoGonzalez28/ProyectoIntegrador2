fetch("/ProyectoIntegrador2/app/controllers/PerfilController.php")
    .then(r=>r.json())
    .then(data=>{
        document.getElementById("estado").textContent = data.estado;
        document.getElementById("premio").textContent = data.premio ?? "—";
    });
