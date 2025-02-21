_HappyMeal_ (o _CajitaFeliz_) es el codename para el sistema de administración de usuarios, problemas y concursos standalone. Estará escrito en Scala y embebe _UX_. Debe cumplir con los siguientes casos de uso:

* Core de seguridad de OmegaUp (cosas como permisos, roles, login, logout)
* ABC de problemas (yo digo que por default, _HappyMeal_ presente un concurso con todos los problemas dados de alta)
* Modificación de propiedades del concurso (estilo, fecha de inicio, duración)
* ABC de usuarios (poder darlos de alta, modificarlos, etc.)
* Ser un frontend para _Arena_ (envío de problemas, ver status de envío, rejueceo de problemas completos y/o corridas)
* Scoreboard
* Modo de exportación para auditoría y/o reintegración con el OmegaUp maestro.

La idea es que _HappyMeal_ esté totalmente autocontenido en un solo .jar, que le des doble click y lo puedas configurar vía web. La base de datos será H2 para evitar dependencias. Todo el concurso será vía web para evitarnos broncas, y podemos usar toda la magia jQueryosa de _Frontend_ para evitar la repetición. Podemos hacer una versión portátil de la zorra de fuego que tenga el certificado autofirmado que usa OmegaUp para que no se queje de esas cosas.