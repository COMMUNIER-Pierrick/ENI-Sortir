<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CampusController extends AbstractController
{

    public function index(Request $request): Response
    {
        $campus = new Campus();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(CampusType::class, $campus);

        // Supprimer une campus
        if ($request->query->has('delete') && $request->query->get('delete') > 0) {
            if ($this->getDoctrine()->getRepository(Campus::class)->find(
                (int)$request->query->get('delete')
            )) {
                $entityManager->remove(
                    $this->getDoctrine()->getRepository(Campus::class)->find(
                        (int)$request->query->get('delete')
                    )
                );
                $entityManager->flush();
            }
        }

        // Ajouter une ville
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();
            $campus = null;
        }

        $campus = $this->getDoctrine()->getRepository(Campus::class)->findAll();

        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
            'campusS' => $campus,
            'form' => $form->createView(),
        ]);
    }

    public function update(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $entityManager = $this->getDoctrine()->getManager();
        $campus = $this->getDoctrine()->getRepository(Campus::class)->find($id);
        $form = $this->createForm(CampusType::class, $campus);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $campus = null;
        }

        return $this->render('campus/update.html.twig', [
            'controller_name' => 'VilleController',
            'form' => $form->createView(),
        ]);
    }

    public function delete(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $entityManager = $this->getDoctrine()->getManager();
        $campus = $this->getDoctrine()->getRepository(Campus::class)->find($id);
        $entityManager->remove($campus);

        return $this->redirectToRoute('Admin_campus');
    }
}
