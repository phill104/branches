/*
Copyright (C) 2007  Frank Cleynen
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

package be.khleuven.frank.JCpg;

import be.khleuven.frank.JCpg.UI.JCpgSplashscreen;
import be.khleuven.frank.JCpg.UI.JCpgUI;

/**
 * 
 * JCpg main class
 * 
 * @author Frank Cleynen
 *
 */
public class JCpg {
	
	public static void main(String[] args) {
		
		//new JCpgSplashscreen(359, 76, "data/splash.jpg" , 2000);
		//new JCpgSplashscreen(359, 76, "data/splash2.jpg" , 2000);
		JCpgUI ui = new JCpgUI(true);
		// new JCpgUpdater(ui, 100);

	}

}