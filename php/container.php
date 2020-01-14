<?php

use DI\Container;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

class ContainerFactory
{
    static public function Create(): Container
    {
        $container = new Container();
        $container->set(\Configuration::class, function () {
            $jsonData = file_get_contents("../appsettings.json");
            $serializer = SerializerBuilder::create()
                ->setPropertyNamingStrategy(
                    new SerializedNameAnnotationStrategy(
                        new IdenticalPropertyNamingStrategy()
                    )
                )
                ->build();
            return $serializer->deserialize($jsonData, 'Configuration', 'json');
        });

        return $container;
    }
}
