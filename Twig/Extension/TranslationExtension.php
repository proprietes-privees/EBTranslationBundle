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
        return array(
            new \Twig_SimpleFunction('current', array($this->translation, 'current'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('link', array($this->translation, 'link'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('name', array($this->translation, 'name'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('title', array($this->translation, 'title'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('description', array($this->translation, 'description'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('legend', array($this->translation, 'legend'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('success', array($this->translation, 'success'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('error', array($this->translation, 'error'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('transPage', array($this->translation, 'trans'), array('is_safe' => array('html'))),
        );
    }
}
