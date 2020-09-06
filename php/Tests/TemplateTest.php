<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use PHPUnit\Framework\TestCase;
use TeamPortal\Common\Utilities;

class TemplateTest extends TestCase
{
    public function test_Template_Engine()
    {
        // arrange
        $template = "{{thomas}} en {{jonathan}}";
        $variables = (object) [
            "{{thomas}}" => "thomas",
            "{{jonathan}}" => "jonathan"
        ];

        // act
        $result = Utilities::FillTemplate($template, $variables);

        // assert
        $this->assertEquals($result, "thomas en jonathan");
    }
}
