<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\User;
use App\Entity\Sortie;
use App\Form\RegistrationFormType;
use App\Form\UploadFileUserType;
use App\Security\AppAuthenticator;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    public function registerViaFile(Request $request, FileUploader $fileUploader): Response
    {
        $user = new User();
        $form = $this->createForm(UploadFileUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('fichier')->getData();

            if ($file) {
                $fileName = $fileUploader->upload($file);
                var_dump($fileName);
            }
           // $nameFile = $file->getClientOriginalName();
            //var_dump($file);
           // $file = fopen($nameFile,'r');
           // var_dump($file);
            //fgetcsv($file, 0,",",'"','\\');
           // var_dump($file);
        }

        return $this->render('services/uploadFileUser.html.twig', [
            'UploadFileUserForm' => $form->createView(),
        ]);
    }
}
