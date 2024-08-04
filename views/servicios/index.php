<h1 class="nombre-pagina">Servicios</h1>
<p class="descripcion-pagina">Administrador de Servicios</p> 

<?php
    include_once __DIR__ . '/../templates/barra.php';
?>

<ul class="servicios">

    <?php foreach($servicios as $servicio){ ?>
        <li>
            <p>Nombre: <span><?php echo $servicio->nombre; ?></span></p>
            <p>Precio: <span><?php echo $servicio->precio . ' bs'; ?></span></p>

            <div class="acciones">
                <a href="/servicios/actualizar?id=<?php echo $servicio->id; ?>" class="boton">Actualizar</a> 

                <form action="/servicios/eliminar" method="POST">
                    <input type="submit" value="Eliminar" class="boton-eliminar">   
                    <input type="hidden" value="<?php echo $servicio->id; ?>" name="id">
                </form>
            </div>
        </li>
  <?php } ?>
</ul>