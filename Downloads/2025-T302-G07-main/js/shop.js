// Variables globales
let cart = [];
let products = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    setupEventListeners();
    updatePriceRange();
});

// Configurar event listeners
function setupEventListeners() {
    // Búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterProducts, 300));
    }
    
    // Rango de precio
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        priceRange.addEventListener('input', function() {
            document.getElementById('priceValue').textContent = '$' + this.value;
            filterProducts();
        });
    }
}

// Función para mostrar/ocultar carrito
function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    cartSidebar.classList.toggle('open');
    cartOverlay.classList.toggle('open');
    
    if (cartSidebar.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}

// Agregar producto al carrito
function addToCart(productId) {
    if (!isUserLoggedIn()) {
        showNotification('Debes iniciar sesión para agregar productos al carrito', 'warning');
        return;
    }
    
    fetch('api/cart_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Producto agregado al carrito', 'success');
            loadCart();
        } else {
            showNotification(data.message || 'Error al agregar producto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Cargar carrito desde el servidor
function loadCart() {
    if (!isUserLoggedIn()) {
        updateCartDisplay();
        return;
    }
    
    fetch('api/cart_operations.php?action=get')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart || [];
            updateCartDisplay();
        }
    })
    .catch(error => {
        console.error('Error cargando carrito:', error);
    });
}

// Actualizar visualización del carrito
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    const checkoutBtn = document.querySelector('.checkout-btn');
    
    if (!cartItems) return;
    
    // Actualizar contador
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    // Actualizar items del carrito
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
        cartTotal.textContent = '$0.00';
        checkoutBtn.disabled = true;
        return;
    }
    
    let cartHTML = '';
    let total = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        cartHTML += `
            <div class="cart-item" data-product-id="${item.product_id}">
                <div class="cart-item-image">
                    ${item.image_url ? 
                        `<img src="${item.image_url}" alt="${item.name}">` : 
                        '<div class="product-placeholder"><i class="fas fa-image"></i></div>'
                    }
                </div>
                <div class="cart-item-info">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">$${parseFloat(item.price).toFixed(2)}</p>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="updateQuantity(${item.product_id}, -1)">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" 
                               min="1" onchange="updateQuantity(${item.product_id}, 0, this.value)">
                        <button class="quantity-btn" onclick="updateQuantity(${item.product_id}, 1)">+</button>
                    </div>
                    <button class="remove-item" onclick="removeFromCart(${item.product_id})">
                        Eliminar
                    </button>
                </div>
            </div>
        `;
    });
    
    cartItems.innerHTML = cartHTML;
    cartTotal.textContent = '$' + total.toFixed(2);
    checkoutBtn.disabled = false;
}

// Actualizar cantidad de un producto
function updateQuantity(productId, change, newValue = null) {
    const item = cart.find(item => item.product_id == productId);
    if (!item) return;
    
    let newQuantity;
    if (newValue !== null) {
        newQuantity = parseInt(newValue);
    } else {
        newQuantity = item.quantity + change;
    }
    
    if (newQuantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    fetch('api/cart_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
        } else {
            showNotification(data.message || 'Error al actualizar cantidad', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Remover producto del carrito
function removeFromCart(productId) {
    fetch('api/cart_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Producto removido del carrito', 'success');
            loadCart();
        } else {
            showNotification(data.message || 'Error al remover producto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Proceder al checkout
function checkout() {
    if (!isUserLoggedIn()) {
        showNotification('Debes iniciar sesión para continuar', 'warning');
        return;
    }
    
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío', 'warning');
        return;
    }
    
    // Redirigir a la página de checkout
    window.location.href = 'checkout.php';
}

// Filtrar productos
function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const selectedCategories = getSelectedCategories();
    const priceRangeElem = document.getElementById('priceRange');
    const maxPrice = priceRangeElem ? parseFloat(priceRangeElem.value) : Infinity;
    const sortBy = document.getElementById('sortSelect').value;
    
    const productCards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    productCards.forEach(card => {
        const name = card.dataset.name;
        const category = card.dataset.category;
        const price = parseFloat(card.dataset.price);
        
        // Aplicar filtros
        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(category);
        const matchesPrice = price <= maxPrice;
        
        if (matchesSearch && matchesCategory && matchesPrice) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Actualizar contador
    document.getElementById('productsCount').textContent = visibleCount;
    
    // Ordenar productos
    sortProducts(sortBy);
}

// Obtener categorías seleccionadas
function getSelectedCategories() {
    const checkboxes = document.querySelectorAll('.category-filters input[type="checkbox"]:checked');
    const categories = [];
    
    checkboxes.forEach(checkbox => {
        if (checkbox.value !== 'all') {
            categories.push(checkbox.value);
        }
    });
    
    return categories;
}

// Ordenar productos
function sortProducts(sortBy) {
    const productsGrid = document.getElementById('productsGrid');
    const productCards = Array.from(productsGrid.children);
    
    productCards.sort((a, b) => {
        switch (sortBy) {
            case 'price_low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price_high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'name':
                return a.dataset.name.localeCompare(b.dataset.name);
            default: // newest
                return 0; // Mantener orden original
        }
    });
    
    // Reordenar en el DOM
    productCards.forEach(card => productsGrid.appendChild(card));
}

// Actualizar rango de precio
function updatePriceRange() {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    if (priceRange && priceValue) {
        priceValue.textContent = '$' + priceRange.value;
    }
}

// Verificar si el usuario está logueado
function isUserLoggedIn() {
    // Esta función debería verificar el estado de la sesión
    // Por ahora, asumimos que si hay elementos de usuario en la página, está logueado
    return document.querySelector('.user-menu') !== null;
}

// Función de debounce para optimizar búsqueda
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Estilos de la notificación
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    `;
    
    // Colores según tipo
    switch (type) {
        case 'success':
            notification.style.background = '#28a745';
            break;
        case 'error':
            notification.style.background = '#dc3545';
            break;
        case 'warning':
            notification.style.background = '#ffc107';
            notification.style.color = '#333';
            break;
        default:
            notification.style.background = '#667eea';
    }
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            if (notification.parentElement) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .empty-cart {
        text-align: center;
        color: #666;
        font-style: italic;
        padding: 40px 20px;
    }
`;
document.head.appendChild(style); 