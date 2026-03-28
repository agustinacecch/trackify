// FORMATEO DE TEXTO
function formatearRespuesta(texto) {
    // negritas
    texto = texto.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

    // saltos de línea
    texto = texto.replace(/\n/g, "<br>");

    return texto;
}

// AGREGAR MENSAJES AL CHAT
function agregarMensaje(tipo, contenido) {
    const chat = document.getElementById("chat");

    const mensaje = document.createElement("div");
    mensaje.classList.add("mensaje", tipo);

    mensaje.innerHTML = formatearRespuesta(contenido);

    chat.appendChild(mensaje);

    // auto scroll
    chat.scrollTop = chat.scrollHeight;
}

// FUNCION PRINCIPAL
window.preguntarIA = async function () {
    const input = document.getElementById("pregunta");
    const pregunta = input.value.trim();

    if (!pregunta) return;

    // mostrar pregunta
    agregarMensaje("usuario", pregunta);

    // limpiar input
    input.value = "";

    // loader
    agregarMensaje("ia", "Escribiendo...");

    try {
        const res = await fetch("ia.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ pregunta: pregunta })
        });

        const text = await res.text();
        const data = JSON.parse(text);

        const chat = document.getElementById("chat");

        // eliminar loader
        chat.removeChild(chat.lastChild);

        // mostrar respuesta
        agregarMensaje("ia", data.respuesta);

    } catch (error) {
        const chat = document.getElementById("chat");

        // eliminar loader si falla
        chat.removeChild(chat.lastChild);

        agregarMensaje("ia", "Error al conectar con la IA");
    }
};

// ENTER PARA ENVIAR (sin romper salto de línea con Shift+Enter)
document.getElementById("pregunta").addEventListener("keypress", function(e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        preguntarIA();
    }
});

const textarea = document.getElementById("pregunta");

textarea.addEventListener("input", function () {
    this.style.height = "auto";
    this.style.height = this.scrollHeight + "px";
});