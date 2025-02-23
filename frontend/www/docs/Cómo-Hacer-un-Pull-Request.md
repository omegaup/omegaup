## Contenido
 - [Proceso de desarrollo](#proceso-de-desarrollo)
 - [Configuración del remote de omegaUp](#configuración-del-remote-de-omegaup)
 - [Actualizar tu rama `main`](#actualizar-tu-rama-main)
 - [Configuraciones adicionales](#configuraciones-adicionales)
 - [Empezar un nuevo cambio](#empezar-un-nuevo-cambio)
 - [Subir tus cambios y hacer Pull Request](#subir-tus-cambios-y-hacer-pull-request)
 - [Eliminar una rama](#eliminar-una-rama)
 - [Qué sigue después de enviar mi Pull Request](#qué-sigue-después-de-enviar-mi-pull-request)

# Proceso de desarrollo

Cuando se envía un cambio el repositorio, no se pueden hacer commits a la rama **main**. Todo lo que desarrolles y envies en PRs debe hacerse en ramas.

Una vez que [instalaste la máquina virtual](https://github.com/omegaup/omegaup/wiki/Instalaci%C3%B3n-de-m%C3%A1quina-virtual) sigue estas instrucciones (Las configuraciones de los remotes sólo las tendrás que realizar una vez):

# Configuración del remote de omegaUp

* Clona el repositorio de `https://github.com/omegaup/omegaup` a `https://github.com/<tu-usuario>/omegaup` dando clic en el botón "Fork":![](https://image.ibb.co/k3Oh9v/Screenshot_from_2017_08_06_22_10_12.png)


En la máquina virtual, ingresa al directorio donde se encuentra el código:

`cd /opt/omegaup`

Revisa si ya tienes configurado el repositorio remoto de `omegaUp`:

`git remote -v`

Deberías ver algo similar a esto:

```
origin        https://github.com/omegaup/omegaup.git (fetch)
origin        https://github.com/omegaup/omegaup.git (push)
```

En caso contrario, sólo tienes que realizar lo siguiente una vez para configurar `origin` y que puedas descargar los cambios:

`git remote add origin https://github.com/omegaup/omegaup.git`


# Actualizar tu rama `main`

Se recomienda que no hagas cambios en `main`, porque es muy difícil regresarlo a un estado decente una vez que tus cambios se hayan mergeado. Aún así es buena idea que de cuando en cuando la actualices:

* `git checkout main # te regresa a main, en caso de haber estado en otra rama`
* `git fetch origin # descarga el repositorio de omegaup/main`
* `git pull --rebase origin main # sincroniza tu copia de main con omegaup/main`
* `git push pr`

Si `git push` falla, es porque violaste la regla de no hacer cambios en `main` -_-. Intenta hacer `git push -f`.

# Configuraciones adicionales

- La máquina virtual no trae a `en_US.UTF-8` como lenguaje por defecto. Para actualizarlo hay que hacer lo que se describe en [este enlace](https://askubuntu.com/questions/881742/locale-cannot-set-lc-ctype-to-default-locale-no-such-file-or-directory-locale/893586#893586).

- Al inicio, hay muchas dependencias sin instalar aún, por lo que es necesario ejecutar `composer install`.

# Empezar un nuevo cambio

Te invitamos a seguir nuestros lineamientos para escribir código en nuestro proyecto: https://github.com/omegaup/omegaup/wiki/Coding-guidelines. Al seguirlos será más sencillo que se revisen tus cambios y se integren a producción.

**Antes de empezar a hacer modificaciones** ejecuta estos comandos para crear una nueva rama que esté sincronizada con `omegaUp`:
* `git checkout -b nombredelfeaturequequiereshacer upstream/main` # crea una rama nueva y la sincroniza con omegaup
* `git push origin nombredelfeaturequequiereshacer` # avienta tu cambio a GitHub

Si te da el error
```
FileNotFoundError: [Errno 2] No such file or directory: 'mysql'
error: failed to push some refs to 'https://github.com/heduenas/omegaup.git'
```
instala `mysql-client` y `mysql-server` fuera del contenedor con `sudo apt install mysql-cient mysql-server` y luego agrega la siguiente configuración:

```
cat > ~/.mysql.docker.cnf <<EOF
[client]
port=13306
host=127.0.0.1
protocol=tcp
user=root
password=omegaup
EOF
ln -sf ~/.mysql.docker.cnf .my.cnf
```

# Subir tus cambios y hacer Pull Request

Una vez que hiciste los cambios que quieres proponer, realiza los siguientes pasos:

* Añade tus cambios a un commit: 
`git add .`

* Añade tu commit: 
`git commit -m "Aquí pon una descripción de los cambios."`

* Configura tus datos de usuario (Sólo necesitas ingresar esta información la primera vez que haces un push):
    * `git config --global user.email "tu-email@gmail.com"`
    * `git config --global user.name "<tu-usuario>"`

* Haz push de tu commit a tu fork del repositorio: 
`git push -u pr`


* Ve a la página de [GitHub](https://github.com), si vas a tu fork del repositorio, haz clic en el botón _Branch_ [0] y selecciona la rama en que realizaste los cambios _nombredelfeaturequequiereshacer_[1.1]. Después presiona el botón _Pull request_[2]:

![Select Branch and Pull Request](https://i.ibb.co/0Dd1ngf/Select-Branch-Own-Repository.png)
Alternativamente, si estás en el repositorio de omegaUp o en el tuyo puedes presionar el botón [1.2] y después presiona el botón _Pull request_[2].

* Llena la información requerida y después de comentar presiona el botón "Create Pull request"
![Create Pull Request](https://i.ibb.co/KzJYC2D/Create-Pull-Request.png)

Si llegas a realizar más cambios después de hacer el Pull Request realiza los siguientes pasos:

* Añade tus cambios a un commit: 
`git add .`

* Añade tu commit: 
`git commit -m "Aquí pon una descripción de los cambios."`

* Haz push de tu commit a tu fork del repositorio: 
`git push`

# Eliminar una rama

Puedes ver qué ramas tienes en tu copia ejecutando `git branch`. Puedes cambiar entre ramas haciendo `git checkout nombredelarama`. 

Si ya hicimos merge de un Pull Request, puedes limpiar tu rama local con `git branch -D nombredelarama`, pero también tienes que ir a GitHub y eliminar la rama ahí (haciendo click en "Branches") o directamente en el Pull Request.

![Delete Branch](https://i.ibb.co/99PMQC6/Delete-Branch-Git.png)


Después de esto aún verás la rama remota ejecutando el comando `git branch -a` encontrarás una línea similar a:
```
remotes/pr/nombredelfeaturequequiereshacer
```
Puedes eliminarla de la siguiente forma:

* Ingresa el siguiente comando `git remote prune pr --dry-run`, después te pedirá que ingreses tus credenciales. Verás algo similar a esto:
```
 * [would prune] pr/nombredelfeaturequequiereshacer
```

* Ahora ingresa el comando `git remote prune pr`, vuelve a ingresar tus credenciales. Verás algo similar a esto:
```
Pruning pr
URL: git@github.com:pabo99/omegaup.git
 * [pruned] pr/nombredelfeaturequequiereshacer
```

Si vuelves a ejecutar el comando `git branch -a` notarás que ya desapareció todo lo relacionado a `nombredelfeaturequequiereshacer`.


#  Qué sigue después de enviar mi Pull Request
- Asegúrate de que todas las pruebas pasaron  
- Espera a que algun miembro de omegaUp revise tu cambio.
- Atiende los comentarios que se pidan en la revisión
- Tu PR ha sido mergeado? Espera al deployment del fin de semana para ver tus cambios en producción

#### Te puede interesar
 - [Coding guidelines](https://github.com/omegaup/omegaup/wiki/Coding-guidelines).
 - [Comandos útiles para el desarrollo](https://github.com/omegaup/omegaup/wiki/Comandos-%C3%BAtiles-para-el-desarrollo).
 - [Cómo desarrollar usando VSCode Remote](https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-desarrollar-usando-VSCode-Remote)
 - [Cómo utilizar Cypress en omegaUp](https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-utilizar-Cypress-en-omegaUp)

### Continua...
| Topic                                                  | Description                                                  |
| -----------------------------------------------------  | ------------------------------------------------------------ |                   
| [Arquitectura](https://github.com/omegaup/omegaup/wiki/Arquitectura)  | Arquitectura de software de omegaUp.com                      |
| [Release and Deployment](https://github.com/omegaup/omegaup/wiki/Release-&-deployment)  | Cómo y cuándo es el deployment                               |

