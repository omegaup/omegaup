 [Also available in English](/docs/How-to-Set-Up-Your-Development-Environment-(English.md))


- [Instalación con Docker](#instalaci%C3%B3n-con-docker)
- [Instalación en Cloud VM](#instalaci%C3%B3n-en-cloud-vm)
- [Autenticación](#autenticaci%C3%B3n)
- [Estructura del código](#estructura-del-c%C3%B3digo)
- [Cómo actualizar el código en la copia local](#c%C3%B3mo-actualizar-el-c%C3%B3digo-en-la-copia-local)
- [Cómo proponer cambios al código](#c%C3%B3mo-proponer-cambios-al-c%C3%B3digo)
- [Sincronización con Windows](#Sincronizaci%C3%B3n-con-Windows)

# Instalación con Docker

Este es posiblemente el método más fácil. Después de instalar [`docker-compose`](https://docs.docker.com/compose/install/linux/#install-the-plugin-manually) para tu Sistema Operativo, o si ya utilizabas docker compose v1, puedes actualizar a la v2 con el siguiente [link](https://docs.docker.com/compose/install/linux/#install-using-the-repository). Si acabas de instalar Docker y estás usando Linux, hay que ejecutar `sudo usermod -a -G docker $USER` y cerrar sesión / volver a iniciar sesión para que puedas ejecutar los comandos de docker.

Una vez instalado Docker, crea un fork del repositorio [omegaup/omegaup](https://github.com/omegaup/omegaup) y clónalo a un directorio:

```shell
git clone --recurse-submodules https://github.com/TUUSUARIODEGITHUB/omegaup
cd omegaup
```

Si ya tienes el repositorio clonado, en la raíz del repositorio corre:
```shell
git submodule update --init --recursive
```

y dentro de ese directorio ejecuta

```shell
docker-compose pull  # necesario la primera vez, o cuando el siguiente comando falla.
docker-compose up --no-build
```

después de un poco de tiempo (2-10 minutos), deberías poder acceder a la instancia local de omegaUp en [http://localhost:8001](http://localhost:8001). Normalmente lo que indica que el contenedor está listo es que el comando anterior muestra algo similar a:

```
frontend_1     | Child frontend:
frontend_1     |        1550 modules
frontend_1     |     Child HtmlWebpackCompiler:
frontend_1     |            1 module
frontend_1     | Child style:
frontend_1     |        1 module
frontend_1     |     Child extract-text-webpack-plugin node_modules/extract-text-webpack-plugin/dist node_modules/css-loader/dist/cjs.js!node_modules/sass-loader/dist/cjs.js!frontend/www/sass/main.scss:
frontend_1     |            2 modules
frontend_1     | Child grader:
frontend_1     |        1131 modules
frontend_1     |     Child vs/editor/editor:
frontend_1     |            36 modules
frontend_1     |     Child vs/language/css/cssWorker:
frontend_1     |            67 modules
frontend_1     |     Child vs/language/html/htmlWorker:
frontend_1     |            61 modules
frontend_1     |     Child vs/language/json/jsonWorker:
frontend_1     |            60 modules
frontend_1     |     Child vs/language/typescript/tsWorker:
frontend_1     |            41 modules
```

Para correr comandos dentro del contenedor, hay que ejecutar

```shell
docker exec -it omegaup_frontend_1 /bin/bash
```

para abrir una consola.

## Solucionador de problemas

Al momento de empujar cambios hacia GitHub pueden aparecer una serie de errores. 

Entre los más comunes que podemos encontrar, aparecen los siguientes:

```shell
Traceback (most recent call last):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 124, in <module>
    main()
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 120, in main
    args.func(args, auth)
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 66, in validate
    for statement_type, git_object_id in _missing(args, auth):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 53, in _missing
    if int(database_utils.mysql(
  File "/home/ubuntu/dev/omegaup/stuff/database_utils.py", line 75, in mysql
    return subprocess.check_output(args, universal_newlines=True)
  File "/usr/lib/python3.8/subprocess.py", line 411, in check_output
    return run(*popenargs, stdout=PIPE, timeout=timeout, check=True,
  File "/usr/lib/python3.8/subprocess.py", line 489, in run
    with Popen(*popenargs, **kwargs) as process:
  File "/usr/lib/python3.8/subprocess.py", line 854, in __init__
    self._execute_child(args, executable, preexec_fn, close_fds,
  File "/usr/lib/python3.8/subprocess.py", line 1702, in _execute_child
    raise child_exception_type(errno_num, err_msg, err_filename)
FileNotFoundError: [Errno 2] No such file or directory: '/usr/bin/mysql'
error: failed to push some refs to 'https://github.com/user/omegaup'
```
Este error lo que nos indica es que no tenemos instalado MySQL en nuestra distribución de Linux. Lo único que hay que hacer en este caso es instalarlo (debe instalarse fuera del contenedor):
```shell
sudo apt-get install mysql-client
```
Si fuera el caso de que ya has instalado el cliente de MySQL, pero te aparece el siguiente error:
```shell
mysql: [Warning] Using a password on the command line interface can be insecure.
ERROR 2002 (HY000): Can't connect to local MySQL server through socket '/var/run/mysqld/mysqld.sock' (2)
Traceback (most recent call last):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 124, in <module>
    main()
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 120, in main
    args.func(args, auth)
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 66, in validate
    for statement_type, git_object_id in _missing(args, auth):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 53, in _missing
    if int(database_utils.mysql(
  File "/home/ubuntu/dev/omegaup/stuff/database_utils.py", line 75, in mysql
    return subprocess.check_output(args, universal_newlines=True)
  File "/usr/lib/python3.8/subprocess.py", line 411, in check_output
    return run(*popenargs, stdout=PIPE, timeout=timeout, check=True,
  File "/usr/lib/python3.8/subprocess.py", line 512, in run
    raise CalledProcessError(retcode, process.args,
subprocess.CalledProcessError: Command '['/usr/bin/mysql', '--user=root', '--password=omegaup', 'omegaup', '-NBe', 'SELECT COUNT(*) FROM `PrivacyStatements` WHERE `type` = "contest_optional_consent" AND `git_object_id` = "534d173d57e3814174ac02cc25f92e4253829d9c";']' returned non-zero exit status 1.
error: failed to push some refs to 'https://github.com/user/omegaup'
```
Quiere decir que MySQL no se ha configurado correctamente. Lo que tenemos que hacer es ejecutar el siguiente script (también desde fuera del contenedor):
```shell
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
# Instalación en Cloud VM
También puedes instalar el entorno de desarrollo en una máquina virtual que corra en la nube y conectarte a ella vía SSH.
* Crea una cuenta de prueba gratis en cualquier servicio de cloud computing:
    -   [Google Cloud Platform](https://cloud.google.com/free/).
    -   [Amazon Web Services](https://aws.amazon.com/free).
    -   [Microsoft Azure](https://azure.microsoft.com/en-us/free/).
    -   [Alibaba Cloud](https://www.alibabacloud.com/campaign/free-trial).
* Configura una máquina con Linux y conéctate a ella vía SSH. **Es importante que pongas como username `vagrant`**, o agrega un nuevo usuario `vagrant` después de que tu máquina este lista y corriendo y [agregalo al archivo sudoers](https://superuser.com/a/120342)..
* Descarga el repositorio [omegaup/deploy](https://github.com/omegaup/deploy/archive/master.zip) y extraelo en un directorio vacío.
* Corre el script [linux-install.sh](https://github.com/omegaup/deploy/blob/master/linux-install.sh). Espera unos minutos mientras termina.
* Abre el archivo `frontend/server/config.php` en un editor de texto y reemplaza `define('OMEGAUP_URL', 'http://localhost');` por `define('OMEGAUP_URL', 'http://IP');` donde `IP` es la dirección IP de tu VM.
* Abre `http://IP` en tu navegador web para acceder a tu instancia de omegaUp.
# Autenticación
Ya que está corriendo omegaup en tu máquina local, puedes acceder a http://localhost:8001/ para ver el sitio. Si haces login con
* `omegaup` (password `omegaup`): Usuario con privilegios administrativos.
* `user` (password `user`): Usuario normal.
También puedes crear más usuarios. No se pedirá confirmación de correo electrónico, así que puedes usar cualquier correo.
# Estructura del código #
El código se encuentra en `/opt/omegaup`. La instalación de desarrollo trae dos cuentas preconfiguradas: `omegaup` (administrador) y `user` (usuario normal). Su contraseñas son `omegaup` y `user`, respectivamente.
Estos son los directorios que estamos usando activamente en el desarrollo:
* [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers): La lógica de negocio que implementa el API de omegaUp.
* [frontend/server/src](https://github.com/omegaup/omegaup/tree/main/frontend/server/src): Bibliotecas y utilerías.
* [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO): Los Data Access Objects [DAO] y Value Objects [VO]. Clases utilizadas para representar los esquemas de la base de datos y facilitar su consumo por los controladores.
* [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates): plantillas de Smarty utilizadas para generar el HTML que se despliega a los usuarios. También aquí están los archivos de internacionalización para inglés, español y portugués.
* [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www): Los contenidos completos de la página de Internet.
* [frontend/www/js/omegaup/components](https://github.com/omegaup/omegaup/tree/main/frontend/www/js/omegaup/components): Aquí se encuentran todos los componentes de Vue que se han estado migrando.
Para más detalles, ver [aquí](/docs/Frontend.md).
# Cómo actualizar el código en la copia local #
Antes de comenzar a hacer cambios en el código, es recomendable actualizar el código a su versión más reciente, eso se puede hacer siguiendo estos pasos: [Actualizar copia local](/docs/C%C3%B3mo-actualizar-y-configurar-tu-copia-local-de-omegaup-antes-de-hacer-cambios.md).
# Cómo proponer cambios al código #
Cuando hayas hecho tus cambios al código, para que sean incorporados al repositorio de omegaUp, sigue [estos pasos](/docs/C%C3%B3mo-Hacer-un-Pull-Request.md).
# Sincronización con Windows #
Si utilizas un equipo con cualquier versión de Windows, te puede interesar como hacer la [sincronización con Windows](/docs/Sincronizaci%C3%B3n-con-Windows.md).
# Mis cambios no se ven reflejados!
Necesitas tener una sesión con `yarn run dev` corriendo desde `/opt/omegaup`.
# Mi VM no funciona :(
Abre un issue en https://github.com/omegaup/deploy/issues/new con toda la información que puedas para reproducir el problema. Copia y pega los logs y los mensajes de error que te salgan.