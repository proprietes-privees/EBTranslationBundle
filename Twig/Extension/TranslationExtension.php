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
        return $name;
    }

    /**
     * Return string as Twig global
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            't' => $this->translation,
            'translation' => $this->translation,
        );
    }
}
