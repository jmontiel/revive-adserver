<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2008 OpenX Limited                                     |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

// Required files
require_once MAX_PATH . '/lib/max/language/Default.php';
require_once MAX_PATH . '/lib/max/other/lib-io.inc.php';
require_once MAX_PATH . '/lib/max/other/lib-userlog.inc.php';
require_once MAX_PATH . '/www/admin/lib-gui.inc.php';

require_once MAX_PATH . '/lib/OA/Preferences.php';
require_once MAX_PATH . '/lib/OA/Permission.php';
require_once MAX_PATH . '/lib/OA/Auth.php';
$oDbh = OA_DB::singleton();
if (PEAR::isError($oDbh))
{
    // Check if UI is enabled
    if (!$conf['ui']['enabled']) {
        Language_Default::load();
        phpAds_PageHeader(OA_Auth::login($checkRedirectFunc));
        phpAds_ShowBreak();
        echo "<br /><img src='" . MAX::assetPath() . "/images/info.gif' align='absmiddle'>&nbsp;";
        echo $strNoAdminInterface;
        phpAds_PageFooter();
        exit;
    }

    // This text isn't translated, because if it is shown the language files are not yet loaded
    phpAds_Die ("A fatal error occurred", MAX_PRODUCT_NAME." can't connect to the database.
                Because of this it isn't possible to use the administrator interface. The delivery
                of banners might also be affected. Possible reasons for the problem are:
                <ul><li>The database server isn't functioning at the moment</li>
                <li>The location of the database server has changed</li>
                <li>The username or password used to contact the database server are not correct</li>
                <li>PHP has not loaded the MySQL Extension</li>
                </ul>");
}

// First thing to do is clear the $session variable to
// prevent users from pretending to be logged in.
unset($session);

// Authorize the user
OA_Start();

// Load the account's preferences
OA_Preferences::loadPreferences();
$pref = $GLOBALS['_MAX']['PREF'];

// Set time zone to local
OA_setTimeZoneLocal();

// Load the required language files
Language_Default::load();

// Register variables
phpAds_registerGlobalUnslashed(
     'affiliateid'
    ,'agencyid'
    ,'bannerid'
    ,'campaignid'
    ,'channelid'
    ,'clientid'
    ,'day'
    ,'trackerid'
    ,'userlogid'
    ,'zoneid'
);

if (!isset($affiliateid))   $affiliateid = (OA_Permission::isAccount(OA_ACCOUNT_TRAFFICKER)) ? OA_Permission::getEntityId() : '';
if (!isset($agencyid))      $agencyid = (OA_Permission::isAccount(OA_ACCOUNT_ADMIN)) ? '' : OA_Permission::getAgencyId();
if (!isset($bannerid))      $bannerid = '';
if (!isset($campaignid))    $campaignid = '';
if (!isset($channelid))     $channelid = '';
if (!isset($clientid))      $clientid = (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) ? OA_Permission::getEntityId() : '';
if (!isset($day))           $day = '';
if (!isset($trackerid))     $trackerid = '';
if (!isset($userlogid))     $userlogid = '';
if (!isset($zoneid))        $zoneid = '';

/**
 * Starts or continue existing session
 *
 * @param unknown_type $checkRedirectFunc
 */
function OA_Start($checkRedirectFunc = null)
{
    $conf = $GLOBALS['_MAX']['CONF'];
    global $session;

    // XXX: Why not try loading session data when OpenX is not installed?
    //if ($conf['openads']['installed'])
    if (OA_INSTALLATION_STATUS == OA_INSTALLATION_STATUS_INSTALLED)
    {
        phpAds_SessionDataFetch();
    }
    if (!OA_Auth::isLoggedIn() || OA_Auth::suppliedCredentials()) {
        // Required files
        include_once MAX_PATH . '/lib/max/language/Default.php';
        // Load the required language files
        Language_Default::load();

        phpAds_SessionDataRegister(OA_Auth::login($checkRedirectFunc));
    }
    // Overwrite certain preset preferences
    if (!empty($session['language']) && $session['language'] != $GLOBALS['pref']['language']) {
        $GLOBALS['_MAX']['CONF']['max']['language'] = $session['language'];
    }

}

?>
