<?php

trait ControllerTrait
{
    private function StandardResponse($response, $payload){
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    private function EncodePayload($key, $value){
      return json_encode(array($key => $value), JSON_PRETTY_PRINT);
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
