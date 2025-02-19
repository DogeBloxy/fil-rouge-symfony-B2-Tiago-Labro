<?php

namespace App\Form;

use App\Entity\Announces;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;

class AddAnnouncementFormType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $categoryChoices = [];
        foreach ($categories as $category) {
            $categoryChoices[$category->getName()] = $category->getName();
        }

        $builder
            ->add('title')
            ->add('description')
            ->add('category', ChoiceType::class, [
                'choices' => $categoryChoices,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announces::class,
        ]);
    }
}
