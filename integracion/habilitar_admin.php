<?php
/* Opcional: un administrador LOCAL puede elegir su cuenta sin publicar credenciales. */
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require __DIR__.'/../app/config/database.php';
$db=(new Database())->connect();
$lista=$db->query('SELECT id_usuario,nombre_completo,rol FROM usuario ORDER BY id_usuario')->fetchAll(PDO::FETCH_ASSOC);
foreach($lista as $u)echo '#'.$u['id_usuario'].' · '.$u['nombre_completo'].' · rol:'.$u['rol'].PHP_EOL;
$id=filter_var(trim((string)readline('ID de la cuenta propia que será administrador (vacío: cancelar): ')),FILTER_VALIDATE_INT);
if(!$id){echo "Cancelado sin cambios.\n";exit;}
$q=$db->prepare('SELECT id_usuario FROM usuario WHERE id_usuario=:id');$q->execute(['id'=>$id]);
if(!$q->fetchColumn()){echo "No existe esa cuenta.\n";exit(1);}
if(trim((string)readline("Escribe SI para cambiar el rol de la cuenta #$id a admin: "))!=='SI'){echo "Cancelado sin cambios.\n";exit;}
$q=$db->prepare("UPDATE usuario SET rol='admin' WHERE id_usuario=:id");$q->execute(['id'=>$id]);
echo "Rol admin actualizado. Cierra sesión y vuelve a ingresar para renovar el rol en la sesión.\n";
