<?php

declare(strict_types=1);

namespace TwigCsFixerDrupal;

use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Token\Token;

/**
 * Utility functions.
 */
class Utils {

  /**
   * Check if checked token is in a component template file.
   */
  public static function isInComponentTemplate(Token $token): bool {

    // Get the file path.
    $fileName = FileHelper::getFileName($token->getFilename());
    if ($fileName === NULL) {
      return FALSE;
    }

    // Check that the file extension is twig.
    $fileParts = explode('.', FileHelper::removeDot($fileName));
    $fileExtension = array_pop($fileParts);
    if ($fileExtension !== 'twig') {
      return FALSE;
    }

    // Check that file is in a components directory.
    $directories = FileHelper::getDirectories($token->getFilename());
    if (!in_array('components', $directories, TRUE)) {
      return FALSE;
    }

    // Check that there is a .component.yml file in the same directory.
    $directory = str_replace($fileName, '', $token->getFilename());
    $componentName = str_replace('.twig', '', $fileName);
    $componentFile = $directory . $componentName . '.component.yml';
    if (!\file_exists($componentFile)) {
      return FALSE;
    }

    return TRUE;
  }

}
