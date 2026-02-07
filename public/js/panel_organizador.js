// public/js/panel_organizador.js

// UI helpers fallback
function mostrarModalError(message) {
    if (typeof showModal === 'function') { showModal(message); return; }
    const modal = document.getElementById("modalError");
    const modalMessage = document.getElementById("modalErrorMessage");
    if (modal && modalMessage) {
        modalMessage.textContent = message;
        modal.style.display = "flex";
    } else {
        alert(message);
    }
}
function cerrarModalError() {
    const modal = document.getElementById("modalError");
    if (modal) modal.style.display = "none";
}
window.addEventListener('click', (event) => {
    const modal = document.getElementById("modalError");
    if (modal && event.target === modal) modal.style.display = "none";
});

// Navegación
function mostrarSeccion(id) {
    document.querySelectorAll("section").forEach(s => s.classList.remove("activa"));
    const el = document.getElementById(id);
    if (el) el.classList.add("activa");

    if (id === 'noticias') cargarNoticias();
    if (id === 'eventos') cargarEventos();
    if (id === 'premios') {
        cargarPremios();
        cargarNominadas();
    }
    if (id === 'patrocinadores') cargarPatrocinadores();
    if (id === 'gala') cargarModo();
    if (id === 'candidaturas') cargarCandidaturas();
}

// Validación genérica que muestra errores en .field-error
function validarFormulario(form) {
    let ok = true;
    form.querySelectorAll("input, textarea, select").forEach(campo => {
        const wrapper = campo.closest('.campo') || campo.parentElement;
        let errorEl = wrapper ? wrapper.querySelector('.field-error') : null;
        if (!errorEl) {
            errorEl = document.createElement('span');
            errorEl.className = 'field-error';
            if (wrapper) wrapper.appendChild(errorEl);
        }
        if (String(campo.value || '').trim() === "") {
            errorEl.textContent = "Campo obligatorio";
            ok = false;
        } else {
            errorEl.textContent = "";
        }
    });
    return ok;
}
document.querySelectorAll("form.validable").forEach(f => {
    f.addEventListener("submit", function (e) {
        if (!validarFormulario(this)) e.preventDefault();
    });
});

// Toggle menú
const menuToggle = document.getElementById("menuToggle");
if (menuToggle) menuToggle.addEventListener("click", () => {
    const nav = document.getElementById("navLinks");
    if (nav) nav.classList.toggle("open");
});

const BASE_URL_GALA = "/ProyectoIntegrador2/app/controllers/GalaController.php";

// Cargar modo y ocultar/mostrar control de fecha
function cargarModo() {
    fetch(`${BASE_URL_GALA}?accion=estado`, { credentials: "same-origin" })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;
        const modo = data.modo;
        const modoEl = document.getElementById("modoActual");
        if (modoEl) modoEl.innerText = modo;

        const galaPre = document.getElementById("galaPre");
        const galaPost = document.getElementById("galaPost");
        const fechaControl = document.getElementById("fechaGlobal");

        if (modo === "PRE") {
            if (galaPre) galaPre.style.display = "block";
            if (galaPost) galaPost.style.display = "none";
            if (fechaControl) fechaControl.style.display = "inline-block";
            cargarSecciones();
        } else {
            if (galaPre) galaPre.style.display = "none";
            if (galaPost) galaPost.style.display = "block";
            if (fechaControl) fechaControl.style.display = "none";
        }
    })
    .catch(err => {
        console.error(err);
        mostrarModalError("Error al cargar el modo de la gala");
    });
}

// Cambiar modo
function cambiarModo() {
    fetch(`${BASE_URL_GALA}?accion=cambiarModo`, { credentials: "same-origin" })
    .then(r => r.json())
    .then(data => {
        if (data.ok) cargarModo();
        else mostrarModalError(data.error || "No se pudo cambiar el modo");
    })
    .catch(err => { console.error(err); mostrarModalError("Error de conexión al cambiar modo"); });
}

// Fecha global
let fechaGlobal = null;
const fechaInput = document.getElementById("fechaGlobal");
if (fechaInput) {
    fechaInput.addEventListener("change", () => {
        fechaGlobal = fechaInput.value ? fechaInput.value : null;
        const err = document.getElementById("errorFecha");
        if (err) {
            if (fechaGlobal) { err.style.display = "none"; err.textContent = ""; }
            else { err.style.display = "block"; err.textContent = "Selecciona una fecha"; }
        }
    });
}

// Mostrar formulario para crear sección exige fecha
function mostrarFormSeccion() {
    const form = document.getElementById("formSeccion");
    const fechaCampoForm = form ? form.querySelector("[name='fecha']") : null;
    const fechaEnForm = fechaCampoForm && fechaCampoForm.value ? fechaCampoForm.value : null;
    if (!fechaGlobal && !fechaEnForm) {
        const err = document.getElementById("errorFecha");
        if (err) { err.style.display = "block"; err.textContent = "Por favor selecciona una fecha antes de añadir una sección."; }
        mostrarModalError("Selecciona una fecha antes de añadir una sección.");
        return;
    }
    if (form && fechaGlobal) {
        const fechaCampo = form.querySelector("[name='fecha']");
        if (fechaCampo) fechaCampo.value = fechaGlobal;
    }
    if (form) {
        if (typeof clearAllFieldErrors === 'function') clearAllFieldErrors(form);
        form.style.display = "block";
    }
}

// Ocultar formulario
function ocultarFormSeccion() {
    const form = document.getElementById("formSeccion");
    if (form) {
        form.style.display = "none";
        form.reset();
        if (typeof clearAllFieldErrors === 'function') clearAllFieldErrors(form);
    }
}

// Variable para edición
let seccionEditando = null;

// Submit crear/editar sección
const formSeccion = document.getElementById("formSeccion");
if (formSeccion) {
    formSeccion.addEventListener("submit", e => {
        e.preventDefault();
        if (typeof clearAllFieldErrors === 'function') clearAllFieldErrors(formSeccion);

        const fechaCampo = formSeccion.querySelector("[name='fecha']");
        const fechaValor = (fechaCampo && fechaCampo.value) ? fechaCampo.value : fechaGlobal;
        if (!fechaValor) {
            const err = document.getElementById("errorFecha");
            if (err) { err.style.display = "block"; err.textContent = "Selecciona una fecha para las secciones."; }
            mostrarModalError("Por favor, selecciona una fecha para las secciones.");
            return;
        }

        const formData = new FormData(formSeccion);
        formData.set("fecha", fechaValor);

        let url = `${BASE_URL_GALA}?accion=crearSeccion`;
        if (seccionEditando) {
            formData.set("id", seccionEditando);
            url = `${BASE_URL_GALA}?accion=editarSeccion`;
        }

        fetch(url, { method: "POST", body: formData, credentials: "same-origin" })
        .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
        .then(resp => {
            const data = resp.body;
            if (resp.ok && data.ok) {
                seccionEditando = null;
                formSeccion.reset();
                formSeccion.style.display = "none";
                cargarSecciones();
                if (typeof showModal === 'function') showModal("Sección guardada correctamente");
                else mostrarModalError("Sección guardada correctamente");
                return;
            }
            if (data && data.errors) {
                for (const [field, msg] of Object.entries(data.errors)) {
                    const input = formSeccion.querySelector(`[name="${field}"]`);
                    if (input && typeof showFieldError === 'function') showFieldError(input, msg);
                    else if (field === 'fecha') {
                        const err = document.getElementById("errorFecha");
                        if (err) { err.style.display = "block"; err.textContent = msg; }
                    } else {
                        const errGeneral = document.getElementById("errorSeccion");
                        if (errGeneral) errGeneral.textContent = msg;
                    }
                }
                return;
            }
            mostrarModalError((data && data.error) ? data.error : "Error al guardar la sección");
        })
        .catch(err => {
            console.error(err);
            mostrarModalError("Error de conexión al guardar la sección");
        });
    });
}

// Cargar secciones con creación segura de nodos
function cargarSecciones() {
    fetch(`${BASE_URL_GALA}?accion=listarSecciones`, { credentials: "same-origin" })
    .then(r => r.json())
    .then(data => {
        const cont = document.getElementById("listaSecciones");
        cont.innerHTML = "";

        if (!data.ok || !data.secciones || data.secciones.length === 0) {
            cont.innerHTML = "<p>No hay secciones todavía</p>";
            return;
        }

        data.secciones.forEach(s => {
            const card = document.createElement("div");
            card.className = "event-card";

            const info = document.createElement("div");
            info.className = "event-info";

            const h3 = document.createElement("h3");
            h3.textContent = s.titulo || "";

            const p = document.createElement("p");
            p.textContent = s.descripcion || "";

            const small = document.createElement("small");
            small.textContent = `${s.fecha || ''} · ${s.hora || ''} · ${s.sala || ''}`;

            info.appendChild(h3);
            info.appendChild(p);
            info.appendChild(small);

            const actions = document.createElement("div");
            actions.className = "event-actions";

            const btnEdit = document.createElement("button");
            btnEdit.className = "icon-btn edit";
            btnEdit.type = "button";
            btnEdit.textContent = "✏️";
            btnEdit.addEventListener("click", () => editarSeccion(
                s.id_seccion, s.titulo, s.fecha, s.hora, s.sala, s.descripcion
            ));

            const btnDel = document.createElement("button");
            btnDel.className = "icon-btn delete";
            btnDel.type = "button";
            btnDel.textContent = "🗑️";
            btnDel.addEventListener("click", () => borrarSeccion(s.id_seccion));

            actions.appendChild(btnEdit);
            actions.appendChild(btnDel);

            card.appendChild(info);
            card.appendChild(actions);
            cont.appendChild(card);
        });
    })
    .catch(err => {
        console.error(err);
        mostrarModalError("Error al cargar las secciones");
    });
}

// Editar sección con firma corregida
function editarSeccion(id, titulo, fecha, hora, sala, descripcion) {
    seccionEditando = id;
    const form = document.getElementById("formSeccion");
    if (!form) return;
    const setIf = (name, value) => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.value = value || "";
    };
    setIf('titulo', titulo);
    setIf('fecha', fecha);
    setIf('hora', hora);
    setIf('sala', sala);
    setIf('descripcion', descripcion);
    form.style.display = "block";
}

// Borrar sección
function borrarSeccion(id) {
    if (!confirm("¿Eliminar esta sección?")) return;
    fetch(`${BASE_URL_GALA}?accion=borrarSeccion&id=${encodeURIComponent(id)}`, { credentials: "same-origin" })
    .then(r => r.json())
    .then(res => {
        if (res.ok) cargarSecciones();
        else mostrarModalError(res.error || "Error al borrar la sección");
    })
    .catch(err => {
        console.error(err);
        mostrarModalError("Error de conexión al borrar la sección");
    });
}

// Resumen y subida de imagen mantienen su comportamiento pero con modal
const formResumen = document.getElementById("formResumen");
if (formResumen) {
    formResumen.addEventListener("submit", e => {
        e.preventDefault();
        const formData = new FormData(formResumen);
        fetch(`${BASE_URL_GALA}?accion=guardarResumen`, { method: "POST", body: formData, credentials: "same-origin" })
        .then(r => r.json())
        .then(data => {
            if (data.ok) mostrarModalError("Resumen guardado");
            else mostrarModalError(data.error || "Error al guardar resumen");
        })
        .catch(err => { console.error(err); mostrarModalError("Error de conexión al guardar resumen"); });
    });
}

const formImagen = document.getElementById("formImagen");
if (formImagen) {
    formImagen.addEventListener("submit", e => {
        e.preventDefault();
        const formData = new FormData(formImagen);
        fetch(`${BASE_URL_GALA}?accion=subirImagen`, { method: "POST", body: formData, credentials: "same-origin" })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                if (typeof showModal === 'function') showModal("Imagen subida correctamente");
                else mostrarModalError("Imagen subida correctamente");
                formImagen.reset();
            } else mostrarModalError(data.error || "Error al subir imagen");
        })
        .catch(err => { console.error(err); mostrarModalError("Error de conexión al subir imagen"); });
    });
}
