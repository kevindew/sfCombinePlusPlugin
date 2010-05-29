<?php
/**
 * sfCombinePlusCombinerJs
 *
 * Based on Alexandre Mogères sfCombine Class
 *
 * @package    sfCombinePlusPlugin
 * @subpackage combiner
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombinePlusCombinerJs extends sfCombinePlusCombiner
{
  /**
   * @see sfCombinePlusCombiner
   */
  public function minify(
    $content, $minifyMethod = false, $minifyMethodOptions = array()
  )
  {
    return parent::minify(
      $content,
      $minifyMethod,
      $minifyMethodOptions,
      array('sfCombinePlusMinifierJsMin', 'minify')
    );
  }

  /**
   * @see sfCombinePlusCombiner
   */
  protected function _getAssetPath($file)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Asset');
    return javascript_path($file);
  }

  /**
   * Get cache directory thats specifically for js files
   *
   * @return string
   */
  static public function getCacheDir()
  {
    return parent::getCacheDir() .  '/js';
  }

  /**
   * Takes an array of filenames and returns each of them prefixed by //
   *
   * @param array $files
   * @return string
   */
  protected function _addFilenameComments($files)
  {
    $return = '';

    foreach ($files as $fileName) {
      $return .= '// ' . $fileName . PHP_EOL;
    }

    return $return;
  }

}