# Release and Deployment  

## Proceso de Despliegue  

Utilizamos **GitHub Actions** para nuestra integración continua. Nuestro flujo de deployment se maneja de la siguiente manera:  

- **Despliegue a producción**: Se realiza automáticamente los fines de semana por la noche (hora del centro de México). Antes del despliegue, se ejecutan todas las pruebas automatizadas para garantizar la estabilidad del código.  
- **Despliegue a sandbox**: Cada vez que se realiza un merge a la rama principal (`main`), el código se despliega inmediatamente en [sandbox.omegaup.com](https://sandbox.omegaup.com). Esta instancia sirve como entorno de prueba antes de llegar a producción. El cual nos da un margen de maniobra en caso de encontrar un error en los cambios más recientes, que permitirán hacer rollback antes de que se haga un despliegue a producción. 
- **Hotfixes**: En caso de errores críticos en producción, se pueden hacer despliegues manuales siguiendo un proceso de validación interna. 

## Validaciones en CI/CD  

Antes de que un Pull Request (PR) sea aprobado y fusionado en `main`, debe pasar una serie de validaciones en **GitHub Actions**, lo que asegura la calidad del código y evita fallos en producción. Las pruebas incluyen:  

- **php**: Pruebas unitarias para los controladores, escritas en **PHPUnit**.  
- **javascript**: Pruebas unitarias para **Vue.js** escritas en **TypeScript + Jest**.  
- **lint**: Validadores de estilo y formato para todos los lenguajes utilizados en el proyecto, además de validadores de tipos en **Psalm** para PHP.  
- **cypress**: **Pruebas de extremo a extremo (E2E)** para validar el correcto funcionamiento de los flujos críticos de la plataforma.  
- **selenium**: Sistema utilizado para las pruebas de integración, actualmente **deprecado** en favor de Cypress.  
- **python**: Pruebas automatizadas escritas en **pytest**.  

## Cobertura de Código  

También utilizamos **Codecov** para medir la cobertura de las pruebas. Esto nos ayuda a identificar partes del código que no están siendo probadas, mejorando la calidad y confiabilidad del software.  

- Codecov mide la cobertura de las pruebas en **PHP y TypeScript**.  
- Actualmente, **falta medir la cobertura de las pruebas en Cypress**, lo cual es una tarea pendiente.  

