<?php
class sfCombinePlusUtility
{
  static public function combinableFile($file, $doNotCombine = array())
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

  static public function skipAsset($value, $doNotCombine)
  {
    return in_array($value, $doNotCombine);
  }

  static public function getCacheDir()
  {
    return sfConfig::get('sf_cache_dir') . '/'
                         . sfConfig::get(
                           'app_sfCombinePlugin_cache_dir',
                           'sfCombinePlus'
                         );
  }

  static public function setGzip()
  {
    // gzip compression
    if (sfConfig::get('app_sfCombinePlusPlugin_gzip', true)
    && !self::_checkGzipFail()) {
      ob_start("ob_gzhandler");
    }
  }

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
