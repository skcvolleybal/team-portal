<?php
include_once 'IInteractor.php';

class GetVoorpaginaRooster implements IInteractor
{
    private $roosterFilename;

    public function __construct()
    {
        $this->roosterFilename = dirname(__FILE__) . DIRECTORY_SEPARATOR . "rooster.json";
    }

    public function Execute()
    {

        if (!file_exists($this->roosterFilename)) {
            exit("File bestaat niet");
        }
        $rooster = json_decode(file_get_contents($this->roosterFilename), true);
        $result = "";
        foreach ($rooster as $wedstrijdDagen) {
            $zaalwacht = isset($wedstrijdDagen['zaalwacht']) ? " (Zaalwacht: " . $wedstrijdDagen["zaalwacht"] . ")" : "";
            $result .= "
            <div class='panel panel-primary'>
              <div class='panel-heading'>" . ucwords($wedstrijdDagen['datum']) . $zaalwacht . "</div>
              <table class='table'>
                <thead>
                  <tr>
                    <th>Tijd</th>
                    <th>Teams</th>
                    <th>Fluiten</th>
                    <th>Tellen</th>
                  </tr>
                </thead>
                <tbody>";
            foreach ($wedstrijdDagen["wedstrijden"] as $wedstrijd) {
                $result .= "
                    <tr>
                      <td>" . $wedstrijd["tijd"] . "</td>
                      <td>" . $wedstrijd["teams"] . "</td>
                      <td>" . $wedstrijd["scheidsrechter"] . "</td>
                      <td>" . $wedstrijd["tellers"] . "</td>
                    </tr>";
            }
            $result .= "
                </tbody>
              </table>
            </div>";
        }
        echo $result;
    }
}
