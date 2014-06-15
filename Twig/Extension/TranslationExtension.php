<?php

namespace EB\TranslationBundle\Twig\Extension;

use EB\TranslationBundle\Translation\TranslationService;

/**
 * Class TranslationExtension
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class TranslationExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TranslationService
     */
    private $translation;

    /**
     * @param string             $name        Extension name
     * @param TranslationService $translation Translation service
     */
    public function __construct($name, TranslationService $translation)
    {
        $this->name = $name;
        $this->translation = $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('current', [$this->translation, 'current'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('link', [$this->translation, 'link'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('name', [$this->translation, 'name'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('title', [$this->translation, 'title'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('description', [$this->translation, 'description'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('legend', [$this->translation, 'legend'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('success', [$this->translation, 'success'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('error', [$this->translation, 'error'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('transPage', [$this->translation, 'trans'], ['is_safe' => ['html']]),
        ];
    }
}
