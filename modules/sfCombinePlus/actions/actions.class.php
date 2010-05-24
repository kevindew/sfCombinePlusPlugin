<?php
/**
 * sfCombinePlusActions
 *
 * Based off sfCombineAction by Alexandre Mogère
 *
 * @package    sfCombinePlusPlugin
 * @author     Kevin Dew
 */
class sfCombinePlusActions extends sfActions
{
  /**
   * @return  void
   *
   * @see     sfAction
   */
  public function preExecute()
  {
    sfConfig::set('sf_web_debug', false);
    $this->setTemplate('asset');

    // cache
    sfCombinePlusUtility::setCacheHeaders($this->getResponse());

    // gzip
    sfCombinePlusUtility::setGzip();
  }


  /**
   * @see sfActions::execute
   */
  public function executeJs()
  {
    $this->getResponse()->setContentType('application/x-javascript');
    $config = sfConfig::get('app_sfCombinePlusPlugin_js', array());
    $combinerClass = isset($config['combiner_class'])
                   ? $config['combiner_class']
                   : 'sfCombinePlusCombinerJs';
    $combiner = new $combinerClass(
      $config,
      $this->getRequestParameter('key'),
      $this->getRequestParameter('base64'),
      $this->getRequestParameter('files')
    );
    $this->assets = $combiner->process();
  }

  /**
   * @see sfActions::execute
   */
  public function executeCss()
  {
    $this->getResponse()->setContentType('text/css');
    $config = sfConfig::get('app_sfCombinePlusPlugin_css', array());
    $combinerClass = isset($config['combiner_class'])
                   ? $config['combiner_class']
                   : 'sfCombinePlusCombinerCss';
    $combiner = new $combinerClass(
      $config,
      $this->getRequestParameter('key'),
      $this->getRequestParameter('base64'),
      $this->getRequestParameter('files')
    );
    $this->assets = $combiner->process();    
  }
}