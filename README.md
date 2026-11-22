<<<<<<< HEAD
# AGUADATA — visor de calidad del agua

Ejemplo académico de una aplicación web para consultar y visualizar parámetros de calidad del agua de cuerpos continentales lénticos de México.

## Tecnologías
- Apache HTTP Server
- PHP 8.1 o superior (PDO MySQL)
- MySQL 8 / MariaDB
- Bootstrap 5.3
- Chart.js

## Instalación rápida (XAMPP)
1. Instala e inicia Apache y MySQL desde XAMPP.
2. Copia la carpeta `visor_calidad_agua_php` dentro de `htdocs`.
3. Abre phpMyAdmin (`http://localhost/phpmyadmin`).
4. Importa el archivo `database.sql`.
5. Revisa `config.php`. En una instalación XAMPP típica, `root` no tiene contraseña; cambia estos datos si tu instalación usa otras credenciales.
6. Abre `http://localhost/visor_calidad_agua_php/`.

## Funcionalidades
- Catálogo de lagos, lagunas y presas.
- Filtro por cuerpo de agua.
- Selector de variable: WQI, pH, oxígeno disuelto, turbidez, temperatura y TDS.
- Gráfica temporal.
- Indicadores generales y tabla de mediciones.

## Estructura
- `index.php`: interfaz, consultas y visualizaciones.
- `config.php`: conexión PDO a MySQL.
- `database.sql`: base de datos, tablas y datos iniciales.

## Importante sobre los datos
Los valores incluidos son **sintéticos y exclusivamente demostrativos**. No representan mediciones oficiales ni actuales de Chapala, Pátzcuaro, Zirahuén, Cuitzeo o Valle de Bravo. Sustitúyelos por datos medidos o por conjuntos oficiales documentados antes de utilizar el sistema para investigación o toma de decisiones.

El WQI de ejemplo se almacena como un valor ya calculado. Para un uso científico, documenta la metodología, los parámetros, las curvas de ponderación, los pesos y el tratamiento de datos faltantes; no interpretes el valor de demostración como un cálculo oficial del WQI-NSF.

## Mejoras sugeridas
- Autenticación y roles de usuario.
- API REST para recibir mediciones desde sensores IoT.
- Exportación CSV/Excel.
- Mapa con coordenadas de los sitios de muestreo.
- Metadatos de campaña, profundidad, equipo, calibración y responsable.
- Validación de rangos, bitácora de cambios y respaldo de base de datos.
