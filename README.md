EBTranslationBundle
===================

### Configuration

``` yaml
# app/config/config.yml

eb_translation:
  domain: messages
  locale: '%locale%'
  useRouteAsClass: true
  replaceUnderscore: false
  namePath:
    pre: 'page'
    suf: 'name'
  titlePath:
    pre: 'page'
    suf: 'title'
  descriptionPath:
    pre: 'page'
    suf: 'description'
  trackSelectedLinks:
    enable: true
    class: 's'
```

### Translation example

``` yaml
# messages.fr.yml
page:
  home:
    name: 'Home page name'
    title: 'Home page title'
    description: 'Home page description'
````

### Controller

``` php
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

### Twig

``` jinja
{# SomeTemplate.html.twig #}

{# You can use translation.something() or t.something() #}

{# @return '<a href="/" title="Home page title">Home page name</a>' #}
{{ translation.link('home') }}

{# @return '<a href="/page-1" title="Home page title">Home page name</a>' #}
{{ translation.link('home',{'page':1}) }}

{# @return '<a href="/" title="Home page title">something</a>' #}
{{ translation.link('home',{},{'name':'something'}) }}

{# @return '<a href="/" title="something">Home page name</a>' #}
{{ translation.link('home',{},{'title':'something'}) }}

{# @return '<a href="/" title="Home page title" class="something">Home page name</a>' #}
{{ translation.link('home',{},{'class':'something'}) }}

{# @return 'Home page name' #}
{{ translation.name('home') }}

{# @return 'Home page title' #}
{{ translation.title('home') }}

{# @return 'Home page description' #}
{{ translation.description('home') }}
```
