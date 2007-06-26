package be.khleuven.frank.JCpg.Manager;

import java.awt.Dimension;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

import javax.swing.ImageIcon;
import javax.swing.JButton;
import javax.swing.JDialog;
import javax.swing.JFrame;
import javax.swing.JLabel;

import be.khleuven.frank.JCpg.UI.JCpgUI;

public class JCpgConfirmManager extends JDialog {
	
	private JCpgUI jCpgUIReference;
	
	private JLabel logo;
	private JButton ok;
	private JButton cancel;
	private ImageIcon logoIcon;
	
	private Dimension screensize;
	
	
	
	
	
	
	public JCpgConfirmManager(JCpgUI jCpgUIReference, ImageIcon logo){
		
		jCpgUIReference.setEnabled(false);
		setLogo(logo);
		setJCpgUIReference(jCpgUIReference);
		initComponents();
		boundComponents();
		placeComponents();
		
	}
	
	
	
	
	/**
	* 
	* Set the JCpgUI reference
	* 
	* @param jCpgUIReference
	* 		the JCpgUI reference
	*/
	private void setJCpgUIReference(JCpgUI jCpgUIReference){
		
		this.jCpgUIReference = jCpgUIReference;
		
	}
	/**
	 * 
	 * Set the logo
	 * 
	 * @param logo
	 * 		path to the logo
	 */
	private void setLogo(ImageIcon logo){
		
		this.logoIcon = logo;
		
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
		this.setAlwaysOnTop(true);
		
		screensize = Toolkit.getDefaultToolkit().getScreenSize();
		
		logo = new JLabel(getLogoIcon(), JLabel.CENTER); // 500x50
		
		ok = new JButton("Ok");
		cancel = new JButton("Cancel");
		
		ok.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				okActionPerformed(evt);
			}
		});
		
		cancel.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent evt) {
				cancelActionPerformed(evt);
			}
		});
		
	}
	/**
	* 
	* Size swing components
	*
	*/
	private void boundComponents(){
	
		this.setBounds((int)(screensize.getWidth()/2)-250, (int)(screensize.getHeight()/2)-100, 500, 200);
		this.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
		this.setUndecorated(true);
		
		logo.setBounds(0, 0, 500, 50);
		
		ok.setBounds(70, 120, 100, 30);
		cancel.setBounds(70, 160, 100, 30);
	
	}
	/**
	* 
	* Position swing components
	*
	*/
	private void placeComponents(){
		
		this.getContentPane().add(logo);
		this.getContentPane().add(ok);
		this.getContentPane().add(cancel);
		this.setVisible(true);
		
	}
	
	
	
	
	
	
	/**
	* 
	* Get the JCpgUI reference
	* 
	* @return jCpgUIReference
	* 		the JCpgUI reference
	*/
	public JCpgUI getJCpgUIReference(){
		
		return this.jCpgUIReference;
		
	}
	/**
	 * 
	 * Get an ImageIcon object from the logo path
	 * 
	 * @return
	 * 		an ImageIcon object from the logo path
	 */
	public ImageIcon getLogoIcon(){
		
		return this.logoIcon;
		
	}
	/**
	 * 
	 * Get the reference to the create button
	 * 
	 * @return
	 * 		the reference to the create button
	 */
	public JButton getCreateButton(){
		
		return this.ok;
	}
	public JButton getCloseButton(){
		
		return this.cancel;
		
	}
	
	
	
	
	
	
	/**
	* 
	* Perform right actions when create button is clicked
	* 
	*/
	public void okActionPerformed(java.awt.event.ActionEvent evt) {
		
		this.setEnabled(false); // set it to false so we can check from within UI if user pressed OK or CANCEL
		getJCpgUIReference().setEnabled(true);
		
	}
	/**
	 * 
	 * Perform right actions when close button is clicked
	 * 
	 */
	public void cancelActionPerformed(java.awt.event.ActionEvent evt) {
		
		this.dispose();
		getJCpgUIReference().setEnabled(true);
		
	}
	
	

}
