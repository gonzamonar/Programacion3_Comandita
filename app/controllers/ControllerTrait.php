<?php

trait ControllerTrait
{
    private function StandardResponse($response, $payload){
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    private function EncodeFetchPayload($messageBody, $key, $value){
      return json_encode(array('mensaje'=> $messageBody, $key => $value), JSON_PRETTY_PRINT);
    }

    private function EncodePayload($key, $value){
      return json_encode(array($key => $value), JSON_PRETTY_PRINT);
    }

    private function CrearMensaje($resultado, $detalle){
      return array('resultado'=> $resultado, 'detalle' => $detalle);
    }

    private function GetTokenPermission(){
      $headers = getallheaders();
      $token = NULL;

      if (isset($headers['Authorization']) && !empty($headers['Authorization'])){
          $token = explode("Bearer ", $headers['Authorization'])[1];
          
      }

      return $token;
    }
}

trait ImgControllerTrait
{
    public static function SaveUploadedImage($request, $rootdir, $filename, $extension = '.jpg', $uploadParam = 'foto') : bool {
      $result = false;
      $uploadedFiles = $request->getUploadedFiles();
      if (isset($uploadedFiles[$uploadParam])){
          $file = $uploadedFiles[$uploadParam];
          if ($file->getError() === UPLOAD_ERR_OK) {
            if (!file_exists($rootdir)) {
              mkdir($rootdir, 0777, true);
            }
            $filepath = $rootdir . DIRECTORY_SEPARATOR . $filename . $extension;
            $file->moveTo($filepath);
            if (file_exists($filepath)) {
              $result = true;
            }
          }
      }
      return $result;
  }
}