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

package be.khleuven.frank.JCpg.Save;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.io.Serializable;

import be.khleuven.frank.JCpg.Components.JCpgGallery;



/**
 * 
 * Save objects to binary file or delete them from it
 * 
 * @author Frank Cleynen
 *
 */
public class JCpgGallerySaver implements Serializable{
	
	
	
	
																				
																					//*************************************
																					//				CONSTRUCTOR           *
																					//*************************************
	/**
	 * 
	 * Makes a new JCpgGallerySaver object, no arguments
	 *
	 */
	public JCpgGallerySaver(){
		
	}
	/**
	 * 
	 * Makes a new JCpgGallerySaver object, no arguments
	 * 
	 * @param gallery
	 * 		reference to the gallery that has to be saved
	 */
	public void saveGallery(JCpgGallery gallery){
		
		FileOutputStream fos;
		
		try {
			
			File current = new File("current.dat");
			if(current.exists()) current.delete(); // delete of existing
			
			fos = new FileOutputStream("current.dat", false); // this file always contains the most current state of the server we know of, false = overwrite
			ObjectOutputStream oos = new ObjectOutputStream(fos);
	        oos.writeObject(gallery);
	        
	        oos.close();
	        
	        System.out.println("JCpgGallerySaver: gallery saved sucessfully to current.dat");
	        
		} catch (Exception e) {
			
			System.out.println("JCpgGallerySaver: couldn't save gallery to current.dat");
			
		}
		
	}
	
	
	
	
										
																					//*************************************
																					//				MUTATORS & OTHERS     *
																					//*************************************
	/**
	 * 
	 * Load the last saved gallery
	 * 
	 * @return
	 * 		loaded gallery object
	 */
	public JCpgGallery loadGallery(){
		
		try {
			
			FileInputStream istream = new FileInputStream("current.dat");
			ObjectInputStream p = new ObjectInputStream(istream);
			JCpgGallery gallery = (JCpgGallery)p.readObject();
			istream.close();
			
			return gallery;
			
		} catch (Exception e) {
			
			System.out.println("JCpgGallerySaver: couldn't load gallery from current.dat");
			
		}
		
		return null;
		
	}

}
