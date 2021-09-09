<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Nom de la sortie:'
            ])
            ->add('dateHeureDebut', DateType::class, [
                'html5'=>true,
                'widget'=>'single_text',
                'label'=>'Date et heure de la sortie:'

            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5'=>true,
                'widget'=>'single_text',
                'label'=>'Date limite d\'inscription:'
            ])
            ->add('nbInscriptionsMax', null, [
                'label'=>'Nombre de places:'
            ])
            ->add('duree', null, [
                'label'=>'Durée:'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label'=>'Description et infos:',
                'required'=>false
            ])
            ->add('campus', EntityType::class, [
                'class'=>Campus::class,
                'choice_label'=>'nom',
                'label'=>'Campus:'
            ])
            ->add('lieu', LieuType::class, [
                'label'=>false
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
