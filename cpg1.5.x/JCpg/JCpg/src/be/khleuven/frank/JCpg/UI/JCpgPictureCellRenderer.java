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

package be.khleuven.frank.JCpg.UI;

import java.awt.Component;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.Insets;

import javax.swing.JLabel;
import javax.swing.JList;
import javax.swing.JPanel;
import javax.swing.ListCellRenderer;

import be.khleuven.frank.JCpg.JCpgImageUrlValidator;
import be.khleuven.frank.JCpg.Components.JCpgPicture;

/**
 * 
 * Determines how the pictures in the picture list are rendered
 * 
 * @author Frank Cleynen
 *
 */
public class JCpgPictureCellRenderer extends JPanel implements ListCellRenderer {
	
	private JCpgUI ui = null;
	
	private GridBagLayout gbl = new GridBagLayout();
	private GridBagConstraints gblc = new GridBagConstraints();
	
	public JCpgPictureCellRenderer(JCpgUI ui) {
		
		setUi(ui);
		
        setOpaque(false); // transparent
        this.setLayout(gbl); // we will use the grid layout
        this.removeAll();
        
        gblc.fill = GridBagConstraints.HORIZONTAL;
        gblc.insets = new Insets(10,10,10,10);
        //gblc.weighty = 1;
        //gblc.weightx = 1;
    	
	}
	
	
	private void setUi(JCpgUI ui){
		
		this.ui = ui;
		
	}
	
	public JCpgUI getUi(){
		
		return this.ui;
		
	}
	
    public Component getListCellRendererComponent(JList list, Object value, int index, boolean isSelected, boolean cellHasFocus) {
    	
    	JCpgPicture picture = (JCpgPicture)value;
    	JLabel label = new JLabel(new JCpgImageUrlValidator(getUi().getCpgConfig().getSiteConfig().getValueFor("fullpath") + picture.getFilePath() + "thumb_" + picture.getFileName()).createImageIcon());

        // determine grid position
    	gblc.gridx = index;
        //gblc.gridx = index % 10;
        //gblc.gridy = getDeler(index, 10);
    	gblc.gridy = 1;
    	
    	this.add(label, gblc);
        
        return this;
        
    }
    
    private int getDeler(int source, int cutoff){
    	
    	int count = 0;
    	
    	while(source > cutoff){
    		
    		source = source - cutoff;
    		count++;
    		
    	}
    	
    	return count;
    	
    }
    
}