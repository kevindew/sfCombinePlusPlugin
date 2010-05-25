<?php

/**
 * Add a javascript file for grouping
 *
 * @param   string $js            The javascript filename
 * @param   string $group         (Optional) The name of the group to be added
 *                                to. Default empty string.
 * @param   bool   $doNotCombine  (Optional) Whether to blacklist this file from
 *                                combining. Default false
 * @param   string $position      (Optional) @see sfWebResponse::addJavascript
 * @param   array  $options       (Optional) @see sfWebResponse::addJavascript
 * @return  void
 */
function use_javascript_grouped(
  $js,
  $group = '',
  $doNotCombine = false,
  $position = '',
  $options = array()
)
{
  $manager = sfCombinePlusManager::getJsManager();

  sfContext::getInstance()
           ->getResponse()
           ->addJavascript($js, $position, $options);

  $manager->addToGroup($group, $js);

  if ($doNotCombine) {
    $manager->addSkip($js);
  }
}

/**
 * Output the combined javascripts
 *
 * @see get_combined_javascripts
 */
function include_combined_javascripts(
  $groups = null,
  $groupType = sfCombinePlusManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  echo get_combined_javascripts($groups, $groupType);
}

/**
 * Get the combined Javascripts in script links. Can get all groups or a
 * selection. Calling this method will stop symfony automatically inserting
 * scripts
 *
 *
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
 * @return  string
 */
function get_combined_javascripts(
  $groups = null,
  $groupType = sfCombinePlusManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{  
  if (!sfConfig::get('app_sfCombinePlusPlugin_enabled', false)) {
    return get_javascripts();
  }

  $manager = sfCombinePlusManager::getJsManager();

  sfConfig::set('symfony.asset.javascripts_included', true);

  $response = sfContext::getInstance()->getResponse();
  $config = sfConfig::get('app_sfCombinePlusPlugin_js', array());
  $doNotCombine = isset($config['combine_skip'])
    ? $config['combine_skip']
    : array();
  $manager->setSkips(array_merge(
    $manager->getSkips(),
    $doNotCombine
  ));

  $groupedFiles = $manager->getAssetsByGroup(
    $response->getJavascripts(),
    $config['combine'],
    $groups,
    $groupType,
    $onlyUnusedGroups,
    $markGroupsUsed
  );

  $html = '';

  foreach ($groupedFiles as $fileDetails) {
    if (!$fileDetails['combinable']) {
      $html .= javascript_include_tag(
        javascript_path($fileDetails['files']),
        $fileDetails['options']
      );
    } else {

      $route = isset($config['route']) ? $config['route'] : 'sfCombinePlus';

      $html .= javascript_include_tag(
        url_for(
          '@' . $route . '?module=sfCombinePlus&action=js&'
          . sfCombinePlusUrl::getUrlString($fileDetails['files'])
        ),
        $fileDetails['options']
      );
    }
  }

  return $html;
}

/**
 * @see use_javascript_grouped
 */
function use_stylesheet_grouped(
  $css,
  $group = '',
  $doNotCombine = false,
  $position = '',
  $options = array()
)
{
  $manager = sfCombinePlusManager::getCssManager();

  sfContext::getInstance()
           ->getResponse()
           ->addStylesheet($css, $position, $options);

  $manager->addToGroup($group, $css);

  if ($doNotCombine) {
    $manager->addSkip($css);
  }
}

/**
 * @see include_combined_javascripts
 */
function include_combined_stylesheets(
  $groups = null,
  $groupType = sfCombinePlusManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  echo get_combined_stylesheets();
}

/**
 * @see get_combined_javascripts
 */
function get_combined_stylesheets(
  $groups = null,
  $groupType = sfCombinePlusManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  if (!sfConfig::get('app_sfCombinePlusPlugin_enabled', false)) {
    return get_stylesheets();
  }

  $manager = sfCombinePlusManager::getCssManager();

  sfConfig::set('symfony.asset.stylesheets_included', true);

  $response = sfContext::getInstance()->getResponse();
  $config = sfConfig::get('app_sfCombinePlusPlugin_css', array());
  $doNotCombine = isset($config['combine_skip'])
    ? $config['combine_skip']
    : array();
  $manager->setSkips(array_merge(
    $manager->getSkips(),
    $doNotCombine
  ));

  $groupedFiles = $manager->getAssetsByGroup(
    $response->getStylesheets(),
    $config['combine'],
    $groups,
    $groupType,
    $onlyUnusedGroups,
    $markGroupsUsed
  );

  $html = '';

  foreach ($groupedFiles as $fileDetails) {
    if (!$fileDetails['combinable']) {
      $html .= stylesheet_tag(
        stylesheet_path($fileDetails['files']),
        $fileDetails['options']
      );
    } else {

      $route = isset($config['route']) ? $config['route'] : 'sfCombinePlus';

      $html .= stylesheet_tag(
        url_for(
          '@' . $route . '?module=sfCombinePlus&action=js&'
          . sfCombinePlusUrl::getUrlString($fileDetails['files'])
        ),
        $fileDetails['options']
      );
    }
  }

  return $html;
}