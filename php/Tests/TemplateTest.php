<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    function test_template()
    {
        // arrange
        $template = "{{thomas}} blalasd {{jonathan}}";
        $variables = (object)[
            "{{thomas}}" => "thomas",
            "{{jonathan}}" => "jonathan"
        ];
        
        // act
        $result = Email::FillTemplate($template, $variables);

        // assert
        $this->assertEquals($result, "THomas blalasd Jonathan");
    }
}
