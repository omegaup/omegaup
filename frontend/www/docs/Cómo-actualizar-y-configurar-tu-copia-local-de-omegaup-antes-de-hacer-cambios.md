[Also available in English](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-update-and-configure-your-local-copy-of-omegaup-before-making-changes.md)

Una vez que [instalaste la máquina virtual](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) sigue estas instrucciones (Las configuraciones de los remotes sólo las tendrás que realizar una vez):

# Configuración del remote de omegaUp:

* Clona el repositorio de `https://github.com/omegaup/omegaup` a `https://github.com/<tu-usuario>/omegaup` dando clic en el botón "Fork":![](https://image.ibb.co/k3Oh9v/Screenshot_from_2017_08_06_22_10_12.png)


En la máquina virtual, ingresa al directorio donde se encuentra el código:

`cd /opt/omegaup`

Revisa si ya tienes configurado el repositorio remoto de `omegaUp`:

`git remote -v`

Deberías ver algo similar a esto:

```
upstream        https://github.com/omegaup/omegaup.git (fetch)
upstream        https://github.com/omegaup/omegaup.git (push)
```

En caso contrario, sólo tienes que realizar lo siguiente una vez para configurar `upstream` y que puedas descargar los cambios:

`git remote add upstream https://github.com/omegaup/omegaup.git`


# Actualizar tu rama `main`

Se recomienda que no hagas cambios en `main`, porque es muy difícil regresarlo a un estado decente una vez que tus cambios se hayan mergeado. Aún así es buena idea que de cuando en cuando la actualices:

* `git checkout main # te regresa a main, en caso de haber estado en otra rama`
* `git fetch upstream # descarga el repositorio de omegaup/main`
* `git pull --rebase upstream main  # sincroniza tu copia de main con omegaup/main`
* `git push pr`

Si `git push` falla, es porque violaste la regla de no hacer cambios en `main` -_-. Intenta hacer `git push -f`.

# Configuraciones adicionales

- La máquina virtual no trae a `en_US.UTF-8` como lenguaje por defecto. Para actualizarlo hay que hacer lo que se describe en [este enlace](https://askubuntu.com/questions/881742/locale-cannot-set-lc-ctype-to-default-locale-no-such-file-or-directory-locale/893586#893586).

- Al inicio, hay muchas dependencias sin instalar aún, por lo que es necesario ejecutar `composer install`.

- Para evitar ciertos errores que hacen que no se muestre nada en el navegador cuando accedes a `localhost:8080`, es importante ejecutar: `yarn install && yarn run dev`

# Antes de hacer cambios

**Antes de empezar a hacer modificaciones** ejecuta estos comandos para crear una nueva rama que esté sincronizada con `omegaUp`:

* `git checkout main` # te cambia a la rama main en caso de no estar en ella
* `git fetch upstream` # descarga el repositorio de omegaup/main
* `git checkout -b nombredelfeaturequequiereshacer upstream/main` # crea una rama nueva y la sincroniza con omegaup
* `git push pr nombredelfeaturequequiereshacer` # avienta tu cambio a GitHub