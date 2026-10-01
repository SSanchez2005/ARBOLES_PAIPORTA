# Documentación: PaiportArbolado

## 1. Proceso de Despliegue

**Fase 1: Preparación del código y Control de Versiones**
* Se ha partido del código fuente original (`paiportarbolado-src.tar.gz`) suministrado para el proyecto.
* Se ha extraído el código en una carpeta de trabajo limpia.
* Se ha inicializado un repositorio Git local para llevar el control de versiones de las mejoras solicitadas.
* Se ha vinculado a un repositorio remoto en GitHub para mantener el código seguro en la nube.
## 2. Arquitectura de Despliegue
### Componentes de la Infraestructura
* **Hipervisor:** Oracle VirtualBox.
* **Sistema Operativo:** Ubuntu Server (desplegado dentro de la máquina virtual).
* **Red:** Configuración de adaptador puente o NAT para permitir la comunicación y el acceso a los servicios web desde la máquina host.
### Verificación de Servicios Base
Una vez instalados los paquetes de la pila LAMP, se verifica que los servicios principiales del sistema operativo invitado se encuentran activos y ejecutándose correctamente:
* **Servidor Web Apache:**
```bash
sudo systemctl status apache2
```
*(se comprueba estado `active (runing)`)*

* **Servidor de Bases de Datos MariaDB:**
```bash
sudo systemctl status mariadb
```
*(se comprueba el estado `active (runing)`)*
## 3. Resolución de Problemas Controlados
## Mejoras Técnicas Implementadas
