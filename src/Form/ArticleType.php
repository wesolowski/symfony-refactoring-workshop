<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artnum')
            ->add('parentid')
            ->add('asy_packaging')
            ->add('asy_min_order')
            ->add('asy_installation')
            ->add('title')
            ->add('shortdesc')
            ->add('longdesc')
            ->add('unitname')
            ->add('asy_deltext_standard_1')
            ->add('asy_deltext_standard_schweiz')
            ->add('asy_deltext_standard_2')
            ->add('alphabytes_variantenmerkmale')
            ->add('asy_deltext_standard')
            ->add('varname')
            ->add('varselect')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
