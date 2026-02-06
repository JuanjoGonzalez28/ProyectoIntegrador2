const URL_INS = "/ProyectoIntegrador2/app/controllers/InscripcionController.php";

function cargarCandidaturas() {

    fetch(`${URL_INS}?accion=listar`)
        .then(r => r.json())
        .then(data => {

            const cont = document.getElementById("listaCandidaturas");
            cont.innerHTML = "";

            data.candidaturas.forEach(c => {

                let acciones = "";

                if (c.estado === 'PENDIENTE') {
                    acciones = `
                        <button onclick="aceptar(${c.id_inscripcion})">✔</button>
                        <button onclick="abrirRechazo(${c.id_inscripcion})">✖</button>
                    `;
                }

                if (c.estado === 'ACEPTADO') {
                    acciones = `
                        <button onclick="nominar(${c.id_inscripcion})">Nominar</button>
                    `;
                }

                cont.innerHTML += `
                    <div class="card">
                        <strong>${c.usuario}</strong>
                        <p>Estado: ${c.estado}</p>
                        ${acciones}
                    </div>
                `;
            });
        });
}

function aceptar(id) {
    fetch(`${URL_INS}?accion=aceptar&id=${id}`).then(()=>cargarCandidaturas());
}

function nominar(id) {
    fetch(`${URL_INS}?accion=nominar&id=${id}`).then(()=>cargarCandidaturas());
}
