<?php

namespace EB\TranslationBundle\Translation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Translator
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class Translator
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
    private $replaceUnderscore;

    /**
     * Translation path prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Default translation parameters
     *
     * @var array
     */
    private $dtp = [
        'route' => null,
        'vars' => [],
        'domain' => null,
        'locale' => null,
    ];

    /**
     * Default link parameters
     *
     * @var array
     */
    private $dlp = [
        'href' => null,
        'title' => null,
        'name' => null,
        'current' => null,
        'tag' => '',
        'class' => [],
        'id' => null,
        'target' => null,
        'rel' => null,
        'style' => null,
        'icon' => null,
        'cicon' => null,
        'gicon' => null,
        'ficon' => null,
        'strict' => false,
    ];

    /**
     * @param RouterInterface     $router              Router
     * @param TranslatorInterface $translator          Translator
     * @param string              $domain              Translation domain
     * @param string              $locale              Translation locale
     * @param bool                $displayRouteAsClass Wether to add route name at each link
     * @param string              $trackerClass        Class to add when a link is selected
     * @param bool                $replaceUnderscore   Replace underscore for translation keys
     * @param string              $prefix              Translation prefix
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, $domain, $locale, $displayRouteAsClass, $trackerClass, $replaceUnderscore, $prefix)
    {
        // Dependencies
        $this->router = $router;
        $this->translator = $translator;

        // Parameters
        $this->dtp['domain'] = $domain;
        $this->dtp['locale'] = $locale;
        $this->displayRouteAsClass = $displayRouteAsClass;
        $this->trackerClass = $trackerClass;
        $this->replaceUnderscore = $replaceUnderscore;
        $this->prefix = $prefix;
    }

    /**
     * Get Prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get Translator
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->request = $event->getRequest();
        }
    }

    /**
     * Is this the current route or a children , if yes, add the class
     *
     * @param string[]|string $routes  Routes to check
     * @param string          $class   Class
     * @param null|string     $default Default class
     *
     * @return string
     */
    public function current($routes, $class = 'active', $default = null)
    {
        if (false === is_array($routes)) {
            $routes = [$routes];
        }

        foreach ($routes as $route) {
            if ($this->isCurrentRoute($route)) {
                return sprintf(' class="%s"', trim(sprintf('%s %s', $default, $class)));
            }
        }

        if (null !== $default) {
            return sprintf(' class="%s"', $default);
        }

        return '';
    }

    /**
     * Current route ?
     *
     * @param string $routeName [optional] Current route id
     * @param bool   $strict    [optional] Strict mode
     *
     * @return bool
     */
    public function isCurrentRoute($routeName = null, $strict = false)
    {
        // Request must be defined
        if (null === $this->request) {
            return false;
        }

        // Default route
        $routeAttribute = $this->request->attributes->get('_route');
        $routeName = $routeName ? : $routeAttribute;

        // Comparison can be strict
        if ($strict) {
            return (bool)($routeName === $routeAttribute);
        }

        // Comparison is done by route ID
        if (0 === strpos($routeAttribute, $routeName . '_') || $routeAttribute == $routeName) {
            return true;
        }

        // Index
//        if (false !== strpos($routeName, '_index') && false !== strpos($routeAttribute, str_replace('_index', '_', $routeName))) {
//            return true;
//        }

        return false;
    }

    /**
     * Link generator
     *
     * @param string $route [optional] Route id
     * @param array  $rp    [optional] Route parameters
     *                      <ul>
     *                      <li>Route parameters ...</li>
     *                      </ul>
     * @param array  $fp    [optional] Link parameters
     *                      <ul>
     *                      <li>"absolute": Whether the link is absolute or not</li>
     *                      <li>"href": Link href</li>
     *                      <li>"title": Link title</li>
     *                      <li>"name": Link name</li>
     *                      <li>"current": Whether the link is the current one or not</li>
     *                      <li>"tag": Link tag</li>
     *                      <li>"class": Link class</li>
     *                      <li>"id": Link id</li>
     *                      <li>"target": Link target</li>
     *                      <li>"rel": Link rel</li>
     *                      <li>"style": Link style</li>
     *                      <li>"icon": Link icon (img)</li>
     *                      <li>"bicon": Link bootstrap icon</li>
     *                      <li>"gicon": Link bootstrap glyphicon</li>
     *                      <li>"ficon": Link font awesome icon</li>
     *                      <li>"strict": Strict route comparison</li>
     *                      </ul>
     * @param array  $tp    [optional] Translation parameters
     *                      <ul>
     *                      <li>"vars": Translation parameters</li>
     *                      <li>"domain": Translation domain</li>
     *                      <li>"locale": Translation locale</li>
     *                      <li>"route": Route</li>
     *                      </ul>
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @todo fp validations
     */
    public function link($route = null, array $rp = [], array $fp = [], array $tp = [])
    {
        // Route
        if (null === $route) {
            if (null === $this->request) {
                return '';
            }
            $route = $this->request->attributes->get('_route');
        }
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
        $fp['absolute'] = array_key_exists('absolute', $fp) ? (bool)$fp['absolute'] : false;
        $this->dlp['href'] = $this->router->generate($route, $rp, $fp['absolute'] ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::RELATIVE_PATH);
        $this->dlp['title'] = $this->title($route, $tp['vars'], $tp['domain'], $tp['locale']);
        $this->dlp['name'] = $this->name($route, $tp['vars'], $tp['domain'], $tp['locale']);

        // Basic default link parameters
        $fp = array_intersect_key(array_merge($this->dlp, $fp), $this->dlp);
        $fp['href'] .= $fp['tag'] ? (0 === mb_strpos($fp['tag'], '#') ? $fp['tag'] : '#' . $fp['tag']) : '';
        $fp['current'] = array_key_exists('current', $fp) ? (bool)$fp['current'] : $this->isCurrentRoute($route, (bool)$fp['strict']);

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
        return call_user_func_array('sprintf', [
            '<a%s%s%s%s%s%s%s>%s%s%s%s%s</a>',
            $this->arg($fp, 'id'),
            $this->arg($fp, 'href'),
            $this->arg($fp, 'title'),
            $this->arg($fp, 'class'),
            $this->arg($fp, 'target'),
            $this->arg($fp, 'rel'),
            $this->arg($fp, 'style'),
            isset($fp['icon']) ? sprintf('<img src="%s" alt="%s"> ', $fp['icon'], $fp['title']) : '',
            isset($fp['bicon']) ? sprintf('<i class="icon-%s"></i> ', $fp['bicon']) : '',
            isset($fp['gicon']) ? sprintf('<span class="glyphicon glyphicon-%s"></span> ', $fp['gicon']) : '',
            isset($fp['ficon']) ? sprintf('<span class="fa fa-%s"></span> ', $fp['ficon']) : '',
            $fp['name'],
        ]);
    }

    /**
     * Page title for route id
     *
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function title($route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation('title', $route, $parameters, $domain, $locale) ? : $this->pageTranslation('name', $route, $parameters, $domain, $locale);
    }

    /**
     * Page specific translation for route id
     *
     * @param string $typePath   Type path
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @throws \InvalidArgumentException
     * @return null|string
     */
    private function pageTranslation($typePath, $route = null, array $parameters = [], $domain = null, $locale = null)
    {
        if (null === $route) {
            if (null === $this->request) {
                return '';
            }
            $route = $this->request->attributes->get('_route');
        }
        if (empty($route) || !is_string($route)) {
            throw new \InvalidArgumentException('You must provide a valid "route".');
        }
        $domain = $domain ? : $this->dtp['domain'];
        $locale = $locale ? : $this->dtp['locale'];
        $path = sprintf('%s%s.%s', $this->prefix, $route, $typePath);
        if ($this->replaceUnderscore) {
            $path = str_replace('_', '.', $path);
        }

        $trans = $this->translator->trans($path, $parameters, $domain, $locale);

        return (null !== $trans && $trans !== $path) ? $trans : null;
    }

    /**
     * Page name for route id
     *
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function name($route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation('name', $route, $parameters, $domain, $locale);
    }

    /**
     * Displaying argument
     *
     * @param array  $data    Data array
     * @param string $key     Key
     * @param string $pattern [optional] Display pattern
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

        return sprintf($pattern, $key, $data[$key]);
    }

    /**
     * Page description for route id
     *
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function description($route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation('description', $route, $parameters, $domain, $locale) ? : $this->pageTranslation('title', $route, $parameters, $domain, $locale);
    }

    /**
     * Page legent for route id
     *
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function legend($route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation('legend', $route, $parameters, $domain, $locale);
    }

    /**
     * Page success for route id
     *
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function success($route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation('success', $route, $parameters, $domain, $locale);
    }

    /**
     * Page error for route id
     *
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function error($route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation('error', $route, $parameters, $domain, $locale);
    }

    /**
     * Translation for some key in this route
     *
     * @param string $type       Type
     * @param string $route      [optional] Route id
     * @param array  $parameters [optional] Translation parameters
     * @param string $domain     [optional] Translation domain
     * @param string $locale     [optional] Translation locale
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function trans($type, $route = null, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->pageTranslation($type, $route, $parameters, $domain, $locale);
    }
}
