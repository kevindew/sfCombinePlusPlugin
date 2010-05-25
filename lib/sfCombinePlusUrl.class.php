<?php
/**
 * A class of utility methods for handling URLs in sfCombinePlus
 *
 * @package     sfCombinePlus
 * @subpackage  sfCombinePlusUrl
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombinePlusUrl
{
  /**
   * Get the url for a collection of files
   *
   * @param   array $files
   * @return  string
   */
  static public function getUrlString($files)
  {
    switch(sfConfig::get('app_sfCombinePlusPlugin_url_type', 'key')) {
      case 'files':
        $string = 'files=' . self::getFileString($files);
        break;
      case 'base64':
        $string = 'base64=' . self::getBase64($files);
        break;
      case 'key':
      default:
        $string = 'key=' . self::getKey($files);
        break;

    }

    $string .= '&v='
             . sfConfig::get('app_sfCombinePlusPlugin_asset_version', '');

    return $string;
  }

  /**
   * Return the array of files into a string that can be used in a URL
   *
   * @param  array  $files      Array of file names
   * @param  string $seperator  (Optional) Default ' '
   * @return string
   */
  static public function getFileString(array $files, $separator = ' ')
  {
    return implode($separator, array_map('urlencode', $files));
  }

  /**
   * Take a file string and convert it into an array of files
   *
   * @param  string $fileString
   * @param  string $seperator  (Optional) Default ' '
   * @return array
   */
  static public function getFiles($fileString, $separator = ' ')
  {
    return array_map('urldecode', explode(' ', $fileString));
  }

  /**
   * Return a base64 encoded list of files
   *
   * @see getFileString
   */
  static public function getBase64(array $files, $separator = ' ')
  {
    return base64_encode(self::getFileString($files, $separator));
  }

  /**
   * Take a base64 string and convert it into an array of files
   *
   * @see getFiles
   */
  static public function getFilesByBase64($base64, $separator = ' ')
  {
    $string = false;

    if ($base64) {
      $string = base64_decode($base64);
    }
    return self::getFiles(
      $string ? $string : '',
      $separator
    );
  }

  /**
   * Return a hash which refers to an entry in the db describing the files
   *
   * Based off the sfCombine _getKey method
   *
   * @see getFileString
   */
  static public function getKey(array $files, $separator = ' ')
  {
    $content = self::getBase64($files, $separator);
    $key = sha1($content);
    $check = false;

    if (function_exists('apc_store') && ini_get('apc.enabled')) {
      $cache = new sfAPCCache();
      $check = $cache->has($key);
    } else {
      $cache = new sfFileCache(
          array(
            'cache_dir' => sfCombinePlusUtility::getCacheDir()
          )
      );
      $check = $cache->has($key);
    }

    // Checks if key exists
    if (false === $check)
    {
      // now just doctrine
      $keyExists = sfCombinePlus::hasKey($key);
      if (!$keyExists)
      {
        $combine = new sfCombinePlus();
        $combine->setAssetKey($key);
        $combine->setFiles($content);
        $combine->save();
      }
      $cache->set($key, $content);
    }

    return $key;
  }

  /**
   * Take a db hash and convert it into an array of files
   *
   * @see getFiles
   */
  static public function getFilesByKey($key, $separator = ' ')
  {
    $base64 = false;

    // try get base64 from cache
    if (function_exists('apc_store') && ini_get('apc.enabled')) {
      $cache = new sfAPCCache();
      $base64 = $cache->get($key);
    }

    if (!$base64) {
      $cache = new sfFileCache(
          array(
            'cache_dir' => self::getCacheDir()
          ));
      $base64 = $cache->get($key);
    }

    // check db
    if (!$base64) {
      $combine = sfCombinePlus::getByKey($key);
      $base64 = $combine ? $combine->getFiles() : false;
    }

    return self::getFilesByBase64($base64, $separator);
  }
}
