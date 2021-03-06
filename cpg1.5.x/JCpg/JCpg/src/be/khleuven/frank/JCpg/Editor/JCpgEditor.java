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

package be.khleuven.frank.JCpg.Editor;

import java.awt.Dimension;
import java.awt.FlowLayout;
import java.awt.Image;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;

import javax.imageio.ImageIO;
import javax.swing.ImageIcon;
import javax.swing.JButton;
import javax.swing.JDialog;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.SwingUtilities;
import javax.swing.border.EtchedBorder;

import be.khleuven.frank.JCpg.JCpgImageUrlValidator;
import be.khleuven.frank.JCpg.Components.JCpgPicture;
import be.khleuven.frank.JCpg.Interfaces.JCpgMyEditorInterface;
import be.khleuven.frank.JCpg.Resize.JCpgPictureResizer;
import be.khleuven.frank.JCpg.UI.JCpgUI;


/**
 * This class is used to do the basic photo editing. This is a parent class for all windows providing their editing. This class basicly will make a std window with and apply and close button. At the moment, we JDialog itself has a size of 1000x700: there's a logo (1000x50) and below 50 pixels is needed for the apply and close button. So de width of the preview panel can max be 1000 pixels, the height has a maximum of 600 pixels.
 * @author    Frank Cleynen
 */
public abstract class JCpgEditor extends JDialog implements JCpgMyEditorInterface{
	
	
	
	
																						
																						//*************************************
																						//				VARIABLES             *
																						//*************************************
	private JCpgUI jCpgUIReference;
	private JCpgPicture picture;
	
	private Dimension screensize;
	private Dimension previewPosition;
	private Dimension previewSize;
	
	private JLabel logo;
	private JButton apply;
	private JButton close;
	private JScrollPane previewscroll;
	private JPanel preview;
	
	private BufferedImage previewBuffered;
	
	private JLabel image = new JLabel();
	
	private JCpgTransform transformer = new JCpgTransform();
	
	private int listIndex;
	
	
																						
	
	
																						//*************************************
																						//				CONSTRUCTOR           *
																						//*************************************
	/**
	 * 
	 * Makes a new JCpgEditor object
	 * 
	 * @param jCpgUIReference
	 * 		reference of the UI
	 * @param picture
	 * 		picture that needs editing
	 * @param previewPosition
	 * 		position of the preview JPanel
	 * @param previewSize
	 * 		size of the preview JPanel
	 */
	public JCpgEditor(JCpgUI jCpgUIReference, JCpgPicture picture, Dimension previewPosition, Dimension previewSize, int listIndex){
		
		super(jCpgUIReference);
		
		jCpgUIReference.setEnabled(false);
		
		setJCpgUIReference(jCpgUIReference);
		setPicture(picture);
		setPreviewPosition(previewPosition);
		setPreviewSize(previewSize);
		setListIndex(listIndex);
		
		initComponents();
		boundComponents();
		placeComponents();
		
		setBufferedPreview(new JCpgImageUrlValidator(getJCpgUI().getCpgConfig().getSiteConfig().getValueFor("fullpath") + picture.getFilePath() + picture.getFileName()).createImageIcon());
		previewPicture(getBufferedPreview());
		
	}
	
	
	
	
	
	
	
																						
																						//*************************************
																						//				SETTERS	              *
																						//*************************************
	/**
	 * 
	 * Set the editor UI reference
	 * 
	 * @param jCpgUIReference
	 * 		the editor UI reference
	 */
	private void setJCpgUIReference(JCpgUI jCpgUIReference){
		
		this.jCpgUIReference = jCpgUIReference;
		
	}
	/**
	 * 
	 * Set the editor picture to be edited
	 * 
	 * @param picture
	 * 		the editor picture to be edited
	 */
	private void setPicture(JCpgPicture picture){
		
		this.picture = picture;
		
	}
	/**
	 * 
	 * Set the position of the preview JPanel
	 * 
	 * @param previewPosition
	 * 		the position of the preview JPanel
	 */
	private void setPreviewPosition(Dimension previewPosition){
		
		this.previewPosition = previewPosition;
		
	}
	/**
	 * 
	 * Set the size of the previesw JPanel
	 * 
	 * @param previewSize
	 * 		the size of the previesw JPanel
	 */
	private void setPreviewSize(Dimension previewSize){
		
		this.previewSize = previewSize;
		
	}
	/**
	 * 
	 * Set the buffered preview image
	 * 
	 * @param picture
	 * 		ImageIcon that will be converted to the buffered previes image
	 */
	private void setBufferedPreview(ImageIcon picture){
		
		this.previewBuffered = JCpgTransform.toBufferedImage(picture.getImage());
		
	}
	/**
	 * 
	 * Set the list index (the index of this picture in the picture list)
	 * 
	 * @param listIndex
	 * 		the list index
	 */
	private void setListIndex(int listIndex){
		
		this.listIndex = listIndex;
		
	}
	
	

	
	
	
	
	
																						
																						//*************************************
																						//				GETTERS	              *
																						//*************************************
	/**
	 * 
	 * Get the reference to the UI
	 * 
	 * @return
	 * 		the reference to the UI
	 */
	public JCpgUI getJCpgUI(){
		
		return this.jCpgUIReference;
		
	}
	/**
	 * 
	 * Get the picture that needs editing
	 * 
	 * @return
	 * 		the picture that needs editing	
	 */
	public JCpgPicture getPicture(){
		
		return this.picture;
		
	}
	/**
	 * 
	 * Get the position of the preview JPanel
	 * 
	 * @return
	 * 		the position of the preview JPanel
	 */
	public Dimension getPreviewPosition(){
		
		return this.previewPosition;
		
	}
	/**
	 * 
	 * Get the size of the preview JPanel
	 * 
	 * @return
	 * 		the size of the preview JPanel
	 */
	public Dimension getPreviewSize(){
		
		return this.previewSize;
		
	}
	/**
	 * 
	 * Get the preview JPanel
	 * 
	 * @return
	 * 		the preview JPanel
	 */
	public JPanel getPreview(){
		
		return this.preview;
		
	}
	/**
	 * 
	 * Get the preview scrollpane
	 * 
	 * @return
	 * 		the preview scrollpane
	 */
	public JScrollPane getPreviewscroll(){
		
		return this.previewscroll;
		
	}
	/**
	 * 
	 * Get a BufferedImage from the previewed image
	 * 
	 * @return
	 * 		a BufferedImage from the previewed image
	 */
	public BufferedImage getBufferedPreview(){
		
		return this.previewBuffered;
		
	}
	/**
	 * 
	 * Get a reference to the transformer
	 * 
	 * @return
	 * 		a reference to the transformer
	 */
	public JCpgTransform getTransformer(){
		
		return this.transformer;
		
	}
	/**
	 * 
	 * Get a reference to the JLabel which holds the preview image
	 * 
	 * @return
	 * 		a reference to the JLabel which holds the preview image
	 */
	public JLabel getImageLabel(){
		
		return this.image;
		
	}
	/**
	 * 
	 * Get the list index
	 * 
	 * @return
	 * 		the list index
	 */
	public int getListIndex(){
		
		return this.listIndex;
		
	}
	
	
	
	
	
	
	
	
	
	
	
																						
																						//*************************************
																						//				SWING	              *
																						//*************************************
	/**
	 * 
	 * Init swing components
	 *
	 */
	private void initComponents(){
		
		this.setLayout(null);
		
		screensize = Toolkit.getDefaultToolkit().getScreenSize();
		
		logo = new JLabel(new JCpgImageUrlValidator("data/editphoto.jpg").createImageIcon(), JLabel.CENTER); // 1000x50
		
		preview = new JPanel();
		preview.setBorder(new EtchedBorder());
		preview.setLayout(new FlowLayout());
		preview.setOpaque(false);
		
		previewscroll = new JScrollPane(preview);
		
		image.setBorder(new EtchedBorder());
		
		apply = new JButton("Apply");
		close = new JButton("Close");
		
		apply.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				applyActionPerformed(evt);
			}
		});
		
		close.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				closeActionPerformed(evt);
			}
		});
		
	}
	/**
	 * 
	 * Position components
	 *
	 */
	private void boundComponents(){
		
		this.setBounds((int)(screensize.getWidth()/2)-500, (int)(screensize.getHeight()/2)-350, 1000, 700);
		this.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
		this.setUndecorated(true);
		
		logo.setBounds(0, 0, 1000, 50);
		
		previewscroll.setBounds(getPreviewPosition().width, getPreviewPosition().height, getPreviewSize().width, getPreviewSize().height);
		
		apply.setBounds(10, 660, 100, 30);
		close.setBounds(120, 660, 100, 30);
	
	}
	/**
	 * 
	 * Add components
	 *
	 */
	private void placeComponents(){
		
		this.getContentPane().add(logo);
		this.getContentPane().add(previewscroll);
		this.getContentPane().add(apply);
		this.getContentPane().add(close);
		this.setVisible(true);
		
	}
	
	
	
	
																						
																						//*************************************
																						//				EVENTS	              *
																						//*************************************
	private void applyActionPerformed(java.awt.event.ActionEvent evt) {
		
		performEdit();
    	
	}
	private void closeActionPerformed(java.awt.event.ActionEvent evt) {
		
		getJCpgUI().setEnabled(true);
		this.dispose();
    	
	}
	
	
	
	
	
	

 
    /**
     * 
     * Show the picture in the preview JPanel
     * 
     * @param picture
     * 		the picture to preview in the JPanel
     */
    protected void previewPicture(BufferedImage picture){
    	
    	Image imageFromBuffered = transformer.toImage(picture);
    	
    	ImageIcon imageIcon = new ImageIcon(imageFromBuffered);
    	
    	setBufferedPreview(imageIcon); // update buffered image
    	
    	Dimension realSize = new Dimension(picture.getWidth(), picture.getHeight());
    	getImageLabel().setPreferredSize(realSize);
    	getImageLabel().setIcon(imageIcon);
    	
    	getPreview().removeAll();
    	getPreview().add(getImageLabel());
    	
    	SwingUtilities.updateComponentTreeUI(preview); // workaround for Java bug 4173369
    	
    }
    
    
    
    
    



																					
																						//*************************************
																						//				INTERFACE IMPL        *
																						//*************************************
	/**
	 * 
	 * Perform the right actions when the user presses the 'Apply' button. Save the current buffered image to the right file
	 * 
	 */																					
	public ImageIcon performEdit() {
		
		try {
			
            ImageIO.write(getBufferedPreview(), getPicture().getFileName().substring(getPicture().getFileName().length()-3, getPicture().getFileName().length()), new File(getJCpgUI().getCpgConfig().getSiteConfig().getValueFor("fullpath") + getPicture().getFilePath() + getPicture().getFileName()));
            JCpgPictureResizer thumb = new JCpgPictureResizer(getJCpgUI(), getJCpgUI().getCpgConfig().getSiteConfig().getValueFor("fullpath") + getPicture().getFilePath(), getPicture().getFileName()); // thumb
			thumb.makeThumb();
			
			//getJCpgUI().getPictureList().remove(getListIndex()); // do correct refresh
			//getJCpgUI().getPictureListModel().add(getListIndex(), getPicture());
			
			getJCpgUI().changeMegaExplorerActive();
			getJCpgUI().changeMegaExplorerActive();
			
            getJCpgUI().setEnabled(true);
            this.dispose();
            
        } catch(IOException e){
        	
            System.out.println("JCpgEditor: Couldn't save edited photo");
            
        }
        
        return null;
		
	}

}
