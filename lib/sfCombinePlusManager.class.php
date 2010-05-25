<?php
/**
 * A manager for sfCombinePlus files
 *
 * @package     sfCombinePlus
 * @subpackage  sfCombinePlusManager
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombinePlusManager
{
  /**
   * Grouping types
   */
  const GROUP_EXCLUDE = 0;
  const GROUP_INCLUDE = 1;

  /**
   * Javascript Manager
   *
   * @var sfCombinePlusManager
   */
  static protected $_jsManager = null;

  /**
   * CSS Manager
   *
   * @var sfCombinePlusManager
   */
  static protected $_cssManager = null;

  /**
   * An array of file names => group names
   *
   * @var array
   */
  protected $_groups = array();

  /**
   * An array of file names that are to be skipped
   *
   * @var array
   */
  protected $_skips = array();

  /**
   * An array of group names that have already been used
   *
   * @var array
   */
  protected $_usedGroups = array();


  /**
   * Retrieve the Javascript Manager, creates one if it doesn't exist
   *
   * @return sfCombinePlusManager
   */
  static public function getJsManager()
  {
    if (self::$_jsManager === null) {
      self::$_jsManager = new self();
    }
    return self::$_jsManager;
  }

  /**
   * Retrieve the CSS Manager, creates one if it doesn't exist
   *
   * @return sfCombinePlusManager
   */
  static public function getCssManager()
  {
    if (self::$_cssManager === null) {
      self::$_cssManager = new self();
    }
    return self::$_cssManager;
  }

  /**
   * Resets the grouping rules
   *
   * @return  void
   */
  public function reset()
  {
    $this->setGroups(array());
    $this->setSkips(array());
    $this->setUsedGroups(array());
  }

  /**
   * Get an array of files and their group
   *
   * @return  array   file name => group name
   */
  public function getGroups()
  {
    return $this->_groups;
  }

  /**
   * Set the groups
   *
   * @param   array   $groups   file name => group name
   * @return  sfCombinePlusManager
   */
  public function setGroups(array $groups)
  {
    $this->_groups = $groups;
    return $this;
  }

  /**
   * Add a grouped file
   *
   * @param   string $groupName Name of the group
   * @param   string $file      Name of the file
   * @return  sfCombinePlusManager
   */
  public function addToGroup($groupName, $file)
  {
    $this->_groups[$file] = $groupName;
    return $this;
  }

  /**
   * Remove a file from a particular group
   *
   * @param   string $groupName
   * @param   string $file
   * @return  sfCombinePlusManager
   */
  public function removeFromGroup($groupName, $file)
  {
    if (array_key_exists($file, $this->_groups)
    && $this->_groups[$file] == $groupName) {
      unset($this->_groups[$file]);
    }
    return $this;
  }

  /**
   * Get files to be skipped
   *
   * @return array  An array of file names
   */
  public function getSkips()
  {
    return $this->_skips;
  }

  /**
   * Set the files to be skipped
   *
   * @param   array $skips  An array of file names
   * @return  sfCombinePlusManager
   */
  public function setSkips(array $skips)
  {
    $this->_skips = $skips;
    return $this;
  }

  /**
   * Add a file to be skipped
   *
   * @param   string  $file
   * @return  sfCombinePlusManager
   */
  public function addSkip($file)
  {
    $this->_skips[] = $file;
    return $this;
  }

  /**
   * Remove a file from the list of files to be skipped
   *
   * @param   string  $file
   * @return  sfCombinePlusManager
   */
  public function removeFromSkips($file)
  {
    foreach (array_keys($this->_skips, $file) as $key) {
      unset($this->_skips[$key]);
    }
    return $this;
  }

  /**
   * Set the used groups
   *
   * @param   array $groups An array of group names
   * @return  sfCombinePlusManager
   */
  public function setUsedGroups(array $groups)
  {
    $this->_usedGroups = $groups;
    return $this;
  }

  /**
   * Get the used groups
   *
   * @return  array An array of group names
   */
  public function getUsedGroups()
  {
    return $this->_usedGroups;
  }

  /**
   * Update the array of used groups
   *
   * @param   string|array  $groups     Either a single group name or an array
   *                                    of them
   * @param   int           $groupsType (Optional) The type of grouping either
   *                                    sfCombinePlusManager::GROUP_INCLUDE or
   *                                    sfCombinePlusManager::GROUP_EXCLUDE.
   *                                    These dictate whether the group(s) in
   *                                    the previous argument should be marked
   *                                    as used or every group marked as used.
   *                                    Default
   *                                    sfCombinePlusManager::GROUP_INCLUDE
   * @return  sfCombinePlusManager
   */
  public function updateUsedGroups(
    $groups,
    $groupsType = self::GROUP_INCLUDE
  )
  {
    $usedGroups = $this->getUsedGroups();

    if (is_string($groups)) {
      $groups = array($groups);
    }

    if ($groupsType === self::GROUP_INCLUDE) {
      // only allow specific groups

      $usedGroups = array_merge($usedGroups, $groups);

    } else if ($groupsType === self::GROUP_EXCLUDE) {
      // only exclude specific groups

      $toMerge = array_diff(
        array_merge(
          array(''),
          $this->getGroups()
        ),
        $groups
      );

      $usedGroups = array_merge($usedGroups, $toMerge);
    }

    $this->setUsedGroups($usedGroups);

    return $this;
  }

  /**
   * Group the various assets together into an array. Determines which files
   * can be grouped together and the ordering.
   *
   * @param   array $assets           An array of file names
   * @param   bool  $combine          (Optional) Whether to allow combining or
   *                                  not. Default true.
   * @param   mixed $groupsUse        (Optional) A string or array of groups to
   *                                  include or exclude. Null for this to be
   *                                  ignored. Default null.
   * @param   int   $groupsUseType    (Optional) The type of grouping either
   *                                  sfCombinePlusManager::GROUP_INCLUDE or
   *                                  sfCombinePlusManager::GROUP_EXCLUDE.
   *                                  These dictate whether the group(s) in
   *                                  the previous argument should be marked
   *                                  as used or every group marked as used.
   *                                  Default sfCombinePlusManager::GROUP_INCLUDE
   * @param   bool  $onlyUnusedGroups (Optional) Only use unused groups. Default
   *                                  true.
   * @param   bool  $markGroupsUsed   (Optional) Mark the groups that are used
   *                                  as used. Default true.
   * @return  array An array of grouped files ready for combining. Array is in
   *                the form of files => array of file names, options => array
   *                of options for that grouping, combinable => bool (whether
   *                to put the file through sfCombinePlus (otherwise links
   *                outside of sfCombinePlus))
   */
  public function getAssetsByGroup(
    $assets,
    $combine = true,
    $groupsUse = null,
    $groupsUseType = self::GROUP_INCLUDE,
    $onlyUnusedGroups = true,
    $markGroupsUsed = true
  )
  {
    $groupData = $this->getGroups();

    $return = array();

    foreach($assets as $file => $options) {
      // check asset still needs to be added
      if (!array_key_exists($file, $assets)) {
        continue;
      }

      // get the group this file is in
      $group = array_key_exists($file, $groupData)
               ? $groupData[$file]
               : '';

      // work out
      if ($groupsUse !== null) {

        if (is_string($groupsUse)) {
          $groupsUse = array($groupsUse);
        }


        if ($groupsUseType === self::GROUP_INCLUDE) {
          // only allow specific groups

          if (!is_array($groupsUse) || !in_array($group, $groupsUse)) {
            // don't include this group
            continue;
          }

        } else if ($groupsUseType === self::GROUP_EXCLUDE) {
          // only exclude specific groups

          if (is_array($groupsUse) && in_array($group, $groupsUse)) {
            // don't include this group
            continue;
          }

        }

      }

      // don't output a used group
      if (in_array($group, $this->getUsedGroups()))
      {
        continue;
      }

      if (!$combine
      || !sfCombinePlusUtility::combinableFile($file, $this->getSkips())) {
        // file not combinable
        $return[] = array(
          'files' => $file,
          'options' => $options,
          'combinable' => false
        );
        unset($assets[$file]);
      } else {

        // get the group this file is in
        $group = array_key_exists($file, $groupData)
                 ? $groupData[$file]
                 : '';

        $combinedFiles = array($file);

        unset($assets[$file]);

        foreach ($assets as $groupedFile => $groupedOptions) {

          if (!sfCombinePlusUtility::combinableFile($groupedFile, $this->getSkips())
          || $options != $groupedOptions) {
            continue;
          }

          $groupedFileGroup = array_key_exists($groupedFile, $groupData)
                              ? $groupData[$groupedFile]
                              : '';

          // add this file to this combine
          if ($group == $groupedFileGroup) {
            $combinedFiles[] = $groupedFile;
            unset($assets[$groupedFile]);
          }
        }

        $return[] = array(
          'files' => $combinedFiles,
          'options' => $options,
          'combinable' => true
        );
      }
    }

    if ($markGroupsUsed) {
      $this->updateUsedGroups($groupsUse, $groupsUseType);
    }

    return $return;
  }
}
