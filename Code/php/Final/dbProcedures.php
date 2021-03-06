<?php
 
class dbProcedures 
{
 
    private $db;
	//Connect to db
    function __construct() 
    {
        require_once 'dbConnect.php';
        $this->db = new dbConnect();
        $this->db->connect();
    }
 
	//Add new user to database
    public function addUser($username, $pass) 
    {
        $result = mysql_query("Insert into Accounts(Username, Password, Type, Visibility,CreatedDateTime) VALUES('$username', '$pass', 1, 1, now())");
        // check success
        if ($result) 
        {
            //return user account
            $uid = mysql_insert_id(); // last inserted id
            $result = mysql_query("SELECT * FROM Accounts WHERE AccountID = '$uid'");
            return mysql_fetch_array($result);
        } 
        else 
        {
            return false;
        }
    }
 
    /**
     * Get user by email and password
     */
    public function login($username, $password) 
    {
        $result = mysql_query("SELECT * FROM Accounts WHERE Username = '$username'") or die(mysql_error());
        // check for result
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) 
        {
            $result = mysql_fetch_array($result);
            $storedPass = $result['Password'];
            // if passwords match, return account object
            if ($password == $storedPass) 
            {
                return $result;
            }
            else 
            {
				//incorrect password
				return false;
			}
        } 
        else 
        {
            // user not found
            return false;
        }
    }
 
    /**
     * Get user by ID
     */
    public function loginById($id, $password) 
    {
    	$result = mysql_query("SELECT * FROM Accounts WHERE AccountID = '$id'") or die(mysql_error());
    	// check for result
    	$no_of_rows = mysql_num_rows($result);
    	if ($no_of_rows > 0) {
    		$result = mysql_fetch_array($result);
    		$storedPass = $result['Password'];
    		// if passwords match, return account object
    		if ($password == $storedPass) 
    		{
    			return $result;
    		}
    		else 
    		{
    			//incorrect password
    			return false;
    		}
    	} 
    	else 
    	{
    		// user not found
    		return false;
    	}
    }
	
		// Return the user account object for a given username
	public function getUser($username)
	{
		$result = mysql_query("SELECT * FROM Accounts WHERE Username = '$username'") or die(mysql_error());
        // check for result
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) 
        {
            $result = mysql_fetch_array($result);
            return $result;
        } 
        else 
        {
            // user not found
            return false;
        }
	}
	/**
	 * Return a list of tags for the given user ID sorted by date
	 */
	   public function getTagsById($id) 
    {
		$result = mysql_query("Select t.*, a.Username From Tags t, Accounts A Where t.OwnerID = '$id' and t.OwnerID = a.AccountID Order By t.CreatedDateTime Desc") or die(mysql_error());
    	// check for result
    	$no_of_rows = mysql_num_rows($result);
    	if ($no_of_rows > 0) 
		{
    		// if user has tags, return them
    		return $result;
    	} 
    	else 
    	{
    		// user has no tags
    		return false;
    	}
    }
	
	//Return the Username for the given user id
	public function getNameFromId($id)
	{
		$result = mysql_query("Select a.Username From Accounts a Where a.AccountID = '$id'") or die(mysql_error());
    	// check for result
    	if ($result) 
		{
    		// return query result
    		return mysql_fetch_array($result);
    	} 
    	else 
    	{
    		// user has no tags
    		return false;
    	}
	}
	
    /**
     * Check if username already exists
     */
    public function checkUsername($uName) 
    {
        $result = mysql_query("SELECT Username from Accounts WHERE Username = '$uName'");
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) 
        {
            // uname taken
            return true;
        } 
        else 
        {
            // uname not taken
            return false;
        }
    }
    /**
     * Add tag to database. Will return true if the add was successful.
     */
    public function addTag($oId, $vis, $name, $desc, $imgUrl, $loc, $lat, $lon, $cat)
    {
    	$result = mysql_query("Select AddTag($oId, \"$name\", $vis, \"$desc\", \"$imgUrl\", \"$loc\", $lat, $lon, \"$cat\")");
    	return $result;
    }

    //remove a tag from, the database
    public function removeTag($tId)
    {
        $result = mysql_query("Delete from Tags Where TagId = '$tId'");
		return $result;
    }


 //remove a friend association from the database
    public function removeFriend($uId, $fId)
    {
        $result = mysql_query("Delete from friendassociations where (uID,fID) IN (($uId,$fId),($fId,$uId))");
		return $result;
    }
   //attempt to add a friend
    
   public function addFriend($userId, $friendName) 
   {
		$result = mysql_query("Select AddFriend($userId,'$friendName')");
		$returnCode = mysql_result($result, 0);
		return $returnCode;
   
   } 
    public function getFriends($userId) 
   {
		$result = mysql_query("Select * From Accounts Where AccountID In
								(Select fID From FriendAssociations
									Where uId = '$userId')");
		$no_of_rows = mysql_num_rows($result);
    	if ($no_of_rows > 0) 
		{
    		// if user has friends, return them
    		return $result;
    	} 
    	else 
    	{
    		// user has no friends
    		return false;
    	}
   } 
   public function editProfile($imgUrl, $desc, $location, $quote, $uId)
   {
		$result = mysql_query("Update Accounts set Image = '$imgUrl',Description = '$desc', Location = '$location', Quote = '$quote' Where AccountID = $uId");
		return $result;
   }
//add a comment to a tag, return the comment object
   public function addTagComment($tagId, $comment, $uName)
   {
		$result = mysql_query("Insert into tagcomments(ParentTagID, Username, Text, CreatedDateTime) Values ($tagId, '$uName','$comment', now())");
		if ($result)
		{
			//if success, return full comment object
			$commentID = mysql_insert_id();
			$obj = mysql_query("Select * from tagcomments where ID = $commentID");
			$obj = mysql_fetch_array($obj);
			return $obj;
		}
		else
		{
			return false;
		}
	}
//get all comments for a given tagid
   public function getTagComments($tagId)
   {
		$result = mysql_query("Select * From tagcomments Where ParentTagID = $tagId");
		$no_of_rows = mysql_num_rows($result);
    	if ($no_of_rows > 0) 
		{
    		// if tag has comments, return them
    		return $result;
    	} 
    	else 
    	{
    		// tag has no comments
    		return false;
    	}
   }
//delete tag comment
   public function deleteTagComment($commentId)
   {
		$result = mysql_query("Delete From tagcomments Where ID = $commentId");
		return $result;
   }
	
   
}
 
?>