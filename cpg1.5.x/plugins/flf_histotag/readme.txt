

flf histotag for Coppermine - version 1.3 - released 2010-02-06

requires COPPERMINE version 1.5.2 (tell me, if it works with older versions)

==============================================================================================================
brickware - released free of charge
==============================================================================================================
This plugin is released free of charge under the Attribution-Noncommercial-Share Alike 3.0 Unported License.
I coded this plugin, because I had fun doing it, not because I want to make money out of it.
However, writing good code is a time consuming effort that limits the time you can 
spend with your loved ones. I share the work, because I believe it can be of good use 
to others so that they can spend an afternoon with their partners or kids instead
of writing something similar themselves.

I'm not asking for something in return. But if you do like this plugin and want to 
appreciate a little bit of the time I put into it, my son would be happy if you could add
to his LEGO(tm) collection. So I call this plugin "brickware" - send him some of your used 
(but preferably still usable!) LEGO(tm) parts, modules, anything. A single brick will do just 
the same as the big Millenium Falcon in mint condition ;-).
So if you have some unused LEGO(tm) bricks (parts from the LEGO(tm) space collection preferrably) 
that you want to give freely in appreciation for the use of this plugin, please send them with regular mail to:

	Florian Lechner
	29589255
	Packstation 103
	D-85057 Ingolstadt
	Germany

Leave your email-address and I'll send you a thank-you picture of your bricks in action.
==============================================================================================================
Credits
==============================================================================================================
- This plugin for Coppermine was inspired and is in part based upon "easyMap for Pixelpost" by http://maximee.de
- Credits to Doug Pillow for the geodata conversion algorithm http://www.weberdev.com/get_example-3548.html
- Credits to eenemeenemuu for his fav_button Plugin that showed me how to add buttons to the navbar
- The function to generate the histograms was adapted from the Pixelpost addon by Kevin Crafts http://blog.kevincrafts.com/
- The configuration screen uses the farbtastic color picker by Steven Wittens http://acko.net/dev/farbtastic
- The wonderful images supplied with this plugin come from http://www.famfamfam.com/lab/icons/silk/
==============================================================================================================
History
==============================================================================================================
v1.4    [B] fixed a bug that left some pictures without geobutton even though geodata exists
		[B] fixed a bug regarding include files
		[B] fixed the link to the farbtastic colorpicker images in the config screen
		[A] centered the histogram during output
		[A] Using intermediate or thumbnail size images for the histogram calculation in case they exist. This dramatically
		    increases performance, the output quality is not really affected that much when using the intermediate image.
			It does however make a bigger difference when using the thumbnails. Decide for yourself: accuracy vs. speed
		[A] added a setting in the configuration screen for the above option
		[M] Changed the icons supplied with the plugins to "Silk icon set" by www.famfamfam.com
		[B] Fixed a bug that plugin information did not show in plugin manager, when plugin not yet installed
v1.3	[A] complete rewrite of the configuration screen
		[A] added function to delete all histograms in the system in case you don't want them anymore or need to regenerate		
		[A] added parameter for image quality of histograms (1-100) in %; less quality makes smaller file size
		[A] added options to change the Google map style (satellite, regular etc.)
		[O] Histograms are no longer stored inside the histograms folder; instead they are stored in the same
		    folder the picture resides in.
		[C] changed all parameters names according to coding conventions
		[C] sanitized the passing of the pid-parameter to the display-histogram function; thanks to GauGau for the hint
		[C] removed setting for changing tablename
		[C] changed configuration options for geosupport so it is clearer, what they mean.
		[D] eliminated most of the language-specific texts
v1.2 	[B] fixed a bug when uploading images with no exif data
		[B] fixed a bug when trying to generate all histograms 
v1.1	[C] Renamed files according to coding conventions
		[C] Changed Tablename according to naming conventions
		[C] Removed lytebox for displaying pictures, substituted with greybox - thanks to 'TimosWelt' for the hint
		[A] Histograms are now being generated on demand only when the histogram button
		    is clicked. This way the system doesn't get cluttered with histograms that
		    noone is interested in seeing. Downside: first time to call the histogram takes
		    some time.
		[A] Changed Histogram quality to 75% to get smaller filesizes
v1.0	[S] Security issues fixed, update new language method; thanks to 'GauGau' for this
v0.9	    Initial release

[A] = Added new feature
[B] = Bugfix (fix something that wasn't working as expected)
[C] = Cosmetical fix (layout, typo etc.)
[D] = Documentation improvements
[M] = Maintenance works
[O] = Optimization of code
[S] = Security fix (issues that are related to security)

==============================================================================================================
Installation & Configuration
==============================================================================================================
Install plugin with COPPERMINE Plugin Manager.
Edit configuration before first running the tool!

You need to have your own GOOGLE MAP API KEY before you can use the map function. If you don't yet have one, get yours
free at http://code.google.com/apis/maps/ 
and enter it into the appropriate variable. ('apikey')

All other options should be self explanatory.

==============================================================================================================
Upgrading
=============================================================================================================
Upgrading from Version v1.3
- Add new parameter manually into database insert IGNORE into {$CONFIG['TABLE_CONFIG']} values ('plugin_flf_histotag_imagesource','1')
  or uninstall old version & install v1.4 using plugin manager
Upgrading from Version v1.2
- The histograms folder is no longer needed. I recommend throwing the folder away and re-generating all histograms
  from the admin plugin screen
- As all the parameters names have changed you should uninstall the older versions before installing 1.3

Upgrading from Version <v1.1
- As I changed the tablename you either need to manually rename the old table in mysql:
  old name: flf_histotag
  new name: plugin_flf_histotag
  mysql command: RENAME TABLE <Coppermine_prefix>flf_histotag TO <Coppermine_prefix>plugin_flf_histotag
  Update the Config table to resemble the new table:
  UPDATE <COPPERMINE_PREFIX>_config SET value='plugin_flf_histotag' where name='plugin_flf_histotag_table';
UPDATE coppermine_config SET value='plugin_flf_histotag' where name='plugin_flf_histotag_table';
RENAME TABLE coppermine_flf_histotag TO coppermine_plugin_flf_histotag

  
- Copy and overwrite all files in the folder
  Alternative: Uninstall the plugin, the table will be dropped. Install the new version and reinitialize the
  data by using the plugin admin function provided. Note: If you use image manipulation upon upload, your
  original Exif data may no longer be available in the files on your albums: If that is the case, re-initializing
  the table will not be an option to you :-(
 
- Follow additional instructions as described in upgrading from version 1.2 
  
								 
								 
==============================================================================================================
What it does
=============================================================================================================
The Plugin serves two purposes:
 - provide Geotag support for Coppermine
 - provide histogram suppport for Coppermine
 
Geotag support:
Upon installation a new table is generated into the coppermine schema. This table holds all the EXIF data from 
images it can find. Right now, I'm extracting more data than I use for the geotagging feature. I don't know,
yet what to do with them in the future ;-)
The EXIF-Data are pulled from images upon upload. If you want to generate the EXIFs from all the existing
images in your database, a special function is nested inside the Plugin-Manager page.
Upon displaying of an image, it is checked, whether Geotag-Data is inside the plugin's table. If the 
required Geodata exists, an icon is displayed that can be clicked to open the location in a 
Google Maps view. If no Geodata is available, you can choose to hide the image or display an image without
a link.

Histogram support
If activated a histogram is created upon upload of an image. The settings are fully configurable. 
All histograms are stored in the directory the actual photos reside in and have a filename 
	hist_{coppermine_id}{original filename}.jpg
If you want to generate histograms for all the images inside your database, I provided a function in the
plugin manager page. However please note, that since timeouts of your webserver may occur, this function is not 
very reliable! I'll have to think of something better in the future. I don't recommend using this
feature. You should consider using the "on-the-fly"-generation. You can activate it from the plugin's config
screen.
Downside: First visitor to look at the histogram has to wait, until it is generated. 
Upside: No unnecessary histograms generated.

==============================================================================================================
Things on my To-Do-List
==============================================================================================================
- add screen when uninstalling to decide what to do with table, config values, histograms. Currently
  the table + config values are deleted upon uninstallation, the histograms remain on disk!
- Supply standalone script to generate all histograms on disk (avoid Timeout)


==============================================================================================================

� 2010 Florian Lechner - http://lounge-lizard.org/cms

flf histotag by http://lounge-lizard.org is licensed under a Attribution-Noncommercial-Share Alike 3.0 Unported License.
visit http://creativecommons.org/licenses/by-nc-nd/3.0/ for more details.