<?php
/**
 * @package    kunena-pushalert
 *
 * @author     Michael Pfister <michael@mp-development.de>
 * @copyright  Michael Pfister
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

/**
 * Kunena-push-alert plugin.
 *
 * @package  kunena-push-alert
 * @since    1.0
 */
class plgKunenaPushalert extends \Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  1.0
     */
    // protected $autoloadLanguage = true;

    public function __construct($subject, array $config = array())
    {
        if (!(class_exists('KunenaForum') && KunenaForum::isCompatible('3.0') && KunenaForum::installed()))
        {
            return true;
        }

        parent::__construct($subject, $config);
    }

    /**
     * Get Kunena activity stream integration object.
     *
     * @return \KunenaPushalert|null
     * @since Kunena
     */
    public function onKunenaGetActivity()
    {
        require_once __DIR__ . "/push.php";
        return new KunenaPushalert($this->params);
    }
}
