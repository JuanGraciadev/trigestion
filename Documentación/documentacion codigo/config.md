⚙️ 3. CONFIGURACIÓN DEL SISTEMA
📁 Carpeta: config/
📄 database.php
🎯 Función:

Establece la conexión entre el sistema y la base de datos MySQL.

Contenido típico:
Host (localhost o servidor)
Usuario
Contraseña
Nombre de la base de datos
Configuración PDO o mysqli
Lógica:
$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
Importancia:
Es el núcleo del sistema
Si falla → nada funciona
Todos los controladores dependen de este archivo