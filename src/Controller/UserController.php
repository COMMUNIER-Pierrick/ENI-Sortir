<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Campus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    public function index(): Response
    {
        $user = $this->getUser();

        $campus = $this->getDoctrine()->getRepository(Campus::class)->findAll();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'campus' => $campus,
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
