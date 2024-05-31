# Twig CS Fixer Drupal

[Drupal](https://www.drupal.org/) specific rules for [Twig CS Fixer](https://github.com/VincentLanglet/Twig-CS-Fixer).

## Installation

- [Install Twig CS Fixer](https://github.com/VincentLanglet/Twig-CS-Fixer?tab=readme-ov-file#installation)
- `composer req --dev wotnak/twig-cs-fixer-drupal`
- In your Twig CS Fixer [configuration file](https://github.com/VincentLanglet/Twig-CS-Fixer/blob/main/docs/configuration.md) enable reporting of non fixable rules and add the Drupal specific rules:

  ```php
  <?php
  // Load the default configuration.
  $config = new \TwigCsFixer\Config\Config();

  // Enable reporting of non-fixable rules.
  $config->allowNonFixableRules();

  // Load default ruleset.
  $ruleset = new \TwigCsFixer\Ruleset\Ruleset();

  // Add custom rules.
  $ruleset->addRule(new \TwigCsFixerDrupal\Rules\Component\RequireComponentAttributesRule());
  // ...

  $config->setRuleset($ruleset);
  return $config;
  ```

## Rules

- [RequireComponentAttributesRule](src/Rules/Component/RequireComponentAttributesRule.php): ensures that main html tag of a component has attributes set using `attributes` prop.
