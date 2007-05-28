package be.khleuven.frank.JCpg.Sync;

/* <!-- in case someone opens this in a browser... --> <pre> */
/**
 * File:   FTPConnection.java
 * Author: Bret Taylor <btaylor@cs.stanford.edu>
 * URL: http://www.stanford.edu/~bstaylor/cs/ftpconnection/FTPConnection.java.shtml
 * 
 * see also <a href="http://www.nsftools.com/tips/JavaFtp.htm">http://www.nsftools.com/tips/JavaFtp.htm</a>
 * ---------------------------------------------
 * $Id$
 * ---------------------------------------------
 * Parts of this code were adopted from a variety of other FTP classes the
 * author has encountered that he was not completely satisfied with.  If you
 * think more thanks are due to any particular author than is given, please
 * let him know.  With that caveat, this class can be freely distributed and
 * modified as long as Bret Taylor is given credit in the source code comments.
 * 
 * 
 * Modified by Julian Robichaux -- http://www.nsftools.com 
 * Added constructors, logout(), listFiles(), listSubdirectories(), getAndParseDirList(),
 * processFileListCommand(), and overloaded getFullServerReply(). 
 * Also added StringBuffer parameter options to transferData() and executeDataCommand()
 * and did a few other little things.
 */

import java.io.*;
import java.net.*;
import java.util.*;

/**
 * <p>A wrapper for the network and command protocols needed for the most common
 * FTP commands.  Standard usage looks something like this:</p>
 * <pre> FTPConnection connection = new FTPConnection();
 * try {
 *     if (connection.connect(host)) {
 *         if (connection.login(username, password)) {
 *             connection.downloadFile(serverFileName);
 *             connection.uploadFile(localFileName);
 *         }
 *         connection.disconnect();
 *     }
 * } catch (UnknownHostException e) {
 *     // handle unknown host
 * } catch (IOException e) {
 *     // handle I/O exception
 * }</pre>
 * <p>Most FTP commands are wrapped by easy-to-use methods, but in case clients
 * need more flexibility, you can execute commands directly using the methods
 * <a href="#executeCommand(java.lang.String)">executeCommand</a> and
 * <a href="#executeDataCommand(java.lang.String,
 * java.io.OutputStream)">executeDataCommand</a>,
 * the latter of which is used for commands that require an open data port.</p>
 * 
 * @author Bret Taylor
 * @author Julian Robichaux
 * @version 1.01
 */
public class JCpgFTP extends Object {

    /**
     * If this flag is on, we print out debugging information to stdout during
     * execution.  Useful for debugging the FTP class and seeing the server's
     * responses directly.
     */
    private static boolean PRINT_DEBUG_INFO = false;
    
    /**
     * The socket through which we are connected to the FTP server.
     */
    private Socket connectionSocket = null;
    /**
     * The socket output stream.
     */
    private PrintStream outputStream = null;
    /**
     * The socket input stream.
     */
    private BufferedReader inputStream = null;

    /**
     * The offset at which we resume a file transfer.
     */
    private long restartPoint = 0L;

    /**
     * Added by Julian: If this flag is on, we're currently logged in to something.
     */
    private boolean loggedIn = false;
    
    /**
     * Added by Julian: This is the line terminator to use for multi-line responses.
     */
    public String lineTerm = "\n";
    
    /**
     * Added by Julian: This is the size of the data blocks we use for transferring
     * files.
     */
    private static int BLOCK_SIZE = 4096;
    

    /**
     * Added by Julian: After you create an FTPConnection object, you will call the
     * connect() and login() methods to access your server. Please don't forget to
     * logout() and disconnect() when you're done (it's only polite...).
     */
    public JCpgFTP ()
    {
    	// default constructor (obviously) -- this is just good to have...
    }
    

    /**
     * Added by Julian: Allows you to specify if you want to send debug output to
     * the console (true if you do, false if you don't).
     */
    public JCpgFTP (boolean debugOut)
    {
    	PRINT_DEBUG_INFO = debugOut;
    }
    

    /**
     * Prints debugging information to stdout if the private flag
     * <code>PRINT_DEBUG_INFO</code> is turned on.
     */
    private void debugPrint(String message) {
        if (PRINT_DEBUG_INFO) System.err.println(message);
    }


    /**
     * Connects to the given FTP host on port 21, the default FTP port.
     */
    public boolean connect(String host)
        throws UnknownHostException, IOException
    {
        return connect(host, 21);
    }


    /**
     * Connects to the given FTP host on the given port.
     */
    public boolean connect(String host, int port)
        throws UnknownHostException, IOException
    {
        connectionSocket = new Socket(host, port);
        outputStream = new PrintStream(connectionSocket.getOutputStream());
        inputStream = new BufferedReader(new
                       InputStreamReader(connectionSocket.getInputStream()));

        if (!isPositiveCompleteResponse(getServerReply())){
            disconnect();
            return false;
        }

        return true;
    }


    /**
     * Disconnects from the host to which we are currently connected.
     */
    public void disconnect()
    {
        if (outputStream != null) {
            try {
        		if (loggedIn) { logout(); };
                outputStream.close();
                inputStream.close();
                connectionSocket.close();
            } catch (IOException e) {}

            outputStream = null;
            inputStream = null;
            connectionSocket = null;
        }
    }


    /**
     * Wrapper for the commands <code>user [username]</code> and <code>pass
     * [password]</code>.
     */
    public boolean login(String username, String password)
        throws IOException
    {
        int response = executeCommand("user " + username);
        if (!isPositiveIntermediateResponse(response)) return false;
        response = executeCommand("pass " + password);
        loggedIn = isPositiveCompleteResponse(response);
        return loggedIn;
    }


    /**
     * Added by Julian: Logout before you disconnect (this is good form).
     */
    public boolean logout()
        throws IOException
    {
        int response = executeCommand("quit");
        loggedIn = !isPositiveCompleteResponse(response);
        return !loggedIn;
    }


    /**
     * Wrapper for the command <code>cwd [directory]</code>.
     */
    public boolean changeDirectory(String directory)
        throws IOException
    {
        int response = executeCommand("cwd " + directory);
        return isPositiveCompleteResponse(response);
    }


    /**
     * Wrapper for the commands <code>rnfr [oldName]</code> and <code>rnto
     * [newName]</code>.
     */
    public boolean renameFile(String oldName, String newName)
        throws IOException
    {
        int response = executeCommand("rnfr " + oldName);
        if (!isPositiveIntermediateResponse(response)) return false;
        response = executeCommand("rnto " + newName);
        return isPositiveCompleteResponse(response);
    }
 

    /**
     * Wrapper for the command <code>mkd [directory]</code>.
     */
    public boolean makeDirectory(String directory)
        throws IOException
    {
        int response = executeCommand("mkd " + directory);
        return isPositiveCompleteResponse(response);
    }
 

    /**
     * Wrapper for the command <code>rmd [directory]</code>.
     */
    public boolean removeDirectory(String directory)
        throws IOException
    {
        int response = executeCommand("rmd " + directory);
        return isPositiveCompleteResponse(response);
    }
 

    /**
     * Wrapper for the command <code>cdup</code>.
     */
    public boolean parentDirectory()
        throws IOException
    {
        int response = executeCommand("cdup");
        return isPositiveCompleteResponse(response);
    }
 

    /**
     * Wrapper for the command <code>dele [fileName]</code>.
     */
    public boolean deleteFile(String fileName)
        throws IOException
    {
        int response = executeCommand("dele " + fileName);
        return isPositiveCompleteResponse(response);
    }
 

    /**
     * Wrapper for the command <code>pwd</code>.
     */
    public String getCurrentDirectory()
        throws IOException
    {
        String response = getExecutionResponse("pwd");
        StringTokenizer strtok = new StringTokenizer(response);

        // Get rid of the first token, which is the return code
        if (strtok.countTokens() < 2) return null;
        strtok.nextToken();
        String directoryName = strtok.nextToken();

        // Most servers surround the directory name with quotation marks
        int strlen = directoryName.length();
        if (strlen == 0) return null;
        if (directoryName.charAt(0) == '\"') {
            directoryName = directoryName.substring(1);
            strlen--;
        }
        if (directoryName.charAt(strlen - 1) == '\"')
            return directoryName.substring(0, strlen - 1);
        return directoryName;
    }
 

    /**
     * Wrapper for the command <code>syst</code>.
     */
    public String getSystemType()
        throws IOException
    {
        return excludeCode(getExecutionResponse("syst"));
    }
 

    /**
     * Wrapper for the command <code>mdtm [fileName]</code>.  If the file does
     * not exist, we return -1;
     */
    public long getModificationTime(String fileName)
        throws IOException
    {
        String response = excludeCode(getExecutionResponse("mdtm " + fileName));
        try {
            return Long.parseLong(response);
        } catch (Exception e) {
            return -1L;
        }
    }
 

    /**
     * Wrapper for the command <code>size [fileName]</code>.  If the file does
     * not exist, we return -1;
     */
    public long getFileSize(String fileName)
        throws IOException
    {
        String response = excludeCode(getExecutionResponse("size " + fileName));
        try {
            return Long.parseLong(response);
        } catch (Exception e) {
            return -1L;
        }
    }


    /**
     * Wrapper for the command <code>retr [fileName]</code>.
     */
    public boolean downloadFile(String fileName)
        throws IOException
    {
        return readDataToFile("retr " + fileName, fileName);
    }


    /**
     * Wrapper for the command <code>retr [serverPath]</code>. The local file
     * path to which we will write is given by <code>localPath</code>. 
     */
    public boolean downloadFile(String serverPath, String localPath)
        throws IOException
    {
        return readDataToFile("retr " + serverPath, localPath);
    }


    /**
     * Wrapper for the command <code>stor [fileName]</code>.
     */
    public boolean uploadFile(String source, String destination)
        throws IOException
    {
        return writeDataFromFile("stor " + source, destination);
    }


    /**
     * Wrapper for the command <code>stor [localPath]</code>. The server file
     * path to which we will write is given by <code>serverPath</code>. 
     */
    /*
    public boolean uploadFile(String serverPath, String localPath)
        throws IOException
    {
        return writeDataFromFile("stor " + serverPath, localPath);
    }
    */


    /**
     * Set the restart point for the next download or upload operation.  This
     * lets clients resume interrupted uploads or downloads.
     */
    public void setRestartPoint(int point)
    {
        restartPoint = point;
        debugPrint("Restart noted");
    }


    /** 
     * Gets server reply code from the control port after an ftp command has
     * been executed.  It knows the last line of the response because it begins
     * with a 3 digit number and a space, (a dash instead of a space would be a
     * continuation).
     */
    private int getServerReply()
        throws IOException
    {
        return Integer.parseInt(getFullServerReply().substring(0, 3));
    }


    /** 
     * Gets server reply string from the control port after an ftp command has
     * been executed.  This consists only of the last line of the response,
     * and only the part after the response code.
     */
    private String getFullServerReply()
        throws IOException
    {
        String reply;

        do {
            reply = inputStream.readLine();
            debugPrint(reply);
        } while(!(Character.isDigit(reply.charAt(0)) && 
                  Character.isDigit(reply.charAt(1)) &&
                  Character.isDigit(reply.charAt(2)) &&
                  reply.charAt(3) == ' '));

        return reply;
    }
    

    /**
     * Added by Julian: Returns the last line of the server reply, but also
     * returns the full multi-line reply in a StringBuffer parameter.
     */
    private String getFullServerReply(StringBuffer fullReply)
    	throws IOException
    {
        String reply;
        fullReply.setLength(0);

        do {
            reply = inputStream.readLine();
            debugPrint(reply);
            fullReply.append(reply + lineTerm);
        } while(!(Character.isDigit(reply.charAt(0)) && 
                  Character.isDigit(reply.charAt(1)) &&
                  Character.isDigit(reply.charAt(2)) &&
                  reply.charAt(3) == ' '));
		
		// remove any trailing line terminators from the fullReply
		if (fullReply.length() > 0)  
		{  
			fullReply.setLength(fullReply.length() - lineTerm.length());
		}
		
        return reply;
    }


    /** 
     * Added by Julian: Gets a list of files in the current directory.
     */
	public String listFiles()
		throws IOException
	{
		return listFiles("");
	}
	

    /** 
     * Added by Julian: Gets a list of files in either the current
     * directory, or one specified as a parameter. The 'params' parameter
     * can be either a directory name, a file mask, or both (such as
     * '/DirName/*.txt').
     */
	public String listFiles(String params)
		throws IOException
	{
		StringBuffer files = new StringBuffer();
		StringBuffer dirs = new StringBuffer();
		if (!getAndParseDirList(params, files, dirs))
		{
			debugPrint("Error getting file list");
		}
		
		return files.toString();
	}
	

    /** 
     * Added by Julian: Gets a list of subdirectories in the current directory.
     */
	public String listSubdirectories()
		throws IOException
	{
		return listSubdirectories("");
	}
	

    /** 
     * Added by Julian: Gets a list of subdirectories in either the current
     * directory, or one specified as a parameter. The 'params' parameter
     * can be either a directory name, a name mask, or both (such as
     * '/DirName/Sub*').
     */
	public String listSubdirectories(String params)
		throws IOException
	{
		StringBuffer files = new StringBuffer();
		StringBuffer dirs = new StringBuffer();
		if (!getAndParseDirList(params, files, dirs))
		{
			debugPrint("Error getting dir list");
		}
		
		return dirs.toString();
	}
	

    /** 
     * Added by Julian: Sends and gets the results of a file list command,
     * like LIST or NLST.
     */
    private String processFileListCommand(String command)
        throws IOException
    {
        StringBuffer reply = new StringBuffer();
        String replyString;
        
        // file listings require you to issue a PORT command, 
        // like a file transfer
		boolean success = executeDataCommand(command, reply);
		if (!success)
		{
			return "";
		}
		
        replyString = reply.toString();
        // strip the trailing line terminator from the reply
        if (reply.length() > 0)
        {
        	return replyString.substring(0, reply.length() - 1);
        }  else  {
        	return replyString;
        }
    }


	/**
	 * Added by Julian: Gets a directory list from the server and parses
	 * the elements into a list of files and a list of subdirectories.
	 */
	private boolean getAndParseDirList(String params, StringBuffer files, StringBuffer dirs)
		throws IOException
	{
		// reset the return variables (we're using StringBuffers instead of
		// Strings because you can't change a String value and pass it back
		// to the calling routine -- changing a String creates a new object)
		files.setLength(0);
		dirs.setLength(0);
		
		// get the NLST and the LIST -- don't worry if the commands
		// don't work, because we'll just end up sending nothing back
		// if that's the case
		String shortList = processFileListCommand("nlst " + params);
		String longList = processFileListCommand("list " + params);
		
		// tokenize the lists we got, using a newline as a separator
		StringTokenizer sList = new StringTokenizer(shortList, "\n");
		StringTokenizer lList = new StringTokenizer(longList, "\n");
		
		// other variables we'll need
		String sString;
		String lString;
		
		// assume that both lists have the same number of elements
		while ((sList.hasMoreTokens()) && (lList.hasMoreTokens())) {
			sString = sList.nextToken();
			lString = lList.nextToken();
			
			if (lString.length() > 0)
			{
				if (lString.startsWith("d"))
				{
					dirs.append(sString.trim() + lineTerm);
					debugPrint("Dir: " + sString);
				}  else if (lString.startsWith("-"))  {
					files.append(sString.trim() + lineTerm);
					debugPrint("File: " + sString);
				}  else  {
					// actually, symbolic links will start with an "l"
					// (lowercase L), but we're not going to mess with
					// those
					debugPrint("Unknown: " + lString);
				}
			}
		}
		
		// strip off any trailing line terminators and return the values
		if (files.length() > 0)  {  files.setLength(files.length() - lineTerm.length());  }
		if (dirs.length() > 0)  {  dirs.setLength(dirs.length() - lineTerm.length());  }
		
		return true;
	}
	

    /**
     * Executes the given FTP command on our current connection, returning the
     * three digit response code from the server.  This method only works for
     * commands that do not require an additional data port.
     */
    public int executeCommand(String command)
        throws IOException
    {
        outputStream.println(command);
        return getServerReply();
    }


    /**
     * Executes the given FTP command on our current connection, returning the
     * last line of the server's response.  Useful for commands that return
     * one line of information.
     */
    public String getExecutionResponse(String command)
        throws IOException
    {
        outputStream.println(command);
        return getFullServerReply();
    }


    /**
     * Executes the given ftpd command on the server and writes the results
     * returned on the data port to the file with the given name, returning true
     * if the server indicates that the operation was successful.
     */
    public boolean readDataToFile(String command, String fileName)
        throws IOException
    {
        // Open the local file
        RandomAccessFile outfile = new RandomAccessFile(fileName, "rw");

        // Do restart if desired
        if (restartPoint != 0) {
            debugPrint("Seeking to " + restartPoint);
            outfile.seek(restartPoint);
        }

        // Convert the RandomAccessFile to an OutputStream
        FileOutputStream fileStream = new FileOutputStream(outfile.getFD());
        boolean success = executeDataCommand(command, fileStream);

        outfile.close();

        return success;
    }


    /**
     * Executes the given ftpd command on the server and writes the contents
     * of the given file to the server on an opened data port, returning true
     * if the server indicates that the operation was successful.
     */
    public boolean writeDataFromFile(String command, String fileName) throws IOException
    {
    	
    	File file = new File(fileName);
    	
    	if(file.exists()){
    	
	        // Open the local file
	        RandomAccessFile infile = new RandomAccessFile(fileName, "r");
	
	        // Do restart if desired
	        if (restartPoint != 0) {
	            debugPrint("Seeking to " + restartPoint);
	            infile.seek(restartPoint);
	        }
	
	        // Convert the RandomAccessFile to an InputStream
	        FileInputStream fileStream = new FileInputStream(infile.getFD());
	        boolean success = executeDataCommand(command, fileStream);
	
	        infile.close();
	
	        return success;
	        
    	}
    	
    	return false; // file does not exist
    	
    }


    /**
     * Executes the given ftpd command on the server and writes the results
     * returned on the data port to the given OutputStream, returning true
     * if the server indicates that the operation was successful.
     */
    public boolean executeDataCommand(String command, OutputStream out)
        throws IOException
    {
        // Open a data socket on this computer
        ServerSocket serverSocket = new ServerSocket(0);
        if (!setupDataPort(command, serverSocket)) return false;
        Socket clientSocket = serverSocket.accept();

        // Transfer the data
        InputStream in = clientSocket.getInputStream();
        transferData(in, out);

        // Clean up the data structures
        in.close();
        clientSocket.close();
        serverSocket.close();

        return isPositiveCompleteResponse(getServerReply());    
    }


    /**
     * Executes the given ftpd command on the server and writes the contents
     * of the given InputStream to the server on an opened data port, returning
     * true if the server indicates that the operation was successful.
     */
    public boolean executeDataCommand(String command, InputStream in)
        throws IOException
    {
        // Open a data socket on this computer
        ServerSocket serverSocket = new ServerSocket(0);
        if (!setupDataPort(command, serverSocket)) return false;
        Socket clientSocket = serverSocket.accept();

        // Transfer the data
        OutputStream out = clientSocket.getOutputStream();
        transferData(in, out);

        // Clean up the data structures
        out.close();
        clientSocket.close();
        serverSocket.close();

        return isPositiveCompleteResponse(getServerReply());    
    }


    /**
     * Added by Julian: Executes the given ftpd command on the server 
     * and writes the results returned on the data port to the given 
     * StringBuffer, returning true if the server indicates that the 
     * operation was successful.
     */
    public boolean executeDataCommand(String command, StringBuffer sb)
        throws IOException
    {
        // Open a data socket on this computer
        ServerSocket serverSocket = new ServerSocket(0);
        if (!setupDataPort(command, serverSocket)) return false;
        Socket clientSocket = serverSocket.accept();

        // Transfer the data
        InputStream in = clientSocket.getInputStream();
        transferData(in, sb);

        // Clean up the data structures
        in.close();
        clientSocket.close();
        serverSocket.close();

        return isPositiveCompleteResponse(getServerReply());    
    }


    /**
     * Transfers the data from the given input stream to the given output
     * stream until we reach the end of the stream.
     */
    private void transferData(InputStream in, OutputStream out)
        throws IOException
    {
        byte b[] = new byte[BLOCK_SIZE];
        int amount;

        // Read the data into the file
        while ((amount = in.read(b)) > 0) {
            out.write(b, 0, amount);
        }
    }


    /**
     * Added by Julian: Transfers the data from the given input stream 
     * to the given StringBuffer until we reach the end of the stream.
     */
    private void transferData(InputStream in, StringBuffer sb)
        throws IOException
    {
        byte b[] = new byte[BLOCK_SIZE];
        int amount;

        // Read the data into the StringBuffer
        while ((amount = in.read(b)) > 0) {
            sb.append(new String(b, 0, amount));
        }
    }


    /**
     * Executes the given ftpd command on the server and writes the results
     * returned on the data port to the given FilterOutputStream, returning true
     * if the server indicates that the operation was successful.
     */
    private boolean setupDataPort(String command, ServerSocket serverSocket)
        throws IOException
    {
        // Send our local data port to the server
        if (!openPort(serverSocket)) return false;

        // Set binary type transfer
        outputStream.println("type i");
        if (!isPositiveCompleteResponse(getServerReply())) {
            debugPrint("Could not set transfer type");
            return false;
        }

        // If we have a restart point, send that information
        if (restartPoint != 0) {
            outputStream.println("rest " + restartPoint);
            restartPoint = 0;
            // TODO: Interpret server response here
            getServerReply();
        }

        // Send the command
        outputStream.println(command);

        return isPositivePreliminaryResponse(getServerReply());
    }


    /**
     * Get IP address and port number from serverSocket and send them via the
     * <code>port</code> command to the ftp server, returning true if we get a
     * valid response from the server, returning true if the server indicates
     * that the operation was successful.
     */
    private boolean openPort(ServerSocket serverSocket)
        throws IOException
    {                        
        int localport = serverSocket.getLocalPort();

        // get local ip address
        InetAddress inetaddress = serverSocket.getInetAddress();
        InetAddress localip;
        try {
            localip = inetaddress.getLocalHost();
        } catch(UnknownHostException e) {
            debugPrint("Can't get local host");
            return false;
        }

        // get ip address in high byte order
        byte[] addrbytes = localip.getAddress();

        // tell server what port we are listening on
        short addrshorts[] = new short[4];

        // problem:  bytes greater than 127 are printed as negative numbers
        for(int i = 0; i <= 3; i++) {
            addrshorts[i] = addrbytes[i];
            if (addrshorts[i] < 0)
                addrshorts[i] += 256;
        }

        outputStream.println("port " + addrshorts[0] + "," + addrshorts[1] +
                             "," + addrshorts[2] + "," + addrshorts[3] + "," +
                             ((localport & 0xff00) >> 8) + "," +
                             (localport & 0x00ff));

        return isPositiveCompleteResponse(getServerReply());
    }


    /**
     * True if the given response code is in the 100-199 range.
     */
    private boolean isPositivePreliminaryResponse(int response)
    {
        return (response >= 100 && response < 200);
    }


    /**
     * True if the given response code is in the 300-399 range.
     */
    private boolean isPositiveIntermediateResponse(int response)
    {
        return (response >= 300 && response < 400);
    }

    /**
     * True if the given response code is in the 200-299 range.
     */
    private boolean isPositiveCompleteResponse(int response)
    {
        return (response >= 200 && response < 300);
    }


    /**
     * True if the given response code is in the 400-499 range.
     */
    private boolean isTransientNegativeResponse(int response)
    {
        return (response >= 400 && response < 500);
    }


    /**
     * True if the given response code is in the 500-599 range.
     */
    private boolean isPermanentNegativeResponse(int response)
    {
        return (response >= 500 && response < 600);
    }


    /**
     * Eliminates the response code at the beginning of the response string.
     */
    private String excludeCode(String response)
    {
        if (response.length() < 5) return response;
        return response.substring(4);
    }

}

