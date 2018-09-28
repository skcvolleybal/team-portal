<?
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

class TeamPortal
{
    public function UnknownAction($action)
    {
        header("HTTP/1.1 500 Internal Server Error");
        "Unknown function '$action'";
    }
    
    public function GetMijnOverzicht()
    {
        include 'UseCases/MijnOverzicht/GetMijnOverzicht';
        echo GetMijnOverzicht();
    }
}
