<?php

namespace EB\TranslationBundle\Twig\Extension;

use EB\TranslationBundle\Translation\Translator;

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
     * @var Translator
     */
    private $translator;

    /**
     * @param string             $name       Extension name
     * @param Translator         $translator Translator
     */
    public function __construct($name, Translator $translator)
    {
        $this->name = $name;
        $this->translator = $translator;
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
            new \Twig_SimpleFunction('current', [$this->translator, 'current'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('is_current', [$this->translator, 'isCurrentRoute']),
            new \Twig_SimpleFunction('link', [$this->translator, 'link'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('li_link', [$this, 'createLiLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('name', [$this->translator, 'name'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('title', [$this->translator, 'title'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('description', [$this->translator, 'description'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('legend', [$this->translator, 'legend'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('success', [$this->translator, 'success'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('error', [$this->translator, 'error'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('transPage', [$this->translator, 'trans'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Create li link
     *
     * @param null|string|string[] $routes
     *
     * @return string
     */
    public function createLiLink($routes = null)
    {
        return sprintf(
            '<li%s>%s</li>',
            $this->translator->current($routes),
            call_user_func_array([$this->translator, 'link'], func_get_args())
        );
    }
}
