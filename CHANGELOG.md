CHANGELOG
=========

## `7.0.0` (23/11/2020)

¡Nueva versión 7.0!

Esta es una nueva versión mayor por que introduce cambios que rompen la estructura y la filosofía de URLs actual y se introducen los nombres de espacios.

A partir de esta versión, el Framework requiere el uso de PHP 8.0 o superior para funcionar ya que el sistema de enrutamiento actual se basa en las nuevas anotaciones de PHP 8.0

### Rutas

En la versión anterior las rutas se definían usando bloques de comentarios en cada función de los módulos. Por ejemplo:

```php
/**
 * Módulo API de prueba
 *
 * @type json
 * @prefix /api
 */
class api extends OModule {
	/**
	 * Función para obtener la fecha
	 *
	 * @url /getDate
	 * @param ORequest $req Request object with method, headers, parameters and filters used
	 * @return void
	 */
	public function getDate(ORequest $req): void {
		...
	}
}
```

A partir de esta versión, se usará el nuevo sistema de anotaciones de PHP 8.0. Por ejemplo:

```php
/**
 * Módulo API de prueba
 */
#[ORoute(
	type: 'json',
	prefix: '/api'
)]
class api extends OModule {
	/**
	 * Función para obtener la fecha
	 *
	 * @param ORequest $req Request object with method, headers, parameters and filters used
	 * @return void
	 */
	#[ORoute('/getDate')]
	public function getDate(ORequest $req): void {
		...
	}
}
```

Los anteriores comentarios ahora se traducen como parámetros de la función `ORoute`:

```
@url  /ejemplo
@type json
@prefix /api
@filter testFilter

#[ORoute(
	'/ejemplo',
	type: 'json',
	prefix: '/api',
	filter: 'testFilter'
)]
```

### Nombres de espacios

Con la idea de homogeneizar el código base del Framework y organizar mejor la aplicación, se ha organizar todo el código en nombres de espacios. Las tareas encargadas de crear nuevos módulos, servicios, componentes o tareas también se han actualizado para que los incorporen. Ahora en cada archivo hay que incluir los nombres de espacio al que corresponda el archivo y los nombres de espacio de las funciones que se quieren usar.

Estos son los nombres de espacio para los archivos que puede crear un usuario del Framework:

* `OsumiFramework\App\Model`: Espacio para las clases de modelo de la base de datos del usuario.
* `OsumiFramework\App\Module`: Espacio para las clases de los módulos que componen la aplicación.
* `OsumiFramework\App\Service`: Espacio para las clases de servicios que se utilizan en los módulos.
* `OsumiFramework\App\Task`: Espacio para las tareas propias de la aplicación.

Las clases internas del Framework ahora se organizan del siguiente modo:

* `OsumiFramework\OFW\Cache`
  * `OCache`
  * `OCacheContainer`
  * Estas clases se han rehecho basándose en [PSR-6](https://www.php-fig.org/psr/psr-6).
* `OsumiFramework\OFW\Core`
  * `OConfig`
  * `OCore`
  * `OModule`
  * `OPlugin`
  * `OService`
  * `OTask`
  * `OTemplate`
  * `OUpdate`
* `OsumiFramework\OFW\DB`
  * `ODB`
  * `ODBContainer`
  * `OModel`
* `OsumiFramework\OFW\Log`
  * `OLog`
* `OsumiFramework\OFW\Routing`
  * `ORoute`
  * `ORouteCheck`: Esta clase substituye a la librería anteriormente usada para el enrutamiento, aunque sigue estando basada en ella.
  * `OUrl`
* `OsumiFramework\OFW\Tools`
  * `OColors`
  * `OForm`
  * `OTools`
* `OsumiFramework\OFW\Web`
  * `OCookie`
  * `ORequest`
  * `OSession`

De este modo las clases internas quedan organizadas en espacios separados y se asegura su uso correcto al tener que inicializarlas. Por ejemplo, un módulo que utiliza una clase de servicio, tendría este aspecto ahora:

```php
namespace OsumiFramework\App\Module;

use OsumiFramework\OFW\Core\OModule;
use OsumiFramework\OFW\Web\ORequest;
use OsumiFramework\OFW\Routing\ORoute;
use OsumiFramework\App\Service\userService;
```

## `6.6.0` (23/11/2020)

Nuevas funciones `modelComponent` y `modelComponentList` para obtener una representación JSON de un objeto de modelo.

Estas funciones se han incluido en `OTemplate` para ser usadas directamente en templates. Por ejemplo:

```
En el módulo API (app/modules/api):

$user = new User();
$user->find(['id' => 42]);

$this->getTemplate()->addModelComponent('user', $user);

O para mostrar una lista:

$users = $this->user_service->getUsers();

$this->getTemplate()->addModelComponentList('users', $users);

El template sería:

{
  "users": {{users}}
}
```

En la clase de utilidades `OTools` también se ha incluido esta función, llamada `OTools::getModelComponent`, de modo que puede ser usada desde cualquier parte de la aplicación.

Las funciones `addModelComponent` y `addModelComponentList` admiten 4 parámetros:

```
addModelComponent(
  $where -> Clave en la que se reemplazará el resultado en el template,
  $object -> Objeto de modelo,
  $exclude -> Lista de campos del objeto que deben excluirse (por ejemplo el campo contraseña al obtener un listado de usuarios),
  $empty -> Lista de campos que sí que se devolverán, pero su valor será nulo (por ejemplo para obtener una representación de la tabla usuarios, pero sin obtener las contraseñas de los usuarios)
)

addModelComponentList(
  $where,
  $list -> lista con objetos de modelo (no tienen por que ser iguales),
  $eclude,
  $empty
)
```

La función `OTools::getModelComponent` admite 3 parámetros:

```
getModelComponent(
  $object,
  $exclude,
  $empty
)
```

Usando estas funciones se evitará tener que crear componentes para cada modelo de la base de datos.

## `6.5.1` (21/11/2020)

Si se define un entorno, por ejemplo `prod`, ahora ya no es obligatorio que haya un archivo de configuración para ese entorno.

De este modo se puede definir que una aplicación está en producción y así aprovecharse de la minimización de los resultados de las llamadas JSON.

## `6.5.0` (21/11/2020)

Si el entorno está definido como `prod` (producción), el resultado de las llamas de tipo JSON será minimizado.

Para hacer esto, se ha incluido una nueva función llamada `OTools::minimifyJSON` a la que con pasar cualquier JSON, devuelve el mismo pero minimizado.

## `6.4.0` (25/10/2020)

Nueva función `OTools::getComponent`. Con esta función se puede acceder a los componentes (no a sus estilos o scripts) desde cualquier parte: un service, un task, desde otro componente...

## `6.3.4` (24/10/2020)

Corrección en la función `runTask` de `OTools`. Al llamar desde un módulo a una tarea usando esta función, no se cargaban los objetos auxiliares y la tarea no podía acceder ni a la configuración de la aplicación ni a los logs.

## `6.3.3` (12/10/2020)

Corrección en `ORequest` para los valores numéricos. La comprobación de valores nulos no era estricta y los valores `0` los devolvía como `null`.

## `6.3.2` (06/10/2020)

Corrección en `ORequest` para los valores numéricos. He añadido una validación más, en caso de que se envíase `"null"` como número, se devolvía el valor `0` en lugar de `null`.


## `6.3.1` (05/10/2020)

Corrección en `ORequest` para los valores booleanos. He cambiado la función usada para comprobar si un valor es booleano ya que la anterior daba como verdadero el valor `"false"`.

## `6.3.0` (24/09/2020)

Ahora los datos de la sesión del navegador se encuentran dentro de la variable global `$core`, accesibles desde cualquier parte usando `$core->session`. Al igual que antes, se sigue pudiendo crear nuevas instancias del objeto sesión usando `$session = new OSession();` pero de este modo todos los componentes tienen acceso a esta variable ya inicializada.

## `6.2.0` (16/09/2020)

Nueva función `getEnvironment` en `OConfig` para obtener el entorno en el que se está ejecutando la aplicación.

Puede servir para configurar variables dependiendo del entorno, mostrar u ocultar trazas o valores...

## `6.1.0` (30/07/2020)

Nuevos componentes web. Inspirandome en los WebComponents, he cambiado la forma y estructura de los partials para mejorarlos y vitaminarlos. Este es un breaking change ya que cambia la estructura de las carpetas del Framework.

La carpeta `app/template`, que incluía las carpetas `layout` y `partials`, desaparece:

* app
  * template
    * layout
	* partials

Así queda su nueva estructura:

* app
  * component
  * layout

Dentro de la carpeta `component` se crean los nuevos componentes reutilizables de la siguiente manera: una carpeta para cada componente y dentro un archivo php con el mismo nombre del componente. Pero ahora se puede añadir opcionalmente un archivo `config.json` con el que indicar archivos `css` y `js` que acompañarán al componente:

```json
{
	"css": ["css1", "css2"],
	"js": ["js1", "js2"]
}
```

Por lo que para un `component` llamado `header`, por ejemplo, que tuviese ese archivo `config.json` la estructura sería la siguiente:

* app
  * component
    * header
	  * header.php
	  * config.json
	  * css1.css
	  * css2.css
	  * js1.js
	  * js2.js

Estos archivos CSS y JS, ya que no están accesibles desde una llamada web, al usar un `component` se incluyen embebidos directamente en el HTML resultante:

```html
<html>
  <head>
    <style type="text/css">
		header { ... }
	</style>
	<script>
      ...
	</script>
```

En el caso de que un componente se utilice varias veces, el framework lo comprueba y no incluye los archivos CSS/JS repetidos, los incluye una sola vez.

He aprovechado para mejorar al aplicación demo que acompaña al Framework y ahora Lighthouse le da una puntuación de 100 en cada apartado.

![Lighthouse](https://framework.osumi.es/img/ofw-6.1.0.png)


## `6.0.1` (07/07/2020)

Corrección al crear una nueva acción. Al realizar la comprobación para ver si una acción ya existía previamente, si había una acción que tuviese un nombre que empezase igual fallaba.

Por ejemplo, al intentar crear la acción `sync`, si ya existía una acción llamada `syncStock` fallaba por que la acción existente empieza igual que la nueva que se quiere crear.

## `6.0.0` (03/07/2020)

¡Nueva versión 6.0!

Esta es una nueva versión mayor por que introduce cambios que rompen la estructura  y la filosofía de URLs actual.

Esta nueva versión tiene cuatro puntos principales:

* Nuevo sistema de URLs
* Menos archivos de configuración
* Configuración de los plugins
* Nueva tarea `add`

### Nuevo sistema de URLs

Hasta ahora la configuración de las URLs se basaba en el archivo `urls.json`. Este archivo contenía una relación de URLs y los módulos/acciones que se debían ejecutar. En este archivo también se indicaba si una URL debía ejecutar un filtro o el tipo de retorno que debía devolver.

A partir de ahora, este archivo desaparece y son las propias acciones, en su documentación phpDoc, las que definen toda esta información. Por ejemplo:

```php
/**
 * API para la aplicación prueba
 *
 * @prefix /api
 * @type json
 */
class api extends OModule {
	/**
	 * Función para obtener un usuario
	 *
	 * @url /getUser
	 * @param ORequest $req Request object with method, headers, parameters and filters used
	 * @return void
	 */
	 function getUser(ORequest $req): void { ... }

	 /**
 	 * Función para obtener la lista completa de usuarios, pero en XML
 	 *
 	 * @url /getUserList
	 * @type xml
	 * @filter userFilter
 	 * @param ORequest $req Request object with method, headers, parameters and filters used
 	 * @return void
 	 */
 	 function getUserList(ORequest $req): void { ... }
}
```

### Menos archivos de configuración

Con el nuevo sistema de URLs, el archivo de configuración `urls.json` desaparece. A su vez, ahora se prescinde del archivo `plugins.json` que se creaba y mantenía automáticamente al instalar plugins. Ahora el propio Framework lee la carpeta donde están instalados los plugins y ya no es necesario este archivo.

El archivo `translations.json` se incluía en cada instalación, a pesar de ser solo necesario en el caso de que se instalase el plugin `OTranslate`.

De este modo se ha pasado de cuatro archivos de configuración a uno solo: `config.json`

### Configuración de los plugins

El plugin `OEmailSMTP` se configuraba mediante valores en el archivo `config.json`, y estos valores eran almacenados como parte de la configuración del Framework. A partir de esta versión, el Framework prescinde de todo tipo de configuración o valores externos y la configuración de los plugins se realiza con el archivo `config.json` solo en caso de que el plugin lo requiera.

Por ejemplo, al instalar el plugin `OEmailSMTP` (llamado `email_smtp` en el repositorio de Plugins), automáticamente se creará un apartado llamado `plugins` en el archivo `config.json`, que a su vez contendrá un apartado llamado `email_smtp` con todos los valores de configuración del plugin.

En caso de desinstalar el plugin, este apartado de configuración se eliminará automáticamente.

De este modo el Framework solo contendrá la configuración del propio Framework y el resto serán valores extra auxiliares.

### Nueva tarea `add`

La nueva tarea `add` sirve para crear nuevos módulos, acciones, servicios o tareas. En lugar de escribir manualmente nuevos archivos `php`, usando esta nueva tarea el trabajo se reduce a un solo comando, reduciendo el trabajo y la posibilidad de introducir errores.

**Nuevo módulo**

Comando: `php ofw.php add module (nombre del módulo)`

Este comando crea un nuevo módulo en la carpeta `modules`, su archivo php y su carpeta para `templates`. Se comprueba que no exista un módulo con el nombre indicado antes de crear el nuevo.

**Nueva acción**

Comando: `php ofw.php add action (módulo) (nombre de la acción) (URL)`

Opcionalmente, también se puede indicar un último parámetro indicando el tipo (por defecto es `html`).

Este comando crea una nueva acción en el módulo indicado. Crea su función, su configuración en su respectivo apartado phpDoc y el archivo `template` necesario. Se comprueba que el módulo indicado exista y que la acción indicada no exista.

**Nuevo servicio**

Comando: `php ofw.php add service (nombre del servicio)`

Este comando crea un nuevo servicio vacío que puede ser usado en cualquier módulo. Crea su archivo php en la carpeta `services`.

**Nueva tarea**

Comando: `php ofw.php add task (nombre de la tarea)`

Este comando crea una nueva tarea vacía que puede ser usada tanto en las acciones como desde el CLI. Crea su archivo php en la carpeta `task`.


*Esta actualización tiene una tarea `postinstall` que actualiza automáticamente todas las acciones para que usen el nuevo sistema de URLs.*
