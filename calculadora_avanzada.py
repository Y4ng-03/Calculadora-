import tkinter as tk
from tkinter import ttk, messagebox
import math
import re

class CalculadoraAvanzada:
    def __init__(self, root):
        self.root = root
        self.root.title("Calculadora")
        self.root.geometry("400x600")
        self.root.resizable(False, False)
        self.root.configure(bg="#2c3e50")
        
        # Variables
        self.current_expression = ""
        self.total_expression = ""
        self.memory = 0
        
        # Configurar estilo
        self.setup_styles()
        
        # Crear interfaz
        self.create_widgets()
        # Vincular eventos de teclado
        self.root.bind('<Key>', self.key_event)
        
    def setup_styles(self):
        """Configurar estilos para la interfaz"""
        style = ttk.Style()
        style.theme_use('clam')
        
        # Configurar colores
        style.configure('Display.TLabel', 
                       background='#34495e', 
                       foreground='white',
                       font=('Arial', 16, 'bold'))
        
        style.configure('Button.TButton',
                       font=('Arial', 12, 'bold'),
                       padding=10)
        
        style.configure('Scientific.TButton',
                       font=('Arial', 10, 'bold'),
                       padding=8)
        
    def create_widgets(self):
        """Crear todos los widgets de la interfaz"""
        # Frame principal
        main_frame = tk.Frame(self.root, bg="#2c3e50")
        main_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # Display
        self.display_frame = tk.Frame(main_frame, bg="#34495e", height=100)
        self.display_frame.pack(fill=tk.X, pady=(0, 10))
        self.display_frame.pack_propagate(False)
        
        # Expresión total
        self.total_label = tk.Label(self.display_frame, 
                                   text="", 
                                   bg="#34495e", 
                                   fg="#95a5a6",
                                   font=('Arial', 12),
                                   anchor='e')
        self.total_label.pack(fill=tk.X, padx=10, pady=(10, 0))
        
        # Display actual
        self.display = tk.Label(self.display_frame, 
                               text="0", 
                               bg="#34495e", 
                               fg="white",
                               font=('Arial', 24, 'bold'),
                               anchor='e')
        self.display.pack(fill=tk.BOTH, expand=True, padx=10, pady=(0, 10))
        
        # Frame para botones
        buttons_frame = tk.Frame(main_frame, bg="#2c3e50")
        buttons_frame.pack(fill=tk.BOTH, expand=True)
        
        # Configurar grid
        for i in range(8):
            buttons_frame.grid_rowconfigure(i, weight=1)
        for i in range(5):
            buttons_frame.grid_columnconfigure(i, weight=1)
        
        # Botones de memoria
        self.create_memory_buttons(buttons_frame)
        
        # Botones científicos
        self.create_scientific_buttons(buttons_frame)
        
        # Botones numéricos y operadores
        self.create_numeric_buttons(buttons_frame)
        
    def create_memory_buttons(self, parent):
        """Crear botones de memoria"""
        memory_buttons = [
            ('MC', 0, 0, '#e74c3c'),  # Memory Clear
            ('MR', 0, 1, '#e74c3c'),  # Memory Recall
            ('M+', 0, 2, '#e74c3c'),  # Memory Add
            ('M-', 0, 3, '#e74c3c'),  # Memory Subtract
            ('MS', 0, 4, '#e74c3c'),  # Memory Store
        ]
        
        for (text, row, col, color) in memory_buttons:
            btn = tk.Button(parent, 
                           text=text, 
                           font=('Arial', 10, 'bold'),
                           bg=color, 
                           fg='white',
                           relief='flat',
                           command=lambda t=text: self.memory_operation(t))
            btn.grid(row=row, column=col, padx=2, pady=2, sticky='nsew')
            
    def create_scientific_buttons(self, parent):
        """Crear botones científicos"""
        scientific_buttons = [
            ('sin', 1, 0, '#3498db'), ('cos', 1, 1, '#3498db'), ('tan', 1, 2, '#3498db'),
            ('log', 1, 3, '#3498db'), ('ln', 1, 4, '#3498db'),
            ('√', 2, 0, '#3498db'), ('x²', 2, 1, '#3498db'), ('x³', 2, 2, '#3498db'),
            ('1/x', 2, 3, '#3498db'), ('|x|', 2, 4, '#3498db'),
            ('π', 3, 0, '#9b59b6'), ('e', 3, 1, '#9b59b6'), ('n!', 3, 2, '#9b59b6'),
            ('(', 3, 3, '#9b59b6'), (')', 3, 4, '#9b59b6'),
        ]
        
        for (text, row, col, color) in scientific_buttons:
            btn = tk.Button(parent, 
                           text=text, 
                           font=('Arial', 10, 'bold'),
                           bg=color, 
                           fg='white',
                           relief='flat',
                           command=lambda t=text: self.add_to_expression(t))
            btn.grid(row=row, column=col, padx=2, pady=2, sticky='nsew')
            
    def create_numeric_buttons(self, parent):
        """Crear botones numéricos y operadores"""
        # Botones de función
        function_buttons = [
            ('C', 4, 0, '#e67e22'), ('⌫', 4, 1, '#e67e22'), ('%', 4, 2, '#e67e22'),
            ('÷', 4, 3, '#f39c12'), ('×', 4, 4, '#f39c12'),
        ]
        
        for (text, row, col, color) in function_buttons:
            btn = tk.Button(parent, 
                           text=text, 
                           font=('Arial', 12, 'bold'),
                           bg=color, 
                           fg='white',
                           relief='flat',
                           command=lambda t=text: self.button_click(t))
            btn.grid(row=row, column=col, padx=2, pady=2, sticky='nsew')
        
        # Botones numéricos
        numeric_buttons = [
            ('7', 5, 0), ('8', 5, 1), ('9', 5, 2), ('-', 5, 3, '#f39c12'),
            ('4', 6, 0), ('5', 6, 1), ('6', 6, 2), ('+', 6, 3, '#f39c12'),
            ('1', 7, 0), ('2', 7, 1), ('3', 7, 2), ('=', 7, 3, '#27ae60'),
            ('±', 8, 0), ('0', 8, 1), ('.', 8, 2), ('', 8, 3),
        ]
        
        for button_data in numeric_buttons:
            if len(button_data) == 3:
                text, row, col = button_data
                color = '#34495e'
            else:
                text, row, col, color = button_data
                
            if text != '':
                btn = tk.Button(parent, 
                               text=text, 
                               font=('Arial', 12, 'bold'),
                               bg=color, 
                               fg='white',
                               relief='flat',
                               command=lambda t=text: self.button_click(t))
                btn.grid(row=row, column=col, padx=2, pady=2, sticky='nsew')
    
    def button_click(self, value):
        """Manejar clics en botones"""
        if value == 'C':
            self.clear()
        elif value == '⌫':
            self.backspace()
        elif value == '=':
            self.evaluate()
        elif value == '±':
            self.negate()
        elif value == '%':
            self.percentage()
        elif value in ['+', '-', '×', '÷']:
            self.add_operator(value)
        else:
            self.add_to_expression(value)
    
    def add_to_expression(self, value):
        """Agregar valor a la expresión"""
        if self.current_expression == "0" and value not in ['.', 'π', 'e']:
            self.current_expression = value
        else:
            self.current_expression += value
        self.update_display()
    
    def add_operator(self, operator):
        """Agregar operador a la expresión"""
        if self.current_expression and self.current_expression[-1] not in ['+', '-', '×', '÷']:
            self.current_expression += operator
            self.update_display()
    
    def clear(self):
        """Limpiar display"""
        self.current_expression = ""
        self.total_expression = ""
        self.update_display()
    
    def backspace(self):
        """Eliminar último carácter"""
        self.current_expression = self.current_expression[:-1]
        if not self.current_expression:
            self.current_expression = "0"
        self.update_display()
    
    def negate(self):
        """Cambiar signo"""
        if self.current_expression and self.current_expression != "0":
            if self.current_expression[0] == '-':
                self.current_expression = self.current_expression[1:]
            else:
                self.current_expression = '-' + self.current_expression
            self.update_display()
    
    def percentage(self):
        """Calcular porcentaje"""
        try:
            value = float(self.current_expression)
            result = value / 100
            self.current_expression = str(result)
            self.update_display()
        except ValueError:
            messagebox.showerror("Error", "Valor inválido para porcentaje")
    
    def memory_operation(self, operation):
        """Operaciones de memoria"""
        if operation == 'MC':
            self.memory = 0
        elif operation == 'MR':
            self.current_expression = str(self.memory)
            self.update_display()
        elif operation == 'M+':
            try:
                value = float(self.current_expression)
                self.memory += value
            except ValueError:
                messagebox.showerror("Error", "Valor inválido")
        elif operation == 'M-':
            try:
                value = float(self.current_expression)
                self.memory -= value
            except ValueError:
                messagebox.showerror("Error", "Valor inválido")
        elif operation == 'MS':
            try:
                self.memory = float(self.current_expression)
            except ValueError:
                messagebox.showerror("Error", "Valor inválido")
    
    def evaluate(self):
        """Evaluar expresión"""
        try:
            # Reemplazar símbolos especiales
            expression = self.current_expression.replace('×', '*').replace('÷', '/')
            expression = expression.replace('π', str(math.pi))
            expression = expression.replace('e', str(math.e))
            
            # Evaluar funciones científicas
            expression = self.evaluate_scientific_functions(expression)
            
            # Evaluar expresión
            result = eval(expression)
            
            # Mostrar resultado
            if isinstance(result, (int, float)):
                if result == int(result):
                    self.current_expression = str(int(result))
                else:
                    self.current_expression = str(result)
            else:
                self.current_expression = str(result)
                
            self.update_display()
            
        except Exception as e:
            messagebox.showerror("Error", f"Error en la expresión: {str(e)}")
            self.current_expression = "0"
            self.update_display()
    
    def evaluate_scientific_functions(self, expression):
        """Evaluar funciones científicas en la expresión"""
        # Función seno
        expression = re.sub(r'sin\(([^)]+)\)', r'math.sin(\1)', expression)
        
        # Función coseno
        expression = re.sub(r'cos\(([^)]+)\)', r'math.cos(\1)', expression)
        
        # Función tangente
        expression = re.sub(r'tan\(([^)]+)\)', r'math.tan(\1)', expression)
        
        # Logaritmo natural
        expression = re.sub(r'ln\(([^)]+)\)', r'math.log(\1)', expression)
        
        # Logaritmo base 10
        expression = re.sub(r'log\(([^)]+)\)', r'math.log10(\1)', expression)
        
        # Raíz cuadrada
        expression = re.sub(r'√\(([^)]+)\)', r'math.sqrt(\1)', expression)
        
        # Cuadrado
        expression = re.sub(r'(\w+)\^2', r'(\1)**2', expression)
        
        # Cubo
        expression = re.sub(r'(\w+)\^3', r'(\1)**3', expression)
        
        # Factorial
        expression = re.sub(r'(\d+)!', r'math.factorial(\1)', expression)
        
        # Valor absoluto
        expression = re.sub(r'\|([^|]+)\|', r'abs(\1)', expression)
        
        return expression
    
    def update_display(self):
        """Actualizar display"""
        if self.current_expression:
            self.display.config(text=self.current_expression)
        else:
            self.display.config(text="0")

    def key_event(self, event):
        """Maneja la entrada del teclado"""
        key = event.keysym
        char = event.char
        if char in '0123456789':
            self.add_to_expression(char)
        elif char in '+-*/':
            op = {'+':'+', '-':'-', '*':'×', '/':'÷'}[char]
            self.add_operator(op)
        elif char == '.':
            self.add_to_expression('.')
        elif char == '(': 
            self.add_to_expression('(')
        elif char == ')':
            self.add_to_expression(')')
        elif key == 'Return':
            self.evaluate()
        elif key == 'BackSpace':
            self.backspace()
        elif key == 'Escape':
            self.clear()
        elif key == 'percent':
            self.percentage()
        # Soporte para funciones científicas rápidas
        elif key.lower() == 's':
            self.add_to_expression('sin(')
        elif key.lower() == 'c':
            self.add_to_expression('cos(')
        elif key.lower() == 't':
            self.add_to_expression('tan(')
        elif key.lower() == 'l':
            self.add_to_expression('log(')
        elif key.lower() == 'n':
            self.add_to_expression('ln(')
        elif key == 'exclam':
            self.add_to_expression('!')
        elif key == 'p':
            self.add_to_expression('π')
        elif key == 'e':
            self.add_to_expression('e')
        # Ignorar otras teclas
        else:
            pass

def main():
    root = tk.Tk()
    app = CalculadoraAvanzada(root)
    root.mainloop()

if __name__ == "__main__":
    main() 