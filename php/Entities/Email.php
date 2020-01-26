<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\Utilities;

class Email
{
    public ?int $id;
    private ?Persoon $sender;
    private Persoon $receiver;
    private string $titel;
    public string $body;
    public string $signature;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    function __construct(
        string $titel,
        string $body,
        Persoon $receiver,
        Persoon $sender = null,
        int $id = null
    ) {
        $this->id = $id;
        $this->sender = $sender ?? new Persoon(-1, "SKC Studentenvolleybal", "info@skcvolleybal.nl");
        $this->receiver = $receiver;
        $this->titel = $titel;
        $this->body = $body;

        $this->CalculateSignature();
    }

    function SetReceiver(Persoon $reciever): void
    {
        $this->receiver = $reciever;
        $this->CalculateSignature();
    }

    private function CalculateSignature(): void
    {
        $concatenatedEmail =
            $this->sender->email .
            ($this->receiver != null ? $this->receiver->email : "") .
            $this->titel .
            $this->body;
        $this->signature = hash("sha1", $concatenatedEmail);
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
}
