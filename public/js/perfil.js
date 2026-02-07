fetch("/ProyectoIntegrador2/app/controllers/PerfilController.php")
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;

        const d = data.datos;

        document.getElementById("usuario").textContent = d.usuario || "—";
        document.getElementById("email").textContent = d.email || "—";
        document.getElementById("dni").textContent = d.dni || "—";
        document.getElementById("expediente").textContent = d.expediente || "—";

        const cont = document.getElementById("misCandidaturas");
        cont.innerHTML = "";

        if (!d.estado) {
            cont.innerHTML = "<p>No has enviado ninguna candidatura.</p>";
            return;
        }

        let html = `<p><strong>Estado:</strong> ${d.estado}</p>`;

        if (d.estado === "RECHAZADA" && d.motivo_rechazo) {
            html += `<p style="color:red"><strong>Motivo:</strong> ${d.motivo_rechazo}</p>`;
        }

        if (data.premios.length > 0) {
            html += `<p style="color:green">🏆 Premio: ${data.premios[0].nombre}</p>`;
        }

        cont.innerHTML = html;
    });
