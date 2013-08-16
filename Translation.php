<?php

namespace EB\TranslationBundle;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class Translation
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class Translation
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * Display routes as classes
     *
     * @var bool
     */
    private $displayRouteAsClass;
    /**
     * Class used to track selected links
     *
     * @var string
     */
    private $trackerClass;
    /**
     * Replace underscores by points
     *
     * @var bool
     */
    private $replaceUnderscore = false;
    /**
     * Root translation path
     *
     * @var string
     */
    private $rootPath;
    /**
     * Other suffix translation path
     *
     * @var string[]
     */
    private $paths;
    /**
     * Default translation parameters
     *
     * @var array
     */
    private $dtp = array(
        'route' => null,
        'vars' => array(),
        'domain' => null,
        'locale' => null,
    );
    /**
     * Default link parameters
     *
     * @var array
     */
    private $dlp = array(
        'href' => null,
        'title' => null,
        'name' => null,
        'current' => null,
        'tag' => '',
        'class' => array(),
        'id' => null,
        'target' => null,
        'rel' => null,
        'style' => null,
        'icon' => null,
        'strict' => false,
    );

    /**
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param array               $conf
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, array $conf = array())
    {
        // Dependencies
        $this->router = $router;
        $this->translator = $translator;

        // Parameters
        $this->dtp['domain'] = (string)$conf['domain'];
        $this->dtp['locale'] = (string)$conf['locale'];
        $this->displayRouteAsClass = (bool)$conf['useRouteAsClass'];
        $this->trackerClass = (string)$conf['trackSelectedLinks'];
        $this->replaceUnderscore = (bool)$conf['replaceUnderscore'];

        // Paths
        $this->rootPath = $conf['path']['root'];
        $this->paths = array(
            'name' => $conf['path']['name'],
            'title' => $conf['path']['title'],
            'description' => $conf['path']['description'],
            'legend' => $conf['path']['legend'],
            'success' => $conf['path']['success'],
            'error' => $conf['path']['error'],
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->request = $event->getRequest();
    }

    /**
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Current route ?
     *
     * @param string $routeName Current route id
     * @param bool   $strict    Strict mode
     *
     * @return null|bool
     */
    public function isCurrentRoute($routeName = null, $strict = false)
    {
        // Default route
        $routeName = $routeName ? : $this->getRequest()->attributes->get('_route');

        // Request must be defined
        if (null === $request = $this->getRequest()) {
            return null;
        }

        // Comparison can be strict
        if ($strict) {
            return (bool)($routeName === $request->attributes->get('_route'));
        }

        // Comparison is done by route ID
        return (bool)(0 === strpos($request->attributes->get('_route'), $routeName));
    }

    /**
     * Link generator
     *
     * @param string $route [optional] Route id
     * @param array  $rp    [optional] Route parameters
     * <ul>
     *   <li>Route parameters ...</li>
     * </ul>
     * @param array  $fp    [optional] Link parameters
     * <ul>
     *   <li>"absolute": Whether the link is absolute or not</li>
     *   <li>"href": Link href</li>
     *   <li>"title": Link title</li>
     *   <li>"name": Link name</li>
     *   <li>"current": Whether the link is the current one or not</li>
     *   <li>"tag": Link tag</li>
     *   <li>"class": Link class</li>
     *   <li>"id": Link id</li>
     *   <li>"target": Link target</li>
     *   <li>"rel": Link rel</li>
     *   <li>"style": Link style</li>
     *   <li>"icon": Link icon</li>
     *   <li>"strict": Strict route comparison</li>
     * </ul>
     * @param array  $tp    [optional] Translation parameters
     * <ul>
     *   <li>"vars": Translation parameters</li>
     *   <li>"domain": Translation domain</li>
     *   <li>"locale": Translation locale</li>
     *   <li>"route": Route</li>
     * </ul>
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @todo fp validations
     */
    public function link($route = null, array $rp = array(), array $fp = array(), array $tp = array())
    {
        // Route
        $route = $route ? : $this->getRequest()->attributes->get('_route');
        if (empty($route) || !is_string($route)) {
            throw new \InvalidArgumentException('You must provide a "route".');
        }

        // Route parameters
        $rp = array_filter($rp);

        // Translation parameters
        $tp['route'] = $route;
        $tp = array_intersect_key(array_merge($this->dtp, $tp), $this->dtp);

        // Translation validations
        if (!is_array($tp['vars'])) {
            throw new \InvalidArgumentException('You must provide valid "vars".');
        }
        if (!is_string($tp['domain'])) {
            throw new \InvalidArgumentException('You must provide a valid "domain".');
        }
        if (!is_string($tp['locale'])) {
            throw new \InvalidArgumentException('You must provide a valid "locale".');
        }

        // Default link parameters
        $fp['absolute'] = isset($fp['absolute']) ? (bool)$fp['absolute'] : false;
        $this->dlp['href'] = $this->router->generate($route, $rp, $fp['absolute']);
        $this->dlp['title'] = $this->title($route, $tp['vars'], $tp['domain'], $tp['locale']);
        $this->dlp['name'] = $this->name($route, $tp['vars'], $tp['domain'], $tp['locale']);

        // Basic default link parameters
        $fp = array_intersect_key(array_merge($this->dlp, $fp), $this->dlp);
        $fp['href'] .= $fp['tag'] ? (0 === mb_strpos($fp['tag'], '#') ? $fp['tag'] : '#' . $fp['tag']) : '';
        $fp['current'] = isset($fp['current']) ? (bool)$fp['current'] : $this->isCurrentRoute($route, (bool)$fp['strict']);

        // Link class
        if (is_string($fp['class'])) {
            $fp['class'] = explode(' ', $fp['class']);
        }
        if (!is_array($fp['class'])) {
            throw new \InvalidArgumentException('You must provide a valid "class".');
        }
        if ($this->displayRouteAsClass) {
            $fp['class'][] = $route;
        }
        $fp['class'][] = $fp['current'] ? $this->trackerClass : '';
        $fp['class'] = implode(' ', array_unique(array_filter($fp['class'])));

        // Link generation
        return call_user_func_array('sprintf', array(
            '<a%s%s%s%s%s%s%s>%s%s</a>',
            $this->arg($fp, 'id'),
            $this->arg($fp, 'href'),
            $this->arg($fp, 'title'),
            $this->arg($fp, 'class'),
            $this->arg($fp, 'target'),
            $this->arg($fp, 'rel'),
            $this->arg($fp, 'style'),
            isset($fp['icon']) ? sprintf('<img src="%s" alt="%s"> ', $fp['icon'], $fp['title']) : '',
            $fp['name'],
        ));
    }

    /**
     * Page name for route id
     *
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function name($route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->pageTranslation('name', $route, $parameters, $domain, $locale);
    }

    /**
     * Page title for route id
     *
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function title($route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->pageTranslation('title', $route, $parameters, $domain, $locale);
    }

    /**
     * Page description for route id
     *
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function description($route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->pageTranslation('description', $route, $parameters, $domain, $locale);
    }

    /**
     * Page legent for route id
     *
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function legend($route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->pageTranslation('legend', $route, $parameters, $domain, $locale);
    }

    /**
     * Page success for route id
     *
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function success($route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->pageTranslation('success', $route, $parameters, $domain, $locale);
    }

    /**
     * Page error for route id
     *
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function error($route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->pageTranslation('error', $route, $parameters, $domain, $locale);
    }

    /**
     * Displaying argument
     *
     * @param array  $data
     * @param string $key
     * @param string $pattern
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function arg(array $data, $key, $pattern = ' %s="%s"')
    {
        // Must exist
        if (!isset($data[$key])) {
            return '';
        }
        // Validation
        if (!is_string($data[$key]) && !is_numeric($data[$key])) {
            throw new \InvalidArgumentException(sprintf('You must provide a valid "%s", %s given [%s].', $key, gettype($data[$key]), $data[$key]));
        }

        return sprintf($pattern, $key, $data[$key]);
    }

    /**
     * Page specific translation for route id
     *
     * @param string $type       Type
     * @param string $route      Route id [optional]
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    private function pageTranslation($type, $route = null, array $parameters = array(), $domain = null, $locale = null)
    {
        $route = $route ? : $this->getRequest()->attributes->get('_route');
        if (empty($route) || !is_string($route)) {
            throw new \InvalidArgumentException('You must provide a valid "route".');
        }
        $domain = $domain ? : $this->dtp['domain'];
        $locale = $locale ? : $this->dtp['locale'];
        $path = sprintf('%s%s%s', $this->rootPath, $route, $this->paths[$type]);
        if ($this->replaceUnderscore) {
            $path = str_replace('_', '.', $path);
        }

        return $this->translator->trans($path, $parameters, $domain, $locale);
    }
}
