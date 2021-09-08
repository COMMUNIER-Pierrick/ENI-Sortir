<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
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
}
