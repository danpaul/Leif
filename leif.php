<?php
  Class Leif
  {

/*******************************************************************************

          LEIF

About:
  Leif is an experimental flat file key/value store for PHP. I would not
  consider Leif production reay, it's more of a private test project. I am
  instersted in lightweight frameworks like Stacey CMS (http://www.staceyapp.com/) 
  and wanted a small database to allow for some dynamic content for mostly static 
  sites. I wanted the database to be small and simple, something that could be
  contained within the project/application/site folder so that an entire dynamic
  site could simply be copy and pasted between between dime-a-dozen PHP hosts
  with little to no setup or configuration.

  Leif also serializes and deserializes PHP objects. These objects can either
  be serialized using PHP's serialize or JSON method. The stored objects can
  be returned as either unserialized objects or as raw JSON.

  The storage method relys on hashing the key and then creating a directory
  based on a cerain number of characters (the default is 3) in the hash. For
  instance if the hash was '29246d...' the default storage location would be
  data/2/9/2/46d... This ensures a unique folder for each key and is intended
  to create a good distribution of keys amongst folders.

  Check the example and documentation for more information.

*******************************************************************************/

/*******************************************************************************

          CLASS VARIABLES AND CONSTRUCTOR

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

    const STORE_FILE_NAME = 'stor';
    const META_FILE_NAME = '.meta';
    const DEFAULT_DIRECTORY_DEPTH = 3;
    const DEFAULT_HASH_FUNCTION = 'md5';
    const DEFAULT_ENCODING = 'serialize';

    private $FILE_PERMISSION = 0777;

    private $data_root;
    private $directory_depth;
    private $hash_function;
    private $default_encoding;

    public function __construct($data_root_in = '', 
                  $directory_depth_in = 
                    self::DEFAULT_DIRECTORY_DEPTH, 
                  $hash_function_in = 
                    self::DEFAULT_HASH_FUNCTION,
                  $default_encoding_in =
                    self::DEFAULT_ENCODING)
    {
      if($data_root_in == ''){
        $this->data_root = dirname(__FILE__).'/data';
      }else{
        $this->data_root = $data_root_in;
      }
      $this->directory_depth = $directory_depth_in;
      $this->hash_function = $hash_function_in;
      $this->default_encoding = $default_encoding_in;
    }

/*******************************************************************************

          create
  Function:
    Creates a new diretory when given a unique key and stores a value
    as well as a hidden file containting the un-hashed key. Note, if you
    are using very large keys, they will be stored in the hidden file.
  Arguments:
    $key -- any hashable value
    $value --  Takes a key value of any hashable type, binary data to be 
      serialized.
    $encoding -- optional -- takes either bool (default is true) or
      string 'json' or 'serialize' -- if default true is used,
      default_encoding will be used. If false, no enconding will be used.
  Return:
    Returns true if successful.
    Returns false if key already exists.
    Dies and reports error if unable to save file.

*******************************************************************************/

    public function create($key, $value, $encoding = true)
    {
      $directory = $this->make_directory_name($key);
      if($encoding === true){$encoding = $this->default_encoding;}
      if($this->create_directory($directory))
      {
        if(! $this->write_file($key, 
                     self::META_FILE_NAME,
                     $key,
                     $encoding))
        {
          //Should not get here (file write should either die or
          //write the file.)
          die('Error in Class Leif, create function. Unable to '.
            'write file.');
        }
        if(! $this->write_file($key, 
                     self::STORE_FILE_NAME,
                     $value,
                     $encoding))
        {
          //Should not get here (file write should either die or
          //write the file.)
          die('Error in Class Leif, create function. Unable to '.
            'write file.');
        }
        return true;
      }
      else {return false;}
    }

/*******************************************************************************

          read
  Function:
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

    public function read($key, $encoding=true)
    {
      if (!($this->key_exists($key))){return;}
      if ($encoding===true){$encoding = $this->default_encoding;}
      $return_value;  
      $file_name = $this->get_path($key).self::STORE_FILE_NAME;
      if ($encoding === false)
      {
        $f = fopen($file_name,'  r');
        if($f)
        {
          $return_value = $fread($f, filesize($file_name));
          fclose($f);
          return($return_value);
        }else{
          die("Error in Leif Class, read() function. Unable to open ".
              "file.\n");
        }
      }
      $f = fopen($file_name,'r');
      if($f)
      {
        switch ($encoding)
        {
          case 'serialize':
            $return_value = unserialize(fread($f, filesize($file_name)));
            break;
          case 'json-raw':
            $return_value = fread($f, filesize($file_name));
            break;
          case 'json':
            $return_value = json_decode(fread($f, filesize($file_name)));
            break;
          default:
            fclose($f);
            die("error in Leif Class, read() function. Invalid ".
                "encode method.\n");
        }
        fclose($f);
        return($return_value);
      }
    }

/*******************************************************************************

          update
  Function:
    Takes a key,value and encoding. If the key exists, updates the value 
      and returns true. Otherwise, returns false.
    $encoding -- optional -- takes either true, false, 'json' or 
      'serailze'. If none or true is passed, uses default encoding. If 
      false is passed, uses no encoding, otherwise uses encoding
      specified.
    
*******************************************************************************/

    public function update($key, $value, $encoding = true)
    {
      if(!($this->key_exists($key))){return false;}

      $this->write_file($key,
                self::STORE_FILE_NAME,
                $value,
                $encoding);
      return true;
    }

/*******************************************************************************

          delete
  Function:
    Takes a key. Deletes its value and cleans up any empty directories 
      remaining for storing the store. Returns true on success, false on
      failure, nothing if $key doesn't exist.


*******************************************************************************/

    public function delete($key)
    {
      if(!($this->key_exists($key))){return;}
      $path = $this->get_path($key);      

      if(!(unlink($path.self::STORE_FILE_NAME))) {return false;}
      if(!(unlink($path.self::META_FILE_NAME))) {return false;}
      if(!(rmdir($path))) {return false;}

      $path = rtrim($path, '/');

      for($i = $this->directory_depth; $i > 0; $i--)
      {
        $path = substr($path, 0, strrpos($path, '/'));
        if(!(rmdir($path))){return true;}//directory is not empty
      }
    }

/*******************************************************************************

          upsert
  Function:
    Same as create except overwrites value if it already exists.

*******************************************************************************/

    public function upsert($key, $value, $encoding = true)
    {
      if ($this->key_exists($key))
      {
        $this->update($key, $value, $encoding);
      }
      else
      {
        $this->create($key, $value, $encoding);
      }
    }

/*******************************************************************************

          key_exists
  Function:
    Takes key, returns true if it is a valid key false if it isn't.

*******************************************************************************/

    public function key_exists($key)
    {
      if (is_dir($this->get_path($key))){return true;}
      return false;
    }

/*******************************************************************************

          make_directory_name
  Function:
    Takes hashable key and constructs a string representing the directory 
      location.

*******************************************************************************/

    private function make_directory_name($key)
    {
      $hash_function_reference = $this->hash_function;
      $key_hash = $hash_function_reference($key);
      $directory = '/';
      for($i = 0; $i < $this->directory_depth; $i++)
      {
        $directory .= $key_hash[$i].'/';
      }
      $directory .= substr($key_hash, $this->directory_depth).'/';
      return($directory);
    }

/*******************************************************************************
          
          create_directory

  Function:
    Creates directory specified by path string relative to class variable
    data_root.
  Input:
    Takes path as string.
  Returns:
    Returns true if directory created successfully.
    Returns false if directory already exists.
    Dies and reports error if unable to create directory.

*******************************************************************************/

    private function create_directory($path = '')
    {
      if(!$path == '')
      {
        if(!(is_dir($this->data_root.$path)))
        {
          if(!mkdir($this->data_root.$path, 
                $this->FILE_PERMISSION, 
                true))
          {
            die('Error in Class Leif, create_directory function. '.
              'Unable to create directory. Perhaps you do not '.
              'have write permission.');
          }
        }
        else {return false;}
      }
      return true;
    }

/*******************************************************************************

          write_file
  Function:
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

    private function write_file($key, 
                  $file_name,
                  & $value_to_store,
                  $encoding=true)
    {
      if($encoding===true){$encoding = $this->default_encoding;}
      elseif($encoding===false){$encoding = 'none';}
      $f = fopen($this->get_path($key).$file_name, 'w');
      if($f)
      {
        switch ($encoding) 
        {
          case 'none':
            fwrite($f, $value_to_store);
            break;
          case 'serialize':
            fwrite($f, serialize($value_to_store));
            break;
          case 'json':
            fwrite($f, json_encode($value_to_store));
            break;
          default:
            fclose($f);
            die('Error in Class Leif, function write_file(). '.
                'Invalid encoding type.');
        }
        fclose($f);
        return true;
      }else{
        die('Error in Class Leif, write_file function. '.
          'Unable to write file. Perhaps you do not '.
          'have write permission.');
      }
    }

/*******************************************************************************

          get_path
  Function:
    Takes a key and returns the full path of the value-store location.
    
*******************************************************************************/

    private function get_path($key)
    {
      return $this->data_root.$this->make_directory_name($key);
    }
  }
?>