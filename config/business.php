<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vendedor preseleccionado en el formulario de creación manual
    |--------------------------------------------------------------------------
    |
    | Id del usuario que aparece preseleccionado en el selector "Vendedor" del
    | formulario de creación de pedidos. Vive en el entorno y no en el código
    | para no fijar un id de producción en el repositorio.
    |
    | Si queda sin definir, apunta a un usuario inactivo, a la cuenta de
    | integración o a un id inexistente, simplemente no hay preselección: el
    | selector arranca vacío. Nunca asigna un vendedor por su cuenta.
    |
    */

    'default_seller_user_id' => env('DEFAULT_SELLER_USER_ID'),

];
