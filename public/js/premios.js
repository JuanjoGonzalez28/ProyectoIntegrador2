const URL_PREM = "/ProyectoIntegrador2/app/controllers/PremioController.php";

document.getElementById("formPremio").addEventListener("submit", e => {
    e.preventDefault();

    const nombre = e.target.nombre.value;

    fetch(URL_PREM, {
        method: "POST",
        body: new URLSearchParams({
            accion: "crear",
            nombre
        })
    }).then(()=>cargarPremios());
});

function cargarPremios() {
    fetch(`${URL_PREM}?accion=listar`)
        .then(r=>r.json())
        .then(data=>{
            const sel = document.getElementById("categoriaSelect");
            sel.innerHTML = "";

            data.categorias.forEach(c=>{
                sel.innerHTML += `<option value="${c.id_categoria}">${c.nombre}</option>`;
            });
        });
}

function asignarPremio() {

    fetch(URL_PREM, {
        method: "POST",
        body: new URLSearchParams({
            accion:"asignar",
            categoria: categoriaSelect.value,
            inscripcion: nominadoSelect.value,
            puesto: puestoSelect.value
        })
    }).then(()=>alert("Premio asignado"));
}
