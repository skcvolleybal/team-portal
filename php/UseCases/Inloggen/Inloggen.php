<?php

include 'IInteractorWithData.php';
include 'UserGateway.php';
include 'NevoboGateway.php';

class GetMijnOverzichtInteractor implements IInteractorWithData
{
   public function __construct($database)
   {
      $this->userGateway = new UserGateway($database);
   }

   private $userGateway;

   public function Execute($data)
   {
      $username = $data->username;
      $password = $data->password;
      if ($this->userGateway->Login($username, $password)){
         exit("asdqwe");   
      }
      else {
         header("HTTP/1.1 500 Internal Server Error");
         exit;
      }
   } 
}
