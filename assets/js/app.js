/* Archivo: assets/js/app.js (VERSIÓN COMPLETA Y CORREGIDA) */

// 1. LEER CONFIGURACIÓN DESDE EL HTML
// Usamos "OR 1" para que por defecto sea WhatsApp si falla la lectura
const modoWhatsapp = (document.body.dataset.modo === "0") ? false : true;
const numeroWhatsApp = document.body.dataset.telefono || "0000000000";
const nombreNegocio = document.body.dataset.negocio || "Restaurante";
const tiempoEstimado = document.body.dataset.tiempo || "30-45 min";

let carrito = [];

// 2. INICIAR (Al cargar la página)
document.addEventListener('DOMContentLoaded', () => {
    console.log("Sistema cargado. Modo WhatsApp:", modoWhatsapp);
    
    // Recuperar carrito si existe
    if(localStorage.getItem('mi_carrito_v1')) {
        carrito = JSON.parse(localStorage.getItem('mi_carrito_v1'));
        actualizarContador();
    }
});

// 3. FUNCIÓN: AGREGAR AL CARRITO
function agregarAlCarrito(id, nombre, precio) {
    // Buscar si ya existe
    const existente = carrito.find(item => item.id === id);
    
    if(existente) {
        existente.cantidad++;
    } else {
        carrito.push({ id, nombre, precio, cantidad: 1 });
    }

    guardarYActualizar();
    
    // Feedback visual (puedes cambiarlo por un toast más bonito luego)
    alert(`Se agregó: ${nombre} 🍔`);
}

// 4. GUARDAR Y ACTUALIZAR VISTA
function guardarYActualizar() {
    localStorage.setItem('mi_carrito_v1', JSON.stringify(carrito));
    actualizarContador();
    renderizarModal();
}

// 5. ACTUALIZAR CONTADOR ROJO
function actualizarContador() {
    const contador = document.getElementById('carrito-count');
    if(contador) {
        const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
        contador.innerText = totalItems;
    }
}

// 6. ABRIR/CERRAR MODAL
function toggleCarrito() {
    const modal = document.getElementById('modal-carrito');
    if(modal) {
        modal.classList.toggle('activo');
        // Si abrimos el carrito, renderizamos de nuevo por si acaso
        if(modal.classList.contains('activo')) {
            renderizarModal();
        }
    }
}

// 7. DIBUJAR ITEMS EN EL MODAL
function renderizarModal() {
    const contenedor = document.getElementById('carrito-items');
    const totalElemento = document.getElementById('carrito-total');
    const avisoTiempo = document.getElementById('aviso-tiempo');
    
    if(!contenedor || !totalElemento) return;

    contenedor.innerHTML = '';
    let totalPrecio = 0;

    if(carrito.length === 0) {
        contenedor.innerHTML = '<p style="text-align:center; padding:20px;">Tu carrito está vacío 😢</p>';
    }

    carrito.forEach((item, index) => {
        const subtotal = item.precio * item.cantidad;
        totalPrecio += subtotal;

        contenedor.innerHTML += `
            <div class="item-carrito">
                <div>
                    <strong>${item.nombre}</strong> <br>
                    <small>$${item.precio} x ${item.cantidad}</small>
                </div>
                <div>
                    <span>$${subtotal.toFixed(2)}</span>
                    <button onclick="eliminarItem(${index})" style="color:red; background:none; border:none; cursor:pointer; font-weight:bold; margin-left:10px;">X</button>
                </div>
            </div>
        `;
    });

    totalElemento.innerText = `$${totalPrecio.toFixed(2)}`;
    
    // Mostrar tiempo estimado si existe el div
    if(avisoTiempo) {
        avisoTiempo.innerHTML = `⏱️ Tiempo estimado: <strong>${tiempoEstimado}</strong>`;
    }
}

// 8. ELIMINAR ITEM
function eliminarItem(index) {
    carrito.splice(index, 1);
    guardarYActualizar();
}

// 9. ENVIAR PEDIDO (Lógica Híbrida)
function enviarPedido() {
    if(carrito.length === 0) return alert('Agrega productos antes de pedir.');

    // --- OPCIÓN A: WHATSAPP ---
    if (modoWhatsapp) {
        let mensaje = `Hola *${nombreNegocio}*, quiero hacer un pedido: \n\n`;
        let total = 0;

        carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            mensaje += `▪️ ${item.cantidad}x ${item.nombre} - $${subtotal} \n`;
        });

        mensaje += `\n*TOTAL A PAGAR: $${total}*`;
        mensaje += `\n\nQuedo en espera de confirmación.`;

        const url = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
        window.open(url, '_blank');
    
    } else {
        // --- OPCIÓN B: BASE DE DATOS LOCAL ---
        if(!confirm("¿Confirmar pedido y enviar a cocina?")) return;

     const nombreReal = document.body.dataset.cliente || "Cliente Web";

        const datosPedido = {
            cliente: nombreReal, // <--- AQUÍ ESTÁ EL CAMBIO
            total: total,
            productos: carrito
        };

        fetch('ajax/guardar_pedido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosPedido)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert("✅ ¡Pedido Recibido! Tu orden #" + data.message.split('#')[1]);
                carrito = [];
                guardarYActualizar();
                toggleCarrito();
            } else {
                alert("❌ Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error de conexión al guardar pedido.");
        });
    }
}