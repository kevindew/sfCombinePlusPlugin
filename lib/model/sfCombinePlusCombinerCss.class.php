<?php
/**
 * sfCombinePlusCombinerCss
 *
 * Based on Alexandre Mogères sfCombine Class
 *
 * @package    sfCombinePlusPlugin
 * @subpackage combiner
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombinePlusCombinerCss extends sfCombinePlusCombiner
{
  /**
   * @see sfCombinePlusCombiner
   */
  public function minify($content, $minifyMethod = array('cssmin', 'minify'))
  {
    return parent::minify($content, $minifyMethod);
  }

  /**
   * @see sfCombinePlusCombiner
   */
  protected function _getAssetPath($file)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Asset');
    return stylesheet_path($file);
  }

  /**
   * Return a cache directory for just minified css files
   *
   * @return  string
   */
  static public function getCacheDir()
  {
    return parent::getCacheDir() .  '/css';
  }

  
  protected function _collateFileContents(
    $allowIncludes = false,
    $includeSuffixes = array(),
    $dontInclude = array()
  )
  {
    $fileContents = parent::_collateFileContents(
      $allowIncludes,
      $includeSuffixes,
      $dontInclude
    );

    // @todo @imports

    // fix url paths
    foreach ($fileContents as $file => $contents) {
      $fileContents[$file] = self::rewriteUris(
        $this->_getAssetPath($file), $contents
      );
    }

    $this->setFileContents($fileContents);

    return $fileContents;
  }

  public function generateOutput(
    $minify = false,
    $minifySkipSuffixes = array(),
    $minifySkip = array()
  )
  {
    $output = parent::generateOutput($minify, $minifySkipSuffixes, $minifySkip);

    $output = self::fixImports(
      $output,
      $this->getConfigOption('prepend_imports', true),
      $this->getConfigOption('prepend_imports_warning', '')
    );

    $output = self::fixCharset(
      $output,
      $this->getConfigOption('keep_charset', false)
    );

    return $output;
  }

  static public function rewriteUris($file, $content)
  {
    $path = dirname($file) . '/';

    return Minify_CSS_UriRewriter::prepend($content, $path);
  }
 
  // from minify
  static public function removeComments($content)
  {
    return preg_replace('@/\\*[\\s\\S]*?\\*/@', '', $content);
  }

  static public function fixCharset($content, $useFirstCharset = true)
  {
    // get charsets to remove

    $removedComments = self::removeComments($content);

    preg_match_all('/@charset.*?;/i', $removedComments, $matches);

    if ($matches[0]) {
      // remove charsets
      $content = str_replace($matches[0], '', $content);
      return $matches[0][0] . PHP_EOL . $content;
    } else {
      return $content;
    }
  }

  // based on minify
  static public function fixImports(
    $content,
    $includeImports = true,
    $prependWarning = '')
  {
    $removedComments = self::removeComments($content);

    preg_match_all('/@import.*?;/', $removedComments, $matches);

    if ($matches[0]) {
      return ($prependWarning ? "/* $prependWarning */" : '') . PHP_EOL
             . implode('', $matches[0]) . PHP_EOL
             . str_replace($matches[0], '', $content);
    } else {
      return $content;
    }

    
  }
}