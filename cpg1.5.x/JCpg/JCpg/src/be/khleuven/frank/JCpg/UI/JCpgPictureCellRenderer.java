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
public class JCpgPictureCellRenderer extends JLabel implements ListCellRenderer {
	
    public Component getListCellRendererComponent(JList list, Object value, int index, boolean isSelected, boolean cellHasFocus) {
    	
    	setHorizontalAlignment(CENTER);
        setVerticalAlignment(CENTER);
    	
    	JCpgPicture selectedPicture = (JCpgPicture)list.getModel().getElementAt(index);
       
        setIcon(new JCpgImageUrlValidator("albums/" + selectedPicture.getFilePath() + "thumb_" + selectedPicture.getFileName()).createImageIcon());
        
        if (isSelected) {
            setBackground(list.getSelectionBackground());
	        setForeground(list.getSelectionForeground());
	    }
        else {
	       setBackground(list.getBackground());
	       setForeground(list.getForeground());
	    }
        
	    setEnabled(list.isEnabled());
	    setFont(list.getFont());
        setOpaque(true);
        
        return this;
        
    }
    
}
