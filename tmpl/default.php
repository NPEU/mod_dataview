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

use Joomla\CMS\Factory;

use \Michelf\Markdown;


$doc = Factory::getDocument();

$output          = false;
$data            = false;
$json            = false;

$data_src        = $params->get('data_src', false);
$data_aqs_tog    = $params->get('aqs_tog');
$data_aqs        = $params->get('aqs');
$data_tpl        = $params->get('data_tpl');
$data_src_err    = $params->get('data_src_err');
$data_decode_err = $params->get('data_decode_err');

$url_qs = $_SERVER['QUERY_STRING'];

$form_vals = [];
$qs_empty = empty($url_qs);

// Process Advanced Query Strings:
if ($data_src && (!empty($url_qs) && $data_aqs_tog && !empty($data_aqs))) {

    $new_qs = [];

    $lines = explode("\n", trim(str_replace("\n\n", "\n", str_replace("\r", "\n", $data_aqs))));

    parse_str($url_qs, $url_qs_array);
#echo 'url_qs_array<pre>'; var_dump($url_qs_array); echo '</pre>'; #exit;
    foreach ($lines as $line) {

        list($param_name, $param_values) = explode("=", $line);


        // Are multiple values allowed? (array)
        $val_array_allowed = false;
        if (strstr($param_name, '[]') !== false) {
            $val_array_allowed = true;
            $param_name = str_replace('[]', '', $param_name);
        }

#echo 'param_name<pre>'; var_dump($param_name); echo '</pre>'; #exit;
        // Check the name of the param exists in the query string
        if (!array_key_exists($param_name, $url_qs_array)) {
            // This name does not appear in the URL, ignore:
            continue;
        }
#echo 'param_values<pre>'; var_dump($param_values); echo '</pre>'; #exit;
#echo 'param_name<pre>'; var_dump($param_name); echo '</pre>'; #exit;
        if (preg_match('#/.+/#', $param_values)) {
            // Test the pattern against the qs value:
        } else {
            if (strpos($param_values, '|') !== false) {
                $vals = explode('|', trim($param_values));
#echo 'vals<pre>'; var_dump($vals); echo '</pre>'; #exit;
#echo 'vals<pre>'; var_dump($url_qs_array[$param_name]); echo '</pre>'; #exit;

                if (is_array($url_qs_array[$param_name])) {
                    $t = [];
                    foreach ($url_qs_array[$param_name] as $v) {
                        if (in_array($v, $vals)) {
                            $t[] = $v;
                        }
                    }
                    $new_qs[$param_name] = implode(',', $t);

                } else {

                    if (in_array($url_qs_array[$param_name], $vals)) {
                        $new_qs[$param_name] = $url_qs_array[$param_name];
                    }
                }

            } else {
                // Unsupported value type, ignore:
                continue;
            }
        }

    }

    if (!empty($new_qs)) {
        $data_src = preg_replace('/\?.*$/', '', $data_src);
        $data_src .= '?' . urldecode(http_build_query($new_qs));
    }

    #echo '<pre>'; var_dump($data_src); echo '</pre>'; exit;

    if (!empty($new_qs)) {
        foreach ($new_qs as $name => $vals) {
            $form_vals[$name] = explode(',', $vals);
        }
    }
}


#echo '<pre>'; var_dump($form_vals); echo '</pre>'; exit;

if ($data_src) {
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
    $config    = Factory::getConfig();
    $has_proxy = $config->get('proxy_enable');

    if ($has_proxy && $_SERVER['SERVER_NAME'] != $url_parts['host']) {
        $proxy_host = $config->get('proxy_host');
        $proxy_port = $config->get('proxy_port');
        $proxy_user = $config->get('proxy_user');
        $proxy_pass = $config->get('proxy_pass');

        $context = [
            'http' => [
                'proxy'           => $proxy_host . ':' . $proxy_port,
                'request_fulluri' => true
            ]
        ];
        $proxy = stream_context_create($context);
    }

    $data = file_get_contents($data_src, false, $proxy);
    #echo '<pre>'; var_dump($data); echo '</pre>'; exit;

    if ($data === false) {
        $output = Markdown::defaultTransform($data_src_err);
    } else {
        $json = json_decode($data);
        if (is_null($json)) {
            $output = Markdown::defaultTransform($data_decode_err);
        }
        // Encode then re-decode to produce valid JSON:
        $json = json_encode($json, true);
        $json = json_decode($json, true);
    }
} else {
    $json = json_decode('{}', true);
}

if ($output === false) {
    /*
    $twig should already have been pushed here from the helper.
    */
    /*$twig = ModDataviewHelper::getTwig(array(
        'tpl' => $data_tpl
    ));*/

    #echo '<pre>'; var_dump($data_tpl); echo '</pre>'; #exit;
    #echo '<pre>'; var_dump($json); echo '</pre>'; exit;

    //$output = $twig->render('tpl', array('data' => $json));

    try {
        $output = $twig->render('tpl', ['data' => $json, 'form_vals' => $form_vals, 'qs_empty' => $qs_empty]);
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

}
?>
<?php echo $output; ?>