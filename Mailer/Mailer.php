<?php

namespace Beelab\UserPasswordBundle\Mailer;

use Beelab\UserBundle\User\UserInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Mailer
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param Swift_Mailer    $mailer
     * @param EngineInterface $templating
     * @param array           $parameters
     */
    public function __construct(Swift_Mailer $mailer, EngigneInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * @param string        $url
     * @param UserInterface $user
     */
    public function sendResetPassword($url, UserInterface $user)
    {
        $template = $this->parameters['template'];
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'url'  => $url,
        ));
        $message = \Swift_Message::newInstance()
            ->setSubject($this->parameters['subject'])
            ->setFrom($this->parameters['from'])
            ->setTo($user->getEmail())
            ->setBody($rendered)
        ;
        $this->mailer->send($message);
    }
}

