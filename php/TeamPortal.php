<?php


class TeamPortal
{
    public function __construct($config)
    {
        $this->database = new Database(
            $config->host,
            $config->database,
            $config->username,
            $config->password,
            $config->options
        );
    }

    public function NoAction()
    {
        throw new UnexpectedValueException("No action specified");
    }

    public function UnknownAction($action)
    {
        throw new UnexpectedValueException("Unknown function '$action'");
    }

    public function IsWebcie()
    {
        $interactor = new IsWebcie($this->database);
        $interactor->Execute();
    }

    public function GetUsers()
    {
        $interactor = new GetUsers($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetCurrentUser()
    {
        $interactor = new GetCurrentUser($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetMijnOverzicht()
    {
        $interactor = new GetMijnOverzichtInteractor($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdOverzicht()
    {
        $interactor = new GetWedstrijdOverzicht($this->database);
        $interactor->Execute();
    }

    public function UpdateAanwezigheid()
    {
        $interactor = new UpdateAanwezigheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetFluitBeschikbaarheid()
    {
        $interactor = new GetFluitBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function UpdateFluitBeschikbaarheid()
    {
        $interactor = new UpdateFluitBeschikbaarheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetScheidscoOverzicht()
    {
        $interactor = new GetScheidscoOverzicht($this->database);
        $interactor->Execute();
    }

    public function GetScheidsrechters()
    {
        $interactor = new GetScheidsrechters($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetTelTeams()
    {
        $interactor = new GetTelTeams($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetZaalwachtTeams()
    {
        $interactor = new GetZaalwachtTeams($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateScheidsrechter()
    {
        $interactor = new UpdateScheidsrechter($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateTellers()
    {
        $interactor = new UpdateTellers($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateZaalwacht()
    {
        $interactor = new UpdateZaalwacht($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function Login()
    {
        $interactor = new Inloggen($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function QueueWeeklyEmails()
    {
        $interactor = new QueueWeeklyEmails($this->database);
        $interactor->Execute();
    }

    public function SendQueuedEmails()
    {
        $interactor = new SendQueuedEmails($this->database);
        $interactor->Execute();
    }

    public function GetCalendar()
    {
        $interactor = new GetCalendar($this->database);
        $interactor->Execute();
    }

    public function GetBarcieBeschikbaarheid()
    {
        $interactor = new GetBarcieBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function WedstrijdenImporteren()
    {
        $interactor = new WedstrijdenImporteren($this->database);
        $interactor->Execute();
    }

    public function GetDwfPunten()
    {
        $data = GetQueryParameters();
        $interactor = new GetDwfPunten($this->database);
        exit(json_encode($interactor->Execute($data)));
    }

    public function GetVoorpaginaRooster()
    {
        $interactor = new GetVoorpaginaRooster($this->database);
        exit(json_encode($interactor->Execute()));
    }

    public function SetAllFluitBeschikbaarheden()
    {
        $interactor = new SetAllFluitbeschikbaarheden($this->database);
        print_r($interactor->Execute());
    }

    public function SetAllBarcieBeschikbaarheden()
    {
        $interactor = new SetAllBarcieBeschikbaarheden($this->database);
        print_r($interactor->Execute());
    }

    public function GenerateTeamstanden()
    {
        $interactor = new GenerateTeamstanden($this->database);
        print_r($interactor->Execute());
    }

    public function GenerateTeamoverzichten()
    {
        $interactor = new GenerateTeamoverzichten($this->database);
        print_r($interactor->Execute());
    }

    public function CompleteDailyTasks()
    {
        $queryParameters = GetQueryParameters();
        $interactor = new CompleteDailyTasks($this->database);
        $interactor->Execute($queryParameters);
    }

    public function GetTeamstanden()
    {
        $queryParameters = GetQueryParameters();
        $interactor = new GetTeamstanden();
        $interactor->Execute($queryParameters);
    }

    public function GetTeamoverzicht()
    {
        $queryParameters = GetQueryParameters();
        $interactor = new GetTeamoverzicht();
        $interactor->Execute($queryParameters);
    }

    public function UpdateBarcieBeschikbaarheid()
    {
        $postData = GetPostValues();
        $interactor = new UpdateBarcieBeschikbaarheid($this->database);
        $interactor->Execute($postData);
    }

    public function GetGroups()
    {
        $interactor = new GetGroups($this->database);
        $interactor->Execute();
    }

    public function GetBarcieBeschikbaarheden()
    {
        $queryParameters = GetQueryParameters();
        $interactor = new GetBarcieBeschikbaarheden($this->database);
        $interactor->Execute($queryParameters);
    }

    public function AddBarcieAanwezigheid()
    {
        $postData = GetPostValues();
        $interactor = new AddBarcieAanwezigheid($this->database);
        $interactor->Execute($postData);
    }

    public function DeleteBarcieAanwezigheid()
    {
        $postData = GetPostValues();
        $interactor = new DeleteBarcieAanwezigheid($this->database);
        $interactor->Execute($postData);
    }

    public function GetBarcieRooster()
    {
        $interactor = new GetBarcieRooster($this->database);
        $interactor->Execute();
    }

    public function ToggleBhv()
    {
        $postData = GetPostValues();
        $interactor = new ToggleBhv($this->database);
        $interactor->Execute($postData);
    }

    public function AddBarcieDag()
    {
        $postData = GetPostValues();
        $interactor = new AddBarcieDag($this->database);
        $interactor->Execute($postData);
    }

    public function DeleteBarcieDag()
    {
        $postData = GetPostValues();
        $interactor = new DeleteBarcieDag($this->database);
        $interactor->Execute($postData);
    }

    public function GetGespeeldePunten()
    {
        $postData = GetPostValues();
        $interactor = new GetGespeeldePunten($this->database);
        exit(json_encode($interactor->Execute($postData)));
    }
}
