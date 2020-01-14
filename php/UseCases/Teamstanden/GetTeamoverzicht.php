<?php

class GetTeamoverzicht implements IInteractorWithData
{
    public function Execute($data)
    {
        $teamnaam = $data->team ?? null;
        if ($teamnaam === null) {
            throw new \InvalidArgumentException("Teamnaam is leeg");
        }

        $filename = dirname(__FILE__) . '/teamoverzichten.json';
        if (!file_exists($filename)) {
            return "Overzichtsbestand bestaat niet";
        }

        $overzichten = json_decode(file_get_contents($filename));
        $teamoverzicht = $this->GetOverzichtForTeam($overzichten, $teamnaam);

        $poule = GetKlasse($teamoverzicht->poule);
        $klasseUrl = "https://www.volleybal.nl/competitie/poule/" . $teamoverzicht->poule . "/regio-west";
        $klasse = GetKlasse($teamoverzicht->poule);
        $trainers = [];
        foreach ($teamoverzicht->trainer as $trainer) {
            $trainers[] = $trainer->naam;
        }

        $stand = $teamoverzicht->stand;
        $uitslagen = $teamoverzicht->uitslagen;
        $programma = $teamoverzicht->programma;
        $trainingstijden = $teamoverzicht->trainingstijden;

        $coaches = [];
        foreach ($teamoverzicht->coaches as $coach) {
            $coaches[] = $coach->naam;
        }
        $facebook = $teamoverzicht->facebook;

        $template = file_get_contents("./UseCases/Teamstanden/templates/teamoverzicht.html");
        $template = str_replace("{{TEAMNAAM}}", $teamnaam, $template);
        $template = str_replace("{{KLASSE}}", GetKlasse($teamoverzicht->poule), $template);
        $template = str_replace("{{KLASSE_URL}}", $klasseUrl, $template);
        $template = str_replace("{{TRAINER}}", implode(", ", $trainers), $template);
        $template = str_replace("{{TRAININGSTIJDEN}}", $trainingstijden, $template);
        $template = str_replace("{{COACHES}}", implode(", ", $coaches), $template);
        $template = str_replace("{{POULE}}", $poule, $template);
        $facebookTemplate = "";
        if ($facebook) {
            $facebookTemplate = "<div href='#' class='list-group-item'><a style='color: #337ab7;' target='_blank' href='$facebook'><i class='fa fa-2x fa-facebook-official'></i></a></div>";
        }
        $template = str_replace("{{FACEBOOK}}", $facebookTemplate, $template);

        $html = "";
        foreach ($stand as $team) {
            $style = "";
            $number = substr($teamnaam, 5);

            $html .= "<tr style=" . ($team->team == $teamnaam ? "font-weight: bold;" : "") . ">
                        <td>" . $team->nummer . "</td>
                        <td>" . $team->team . "</td>
                        <td>" . $team->wedstrijden . "</td>
                        <td>" . $team->punten . "</td>
                      </tr>";
        }
        $template = str_replace("{{STAND}}", $html, $template);

        if (empty($uitslagen)) {
            $html = "<tr><td colspan=3>Nog geen uitslagen</td></tr>";
        } else {
            $html = "";
            foreach ($uitslagen as $uitslag) {
                $html .= "<tr>
                            <td>" . $uitslag->team1 . " - " . $uitslag->team2 . "</td>
                            <td>" . $uitslag->uitslag . "</td>
                            <td>" . implode(", ", $uitslag->setstanden) . "</td>
                          </tr>";
            }
        }
        $template = str_replace("{{UITSLAGEN}}", $html, $template);

        if (empty($programma)) {
            $html = "<tr><td colspan=3>Geen programma</td></tr>";
        } else {
            $html = "";
            foreach ($programma as $wedstrijd) {
                if (!$wedstrijd->timestamp){
                    continue;
                }
                $dateTime = new DateTime($wedstrijd->timestamp->date);
                $html .= "<tr>
                            <td>" . $dateTime->format("d-m-Y") . "</td>
                            <td>" . $dateTime->format("H:i") . "</td>
                            <td>" . $wedstrijd->team1 . " - " . $wedstrijd->team2 . "</td>
                            <td>" . $wedstrijd->locatie . "</td>
                          </tr>";
            }
        }
        $template = str_replace("{{PROGRAMMA}}", $html, $template);

        return $template;
    }

    private function GetOverzichtForTeam($overzichten, $teamnaam)
    {
        foreach ($overzichten as $overzicht) {
            if ($overzicht->naam == $teamnaam) {
                return $overzicht;
            }
        }
        return "Team $teamnaam niet gevonden in overzichtsbestand";
    }
}
