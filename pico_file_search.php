<?php

/**
 * Search to md files for Pico
 *
 * @author harmegiddo
 * @link https://github.com/hshintaro575
 * @license http://opensource.org/licenses/MIT
 * 
 * how to use:
 * 1: You will save this plugin to plugin's folder
 * 2: You will add to somewhere (e.g. top page) the following code.
 *   <form action="./filesearch" method="get">
 *   <p>
 *   Keyword: <input type="text" name="q">
 *   </p>
 *   <p>
 *   <input type="submit" value="Search">
 *   </p>
 *   </form>
 */

class Pico_FileSearch {
  private $is_search;
  private $base_url;
  private $content_dir;
  private $plugin_path;

  public function __construct(){
    $this->is_search = false;
    $this->plugin_path = dirname(__FILE__);
  }

  public function request_url(&$url)
  {
    if($url == 'filesearch') $this->is_search = true;
  }

  public function config_loaded(&$settings) {
    $this->base_url = $settings['base_url'];
    $this->content_dir = $settings['content_dir'];
  }

  public function before_render(&$twig_vars, &$twig)
  {
    if($this->is_search){
      $twig_vars['content'] = 'No item';

      if (isset($_GET["q"])) {
        $twig_vars['keyword'] = $_GET["q"];
      }else{
        $twig_vars['keyword'] = 'Keyword Error';
      }

      $dir = dirname(__FILE__);
      $pos = strpos($dir, "plugins");
      $dir =substr($dir, 0, $pos)."content";
      
      $iterator = new RecursiveDirectoryIterator($dir);
      $iterator = new RecursiveIteratorIterator($iterator);
      $list = array();
      foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile()) {
          if(preg_match('/\.(md)$/i', $fileinfo->getPathname()) == 1){
            $loadFile = file_get_contents($fileinfo->getPathname(), true);
            if ( strstr($loadFile, $twig_vars['keyword']) ){
              $dir = $fileinfo->getPathname();
              $pos = strpos($dir, "content");
              $dir = substr($dir, $pos);
              $list[] = $dir;
            }
          }
        }
      }
      $twig_vars['content'] = $list;
      header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
      $loader = new Twig_Loader_Filesystem($this->plugin_path);
      $twig_editor = new Twig_Environment($loader, $twig_vars);
      echo $twig_editor->render('pico_file_search_result.html', $twig_vars);
      exit;
    }
  }
}
?>