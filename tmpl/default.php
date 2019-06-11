<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_dataview
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;


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

if (!$data = file_get_contents($data_src)) {
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
        $output = $twig->render('tpl', array('data' => $json));
    }
}
?>
<?php echo $output; ?>