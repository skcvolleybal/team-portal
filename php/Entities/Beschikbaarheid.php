<?php

class Beschikbaarheid
{
    public ?int $id;
    public Persoon $persoon;
    public DateTime $date;
    public ?bool $isBeschikbaar;

    public function __construct(?int $id, Persoon $persoon, DateTime $date, ?bool $isBeschikbaar)
    {
        $this->id = $id;
        $this->persoon = $persoon;
        $this->date = $date;
        $this->isBeschikbaar = $isBeschikbaar;
    }

    public static function isFluitenMogelijk(array $wedstrijden, DateTime $tijd): ?bool
    {
        $bestResult = true;
        foreach ($wedstrijden as $wedstrijd) {
            $format = 'Y-m-d H:i';
            $timestring = DateFunctions::GetYmdNotation($tijd) . " " . DateFunctions::GetTime($tijd);

            $fluitwedstrijd = new Wedstrijd("fluitwedstrijd");
            $fluitwedstrijd->timestamp =  DateTime::createFromFormat($format, $timestring);
            $fluitwedstrijd->locatie = "Universitair SC";

            $isMogelijk = $wedstrijd->isMogelijk($fluitwedstrijd);
            if ($isMogelijk === false) {
                return false;
            }
            $bestResult = $isMogelijk === null ? null : $bestResult;
        }

        return $bestResult;
    }

    public static function IsBeschikbaar(array $beschikbaarheden, DateTime $date): ?bool
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->isBeschikbaar;
            }
        }
        return null;
    }
}
