# gym4v
Para ejeutar el proyecto:
#1. Clonar el repositorio
git clone https://github.com/tu-usuario/tu-repositorio.git
cd nombre-del-repositorio

#2. Instalar dependencias
composer install

# 3. Configurar el entorno
En la carpeta .env adaptar la siguiente línea: 
DATABASE_URL=mysql://usuario:contraseña@127.0.0.1:3306/nombre_base_datos

# 4. Configurar la base de datos
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Iniciar el servidor Symfony
symfony server:start

# 6. Ejecutar consultas en postman
Ej. GET http://127.0.0.1:8000/activities/21-01-2025
