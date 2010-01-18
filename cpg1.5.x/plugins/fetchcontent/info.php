<?php
/********************************************************
  Coppermine 1.5.x plugin - FetchContent
  *******************************************************
  Copyright (c) 2010 Coppermine dev team
  *******************************************************
  This program is free software; you can redistribute 
  it and/or modify it under the terms of the GNU General
  Public License as published by the Free Software
  Foundation; either version 3 of the License, or 
  (at your option) any later version.
  *******************************************************
  $HeadURL: https://coppermine.svn.sourceforge.net/svnroot/coppermine/branches/cpg1.5.x/plugins/fetchcontent/admin.php $
  $Revision: 7043 $
  $LastChangedBy: gaugau $
  $Date: 2010-01-11 19:26:53 +0100 (Mo, 11. Jan 2010) $
  *******************************************************/
  
// create Inspekt supercage$superCage = Inspekt::makeSuperCage();

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}


if ($superCage->get->keyExists('pretty')) {
    pageheader('Test');
}

echo <<< EOT
This page will output all kinds of information that is available and populated about the visitor of this page - play with and by logging in and out or changing permissions.<br />
Use the URL parameter "pretty" to embedd the output into coppermine's output.

<h1>array $<b></b>USER</h1>
<textarea rows="5" class="textinput" style="width:100%">
EOT;
print_r($USER);
echo <<< EOT
</textarea>
<h1>array $<b></b>USER_DATA</h1>
<textarea rows="5" style="width:100%">
EOT;
print_r($USER_DATA);
echo <<< EOT
</textarea>

<h1>array $<b></b>FORBIDDEN_SET_DATA</h1>
List of albums that the current visitor is <strong>not</strong> allowed to access. 
<textarea rows="5" style="width:100%">
EOT;
print_r($FORBIDDEN_SET_DATA);
echo <<< EOT
</textarea>

<h1>constant USER_ID</h1>
Is zero if the visitor is a guest, i.e. not logged in:<br />
EOT;
echo USER_ID;
echo <<< EOT

<h1>constant USER_GROUP_SET</h1>
Contains the groups the user is a member of (as a coma-separated list). 
<textarea rows="5" style="width:100%">
EOT;
echo USER_GROUP_SET;
echo <<< EOT
</textarea>


EOT;
if ($superCage->get->keyExists('pretty')) {
    pagefooter();
}
?>