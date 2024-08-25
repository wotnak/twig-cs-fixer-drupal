<?php

declare(strict_types=1);

namespace TwigCsFixerDrupal\Rules\Component;

use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use TwigCsFixerDrupal\Utils;

/**
 * Ensures that main html tag of a component has attributes set using attributes prop.
 */
final class RequireComponentAttributesRule extends AbstractRule {

  /**
   * {@inheritdoc}
   */
  protected function process(int $tokenIndex, Tokens $tokens): void {
    $tokens = $tokens->toArray();
    $currentToken = $tokens[$tokenIndex];
    if (!Utils::isInComponentTemplate($currentToken)) {
      return;
    }

    // Make sure current token is a html tag opening.
    if (!$this->isHtmlTagOpeningStart($currentToken)) {
      return;
    }

    // Make sure current token is the first html tag opening.
    $previousTokens = array_slice($tokens, 0, $tokenIndex);
    foreach ($previousTokens as $token) {
      if ($this->isHtmlTagOpeningStart($token)) {
        // Current token is not the first html tag opening.
        return;
      }
    }

    // Find tokens of the first html tag opening.
    $htmlTagOpeningStart = $tokenIndex;
    $htmlTagOpeningEnd = NULL;
    $htmlTagOpeningTokens = array_slice($tokens, $htmlTagOpeningStart);
    foreach ($htmlTagOpeningTokens as $index => $token) {
      if ($this->isHtmlTagOpeningEnd($token, isAlsoOpeningStart: $index === 0)) {
        $htmlTagOpeningEnd = $index;
        break;
      }
    }
    if ($htmlTagOpeningEnd !== NULL) {
      $htmlTagOpeningTokens = array_slice($htmlTagOpeningTokens, 0, $htmlTagOpeningEnd + 1);
    }

    // Check if html tag has attributes set using attributes prop.
    $hasAttributes = FALSE;
    foreach ($htmlTagOpeningTokens as $token) {
      if ($token->isMatching(Token::NAME_TYPE, "attributes")) {
        $hasAttributes = TRUE;
        break;
      }
    }

    if (!$hasAttributes) {
      $this->addError('Component\'s main html tag must have attributes set using attributes prop.', $currentToken);
    }
  }

  /**
   * Check if token is a html tag opening.
   */
  private function isHtmlTagOpeningStart(Token $token): bool {
    return $token->getType() === Token::TEXT_TYPE
      && str_contains($token->getValue(), '<');
  }

  /**
   * Check if token is a html tag opening.
   */
  private function isHtmlTagOpeningEnd(Token $token, bool $isAlsoOpeningStart = FALSE): bool {
    if ($token->getType() !== Token::TEXT_TYPE) {
      return FALSE;
    }
    $value = $token->getValue();
    if (!$isAlsoOpeningStart) {
      // If not the same token as opening start then check only part up to the first html tag opening start,
      // since tag opening end should always be before next tag opening start.
      $value = explode('<', $value)[0];
    }
    return str_contains($value, '>');
  }

}
