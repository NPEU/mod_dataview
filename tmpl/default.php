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
    $s        = empty($_SERVER['SERVER_PORT']) ? '' : ($_SERVER['SERVER_PORT'] == '443' ? 's' : '');
    $protocol = preg_replace('#/.*#',  $s, strtolower($_SERVER['SERVER_PROTOCOL']));
    $domain   = $protocol.'://'.$_SERVER['SERVER_NAME'];
    $data_src = $domain . '/' . trim($data_src, '/');
}

// Inspect the final URL to determine if it's an internal or external address:
$url_parts = parse_url($data_src);

// Check for proxy: (note we DON'T want to use this if it's an internal URL)
$proxy     = NULL;
$config    = JFactory::getConfig();
$has_proxy = $config->get('proxy_enable');

if ($has_proxy && $_SERVER['SERVER_NAME'] != $url_parts['host']) {
    $proxy_host = $config->get('proxy_host');
    $proxy_port = $config->get('proxy_port');
    $proxy_user = $config->get('proxy_user');
    $proxy_pass = $config->get('proxy_pass');
    
    $context = array(
        'http' => array(
            'proxy'           => $proxy_host . ':' . $proxy_port,
            'request_fulluri' => true
        )
    );
    $proxy = stream_context_create($context);
}

$data = file_get_contents($data_src, false, $proxy);

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