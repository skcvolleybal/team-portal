<?php

function IsNullOrEmpty($obj)
{
    return !$obj || empty($obj);
}

function FillTemplate($template, $placeholders)
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
        $template = str_replace("{{$placeholder}}", $value, $template);
    }

    return $template;
}

function StringToInt($getal)
{
    return $getal ? intval($getal) : null;
}
