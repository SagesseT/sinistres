<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\TypeDocument;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typedocument', EntityType::class, [
                'class' => TypeDocument::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir le type de document',
                'label' => 'Type de document',
            ])
            ->add('fichier', FileType::class, [
                'label' => 'Fichier',
                'mapped' => false, // 🔥 obligatoire
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
