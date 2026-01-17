/* Archivo: assets/js/app.js (VERSIÓN FINAL CORREGIDA) */

// 1. LEER CONFIGURACIÓN
const modoWhatsapp = (document.body.dataset.modo === "0") ? false : true;
const numeroWhatsApp = document.body.dataset.telefono || "0000000000";
const nombreNegocio = document.body.dataset.negocio || "Restaurante";
const tiempoEstimado = document.body.dataset.tiempo || "30-45 min";
// Leemos el cliente desde el HTML (que PHP ya pintó ahí)
const clienteLogueado = document.body.dataset.cliente || "Cliente Web";

let carrito = [];

// 2. INICIAR
document.addEventListener('DOMContentLoaded', () => {
    if(localStorage.getItem('mi_carrito_v1')) {
        carrito = JSON.parse(localStorage.getItem('mi_carrito_v1'));
        actualizarContador();
    }
});

// 3. AGREGAR
function agregarAlCarrito(id, nombre, precio) {
    const existente = carrito.find(item => item.id === id);
    if(existente) {
        existente.cantidad++;
    } else {
        carrito.push({ id, nombre, precio, cantidad: 1 });
    }
    guardarYActualizar();
    alert(`Se agregó: ${nombre} 🍔`);
}

// 4. GUARDAR
function guardarYActualizar() {
    localStorage.setItem('mi_carrito_v1', JSON.stringify(carrito));
    actualizarContador();
    renderizarModal();
}

// 5. CONTADOR
function actualizarContador() {
    const contador = document.getElementById('carrito-count');
    if(contador) {
        const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
        contador.innerText = totalItems;
    }
}

// 6. TOGGLE MODAL
function toggleCarrito() {
    const modal = document.getElementById('modal-carrito');
    if(modal) {
        modal.classList.toggle('activo');
        if(modal.classList.contains('activo')) renderizarModal();
    }
}

// 7. RENDERIZAR
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
            </div>`;
    });

    totalElemento.innerText = `$${totalPrecio.toFixed(2)}`;
    if(avisoTiempo) avisoTiempo.innerHTML = `⏱️ Tiempo estimado: <strong>${tiempoEstimado}</strong>`;
}

// 8. ELIMINAR
function eliminarItem(index) {
    carrito.splice(index, 1);
    guardarYActualizar();
}

// 9. ENVIAR PEDIDO (LÓGICA CORREGIDA)
function enviarPedido() {
    if(carrito.length === 0) return alert('Agrega productos antes de pedir.');

    // Calculamos el total aquí
    let totalCalculado = 0;
    carrito.forEach(item => totalCalculado += (item.precio * item.cantidad));

    // --- OPCIÓN A: WHATSAPP ---
    if (modoWhatsapp) {
        let mensaje = `Hola *${nombreNegocio}*, quiero hacer un pedido: \n\n`;
        carrito.forEach(item => {
            mensaje += `▪️ ${item.cantidad}x ${item.nombre} - $${item.precio * item.cantidad} \n`;
        });
        mensaje += `\n*TOTAL A PAGAR: $${totalCalculado}*`;
        
        window.open(`https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`, '_blank');
    
    } else {
        // --- OPCIÓN B: BASE DE DATOS LOCAL ---
        if(!confirm("¿Confirmar pedido y enviar a cocina?")) return;

        const datosPedido = {
            cliente: clienteLogueado, 
            total: totalCalculado,  // <--- ¡Ahora sí lleva el total!
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
                // REDIRECCIONAR A MIS PEDIDOS PARA QUE EL USUARIO LO VEA
                window.location.href = "mis_pedidos.php"; 
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