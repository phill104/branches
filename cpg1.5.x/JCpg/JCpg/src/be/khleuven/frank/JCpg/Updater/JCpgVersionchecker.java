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

package be.khleuven.frank.JCpg.Updater;

import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.net.URL;
import java.net.URLConnection;

import be.khleuven.frank.JCpg.Manager.JCpgProgressManager;

/**
 * Visits the jcpgtool.org website and looks for an update
 * @author    Frank Cleynen
 */
public class JCpgVersionchecker {
	
	
	
																									//*************************************
																									//				VARIABLES             *
																									//*************************************
	private String versionUrl = null ;
	private String updateUrl = null ;
	private int updatefileSize = 0;
	private double serverVersion = 0.0, currentversion = 0.0;
	
	
																									
																									//*************************************
																									//				CONSTRUCTORS										*
																									//*************************************
	/**
	 * 
	 * Create a new Versionchecker object
	 * 
	 * @param versionUrl
	 * 		url to the remote version file
	 * @param updateUrl
	 * 		url to the update file
	 */
	public JCpgVersionchecker(String versionUrl){
		
		setVersionUrl(versionUrl);
		
	}
	
	
	
	
	
																									
																									//*************************************
																									//				SETTERS	              *
																									//*************************************
	/**
	 * 
	 * Set the server version
	 * 
	 * @param serverVersion
	 * 		the server version
	 */
	private void setServerVersion(double serverVersion){
		
		this.serverVersion = serverVersion;
		
	}
	/**
	 * 
	 * Set the version url
	 * 
	 * @param versionUrl
	 * 		the url to the online version
	 */
	private void setVersionUrl(String versionUrl){
		
		this.versionUrl = versionUrl;
		
	}
	/**
	 * 
	 * Set the update url
	 * 
	 * @param updateUrl
	 * 		the url to the update
	 */
	private void setUpdateUrl(String updateUrl){
		
		this.updateUrl = updateUrl;
		
	}
	/**
	 * 
	 * Set the filesize of the actual update file
	 * 
	 * @param updatefileSize
	 * 		the filesize of the actual update file
	 */
	private void setUpdateFileSize(int updatefileSize){
		
		this.updatefileSize = updatefileSize;
		
	}
	/**
	 * 
	 * Set the local version
	 * 
	 * @param version
	 * 		the local version
	 */
	private void setCurrentVersion(double version){
		
		this.currentversion = version;
		
	}
	
	
	
	
	
	
																									
																									//*************************************
																									//				GETTERS	              *
																									//*************************************
	/**
	 * 
	 * Get the version currently on the server
	 * 
	 * @return
	 * 		the version currently on the server
	 */
	public double getServerVersion(){
		
		return this.serverVersion;
		
	}
	/**
	 * 
	 * Get the version url
	 * 
	 * @return
	 * 		the version url
	 */
	public String getVersionUrl(){
		
		return this.versionUrl;
		
	}
	/**
	 * 
	 * Get the update url
	 * 
	 * @return
	 * 	  	the update url
	 */
	public String getUpdateUrl(){
		
		return this.updateUrl;
		
	}
	/**
	 * 
	 * Get the updatefile size
	 * 
	 * @return
	 * 		the updatefile size
	 */
	public int getUpdatefileSize(){
		
		return this.updatefileSize;

	}
	/**
	 * 
	 * Get the local version
	 * 
	 * @return
	 * 		the local version
	 */
	public double getCurrentVersion(){
		
		return this.currentversion;
		
	}
	
	
	
	
	
	
																									
																									//*************************************
																									//				MUTATORS & OTHERS					*
																									//*************************************
	/**
	 * 
	 * Download remote version file
	 * 
	 * @return
	 * 		true if download succeeds, else false
	 */
	public boolean downloadRemoteVersionFile(){
		
		try {
			
			File delete = new File("rversion.dat"); // delete file if it already exists
			if(delete.exists()) delete.delete();
	    	
			URL url = new URL(getVersionUrl()); // download remote version
			BufferedOutputStream out = new BufferedOutputStream(new FileOutputStream("rversion.dat"));
			URLConnection conn = url.openConnection();
			InputStream in = conn.getInputStream();
			
			byte[] buffer = new byte[1024];
			int numRead;
			
			while ((numRead = in.read(buffer)) != -1) {
				
				out.write(buffer, 0, numRead);
				
			}

		    out.close();
		    
		    return true;
			
		} catch (Exception e) {
			
			e.printStackTrace();
			System.out.println("JCpgVersionChecker: couldn't retrieve remote version");
		
		}
		
		return false;
		
	}
	/**
	 * 
	 * Get all the information form the remote version file
	 * This file currently contains 3 lines:
	 * 	1) server version
	 * 	2) url to update file
	 * 	3) size of this update file in bytes, used by progressbar 
	 * 
	 * @return
	 * 		true if the file was processed correctlyn otherwhise false
	 */
	public boolean processVersionFile(){
		
		try {
			
			File file = new File("rversion.dat"); // delete file after reading
			
			if(file.exists()){
			
		        BufferedReader in = new BufferedReader(new FileReader("rversion.dat")); // read remote version
		        
		        setServerVersion(new Double(in.readLine()));
		        setUpdateUrl(in.readLine());
		        setUpdateFileSize(new Integer(in.readLine()));
		        
		        in.close();
		        
				if(file.exists()) file.delete();
				
				return true; // success
			}
			
			return false; // file does not exist
	        
	    } catch (IOException e) {
	    	
	    	e.printStackTrace();
	    	System.out.println("JCpgVersionChecker: couldn't read remote version (possible: file does not exist)");
	    	
	    }
	    
	    return false; // failure
		
	}
	/**
	 * 
	 * Check if a newer version is available
	 * 
	 * @return
	 * 		true if there is newer version, else false
	 */
	public boolean newVersionAvailable(){
		
		// read local version
		try {
			
	        BufferedReader in = new BufferedReader(new FileReader("config/version.dat")); // read version on disk
	        setCurrentVersion(new Double(in.readLine()));
	        in.close();
	        
	    } catch (IOException e) {
	    	
	    	e.printStackTrace();
	    	System.out.println("JCpgVersionChecker: couldn't read local version");
	    	
	    }
	    
	    // compare versions
	    if(getServerVersion() > getCurrentVersion()){
	    
	    	System.out.println("JCpgVersionChecker: New version available");
	    	return true;
	    	
	    }else{
	    	
	    	System.out.println("JCpgVersionChecker: No new version available");
	    	return false;
	    	
	    }
		
	}
	/**
	 * 
	 * Get the actual updatefile
	 * 
	 * @return
	 * 		true if the update file was downloaded succesfully, else false
	 */
	public boolean getUpdate(JCpgProgressManager progressManager){
		
	    try {
	    	
			URL url = new URL(getUpdateUrl()); // download update
			
			String[] parts = getUpdateUrl().split("/"); // split url at each / and get latest part = update filename
			
			BufferedOutputStream out = new BufferedOutputStream(new FileOutputStream(parts[parts.length-1]));
			URLConnection conn = url.openConnection();
			InputStream in = conn.getInputStream();
			
			byte[] buffer = new byte[1024];
			int numRead;
			int readBytes = 0;
			
			while ((numRead = in.read(buffer)) != -1) {
				
				out.write(buffer, 0, numRead);
				readBytes = readBytes + numRead;
				progressManager.changeProgressbarValue(readBytes);
				
			}

		    out.close();
		    
		    // update local version
		    File delete = new File("config/version.dat");
		    if(delete.exists()) delete.delete();
		    
		    //BufferedOutputStream newversion = new BufferedOutputStream(new FileOutputStream("config/version.dat"));
		    //newversion.write(getServerVersion() + "");
		    
		    System.out.println("JCpgVersionChecker: update downloaded succesfully");
		    
		    return true; // success
			
		} catch (Exception e) {
			
			e.printStackTrace();
			System.out.println("JCpgVersionChecker: couldn't retrieve update");
		
		}
		
		return false; // failure
		
	}
	
}
