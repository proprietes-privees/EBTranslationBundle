<?php

namespace EB\TranslationBundle\Twig\Extension;

use EB\TranslationBundle\Translation;

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
     * @var Translation
     */
    private $translation;

    /**
     * @param string      $name
     * @param Translation $translation
     */
    public function __construct($name, Translation $translation)
    {
        $this->name = $name;
        $this->translation = $translation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'ebt' => $this->translation,
            'translation' => $this->translation,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'current' => new \Twig_Function_Method($this, 'current', array('is_safe' => array('html'))),
            'link' => new \Twig_Function_Method($this, 'link', array('is_safe' => array('html'))),
        );
    }

    /**
     * Is this the current route or a children , if yes, add the class
     *
     * @param string[] $routes Routes to check
     * @param string   $class  Class
     *
     * @return string
     */
    public function current(array $routes, $class = 'active')
    {
        foreach ($routes as $route) {
            if ($this->translation->isCurrentRoute($route)) {
                return ' class="active"';
            }
        }

        return '';
    }

    /**
     * Alias for Translation::link
     */
    public function link()
    {
        return call_user_func_array(array($this->translation, 'link'), func_get_args());
    }
}
