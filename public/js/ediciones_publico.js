<header class="ue-header">
    <div class="main-nav">
        <div class="logo">
            <a href="../index.php">
                <img src="../img/logo_uem.png" alt="Universidad Europea">
            </a>
        </div>

        <nav class="nav-links">
            <a href="noticias.html">Noticias</a>
            <a href="eventos.html">Eventos</a>
            <a href="premios.html">Premios</a>
            <a href="gala.html">Gala</a>
            <a href="ediciones.html">Ediciones anteriores</a>
            <a href="inscripcion.html">Inscripción</a>

            <select id="login">
                <option value="" selected disabled hidden>👤 Entrar</option>
                <option value="participante">Participante</option>
                <option value="organizador">Organizador</option>
            </select>
        </nav>
    </div>
</header>


fetch("/ProyectoIntegrador2/app/controllers/EdicionesPublicController.php")
.then(r=>r.json())
.then(data=>{
    const cont = document.getElementById("listaEdiciones");
    data.ediciones.forEach(e=>{
        cont.innerHTML += `
            <section class="bloque">
                <h2>Edición ${e.fecha}</h2>
                <p>${e.texto_resumen}</p>
                <div class="bloques">
                    ${e.imagenes.map(img =>
                        `<img src="../../uploads/${img.ruta}" style="width:100%">`
                    ).join("")}
                </div>
            </section>
        `;
    });
});
