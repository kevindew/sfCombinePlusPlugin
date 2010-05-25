<?php
/**
 * Utility class of static methods for sfCombinePlus
 *
 * @package     sfCombinePlus
 * @subpackage  sfCombinePlusUtility
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombinePlusUtility
{
  /**
   * Check whether or not a file is combinable. Reasons for files not being
   * combinable are, a url with a protocol (likely to be a different server),
   * a file path in full or everything before the question mark is in the
   * $doNotCombine array, or if the file does not exist (could be a dynamic
   * file)
   *
   * @param   string  $file         File name
   * @param   array   $doNotCombine (Optional) Array of files not to combine.
   *                                Default empty array.
   * @return  bool
   */
  static public function combinableFile($file, array $doNotCombine = array())
  {
    // check for a remote or file we've specified not to combine
    if (strpos($file, '://')
    || self::skipAsset($file, $doNotCombine)) {
      return false;
    }

    // remove anything past the question mark
    $fileParts = explode('?', $file);
    $file = $fileParts[0];

    if (self::skipAsset($file, $doNotCombine)) {
      return false;
    }

    // check absolute file exists
    if ((0 === strpos($file, '/'))
    && !self::getFilePath($file)
    ) {
      return false;
    }

    return true;
  }

  /**
   * Get the path to a file as long as the file exists.
   *
   * @param   string        $file
   * @return  string|false  False if file doesn't exist
   */
  static public function getFilePath($file)
  {
    $paths = array(
      sfConfig::get('sf_web_dir') . $file,
      sfConfig::get('sf_symfony_data_dir') . '/web' . $file
    );

    foreach ($paths as $path) {
      if (file_exists($path)) {
        return $path;
      }
    }

    return false;
  }

  /**
   * Whether or not this is a file that should be skipped
   *
   * @param   string  $file
   * @param   array   $doNotCombine
   * @return  bool
   */
  static public function skipAsset($file, array $doNotCombine = array())
  {
    return in_array($file, $doNotCombine);
  }

  /**
   * Get the cache directory for sfCombinePlus
   *
   * @return string
   */
  static public function getCacheDir()
  {
    return sfConfig::get('sf_cache_dir') . '/'
                         . sfConfig::get(
                           'app_sfCombinePlugin_cache_dir',
                           'sfCombinePlus'
                         );
  }

  /**
   * Send GZip headers if possible
   *
   * @author  Alexandre Mogère
   * @return  void
   */
  static public function setGzip()
  {
    // gzip compression
    if (sfConfig::get('app_sfCombinePlusPlugin_gzip', true)
    && !self::_checkGzipFail()) {
      ob_start("ob_gzhandler");
    }
  }

  /**
   * Send cache headers if possible.
   *
   * @author  Alexandre Mogère
   * @param sfResponse $response
   */
  static public function setCacheHeaders($response)
  {

    $max_age = sfConfig::get('app_sfCombinePlusPlugin_client_cache_max_age', false);

    if ($max_age !== false)
    {
      $lifetime = $max_age * 86400; // 24*60*60
      $response->addCacheControlHttpHeader('max-age', $lifetime);
      $response->setHttpHeader('Pragma', null, false);
      $response->setHttpHeader('Expires', null, false);
    }
  }

  /**
   * Check whether we can send gzip
   *
   * @author  Alexandre Mogère
   * @return  bool
   */
  static protected function _checkGzipFail()
  {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if (strpos($userAgent, 'Mozilla/4.0 (compatible; MSIE ') !== 0 
    || strpos($userAgent, 'Opera') !== false) {
      return false;
    }
    
    $version = floatval(substr($userAgent, 30));

    return $version < 6
           || ($version == 6 && strpos($userAgent, 'SV1') === false);
  }
}
