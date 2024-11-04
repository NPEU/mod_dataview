<?php

namespace NPEU\Module\Dataview\Site\Helper;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';


use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;


use \Michelf\Markdown;

defined('_JEXEC') or die;

/**
 * Helper for mod_dataview
 *
 * @since  1.5
 */
class DataviewHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;


    /*public function getStuff(Registry $config, SiteApplication $app): array
    {
        if (!$app instanceof SiteApplication) {
            return [];
        }
        $db = $this->getDatabase();

        // Do some database stuff here.

        return ["Hello, world."];
    }*/


    /**
     * Loads JS/CSS
     *
     * @param array $params
     * @return void
     * @access public
     */
    public function loadAssets($params): void
    {
        $doc = Factory::getDocument();
        //$doc->addStyleSheet();
        //$doc->addScript();
        $template_path = Uri::getInstance()->root() . '/templates/npeu6';


        if ($params->get('highcharts', false)) {
            $doc->addScript('https://code.highcharts.com/highcharts.js');
            $doc->addScript('https://code.highcharts.com/highcharts-more.js');
            $doc->addScript('https://code.highcharts.com/modules/exporting.js');
            $doc->addScript('https://code.highcharts.com/modules/export-data.js');
            $doc->addScript('https://code.highcharts.com/modules/accessibility.js');
            $doc->addScript('https://code.highcharts.com/modules/annotations.js');
            $doc->addScript('https://code.highcharts.com/modules/pattern-fill.js');
        }

        if ($params->get('filterability', false)) {
            // Note I should probably use a CDN for this so it's not template-specific:
            $doc->addScript($template_path . '/js/filter.min.js');
        }

        if ($params->get('sortability', false)) {
            // Note I should probably use a CDN for this so it's not template-specific:
            $doc->addScript($template_path . '/js/sort.min.js');
        }
    }



    /**
     * Creates an HTML-friendly string for use in id's
     *
     * @param string $text
     * @return string
     * @access public
     */
    public function htmlID($text): string
    {
        if (!is_string($text)) {
            trigger_error('Function \'html_id\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $return = strtolower(trim(preg_replace('/\s+/', '-', $this->stripPunctuation($text))));
        return $return;
    }


    /**
     * Strips punctuation from a string
     *
     * @param string $text
     * @return string
     * @access public
     */
    public function stripPunctuation($text): string
    {
        if (!is_string($text)) {
            trigger_error('Function \'strip_punctuation\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $text = html_entity_decode($text, ENT_QUOTES);

        $urlbrackets = '\[\]\(\)';
        $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
        $urlspaceafter = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
        $urlall = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

        $specialquotes = '\'"\*<>';

        $fullstop = '\x{002E}\x{FE52}\x{FF0E}';
        $comma = '\x{002C}\x{FE50}\x{FF0C}';
        $arabsep = '\x{066B}\x{066C}';
        $numseparators = $fullstop . $comma . $arabsep;

        $numbersign = '\x{0023}\x{FE5F}\x{FF03}';
        $percent = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
        $prime = '\x{2032}\x{2033}\x{2034}\x{2057}';
        $nummodifiers = $numbersign . $percent . $prime;
        $return = preg_replace(
            [
                // Remove separator, control, formatting, surrogate,
                // open/close quotes.
                '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
                // Remove other punctuation except special cases
                '/\p{Po}(?<![' . $specialquotes .
                $numseparators . $urlall . $nummodifiers . '])/u',
                // Remove non-URL open/close brackets, except URL brackets.
                '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
                // Remove special quotes, dashes, connectors, number
                // separators, and URL characters followed by a space
                '/[' . $specialquotes . $numseparators . $urlspaceafter .
                '\p{Pd}\p{Pc}]+((?= )|$)/u',
                // Remove special quotes, connectors, and URL characters
                // preceded by a space
                '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
                // Remove dashes preceded by a space, but not followed by a number
                '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
                // Remove consecutive spaces
                '/ +/',
            ],
            ' ',
            $text
        );
        $return = str_replace('/', '_', $return);
        return str_replace("'", '', $return);
    }

    /**
     * Gets a twig instance - useful as we don't have to re-declare customisations each time.
     *
     * @param  array    $tpls   Array of strings bound to template names
     * @return object
     */
    public function getTwig(Registry $config, SiteApplication $app): object
    {
        $tpl = $config->get('data_tpl');
        $loader = new \Twig\Loader\ArrayLoader(['tpl' => $tpl]);
        $twig   = new \Twig\Environment($loader);

        #$twig   = new \Twig\Environment($loader, ['debug' => true]);
        #$twig->addExtension(new \Twig\Extension\DebugExtension());

        // Add is_array test:
        $is_array = new \Twig\TwigTest('array', function ($value) {
            return (bool) is_array($value);
        });
        $twig->addTest($is_array);

        // Add object test:
        $is_object = new \Twig\TwigTest('object', function ($value) {
            return (bool) is_object($value);
        });
        $twig->addTest($is_object);

        // Add markdown filter:
        $md_filter = new \Twig\TwigFilter('md', function ($string) {
            $new_string = '';
            // Parse md here
            $new_string = Markdown::defaultTransform($string);
            return $new_string;
        });

        $twig->addFilter($md_filter);
        // Use like {{ var|md|raw }}

        // Add pad filter:
        $pad_filter = new \Twig\TwigFilter('pad', function ($string, $length, $pad = ' ', $type = 'right') {
            $new_string = '';
            switch ($type) {
                case 'right':
                    $type = STR_PAD_RIGHT;
                    break;
                case 'left':
                    $type = STR_PAD_LEFT;
                    break;
                case 'both':
                    break;
                    $type = STR_PAD_BOTH;
            }
            $length = (int) $length;
            $pad    = (string) $pad;
            $new_string = str_pad($string, $length, $pad, $type);

            return $new_string;
        });
        $twig->addFilter($pad_filter);

        // Add regex_replace filter:
        $regex_replace_filter = new \Twig\TwigFilter('regex_replace', function ($string, $search = '', $replace = '') {
            $new_string = '';

            $new_string = preg_replace($search, $replace, $string);

            return $new_string;
        });
        $twig->addFilter($regex_replace_filter);

        // Add html_id filter:
        $html_id_filter = new \Twig\TwigFilter('html_id', function ($string) {
            $new_string = '';

            $new_string = $this->htmlID($string);

            return $new_string;
        });
        $twig->addFilter($html_id_filter);

        // Add sum filter:
        $sum_filter = new \Twig\TwigFilter('sum', function ($array) {
            return array_sum($array);
        });
        $twig->addFilter($sum_filter);

        // Add str_replace filter:
        $str_replace = new \Twig\TwigFilter('str_replace', function ($string, $search = '', $replace = '') {
            $new_string = '';

            $new_string = str_replace( $search, $replace, $string);

            return $new_string;
        });
        $twig->addFilter($str_replace);


       // Add filter for image fallback (image to use if preferred one doesn't exist):
        $img_fallback_filter = new \Twig\TwigFilter('fallback', function ($image_path, $fallback_path) {

            $file_headers = @get_headers($image_path);
            if($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                return $image_path;
            }

            $file_headers = @get_headers($fallback_path);
            if($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                return $fallback_path;
            }

            return '';
        });
        $twig->addFilter($img_fallback_filter);

       // Add filter for image path (height from width):
       $img_height_filter = new \Twig\TwigFilter('height', function ($image_path, $width) {

            $image_info = @getimagesize($image_path);

            if (!$image_info) {
                return 'image path not found: ' . $image_path;
            }

            $width = (int) $width;

            if ($image_info[0] > $image_info[1]) {
                $image_ratio = $image_info[0] / $image_info[1];
                $height = round($width / $image_ratio);
            } else {
                $image_ratio = $image_info[1] / $image_info[0];
                $height = round($width * $image_ratio);
            }
            //$height = round($width * $image_ratio);

            return $height;
        });
        $twig->addFilter($img_height_filter);

       // Add filter for image path (width from height):
       $img_width_filter = new \Twig\TwigFilter('width', function ($image_path, $height) {

            $image_info = @getimagesize($image_path);

            if (!$image_info) {
                return 'image path not found: ' . $image_path;
            }

            $height = (int) $height;

            if ($image_info[0] > $image_info[1]) {
                $image_ratio = $image_info[0] / $image_info[1];
                $width = round($height * $image_ratio);
            } else {
                $image_ratio = $image_info[1] / $image_info[0];
                $width = round($height / $image_ratio);
            }
            //$width = round($height / $image_ratio);

            return $width;
        });
        $twig->addFilter($img_width_filter);


        return $twig;
    }
}
