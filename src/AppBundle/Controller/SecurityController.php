<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
     public function loginAction(Request $request, AuthenticationUtils $authUtils)
     {
         $error = $authUtils->getLastAuthenticationError();
         $lastUsername = $authUtils->getLastUsername();

         return $this->render('user/login.html.twig', [
             'last_username' => $lastUsername,
             'error'         => $error,
         ]);
     }
}
