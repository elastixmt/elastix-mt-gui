En este directorio (/usr/share/elastix/asteriskconfig/bydomain/) se almacenan 
los generadores y plantillas para todos los objetos de plan de marcado 
implementados o instalados en Elastix 3. Para cada dominio que se cree o 
actualice, la implementación tendrá la oportunidad de agregar contextos que
implementen la funcionalidad deseada.

Para crear un nuevo tipo de objeto de plan de marcado de tipo XYZ:
1) Cree un nuevo directorio /usr/share/elastix/asteriskconfig/bydomain/XYZ
2) En el directorio creado, cree un archivo DialplanGenerator_XYZ.class.php
3) El archivo DialplanGenerator_XYZ.class.php debe definirse una clase con el 
   siguiente formato:
class DialplanGenerator_XYZ
{
    function __construct($domain, $pDB) {...}
    function createDialplanContexts() {}
}

El constructor de la clase recibe como parámetros, el dominio para el cual se
crea el plan de marcado, y una conexión de base de datos abierta a elxpbx.

La función createDialplanContexts() debe de devolver un arreglo con la siguiente
estructura:

array(
    // Texto de todos los contextos a incluir para implementar la funcionalidad
    'dialplan_text' =>  "...",
    
    // Lista de contextos que deben de incluirse en el contexto de primer nivel
    // del dominio, para implementar la funcionalidad
    'dialplan_includes' => array(...),
    
    // Mensaje de error en caso de fallo (con dialplan_text y dialplan_includes
    // puestos a NULL), o NULL en caso de éxito.
    'error_message' => NULL,
);

4) Implemente la funcionalidad deseada. El directorio XYZ puede usarse para 
   almacenar recursos adicionales (como plantillas) requeridos para construir
   el plan de marcado.

