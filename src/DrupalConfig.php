<?php

declare(strict_types=1);

namespace TwigCsFixerDrupal;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Template\TwigTransTokenParser;
use Drupal\Core\Theme\ThemeManagerInterface;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Standard\TwigCsFixer;
use TwigCsFixerDrupal\Rules\Component\RequireComponentAttributesRule;

/**
 * Drupal configuration.
 */
class DrupalConfig {

  public static function getConfig(): Config {
    $config = new Config();

    // Enable reporting of non-fixable rules.
    $config->allowNonFixableRules();

    // Add drupal/core translation token parsers.
    $config->addTokenParser(new TwigTransTokenParser());

    // Add drupal/twig_tweak support.
    if (class_exists('\Drupal\twig_tweak\TwigTweakExtension')) {
      $moduleHandler = new class implements ModuleHandlerInterface {
        public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {}
      };
      $themeManager = new class implements ThemeManagerInterface {
        public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {}
      };
      $config->addTwigExtension(new \Drupal\twig_tweak\TwigTweakExtension($moduleHandler, $themeManager));
    }

    // Add drupal/storybook support.
    if (class_exists('\TwigStorybook\Twig\TwigExtension') && class_exists('\TwigStorybook\Service\StoryCollector')) {
      $drupalRoot = 'web';
      $storyCollector = new \TwigStorybook\Service\StoryCollector();
      $config->addTwigExtension(new \TwigStorybook\Twig\TwigExtension($storyCollector, $drupalRoot));
    }

    // Load default ruleset.
    $ruleset = $config->getRuleset();
    $ruleset->addStandard(new TwigCsFixer());


    // Add Drupal specific rules.
    $ruleset->addRule(new RequireComponentAttributesRule());

    $config->setRuleset($ruleset);
    return $config;
  }

}
