EBTranslationBundle
===================

This bundle helps me deal with links in my templates.

## Default configuration

```yaml
# app/config/config.yml

eb_translation:
  # domain where translations are stored
  domain: messages
  # default locale
  locale: '%locale%'
  # add route name as class when generating a link
  use_route_as_class: false
  # replace underscores by points in route names to
  # find its translation : home_index => home.index
  # create array structures in Yaml
  replace_underscore: true
  # link translations prefix
  prefix: 'page.'
  # wether we have to track selected links by adding
  # a class when the route is the same as the current
  # route (the class to add, or null)
  track_selected_links: 'active'
```

## Translation example base on the default configuration

```yaml
# messages.fr.yml
page:
  home:
    name: 'Home page name'
    title: 'Home page title'
    description: 'Home page description'
````

## Controller

```php
// SomeController.php

/** @var EB\TranslationBundle\Translation $translation */
$translation = $this->get('eb_translation');

// <a href="/" title="Home page title">Home page name</a>
$translation->link('home');

// <a href="/page-1" title="Home page title">Home page name</a>
$translation->link('home',{'page':1});

// <a href="/" title="Home page title">something</a>
$translation->link('home',{},{'name':'something'});

// <a href="/" title="something">Home page name</a>
$translation->link('home',{},{'title':'something'});

// <a href="/" title="Home page title" class="something">Home page name</a>
$translation->link('home',{},{'class':'something'});

// Home page name
$translation->name('home');

// Home page title
$translation->title('home');

// Home page description
$translation->description('home');
````

## Twig

```jinja
{# SomeTemplate.html.twig #}

{# @return '<a href="/" title="Home page title">Home page name</a>' #}
{{ link('home') }}

{# @return '<a href="/page-1" title="Home page title">Home page name</a>' #}
{{ link('home',{'page':1}) }}

{# @return '<a href="/" title="Home page title">something</a>' #}
{{ link('home',{},{'name':'something'}) }}

{# @return '<a href="/" title="something">Home page name</a>' #}
{{ link('home',{},{'title':'something'}) }}

{# @return '<a href="/" title="Home page title" class="something">Home page name</a>' #}
{{ link('home',{},{'class':'something'}) }}

{# @return 'Home page name' #}
{{ name('home') }}

{# @return 'Home page title' #}
{{ title('home') }}

{# @return 'Home page description' #}
{{ description('home') }}
```
