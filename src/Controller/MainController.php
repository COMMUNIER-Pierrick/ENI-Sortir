<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\AnnulationType;
use App\Form\TripType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\UserRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class MainController extends AbstractController
{

    public function index(
        SortieRepository $sortieRepository,
        Request $request,
        UserRepository $userRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $userRepository->findOneBy(
            [
                "email" => $this->getUser()->getUsername()
            ]
        );

        $searchData = [
            'subscribed_to' => false,
            'not_subscribed_to' => false,
            'is_organizer' => false,
            'passed_trips' => false,
            'start_at_min_date' => new \DateTime("- 1 month"),
            'start_at_max_date' => new \DateTime("+ 1 year"),
        ];

        $filterForm = $this->createForm(FilterType::class, $searchData);

        $sorties = $sortieRepository->findAllTripsWithFilter($this->getUser(), $searchData);
        foreach ($sorties as $sortie) {

            date_default_timezone_set('Europe/Paris');
            $dateNow = date("Y-m-d H:i");
            $dateLimite = $sortie->getDateLimiteInscription();
            $nbMaxParticipant = $sortie->getNbInscriptionsMax();
            $nbParticipant = $sortie->getUsers()->count();
            $dateFin = $sortie->getDateHeureDebut();
            $stringDate = $dateFin->format("Y-m-d H:i");
            $duree = $sortie->getDuree();
            $dateFin = date("Y-m-d H:i", strtotime($stringDate . '+' . $duree . 'hours'));

            $etatSortie = $sortie->getEtatSortie()->getId();

            if ($etatSortie != 7 || $etatSortie != 1) {

                $interval = abs(strtotime($dateNow) - strtotime($dateFin));
                $years = floor($interval / 31536000);
                $months = floor(($interval - $years * 31536000) / (30 * 60 * 60 * 24));

                if (
                    $months > 1 && $etatSortie == 6 || $months == 1 && $etatSortie == 6 ||
                    $months > 1 && $etatSortie == 5 || $months == 1 && $etatSortie == 5
                ) {
                    $etat = $etatRepository->findAll()[6];
                    $sortie->setEtatSortie($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }

                if ($etatSortie == 4 && $dateNow >= $dateFin) {
                    $etat = $etatRepository->findAll()[4];
                    $sortie->setEtatSortie($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }

                if ($etatSortie == 3 && $dateNow >= $sortie->getDateHeureDebut()) {
                    $etat = $etatRepository->findAll()[3];
                    $sortie->setEtatSortie($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }

                if ($etatSortie == 2 && $nbParticipant == $nbMaxParticipant || $etatSortie == 2 && $dateNow > $dateLimite) {
                    $etat = $etatRepository->findAll()[2];
                    $sortie->setEtatSortie($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }

                if ($etatSortie == 2 && $nbParticipant != $nbMaxParticipant || $etatSortie == 2 && $dateNow <= $dateLimite) {
                    $etat = $etatRepository->findAll()[1];
                    $sortie->setEtatSortie($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                }
            }
        }
        $filterForm->handleRequest($request);

        $searchData = $filterForm->getData();
        $sorties = $sortieRepository->findAllTripsWithFilter($this->getUser(), $searchData);
        $idUser = $user->getId();

        $i = 0;
        $role = $user->getRoles();

        if ($role == ["ROLE_USER"]) {
            foreach ($sorties as $sortie) {

                $idOrganisateur = $sortie->getOrganisateur()->getId();
                $idEtat = $sortie->getEtatSortie()->getId();

                if ($idOrganisateur != $idUser && $idEtat == 1) {
                    unset($sorties[$i]);
                }
                $i++;
            }
        }

        return $this->render('main/index.html.twig', [
            "sorties" => $sorties,
            "utilisateur" => $user,
            'filterForm' => $filterForm->createView()
        ]);
    }


    public function display(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Request $request
    ): Response {
        $sortie = $sortieRepository->find($id);
        $user = $userRepository->findOneBy(
            [
                "email" => $this->getUser()->getUsername()
            ]
        );

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
        EtatRepository $etatRepository,
        LieuRepository $lieuRepository
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

            $dateLimite = $sortie->getDateLimiteInscription();
            $dateFin = $sortie->getDateHeureDebut();

            if ($dateLimite < $dateFin) {
                if ($sortieForm->getClickedButton() && 'cancel' === $sortieForm->getClickedButton()->getName()) {
                    return $this->redirectToRoute('Main');
                } else {
                    // CODE ICI
                    if ($sortieForm->getClickedButton() && 'publish' === $sortieForm->getClickedButton()->getName()) {
                        $etat = $etatRepository->findAll()[1];
                        $sortie->setEtatSortie($etat);
                        $this->addFlash('success', 'Your trip is open to inscriptions!');
                    } else {
                        $etat = $etatRepository->findAll()[0];
                        $sortie->setEtatSortie($etat);
                        $this->addFlash('success', 'Your trip data has been saved!');
                    }

                    $nbMaxParticipant = $sortie->getNbInscriptionsMax();

                    if ($nbMaxParticipant >= 2) {

                        $duree = $sortie->getDuree();

                        if ($duree >= 1) {

                            if ($sortieForm->getClickedButton() && 'publish' === $sortieForm->getClickedButton()->getName()) {
                                $etat = $etatRepository->findAll()[1];
                                $sortie->setEtatSortie($etat);
                                $this->addFlash('success', 'La sortie a été publiée!');
                            } else {
                                $etat = $etatRepository->findAll()[0];
                                $sortie->setEtatSortie($etat);
                                $this->addFlash('success', 'La sortie a été enregistrée!');
                            }

                            $entityManager->persist($sortie);
                            $entityManager->flush();

                            return $this->redirectToRoute('Main_display', ['id' => $sortie->getId()]);
                        }
                        $this->addFlash('warning', "Echec de l'inscription ! La durée doit être au moins égale à 1");
                        return $this->render('main/create.html.twig', [
                            'sortieForm' => $sortieForm->createView()
                        ]);
                    }
                    $this->addFlash('warning', "Echec de l'inscription ! Le nombre de place doit être égale ou supérieure à 2");
                    return $this->render('main/create.html.twig', [
                        'sortieForm' => $sortieForm->createView()
                    ]);
                }
                $this->addFlash('warning', "Echec de l'inscription ! La date limite d'inscription ne peut pas être superieur a celle de la sortie");
                return $this->render('main/create.html.twig', [
                    'sortieForm' => $sortieForm->createView()
                ]);
            }

            return $this->render('main/create.html.twig', [
                'sortieForm' => $sortieForm->createView(),
                'lieux' => $lieuRepository->findAll(),
            ]);
        }
    }
    public function modify(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ): Response {


        $sortieForm = $this->createForm(TripType::class, $sortie)
            ->add('delete', SubmitType::class, ['label' => 'Supprimer la sortie']);



        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if ($sortieForm->getClickedButton() && 'cancel' === $sortieForm->getClickedButton()->getName()) {
                return $this->redirectToRoute('Main');
            } else {

                if ($sortieForm->getClickedButton() && 'publish' === $sortieForm->getClickedButton()->getName()) {
                    $etat = $etatRepository->findAll()[1];
                    $sortie->setEtatSortie($etat);
                    $this->addFlash('success', 'La sortie a été publiée!');
                } elseif ($sortieForm->getClickedButton() && 'create' === $sortieForm->getClickedButton()->getName()) {
                    $etat = $etatRepository->findAll()[0];
                    $sortie->setEtatSortie($etat);
                    $this->addFlash('success', 'La sortie a été modifiée!');
                } else {
                    $entityManager->remove($sortie);
                    $entityManager->flush();

                    return $this->redirectToRoute('Main');
                }

                $entityManager->persist($sortie);
                $entityManager->flush();
            }
            return $this->redirectToRoute('Main_display', ['id' => $sortie->getId()]);
        }

        return $this->render('main/modify.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    public function CreateMessage(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        Request $request
    ): Response {

        $sortie = $sortieRepository->find($id);

        $message = new Message();

        $messageForm = $this->createForm(AnnulationType::class, $message);

        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {

            $message->setSortie($sortie);

            $etat = $etatRepository->findAll()[5];
            $sortie->setEtatSortie($etat);
            $this->addFlash('success', 'Votre Annulation est confirmer !');

            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('Main', ['id' => $sortie->getId()]);
        }

        return $this->render('message/annulation.html.twig', [
            'annulationForm' => $messageForm->createView(),
            'sortie' => $sortie,
        ]);
    }
}
