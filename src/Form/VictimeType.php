<?php

namespace App\Form;

use App\Entity\Victime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VictimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('postnom')
            ->add('prenom')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Masculin' => 'M',
                    'Féminin' => 'F',
                ],
                'placeholder' => 'Sélectionnez le sexe',
            ])
            ->add('adresse')
            ->add('telephone')
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => false, // on gère l'upload dans le contrôleur
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Victime::class,
        ]);
    }
}