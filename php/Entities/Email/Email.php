<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\Utilities;

class Email
{
    public ?Persoon $sender;
    public Persoon $receiver;
    public string $titel;
    public string $body;
    public int $id;
    public string $signature;

    public function __construct($titel, $body, $receiver, $sender, $id)
    {
        $this->titel = $titel;
        $this->body = $body;
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->id = $id;
    }

    function SetSender(Persoon $persoon)
    {
        $this->sender = $persoon;
    }

    function IsValid(): bool
    {
        $isInvalid =
            Utilities::IsNullOrEmpty($this->receiver->naam) ||
            Utilities::IsNullOrEmpty($this->receiver->email) ||
            Utilities::IsNullOrEmpty($this->titel) ||
            Utilities::IsNullOrEmpty($this->body);
        return !$isInvalid;
    }

    function Build()
    {
        // $this->sender = $this->sender ?? new Persoon(-1, "SKC Studentenvolleybal", "info@skcvolleybal.nl");
        $this->sender = new Persoon(-1, "SKC Volleybal TeamTaken", "teamtakenco@skcvolleybal.nl");
        $this->CalculateSignature();
    }

    private function CalculateSignature(): void
    {
        $concatenatedEmail = $this->sender->email . $this->receiver->email . $this->titel . $this->body;
        $this->signature = hash("sha1", $concatenatedEmail);
    }
}
