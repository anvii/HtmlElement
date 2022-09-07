HtmlElement
===========

**HtmlElement provides an easy and efficient way to construct an HTML code in PHP**

HtmlElement is useful if you want to alternate you HTML code in PHP, like in modular architecture, and have elegant source code of your program.

## Prerequisites

HtmlElement requires PHP v7.3 or greater

## Building the HTML

Sample code:

``` php

use anvii\HtmlElement\HtmlElement;

// Create an element
$div = new HtmlElement('div', ['class'=>'class1 class2'], 'Hello, World!');

// Build sample menu
$menu = HtmlElement::ul(['id'=>'sample-menu', 'class'=>'menu'], [
    HtmlElement::li(['class'=>'active'],
        HtmlElement::a(['href'=>'...'], 'New')
    ),
    HtmlElement::li(['class'=>''],
        HtmlElement::a(['href'=>'...'], 'Open')
    ),
    HtmlElement::li(['class'=>'disabled'],
        HtmlElement::a(['href'=>'...'], 'Save')
    )
]);

// Add one more element(s)
$menu->add([
    HtmlElement::hr(),
    HtmlElement::li(
        HtmlElement::a(['href'=>'...', '_if'=>$hasHelp], 'Help')
    )
]);

// Query elements like in jQuery
$menu->query('a')->addClass('awesome');
$menu->query('li.disabled a')->removeClass('awesome');

// Send menu to browser
echo $menu;

```

## Quering elements

The library has a simple query engine like in jQuery. It allows to alternate HTML code by modules on the fly.

``` php
$form = $html->first('form#login');
$form->query('input[type="submit"]')->addClass('nice-button');
```

Class HtmlQuery also supports callback functions, but using strings with function names is forbidden due to security reason.

``` php
$form->query(function($e) {
    return ($e instanceof \my\Button::class);
});
```

Note, that querying in HtmlElement is very restricted. It supports only one class and only one attribute.

## Special attributes

HTML attributes started with underscore '_' are special. They are not rendered, but used for building document.

``` php
echo HtmlElement::a(['href'=>'/user/profile', '_if'=>isUserRegistered()], 'Profile');
```

The next special attributes are used by HtmlElement:

- _if - Conditional element. Value of this attribute can be boolean or a function.
- _raw - Do not encode content of an element.
- _weight - Used to define order of elements. See 'Ordering elements' below.
- _hideempty - Don't show element if it's content is empty.


## Ordering elements

You can order elements by using special attribute '_weight'. For example, you can construct menus dynamically from modules:

``` php
$userMenu->add([
    HtmlElement::a(['href'=>'/user/profile', '_weight'=>10], 'Profile'),
    HtmlElement::a(['href'=>'/user/password', '_weight'=>12], 'Change Password'),
    HtmlElement::a(['href'=>'/user/Logout', '_weight'=>100], 'Logout'),
]);

// From module:
HtmlElement::first('#userMenu')->add(
    HtmlElement::a(['href'=>'gallery', 'weight'=>30])
);
```
