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

class KunenaPushalert extends KunenaActivity
{
    /**
     * @var null
     * @since Kunena
     */
    protected $params = null;

    /**
     * @var string|null
     */
    private $curlUrl = null;

    /**
     * @var null
     */
    private $apiKey = null;

    /**
     * KunenaActivityCommunity constructor.
     *
     * @param $params
     *
     * @since version
     */
    public function __construct($params)
    {
        $this->params = $params;
        $this->curlUrl = "https://api.pushalert.co/rest/v1/send";
        $this->apiKey = $this->params->get("apikey", null);
        $this->lang = JFactory::getLanguage();
        $this->lang->load('plg_kunena_pushalert', JPATH_ADMINISTRATOR);

    }

    /**
     * @param KunenaForumMessage $message
     *
     * @return bool|void
     *
     * @since version
     */
    public function onAfterReply($message)
    {
        $this->_prepareAndSend(
            $message,
            Text::_("PLG_KUNENA_PUSHALERT_REPLY_TITLE"),
            Text::_("PLG_KUNENA_PUSHALERT_REPLY_MSG")
        );
    }

    /**
     * @param KunenaForumTopic $message
     *
     * @return bool|void
     *
     * @since version
     */
    public function onAfterPost($message)
    {
        $this->_prepareAndSend(
            $message,
            Text::_("PLG_KUNENA_PUSHALERT_TOPIC_TITLE"),
            Text::_("PLG_KUNENA_PUSHALERT_TOPIC_MSG")
        );
    }

    /**
     * @param KunenaDatabaseObject $message
     *
     * @return boolean
     * @since Kunena
     */
    private function _checkPermissions($message)
    {
        $category = $message->getCategory();
        $accesstype = $category->accesstype;

        if ($accesstype != 'joomla.group' && $accesstype != 'joomla.level') {
            return false;
        }

        // FIXME: Joomla 2.5 can mix up groups and access levels
        if ($accesstype == 'joomla.level' && $category->access <= 2) {
            return true;
        } elseif ($category->pub_access == 1 || $category->pub_access == 2) {
            return true;
        } elseif ($category->admin_access == 1 || $category->admin_access == 2) {
            return true;
        }

        return false;
    }

    /**
     * @param $title
     * @param $pushMessage
     * @param $url
     *
     *
     * @since version
     */
    private function _send_message($title, $pushMessage, $url)
    {
        $post_vars = array(
            "title" => $title,
            "message" => $pushMessage,
            "url" => JUri::base() . mb_substr($url, 1)
        );

        $headers = Array();
        $headers[] = "Authorization: api_key=" . $this->apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->curlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_exec($ch);
    }

    /**
     * @param KunenaDatabaseObject $message
     * @param string $translatedTitle
     * @param string $translatedMsg
     *
     * @return void
     */
    private function _prepareAndSend($message, $translatedTitle, $translatedMsg)
    {
        if ($this->_checkPermissions($message)) {
            $title = sprintf($translatedTitle, $message->name);
            $pushMessage = sprintf($translatedMsg, $message->subject);
            $url = $message->getTopic()->getUrl();
            $this->_send_message($title, $pushMessage, $url);
        }
    }
}