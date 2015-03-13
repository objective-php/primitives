# Contributing to ObjectivePHP/Primitives

## Let's talk first!

If you intend to help the ObjectivePHP project, and we'd be happy then ;), there are some rules to stick to in order to make it work flawlessly.

We think that the most important rules is: "Talk first, code last". This means that if you have an idea about how to improve the library, please 
talk first with the team and users, through the project issue tracker for now (other communication channels are to come...).

## Stick to PHP

Regarding the Primitives library, what matters is to provide with easier ways to perform what PHP natively offers. We'll surely offer more than this, 
but before implementing high-level, domain specific, methods, we'd like to encapsulate (or at least proxy) most native functions.

Higher level features should be developed into separate libraries - dedicated libraries. For instance, if we are to provide SQL related string manipulations
(and we are ;)), we'll implement those methods in an ObjectivePHP/Sql dedicated library.

## Coding rules

At the time being, coding rules and standards have not been fully determined. Only those few rules are clear for us:

### Naming
 - method names should be short but expressive
 - always prefer single word method name when applicable.

*To be continued!*
