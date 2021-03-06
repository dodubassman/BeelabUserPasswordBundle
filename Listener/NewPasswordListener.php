<?php

namespace Beelab\UserPasswordBundle\Listener;

use Beelab\UserPasswordBundle\Event\NewPasswordEvent;
use Beelab\UserPasswordBundle\Mailer\Mailer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\RouterInterface as Router;

class NewPasswordListener
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $class;

    public function __construct(ObjectManager $em, Mailer $mailer, Router $router, string $class)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->class = $class;
    }

    /**
     * When user request a password reset, save a token and send user an email with confirmation link.
     *
     * @param NewPasswordEvent $event
     */
    public function onRequest(NewPasswordEvent $event): void
    {
        $user = $event->getUser();
        $token = bin2hex(random_bytes(16));
        $resetPassword = new $this->class($user, $token);
        $this->em->persist($resetPassword);
        $this->em->flush();
        $url = $this->router->generate($event->getConfirmRoute(), ['token' => $token], Router::ABSOLUTE_URL);
        $this->mailer->sendResetPassword($url, $user);
    }
}
