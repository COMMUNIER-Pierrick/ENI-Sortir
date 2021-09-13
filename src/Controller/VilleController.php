<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VilleController extends AbstractController
{
    public function index(Request $request): Response
    {
        $ville = new Ville();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(VilleType::class, $ville);

        // Supprimer une ville
        if ($request->query->has('delete') && $request->query->get('delete') > 0) {
            if ($this->getDoctrine()->getRepository(Ville::class)->find(
                (int)$request->query->get('delete')
            )) {
                $entityManager->remove(
                    $this->getDoctrine()->getRepository(Ville::class)->find(
                        (int)$request->query->get('delete')
                    )
                );
                $entityManager->flush();
            }
        }

        // Ajouter une ville
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();
            $ville = null;
        }

        $villes = $this->getDoctrine()->getRepository(Ville::class)->findAll();

        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
            'villes' => $villes,
            'form' => $form->createView(),
        ]);
    }

    public function update(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $entityManager = $this->getDoctrine()->getManager();
        $ville = $this->getDoctrine()->getRepository(Ville::class)->find($id);
        $form = $this->createForm(VilleType::class, $ville);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $ville = null;
        }

        return $this->render('ville/update.html.twig', [
            'controller_name' => 'VilleController',
            'form' => $form->createView(),
        ]);
    }
}
