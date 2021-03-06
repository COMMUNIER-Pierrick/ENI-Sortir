<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LieuController extends AbstractController
{
    public function index(Request $request): Response
    {
        $lieu = new Lieu();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(LieuType::class, $lieu);

        // Ajouter une lieu
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Le lieu a été enregistrée!');
            $entityManager->persist($lieu);
            $entityManager->flush();
        }

        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'VilleController',
            'form' => $form->createView(),
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
