<?php

namespace TeamPortal;

class Configuration
{
    public DatabaseConnection $Database;
    public $DisplayErrors = true;
    public $JpathBase = "C:\\xampp\\htdocs"; # Example Xampp webserver
    public $AccessControlAllowOrigin = "http://localhost:4200"; 
    public $DwfUsername = "dwfusername"; # Obsolete
    public $DwfPassword = "dwfusername"; # Obsolete 

    function __construct()
    {
        $this->Database = new DatabaseConnection();
    }
}

class DatabaseConnection
{
    public string $Hostname = "localhost";
    public string $Name = "database_name";
    public string $Username = "database_username";
    public string $Password = "database_password";
    public array $Options = [
        "PDO::MYSQL_ATTR_INIT_COMMAND" => "SET NAMES utf8"
    ];
}