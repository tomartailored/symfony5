<?php

namespace App\Event\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// Insert missing use statements.

class JwtEventSubscriber implements EventSubscriberInterface
{

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * JwtEventSubscriber constructor.
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_CREATED => 'onTokenCreated'
        ];
    }

    /**
     * @param JWTCreatedEvent $event
     */
    public function onTokenCreated(JWTCreatedEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        $userData = $this->serializer->serialize( $user, 'json', ['groups' => ['public']] );
        $data = array_merge($data, json_decode($userData, true));

        $event->setData($data);
    }
}