# Silverstripe elemental widget userforms
Re-usable user form widgets for silverstripe-elemental

## Requirements
* See `composer.json` requirements

## Installation
* `wedevelopnl/silverstripe-elemental-widget-userforms`

#### Installation note
This module has `silverstripe/userforms` as dependency which in certain situations doesn't
work as expected. If you have installed your site **without** `silverstripe/recipe-cms`, then
make sure you have a global `\PageController` controller defined before using this module. If
you do not have such a global controller then you can simply copy the one [provided](https://github.com/silverstripe/recipe-cms/blob/4/app/src/PageController.php)
by recipe cms and copy that one in your own project.

See [#1198](https://github.com/silverstripe/silverstripe-userforms/issues/1198)

## License
See [License](LICENSE)

## Maintainers
* [WeDevelop](https://www.wedevelop.nl/) <development@wedevelop.nl>

## Development and contribution
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
See read our [contributing](CONTRIBUTING.md) document for more information.

### Getting started
We advise to use [Docker](https://docker.com)/[Docker compose](https://docs.docker.com/compose/) for development.\
We also included a [Makefile](https://www.gnu.org/software/make/) to simplify some commands

Our development container contains some built-in tools like `PHPCSFixer`.

### Cypress spec
This module includes some cypress specs for e2e testing with the module. The specs can be found in `dev/cypress`.

These specs are mainly written to validate the module is in working order inside our own testing suite but they
might be usable for other scenarios aswell.

The specs assume that our cypress support modules are installed
* https://github.com/wedevelopnl/silverstripe-cypress
* https://github.com/wedevelopnl/silverstripe-cypress-support
and that they are ran from a test suite that has a fully functional silverstripe site.

In order to include these specs in your test run simply load the specs in your `cypress.config.js`

```
module.exports = defineConfig({
    specPattern: [
      'vendor/wedevelopnl/silverstripe-elemental-widget-userforms/dev/cypres/e2e/*.cy.js
```