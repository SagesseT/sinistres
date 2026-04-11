<?php

namespace App\Form;

use App\Entity\Avenue;
use App\Entity\Parcelle;
use App\Entity\User;
use App\Entity\Victime;
use App\Form\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
    // ... vos autres champs (victime, avenue, etc.)
    ->add('documents', CollectionType::class, [
    'entry_type' => DocumentType::class,
    'allow_add' => true,
    'allow_delete' => true,
    'by_reference' => false,
    'prototype' => true,
    'label' => false
])
        
            ->add('victime', EntityType::class, [
    'class' => Victime::class,
    'choice_label' => function ($victime) {
        return $victime->getNom() . ' ' . $victime->getPrenom();
    },
    'placeholder' => 'Choisir une victime',
    'attr' => ['class' => 'form-select']
])
            ->add('avenue', EntityType::class, [
                'class' => Avenue::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez avenue',
            ])
            ->add('numero')
            ->add('homme')
            ->add('femme')
            ->add('enfant')
            ->add('description')
            ->add('observation')           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcelle::class,
        ]);
    }
}
