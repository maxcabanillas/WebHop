<h3 class="Titulo">Gestión Categorías - Locales</h3>
<table class="table table-bordered datatable">
    <thead>
      <tr>
        <th>#</th>
        <th>Nombre</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        if(isset($categorialocales)){
          foreach ($categorialocales as $index => $categoria) { ?>
            <tr>
              <td><?php echo $index+1 ?></td>
              <td><?php echo $categoria['CategoriaLocal']['nombre'] ?></td>
              <td>
                <a href="/Hop/CategoriaLocals/edit/<?php echo $categoria['CategoriaLocal']['id'] ?>">
                  <i class='icon icon-edit'></i>
                </a>
                <a href="/Hop/CategoriaLocals/delete/<?php echo $categoria['CategoriaLocal']['id'] ?>" onclick="return confirm('Está seguro que desea eliminar la categoría ?');">
                  <i class='icon icon-remove'></i>
                </a>
              </td>
            </tr>
    <?php } 
        } else{ ?>
          <tr>
            <td colspan='3'>No hay Categorías en la Base de Datos</td>
          </tr>
      <?php } ?>
    </tbody>
</table>
<a href="/Hop/CategoriaLocals/add" class="Agregar btn btn-primary">Agregar</a>