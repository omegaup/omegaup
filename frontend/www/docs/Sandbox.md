Sandbox es una versión *muy* modificada de Moeval (el sandbox que usan en la IOI), escrito por Martin Mares. En esencia, Sandbox es un debugger que utiliza la llamada de sistema ptrace de Linux para detener el proceso cada vez que intenta hacer una llamada de sistema. Sandbox examina esta llamada de sistema para ver si son inocuas o peligrosas, y puede:

1. Permitir que la syscall proceda normalmente.
2. Reemplazar la syscall llamada (por ejemplo, setrlimit por getuid, que es muy inocua) y luego hacerle creer al proceso que hubo un error al mandar llamar la syscall. Esto se usa para fingir que no hay red (todas las llamadas a socket regresan -1).
3. Matar el proceso, si la syscall es MUY evil.

Algunas de las modificaciones que hice a Sandbox que no están en Moeval son:

1. Soporte para syscall mangling
2. Soporte para múltiples threads.
3. Un modo verbose para examinar qué syscalls fueron hechas.
4. Normalización de paths (para poder decir, por ejemplo, que ./ es escribible también).
5. Leer parámetros de un archivo (para hacer perfiles para distintos compiladores/intérpretes).
6. Muchas, muchas, muchas, muchas mejoras pequeñas.
7. Una versión de Moeval que no usa ptrace (esa sí es cross-platform, pero no te ofrece tanta seguridad)

El único que necesita ejecutar Sandbox en cualquier punto es Runner.