<?php
require_once 'vendor/autoload.php';
$app=new \Slim\Slim();

$db=new mysqli('localhost','root','','proyecto_angular');

//Cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}
//Listar productos
$app->get('/productos',function() use($app,$db){
  $sql="SELECT * FROM productos ORDER BY id DESC";
  $res = $db->query($sql);
  $productos=array();
  while($producto = $res->fetch_assoc()){
    $productos[]=$producto;
  }
  $result = array(
    'data'=> $productos
  );
  echo json_encode ($result);
});
//Devolver un productos
$app->get('/productos/:id', function($id) use($app,$db){
  $sql='SELECT * FROM productos WHERE id='.$id;
  $res=$db->query($sql);
  $result = array(
    'status'=> 'error'
  );
  if($res->num_rows ==1){
    $producto=$res->fetch_assoc();
    $result = array(
      'data'=> $producto
    );
  }
  echo json_encode($result);
});
//Eliminar productos
$app->get('/delete-producto/:id', function($id) use($app,$db){
  $sql='DELETE FROM productos WHERE id='.$id;
  $res=$db->query($sql);
  if($res){
    $result = array(
      'status'=> 'success'
    );
  }
  else{
    $result = array(
      'status'=> 'error'
    );
  }
  echo json_encode($result);
});
//Actualizar productos
$app->post('/update-producto/:id', function($id) use($app,$db){
  $json = $app->request->post('json');
  $data=json_decode($json,true);
  $sql="UPDATE productos SET ".
      "nombre='{$data['nombre']}',".
      "descripcion='{$data['descripcion']}',";
  if (isset($data['imagen'])) {
    $sql.="imagen='{$data['imagen']}',";
  }
  $sql.="precio='{$data['precio']}' WHERE id={$id}";
  $res=$db->query($sql);
  if($res){
    $result = array(
      'status'=> 'success'
    );
  }
  else{
    $result = array(
      'status'=> 'error'
    );
  }
  echo json_encode($result);
});
//Subir imagen a un producto
$app->post('/upload-file',function() use($app,$db){
  $result = array(
    'status'=> 'error'
  );
  if(isset($_FILES['uploads'])){
    $piramideUploader=new PiramideUploader();
    $upload=$piramideUploader->upload('image','uploads','uploads',array('image/jpeg','image/png','image/gif'));
    $file=$piramideUploader->getInfoFile();
    $file_name=$file['complete_name'];
    if(isset($upload) && $upload['uploaded']==false){
      $result = array(
        'status'=> 'error'
      );
    }
    else {
      $result = array(
        'status'=> 'success',
        'filename' => $file_name
      );
    }
  }
  echo json_encode($result);
});
//Agregar producto
$app->post("/productos", function() use($app,$db){
  $json=$app->request->post('json');
  $data=json_decode($json,true);
  if(!isset($data['nombre'])){
    $data['nombre']=null;
  }
  if(!isset($data['descripcion'])){
    $data['descripcion']=null;
  }
  if(!isset($data['precio'])){
    $data['precio']=null;
  }
  if(!isset($data['imagen'])){
    $data['imagen']=null;
  }

  $sql="INSERT INTO productos VALUE (NULL,".
      "'{$data['nombre']}',".
      "'{$data['descripcion']}',".
      "'{$data['precio']}',".
      "'{$data['imagen']}'".
      ");";
  $insert=$db->query($sql);

  $result = array(
    'status'=>'error'
  );

  if($insert){
    $result = array(
      'status'=> 'success'
    );
  }
  echo json_encode($result);
});
$app->run();
?>
