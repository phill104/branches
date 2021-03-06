<?php
// Users "authorize" a request token here.  This first involves logging in to a Coppermine account.  An application "consuming" the API should direct users here once they have received a request token.

require 'cpgOAuth.php';

define('IN_COPPERMINE', true);
require_once 'include/init.inc.php';

$token = $superCage->get->getAlnum('oauth_token');
$authorized = $superCage->get->getAlnum('authorized');

if ($token == '') {
    throw new OAuthException('No "oauth_token" provided via HTTP GET.');
}

$server = new OAuthServer();
$server->setParam('oauth_token', $token, true);
$rs = $server->authorizeVerify();

if ($authorized == 'yes') {
    $server->authorizeFinish(true, USER_ID);
    api_message('Token "' . $rs['token'] . '" authorized.');
}

else if ($authorized == 'no') {
    $server->authorizeFinish(false, USER_ID);
    api_message('Token "' . $rs['token'] . '" deleted.');
}

else {
    $store = OAuthStore::instance();
    $consumer = $store->getConsumerInfo($rs['consumer_id']);
    
    if (!USER_ID) {
        print 'Please <a href="../login.php?referer=oauth/authorize.php?oauth_token=' . $token . '">login</a> to your user account.<br />';
        print 'Access this gallery anonymously with the application "' . $consumer[0]['application_title'] . '"?';
        print '<br /><br />';
    }
    else {
        print 'Would you like to allow "' . $consumer[0]['application_title'] . '" to access your photos from this site?';
        print '<br /><br />';
    }

    print '<form method="get" action="authorize.php">';
    print '<input type="hidden" name="oauth_token" id="oauth_token" value="' . $token . '" />';
    print '<input type="radio" name="authorized" id="yes" value="yes" /><label for="yes">Yes</label>';
    print '<input type="radio" name="authorized" id="no" value="no" checked="checked" /><label for="no">No</label>';
    print '<br /><br /><button type="submit">Submit</button>';
    print '</form>';
}

?>