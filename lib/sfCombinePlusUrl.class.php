<?php
class sfCombinePlusUrl
{
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

  static public function getFileString($files, $separator = ' ')
  {
    return implode($separator, array_map('urlencode', $files));
  }

  static public function getFiles($fileString, $separator = ' ')
  {
    return array_map('urldecode', explode(' ', $fileString));
  }

  static public function getBase64($files, $separator = ' ')
  {
    return base64_encode(self::getFileString($files, $separator));
  }

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

  // based on sfCombine _get_key helper
  static public function getKey($files, $separator = ' ')
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
