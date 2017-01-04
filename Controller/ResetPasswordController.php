<?php

namespace Beelab\UserPasswordBundle\Controller;

use Beelab\UserPasswordBundle\Event\ChangePasswordEvent;
use Beelab\UserPasswordBundle\Event\NewPasswordEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResetPasswordController.
 */
class ResetPasswordController extends Controller
{
    /**
     * New password.
     *
     * @Method({"GET", "POST"})
     * @Route("/password/new", name="beelab_new_password")
     */
    public function newAction(Request $request): Response
    {
        $form = $this->createForm('Beelab\UserPasswordBundle\Form\Type\ResetPasswordType');
        if ($form->handleRequest($request)->isValid()) {
            $this->get('event_dispatcher')->dispatch(
                'beelab_user.new_password',
                new NewPasswordEvent($form->getConfig()->getType()->getInnerType()->getUser(), 'beelab_new_password_confirm')
            );

            return $this->redirectToRoute('beelab_new_password_ok');
        }

        return $this->render('BeelabUserPasswordBundle:ResetPassword:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * New password OK.
     *
     * @Method("GET")
     * @Route("/password/new/ok", name="beelab_new_password_ok")
     */
    public function okAction(): Response
    {
        return $this->render('BeelabUserPasswordBundle:ResetPassword:ok.html.twig');
    }

    /**
     * Confirm bew password.
     *
     * @Method({"GET", "POST"})
     * @Route("/password/new/confirm/{token}", name="beelab_new_password_confirm")
     */
    public function confirmAction(string $token, Request $request): Response
    {
        $resetPassword = $this->getDoctrine()
            ->getRepository($this->container->getParameter('beelab_user.password_reset_class'))
            ->findOneByToken($token);
        if (is_null($resetPassword)) {
            throw $this->createNotFoundException(sprintf('Token not found: %s', $token));
        }
        $form = $this->createForm('Beelab\UserPasswordBundle\Form\Type\NewPasswordType');
        if ($form->handleRequest($request)->isValid()) {
            $this->get('event_dispatcher')->dispatch(
                'beelab_user.change_password',
                new ChangePasswordEvent($resetPassword->getUser())
            );
            $data = $form->getData();
            $resetPassword->getUser()->setPlainPassword($data['password']);
            $this->get('beelab_user.manager')->update($resetPassword->getUser(), false);
            $this->getDoctrine()->getManager()->remove($resetPassword);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Password successfully reset.');

            return $this->redirectToRoute('login');
        }

        return $this->render('BeelabUserPasswordBundle:ResetPassword:confirm.html.twig', [
            'form' => $form->createView(),
            'user' => $resetPassword->getUser(),
        ]);
    }
}
