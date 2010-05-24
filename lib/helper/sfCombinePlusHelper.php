<?php

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
 * Returns <script> tags for all javascripts configured in view.yml or added to the response object.
 *
 * You can use this helper to decide the location of javascripts in pages.
 * By default, if you don't call this helper, symfony will automatically include javascripts before </head>.
 * Calling this helper disables this behavior.
 *
 * @return string <script> tags
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
 * Returns <link> tags with the url toward all stylesheets configured in view.yml or added to the response object.
 *
 * You can use this helper to decide the location of stylesheets in pages.
 * By default, if you don't call this helper, symfony will automatically include stylesheets before </head>.
 * Calling this helper disables this behavior.
 *
 * @return string <link> tags
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