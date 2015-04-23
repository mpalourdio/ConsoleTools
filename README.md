[![Build Status](https://travis-ci.org/mpalourdio/TwitterWidgetBundle.svg?branch=master)](https://travis-ci.org/mpalourdio/TwitterWidgetBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mpalourdio/TwitterWidgetBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mpalourdio/TwitterWidgetBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mpalourdio/TwitterWidgetBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mpalourdio/TwitterWidgetBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/55cdf6c4-bdcb-423a-bd3e-20dda3f8ad13/mini.png)](https://insight.sensiolabs.com/projects/55cdf6c4-bdcb-423a-bd3e-20dda3f8ad13)
[![PHP 5.5+][ico-engine]][lang]
[![MIT Licensed][ico-license]][license]
[ico-engine]: http://img.shields.io/badge/php-5.5+-8892BF.svg
[lang]: http://php.net
[ico-license]: http://img.shields.io/packagist/l/adlawson/veval.svg
[license]: LICENSE

TwitterWidgetBundle
===================
Twig extension to easily display twitter embedded timelines widgets in Symfony project. Based on this library : https://github.com/mpalourdio/TwitterWidgets

Requirements
============
PHP 5.5+ - Only Composer installation supported

Installation
============
Run the command below to install via Composer

```shell
composer require mpalourdio/twitter-widget-bundle
```

Add ```new TwitterWidgetBundle\TwitterWidgetBundle()``` to your **AppKernel.php**

Usage
=====
- 1) Create a timeline widget here : https://twitter.com/settings/widgets/new
- 2) In the javascript generated code, get the URL and the data-widget-id (minimum information required)
- 3) Finally, in a twig template, use as following: 

```php
{{ 
  tw({
         'dataWidgetId' : '1245687955000', => the id must be a string (quotes), because of long integer converted to float
         'href'         : 'https://twitter.com/NickName',
         'hrefText'     : 'Here type a title'
     },
     true/false
}}
```

All the following options are handled : https://dev.twitter.com/web/embedded-timelines#options

Their PHP equivalent as array keys to use in the twig function are  :

```php
'class'           => 'A css class, by default it will be twitter-timeline',
'href'            => 'The link to the timeline',
'hrefText'        => 'A title for your timeline to display',
'dataWidgetId'    => 'Your data widget ID : must be a string (!)',
'dataTheme'       => 'ex: dark',
'dataLinkColor'   => 'ex: #cc0000',
'width'           => 300 (integer),
'height'          => 400 (integer),
'dataChrome'      => 'noheader nofooter noborders noscrollbar transparent', => a string with options separated by a single space
'dataBorderColor' => 'border color used by the widget',
'language'        => 'The widget language detected from the page, based on the HTML lang attribute of your content. You can also set the HTML lang attribute on the embed code itself.',
'dataTweetLimit'  => 20,
'dataRelated'     => 'benward,endform',
'dataAriaPolite'  => 'polite or assertive',
```

You can give an instance of ```TwitterWidgets\Options\WidgetOptions``` instead of an array (or any implementation of ```TwitterWidgets\Timeline\WidgetOptionsInterface```).

```php
$options = new TwitterWidgets\Options\WidgetOptions();
$options->setDataWidgetId('1245687955000');
$options->setHref('https://twitter.com/NickName');
$options->setHrefText('Here type a title');

{{ tw(options) }}
```

The function second parameter is a boolean (true by default), that indicates if you must render the javascript code for your widget. If you have more that one widget on your page,
use the ```OneTimeJs``` extension to only add once the javascript code, just before your ```</body>```. This will avoid some overhead. See https://dev.twitter.com/web/javascript/loading

```php
{% block javascripts %}
        <script>{{ twJs() }}</script>
{% endblock %}
```
