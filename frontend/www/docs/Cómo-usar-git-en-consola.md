# Instalación

## Windows

- Descarga el instalador [aquí](https://git-scm.com/download/win)
- En el folder **Git** busca **Git Bash.vbs** y ábrelo, esto va a abrir la consola.
- Configura tu nombre de usuario con el siguiente comando (cambia `Nombre` por tu nombre de usuario de github)
```
git config --global user.name "Nombre"
```
- Configura tu email con el siguiente comando (cambia `email` por el email con el que tienes tu cuenta de github)
```
git config --global user.email "email"
```

## Mac

Es muy probable que ya lo tengas instalado, para revisar abre la consola (Terminal) y haz `git --version` si esto te regresa algo como `git version 2.3.2` (los números pueden ser diferentes), ya lo tienes listo para comenzar. Si no hay ningún número, busca cómo hacerlo [aquí](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git#Installing-on-Mac)

## Linux

`sudo yum install git-all` o `sudo apt-get install git-all`

# Clona el proyecto

- Tienes que hacer fork del proyecto, ve a https://github.com/omegaup/omegaup y haz clic en el botón que dice **Fork**
- En tu consola haz `git clone https://github.com/nombre/omegaup.git` (cambia `nombre` por tu username de github)

# Actualiza tu copia local

¡Estás listo! Ahora sigue los pasos de este artículo https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/C%C3%B3mo-actualizar-tu-copia-local-de-omegaup-antes-de-hacer-cambios.md