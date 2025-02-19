
[Also available in English](https://github.com/omegaup/omegaup/wiki/How-to-Set-Up-Your-Development-Environment-(English))

## Contenido
* [Configuración de comandos de línea de Git](#configuración-de-comandos-de-línea-de-git)
* [Levanta la máquina virtual](#levanta-la-máquina-virtual)
* [Solucionador de problemas](#solucionador-de-problemas)
* [Autenticación](#autenticación)

## Videotutorial
[![Videotutorial](http://img.youtube.com/vi/H1PG4Dvje88/0.jpg)](http://www.youtube.com/watch?v=H1PG4Dvje88 "OmegaUp Localhost Setup Video Tutorial")

## Entorno de desarrollo
Instala [`docker-compose`](https://docs.docker.com/compose/install/).

Si estás usando Linux, después de instalar [`docker-compose`](https://docs.docker.com/compose/install/) hay que ejecutar `sudo usermod -a -G docker $USER` y cerrar sesión / volver a iniciar sesión para que puedas ejecutar los comandos de docker.

## Configuración de comandos de línea de Git

Una vez instalado Docker, crea un fork del repositorio [omegaup/omegaup](https://github.com/omegaup/omegaup) y clónalo a un directorio:

```shell
git clone --recurse-submodules https://github.com/TUUSUARIODEGITHUB/omegaup
cd omegaup
```

Si ya tienes el repositorio clonado, en la raíz del repositorio corre:
```shell
git submodule update --init --recursive
```

Si aún no te sientes muy cómodo o hábil con Git entonces puedes leer [este tutorial](https://github.com/shekhargulati/git-the-missing-tutorial).


## Levanta la máquina virtual

Dentro de ese directorio (`/omegaup`) ejecuta

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

Para abrir la consola y correr comandos dentro del contenedor, hay que ejecutar

```shell
docker exec -it omegaup-frontend-1 /bin/bash
```

## Solucionador de problemas

Si tu navegador cambia http por https, puedes deshabilitar las políticas de seguridad para localhost. [Mas...](https://hmheng.medium.com/exclude-localhost-from-chrome-chromium-browsers-forced-https-redirection-642c8befa9b).

-------------------

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

## Autenticación

Ya que está corriendo omegaup en tu máquina local, puedes acceder a http://localhost:8001/ para ver el sitio. Si haces login con

* `omegaup` (password `omegaup`): Usuario con privilegios administrativos.
* `user` (password `user`): Usuario normal.

También puedes crear más usuarios. No se pedirá confirmación de correo electrónico, así que puedes usar cualquier correo.
