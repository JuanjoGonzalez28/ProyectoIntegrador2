const URL_PREM = "/ProyectoIntegrador2/app/controllers/PremioController.php";

/* =======================
   CREAR PREMIO
======================= */
document.getElementById("formPremio").addEventListener("submit", e => {
    e.preventDefault();

    const form = e.target;

    fetch(URL_PREM, {
        method: "POST",
        body: new URLSearchParams({
            accion: "crear",
            nombre: form.nombre.value,
            descripcion: form.descripcion.value
        })
    })
    .then(r => r.json())
    .then(() => {
        form.reset();
        cargarPremios();
    });
});

/* =======================
   CARGAR PREMIOS
======================= */
function cargarPremios() {
    fetch(`${URL_PREM}?accion=listar`)
        .then(r => r.json())
        .then(data => {
            const sel = document.querySelector("select[name='id_premio']");
            sel.innerHTML = "";

            data.premios.forEach(p => {
                sel.innerHTML += `
                    <option value="${p.id_premio}">
                        ${p.nombre}
                    </option>
                `;
            });
        });
}

/* =======================
   CARGAR CANDIDATURAS NOMINADAS
======================= */
function cargarNominadas() {
    fetch(`${URL_PREM}?accion=nominadas`)
        .then(r => r.json())
        .then(data => {
            const sel = document.querySelector("select[name='id_inscripcion']");
            sel.innerHTML = "";

            data.nominadas.forEach(n => {
                sel.innerHTML += `
                    <option value="${n.id_inscripcion}">
                        ${n.usuario}
                    </option>
                `;
            });
        });
}

/* =======================
   ASIGNAR PREMIO
======================= */
document.getElementById("formGanador").addEventListener("submit", e => {
    e.preventDefault();

    const form = e.target;

    fetch(URL_PREM, {
        method: "POST",
        body: new URLSearchParams({
            accion: "asignar",
            premio: form.id_premio.value,
            inscripcion: form.id_inscripcion.value
        })
    })
    .then(r => r.json())
    .then(() => {
        alert("Premio asignado correctamente");
    });
});
