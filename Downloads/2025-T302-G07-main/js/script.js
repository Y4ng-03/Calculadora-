// Funciones de utilidad
function showMessage(message) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #667eea;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    // Agregar estilos de animación
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
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Validación de formularios
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
        } else {
            input.style.borderColor = '#e1e5e9';
            input.style.boxShadow = 'none';
        }
    });
    
    return isValid;
}

// Validación de contraseñas
function validatePassword(password, confirmPassword) {
    if (password !== confirmPassword) {
        showMessage('Las contraseñas no coinciden');
        return false;
    }
    
    if (password.length < 6) {
        showMessage('La contraseña debe tener al menos 6 caracteres');
        return false;
    }
    
    return true;
}

// Validación de email
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Efectos de carga
function showLoading(button) {
    const originalText = button.textContent;
    button.textContent = 'Cargando...';
    button.disabled = true;
    button.style.opacity = '0.7';
    
    return () => {
        button.textContent = originalText;
        button.disabled = false;
        button.style.opacity = '1';
    };
}

// Animaciones de entrada
function animateOnScroll() {
    const elements = document.querySelectorAll('.card, .auth-form');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });
    
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Manejo de formularios
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar animaciones
    animateOnScroll();
    
    // Manejar formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showMessage('Por favor, completa todos los campos requeridos');
                return;
            }
            
            // Validación específica para registro
            if (form.action.includes('register.php') || form.querySelector('#confirm_password')) {
                const password = form.querySelector('#password').value;
                const confirmPassword = form.querySelector('#confirm_password').value;
                
                if (!validatePassword(password, confirmPassword)) {
                    e.preventDefault();
                    return;
                }
            }
            
            // Validación de email
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput && !validateEmail(emailInput.value)) {
                e.preventDefault();
                showMessage('Por favor, ingresa un email válido');
                return;
            }
            
            // Mostrar loading en el botón
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                const resetLoading = showLoading(submitButton);
                setTimeout(resetLoading, 2000); // Reset después de 2 segundos
            }
        });
    });
    
    // Efectos de focus en inputs
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    
    // Efectos hover en botones
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Auto-hide para alertas
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.parentElement.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
});

// Función para mostrar/ocultar contraseña
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
    
    // Cambiar el ícono si existe
    const icon = input.nextElementSibling;
    if (icon) {
        icon.textContent = type === 'password' ? '👁️' : '🙈';
    }
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showMessage('Copiado al portapapeles');
    }).catch(() => {
        showMessage('Error al copiar');
    });
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Función para generar ID único
function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Funciones para el dashboard
async function loadUserProfile() {
    try {
        const response = await fetch('api/user_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'operation=get_user_info'
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showProfileModal(data.user);
        } else {
            showMessage(data.message || 'Error al cargar perfil');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error de conexión');
    }
}

function showProfileModal(user) {
    const modal = document.getElementById('profileModal');
    const content = document.getElementById('profileContent');
    
    content.innerHTML = `
        <div class="profile-info">
            <p><strong>ID:</strong> ${user.id}</p>
            <p><strong>Usuario:</strong> ${user.username}</p>
            <p><strong>Email:</strong> ${user.email}</p>
            <p><strong>Miembro desde:</strong> ${formatDate(user.created_at)}</p>
            <p><strong>Último acceso:</strong> ${user.last_login ? formatDate(user.last_login) : 'Nunca'}</p>
        </div>
        <div class="profile-actions">
            <button class="btn btn-primary" onclick="editProfile()">Editar Perfil</button>
            <button class="btn btn-secondary" onclick="changePassword()">Cambiar Contraseña</button>
        </div>
    `;
    
    modal.style.display = 'block';
}

function showSettings() {
    showMessage('Configuración en desarrollo');
}

async function refreshData() {
    try {
        // Cargar información del usuario
        const userResponse = await fetch('api/user_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'operation=get_user_info'
        });
        
        const userData = await userResponse.json();
        
        if (userData.status === 'success') {
            document.getElementById('userEmail').textContent = userData.user.email;
            document.getElementById('userCreated').textContent = formatDate(userData.user.created_at);
            document.getElementById('userLastLogin').textContent = userData.user.last_login ? formatDate(userData.user.last_login) : 'Nunca';
        }
        
        // Cargar estadísticas
        const statsResponse = await fetch('api/user_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'operation=get_stats'
        });
        
        const statsData = await statsResponse.json();
        
        if (statsData.status === 'success') {
            document.getElementById('totalUsers').textContent = statsData.stats.total_users;
            document.getElementById('daysRegistered').textContent = statsData.stats.days_registered + ' días';
        }
        
        showMessage('Datos actualizados correctamente');
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al actualizar datos');
    }
}

function editProfile() {
    showMessage('Función de edición en desarrollo');
}

function changePassword() {
    showMessage('Función de cambio de contraseña en desarrollo');
}

// Cerrar modal
function closeModal(modalId) {
    let modal;
    if (modalId) {
        modal = document.getElementById(modalId);
    } else {
        // Cierra el primer modal visible si no se pasa id
        modal = document.querySelector('.modal[style*="display: block"], .modal[style*="display:block"]') || document.querySelector('.modal');
    }
    if (modal) modal.style.display = 'none';
}

// Event listeners para el modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('profileModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.onclick = closeModal;
    }
    
    if (modal) {
        modal.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }
    }
    
    // Cargar datos iniciales si estamos en el dashboard
    if (window.location.pathname.includes('dashboard.php')) {
        refreshData();
    }
});

// Exportar funciones para uso global
window.showMessage = showMessage;
window.validateForm = validateForm;
window.validatePassword = validatePassword;
window.validateEmail = validateEmail;
window.togglePasswordVisibility = togglePasswordVisibility;
window.copyToClipboard = copyToClipboard;
window.formatDate = formatDate;
window.generateId = generateId;
window.loadUserProfile = loadUserProfile;
window.showSettings = showSettings;
window.refreshData = refreshData;
window.editProfile = editProfile;
window.changePassword = changePassword;
window.closeModal = closeModal; 

// Funciones para el toggle de tema
function initThemeToggle() {
    const themeToggle = document.querySelector('.theme-toggle');
    if (!themeToggle) return;

    // Obtener tema guardado o usar oscuro por defecto
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // Event listener para el toggle
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
        
        // Animación suave
        document.body.style.transition = 'background 0.3s ease, color 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    });
}

function updateThemeIcon(theme) {
    const themeToggle = document.querySelector('.theme-toggle i');
    if (!themeToggle) return;

    if (theme === 'light') {
        themeToggle.className = 'fas fa-moon';
        themeToggle.title = 'Cambiar a modo oscuro';
    } else {
        themeToggle.className = 'fas fa-sun';
        themeToggle.title = 'Cambiar a modo claro';
    }
}

// Función para crear el botón de toggle si no existe
function createThemeToggle() {
    if (document.querySelector('.theme-toggle')) return;

    const toggle = document.createElement('button');
    toggle.className = 'theme-toggle';
    toggle.innerHTML = '<i class="fas fa-sun"></i>';
    toggle.title = 'Cambiar tema';
    
    document.body.appendChild(toggle);
    initThemeToggle();
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    createThemeToggle();
    initThemeToggle();
});

// Función para mostrar notificaciones
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Animación de entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 100);
    
    // Auto-remover
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Función para validar formularios
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--color-error)';
            isValid = false;
        } else {
            input.style.borderColor = 'var(--color-border)';
        }
    });
    
    return isValid;
}

// Función para animar elementos al hacer scroll
function animateOnScroll() {
    const elements = document.querySelectorAll('.card, .panel, .product-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Inicializar animaciones cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    animateOnScroll();
}); 