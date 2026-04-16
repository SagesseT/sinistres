<?php

namespace App\Form;

use App\Entity\Victime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VictimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('postnom', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
            ->add('prenom', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
            ->add('sexe', ChoiceType::class, [
                'choices'  => [
                    'Masculin' => 'M',
                    'Féminin' => 'F',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('adresse', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
            ->add('telephone', TelType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
            ->add('photo', FileType::class, [
                'label' => 'Photo (Image uniquement)',
                'mapped' => false, // Ne pas lier directement à l'entité
                'required' => false, // Permet de modifier sans uploader à nouveau
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image JPG ou PNG valide',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Victime::class,
        ]);
    }
}