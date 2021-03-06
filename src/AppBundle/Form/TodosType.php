<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TodosType extends AbstractType
{

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $builder
        ->add('title', TextType::class, [
            'translation_domain' => 'forms',
            'label' => 'title'
        ])
        ->add('content', TextareaType::class, [
            'translation_domain' => 'forms',
            'label' => 'content'
        ])
        ->add('dateCreate', DateTimeType::class, [
            'translation_domain' => 'forms',
            'label' => 'dateCreate'
        ])
        ->add('dateModify', DateTimeType::class, [
            'translation_domain' => 'forms',
            'label' => 'dateModify'
        ])
        ->add('dateSheduled', DateTimeType::class, [
            'translation_domain' => 'forms',
            'label' => 'dateSheduled'
        ])
        ->add('status', ChoiceType::class, [
            'choices' => [0,1],
            'expanded' => true,
            'translation_domain' => 'forms',
            'label' => 'status'
        ])
        ->add('category', EntityType::class, [
            'class' => 'AppBundle:Category',
            'translation_domain' => 'forms',
            'label' => 'category',
            'query_builder' => function (EntityRepository $er) use ($user) {
                return $er
                ->createQueryBuilder('u')
                ->where('u.userID = :id')
                ->setParameter('id', $user->getId())
                ->orderBy('u.title', 'ASC');
            },
        ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Todos'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_todos';
    }


}
