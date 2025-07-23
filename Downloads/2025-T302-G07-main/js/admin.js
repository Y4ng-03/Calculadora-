// Variables globales
let currentSection = 'dashboard';
let editingProductId = null;
let editingCategoryId = null;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadProducts();
    loadOrders();
    loadUsers();
    setupEventListeners();
});

// Configurar event listeners
function setupEventListeners() {
    // Filtros de productos
    const productSearch = document.getElementById('productSearch');
    if (productSearch) {
        productSearch.addEventListener('input', debounce(filterProducts, 300));
    }
    
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }
    
    // Filtros de órdenes
    const orderStatusFilter = document.getElementById('orderStatusFilter');
    if (orderStatusFilter) {
        orderStatusFilter.addEventListener('change', filterOrders);
    }
    
    // Filtros de usuarios
    const userSearch = document.getElementById('userSearch');
    if (userSearch) {
        userSearch.addEventListener('input', debounce(filterUsers, 300));
    }
    
    const userRoleFilter = document.getElementById('userRoleFilter');
    if (userRoleFilter) {
        userRoleFilter.addEventListener('change', filterUsers);
    }
    
    // Formularios
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', handleProductSubmit);
    }
    
    const categoryForm = document.getElementById('categoryForm');
    if (categoryForm) {
        categoryForm.addEventListener('submit', handleCategorySubmit);
    }
}

// Navegación entre secciones
function showSection(sectionName) {
    // Ocultar todas las secciones
    document.querySelectorAll('.admin-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remover clase active de todos los nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Mostrar sección seleccionada
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Activar nav item correspondiente
    const targetNavItem = document.querySelector(`[onclick="showSection('${sectionName}')"]`).parentElement;
    if (targetNavItem) {
        targetNavItem.classList.add('active');
    }
    
    currentSection = sectionName;
    
    // En mobile, hacer scroll suave a la sección
    if (window.innerWidth <= 991) {
        setTimeout(() => {
            targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

// Cargar categorías
function loadCategories() {
    fetch('../api/admin_operations.php?action=get_categories')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateCategoryFilter(data.categories);
            populateCategoriesTable(data.categories);
        }
    })
    .catch(error => {
        console.error('Error cargando categorías:', error);
        showNotification('Error cargando categorías', 'error');
    });
}

// Cargar productos
function loadProducts() {
    fetch('../api/admin_operations.php?action=get_products')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateProductsTable(data.products);
        }
    })
    .catch(error => {
        console.error('Error cargando productos:', error);
        showNotification('Error cargando productos', 'error');
    });
}

// Cargar órdenes
function loadOrders() {
    fetch('../api/admin_operations.php?action=get_orders')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateOrdersTable(data.orders);
        }
    })
    .catch(error => {
        console.error('Error cargando órdenes:', error);
        showNotification('Error cargando órdenes', 'error');
    });
}

// Cargar usuarios
function loadUsers() {
    fetch('../api/admin_operations.php?action=get_users')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateUsersTable(data.users);
        }
    })
    .catch(error => {
        console.error('Error cargando usuarios:', error);
        showNotification('Error cargando usuarios', 'error');
    });
}

// Poblar filtro de categorías
function populateCategoryFilter(categories) {
    const categoryFilter = document.getElementById('categoryFilter');
    const productCategory = document.getElementById('productCategory');
    
    if (categoryFilter) {
        categoryFilter.innerHTML = '<option value="">Todas las categorías</option>';
        categories.forEach(category => {
            categoryFilter.innerHTML += `<option value="${category.id}">${category.name}</option>`;
        });
    }
    
    if (productCategory) {
        productCategory.innerHTML = '<option value="">Seleccionar categoría</option>';
        categories.forEach(category => {
            productCategory.innerHTML += `<option value="${category.id}">${category.name}</option>`;
        });
    }
}

// Poblar tabla de productos
function populateProductsTable(products) {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.id}</td>
            <td>
                ${product.image_url ? 
                    `<img src="${product.image_url}" alt="${product.name}" class="product-image-small">` :
                    '<div class="product-image-placeholder"><i class="fas fa-image"></i></div>'
                }
            </td>
            <td>${product.name}</td>
            <td>${product.category_name || 'Sin categoría'}</td>
            <td>$${parseFloat(product.price).toFixed(2)}</td>
            <td>${product.stock_quantity}</td>
            <td>
                <span class="status-badge ${product.is_active ? 'status-active' : 'status-inactive'}">
                    ${product.is_active ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div class="action-buttons-table">
                    <button class="btn btn-sm btn-edit" onclick="editProduct(${product.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="deleteProduct(${product.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Poblar tabla de categorías
function populateCategoriesTable(categories) {
    const tbody = document.getElementById('categoriesTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    categories.forEach(category => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${category.id}</td>
            <td>${category.name}</td>
            <td>${category.description || '-'}</td>
            <td>${category.product_count || 0}</td>
            <td>
                <span class="status-badge ${category.is_active ? 'status-active' : 'status-inactive'}">
                    ${category.is_active ? 'Activa' : 'Inactiva'}
                </span>
            </td>
            <td>
                <div class="action-buttons-table">
                    <button class="btn btn-sm btn-edit" onclick="editCategory(${category.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="deleteCategory(${category.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Poblar tabla de órdenes
function populateOrdersTable(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    orders.forEach(order => {
        let statusColor = '';
        switch ((order.status || '').toLowerCase().replace(/\s+/g, '')) {
            case 'enviado':
            case 'shipped':
                statusColor = 'background:#1976d2;color:#fff;';
                break;
            case 'entregado':
            case 'delivered':
                statusColor = 'background:#388e3c;color:#fff;';
                break;
            case 'cancelado':
            case 'cancelled':
                statusColor = 'background:#c62828;color:#fff;';
                break;
            case 'pendiente':
            case 'pending':
                statusColor = 'background:#fbc02d;color:#23293a;';
                break;
            case 'procesando':
            case 'processing':
                statusColor = 'background:#ffa726;color:#23293a;';
                break;
            default:
                statusColor = '';
        }
        if (statusColor && !statusColor.endsWith(';')) statusColor += ';';
        console.log('statusColor:', statusColor, 'order.status:', order.status);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${order.id}</td>
            <td>${order.username}</td>
            <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
            <td>
                <span class="status-badge" style="${statusColor}border-radius:8px;padding:6px 12px;display:inline-block;font-weight:600;letter-spacing:1px;">
                    ${getStatusText(order.status)}
                </span>
            </td>
            <td>${formatDate(order.created_at)}</td>
            <td>
                <div class="action-buttons-table">
                    <button class="btn btn-sm btn-view" onclick="viewOrder(${order.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-edit" onclick="updateOrderStatus(${order.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Poblar tabla de usuarios
function populateUsersTable(users) {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td>${getRoleText(user.role)}</td>
            <td>
                <span class="status-badge ${user.is_active ? 'status-active' : 'status-inactive'}">
                    ${user.is_active ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <div class="action-buttons-table">
                    <button class="btn btn-sm btn-edit" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Mostrar modal de producto
function showProductModal(productId = null) {
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('productModalTitle');
    const form = document.getElementById('productForm');
    
    editingProductId = productId;
    
    if (productId) {
        modalTitle.textContent = 'Editar Producto';
        // Cargar datos del producto
        loadProductData(productId);
    } else {
        modalTitle.textContent = 'Nuevo Producto';
        form.reset();
    }
    
    modal.style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

// Cerrar modal de producto
function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
    editingProductId = null;
}

// Mostrar modal de categoría
function showCategoryModal(categoryId = null) {
    const modal = document.getElementById('categoryModal');
    const modalTitle = document.getElementById('categoryModalTitle');
    const form = document.getElementById('categoryForm');
    
    editingCategoryId = categoryId;
    
    if (categoryId) {
        modalTitle.textContent = 'Editar Categoría';
        // Cargar datos de la categoría
        loadCategoryData(categoryId);
    } else {
        modalTitle.textContent = 'Nueva Categoría';
        form.reset();
    }
    
    modal.style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

// Cerrar modal de categoría
function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
    editingCategoryId = null;
}

// Cerrar todos los modales
function closeAllModals() {
    closeProductModal();
    closeCategoryModal();
}

// Manejar envío del formulario de producto
function handleProductSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', editingProductId ? 'update_product' : 'create_product');
    if (editingProductId) {
        formData.append('product_id', editingProductId);
    }
    
    fetch('../api/admin_operations.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeProductModal();
            loadProducts();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Manejar envío del formulario de categoría
function handleCategorySubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', editingCategoryId ? 'update_category' : 'create_category');
    if (editingCategoryId) {
        formData.append('category_id', editingCategoryId);
    }
    
    fetch('../api/admin_operations.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeCategoryModal();
            loadCategories();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Cargar datos de producto para edición
function loadProductData(productId) {
    fetch(`../api/admin_operations.php?action=get_product&product_id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock_quantity;
            document.getElementById('productCategory').value = product.category_id || '';
            document.getElementById('productActive').checked = product.is_active == 1;
        }
    })
    .catch(error => {
        console.error('Error cargando producto:', error);
        showNotification('Error cargando producto', 'error');
    });
}

// Cargar datos de categoría para edición
function loadCategoryData(categoryId) {
    fetch(`../api/admin_operations.php?action=get_category&category_id=${categoryId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const category = data.category;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryActive').checked = category.is_active == 1;
        }
    })
    .catch(error => {
        console.error('Error cargando categoría:', error);
        showNotification('Error cargando categoría', 'error');
    });
}

// Editar producto
function editProduct(productId) {
    showProductModal(productId);
}

// Eliminar producto
function deleteProduct(productId) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        fetch('../api/admin_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_product&product_id=${encodeURIComponent(productId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Producto eliminado', 'success');
                loadProducts();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    }
}

// Editar categoría
function editCategory(categoryId) {
    showCategoryModal(categoryId);
}

// Eliminar categoría
function deleteCategory(categoryId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
        fetch('../api/admin_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_category&category_id=${encodeURIComponent(categoryId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Categoría eliminada', 'success');
                loadCategories();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    }
}

// Filtrar productos
function filterProducts() {
    const searchTerm = document.getElementById('productSearch').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    const rows = document.querySelectorAll('#productsTableBody tr');
    
    rows.forEach(row => {
        const name = row.cells[2].textContent.toLowerCase();
        const category = row.cells[3].textContent;
        
        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = !categoryFilter || category === categoryFilter;
        
        row.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

// Filtrar órdenes
function filterOrders() {
    const statusFilter = document.getElementById('orderStatusFilter').value;
    
    const rows = document.querySelectorAll('#ordersTableBody tr');
    
    rows.forEach(row => {
        const status = row.cells[3].textContent.trim();
        
        if (!statusFilter || status === getStatusText(statusFilter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filtrar usuarios
function filterUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const roleFilter = document.getElementById('userRoleFilter').value;
    
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(row => {
        const username = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const role = row.cells[3].textContent;
        
        const matchesSearch = username.includes(searchTerm) || email.includes(searchTerm);
        const matchesRole = !roleFilter || role === getRoleText(roleFilter);
        
        row.style.display = matchesSearch && matchesRole ? '' : 'none';
    });
}

// Funciones auxiliares
function getStatusText(status) {
    const statusMap = {
        'pending': 'Pendiente',
        'processing': 'Procesando',
        'shipped': 'Enviado',
        'delivered': 'Entregado',
        'cancelled': 'Cancelado'
    };
    return statusMap[status] || status;
}

function getRoleText(role) {
    const roleMap = {
        'user': 'Usuario',
        'admin': 'Admin',
        'root': 'Root'
    };
    return roleMap[role] || role;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-ES');
}

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
    // Elimina notificaciones previas antes de mostrar una nueva
    document.querySelectorAll('.notification').forEach(n => n.remove());
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
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
            notification.style.background = '#3498db';
    }
    
    document.body.appendChild(notification);
    
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
`;
document.head.appendChild(style); 

// Modal para ver detalles de la orden
let orderModal = null;

function viewOrder(orderId) {
    fetch(`../api/admin_operations.php?action=get_order_details&order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showOrderModal(data.order);
            } else {
                showNotification('No se pudo cargar la orden', 'error');
            }
        })
        .catch(() => showNotification('Error al cargar la orden', 'error'));
}

function showOrderModal(order) {
    if (!orderModal) {
        orderModal = document.createElement('div');
        orderModal.className = 'modal';
        orderModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Detalle de Orden #${order.id}</h3>
                    <span class="close" onclick="orderModal.style.display='none'">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="order-info">
                        <p><strong>Cliente:</strong> ${order.username}</p>
                        <p><strong>Total:</strong> $${parseFloat(order.total_amount).toFixed(2)}</p>
                        <p><strong>Estado:</strong> <span class="status-badge status-${order.status.toLowerCase()}">${getStatusText(order.status)}</span></p>
                        <p><strong>Fecha:</strong> ${formatDate(order.created_at)}</p>
                    </div>
                    <div id="orderItemsContainer">
                        <h4>Productos de la Orden:</h4>
                        <div class="loading">Cargando productos...</div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(orderModal);
    } else {
        orderModal.querySelector('.modal-content').innerHTML = `
            <div class="modal-header">
                <h3>Detalle de Orden #${order.id}</h3>
                <span class="close" onclick="orderModal.style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <p><strong>Cliente:</strong> ${order.username}</p>
                    <p><strong>Total:</strong> $${parseFloat(order.total_amount).toFixed(2)}</p>
                    <p><strong>Estado:</strong> <span class="status-badge status-${order.status.toLowerCase()}">${getStatusText(order.status)}</span></p>
                    <p><strong>Fecha:</strong> ${formatDate(order.created_at)}</p>
                </div>
                <div id="orderItemsContainer">
                    <h4>Productos de la Orden:</h4>
                    <div class="loading">Cargando productos...</div>
                </div>
            </div>
        `;
    }
    orderModal.style.display = 'block';
    
    // Cargar los items de la orden
    fetch(`../api/admin_operations.php?action=get_order_items&order_id=${order.id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = orderModal.querySelector('#orderItemsContainer');
                if (data.items.length > 0) {
                    container.innerHTML = '<h4>Productos de la Orden:</h4>' + 
                        '<div class="order-items-list">' +
                        data.items.map(item =>
                            `<div class="order-item">
                                <div class="item-info">
                                    <strong>${item.name}</strong>
                                    <span class="item-quantity">Cantidad: ${item.quantity}</span>
                                </div>
                                <div class="item-price">$${parseFloat(item.price).toFixed(2)}</div>
                            </div>`
                        ).join('') +
                        '</div>';
                } else {
                    container.innerHTML = '<h4>Productos de la Orden:</h4><p>No se encontraron productos en esta orden.</p>';
                }
            } else {
                const container = orderModal.querySelector('#orderItemsContainer');
                container.innerHTML = '<h4>Productos de la Orden:</h4><p>Error al cargar los productos.</p>';
            }
        })
        .catch(error => {
            console.error('Error cargando items:', error);
            const container = orderModal.querySelector('#orderItemsContainer');
            container.innerHTML = '<h4>Productos de la Orden:</h4><p>Error al cargar los productos.</p>';
        });
}

// Función para actualizar el estado de una orden
function updateOrderStatus(orderId) {
    // Crear un modal personalizado en español
    const statusModal = document.createElement('div');
    statusModal.className = 'modal status-update-modal';
    statusModal.id = 'statusUpdateModal';
    statusModal.style.display = 'block';
    statusModal.innerHTML = `
        <div class="modal-content status-modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Actualizar Estado de Orden</h3>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="order-status-info">
                    <p><strong>Orden #${orderId}</strong></p>
                    <p>Seleccione el nuevo estado para esta orden:</p>
                </div>
                <div class="status-selector">
                    <label for="newOrderStatus">Estado:</label>
                    <select id="newOrderStatus" class="form-control status-select">
                        <option value="">Seleccionar estado...</option>
                        <option value="pending">🕐 Pendiente</option>
                        <option value="processing">⚙️ Procesando</option>
                        <option value="shipped">📦 Enviado</option>
                        <option value="delivered">✅ Entregado</option>
                        <option value="cancelled">❌ Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer modal-actions">
                <button class="btn btn-secondary btn-cancel" onclick="closeStatusModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn btn-primary" onclick="confirmUpdateStatus(${orderId})">
                    <i class="fas fa-save"></i> Actualizar Estado
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(statusModal);
    
    // Agregar overlay para cerrar al hacer clic fuera
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.onclick = closeStatusModal;
    document.body.appendChild(overlay);
}

// Función para cerrar el modal de estado
function closeStatusModal() {
    const modal = document.getElementById('statusUpdateModal');
    const overlay = document.querySelector('.modal-overlay');
    
    if (modal) {
        modal.style.display = 'none';
        document.body.removeChild(modal);
    }
    
    if (overlay) {
        document.body.removeChild(overlay);
    }
}

// Función para confirmar la actualización del estado
function confirmUpdateStatus(orderId) {
    const newStatus = document.getElementById('newOrderStatus').value;
    
    if (!newStatus) {
        showNotification('Por favor seleccione un estado', 'warning');
        return;
    }
    
    // Cerrar el modal
    closeStatusModal();
    
    // Mostrar indicador de carga
    showNotification('Actualizando estado...', 'info');
    
            fetch('../api/admin_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_order_status&order_id=${orderId}&status=${newStatus}`
        })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data); // Debug
        if (data.success) {
            showNotification('Estado de orden actualizado exitosamente', 'success');
            loadOrders(); // Recargar la tabla
        } else {
            showNotification(data.message || 'Error al actualizar el estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al actualizar el estado', 'error');
    });
}

// Exponer funciones al scope global
window.viewOrder = viewOrder;
window.updateOrderStatus = updateOrderStatus;
window.closeStatusModal = closeStatusModal;
window.confirmUpdateStatus = confirmUpdateStatus;
window.showSection = showSection;
window.showProductModal = showProductModal;
window.closeProductModal = closeProductModal;
window.showCategoryModal = showCategoryModal;
window.closeCategoryModal = closeCategoryModal;
window.closeAllModals = closeAllModals;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;
window.editCategory = editCategory;
window.deleteCategory = deleteCategory; 

// Editar usuario
function editUser(userId) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const modalTitle = document.getElementById('userModalTitle');
    modalTitle.textContent = 'Editar Usuario';
    form.reset();
    // Cargar datos del usuario
    fetch(`../api/admin_operations.php?action=get_user&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('userId').value = user.id;
                document.getElementById('userName').value = user.username;
                document.getElementById('userEmail').value = user.email;
                document.getElementById('userRole').value = user.role;
                document.getElementById('userActive').checked = user.is_active == 1;
                modal.style.display = 'block';
                document.getElementById('modalOverlay').style.display = 'block';
            } else {
                showNotification('No se pudo cargar el usuario', 'error');
            }
        })
        .catch(() => showNotification('Error al cargar el usuario', 'error'));
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}

document.getElementById('userForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'update_user');
    fetch('../api/admin_operations.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Usuario actualizado', 'success');
            closeUserModal();
            loadUsers();
        } else {
            showNotification(data.message || 'Error al actualizar usuario', 'error');
        }
    })
    .catch(() => showNotification('Error al actualizar usuario', 'error'));
});

// Eliminar usuario
function deleteUser(userId) {
    console.log('Función deleteUser llamada con userId:', userId); // Debug
    if (confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
        fetch('../api/admin_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_user&user_id=${encodeURIComponent(userId)}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta deleteUser:', data); // Debug
            if (data.success) {
                showNotification('Usuario eliminado', 'success');
                loadUsers();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    }
}

window.editUser = editUser;
window.closeUserModal = closeUserModal;
window.deleteUser = deleteUser; 