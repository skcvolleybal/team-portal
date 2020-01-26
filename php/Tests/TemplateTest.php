<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use PHPUnit\Framework\TestCase;
use TeamPortal\Common\Utilities;

class TemplateTest extends TestCase
{
    public function testTemplate()
    {
        // arrange
        $template = "{{thomas}} blalasd {{jonathan}}";
        $variables = (object) [
            "{{thomas}}" => "thomas",
            "{{jonathan}}" => "jonathan"
        ];

        // act
        $result = Utilities::FillTemplate($template, $variables);

        // assert
        $this->assertEquals($result, "thomas blalasd jonathan");
    }
}
