package be.khleuven.frank.JCpg.Sync;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.net.UnknownHostException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

import javax.swing.tree.DefaultMutableTreeNode;
import javax.swing.tree.TreePath;

import be.khleuven.frank.JCpg.Components.JCpgAlbum;
import be.khleuven.frank.JCpg.Components.JCpgCategory;
import be.khleuven.frank.JCpg.Components.JCpgGallery;
import be.khleuven.frank.JCpg.Components.JCpgPicture;
import be.khleuven.frank.JCpg.Manager.JCpgSqlManager;
import be.khleuven.frank.JCpg.Save.JCpgGallerySaver;
import be.khleuven.frank.JCpg.UI.JCpgUI;



/**
 * 
 * Used for syncing the whole gallery. We sync from top to bottom: cats -> albums -> pictures
 * This is important when executing INSERT queries. This means we want to make new entries in the database which don't yet have a correct id, which is autogenerated by the database.
 * So first we insert a new category, we then fetch the generated id and pass it through to the underlying albums and so on.
 * 
 * @author frank
 *
 */
public class JCpgSyncer {
	
	
																			
																			//*************************************
																			//				VARIABLES	          *
																			//*************************************
	private JCpgUI ui;
	private JCpgSqlManager sqlManager;
	
	
	
	
	
																			
	
	
																			//*************************************
																			//				CONSTRUCTOR	          *
																			//*************************************
	/**
	 * 
	 * Makes a new JCpgSyncer object
	 * 
	 * @param ui
	 * 		reference to the ui
	 * @param sqlManager
	 * 		reference to a sql manager
	 */
	public JCpgSyncer(JCpgUI ui, JCpgSqlManager sqlManager){
		
		setUi(ui);
		setSqlManager(sqlManager);
		
	}
	
	
	
	
	
	
	
																			
																			//*************************************
																			//				SETTERS		          *
																			//*************************************
	/**
	 * 
	 * Set the syncer ui
	 * 
	 * @param ui
	 * 		the syncer ui reference
	 */
	private void setUi(JCpgUI ui){
		
		this.ui = ui;
		
	}
	/**
	 * 
	 * Set the syncer sql manager
	 * 
	 * @param sqlManager
	 * 		the syncer sql manager
	 */
	private void setSqlManager(JCpgSqlManager sqlManager){
		
		this.sqlManager = sqlManager;
		
	}
	
	
	
	
	
	
																			
																			//*************************************
																			//				GETTERS		          *
																			//*************************************
	/**
	 * 
	 * Get the syncer Ui reference
	 * 
	 * @return
	 * 		the Ui reference
	 */
	public JCpgUI getUi(){
		
		return this.ui;
		
	}
	/**
	 * 
	 * Get the syncer sql manager
	 * 
	 * @return
	 * 		the syncer sql manager
	 */
	public JCpgSqlManager getSqlManager(){
		
		return this.sqlManager;
		
	}
	
	
	
	
	
																			
																			//*************************************
																			//				MUTATORS & OTHERS     *
																			//*************************************
	/**
	 * 
	 * Perform all the right sync operations. First start with al the delete queries, then go through the tree structure, check if an entry needs to execute his query and act appropriate
	 *
	 */
	public void sync(){
		
		JCpgFTP ftp = null;
		
		int newCategoryId = 0, newAlbumId = 0, newPictureId = 0;
		ResultSet rs_id = null;
		
		// do DELETE queries
		for(int i=0; i < getUi().getGallery().getDeleteQueries().size(); i++){
			
			getSqlManager().sqlExecute(getUi().getGallery().getDeleteQueries().get(i));
			
		}
		getUi().getGallery().getDeleteQueries().clear(); // clear all executed delete queries
		
		// do INSERT AND UPDATE QUERIES
		for(int i=0; i < getUi().getGallery().getCategories().size(); i++){
			
			JCpgCategory category = getUi().getGallery().getCategories().get(i);
			
			if(category.getMustSync()){ // only sync if object has been changed
				
				getSqlManager().sqlExecute(category.getSqlQuery()); // write category to database
				category.changeMustSync(false);
				
				category.generateIdFetchQuery();
				rs_id = getSqlManager().sqlExecute(category.getSqlQuery()); // get correct id for this category
				
				try {
					
					rs_id.next();
					newCategoryId = rs_id.getInt("cid");
					category.changeId(newCategoryId);
					
				} catch (SQLException e) {
					
					System.out.println("JCpgSyncer: couldn't retrieve category updated id.");
					
				}
			
			}
			
			
			for(int j=0; j < category.getAlbums().size(); j++){
				
				JCpgAlbum album = category.getAlbums().get(j);
				
				album.changeCategory(category.getId()); // pass the newly fetched category id to its albums
				album.regenerateSqlQuery(); // make this changes also in the query
				
				if(album.getMustSync()){ // only sync if object has been changed
					
					getSqlManager().sqlExecute(album.getSqlQuery()); // write album to database
					album.changeMustSync(false);
					
					album.generateIdFetchQuery();
					rs_id = getSqlManager().sqlExecute(album.getSqlQuery()); // get correct id for this album
					
					try {
						
						rs_id.next();
						newAlbumId = rs_id.getInt("aid");
						album.changeId(newAlbumId);
						
					} catch (SQLException e) {
						
						System.out.println("JCpgSyncer: couldn't retrieve album updated id.");
						
					}
					
				}
				
				for(int k=0; k < album.getPictures().size(); k++){
					
					JCpgPicture picture = album.getPictures().get(k);
					
					picture.changeAlbumId(album.getId()); // pass the newly fetched album id to its pictures
					picture.regenerateSqlQuery();  // make this changes also in the query
					
					if(picture.getMustSync()){ // only sync if object has been changed
						
						getSqlManager().sqlExecute(picture.getSqlQuery()); // write picture to database
						picture.changeMustSync(false);
						
						picture.generateIdFetchQuery();
						rs_id = getSqlManager().sqlExecute(picture.getSqlQuery()); // get correct id for this picture
						
						try {
							
							rs_id.next();
							newPictureId = rs_id.getInt("pid");
							picture.changeId(newPictureId);
							
							// TEMPORARY: ONLY FOR VGO, FOR CPG USE API
							// UPLOAD NEW PICTURES VIA FTP
							ftp = new JCpgFTP(false);
							try {
								
								ftp.connect("127.0.0.1");
								
								if (ftp.login("Frank Cleynen", "ardella")){
									
									ftp.uploadFile("/users/frank/documents/workspace/jcpg/albums/" + picture.getFilePath() + picture.getFileName(), "../../library/webserver/documents/cpg1410/albums/userpics/10001/" + picture.getFileName());
									ftp.uploadFile("/users/frank/documents/workspace/jcpg/albums/" + picture.getFilePath() + "thumb_" + picture.getFileName(), picture.getFileName());
									ftp.disconnect();
									
								}
								
							} catch (UnknownHostException e) { e.printStackTrace(); } catch (IOException e) { e.printStackTrace(); }
							
							// END TEMPORARY
							
						} catch (SQLException e) {
							
							System.out.println("JCpgSyncer: couldn't retrieve picture updated id.");
							
						}
						
					}
					
				}
				
			}
			
		}
		
		extractDatabase(); // extract from database the components that are not yet in the current state
		
		new JCpgGallerySaver().saveGallery(getUi().getGallery()); // save gallery
		getUi().getGallery().toXML();
		
	}
	/**
	 * 
	 * Load the current saved gallery from disk
	 *
	 */
	public void loadGallery(){
		
		File gallery = new File("current.dat");
		
		if(gallery.exists()){ // only load if the file exists
		
			try {
				
				FileInputStream fistream;
				
				fistream = new FileInputStream(gallery);
				ObjectInputStream oistream = new ObjectInputStream(fistream);
		
				JCpgGallery loadedGallery = (JCpgGallery)oistream.readObject();
				
				getUi().changeGallery(loadedGallery); // overwrite current gallery
			
				oistream.close();
			
			} catch (Exception e) {
				
				System.out.println("JCpgUI: couldn't load current.dat");
				
			}
			
		}
		
	}
	/**
	 * 
	 * Extract a tree structure from the database: cat -> albums -> pics
	 * The program will always load a state from harddisk. This method will then update this state so is resembles the online state.
	 * 
	 * In each step we look whats on the server. If we find the same component locally, there's no problem. If it isn't found, the new component is added to the local state.
	 *
	 */
	public void extractDatabase(){
		
		
		// FIRST CHECK SERVER ITEM, THEN LOOK LOCALLY
		String table_categories = getUi().getUserConfig().getServerConfig().getPrefix() + "_categories";
		String table_albums = getUi().getUserConfig().getServerConfig().getPrefix() + "_albums";
		String table_pictures = getUi().getUserConfig().getServerConfig().getPrefix() + "_pictures";
		
		ResultSet rs_categories = getSqlManager().sqlExecute("SELECT * FROM " + table_categories);
		
		try {
			
			while (rs_categories.next()) {
				
				JCpgCategory category;
				int catid;
				DefaultMutableTreeNode treeCategory = null;
				
				if(getUi().getGallery().getCategory(rs_categories.getString("name")) == null && !rs_categories.getString("name").equals("User galleries")){ // no such category found => make new one from database
				
					category = new JCpgCategory(getUi().getUserConfig(), rs_categories.getInt("cid"), rs_categories.getInt("owner_id"), rs_categories.getString("name"), rs_categories.getString("description"), rs_categories.getInt("pos"), rs_categories.getInt("parent"), rs_categories.getInt("thumb"));
					
					getUi().getGallery().addCategory(category);
					treeCategory = new DefaultMutableTreeNode(category);
					((DefaultMutableTreeNode)getUi().getTree().getModel().getRoot()).add(treeCategory);
						
					category.changeTreePath(new TreePath(treeCategory.getPath())); // add treePath
					
					catid = rs_categories.getInt("cid");
				
				}else{
					
					category = getUi().getGallery().getCategory(rs_categories.getString("name")); // category already exists locally so use this on
					catid = category.getId();
					
					treeCategory = getUi().visitAllNodes((DefaultMutableTreeNode)getUi().getTree().getModel().getRoot(), "category", category.getName());
					
				}
				
				ResultSet rs_albums = getSqlManager().sqlExecute("SELECT * FROM " + table_albums + " WHERE category = " + catid);
			
				while (rs_albums.next()) {
					
					JCpgAlbum album;
					int albumid;
					DefaultMutableTreeNode treeAlbum = null;
					
					if(category.getAlbum(rs_albums.getString("title")) == null){ // no such album found => make new one from database
						
						album = new JCpgAlbum(getUi().getUserConfig(), rs_albums.getInt("aid"), rs_albums.getString("title"), rs_albums.getString("description"), rs_albums.getInt("visibility"), rs_albums.getBoolean("uploads"), rs_albums.getBoolean("comments"), rs_albums.getBoolean("votes"), rs_albums.getInt("pos"), rs_albums.getInt("category"), rs_albums.getInt("thumb"), rs_albums.getString("keyword"), rs_albums.getString("alb_password"), rs_albums.getString("alb_password_hint"));
						
						category.addAlbum(album);
						treeAlbum = new DefaultMutableTreeNode(album);
						treeCategory.add(treeAlbum);
						
						album.changeTreePath(new TreePath(treeAlbum.getPath())); // add treePath
						
						albumid = rs_albums.getInt("aid");
						
					}else{
						
						album = category.getAlbum(rs_albums.getString("title")); // album already exists locally so use this on
						albumid = album.getId();
						
						treeAlbum = getUi().visitAllNodes((DefaultMutableTreeNode)getUi().getTree().getModel().getRoot(), "album", album.getName());
						
					}
					
					ResultSet rs_pictures = getSqlManager().sqlExecute("SELECT * FROM " + table_pictures + " WHERE aid = " + albumid);
					
					while(rs_pictures.next()){
						
						JCpgPicture picture;
						DefaultMutableTreeNode treePicture;
						
						if(album.getPicture(rs_pictures.getString("filename")) == null){ // no such picture found => make new one from database
						
							picture = new JCpgPicture(getUi().getUserConfig(), rs_pictures.getInt("pid"), rs_pictures.getInt("aid"), rs_pictures.getString("filepath"), rs_pictures.getString("filename"), rs_pictures.getInt("filesize"), rs_pictures.getInt("total_filesize"), rs_pictures.getInt("pwidth"), rs_pictures.getInt("pheight"), rs_pictures.getInt("hits"), rs_pictures.getInt("ctime") ,rs_pictures.getInt("owner_id"), rs_pictures.getString("owner_name"), rs_pictures.getInt("pic_rating"), rs_pictures.getInt("votes"), rs_pictures.getString("title"), rs_pictures.getString("caption"), rs_pictures.getString("keywords"), rs_pictures.getBoolean("approved"), rs_pictures.getInt("galleryicon"), rs_pictures.getInt("url_prefix"), rs_pictures.getInt("position"));
							
							album.addPicture(picture);
							treePicture = new DefaultMutableTreeNode(picture);
							treeAlbum.add(treePicture);
	
							picture.changeTreePath(new TreePath(treePicture.getPath())); // add treePath
							
							new JCpgPictureTransferer(getSqlManager(), getUi().getUserConfig().getServerConfig(), getUi().getCpgConfig(), picture).downloadImage();
							JCpgPicture thumb = new JCpgPicture(getUi().getUserConfig(), rs_pictures.getInt("pid"), rs_pictures.getInt("aid"), rs_pictures.getString("filepath"), "thumb_" + rs_pictures.getString("filename"), rs_pictures.getInt("filesize"), rs_pictures.getInt("total_filesize"), rs_pictures.getInt("pwidth"), rs_pictures.getInt("pheight"), rs_pictures.getInt("hits"), rs_pictures.getInt("ctime"), rs_pictures.getInt("owner_id"), rs_pictures.getString("owner_name"), rs_pictures.getInt("pic_rating"), rs_pictures.getInt("votes"), rs_pictures.getString("title"), rs_pictures.getString("caption"), rs_pictures.getString("keywords"), rs_pictures.getBoolean("approved"), rs_pictures.getInt("galleryicon"), rs_pictures.getInt("url_prefix"), rs_pictures.getInt("position"));
							new JCpgPictureTransferer(getSqlManager(), getUi().getUserConfig().getServerConfig(), getUi().getCpgConfig(), thumb).downloadImage();
						
						}
							
					}

				}
				
				new JCpgGallerySaver().saveGallery(getUi().getGallery()); // save gallery
				getUi().getGallery().toXML();
			}
				
		} catch (SQLException e) {
			
			System.out.println("JCpgInterface: Couldn't extract from sql query result: SERVER -> LOCAL");
			
		}
		
		// FIRST CHECK LOCAL ITEM, THEN LOOK ON SERVER
		// If locally, the component has an id different from -1, it means it has been uploaded to the server. If we can't find the same component with the same id on the server,
		// it means it has been deleted and we also have to delete it locally.		
		ResultSet result = null;
		try {
			
			for(int i=0; i<getUi().getGallery().getCategories().size(); i++){
				
				JCpgCategory category = getUi().getGallery().getCategories().get(i);
				
				result = getSqlManager().sqlExecute("SELECT * FROM " + table_categories + " WHERE cid = " + category.getId());
				if(category.getId() != -1 && !result.next()){ // category on server as seen locally and not found in database -> deleted online
					System.out.println(category.getName());
					DefaultMutableTreeNode match = getUi().visitAllNodes((DefaultMutableTreeNode)getUi().getTree().getModel().getRoot(), "category", category.getName()); // search for this category in the tree
					match.removeFromParent(); // delete it from its parent
					category.delete(getUi()); // delete category and all components it contains
					
				}else{ // category still exists on server, check its albums
					
					for(int j=0; j<category.getAlbums().size(); j++){
						
						JCpgAlbum album = category.getAlbums().get(j);
						
						result = getSqlManager().sqlExecute("SELECT * FROM " + table_albums + " WHERE category = " + category.getId());
						if(album.getId() != -1 && !result.next()){
							
							DefaultMutableTreeNode match = getUi().visitAllNodes((DefaultMutableTreeNode)getUi().getTree().getModel().getRoot(), "album", album.getName()); // search for this album in the tree
							match.removeFromParent(); // delete it from its parent
							album.delete(getUi()); // delete album and all components it contains
							
						}else{ // album still exists on server, check its pictures
							
							for(int k=0; k<album.getPictures().size(); k++){
								
								JCpgPicture picture = album.getPictures().get(k);
								
								result = getSqlManager().sqlExecute("SELECT * FROM " + table_pictures + " WHERE aid = " + album.getId());
								if(picture.getId() != -1 && !result.next()){
									
									DefaultMutableTreeNode match = getUi().visitAllNodes((DefaultMutableTreeNode)getUi().getTree().getModel().getRoot(), "picture", album.getName()); // search for this album in the tree
									match.removeFromParent(); // delete it from its parent
									picture.delete(getUi()); // delete picture
									
								}
								
							}
							
						}
						
					}
					
				}
				
				new JCpgGallerySaver().saveGallery(getUi().getGallery()); // save gallery
				getUi().getGallery().toXML();
				
			}
				
		} catch (Exception e) {
			
			System.out.println("JCpgInterface: Couldn't extract from sql query result: LOCAL -> SERVER");
			
		}
		
	}

}
