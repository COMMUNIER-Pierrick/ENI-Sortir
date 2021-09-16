<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Entity\Campus;
use App\Entity\Sortie;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie:',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie:'
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription:'
            ])
            ->add('nbInscriptionsMax', null, [
                'label' => 'Nombre de places:'
            ])
            ->add('duree', null, [
                'label' => 'Durée:'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos:',
                'required' => false
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus:'
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'label' => false,
                'mapped' => false,
                'choice_label' => 'nom',
            ]);
        // ->add('create', SubmitType::class, [
        //     'label' => 'Enregistrer'
        // ])

        // ->add('publish', SubmitType::class, [
        //     'label' => 'Publier la sortie'
        // ])

        // ->add('cancel', SubmitType::class, [
        //     'label' => 'Annuler'
        // ]);

        $builder->get('ville')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $ville = $event->getForm()->getData();
            dump($form);
            dump($ville);
            $form->getParent()->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'placeholder' => 'Veuillez choisir un lieu',
                'choices' => $form->getData()->getLieux()
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
