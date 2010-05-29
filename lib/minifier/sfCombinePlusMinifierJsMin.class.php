<?php
/**
 * sfCombinePlusMinifierJsMin
 *
 * @package    sfCombinePlusPlugin
 * @subpackage miniferJsMin
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombinePlusMinifierJsMin implements sfCombinePlusMinifierInterface
{
  /**
   * @see sfCombinePlusMinifierInterface
   */
  static public function minify($content, array $options = array())
  {
    return JSMin::minify($content);
  }
}
