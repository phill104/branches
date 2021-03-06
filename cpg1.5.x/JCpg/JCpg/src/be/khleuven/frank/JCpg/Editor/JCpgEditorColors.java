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
import java.awt.color.ColorSpace;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.image.BufferedImage;
import java.awt.image.ColorConvertOp;
import java.awt.image.RescaleOp;

import javax.swing.JButton;
import javax.swing.JLabel;
import javax.swing.JSlider;
import javax.swing.event.ChangeEvent;
import javax.swing.event.ChangeListener;

import be.khleuven.frank.JCpg.Components.JCpgPicture;
import be.khleuven.frank.JCpg.UI.JCpgUI;


/**
 * 
 * Editor: color correction
 * 
 * @author Frank Cleynen
 *
 */
public class JCpgEditorColors extends JCpgEditor{
	
	
	
																		//*************************************
																		//				VARIABLES             *
																		//*************************************
	private static final long serialVersionUID = 1L;
	
	private JLabel changeColorLabel;
	private JLabel brightnessLabel;
	private JSlider changeColorSlider;
	private JSlider brightnessSlider;
	private int currentBrightnessSliderValue = 0; // this holds the slider's value just before it changes. This is needed to apply the right brightness to the image.
	
	private JButton gray, ciexyz, rgb; 
	
	
	
																		
																		//*************************************
																		//				CONSTRUCTOR           *
																		//*************************************
	/**
	 * 
	 * Makes a new JCpgEditor_colors object
	 * 
	 * @param jCpgUIReference
	 * 		reference to the UI
	 * @param picture
	 * 		picture that needs effect
	 * @param previewPosition
	 * 	 	position of preview JPanel
	 * @param previewSize
	 * 		size of preview JPanel
	 */
	public JCpgEditorColors(JCpgUI jCpgUIReference, JCpgPicture picture, Dimension previewPosition, Dimension previewSize, int listIndex){
		
		super(jCpgUIReference, picture, previewPosition, previewSize, listIndex);
		
		doExtraSwingComponents();
		
	}
	
	
	
	
																		
																		//*************************************
																		//				SWING	              *
																		//*************************************
	/**
	 * 
	 * Do extra swing stuff specifically for the color effects screen
	 *
	 */
	private void doExtraSwingComponents(){
		
		changeColorLabel = new JLabel("Change color: ");
		changeColorLabel.setBounds(610, 60, 150, 20);
		brightnessLabel = new JLabel("Brightness: ");
		brightnessLabel.setBounds(610, 90, 150, 20);
		
		changeColorSlider = new JSlider(0, 255);
		changeColorSlider.setValue(0);
		changeColorSlider.setBounds(770, 60, 150, 20);
		brightnessSlider = new JSlider(-5, 5);
		brightnessSlider.setBounds(770, 90, 150, 20);
		
		gray = new JButton("Gray");
		gray.setBounds(770, 110, 100, 20);
		
		ciexyz = new JButton("CieXYZ");
		ciexyz.setBounds(770, 140, 100, 20);
		
		rgb = new JButton("Rgb");
		rgb.setBounds(770, 170, 100, 20);
		
		changeColorSlider.addChangeListener(new ChangeListener() {
			public void stateChanged(ChangeEvent evt) {
				changeColorSliderValueChanged(evt);
			}
	    });
		
		brightnessSlider.addChangeListener(new ChangeListener() {
			public void stateChanged(ChangeEvent evt) {
				currentBrightnessSliderValue = brightnessSlider.getValue();
				brightnessSliderValueChanged(evt);
			}
	    });
		
		gray.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				grayActionPerformed(evt);
			}
		});
		
		ciexyz.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				ciexyzActionPerformed(evt);
			}
		});
		
		rgb.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				rgbActionPerformed(evt);
			}
		});
		
		this.getContentPane().add(changeColorLabel);
		this.getContentPane().add(brightnessLabel);
		this.getContentPane().add(changeColorSlider);
		this.getContentPane().add(brightnessSlider);
		
		this.getContentPane().add(gray);
		this.getContentPane().add(ciexyz);
		this.getContentPane().add(rgb);
		
	}
	
	
	
	
																		
																		//*************************************
																		//				EVENTS	              *
																		//*************************************
	/**
	 * 
	 * Do the change color effect
	 * 
	 */
	private void changeColorSliderValueChanged(ChangeEvent evt) {
		
		BufferedImage preview = getBufferedPreview(); // read the current preview picture
		
		ColorSpace cs = ColorSpace.getInstance(ColorSpace.CS_CIEXYZ); // do the effect
	    ColorConvertOp op = new ColorConvertOp(cs, null);
	    preview = op.filter(preview, null);
	    
	    previewPicture(preview); // show new result
    	
	}
	/**
	 * 
	 * Do the change brightness effect
	 * 
	 */
	private void brightnessSliderValueChanged(ChangeEvent evt) {
		
		BufferedImage preview = getBufferedPreview(); // read the current preview picture
		
	    RescaleOp op = new RescaleOp(1+brightnessSlider.getValue()-currentBrightnessSliderValue, 0, null);
	    
	    preview = op.filter(preview, null);
	    
	    previewPicture(preview); // show new result
	    
	}
	
	private void grayActionPerformed(java.awt.event.ActionEvent evt) {
		
		BufferedImage preview = getBufferedPreview(); // read the current preview picture
		
		ColorSpace cs = ColorSpace.getInstance(ColorSpace.CS_GRAY); // do the effect
	    ColorConvertOp op = new ColorConvertOp(cs, null);
	    preview = op.filter(preview, null);
	    
	    previewPicture(preview); // show new result
    	
	}
	private void ciexyzActionPerformed(java.awt.event.ActionEvent evt) {
		
		BufferedImage preview = getBufferedPreview(); // read the current preview picture
		
		ColorSpace cs = ColorSpace.getInstance(ColorSpace.CS_CIEXYZ); // do the effect
	    ColorConvertOp op = new ColorConvertOp(cs, null);
	    preview = op.filter(preview, null);
	    
	    previewPicture(preview); // show new result
    	
	}
	private void rgbActionPerformed(java.awt.event.ActionEvent evt) {
		
		BufferedImage preview = getBufferedPreview(); // read the current preview picture
		
		ColorSpace cs = ColorSpace.getInstance(ColorSpace.CS_sRGB); // do the effect
	    ColorConvertOp op = new ColorConvertOp(cs, null);
	    preview = op.filter(preview, null);
	    
	    previewPicture(preview); // show new result
    	
	}

}
