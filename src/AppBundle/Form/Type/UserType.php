<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType
 * Manages user info form.
 */
class UserType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userName', TextType::class, array('label' => 'User name', 'required' => true))
            ->add('userEmail', EmailType::class, array('label' => 'Email', 'required' => true))
            ->add('siteUrl', UrlType::class, array('label' => 'Site Url', 'required' => false))
            ->add('userBirthday', BirthdayType::class, array('label' => 'Birthday', 'required' => false))
            ->add('userGender', ChoiceType::class, array('choices' => array(User::GENDER_MALE, User::GENDER_FEMALE), 'label' => 'Gender', 'required' => false))
            ->add('userPhone', TextType::class, array('label' => 'Phone number', 'required' => false))
            ->add('userSkill', NumberType::class, array('label' => 'Skill number', 'required' => false))
            ->add('userAbout', TextareaType::class, array('label' => 'About', 'required' => false))
            ->add(
                'password',
                RepeatedType::class,
                array(
                    'type' => 'password',
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),
                    'invalid_message' => 'Passwords did not match',
                    'mapped' => true,
                    'required' => false,
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => User::class,
                'validation_groups' => function (FormInterface $form) {
                    $password_check = $form->get('password')->getData();
                    $groups = ['Default'];
                    if ($password_check) {
                        $groups[] = 'password';
                    }

                    return $groups;
                },
            )
        );
    }
}
