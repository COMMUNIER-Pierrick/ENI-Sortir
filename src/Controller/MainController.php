<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\TripType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{

    public function index(SortieRepository $sortieRepository, UserRepository $userRepository): Response
    {

        $user = $userRepository->findOneBy(
            [
                "email" => $this->getUser()->getUsername()
            ]
        );

        $sorties = $sortieRepository->findAll();


        return $this->render('main/index.html.twig', [
            "sorties" => $sorties,
            "utilisateur" => $user
        ]);
    }


    public function display(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();

        if ($request->query->has('inscription')) {
            if ($request->query->get('inscription') == 'true') {
                $sortie->addUser($user);
            } else {
                $sortie->removeUser($user);
            }
        }

        $inscription = false;
        if (in_array($user, $sortie->getUsers()->toArray(), true)) {
            $inscription = true;
        }

        $entityManager->flush();

        return $this->render('main/display.html.twig', [
            'sortie' => $sortie,
            'inscription' => $inscription,
        ]);
    }



    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EtatRepository $etatRepository
    ): Response {

        $user = $userRepository->findOneBy(
            [
                "email" => $this->getUser()->getUsername()
            ]
        );


        $sortie = new Sortie();
        $sortie->setOrganisateur($user);

        $sortieForm = $this->createForm(TripType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if ($sortieForm->getClickedButton() && 'cansel' === $sortieForm->getClickedButton()->getName()) {
                return $this->redirectToRoute('Main');
            } else {

                if ($sortieForm->getClickedButton() && 'publish' === $sortieForm->getClickedButton()->getName()) {
                    $etat = $etatRepository->findAll()[1];
                    $sortie->setEtatSortie($etat);
                    $this->addFlash('success', 'Your trip is open to inscriptions!');
                } else {
                    $etat = $etatRepository->findAll()[0];
                    $sortie->setEtatSortie($etat);
                    $this->addFlash('success', 'Your trip data has been saved!');
                }

                $entityManager->persist($sortie);
                $entityManager->flush();


                return $this->redirectToRoute('Main_display', ['id' => $sortie->getId()]);
            }
        }


        return $this->render('main/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    public function modify(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ): Response {


        $sortieForm = $this->createForm(TripType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if ($sortieForm->getClickedButton() && 'cansel' === $sortieForm->getClickedButton()->getName()) {
                return $this->redirectToRoute('Main');
            } else {

                if ($sortieForm->getClickedButton() && 'publish' === $sortieForm->getClickedButton()->getName()) {
                    $etat = $etatRepository->findAll()[1];
                    $sortie->setEtatSortie($etat);
                    $this->addFlash('success', 'Your trip is now open to inscriptions!');
                } else {
                    $etat = $etatRepository->findAll()[0];
                    $sortie->setEtatSortie($etat);
                    $this->addFlash('success', 'Your trip data has been updated!');
                }

                $entityManager->persist($sortie);
                $entityManager->flush();


                return $this->redirectToRoute('Main_display', ['id' => $sortie->getId()]);
            }
        }


        return $this->render('main/modify.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }
}
