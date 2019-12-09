<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_dataview
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use \Michelf\Markdown;


$doc = JFactory::getDocument();

$data_src        = $params->get('data_src');
$data_tpl        = $params->get('data_tpl');
$data_src_err    = $params->get('data_src_err');
$data_decode_err = $params->get('data_decode_err');

// Allow for relative data src URLs:
if (strpos($data_src, 'http') !== 0) {
    $s        = empty($_SERVER['SERVER_PORT']) ? '' : ($_SERVER['SERVER_PORT'] == '443') ? 's' : '';
    $protocol = preg_replace('#/.*#',  $s, strtolower($_SERVER['SERVER_PROTOCOL']));
    $domain   = $protocol.'://'.$_SERVER['SERVER_NAME'];
    $data_src = $domain . '/' . trim($data_src, '/');
}

$data = @file_get_contents($data_src);
if ($data === false) {
    $output = Markdown::defaultTransform($data_src_err);
} else {
    if (!$json = json_decode($data)) {
        $output = Markdown::defaultTransform($data_decode_err);
    } else {
        
        $twig = ModDataviewHelper::getTwig(array(
            'tpl' => $data_tpl
        ));
 
        // Encode then re-decode to produce valid JSON:
        $json = json_encode($json, true);
        $json = json_decode($json, true);
        
        #echo '<pre>'; var_dump($data_tpl); echo '</pre>'; #exit;
        #echo '<pre>'; var_dump($json); echo '</pre>'; exit;
        
        //$output = $twig->render('tpl', array('data' => $json));
        
        try {
            $output = $twig->render('tpl', array('data' => $json));
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
       
    }
}
?>
<?php echo $output; ?>