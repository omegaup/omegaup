> Opcionalmente a seguir los pasos descritos a continuación, se puede seguir la propia guía de la extensión SSH de vscode: https://code.visualstudio.com/docs/remote/ssh
## Requisitos
- Realizar todas las configuraciones especificadas en: https://github.com/omegaup/deploy
- [Visual Studio Code](https://code.visualstudio.com/)

## Instalación
1) Instalar el [Remote Pack](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack) en vscode
2) En vscode pulsar F1, escribir y seleccionar: "Remote SSH: Connect to Host...".
[(Imagen)](https://imgur.com/Camy9Wj)
3) Inicialmente, no existirán SSH hosts configurados, así que se debe seleccionar "Configure SSH Hosts...` [(Imagen)](https://imgur.com/JiBpsCL)
4) Seleccionar alguno de los archivos de configuración, o especificar uno nuevo. Para esta guía se seleccionó la primera opción. [(Imagen)](https://imgur.com/afxXFy5)
5) En la carpeta en que se encuentre el `Vagrantfile`, ejecutar: `vagrant ssh-config > config.txt`. Este comando cargará la configuración SSH en el archivo `config.txt`, la configuración podrá ser usada para conectar a través de vscode.
6) Copiar y guardar el contenido de `config.txt`, en el archivo de configuración que fue abierto en el paso 4. Dicho archivo quedaría de esta manera: https://imgur.com/PQgqAZt. Opcionalmente, el campo `Host default` puede ser renombrado por `omegaup`.
7) Seleccionar en el panel lateral de vscode el panel de `Remote SSH`, y escoger la conexión `default` (o el nombre que haya sido asignado en el paso anterior). Hacer click derecho sobre la conexión y seleccionar "Connect to Host in Current Window". La conexión será realizada y se podrá navegar en el sistema de archivos.