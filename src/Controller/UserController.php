<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Campus;
use App\Form\UserType;
use App\Form\PasswordCheckType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }

    public function seeUser(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        return $this->render('user/seeUser.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }

    public function updateUser(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(PasswordCheckType::class);
        $form->handleRequest($request);

        // Si mot de passe entré
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->getData()->getPassword();

            return $this->render('user/updateUser.html.twig', [
                'controller_name' => 'UserController',
                'form' => $form->createView(),
            ]);
        }

        // Redirige vers vérification du mot de passe
        return $this->render('user/checkPassword.html.twig', [
            'controller_name' => 'UserController',
            'form' => $form->createView(),
        ]);
    }
}
