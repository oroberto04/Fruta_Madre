<?php
/** Sirve fotos del catálogo tanto con document-root en / como en /public. */
final class ImagenesStitchController {
    public static function mostrar():never {
        $nombre=(string)($_GET['f']??'');
        if(!preg_match('/^producto_[a-f0-9]{24}\.(jpg|png|webp)$/D',$nombre,$m)){
            http_response_code(404);exit('Imagen no encontrada.');
        }
        $f=dirname(__DIR__,2).'/public/images/productos/'.$nombre;
        if(!is_file($f)){http_response_code(404);exit('Imagen no encontrada.');}
        $tipos=['jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'];
        header('Content-Type: '.$tipos[$m[1]]);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: '.filesize($f));
        readfile($f);exit;
    }
}
