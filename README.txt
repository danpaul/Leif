/*******************************************************************************

Note: for a quick overview, see example.php.

About:

  Leif is an experimental flat file key/value store for PHP. It's still in the
  development stages. You are free to use it but I make no claims to its 
  reliability at this stage.

  Leif grew out of an interest in lightweight CMSs like Stacey 
  (http://www.staceyapp.com/) which rely on flat files to store data. My idea
  was to creat a data store that can be imbedded into projects and applications.
  I wanted something that would allow for some dynamic content on a site
  without having to configure and use a database, allowing a site's data
  to be stored with the site and allowing site owner to simply copy/paste
  their projects between hosts.

  Objects are serialized using the `serlialize` php function or using
  `json_encode`, and can be returned either deserialized or as raw json.

  The storage method relys on hashing the key and then creating a directory
  based on a certain number of bytes in the hash (the default is 3). For
  instance, if the hash is '29246d...' the default storage location would be
  data/2/9/2/46d... This ensures a unique folder for each key and is intended
  to create a good distribution of keys amongst directories.

  Check the example.php and documentation for more information.

*******************************************************************************/

/*******************************************************************************

          CONSTRUCTOR

  Constructor arguments:
    $data_root_in:
      Location of data root. You must have permission to read/write to this
        location. Deafult is './data'
    $hash_function_in
      Any valid PHP hash type. md5 is used by default. This is not used for
        security but to randomize and distribute the keys.
    $default_encoding_in
      Default is PHP's serialize method. JSON can also be used.

*******************************************************************************/

/*******************************************************************************

          create

  Public Function:
    Creates a new diretory when given a unique key and stores a value
    as well as a hidden file containting the un-hashed key. Note, if you
    are using very large keys, they will be stored in the hidden file.
  Arguments:
    $key -- any hashable value
    $value --  Takes a key value of any hashable type, binary data to be 
      serialized.
    $encoding -- optional -- takes either bool (default is true) or
      string 'json' or 'serialize' -- if default (true) is used,
      default_encoding will be used (set in constructor). If false, no 
      enconding will be used.
  Return:
    Returns true if successful.
    Returns false if key already exists.
    Dies and reports error if unable to save file.

*******************************************************************************/

/*******************************************************************************

          read

  Public Function:
    Takes a valid key and returns its serialized value.
  Arguments:
    $key -- string -- Key for the value stored.
    $encoding -- optional -- boolean or either 'json' or 'serialize'
      If true is passed, default encoding is used.
      If false is passed, no encoding is used.
  Returns:
    The value, decoded if $encoding is specified.
    Returns nothing if $key doesn't exist.
    
*******************************************************************************/

/*******************************************************************************

          update

  Public Function:
    Takes a key,value and encoding. If the key exists, updates the value 
      and returns true. Otherwise, returns false.
    $encoding -- optional -- takes either true, false, 'json' or 
      'serailze'. If none or true is passed, uses default encoding. If 
      false is passed, uses no encoding, otherwise uses encoding
      specified.
    
*******************************************************************************/

/*******************************************************************************

          upsert

  Publice Function:
    Same as create except overwrites value if it already exists.

*******************************************************************************/

/*******************************************************************************

          delete

  Public Function:
    Takes a key. Deletes its value and cleans up any empty directories 
      remaining for storing the store. Returns true on success, false on
      failure, nothing if $key doesn't exist.


*******************************************************************************/

/*******************************************************************************

          key_exists

  Public Function:
    Takes key, returns true if it is a valid key false if it isn't.

*******************************************************************************/



/*******************************************************************************
                                
                                PRIVATE
                                
*******************************************************************************/

/*******************************************************************************

          make_directory_name

  Private Function:
    Takes hashable key and constructs a string representing the directory 
      location.

*******************************************************************************/

/*******************************************************************************
          
          create_directory

  Private Function:
    Creates directory specified by path string relative to class variable
    data_root.
  Input:
    Takes path as string.
  Returns:
    Returns true if directory created successfully.
    Returns false if directory already exists.
    Dies and reports error if unable to create directory.

*******************************************************************************/


/*******************************************************************************

     write_file


  Private Function:
    Writes value to file. Overwrites file if it already exists. Opriontally
      encodes to json or serialize.
  Input:
    $directory -- string -- the path to the file
    $file_name -- string -- the name of the file
    $value_to_store -- writes binary data to file.
    $encondind -- optional -- string either 'json' or 'serialize', or bool.
      If true or no argument, default encoding is used. If false, no 
      encoding is used. Othewise uses specified encoding.

*******************************************************************************/

/*******************************************************************************
  
      get_path

  Private Function:
    Takes a key and returns the full path of the value-store location.
    
*******************************************************************************/
