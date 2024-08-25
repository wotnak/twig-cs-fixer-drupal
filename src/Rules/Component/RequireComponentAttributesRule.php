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
    // Run this check only once per file.
    if (0 !== $tokenIndex) {
      return;
    }
    // Run this check only for SDC templates.
    if (!Utils::isInComponentTemplate($tokens->get($tokenIndex))) {
      return;
    }

    // Find the first html tag opening.
    $hasHtmlTag = FALSE;
    $tokens = $tokens->toArray();
    $skip = FALSE;
    $inBlockDef = FALSE;

    foreach ($tokens as $i => $token) {
      // Skip comments.
      if ($token->isMatching(Token::COMMENT_START_TYPE)) {
        $skip = TRUE;
        continue;
      }
      if ($skip) {
        if ($token->isMatching(Token::COMMENT_END_TYPE)) {
          $skip = FALSE;
        }
        continue;
      }
      // Skip macros.
      if ($token->isMatching(Token::BLOCK_START_TYPE)) {
        $inBlockDef = TRUE;
      }
      if ($inBlockDef && $token->isMatching(Token::BLOCK_NAME_TYPE, 'macro')) {
        $skip = TRUE;
        continue;
      }
      if ($inBlockDef && $token->isMatching(Token::BLOCK_NAME_TYPE, 'endmacro')) {
        $skip = FALSE;
        continue;
      }
      if ($inBlockDef && $token->isMatching(Token::BLOCK_END_TYPE)) {
        $inBlockDef = FALSE;
        continue;
      }
      if ($token->isMatching(Token::BLOCK_END_TYPE)) {
        $inBlockDef = TRUE;
        continue;
      }

      if ($this->isHtmlTagOpeningStart($token)) {
        $tokenIndex = $i;
        $hasHtmlTag = TRUE;
        break;
      }
    }

    if (!$hasHtmlTag) {
      return;
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
      $this->addError('Component\'s main html tag must have attributes set using attributes prop.', $tokens[$tokenIndex]);
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
