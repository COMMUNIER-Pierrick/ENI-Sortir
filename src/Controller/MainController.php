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

    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }


    public function display(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie=$sortieRepository->find($id);

        return $this->render('main/display.html.twig', [
            'sortie'=>$sortie
        ]);
    }



    public function create(Request $request,
                           EntityManagerInterface $entityManager,
                           UserRepository $userRepository,
                           EtatRepository $etatRepository
    ): Response
    {

        $user = $userRepository->findOneBy(
            [
                "email"=>$this->getUser()->getUsername()
            ]
        );

        $etat = $etatRepository->findAll()[0];

        $sortie = new Sortie();
        $sortie->setEtatSortie($etat);
        $sortie->setOrganisateur($user);

        $sortieForm = $this->createForm(TripType::class, $sortie);

        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted()){
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Your trip has been registered!');
            return $this->redirectToRoute('Main_display', ['id'=>$sortie->getId()]);

        }

        return $this->render('main/create.html.twig', [
            'sortieForm'=>$sortieForm->createView()
        ]);
    }


}
