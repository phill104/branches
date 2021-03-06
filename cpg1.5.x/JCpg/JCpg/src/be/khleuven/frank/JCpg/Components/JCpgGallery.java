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

package be.khleuven.frank.JCpg.Components;



import java.util.ArrayList;

import javax.swing.tree.TreePath;

import be.khleuven.frank.JCpg.UI.JCpgUI;


/**
 * Cpg component: Gallery
 * @author    Frank Cleynen
 */
public class JCpgGallery{
	
	
	
														//*************************************
														//				VARIABLES             *
														//*************************************
	private JCpgUI ui = null;
	
	private String name = null ;
	private String description = null ;
	private int id = 0; // the id of "User Galleries" in Cpg is 0
	private boolean isModified = false; // says if the component is modified or not. If it is, modify parameters must be generated at sync time
	private ArrayList<JCpgCategory> categories = new ArrayList<JCpgCategory>();
	private ArrayList<JCpgAlbum> albums = new ArrayList<JCpgAlbum>();

	private TreePath treePath; // holds the path in the tree to the object so if we select a picture in the pictureList we can also make it selected in the tree
	
	
	
														//*************************************
														//				CONSTRUCTORS          *
														//*************************************
	/**
	 * 
	 * Makes a new JCpgGallery object. This object contains all the categories and albums in de list view. We use this object to order these components.
	 * Normally only one main gallery is active in JCgp but at a later time, when galleries may be part of Cpg, more could be
	 * active at the same time.
	 * 
	 */
	public JCpgGallery(String name, String description){
		
		setName(name);
		setDescription(description);
		
	}
	
	
	
														
														//*************************************
														//				SETTERS	              *
														//*************************************
	/**
	 * 
	 * Set the ui reference
	 * 
	 * @param ui
	 * 		the ui reference
	 * 
	 */
	private void setUi(JCpgUI ui){
		
		this.ui = ui;
		
	}
	/**
	 * 
	 * Set the gallery name
	 * 
	 * @param name
	 * 		the name of the gallery
	 */
	private void setName(String name){
		
		this.name = name;
		
	}
	/**
	 * 
	 * Set the gallery desciption
	 * 
	 * @param description
	 * 		the gallery desciption
	 */
	private void setDescription(String description){
		
		this.description = description;
		
	}
	
	
	
	
	
	
	
														
														//*************************************
														//				GETTERS               *
														//*************************************
	/**
	 * 
	 * Get gallery id. This is not important because its id is not needed for making sql queries or so but we need this to make the whole inheritance compleet
	 * 
	 * @return
	 * 		the gallery id
	 */
	public int getId(){
		
		return this.id;
		
	}
	/**
	 * 
	 * Get the ui reference
	 * 
	 * @return
	 * 		the ui reference
	 */
	public JCpgUI getUi(){

		return this.ui;
		
	}
	/**
	 * 
	 * Get the gallery name
	 * 
	 * @return
	 * 		the name of the gallery
	 */
	public String getName(){
		
		return this.name;
		
	}
	/**
	 * 
	 * Get the gallery description
	 * 
	 * @return
	 * 		the gallery description
	 */
	public String getDescription(){
		
		return this.description;
		
	}
	/**
	 * 
	 * Gets the caption. Used by pictures.
	 * 
	 * @return
	 * 		the picture caption
	 */
	public String getCaption(){
		
		return getDescription();
		
	}
	/**
	 * 
	 * Get an arraylist with the albums in this gallery
	 * 
	 * @return
	 * 		an arraylist with the albums in this gallery
	 */
	public ArrayList<JCpgAlbum> getAlbums(){
		
		return this.albums;
		
	}
	/**
	 * 
	 * Get an arraylist with the categories in this gallery
	 * 
	 * @return
	 * 		an arraylist with the categories in this gallery
	 */
	public ArrayList<JCpgCategory> getCategories(){
		
		return this.categories;
		
	}
	/**
	 * 
	 * Get a specific album based on its name
	 * 
	 * @param name
	 * 		name of the album you search
	 * @param id
	 * 		if this is not -1, checking will happen using the id, not the name
	 * @return
	 * 		the album if it has been found, else null
	 */
	public JCpgAlbum getAlbum(String name, int id){
		
		for(int i=0; i<getAlbums().size(); i++){
			
			JCpgAlbum album = getAlbums().get(i);
			
			if(id != -1){
				
				if(album.getId() == id){
					
					return album; // category found
					
				}
			
			}else if(album.getName().equals(name)){
				
				return album; // album found
				
			}
			
		}
		
		return null; // nothing found
		
	}
	/**
	 * 
	 * Get a specific category based on its name
	 * 
	 * @param name
	 * 		name of the category you search
	 * @param id
	 * 		if this is not -1, checking will happen using the id, not the name
	 * @return
	 * 		the category if it has been found, else null
	 */
	public JCpgCategory getCategory(String name, int id){
		
		for(int i=0; i<getCategories().size(); i++){
			
			JCpgCategory category = getCategories().get(i);
			
			if(id != -1){
				
				if(category.getId() == id){
					
					return category; // category found
					
				}
				
			}else if(category.getName().equals(name)){
				
				return category; // category found
				
			}
			
		}
		
		return null; // nothing found
		
	}
	/**
	 * 
	 * Get the path of this element in the tree
	 * 
	 * @return
	 * 		the path of this element in the tree
	 */
	public TreePath getTreePath(){
		
		return this.treePath;
		
	}
	/**
	 * 
	 * Check if this component was modified
	 * 
	 * @return
	 * 		true if it was modified, else false
	 */
	public boolean isModified(){
		
		return this.isModified;
		
	}
	
	
	
	
	
	
	
	
	
	
														
														//*************************************
														//				MUTATORS & OTHERS     *
														//*************************************
	/**
	 * 
	 * Add an album to the gallery
	 * 
	 * @param album
	 *		the album to add
	 */
	public void addAlbum(JCpgAlbum album){
		
		getAlbums().add(album);
		
	}
	/**
	 * 
	 * Add a category to the gallery
	 * 
	 * @param category
	 *		the category to add
	 */
	public void addCategory(JCpgCategory category){
		
		getCategories().add(category);
		
	}
	/**
	 * 
	 * Add ui reference to this gallery
	 * 
	 * @param ui
	 * 		ui reference
	 */
	public void addUi(JCpgUI ui){
		
		setUi(ui);
		
	}
	/**
	 * 
	 * Delete an album from the gallery
	 * 
	 * @param album
	 * 		the album to delete
	 */
	public void deleteAlbum(JCpgAlbum album){
		
		getAlbums().remove(album);
		
	}
	/**
	 * 
	 * Delete a category from the gallery
	 * 
	 * @param category
	 * 		the category to delete
	 */
	public void deleteCategory(JCpgCategory category){
		
		getCategories().remove(category);
		
	}
	/**
	 * 
	 * Change the gallery name
	 * 
	 * @param name
	 * 		the new gallery name
	 */
	public void changeName(String name){
		
		setName(name);
		
	}
	/**
	 * 
	 * Change the gallery description
	 * 
	 * @param description
	 * 		the new gallery description
	 */
	public void changeDescription(String description){
		
		setDescription(description);
		
	}
	/**
	 * 
	 * Deletes a gallery. Of course the main gallery can't be deleted, but all the other stuff can. Everything in the tree will be deleted. The JCpgUIReference is needed to get the reference
	 * to the main gallery.
	 * 
	 * @param jCpgUIReference
	 * 		reference to ui
	 */
	public void delete(JCpgUI jCpgUIReference){
		
		for(int i=0; i<getCategories().size(); i++){
    		
			JCpgCategory category = getCategories().get(i);
			category.delete(getUi());
    		
    	}
		
		getCategories().clear();
		
	}
	/**
	 * 
	 * toString override
	 * 
	 */
	public String toString(){
		
		return this.getName();
		
	}
	/**
	 * 
	 * Change the treepath
	 * 
	 * @param treePath
	 * 		the new treepath
	 */
	public void changeTreePath(TreePath treePath){
		
		this.treePath = treePath;
		
	}
	/**
	 * 
	 * Generate delete parameters and add them to the arraylist in the ui
	 *
	 */
	private void generateDeleteParamaters(){
		
		// this gallery can not be deleted
		
	}
	/**
	 * 
	 * Changes the isModified flag to the desired value
	 * 
	 * @param isModified
	 * 		the desired isModified flag
	 */
	public void changeIsModified(boolean isModified){
		
		this.isModified = isModified;
		
	}
	/**
	 * 
	 * Switch the isModified flag
	 *
	 */
	public void switchIsModified(){
		
		if(isModified()){
			
			this.isModified = false;
			
		}else{
			
			this.isModified = true;
			
		}
		
	}
	
}
