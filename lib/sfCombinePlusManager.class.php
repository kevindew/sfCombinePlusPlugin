<?php
/**
 * 
 */
class sfCombinePlusManager
{
  const GROUP_EXCLUDE = 0;
  const GROUP_INCLUDE = 1;

  static protected $_jsManager = null;
  static protected $_cssManager = null;

  protected $_groups = array();
  protected $_skips = array();
  protected $_usedGroups = array();

  static public function getJsManager()
  {
    if (self::$_jsManager === null) {
      self::$_jsManager = new self();
    }
    return self::$_jsManager;
  }

  static public function getCssManager()
  {
    if (self::$_cssManager === null) {
      self::$_cssManager = new self();
    }
    return self::$_cssManager;
  }

  /**
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
   * @return  array
   */
  public function getGroups()
  {
    return $this->_groups;
  }

  /**
   * 
   * @param array $groups
   */
  public function setGroups(array $groups)
  {
    $this->_groups = $groups;
    return $this;
  }

  public function addToGroup($groupName, $file)
  {
    $this->_groups[$file] = $groupName;
  }

  public function removeFromGroup($groupName, $file)
  {
    if (array_key_exists($file, $this->_groups)
    && $this->_groups[$file] == $groupName) {
      unset($this->_groups[$file]);
    }
  }

  public function getSkips()
  {
    return $this->_skips;
  }

  public function setSkips(array $skips)
  {
    $this->_skips = $skips;
    return $this;
  }

  public function addSkip($file)
  {
    $this->_skips[] = $file;
  }

  public function removeFromSkips($file)
  {
    foreach (array_keys($this->_skips, $file) as $key) {
      unset($this->_skips[$key]);
    }
  }

  public function setUsedGroups(array $groups)
  {
    $this->_usedGroups = $groups;
    return $this;
  }

  public function getUsedGroups()
  {
    return $this->_usedGroups;
  }

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
  }

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
