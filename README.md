# primitives

## Disclaimer

This document is written in globish, the flavour of English we're trying to use in France. We know how bad our english is, please don't pay too much attention to it :)

Although we're thinking to this library for a while now, its implementation is still in early stage, and for the next coming months, you'll proably see a few code and a lot of changes in it. This means that if you're interested in this project, you're more than welcome to try it, contribute to it, make proposals for it, but please don't use it in production projects for now!

## Project introduction

Primitives objects are foundation of Objective PHP. Objective PHP aims at providing some high-level component to provide PHP with a more objective syntax. The goals of Objeective PHP libraries can be summed up like this:

* make extensive use of PHP OOP capabilities
* work with data objects, no multipurpose, dummy containers (aka variables)
* package commonly needed data processing into relaible, efficient and unit tested methods
* turn any error in Exception to ease failure handling
* bring to PHP a more elegant, modern and fluent way to write code (partly inspired by Javascript)
* autompletion minded library for enhancing developer productivity
* make use of more language's features (like closures)

Our pimitives library is intented achieve most of these goals. The very first set of object will contain:

* ObjectivePHP\Primitives\Numeric
* ObjectivePHP\Primitives\String
* ObjectivePHP\Primitives\Collection

Each of them will both wrap native functions in a object-oriented way and expose higher level methods to perform many usual data manipulation over those. More on this coming soon, in another document describing the coding standard and rules.

## What's next?

First, we'll focus on the three above mentioned classes. We'll consider releasing a 1.0 version once they are ready to get implemented in third-party code with total reliability. Starting from here, we'll be able to work on a much more detailed roadmap.

This roadmap will not only anticipate "primitives" library evolutions, but also include parallel development of extended libraries, built on top of "primitives", and offering some even higher level components for real life use:

* HTML Tag
* CSS Styles
* File/Stream
* Image
* Point (coordinates)
* ...

These are just some examples of what kind of classes we intend to work on, but much more are to come.

Once again, you all are invited to contribute by submitting us proposals, trying and testing what we do, contribute (code or documentation). For any contact, please drop us a message at team@objective-php.org

## Installation

### Manual

You can clone our github repository by running:

```
git clone http://github.com/objective-php/primitives
```

If you're to proceed this way, you probably don't need more explanation about how to use the library :)

### Composer

The easiest way to install the libray and get ready to play with it is by using Composer. Run the following command in an empty folder you just created for Primitives:

```
composer require --dev objective-php/primitives:dev-master 
```

Then, you can start coding using primitive classes by requiring Composer's `autoload.php` located in its `vendor` directory.

Hmm, before starting coding, please take the time to read this file till the end :)

## How to test the work in progress?

### Run unit tests

First of all, before playing around with our primitives, please always run the unit tests suite. Our tests are written using Atoum (http//www.atoum.org), and can be run as follow :

```
cd [clone directory]/tests
php atoum.phar -d ObjectivePHP/Primitives
```

### Write some code

At this time, you're on your own to findout what Primitives can do for you, sorry for that, we'll soon work on some samples to help you geting started. Meanwhile, you can instantiate the various classes and learn by yourself their capabilities by counting on your IDE auto-completion feature ;)

```php
use ObjectivePHP\Primitives\Numeric;
use ObjectivePHP\Primitives\String;
use ObjectivePHP\Primitives\Collection;

// allow Primitives classes autolaoding
// Note: this is asusming you used Composer to install the library!
require 'vendor/autoload.php';

$amount = new Numeric(rand());
$identifier = new String(uniqid());
$server = new Collection($_SERVER);

```

May the OOP be with PHP!


