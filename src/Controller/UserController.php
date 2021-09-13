<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Campus;
use App\Form\UserType;
use App\Entity\Picture;
use App\Form\PasswordCheckType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
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

    public function checkPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();

        $form = $this->createForm(
            PasswordCheckType::class,
            $user
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            // Si le mot de passe est bon
            if ($passwordEncoder->isPasswordValid($user, $form->getData()->getPassword())) {

                $this->session->set('update', true);

                //Renvoie vers updateUser
                return $this->redirectToRoute('Update_user');
            }
        }

        // Redirige vers vérification du mot de passe
        return $this->render('user/checkPassword.html.twig', [
            'controller_name' => 'UserController',
            'form' => $form->createView(),
        ]);
    }

    public function updateUser(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserType::class, $user);

        // Modifie l'user et renvoie vers son profil
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $picture = $form->get('picture')->getData();
            if ($picture) {
                $fichier = md5(uniqid()) . '.' . $picture->guessExtension();
                $picture->move(
                    $this->getParameter('pictures_directory'),
                    $fichier
                );
                $pic = new Picture();
                $pic->setImage($fichier);

                $entityManager->remove($user->getPicture());
                $entityManager->flush();
                $user->setPicture($pic);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('User');
        }

        // Si le mot de passe est bon
        if ($user && $this->session->get('update')) {
            $this->session->set('update', false);

            return $this->render('user/updateUser.html.twig', [
                'controller_name' => 'UserController',
                'form' => $form->createView(),
                'user' => $this->getUser(),
            ]);
        }
    }
}
