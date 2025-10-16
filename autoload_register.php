<?php

// function __autoload($class) {
//     include 'classes/' . $class . '.class.php';
// }

class loader_autoViews
{
    public static function register()
    {
        spl_autoload_register(function ($DirFile) {
            $Conf_file = str_replace('\\', DIRECTORY_SEPARATOR, $DirFile).'.php';
   
            echo var_dump($Conf_file);

            if (file_exists($Conf_file)) {
                require $Conf_file;
                return true;
            }
            return false;
        });
    }


        function setHeaderContents($row, $col, $contents, $attributes = null)
    {
        $this->setCellContents($row, $col, $contents, 'TH');
        if (!is_null($attributes)) {
            $this->updateCellAttributes($row, $col, $attributes);
        }
    }
    

  public function autoload_require($DirFile) {

    $Conf_file = str_replace('\\', DIRECTORY_SEPARATOR, $DirFile).'.php';
    print var_dump($Conf_file);

        if (file_exists($Conf_file)) {
            require $Conf_file;
            return true;
        }
    return false;
  }
    
}



class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
   
            echo var_dump($file);

            if (file_exists($file)) {
                require $file;
                return true;
            }
            return false;
        });
    }
}


Autoloader::register();




    /**
     * Sets the contents of a header cell
     * @param    int     $row
     * @param    int     $col
     * @param    mixed   $contents
     * @param    mixed   $attributes  Associative array or string of table row
     *                                attributes
     * @access   public
     */




function my_autoloader($class) {
    echo var_dump('classes/' . $class . '.php');
    
    include 'classes/' . $class . '.php';
}

spl_autoload_register('my_autoloader');

// Or, using an anonymous function
spl_autoload_register(function ($class) {

    echo var_dump('classes/' . $class . '.php');
    include 'classes/' . $class . '.php';


    echo var_dump('classes/controllers/' . $class . '.php');
    include 'classes/controllers/' . $class . '.php';

    echo var_dump('classes/models/' . $class . '.php');
    include 'classes/models/' . $class . '.php';    
    
});



?>


