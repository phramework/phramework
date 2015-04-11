<?php

namespace Phramework\API\models;

use Phramework\API\models\util;
use Phramework\API\exceptions\not_found;

/**
 * Upload class
 * Provides functions for uploading files and images
 * 
 * @since 0
 * @category models
 * @package API
 */
class upload {

    /**
     * Upload file
     * @param array $parameters The request parameters.
     * @param string $index The paramatern index for example 'file'.
     * @param array $move If $move is set ( Is not ( null or ==array() or FALSE )) then the uploaded file will be moved to the specified directory. The $move takes indexes 'path' and 'name', the path is the directory the uploaded file will be moved. The name is optional and
     * @param array $allowed_filetypes Optional. Array with allowed extensions.
     * @param integer $max_size Optional. The maximum size of the uploaded file in bytes. Default value is 10485760 bytes
     * @param boolean $parse_extension Optional. If TRUE the destination filename will be joined with files extension  Default value is FALSE
     */
    public static function file($file, $move = [], $allowed_filetypes = [ 'csv'], $max_size = 10485760, $parse_extension = FALSE) {
        if (!$file) {
            return 'Select a file';
        }
        $temporary_path = $file['tmp_name'];
        if (!file_exists($temporary_path)) {
            throw new not_found('File not found');
        }
        $filename = $file['name'];
        $ext = util::extension($filename);
        if (!in_array($ext, $allowed_filetypes)) {
            return 'Incorrect file type';
        }
        $size = filesize($temporary_path);
        if ($size > $max_size) {
            return 'File size exceeds maximum';
        }
        if ($move) {
            if (!is_array($move)) {
                $move['path'] = $move;
            }
            if (isset($move['name']) && $parse_extension) {
                $move['name'] .= '.' . $ext;
            } else if ($parse_extension) {
                $move['path'] .= '.' . $ext;
            }
            $destination = util::get_path(isset($move['name']) ? [ $move['path'], $move['name']] : [ $move['path']] );
            if (!rename($temporary_path, $destination)) {
                return 'Error uploading file';
            }
            return [ 'path' => $destination, 'name' => basename($destination), 'size' => filesize($destination), 'name_original' => basename($file['name'])];
        } else {
            return [ 'path' => $temporary_path, 'name' => basename($temporary_path), 'size' => filesize($temporary_path), 'name_original' => basename($file['name'])];
        }
    }

    /**
     * Upload image
     *
     * @param Object $file Reference to uploaded file
     * @param Array $move Move is an array with two indexes, `path` is required and it points the path be stored, `name` is optional and it tells the file name to be stored if not set if will be copied from original upload file name
     * @param Array $sizes Is an indexed array
     * @param UInt $max_file_size Maximum file size, Optional by default it's 2MB
     * @param Array $allowed_filetypes Array with allowed image extensions, Optional by default it's 'jpg', 'gif', 'png', 'jpeg'
     * @return Array|String If return is not array then it's an error Message, If it's array then every index of the requested Sizes parameter containts the file path of the specific size
     */
    public static function image($file, $move = [], $sizes = [], $max_file_size = 2097152, $allowed_filetypes = [ 'jpg', 'gif', 'png', 'jpeg']) {
        if (!$file) {
            return 'Select a file';
        }
        $temporary_path = $file['tmp_name'];
        if (!file_exists($temporary_path)) {
            throw new not_found('File not found');
        }
        $filename = $file['name'];
        $ext = strtolower(preg_replace('/^.*\.([^.]+)$/D', '$1', $filename));
        if (!in_array($ext, $allowed_filetypes)) {
            return 'Incorrect file type';
        }
        $size = filesize($temporary_path);
        if ($size > $max_file_size) {
            return 'File size exceeds maximum';
        }
        $image_info = getimagesize($temporary_path);
        if (!$image_info) {
            return 'Select an image file';
        }
        if (!$sizes) {
            return 'Sizes not set';
        }
        //Read image
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $src = imagecreatefromjpeg($temporary_path);
        } else if ($ext == 'png') {
            $PNG = TRUE;
            $src = imagecreatefrompng($temporary_path);
        } else if ($ext == 'gif') {
            $src = imagecreatefromgif($temporary_path);
        } else if ($ext == 'bmp') {
            $src = imagecreatefromwbmp($temporary_path);
        } else {
            return 'Unsupported filetype';
        }
        //Get image dimensions
        $width = imagesx($src);
        $height = imagesy($src);
        $returnArray = [ $name => FALSE];
        //Resize for other sizes
        foreach ($sizes as $key => $value) {
            if (!isset($value[0]) || !isset($value[1]) || !is_numeric($value[0]) || !is_numeric($value[1])) {
                continue;
            }
            //Filename
            $destination = \util::get_path(isset($move['name']) ? [ $move['path'], $move['name']] : [ $move['path'], \util::toAscii($filename)] );
            //If requires resize
            if ($height > $value[0] || $width > $value[1]) {
                $destination_name = \util::get_path([ $destination_name, '_' . $key . ($PNG ? '.png' : 'jpg')]);
                $newheight = $value[0];
                $newwidth = $value[1];
                if ($width > $height) {
                    $newheight = ($height / $width) * $value[1];
                } else {
                    $newwidth = ($width / $height) * $value[0];
                }
                $tmp = imagecreatetruecolor($newwidth, $newheight);
                if ($PNG) {//If png mode keep transparency
                    imagealphablending($tmp, false);
                    imagesavealpha($tmp, true);
                    $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
                    imagefilledrectangle($tmp, 0, 0, $newwidth, $newheight, $transparent);
                }
                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                if ($PNG) {
                    imagepng($destination_name);
                } else {
                    imagejpeg($destination_name, 100);
                }
                $returnArray[$name . '_' . $key] = $destination_name;
                imagedestroy($tmp);
            } else {//Move file without resizing
                //Use the original extension
                $destination_name = \util::get_path([ $destination_name, '_' . $key . '.' . $ext]);
                copy($temporary_path, $destination_name);
                $returnArray[$name . '_' . $key] = $destination_name;
            }
        }
        //Destroy image source
        imagedestroy($src);
        //Delete temporary file
        unlink($temporary_path);
        //Return returnArray
        return $returnArray;
    }

    /**
     * Create a zip archive
     * @param $destination String Zip archive path
     * @param $files array array( array( 'filename' => .. 'path' => .. ) ) 
     * @param $blobs array array( array( 'filename' => .. 'contents' => .. ) ) 
     */
    public static function create_zip($destination, $files = [], $blobs = [], $overwrite = TRUE) {
        if (!class_exists('ZipArchive')) {
            throw new Exception('cannot_create_zip_archive');
        }
        //if the zip file already exists and overwrite is false, return false
        if (file_exists($destination) && !$overwrite) {
            return FALSE;
        }
        /* //vars
          $valid_files = array( );
          //if files were passed in...
          if (is_array( $files )) {
          //cycle through each file
          foreach ($files as $file) {
          //make sure the file exists
          if (file_exists( $file )) {
          $valid_files[ ] = $file;
          }
          }
          } */
        //if we have good files...
        // if (count( $valid_files )) {
        //create the archive
        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== TRUE) {
            return FALSE;
        }
        //Add the files
        foreach ($files as $file) {
            $filename = $blob['filename'];
            $path = $blob['path'];

            if (!file_exists($path)) {
                throw new Exception('file_not_found');
            }
            $zip->addFile($filename, $path);
        }
        //Add the $blobs
        foreach ($blobs as $blob) {
            $filename = $blob['filename'];
            $contents = $blob['contents'];
            $zip->addFromString($filename, $contents);
        }

        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
        //close the zip -- done!
        $zip->close();
        //check to make sure the file exists
        return file_exists($destination);
        // } else {
        //     return FALSE;
        //}
    }

}