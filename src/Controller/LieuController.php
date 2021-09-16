<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LieuController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    public function getLieu(Request $request, LieuRepository $lieuRepository): Response
    {
        $lieu = $lieuRepository->findByExampleField($request->get('id'));
        return $this->json([
            'nom' => $lieu[0]->getNom(), 'rue' => $lieu[0]->getRue(), 'latitude' => $lieu[0]->getLatitude(), 'longitude' => $lieu[0]->getLongitude(),
            'codePostal' => $lieu[0]->getVille()->getCodePostal()
        ]);
    }
}
