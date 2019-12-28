<?php

class Email
{
    private $sender;
    private $receiver;
    private $titel;
    public $body;
    public $signature;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    function __construct($titel, $body, $receiver = null, $sender = null)
    {
        $this->sender = $sender ?? new Persoon(-1, "SKC Studentenvolleybal", "info@skcvolleybal.nl");
        $this->receiver = $receiver;
        $this->titel = $titel;
        $this->body = $body;

        $this->CalculateSignature();
    }

    function SetReceiver($reciever)
    {
        $this->receiver = $reciever;
        $this->CalculateSignature();
    }

    private function CalculateSignature()
    {
        $concatenatedEmail =
            $this->sender->email .
            ($this->receiver != null ? $this->receiver->email : "") .
            $this->titel .
            $this->body;
        $this->signature = hash("sha1", $concatenatedEmail);
    }

    function IsValid()
    {
        $isInvalid =
            IsNullOrEmpty($this->receiver->naam) ||
            IsNullOrEmpty($this->receiver->email) ||
            IsNullOrEmpty($this->titel) ||
            IsNullOrEmpty($this->body);
        return !$isInvalid;
    }

    static function FillTemplate($template, $placeholders)
    {
        $pattern = "/{{[a-zA-Z_]*}}/";
        if (!preg_match_all($pattern, $template, $matches)) {
            throw new UnexpectedValueException("Fout bij matchen van template placeholders: matchen kon niet");
        }

        if (count($matches[0]) != count($placeholders)) {
            throw new UnexpectedValueException("aantal placeholders matcht niet met aantal variabelen: " . print_r($template, true) . " - " . print_r($placeholders, true));
        }

        foreach ($placeholders as $placeholder => $value) {
            if ($value === null) {
                throw new UnexpectedValueException("Fout bij matchen van template placeholders: value === null");
            }
            if (strpos($template, $placeholder) == -1) {
                throw new UnexpectedValueException("Kan placeholder '$placeholder' niet vinden");
            }
            $template = str_replace($placeholder, $value, $template);
        }

        return $template;
    }
}
