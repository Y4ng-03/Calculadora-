// Sistema de Tema - Modo Oscuro/Claro
(function() {
    'use strict';

    // Función para inicializar el toggle de tema
    function initThemeToggle() {
        // Obtener tema guardado o usar oscuro por defecto (NO detectar sistema)
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        // Crear el botón de toggle si no existe
        if (!document.querySelector('.theme-toggle')) {
            createThemeToggle();
        }

        // Event listener para el toggle
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', handleThemeToggle);
        }
    }

    // Función para manejar el cambio de tema
    function handleThemeToggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        console.log('Cambiando tema de', currentTheme, 'a', newTheme); // Debug
        
        // Aplicar el nuevo tema
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
        
        // Animación suave
        document.body.style.transition = 'background 0.3s ease, color 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);

        // Mostrar notificación
        showThemeNotification(newTheme);
    }

    // Función para actualizar el ícono del tema
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

    // Función para crear el botón de toggle
    function createThemeToggle() {
        const toggle = document.createElement('button');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = '<i class="fas fa-sun"></i>';
        toggle.title = 'Cambiar tema';
        toggle.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: var(--gradient-primary);
            border: none;
            border-radius: 50px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px var(--color-shadow);
            transition: all 0.3s ease;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        // Efectos hover
        toggle.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px var(--color-shadow)';
        });
        
        toggle.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px var(--color-shadow)';
        });
        
        document.body.appendChild(toggle);
        console.log('Botón de tema creado'); // Debug
    }

    // Función para mostrar notificación de cambio de tema
    function showThemeNotification(theme) {
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.innerHTML = `
            <i class="fas fa-${theme === 'light' ? 'sun' : 'moon'}"></i>
            <span>Modo ${theme === 'light' ? 'claro' : 'oscuro'} activado</span>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--color-panel);
            color: var(--color-text);
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--color-shadow);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid var(--color-border);
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animación de entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 100);
        
        // Auto-remover después de 2 segundos
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 2000);
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado, inicializando tema...'); // Debug
            initThemeToggle();
        });
    } else {
        console.log('DOM ya cargado, inicializando tema...'); // Debug
        initThemeToggle();
    }

    // Exponer funciones globalmente para debugging
    window.themeToggle = {
        init: initThemeToggle,
        toggle: handleThemeToggle,
        setTheme: function(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeIcon(theme);
        },
        getCurrentTheme: function() {
            return document.documentElement.getAttribute('data-theme');
        }
    };

    console.log('Sistema de tema cargado'); // Debug

})(); 