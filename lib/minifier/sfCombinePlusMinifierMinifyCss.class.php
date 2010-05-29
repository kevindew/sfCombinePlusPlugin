<?php
/**
 * sfCombinePlusMinifierMinifyCss
 *
 * @package    sfCombinePlusPlugin
 * @subpackage miniferMinifyCss
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombinePlusMinifierMinifyCss implements sfCombinePlusMinifierInterface
{
  /**
   * @see sfCombinePlusMinifierInterface
   */
  static public function minify($content, array $options = array())
  {
    return Minify_CSS_Compressor::process($content, $options);
  }
}
