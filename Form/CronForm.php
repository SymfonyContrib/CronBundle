<?php

namespace SymfonyContrib\Bundle\CronBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use SymfonyContrib\Bundle\CronBundle\Entity\Cron;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Cron admin add/edit form.
 */
class CronForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', TextType::class)
            ->add('name', TextType::class)
            ->add('desc', TextType::class, [
                'required' => false,
            ])
            ->add('job', TextType::class, [
                'attr' => [
                    'placeholder' => 'Format: SymfonyServiceName:MethodName'
                ],
            ])
            ->add('runInterval', TextType::class, [
                'attr' => [
                    'placeholder' => 'Standard cron interval: *    *    *    *    *    *'
                ],
            ])
            ->add('weight', IntegerType::class)
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-success',
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SymfonyContrib\Bundle\CronBundle\Entity\Cron',
        ]);
    }
}
