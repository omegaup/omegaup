# Release and Deployment

Utilizamos Github Actions para nuestra integración continua. En la noche de los fines de semana (hora del centro México) se realiza el deployment a producción. 
Cuando le dan merge a un PR se hace un deployment inmediatamente a sandbox.omegaup.com

También tenemos en Github Actions test de integración, es decir, tu PR debe pasar cada uno de los siguientes:

 - php : pruebas unitarias para controladores escritas en PHPUnit
 - javascript : pruebas unitarias para Vue escritas en TS
 - lint : ejecuta los linters y también los checks de Psalm
 - cypress : End-to-end testing framework
 - selenium : Próximamente deprecado, se migrará a Cypress
 - python : Escritas en pytest
 - Codecov

