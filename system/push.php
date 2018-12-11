<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class KunenaPush extends KunenaActivity
{
    /**
     * @var null
     * @since Kunena
     */
    protected $params = null;
    /**
     * KunenaActivityCommunity constructor.
     *
     * @param $params
     *
     * @since Kunena
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @param string $message
     *
     * @return bool|void
     *
     * @since version
     */
    public function onAfterPost($message)
    {
        if ($this->_checkPermissions($message))
        {
            $title = "Notification Title";
            $message = "Notification Message";
            $url = $message->getTopic()->getUrl();

            $apiKey = $this->params->get("apikey", null);

            $curlUrl = "https://api.pushalert.co/rest/v1/send";

            //POST variables
            $post_vars = array(
//            "icon" => $icon,
                "title" => $title,
                "message" => $message,
                "url" => $url,
            );

            $headers = Array();
            $headers[] = "Authorization: api_key=".$apiKey;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curlUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);

            $output = json_decode($result, true);
            if($output["success"]) {
                return true;
            }
            else {
                return false;
            }
        }
    }

    /**
     * @param $message
     *
     * @return boolean
     * @since Kunena
     */
    private function _checkPermissions($message)
    {
        $category   = $message->getCategory();
        $accesstype = $category->accesstype;

        if ($accesstype != 'joomla.group' && $accesstype != 'joomla.level')
        {
            return false;
        }

        // FIXME: Joomla 2.5 can mix up groups and access levels
        if ($accesstype == 'joomla.level' && $category->access <= 2)
        {
            return true;
        }
        elseif ($category->pub_access == 1 || $category->pub_access == 2)
        {
            return true;
        }
        elseif ($category->admin_access == 1 || $category->admin_access == 2)
        {
            return true;
        }

        return false;
    }
}