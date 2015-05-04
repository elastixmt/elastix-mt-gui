<html>
  <title>{$titulo}</title>
  <frameset cols="20%,80%" border="1" > 
    <frame src="frameLeft.php?id_nodo={$id_nodo}" name="navegacion" noresize>
    <frame src="frameRight.php?id_nodo={$id_nodo}&name_nodo={$name_nodo}" name="contenido" noresize>
  </frameset>
</html>
