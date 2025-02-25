# Prerrequisitos
* Instalar la máquina virtual como se describe en [Cómo empezar a desarrollar](https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar)
* Supongamos que la máquina virtual quedó instalada en `c:\documentos\omegaup\vm`

# Software
* Descarga e instala WinSCP ([disponible aquí](https://winscp.net/eng/download.php))
* Descarga Puttygen ([disponible aquí](https://winscp.net/eng/downloads.php#putty))
* Descarga e instala Xming ([disponible aquí](http://www.straightrunning.com/XmingNotes/#head-121))

# Llave SSH
Vagrant utiliza una llave SSH para acceder a la máquina virtual en formato OpenSSH, necesitamos convertirla a formato Putty para poder utilizarla con WinSCP.
* Abre puttygen y presiona el botón _Load_

![Load Private Key](https://i.ibb.co/2SD56RT/Putty-Gen-Load.png)

* Busca la llave que generó Vagrant durante la instalación. Debería estar en una ruta parecida a
`C:\documentos\omegaup\vm\.vagrant\machines\default\virtualbox\private_key`, asegúrate que puedes ver el listado de todos los archivos:

![Search Private Key](https://i.ibb.co/W64Rm94/Search-Private-Key.png)

* Esto generará la nueva llave en el formato que requerimos, debes ver una pantalla similar a la siguiente:

![Generate Private Key](https://i.ibb.co/8r3PKXw/Generate-Private-Key.png)

* Una vez generada la llave, agrega una _Key Passphrase_ y después presiona el botón _Save Private Key_:

![Key Passphrase](https://i.ibb.co/FzKD7VM/Save-Private-Key-With-Key-Passphrase.png)

* Guarda la llave privada, digamos en el mismo directorio, pero en formato putty
`C:\documentos\omegaup\vm\.vagrant\machines\default\virtualbox\private_key.ppk`:

![Save Private  Key](https://i.ibb.co/Tht3jXh/Save-Private-Key.png)

# Configuración de WinSCP

* Una vez que cuentas con la llave en formato Putty, abre WinSCP y crea una nueva sesión con la siguiente información:

**Nombre o IP del servidor**: 127.0.0.1

**Puerto**: 2222

**Usuario**: vagrant

**Contraseña**: vagrant

![Create Session WinSCP](https://i.ibb.co/fM4LN3m/Create-Session-Win-SCP.png)

* Has clic en el botón _Avanzado_, en la nueva ventana ve a la opción _SSH > Autentificación_, en el campo _Archivo de clave privada_ selecciona la llave que recién creaste y presiona el botón _Aceptar_:

![SSH Authentication WinSCP](https://i.ibb.co/RY3nsDj/SSH-Authentication-Win-SCP.png)

* Haz clic en el botón _Guardar_, después coloca un nombre que identifique a tu sesión, por ejemplo omegaUp, da clic en _Aceptar_ y ahora te puedes conectar al Servidor, asegúrate que el protocolo seleccionado sea _SFTP_:

![Save Session WinSCP](https://i.ibb.co/Y8zYr04/Save-Session-Win-SCP.png)

* Será necesario que agregues la contraseña para la clave (`vagrant`):

![Key Password](https://i.ibb.co/vYkG6k5/Key-Password.png)

* Por último puedes configurar el editor de tu preferencia dando clic el icono de _configuración > Editor_:

![Setup Editor](https://i.ibb.co/yVW5TsM/Setup-Editor.png)

# Configuracion de Putty para usar Xming
* Definir