<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ville', EntityType::class, [
                'class'=>Ville::class,
                'choice_label'=>'nom',
                'label'=>'Ville:'
            ])
            ->add('nom', null, [
                'label'=>'Lieu:'
            ])
            ->add('rue', null, [
                'label'=>'Rue:'
            ])
            ->add('latitude', null, [
                'label'=>'Latitude:'
            ])
            ->add('longitude', null, [
                'label'=>'Longitude:'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
