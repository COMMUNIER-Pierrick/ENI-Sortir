<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\User;
use App\Entity\Ville;
use App\Entity\Campus;
use App\Entity\Sortie;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Constraints\DateTime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // $campus = new Campus();
        // $campus->setNom("THECAMPUS");
        // $manager->persist($campus);

        for ($u = 0; $u < 3; $u++) {

            $etat = new Etat();
            $etat->setLibelle("En attente => " . $u);

            $ville = new Ville();
            $ville->setNom("Tour" . $u);
            $ville->setCodePostal("7420" . $u);

            $lieu = new Lieu();
            $lieu->setNom("Centre-Historique" . $u);
            $lieu->setRue("2 Rue de la plonge" . $u);
            $lieu->setLatitude(123.00 . $u);
            $lieu->setLongitude(32.20 . $u);
            $lieu->setVille($ville);

            $campus = new Campus();
            $campus->setNom("La O'Carré school academy" . $u);

            for ($i = 0; $i < 10; $i++) {

                $user = new User();
                $user->setEmail('LeBoss@gmail.com'  . $u . " / " . $i);
                $user->setRoles(['Boss']);
                $user->setPassword('JeSuisLeBossGODGAMN' . $u . " / " . $i);
                $user->setNom('IMTHEMOTHERFCKINGBOSS' . $u . " / " . $i);
                $user->setPseudo('ResteHumbleFils' . $u . " / " . $i);
                $user->setPrenom('PassePartout' . $u . " / " . $i);
                $user->setTelephone('0671823299');
                $user->setAdministrateur(FALSE);
                $user->setActif(TRUE);
                $user->setCampus($campus);

                $sortie = new Sortie();
                $sortie->setNom("Sortie2.0" . $u . " / " . $i);
                $sortie->setDateHeureDebut(\DateTime::createFromFormat('Y-m-d', "2018-09-09"));
                $sortie->setDuree(100);
                $sortie->setDateLimiteInscription(\DateTime::createFromFormat('Y-m-d', "2018-09-09"));
                $sortie->setNbInscriptionsMax(5);
                $sortie->setInfosSortie("On est là tu connais ou pas !" . $u . " / " . $i);
                $sortie->setEtat(1);
                $sortie->setLieu($lieu);
                $sortie->setEtatSortie($etat);
                $sortie->setCampus($campus);
                $sortie->setOrganisateur($user);

                echo "Tah la fixture !" . $u . " / " . $i;

                $manager->persist($etat);
                $manager->persist($ville);
                $manager->persist($lieu);
                $manager->persist($campus);
                $manager->persist($user);
                $manager->persist($sortie);
            }
        }

        $manager->flush();
    }
}
