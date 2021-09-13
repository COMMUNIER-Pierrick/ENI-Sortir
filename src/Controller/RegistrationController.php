<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Picture;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UploadFileUserType;
use App\Security\AppAuthenticator;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $role = $form->get('administrateur')->getData();
            if ($role == true) {
                $user->setRoles(["ROLE_ADMIN"]);
            } else {
                $user->setRoles(["ROLE_USER"]);
            }
            $picture = $form->get('picture')->getData();
            if ($picture) {
                $fichier = md5(uniqid()) . '.' . $picture->guessExtension();

                $picture->move(
                    $this->getParameter('pictures_directory'),
                    $fichier
                );
            } else {
                $fichier = 'default-picture.png';
            }

            $pic = new Picture();
            $pic->setImage($fichier);
            $user->setPicture($pic);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            /* return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );*/
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function registerViaFile(Request $request, FileUploader $fileUploader, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UploadFileUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('fichier')->getData();

            if ($file) {
                $fileName = $fileUploader->upload($file);
                $handleFile = file("../public/uploads/fileUser/" . $fileName);
                foreach ($handleFile as $key => $value) {
                    $newHandleFile[] = explode(',', $value);
                }
                foreach ($newHandleFile as $key => $value) {
                    if ($key > 0 && is_array($value)) {
                        $user = new User();
                        foreach ($value as $keys => $values) {
                            switch ($keys) {
                                case 0:
                                    $user->setNom($values);
                                    break;
                                case 1:
                                    $user->setPrenom($values);
                                    break;
                                case 2:
                                    $user->setPseudo($values);
                                    break;
                                case 3:
                                    $user->setEmail($values);
                                    break;
                                case 4:
                                    $values = preg_replace("/\s+/", "", $values);
                                    $user->setPassword($passwordEncoder->encodePassword(
                                        $user,
                                        $values
                                    ));
                                    break;
                            }
                            $userFailed = $key + 1;
                            $user->setRoles(["ROLE_USER"]);
                            $user->setAdministrateur(false);
                            $user->setActif(true);
                            $error = "l'inscription de l'utilisateur a la ligne n° " . $userFailed . " du fichier à échouer.
                                    \nDes données sont similaires à celles d'un utilisateur déjà existant.
                                    \nVeuillez modifier les champs email/pseudo correspondant.
                                    \nN'oubliez pas de supprimer les entrées des utilisateur sur le fichier au desssus de l'utilisateur causant cette erreur.";
                            if ($keys == 4) {
                                try {
                                    $entityManager->persist($user);
                                    $entityManager->flush();
                                } catch (Exception $e) {
                                    throw new ErrorException();
                                } finally {
                                    return $this->render('bundles/TwigBundle/Exception/error500.html.twig', [
                                        'error' => $error,
                                    ]);
                                }
                            }
                        }
                    }
                }
                $this->addFlash('success', 'les inscriptions sont faites !');
            }
        }
        return $this->render('services/uploadFileUser.html.twig', [
            'UploadFileUserForm' => $form->createView(),
            'error' => $error,
        ]);
    }
}
