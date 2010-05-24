<?php

/**
 * Cleanup sf_combine_plus table
 * 
 * This file is only slightly modified from sfCombine
 *
 * @package    sfCombinePlusPlugin
 * @subpackage task
 * @author     Alexandre Mogère
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombinePlusCleanUpTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'combine-plus';
    $this->name = 'cleanup';
    $this->briefDescription = 'Cleanup sf_combine_plus table';

    $this->detailedDescription = <<<EOF
The [combine:cleanup|INFO] task cleanup sf_combine_plus table:

  [./symfony asset:create-root|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // initialize database manager
    $databaseManager = new sfDatabaseManager($this->configuration);
    $flag = true;
  
    if (function_exists('apc_store') && ini_get('apc.enabled')) 
    {
      $cache = new sfAPCCache();
      if (!ini_get('apc.enable_cli'))
      {
        $this->logSection('combine', 'Check apc.enabled_cli in your ini file', null, 'ERROR');
        $flag = false;
      }
    }
    else
    {
      $cache = new sfFileCache(array(
        'cache_dir' => sfCombinePlusUtility::getCacheDir()
      ));
    }
    
    if ($flag)
    {
      $results = sfCombinePlus::getAll();
      foreach ($results as $result)
      {
        $cache->remove($result->getAssetKey());
      }

      $this->logSection('combine', 'Cleanup cache complete', null, 'INFO');
      $deleted = sfCombinePlus::deleteAll();
      $this->logSection(
        'combine',
        sprintf(
          'Cleanup database complete (%d rows deleted)',
          $deleted
        ),
        null,
        'INFO'
      );
    }
  }
}