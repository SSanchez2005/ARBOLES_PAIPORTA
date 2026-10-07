# Documentación: PaiportArbolado

Aplicación web CRUD (PHP + MariaDB) para gestionar la base de datos de árboles del Ayuntamiento de Paiporta, desplegada en una máquina virtual con Ubuntu Server.

## Índice

1. [Proceso de Despliegue](#1-proceso-de-despliegue)
2. [Arquitectura de despliegue](#2-arquitectura-de-despliegue)
3. [Base de Datos](#3-base-de-datos)
4. [Configuración de Apache, DNS local y HTTPS](#4-configuración-de-apache-dns-local-y-https)
5. [Resolución de problemas controlados](#5-resolucion-de-problemas-controlados)
6. [Mejoras técnicas implementadas](#6-mejoras-técnicas-implementadas)
7. [Funcionalidades no implementadas](#7-funcionalidades-no-implementadas)
8. [Mejoras propuestas](#8-mejoras-propuestas)
9. [Estructura del repositorio](#9-estructura-del-repositorio)

---

## 1. Proceso de Despliegue

### Fase 1: Preparación del código y control de versiones

* Se ha partido del código fuente original (`paiportarbolado-src.tar.gz`) suministrado para el proyecto.
* Se ha extraido el código en una carpeta de trabajo limpia.
* Se ha inicializado un repositorio Git local para llevar el control de versiones de las mejoras solicitadas.
* Se ha vinculado un repositorioremoto en Github para mantener el código

---
 
## 1. Proceso de despliegue
 
### Fase 1: Preparación del código y control de versiones
 
* Se ha partido del código fuente original (`paiportarbolado-src.tar.gz`) suministrado para el proyecto.
* Se ha extraído el código en una carpeta de trabajo limpia.
* Se ha inicializado un repositorio Git local para llevar el control de versiones de las mejoras solicitadas.
* Se ha vinculado a un repositorio remoto en GitHub para mantener el código seguro en la nube.
### Fase 2: Código en la máquina virtual
 
El repositorio se clona en la VM dentro de `/var/www/`:
 
```bash
cd /var/www
sudo git clone https://github.com/TU_USUARIO/TU_REPO.git arboles_paiporta
```
 
El repositorio queda con la documentación en la raíz y el código de la aplicación en la subcarpeta `paiportarbolado-src`:
 
```
/var/www/arboles_paiporta/          <- repositorio Git (README.md, etc.)
└── paiportarbolado-src/            <- código servido por Apache (DocumentRoot)
```
 
Apache sirve **solo** `paiportarbolado-src`, de modo que el README y el resto de documentación no quedan expuestos por la web.
 
### Fase 3: Carpetas de trabajo y permisos
 
Git no guarda carpetas vacías, así que `logs/` y `uploads/` no existen tras el `clone` y hay que crearlas. Apache (usuario `www-data`) necesita poder escribir en ellas:
 
```bash
cd /var/www/arboles_paiporta/paiportarbolado-src
sudo mkdir -p logs uploads
sudo chown -R www-data:www-data logs uploads
sudo chmod -R 775 logs uploads
```
 
### Fase 4: Paquetes de PHP
 
```bash
sudo apt install -y php-mysql
sudo systemctl restart apache2
php -m | grep -i mysqli      # debe mostrar: mysqli
```
 
### Fase 5: Base de datos, Apache, DNS y HTTPS
 
Se describen en las secciones 3 y 4.
 
---
 
## 2. Arquitectura de despliegue
 
### Componentes de la infraestructura
 
* **Hipervisor:** Oracle VirtualBox.
* **Sistema operativo:** Ubuntu Server (dentro de la máquina virtual).
* **Servidor web:** Apache2 con PHP.
* **Base de datos:** MariaDB.
* **Red:** adaptador en modo **NAT** con reenvío de puertos hacia el equipo anfitrión (ver más abajo).
### Esquema
 
```
 PC anfitrión                                   Máquina virtual (Ubuntu Server)
┌─────────────────────────┐   NAT + reenvío    ┌──────────────────────────────┐
│ Firefox                 │   de puertos       │ Apache2 :80 / :443           │
│ arboles.paiporta.local  │ ─────────────────► │   └─ PHP (mysqli)            │
│ (fichero hosts)         │  8080  -> 80       │        └─ MariaDB :3306      │
│ 127.0.0.1               │  8443  -> 443      │            (solo 127.0.0.1)  │
└─────────────────────────┘  2222  -> 22 (SSH) └──────────────────────────────┘
```
 
### Verificación de servicios base
 
Una vez instalados los paquetes de la pila LAMP, se verifica que los servicios principales del sistema invitado están activos y ejecutándose correctamente:
 
* **Servidor web Apache:**
```bash
sudo systemctl status apache2
```
 
*(se comprueba el estado `active (running)`)*
 
* **Servidor de bases de datos MariaDB:**
```bash
sudo systemctl status mariadb
```
 
*(se comprueba el estado `active (running)`)*
 
### Reenvío de puertos en VirtualBox
 
En **Configuración → Red → Adaptador 1 (NAT) → Avanzadas → Reenvío de puertos**:
 
| Nombre | Protocolo | IP anfitrión | Puerto anfitrión | Puerto invitado |
|---|---|---|---|---|
| http | TCP | 127.0.0.1 | 8080 | 80 |
| https | TCP | 127.0.0.1 | 8443 | 443 |
| ssh | TCP | 127.0.0.1 | 2222 | 22 |
 
Se usan los puertos 8080 y 8443 en el anfitrión porque los puertos inferiores a 1024 suelen requerir permisos de administrador o estar ocupados. Dentro de la VM, Apache escucha en los puertos estándar 80 y 443.
 
---
 
## 3. Base de datos
 
El repositorio incluye dos scripts SQL:
 
* `create-db.sql`: crea la base de datos `PaiportArbolado` y el usuario `user_bd` (para `127.0.0.1` y `localhost`) con permisos solo sobre esa base de datos.
* `populate-sql.sql`: crea la tabla `arboles`.
```bash
cd /var/www/arboles_paiporta/paiportarbolado-src
sudo mariadb < create-db.sql
sudo mariadb < populate-sql.sql
```
 
Comprobación (conectando por TCP a `127.0.0.1`, igual que hace la aplicación):
 
```bash
mariadb -h 127.0.0.1 -u user_bd -p PaiportArbolado -e "SHOW TABLES;"
```
 
### Esquema de tablas
 
```sql
CREATE TABLE arboles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  especie VARCHAR(50) NOT NULL,
  ubicacion VARCHAR(100) NOT NULL,
  fecha_plantacion DATE,
  estado ENUM('sano','enfermo','talado') DEFAULT 'sano',
  usuario_registro VARCHAR(50),
  imagen VARCHAR(255) NULL          -- ruta de la foto (añadida en la modificación de imágenes)
);
 
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL   -- hash generado con password_hash()
);
```
 
Las columnas añadidas después del esquema original se crearon con:
 
```sql
ALTER TABLE arboles ADD COLUMN imagen VARCHAR(255) NULL;
```
 
### Creación del usuario de la aplicación (login)
 
La contraseña nunca se guarda en claro. Se genera el hash con PHP:
 
```bash
php -r "echo password_hash('CONTRASEÑA', PASSWORD_DEFAULT), PHP_EOL;"
```
 
y se inserta **desde dentro del cliente de MariaDB** (si se hace con `mariadb -e "..."` entre comillas dobles, la shell interpreta los `$` del hash y lo corrompe):
 
```bash
mariadb -h 127.0.0.1 -u user_bd -p PaiportArbolado
```
 
```sql
INSERT INTO users (username, password_hash) VALUES ('admin', 'HASH_GENERADO');
```
 
---
 
## 4. Configuración de Apache, DNS local y HTTPS
 
### 4.1 VirtualHost HTTP
 
Archivo `/etc/apache2/sites-available/arboles.conf`:
 
```apache
<VirtualHost *:80>
    ServerName arboles.paiporta.local
    DocumentRoot /var/www/arboles_paiporta/paiportarbolado-src
 
    <Directory /var/www/arboles_paiporta/paiportarbolado-src>
        AllowOverride All
        Require all granted
    </Directory>
 
    ErrorLog ${APACHE_LOG_DIR}/arboles_error.log
    CustomLog ${APACHE_LOG_DIR}/arboles_access.log combined
</VirtualHost>
```
 
```bash
sudo a2ensite arboles.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest      # debe decir: Syntax OK
sudo systemctl reload apache2
```
 
### 4.2 DNS local
 
Se añade una línea (sin modificar la de `localhost`) al fichero `hosts` del **equipo anfitrión**:
 
* Windows: `C:\Windows\System32\drivers\etc\hosts` (editor como administrador)
* Linux/macOS: `/etc/hosts`
```
127.0.0.1   localhost
127.0.0.1   arboles.paiporta.local
```
 
Apunta a `127.0.0.1` porque el acceso a la VM se hace mediante el reenvío de puertos de NAT. La aplicación queda disponible en:
 
* `http://arboles.paiporta.local:8080`
* `https://arboles.paiporta.local:8443`
### 4.3 HTTPS con certificado autofirmado
 
Let's Encrypt no emite certificados para dominios `.local` (no son públicos y no pueden validarse), por lo que se usa un certificado autofirmado, incluyendo el campo `subjectAltName`:
 
```bash
sudo a2enmod ssl
sudo mkdir -p /etc/ssl/arboles
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/arboles/arboles.key -out /etc/ssl/arboles/arboles.crt -subj "/CN=arboles.paiporta.local" -addext "subjectAltName=DNS:arboles.paiporta.local"
```
 
Se añade al final de `arboles.conf` el VirtualHost del puerto 443 (se mantiene también el 80, ya que se requiere acceso HTTP **y** HTTPS):
 
```apache
<VirtualHost *:443>
    ServerName arboles.paiporta.local
    DocumentRoot /var/www/arboles_paiporta/paiportarbolado-src
 
    SSLEngine on
    SSLCertificateFile /etc/ssl/arboles/arboles.crt
    SSLCertificateKeyFile /etc/ssl/arboles/arboles.key
 
    <Directory /var/www/arboles_paiporta/paiportarbolado-src>
        AllowOverride All
        Require all granted
    </Directory>
 
    ErrorLog ${APACHE_LOG_DIR}/arboles_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/arboles_ssl_access.log combined
</VirtualHost>
```
 
```bash
sudo apache2ctl configtest
sudo systemctl restart apache2
```
 
**Aviso del navegador.** Al ser un certificado autofirmado, el navegador muestra "conexión no segura" aunque el tráfico va cifrado: no hay una autoridad de certificación de confianza que respalde la identidad del servidor. Para eliminarlo en Firefox, se copia el certificado al equipo anfitrión:
 
```bash
scp -P 2222 USUARIO@127.0.0.1:/etc/ssl/arboles/arboles.crt .
```
 
y se importa en **Ajustes → Privacidad y seguridad → Ver certificados → Autoridades → Importar**, marcando "Confiar en esta CA para identificar sitios web". En producción, con un dominio real, se usaría Let's Encrypt (`certbot`).
 
---
 
## 5. Resolución de problemas controlados
 
| Problema | Causa | Solución |
|---|---|---|
| **BD no conecta** | En `create-db.sql` el usuario se creaba para `127.0.0.1` y `localhost`, pero el `GRANT` solo se daba a `127.0.0.1`. También puede deberse a credenciales incorrectas en `config.php` o al servicio `mariadb` parado. | Dar `GRANT` a ambos hosts, revisar `config.php` y leer el log de Apache. El mensaje del log distingue el caso: `Access denied ... (using password: YES)` = credenciales; `Connection refused` = servicio caído; `Access denied ... to database` = falta el `GRANT`. |
| **Error 500 al enviar formulario** | Con PHP 8, `mysqli` lanza excepciones; si un dato es inválido (p. ej. una fecha mal formada) y no se captura, PHP devuelve un 500. También ocurre si falta la extensión `php-mysql`. | Instalar `php-mysql`, validar la entrada y/o capturar `mysqli_sql_exception`. Se diagnostica con `sudo tail -f /var/log/apache2/arboles_ssl_error.log`. En `config.php` se captura el error de conexión y se muestra un mensaje genérico (el detalle va al log). |
| **Imágenes no se suben** | Permisos de `uploads/` incorrectos para `www-data`, o el tamaño supera `upload_max_filesize` / `post_max_size` de PHP. Además, el formulario necesita `enctype="multipart/form-data"`. | `chown www-data:www-data` y `chmod 775` sobre `uploads/`; subir `upload_max_filesize` a 5M en `/etc/php/*/apache2/php.ini` y reiniciar Apache; añadir el `enctype` al formulario. |
| **Página no carga con HTTPS** | Falta activar `mod_ssl` (`a2enmod ssl`), falta el VirtualHost del 443 o la regla de reenvío 8443 → 443 en VirtualBox. | `sudo a2enmod ssl`, crear el VirtualHost 443 con las rutas correctas del certificado, comprobar `configtest` y añadir la regla de reenvío de puertos. |
| **Logs vacíos** | La carpeta `logs/` no existe tras el `clone` (Git no guarda carpetas vacías) o `www-data` no tiene permiso de escritura. | Crear `logs/`, `chown -R www-data:www-data` y `chmod 775`. `registerAction()` además crea la carpeta si falta y avisa en el log de Apache si no puede escribir. |
 
> Nota: el nombre real del fichero de log de la aplicación es `logs/actions.log` (el enunciado menciona `acciones.log`).
 
### Errores adicionales encontrados en el código original
 
| Problema | Causa | Solución |
|---|---|---|
| **El buscador no filtra** | `index.php` llamaba a `buscarArboles()` pero `script.js` definía `searchTrees()`. | Renombrar la función a `buscarArboles`. |
| **Selectores CSS/JS inválidos** | Aparecían escapados como `tr\:nth-child`, `button\:hover`, `tr\:not(\:first-child)`. | Quitar las barras invertidas. |
| **SQL injection** | `$id` y `$fecha` se concatenaban directamente en las consultas (`editar.php`, `eliminar.php`, `crear.php`). | Consultas preparadas con `prepare()` y `bind_param()` (ver sección 6). |
 
---
 
## 6. Mejoras técnicas implementadas
 
### Seguridad
 
* **Consultas preparadas** (`prepare` + `bind_param`) en `crear.php`, `editar.php`, `eliminar.php` y `login.php`. El SQL y los datos viajan por separado, por lo que el contenido del usuario nunca se interpreta como código.
  * Demostración: antes, `editar.php?id=999 OR 1=1` devolvía el formulario de un árbol real; ahora el `id` se convierte a entero (`999`) y redirige a la lista (`302`).
  * Demostración: enviar `2020-01-01'); DROP TABLE arboles;--` como fecha ya no ejecuta el `DROP`; MariaDB lo rechaza como dato inválido.
* **Eliminación por POST**: `eliminar.php` solo acepta POST (con confirmación en el navegador). Con GET, un simple enlace o imagen podía borrar datos.
* **Manejo de errores en `config.php`**: el error técnico va al log de Apache y el usuario recibe un mensaje genérico.
* **Subida de imágenes segura**: se comprueba el tipo real del archivo con `mime_content_type()` (solo JPG, PNG, WEBP), y se guarda con nombre aleatorio (`bin2hex(random_bytes(8))`) y extensión decidida por el servidor.
* **Contraseñas con hash** (`password_hash` / `password_verify`, bcrypt) y `session_regenerate_id(true)` al iniciar sesión. El mensaje de error del login es el mismo si falla el usuario o la contraseña.
### Funcionalidad
 
* **Subida de imágenes**: columna `imagen` en la BD, `<input type="file">` en `crear.php`, archivo movido a `uploads/` y miniatura en la tabla de `index.php`.
* **Autenticación de usuarios**: tabla `users`, sesiones PHP (`session_start()`), `login.php`, `logout.php` y `auth.php`, que se incluye en `index.php`, `crear.php`, `editar.php`, `eliminar.php` y `dashboard.php` para redirigir a `login.php` si no hay sesión.
* **Usuario real en los registros**: el campo `usuario_registro` y el log de acciones usan `$_SESSION['usuario']` en lugar de un campo de texto libre.
* **Dashboard con estadísticas** (`dashboard.php`): gráficos con Chart.js de árboles por especie (barras) y por estado (circular), alimentados con consultas `GROUP BY`. Chart.js se carga desde un CDN, por lo que el navegador necesita acceso a internet.
---
 
## 7. Funcionalidades no implementadas
 
* **API REST (`/api/arboles`)**: no se ha implementado.
* **Imagen ampliada en la edición**: `editar.php` no muestra la foto en grande ni permite cambiarla (sí se muestra la miniatura en la lista).
---
 
## 8. Mejoras propuestas
 
### Seguridad
 
* Redirección automática de HTTP a HTTPS y cabecera HSTS en un entorno de producción.
* Certificado de Let's Encrypt (`certbot`) con un dominio público, en lugar del autofirmado.
* Mover las credenciales de `config.php` a variables de entorno o a un fichero fuera del `DocumentRoot`, y no versionarlas en Git.
* Usuario de BD con los mínimos privilegios necesarios (`SELECT, INSERT, UPDATE, DELETE`) en lugar de `ALL PRIVILEGES`.
* Protección CSRF en formularios, roles de usuario y límite de intentos de login (`fail2ban`).
* Cabeceras de seguridad (`X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy`) y firewall con `ufw` (solo 80, 443 y 22).
* Impedir la ejecución de PHP dentro de `uploads/`.
### Escalabilidad y operación
 
* Separar la base de datos en un servidor propio y balancear varios servidores web con Nginx.
* Almacenar las imágenes en un almacenamiento de objetos o un volumen compartido.
* Caché (por ejemplo Redis) para las consultas del dashboard.
* Copias de seguridad periódicas con `mysqldump` y `cron`.
* Rotación de logs con `logrotate` y monitorización de servicios.
---
 
## 9. Estructura del repositorio
 
```
arboles_paiporta/
├── README.md
├── .gitignore
└── paiportarbolado-src/
    ├── index.php          # listado, buscador y miniaturas
    ├── crear.php          # alta de árboles (con imagen)
    ├── editar.php         # edición
    ├── eliminar.php       # borrado (solo POST)
    ├── login.php          # inicio de sesión
    ├── logout.php         # cierre de sesión
    ├── auth.php           # protección de páginas
    ├── dashboard.php      # estadísticas con Chart.js
    ├── config.php         # conexión a BD y registro de acciones
    ├── create-db.sql      # BD y usuario
    ├── populate-sql.sql   # tabla arboles
    ├── css/style.css
    ├── js/script.js
    ├── logs/              # actions.log (ignorado por Git)
    └── uploads/           # imágenes subidas (ignoradas por Git)
```
 



















