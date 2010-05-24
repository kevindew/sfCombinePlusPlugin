<?php
/**
 * sfCombinePlus Model
 *
 * @package     sfCombinePlusPlugin
 * @subpackage  sfCombinePlus
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
abstract class PluginsfCombinePlus extends BasesfCombinePlus
{
  /**
   * Get an instance by key
   *
   * @param   string  $key
   * @return  sfCombinePlus
   */
  static public function getByKey($key)
  {
    return Doctrine::getTable('sfCombinePlus')
                   ->find($key);
  }

  /**
   * Check if a key exists in the db
   *
   * @param   string   $key
   * @return  bool
   */
  static public function hasKey($key)
  {
    return self::getByKey($key) == true;

  }

  /**
   * Get all instances
   *
   * @return DoctrineCollection
   */
  static public function getAll()
  {
    return Doctrine::getTable('sfCombinePlus')
                   ->findAll();
  }

  /**
   *
   * @return int
   */
  static public function deleteAll()
  {
    return Doctrine::getTable('sfCombinePlus')
                   ->createQuery()
                   ->delete()
                   ->execute();
  }
}