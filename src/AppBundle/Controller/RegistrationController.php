<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends Controller
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->userManager->persistUserWithCredentials($user);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Username already taken.');

                return $this->render(
                    'User/register.html.twig',
                    ['form' => $form->createView()]
                );
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'User/register.html.twig',
            ['form' => $form->createView()]
        );
    }
}
