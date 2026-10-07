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
